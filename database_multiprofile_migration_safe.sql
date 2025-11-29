-- ========================================
-- LINKMY - MULTI-PROFILE SYSTEM
-- SAFE Migration Script v2.1
-- ========================================
-- SAFE MIGRATION: Checks if tables exist before creating
-- ========================================

USE linkmy_db;

-- ========================================
-- STEP 1: CREATE PROFILES TABLE (IF NOT EXISTS)
-- ========================================

CREATE TABLE IF NOT EXISTS `profiles` (
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
-- STEP 2: MIGRATE EXISTING USER DATA
-- ========================================

-- Check if profiles table is empty before migrating
INSERT INTO `profiles` (user_id, slug, profile_name, profile_title, bio, profile_pic_filename, is_primary, is_active)
SELECT 
    u.user_id,
    u.page_slug AS slug,
    CONCAT(u.username, ' - Main Profile') AS profile_name,
    u.profile_title,
    u.bio,
    COALESCE(a.profile_pic_filename, 'default-avatar.png') AS profile_pic_filename,
    1 AS is_primary,
    1 AS is_active
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
WHERE NOT EXISTS (
    SELECT 1 FROM profiles p WHERE p.user_id = u.user_id AND p.is_primary = 1
)
ON DUPLICATE KEY UPDATE 
    profile_id = profile_id; -- Do nothing if already exists

-- ========================================
-- STEP 3: RENAME appearance TO user_appearance
-- ========================================

-- Check if table needs to be renamed
SET @table_exists = 0;
SELECT COUNT(*) INTO @table_exists 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'appearance';

SET @sql = IF(@table_exists > 0,
    'RENAME TABLE `appearance` TO `user_appearance`',
    'SELECT "Table appearance does not exist or already renamed" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- STEP 4: ADD profile_id TO EXISTING TABLES
-- ========================================

-- Add profile_id to links table (if not exists)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'links' 
  AND COLUMN_NAME = 'profile_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `links` ADD COLUMN `profile_id` INT NULL AFTER `user_id`, ADD KEY `idx_profile_id` (`profile_id`)',
    'SELECT "Column profile_id already exists in links" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add profile_id to link_categories table (if not exists)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'link_categories' 
  AND COLUMN_NAME = 'profile_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `link_categories` ADD COLUMN `profile_id` INT NULL AFTER `user_id`, ADD KEY `idx_profile_id` (`profile_id`)',
    'SELECT "Column profile_id already exists in link_categories" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add profile_id to user_appearance table (if not exists)
SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_appearance' 
  AND COLUMN_NAME = 'profile_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `user_appearance` ADD COLUMN `profile_id` INT NULL AFTER `user_id`, ADD KEY `idx_profile_id` (`profile_id`)',
    'SELECT "Column profile_id already exists in user_appearance" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- STEP 5: POPULATE profile_id VALUES
-- ========================================

-- Update links with profile_id from primary profile
UPDATE links l
INNER JOIN profiles p ON l.user_id = p.user_id AND p.is_primary = 1
SET l.profile_id = p.profile_id
WHERE l.profile_id IS NULL;

-- Update link_categories with profile_id from primary profile
UPDATE link_categories lc
INNER JOIN profiles p ON lc.user_id = p.user_id AND p.is_primary = 1
SET lc.profile_id = p.profile_id
WHERE lc.profile_id IS NULL;

-- Update user_appearance with profile_id from primary profile
UPDATE user_appearance ua
INNER JOIN profiles p ON ua.user_id = p.user_id AND p.is_primary = 1
SET ua.profile_id = p.profile_id
WHERE ua.profile_id IS NULL;

-- ========================================
-- STEP 6: ADD FOREIGN KEY CONSTRAINTS
-- ========================================

-- Check and add FK for links
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'links' 
  AND CONSTRAINT_NAME = 'fk_links_profile';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `links` ADD CONSTRAINT `fk_links_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "FK fk_links_profile already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add FK for link_categories
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'link_categories' 
  AND CONSTRAINT_NAME = 'fk_link_categories_profile';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `link_categories` ADD CONSTRAINT `fk_link_categories_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "FK fk_link_categories_profile already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add FK for user_appearance
SET @fk_exists = 0;
SELECT COUNT(*) INTO @fk_exists 
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_appearance' 
  AND CONSTRAINT_NAME = 'fk_user_appearance_profile';

SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE `user_appearance` ADD CONSTRAINT `fk_user_appearance_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`profile_id`) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT "FK fk_user_appearance_profile already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- STEP 7: UPDATE UNIQUE CONSTRAINTS
-- ========================================

-- Drop old unique constraint on user_appearance if exists
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_appearance' 
  AND INDEX_NAME = 'user_id';

SET @sql = IF(@idx_exists > 0,
    'ALTER TABLE `user_appearance` DROP INDEX `user_id`',
    'SELECT "Index user_id does not exist in user_appearance" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add new unique constraint on profile_id
SET @idx_exists = 0;
SELECT COUNT(*) INTO @idx_exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_appearance' 
  AND INDEX_NAME = 'unique_profile_id';

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `user_appearance` ADD UNIQUE KEY `unique_profile_id` (`profile_id`)',
    'SELECT "Index unique_profile_id already exists" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ========================================
-- STEP 8: ADD active_profile_id TO USERS TABLE
-- ========================================

SET @col_exists = 0;
SELECT COUNT(*) INTO @col_exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'active_profile_id';

SET @sql = IF(@col_exists = 0,
    'ALTER TABLE `users` ADD COLUMN `active_profile_id` INT NULL AFTER `page_slug`, ADD KEY `idx_active_profile` (`active_profile_id`)',
    'SELECT "Column active_profile_id already exists in users" AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Set active_profile_id to primary profile
UPDATE users u
INNER JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1
SET u.active_profile_id = p.profile_id
WHERE u.active_profile_id IS NULL;

-- ========================================
-- STEP 9: CREATE PROFILE ANALYTICS TABLE
-- ========================================

CREATE TABLE IF NOT EXISTS `profile_analytics` (
  `analytics_id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `total_views` INT DEFAULT 0 COMMENT 'Total profile page views',
  `total_clicks` INT DEFAULT 0 COMMENT 'Total link clicks',
  `unique_visitors` INT DEFAULT 0 COMMENT 'Unique IP addresses',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`analytics_id`),
  UNIQUE KEY `unique_profile_date` (`profile_id`, `date`),
  KEY `idx_date` (`date`),
  
  CONSTRAINT `fk_profile_analytics` 
    FOREIGN KEY (`profile_id`) 
    REFERENCES `profiles` (`profile_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Daily analytics per profile';

-- ========================================
-- STEP 10: CREATE PROFILE ACTIVITY LOG
-- ========================================

CREATE TABLE IF NOT EXISTS `profile_activity_log` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `profile_id` INT NOT NULL,
  `action_type` VARCHAR(50) NOT NULL COMMENT 'created, updated, cloned, deleted',
  `description` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`log_id`),
  KEY `idx_profile_id` (`profile_id`),
  KEY `idx_action_type` (`action_type`),
  KEY `idx_created_at` (`created_at`),
  
  CONSTRAINT `fk_activity_profile` 
    FOREIGN KEY (`profile_id`) 
    REFERENCES `profiles` (`profile_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Audit log for profile activities';

-- ========================================
-- STEP 11: UPDATE VIEWS
-- ========================================

DROP VIEW IF EXISTS `v_public_page_data`;

CREATE VIEW `v_public_page_data` AS
SELECT 
    p.profile_id,
    p.slug AS page_slug,
    p.profile_title,
    p.bio,
    p.profile_pic_filename,
    u.user_id,
    u.username,
    u.email,
    u.is_premium,
    ua.theme_name,
    ua.button_style,
    ua.bg_image_filename,
    ua.gradient_preset,
    ua.custom_bg_color,
    ua.custom_button_color,
    ua.custom_text_color,
    ua.custom_link_text_color,
    ua.profile_layout,
    ua.container_style,
    ua.enable_categories,
    ua.show_profile_border,
    ua.enable_animations,
    ua.enable_glass_effect,
    ua.shadow_intensity,
    ua.boxed_layout,
    ua.outer_bg_type,
    ua.outer_bg_color,
    ua.outer_bg_gradient_start,
    ua.outer_bg_gradient_end,
    ua.container_max_width,
    ua.container_border_radius,
    ua.container_shadow
FROM profiles p
INNER JOIN users u ON p.user_id = u.user_id
LEFT JOIN user_appearance ua ON p.profile_id = ua.profile_id
WHERE p.is_active = 1;

DROP VIEW IF EXISTS `v_public_page_data_with_categories`;

CREATE VIEW `v_public_page_data_with_categories` AS
SELECT 
    vpd.*,
    l.link_id,
    l.title AS link_title,
    l.url AS link_url,
    l.icon_class AS link_icon,
    l.is_active AS link_is_active,
    l.order_index AS link_order,
    l.click_count AS link_clicks,
    lc.category_id,
    lc.category_name,
    lc.category_icon,
    lc.category_color,
    lc.display_order AS category_order
FROM v_public_page_data vpd
LEFT JOIN links l ON vpd.profile_id = l.profile_id AND l.is_active = 1
LEFT JOIN link_categories lc ON l.category_id = lc.category_id
ORDER BY lc.display_order ASC, l.order_index ASC;

-- ========================================
-- STEP 12: CREATE TRIGGERS FOR SLUG SYNC
-- ========================================

DROP TRIGGER IF EXISTS `sync_primary_slug_on_update`;

DELIMITER //
CREATE TRIGGER `sync_primary_slug_on_update`
AFTER UPDATE ON `profiles`
FOR EACH ROW
BEGIN
    IF NEW.is_primary = 1 AND NEW.slug != OLD.slug THEN
        UPDATE users SET page_slug = NEW.slug WHERE user_id = NEW.user_id;
    END IF;
END//
DELIMITER ;

DROP TRIGGER IF EXISTS `sync_primary_slug_on_insert`;

DELIMITER //
CREATE TRIGGER `sync_primary_slug_on_insert`
AFTER INSERT ON `profiles`
FOR EACH ROW
BEGIN
    IF NEW.is_primary = 1 THEN
        UPDATE users SET page_slug = NEW.slug WHERE user_id = NEW.user_id;
    END IF;
END//
DELIMITER ;

-- ========================================
-- MIGRATION COMPLETE!
-- ========================================

SELECT 'Multi-profile migration completed successfully!' AS status;
SELECT COUNT(*) AS total_profiles FROM profiles;
SELECT COUNT(*) AS total_users FROM users;
