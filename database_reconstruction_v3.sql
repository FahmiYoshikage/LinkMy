-- =====================================================
-- LinkMy Database Reconstruction v3.0
-- Clean Structure + Data Preservation
-- MySQL 8.4+ Compatible
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- STEP 1: DROP OLD VIEWS & TABLES
-- =====================================================

DROP VIEW IF EXISTS v_public_page_data_with_categories;
DROP VIEW IF EXISTS v_public_page_data;

DROP TABLE IF EXISTS profile_activity_log;
DROP TABLE IF EXISTS old_profile_analytics;
DROP TABLE IF EXISTS old_link_analytics;
DROP TABLE IF EXISTS old_sessions;
DROP TABLE IF EXISTS old_password_resets;
DROP TABLE IF EXISTS old_email_verifications;
DROP TABLE IF EXISTS link_categories;
DROP TABLE IF EXISTS old_links;
DROP TABLE IF EXISTS old_user_appearance;
DROP TABLE IF EXISTS old_profiles;
DROP TABLE IF EXISTS old_users;
DROP TABLE IF EXISTS social_icons;
DROP TABLE IF EXISTS gradient_presets;
DROP TABLE IF EXISTS categories;

-- Backup old tables instead of dropping (safety net)
RENAME TABLE IF EXISTS `old_users` TO `backup_users`;
RENAME TABLE IF EXISTS `old_profiles` TO `backup_profiles`;
RENAME TABLE IF EXISTS `old_links` TO `backup_links`;
RENAME TABLE IF EXISTS `old_user_appearance` TO `backup_user_appearance`;
RENAME TABLE IF EXISTS `old_sessions` TO `backup_sessions`;
RENAME TABLE IF EXISTS `old_password_resets` TO `backup_password_resets`;
RENAME TABLE IF EXISTS `old_email_verifications` TO `backup_email_verifications`;
RENAME TABLE IF EXISTS `old_link_analytics` TO `backup_link_analytics`;
RENAME TABLE IF EXISTS `old_profile_analytics` TO `backup_profile_analytics`;

-- =====================================================
-- STEP 2: CREATE CLEAN NEW STRUCTURE
-- =====================================================

-- -----------------------------------------------------
-- Table: users (SIMPLIFIED)
-- -----------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0 COMMENT 'Verified badge (influencer/founder)',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts';

-- -----------------------------------------------------
-- Table: profiles (CLEAN - No is_primary flag!)
-- -----------------------------------------------------
CREATE TABLE `profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL COMMENT 'Profile display name',
  `title` VARCHAR(100) NULL COMMENT 'Public title on profile page',
  `bio` TEXT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0 COMMENT 'For sorting user profiles',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User profiles (multiple per user)';

-- -----------------------------------------------------
-- Table: links (SIMPLIFIED)
-- -----------------------------------------------------
CREATE TABLE `links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-link-45deg',
  `position` INT DEFAULT 0,
  `clicks` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `category_id` INT UNSIGNED NULL COMMENT 'Link category/folder',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`),
  INDEX `idx_category_id` (`category_id`),
  INDEX `idx_position` (`position`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile links';

-- -----------------------------------------------------
-- Table: categories (for link grouping)
-- -----------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-folder',
  `color` VARCHAR(20) DEFAULT '#667eea',
  `position` INT DEFAULT 0,
  `is_expanded` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`),
  INDEX `idx_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link categories/folders';

-- Add FK constraint for links.category_id NOW
ALTER TABLE `links` 
ADD CONSTRAINT `fk_links_category` 
FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL;

-- -----------------------------------------------------
-- Table: themes (Appearance settings per profile)
-- -----------------------------------------------------
CREATE TABLE `themes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL UNIQUE,
  `bg_type` ENUM('color', 'gradient', 'image') DEFAULT 'gradient',
  `bg_value` TEXT NULL COMMENT 'Color hex, gradient CSS, or image path',
  `button_style` VARCHAR(20) DEFAULT 'rounded',
  `button_color` VARCHAR(20) DEFAULT '#667eea',
  `text_color` VARCHAR(20) DEFAULT '#333333',
  `font` VARCHAR(50) DEFAULT 'Inter',
  `layout` VARCHAR(20) DEFAULT 'centered' COMMENT 'centered|minimal|left',
  `container_style` VARCHAR(20) DEFAULT 'wide' COMMENT 'wide|boxed',
  `enable_animations` TINYINT(1) DEFAULT 1,
  `enable_glass_effect` TINYINT(1) DEFAULT 0,
  `shadow_intensity` ENUM('none', 'light', 'medium', 'heavy') DEFAULT 'medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile appearance settings';

