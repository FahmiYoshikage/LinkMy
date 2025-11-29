-- ========================================
-- LINKMY - MULTI-PROFILE SYSTEM
-- Database Migration Script v2.0
-- ========================================
-- MAJOR UPGRADE: From single profile to multiple profiles per user
-- 
-- This migration transforms LinkMy into a multi-profile system where:
-- - 1 user account can manage multiple profiles (max 2 for free tier)
-- - Each profile has its own: slug, appearance, links, bio, categories
-- - Profiles are completely independent from each other
-- - User logs in once to manage all their profiles
-- ========================================

-- ========================================
-- PHASE 1: CREATE PROFILES TABLE
-- ========================================

-- Drop existing table if exists (WARNING: This will delete all profile data!)
DROP TABLE IF EXISTS `profile_activity_log`;
DROP TABLE IF EXISTS `profile_analytics`;
DROP TABLE IF EXISTS `profiles`;

CREATE TABLE `profiles` (
  `profile_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL COMMENT 'Owner of this profile',
  `slug` VARCHAR(50) NOT NULL COMMENT 'Unique URL slug for this profile',
  `profile_name` VARCHAR(100) NOT NULL COMMENT 'Profile display name (e.g., "Personal", "Business")',
  `profile_description` TEXT NULL COMMENT 'Internal note about this profile',
  
  -- Profile branding
  `profile_title` VARCHAR(100) NULL COMMENT 'Public title shown on profile page',
  `bio` TEXT NULL COMMENT 'Profile bio/description',
  `profile_pic_filename` VARCHAR(255) NULL COMMENT 'Profile picture',
  
  -- Status flags
  `is_primary` TINYINT(1) DEFAULT 0 COMMENT '1 = primary/default profile, 0 = secondary',
  `is_active` TINYINT(1) DEFAULT 1 COMMENT '1 = active, 0 = deactivated',
  
  -- Metadata
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `last_accessed_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Last time profile was viewed',
  
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `unique_slug` (`slug`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_user_primary` (`user_id`, `is_primary`),
  KEY `idx_active` (`is_active`),
  
  CONSTRAINT `fk_profiles_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`user_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Multiple profiles per user - each with independent appearance and content';

-- ========================================
-- PHASE 2: MIGRATE EXISTING USER DATA
-- ========================================

-- Migrate existing users to profiles table
-- Each user gets 1 primary profile with their current slug
INSERT INTO `profiles` (
  `user_id`, 
  `slug`, 
  `profile_name`, 
  `profile_title`,
  `bio`,
  `profile_pic_filename`,
  `is_primary`, 
  `created_at`
)
SELECT 
  u.`user_id`,
  u.`page_slug`,
  'Main Profile' AS profile_name,
  ua.`profile_title`,
  ua.`bio`,
  ua.`profile_pic_filename`,
  1 AS is_primary,
  u.`created_at`
FROM `users` u
LEFT JOIN `user_appearance` ua ON u.user_id = ua.user_id
WHERE u.`page_slug` IS NOT NULL AND u.`page_slug` != '';

-- ========================================
-- PHASE 3: UPDATE EXISTING TABLES
-- ========================================

-- 3.1: Add profile_id to links table
ALTER TABLE `links`
ADD COLUMN `profile_id` INT NULL COMMENT 'Which profile this link belongs to'
AFTER `user_id`,
ADD KEY `idx_profile_id` (`profile_id`);

-- Migrate existing links to their user's primary profile
UPDATE `links` l
JOIN `profiles` p ON l.user_id = p.user_id AND p.is_primary = 1
SET l.profile_id = p.profile_id;

-- Make profile_id NOT NULL after migration
ALTER TABLE `links`
MODIFY COLUMN `profile_id` INT NOT NULL;

-- Add foreign key constraint
ALTER TABLE `links`
ADD CONSTRAINT `fk_links_profile`
  FOREIGN KEY (`profile_id`)
  REFERENCES `profiles` (`profile_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;

-- 3.2: Add profile_id to link_categories table
ALTER TABLE `link_categories`
ADD COLUMN `profile_id` INT NULL COMMENT 'Which profile this category belongs to'
AFTER `user_id`,
ADD KEY `idx_profile_id` (`profile_id`);

-- Migrate existing categories to primary profile
UPDATE `link_categories` lc
JOIN `profiles` p ON lc.user_id = p.user_id AND p.is_primary = 1
SET lc.profile_id = p.profile_id;

-- Make profile_id NOT NULL
ALTER TABLE `link_categories`
MODIFY COLUMN `profile_id` INT NOT NULL;

-- Add foreign key
ALTER TABLE `link_categories`
ADD CONSTRAINT `fk_categories_profile`
  FOREIGN KEY (`profile_id`)
  REFERENCES `profiles` (`profile_id`)
  ON DELETE CASCADE;

-- 3.3: Update user_appearance to be profile-specific
ALTER TABLE `user_appearance`
ADD COLUMN `profile_id` INT NULL COMMENT 'Appearance settings for this profile'
AFTER `user_id`,
ADD KEY `idx_profile_id` (`profile_id`);

-- Migrate appearance to primary profiles
UPDATE `user_appearance` ua
JOIN `profiles` p ON ua.user_id = p.user_id AND p.is_primary = 1
SET ua.profile_id = p.profile_id;

-- For appearances without profile, create default
INSERT INTO `user_appearance` (user_id, profile_id)
SELECT p.user_id, p.profile_id
FROM `profiles` p
LEFT JOIN `user_appearance` ua ON p.profile_id = ua.profile_id
WHERE ua.appearance_id IS NULL;

-- Make profile_id NOT NULL
ALTER TABLE `user_appearance`
MODIFY COLUMN `profile_id` INT NOT NULL;

-- Add foreign key
ALTER TABLE `user_appearance`
ADD CONSTRAINT `fk_appearance_profile`
  FOREIGN KEY (`profile_id`)
  REFERENCES `profiles` (`profile_id`)
  ON DELETE CASCADE;

-- Remove old unique constraint on user_id (now can have multiple per user via profiles)
ALTER TABLE `user_appearance`
DROP INDEX IF EXISTS `user_id`,
ADD UNIQUE KEY `unique_profile` (`profile_id`);

-- ========================================
-- PHASE 4: CREATE PROFILE ANALYTICS TABLE
-- ========================================

CREATE TABLE `profile_analytics` (
  `analytics_id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `date` DATE NOT NULL COMMENT 'Analytics date',
  
  -- View metrics
  `page_views` INT DEFAULT 0 COMMENT 'Total profile page views',
  `unique_visitors` INT DEFAULT 0 COMMENT 'Unique visitors (by IP/session)',
  
  -- Click metrics
  `total_clicks` INT DEFAULT 0 COMMENT 'Total link clicks',
  `total_links` INT DEFAULT 0 COMMENT 'Number of active links',
  
  -- Engagement
  `avg_time_on_page` INT DEFAULT 0 COMMENT 'Average seconds on page',
  `bounce_rate` DECIMAL(5,2) DEFAULT 0 COMMENT 'Bounce rate percentage',
  
  -- Metadata
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`analytics_id`),
  UNIQUE KEY `unique_profile_date` (`profile_id`, `date`),
  KEY `idx_profile` (`profile_id`),
  KEY `idx_date` (`date`),
  
  CONSTRAINT `fk_analytics_profile`
    FOREIGN KEY (`profile_id`)
    REFERENCES `profiles` (`profile_id`)
    ON DELETE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Daily analytics metrics per profile';

-- ========================================
-- PHASE 5: CREATE PROFILE ACTIVITY LOG
-- ========================================

CREATE TABLE `profile_activity_log` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `user_id` INT NOT NULL COMMENT 'Who performed the action',
  `action_type` ENUM('created', 'updated', 'deleted', 'cloned', 'activated', 'deactivated') NOT NULL,
  `action_details` TEXT NULL COMMENT 'JSON data about the action',
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`log_id`),
  KEY `idx_profile` (`profile_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`),
  
  CONSTRAINT `fk_activity_profile`
    FOREIGN KEY (`profile_id`)
    REFERENCES `profiles` (`profile_id`)
    ON DELETE CASCADE,
  
  CONSTRAINT `fk_activity_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Activity log for profile management actions';

-- ========================================
-- PHASE 6: UPDATE VIEWS
-- ========================================

-- Drop existing views (will be recreated with profile support)
DROP VIEW IF EXISTS `v_public_page_data`;
DROP VIEW IF EXISTS `v_public_page_data_with_categories`;

-- Recreate v_public_page_data with profile support
CREATE VIEW `v_public_page_data` AS
SELECT 
  p.profile_id,
  p.user_id,
  p.slug AS page_slug,
  p.profile_title,
  p.bio,
  p.profile_pic_filename,
  u.username,
  ua.theme_name,
  ua.gradient_preset,
  ua.profile_layout,
  ua.button_style,
  ua.container_style,
  ua.custom_text_color,
  ua.custom_link_text_color,
  ua.custom_button_color,
  ua.custom_bg_color,
  ua.font_family,
  ua.enable_animations,
  ua.show_profile_border,
  ua.shadow_intensity,
  ua.enable_glass_effect,
  ua.container_max_width,
  ua.container_bg_color,
  ua.container_border_radius,
  ua.container_shadow,
  ua.outer_bg_type,
  ua.outer_bg_color,
  ua.outer_bg_gradient_start,
  ua.outer_bg_gradient_end,
  ua.outer_bg_image,
  ua.bg_image_filename,
  ua.boxed_layout,
  ua.enable_categories
FROM `profiles` p
JOIN `users` u ON p.user_id = u.user_id
LEFT JOIN `user_appearance` ua ON p.profile_id = ua.profile_id
WHERE p.is_active = 1;

-- Recreate v_public_page_data_with_categories
CREATE VIEW `v_public_page_data_with_categories` AS
SELECT 
  v.*,
  lc.category_id,
  lc.category_name,
  lc.category_icon,
  lc.category_color,
  lc.display_order,
  lc.is_expanded
FROM `v_public_page_data` v
LEFT JOIN `link_categories` lc ON v.profile_id = lc.profile_id
ORDER BY lc.display_order ASC;

-- ========================================
-- PHASE 7: ADD SESSION MANAGEMENT
-- ========================================

-- Add active_profile_id to track which profile user is currently managing
ALTER TABLE `users`
ADD COLUMN `active_profile_id` INT NULL COMMENT 'Currently active profile in admin dashboard'
AFTER `page_slug`;

-- Set active_profile_id to primary profile
UPDATE `users` u
JOIN `profiles` p ON u.user_id = p.user_id AND p.is_primary = 1
SET u.active_profile_id = p.profile_id;

-- Add foreign key
ALTER TABLE `users`
ADD CONSTRAINT `fk_users_active_profile`
  FOREIGN KEY (`active_profile_id`)
  REFERENCES `profiles` (`profile_id`)
  ON DELETE SET NULL;

-- ========================================
-- PHASE 8: BACKWARD COMPATIBILITY
-- ========================================

-- Keep users.page_slug for backward compatibility
-- But create trigger to sync with primary profile slug

DELIMITER $$

CREATE TRIGGER `sync_primary_slug_on_update` 
AFTER UPDATE ON `profiles`
FOR EACH ROW
BEGIN
  IF NEW.is_primary = 1 THEN
    UPDATE `users` 
    SET `page_slug` = NEW.slug 
    WHERE `user_id` = NEW.user_id;
  END IF;
END$$

CREATE TRIGGER `sync_primary_slug_on_insert`
AFTER INSERT ON `profiles`
FOR EACH ROW
BEGIN
  IF NEW.is_primary = 1 THEN
    UPDATE `users`
    SET `page_slug` = NEW.slug
    WHERE `user_id` = NEW.user_id;
  END IF;
END$$

DELIMITER ;

-- ========================================
-- VERIFICATION QUERIES
-- ========================================

-- Check profiles created correctly
SELECT 
  COUNT(*) AS total_profiles,
  (SELECT COUNT(*) FROM users) AS total_users,
  COUNT(DISTINCT user_id) AS users_with_profiles
FROM profiles;

-- Check each user has exactly 1 primary profile
SELECT 
  user_id,
  COUNT(*) AS primary_count
FROM profiles
WHERE is_primary = 1
GROUP BY user_id
HAVING primary_count != 1;
-- Should return 0 rows

-- Check all links migrated to profiles
SELECT 
  COUNT(*) AS links_without_profile
FROM links
WHERE profile_id IS NULL;
-- Should return 0

-- Check all categories migrated
SELECT 
  COUNT(*) AS categories_without_profile  
FROM link_categories
WHERE profile_id IS NULL;
-- Should return 0

-- Check all appearances have profile
SELECT 
  COUNT(*) AS appearances_without_profile
FROM user_appearance
WHERE profile_id IS NULL;
-- Should return 0

-- Sample profile data
SELECT 
  p.profile_id,
  p.user_id,
  u.username,
  p.slug,
  p.profile_name,
  p.is_primary,
  COUNT(DISTINCT l.link_id) AS link_count,
  COUNT(DISTINCT lc.category_id) AS category_count
FROM profiles p
JOIN users u ON p.user_id = u.user_id
LEFT JOIN links l ON p.profile_id = l.profile_id
LEFT JOIN link_categories lc ON p.profile_id = lc.profile_id
GROUP BY p.profile_id
ORDER BY u.username, p.is_primary DESC;

-- ========================================
-- ROLLBACK SCRIPT (DANGER!)
-- ========================================
/*
-- Use this ONLY if migration fails and you need to rollback
-- WARNING: This will delete all multi-profile data!

DROP TRIGGER IF EXISTS sync_primary_slug_on_update;
DROP TRIGGER IF EXISTS sync_primary_slug_on_insert;

ALTER TABLE users DROP FOREIGN KEY IF EXISTS fk_users_active_profile;
ALTER TABLE users DROP COLUMN IF EXISTS active_profile_id;

ALTER TABLE user_appearance DROP FOREIGN KEY IF EXISTS fk_appearance_profile;
ALTER TABLE user_appearance DROP KEY IF EXISTS unique_profile;
ALTER TABLE user_appearance DROP COLUMN IF EXISTS profile_id;
ALTER TABLE user_appearance ADD UNIQUE KEY user_id (user_id);

ALTER TABLE link_categories DROP FOREIGN KEY IF EXISTS fk_categories_profile;
ALTER TABLE link_categories DROP COLUMN IF EXISTS profile_id;

ALTER TABLE links DROP FOREIGN KEY IF EXISTS fk_links_profile;
ALTER TABLE links DROP COLUMN IF EXISTS profile_id;

DROP VIEW IF EXISTS v_public_page_data_with_categories;
DROP VIEW IF EXISTS v_public_page_data;

DROP TABLE IF EXISTS profile_activity_log;
DROP TABLE IF EXISTS profile_analytics;
DROP TABLE IF EXISTS profiles;

-- Recreate original views (copy from linkmy_db.sql)
*/

-- ========================================
-- POST-MIGRATION NOTES
-- ========================================
-- 
-- WHAT CHANGED:
-- 1. New "profiles" table - core of multi-profile system
-- 2. All content tables now use profile_id instead of user_id
-- 3. Each profile has independent: slug, appearance, links, categories
-- 4. Users can have max 2 profiles (enforced in application)
-- 5. One profile is marked as "primary" (default)
-- 
-- BACKWARD COMPATIBILITY:
-- - users.page_slug still exists and syncs with primary profile
-- - Triggers keep page_slug updated automatically
-- - Old URLs still work via profile routing
-- 
-- NEXT STEPS:
-- 1. Update admin/dashboard.php - add profile switcher
-- 2. Create admin/profiles.php - profile management page
-- 3. Update profile.php - route by profile slug
-- 4. Update all admin pages to use active_profile_id
-- 5. Test thoroughly with existing users
-- 
-- ========================================
