-- ============================================
-- LinkMy Database - SIMPLIFIED & CLEAN STRUCTURE
-- Version: 2.0
-- Date: 2025-12-01
-- ============================================
-- 
-- DESIGN PRINCIPLES:
-- 1. Minimal complexity - Easy to understand
-- 2. Clear relationships - No ambiguous foreign keys
-- 3. Consistent naming - snake_case for all
-- 4. Essential fields only - No redundant data
-- 5. Proper indexes - Performance optimized
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- CORE TABLES
-- ============================================

-- --------------------------------------------
-- 1. USERS - Main user accounts
-- --------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX `idx_email` (`email`),
  INDEX `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 2. PROFILES - User link pages (max 2 per user)
-- --------------------------------------------
CREATE TABLE `profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) NULL,
  `bio` TEXT NULL,
  `avatar` VARCHAR(255) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Constraint: Max 2 profiles per user (enforced in application layer)

-- --------------------------------------------
-- 3. LINKS - User's links in profiles
-- --------------------------------------------
CREATE TABLE `links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-link-45deg',
  `position` INT DEFAULT 0,
  `clicks` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`),
  INDEX `idx_position` (`position`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 4. THEMES - Profile appearance settings
-- --------------------------------------------
CREATE TABLE `themes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL UNIQUE,
  `bg_type` ENUM('gradient', 'color', 'image') DEFAULT 'gradient',
  `bg_value` VARCHAR(255) DEFAULT 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  `button_style` ENUM('fill', 'outline', 'soft') DEFAULT 'fill',
  `button_color` VARCHAR(7) DEFAULT '#667eea',
  `text_color` VARCHAR(7) DEFAULT '#ffffff',
  `font` VARCHAR(50) DEFAULT 'Inter',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- ANALYTICS TABLES (Simplified)
-- ============================================

-- --------------------------------------------
-- 5. CLICKS - Link click tracking (simplified)
-- --------------------------------------------
CREATE TABLE `clicks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `link_id` INT UNSIGNED NOT NULL,
  `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ip` VARCHAR(45) NULL,
  `country` VARCHAR(50) NULL,
  
  FOREIGN KEY (`link_id`) REFERENCES `links`(`id`) ON DELETE CASCADE,
  INDEX `idx_link_id` (`link_id`),
  INDEX `idx_clicked_at` (`clicked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- AUTHENTICATION TABLES
-- ============================================

-- --------------------------------------------
-- 6. SESSIONS - User sessions
-- --------------------------------------------
CREATE TABLE `sessions` (
  `id` VARCHAR(128) NOT NULL PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `data` TEXT NULL,
  `expires_at` INT UNSIGNED NOT NULL,
  
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 7. PASSWORD_RESETS - Password reset tokens
-- --------------------------------------------
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  
  INDEX `idx_token` (`token`),
  INDEX `idx_email` (`email`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------
-- 8. EMAIL_VERIFICATIONS - Email verification codes
-- --------------------------------------------
CREATE TABLE `email_verifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  
  INDEX `idx_email` (`email`),
  INDEX `idx_code` (`code`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DEFAULT DATA
-- ============================================

-- Default gradient presets
INSERT INTO `gradient_presets` (`name`, `css`) VALUES
('Purple Dream', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'),
('Ocean Blue', 'linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)'),
('Sunset Orange', 'linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)'),
('Fresh Mint', 'linear-gradient(135deg, #00b09b 0%, #96c93d 100%)');

-- ============================================
-- STORED PROCEDURES (Helper Functions)
-- ============================================

-- Get profile with stats
DELIMITER $$
CREATE PROCEDURE `get_profile_stats`(IN profile_slug VARCHAR(50))
BEGIN
  SELECT 
    p.*,
    COUNT(DISTINCT l.id) as link_count,
    COALESCE(SUM(l.clicks), 0) as total_clicks
  FROM profiles p
  LEFT JOIN links l ON p.id = l.profile_id
  WHERE p.slug = profile_slug
  GROUP BY p.id;
END$$
DELIMITER ;

-- Get user's profiles
DELIMITER $$
CREATE PROCEDURE `get_user_profiles`(IN user_id_param INT)
BEGIN
  SELECT 
    p.id,
    p.slug,
    p.name,
    p.title,
    p.is_active,
    p.created_at,
    COUNT(DISTINCT l.id) as link_count,
    COALESCE(SUM(l.clicks), 0) as total_clicks
  FROM profiles p
  LEFT JOIN links l ON p.id = l.profile_id
  WHERE p.user_id = user_id_param
  GROUP BY p.id
  ORDER BY p.created_at ASC;
END$$
DELIMITER ;

-- ============================================
-- VIEWS (For Easy Querying)
-- ============================================

-- Profile summary view
CREATE VIEW `profile_summary` AS
SELECT 
  p.id,
  p.user_id,
  p.slug,
  p.name,
  p.title,
  p.avatar,
  p.is_active,
  u.username,
  u.email,
  COUNT(DISTINCT l.id) as link_count,
  COALESCE(SUM(l.clicks), 0) as total_clicks,
  p.created_at
FROM profiles p
JOIN users u ON p.user_id = u.id
LEFT JOIN links l ON p.id = l.profile_id
GROUP BY p.id;

-- ============================================
-- MIGRATION SCRIPT (From Old to New)
-- ============================================

-- Run this AFTER creating new structure to migrate data
/*
INSERT INTO users (id, username, email, password, is_verified, created_at)
SELECT user_id, username, email, password_hash, (email_verified_at IS NOT NULL), created_at
FROM old_users;

INSERT INTO profiles (id, user_id, slug, name, title, bio, avatar, is_active, created_at)
SELECT profile_id, user_id, slug, profile_name, profile_title, bio, profile_pic_filename, is_active, created_at
FROM old_profiles;

INSERT INTO links (id, profile_id, title, url, icon, position, clicks, is_active, created_at)
SELECT link_id, profile_id, title, url, icon_class, order_index, click_count, is_active, created_at
FROM old_links;

-- Migrate themes
INSERT INTO themes (profile_id, bg_type, bg_value, button_style, button_color, text_color, font)
SELECT 
  profile_id,
  CASE 
    WHEN background_type = 'gradient' THEN 'gradient'
    WHEN background_type = 'image' THEN 'image'
    ELSE 'color'
  END,
  COALESCE(background_value, gradient_css, background_color),
  button_style,
  button_color,
  text_color,
  font_family
FROM old_user_appearance;
*/

-- ============================================
-- PERFORMANCE OPTIMIZATION
-- ============================================

-- Composite indexes for common queries
CREATE INDEX idx_profile_links ON links(profile_id, is_active);
CREATE INDEX idx_profile_user ON profiles(user_id, is_active);

-- ============================================
-- CLEANUP OLD UNUSED TABLES (Optional)
-- ============================================
/*
DROP TABLE IF EXISTS link_analytics;
DROP TABLE IF EXISTS profile_activity_log;
DROP TABLE IF EXISTS profile_analytics;
DROP TABLE IF EXISTS link_categories;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS social_icons;
*/

-- ============================================
-- SUMMARY OF SIMPLIFICATIONS
-- ============================================
/*
OLD STRUCTURE (Complex):
- 15+ tables
- Multiple foreign keys
- Confusing naming (user_id vs profile_id)
- Redundant data (users.page_slug + profiles.slug)
- Over-engineered (multiple analytics tables)
- is_primary flag causing confusion

NEW STRUCTURE (Simple):
- 8 core tables only
- Clear relationships
- Consistent naming
- No redundant data
- Single analytics table
- No is_primary - use application logic

KEY IMPROVEMENTS:
1. Removed is_primary flag → One source of truth
2. Simplified analytics → One clicks table instead of 3
3. Removed categories → Can add later if needed
4. Consistent IDs → All use 'id', no 'link_id', 'profile_id' confusion
5. Clear foreign keys → Easy to understand relationships
6. Added views → Easy to query common data
7. Added procedures → Reusable logic

BREAKING CHANGES:
- Need to update all queries in PHP code
- Need migration script to move data
- Need to update foreign key references
*/