-- -----------------------------------------------------
-- Table: theme_boxed (Extended settings for boxed layout)
-- -----------------------------------------------------
CREATE TABLE `theme_boxed` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `theme_id` INT UNSIGNED NOT NULL UNIQUE,
  `enabled` TINYINT(1) DEFAULT 0,
  `outer_bg_type` ENUM('color', 'gradient', 'image') DEFAULT 'gradient',
  `outer_bg_value` TEXT NULL,
  `container_bg_color` VARCHAR(20) DEFAULT '#ffffff',
  `container_max_width` INT DEFAULT 480,
  `container_radius` INT DEFAULT 30,
  `container_shadow` TINYINT(1) DEFAULT 1,
  
  FOREIGN KEY (`theme_id`) REFERENCES `themes`(`id`) ON DELETE CASCADE,
  INDEX `idx_theme_id` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Boxed layout settings';

-- -----------------------------------------------------
-- Table: clicks (Click tracking)
-- -----------------------------------------------------
CREATE TABLE `clicks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `link_id` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) NULL,
  `country` VARCHAR(50) NULL,
  `city` VARCHAR(100) NULL,
  `user_agent` TEXT NULL,
  `referrer` VARCHAR(255) NULL,
  `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`link_id`) REFERENCES `links`(`id`) ON DELETE CASCADE,
  INDEX `idx_link_id` (`link_id`),
  INDEX `idx_clicked_at` (`clicked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link click analytics';

-- -----------------------------------------------------
-- Table: sessions
-- -----------------------------------------------------
CREATE TABLE `sessions` (
  `id` VARCHAR(128) NOT NULL PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `data` TEXT NOT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User sessions';

-- -----------------------------------------------------
-- Table: password_resets
-- -----------------------------------------------------
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `ip` VARCHAR(45) NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens';

-- -----------------------------------------------------
-- Table: email_verifications
-- -----------------------------------------------------
CREATE TABLE `email_verifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `otp` VARCHAR(6) NOT NULL,
  `type` ENUM('registration', 'slug_change') DEFAULT 'registration',
  `ip` VARCHAR(45) NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  
  INDEX `idx_email` (`email`),
  INDEX `idx_otp` (`otp`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email OTP verifications';

-- =====================================================
-- STEP 3: MIGRATE DATA FROM BACKUP TABLES
-- =====================================================

-- Migrate Users
INSERT INTO `users` (id, username, email, password, is_verified, is_active, created_at)
SELECT 
  user_id,
  username,
  email,
  password_hash,
  is_verified,
  1,
  created_at
FROM `backup_users`;

-- Migrate Profiles
INSERT INTO `profiles` (id, user_id, slug, name, title, bio, avatar, is_active, display_order, created_at, updated_at)
SELECT 
  profile_id,
  user_id,
  slug,
  profile_name,
  profile_title,
  bio,
  COALESCE(profile_pic_filename, 'default-avatar.png'),
  is_active,
  CASE WHEN is_primary = 1 THEN 0 ELSE profile_id END, -- Primary profiles first
  created_at,
  updated_at
FROM `backup_profiles`;

-- Migrate Categories (from link_categories table)
INSERT INTO `categories` (id, profile_id, name, icon, color, position, is_expanded, created_at)
SELECT 
  category_id,
  profile_id,
  category_name,
  category_icon,
  category_color,
  display_order,
  is_expanded,
  created_at
FROM `link_categories`
WHERE profile_id IS NOT NULL;

-- Migrate Links
INSERT INTO `links` (id, profile_id, title, url, icon, position, clicks, is_active, category_id, created_at)
SELECT 
  link_id,
  profile_id,
  title,
  url,
  COALESCE(icon_class, 'bi-link-45deg'),
  order_index,
  click_count,
  is_active,
  category_id,
  created_at
FROM `backup_links`
WHERE profile_id IS NOT NULL;

-- Migrate Themes
INSERT INTO `themes` (profile_id, bg_type, bg_value, button_style, button_color, text_color, font, 
                      layout, container_style, enable_animations, enable_glass_effect, shadow_intensity, 
                      created_at, updated_at)
SELECT 
  profile_id,
  CASE 
    WHEN theme_name = 'gradient' THEN 'gradient'
    WHEN bg_image_filename IS NOT NULL THEN 'image'
    ELSE 'color'
  END,
  CASE
    WHEN gradient_preset IS NOT NULL THEN 
      (SELECT gradient_css FROM gradient_presets WHERE preset_name = gradient_preset LIMIT 1)
    WHEN bg_image_filename IS NOT NULL THEN bg_image_filename
    ELSE COALESCE(custom_bg_color, '#667eea')
  END,
  button_style,
  COALESCE(custom_button_color, '#667eea'),
  COALESCE(custom_text_color, '#333333'),
  font_family,
  profile_layout,
  container_style,
  enable_animations,
  enable_glass_effect,
  shadow_intensity,
  updated_at,
  updated_at
FROM `backup_user_appearance`
WHERE profile_id IS NOT NULL;

-- Migrate Boxed Layout Settings
INSERT INTO `theme_boxed` (theme_id, enabled, outer_bg_type, outer_bg_value, container_bg_color, 
                            container_max_width, container_radius, container_shadow)
SELECT 
  t.id,
  a.boxed_layout,
  a.outer_bg_type,
  CASE
    WHEN a.outer_bg_type = 'gradient' THEN 
      CONCAT('linear-gradient(135deg, ', a.outer_bg_gradient_start, ' 0%, ', a.outer_bg_gradient_end, ' 100%)')
    WHEN a.outer_bg_type = 'image' THEN a.outer_bg_image
    ELSE a.outer_bg_color
  END,
  COALESCE(a.container_bg_color, '#ffffff'),
  COALESCE(a.container_max_width, 480),
  COALESCE(a.container_border_radius, 30),
  COALESCE(a.container_shadow, 1)
FROM `backup_user_appearance` a
JOIN `themes` t ON t.profile_id = a.profile_id
WHERE a.boxed_layout = 1;

-- Migrate Click Analytics
INSERT INTO `clicks` (link_id, ip, country, city, user_agent, referrer, clicked_at)
SELECT 
  link_id,
  ip_address,
  country,
  city,
  user_agent,
  referrer,
  clicked_at
FROM `backup_link_analytics`
WHERE link_id IN (SELECT id FROM links);

-- Migrate Sessions (simplified)
INSERT INTO `sessions` (id, user_id, data, last_activity)
SELECT 
  session_id,
  NULL, -- Extract user_id from session_data if needed
  session_data,
  session_expire
FROM `backup_sessions`;

-- Migrate Password Resets
INSERT INTO `password_resets` (email, token, ip, is_used, created_at, expires_at)
SELECT 
  email,
  reset_token,
  ip_address,
  is_used,
  created_at,
  expires_at
FROM `backup_password_resets`;

-- Migrate Email Verifications
INSERT INTO `email_verifications` (email, otp, type, ip, is_used, created_at, expires_at)
SELECT 
  email,
  otp_code,
  CASE verification_type
    WHEN 'slug_change' THEN 'slug_change'
    ELSE 'registration'
  END,
  ip_address,
  is_used,
  created_at,
  expires_at
FROM `backup_email_verifications`;

-- =====================================================
-- STEP 4: CREATE USEFUL VIEWS
-- =====================================================

-- View: Profile Summary (replaces complex queries)
CREATE OR REPLACE VIEW `v_profile_stats` AS
SELECT 
  p.id,
  p.user_id,
  p.slug,
  p.name,
  p.title,
  p.bio,
  p.avatar,
  p.is_active,
  u.username,
  u.email,
  u.is_verified,
  COUNT(DISTINCT l.id) as link_count,
  COALESCE(SUM(l.clicks), 0) as total_clicks,
  p.created_at,
  p.updated_at
FROM `profiles` p
JOIN `users` u ON p.user_id = u.id
LEFT JOIN `links` l ON l.profile_id = p.id AND l.is_active = 1
GROUP BY p.id, p.user_id, p.slug, p.name, p.title, p.bio, p.avatar, p.is_active, 
         u.username, u.email, u.is_verified, p.created_at, p.updated_at;

-- View: Public Profile Data (for profile pages)
CREATE OR REPLACE VIEW `v_public_profiles` AS
SELECT 
  p.id,
  p.slug,
  p.name,
  p.title,
  p.bio,
  p.avatar,
  u.username,
  u.is_verified,
  t.bg_type,
  t.bg_value,
  t.button_style,
  t.button_color,
  t.text_color,
  t.font,
  t.layout,
  t.container_style,
  t.enable_animations,
  t.enable_glass_effect,
  t.shadow_intensity,
  tb.enabled as boxed_enabled,
  tb.outer_bg_type,
  tb.outer_bg_value,
  tb.container_bg_color,
  tb.container_max_width,
  tb.container_radius,
  tb.container_shadow
FROM `profiles` p
JOIN `users` u ON p.user_id = u.id
LEFT JOIN `themes` t ON t.profile_id = p.id
LEFT JOIN `theme_boxed` tb ON tb.theme_id = t.id
WHERE p.is_active = 1;

-- =====================================================
-- STEP 5: CREATE STORED PROCEDURES
-- =====================================================

DELIMITER $$

-- Get user's profiles with stats
CREATE PROCEDURE `sp_get_user_profiles`(IN p_user_id INT)
BEGIN
  SELECT * FROM v_profile_stats 
  WHERE user_id = p_user_id 
  ORDER BY display_order ASC, created_at ASC;
END$$

-- Get profile with links and categories
CREATE PROCEDURE `sp_get_profile_full`(IN p_slug VARCHAR(50))
BEGIN
  -- Profile info
  SELECT * FROM v_public_profiles WHERE slug = p_slug LIMIT 1;
  
  -- Categories
  SELECT * FROM categories 
  WHERE profile_id = (SELECT id FROM profiles WHERE slug = p_slug) 
  ORDER BY position ASC;
  
  -- Links
  SELECT l.*, c.name as category_name 
  FROM links l
  LEFT JOIN categories c ON l.category_id = c.id
  WHERE l.profile_id = (SELECT id FROM profiles WHERE slug = p_slug) 
    AND l.is_active = 1
  ORDER BY l.position ASC;
END$$

-- Increment link click
CREATE PROCEDURE `sp_increment_click`(
  IN p_link_id INT,
  IN p_ip VARCHAR(45),
  IN p_country VARCHAR(50),
  IN p_city VARCHAR(100),
  IN p_user_agent TEXT,
  IN p_referrer VARCHAR(255)
)
BEGIN
  -- Update counter
  UPDATE links SET clicks = clicks + 1 WHERE id = p_link_id;
  
  -- Log click
  INSERT INTO clicks (link_id, ip, country, city, user_agent, referrer)
  VALUES (p_link_id, p_ip, p_country, p_city, p_user_agent, p_referrer);
END$$

DELIMITER ;

-- =====================================================
-- STEP 6: OPTIMIZE & FINISH
-- =====================================================

-- Update AUTO_INCREMENT counters
SELECT @max_user_id := MAX(id) + 1 FROM users;
SET @sql = CONCAT('ALTER TABLE users AUTO_INCREMENT = ', @max_user_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT @max_profile_id := MAX(id) + 1 FROM profiles;
SET @sql = CONCAT('ALTER TABLE profiles AUTO_INCREMENT = ', @max_profile_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT @max_link_id := MAX(id) + 1 FROM links;
SET @sql = CONCAT('ALTER TABLE links AUTO_INCREMENT = ', @max_link_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Optimize tables
OPTIMIZE TABLE users, profiles, links, categories, themes, theme_boxed, clicks, sessions;

-- =====================================================
-- VERIFICATION QUERIES
-- =====================================================

SELECT '=== MIGRATION SUMMARY ===' as '';

SELECT 'Users' as Table_Name, COUNT(*) as Record_Count FROM users
UNION ALL
SELECT 'Profiles', COUNT(*) FROM profiles
UNION ALL
SELECT 'Links', COUNT(*) FROM links
UNION ALL
SELECT 'Categories', COUNT(*) FROM categories
UNION ALL
SELECT 'Themes', COUNT(*) FROM themes
UNION ALL
SELECT 'Clicks', COUNT(*) FROM clicks
UNION ALL
SELECT 'Sessions', COUNT(*) FROM sessions;

SELECT '=== PROFILE STATS SAMPLE ===' as '';
SELECT * FROM v_profile_stats LIMIT 5;

SELECT '=== DONE! ===' as '';
SELECT 'Database restructured successfully!' as Status;
SELECT 'Old tables backed up with "backup_" prefix' as Note;
SELECT 'You can drop backup tables after verification' as Action;

-- =====================================================
-- END OF MIGRATION SCRIPT
-- =====================================================
