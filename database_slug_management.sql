-- ========================================
-- LINKMY - SLUG MANAGEMENT FEATURE
-- Database Migration Script
-- ========================================
-- This migration adds support for:
-- 1. Multiple slugs per user (max 2 for free tier)
-- 2. Slug change feature with 30-day cooldown
-- 3. Primary slug designation
-- ========================================

-- Step 1: Add cooldown tracking for slug changes
-- This column tracks the last time a user changed their primary slug
ALTER TABLE `users` 
ADD COLUMN `last_slug_change_at` DATETIME NULL DEFAULT NULL 
COMMENT 'Last time user changed their primary slug (30-day cooldown)';

-- Step 2: Create user_slugs table for multiple slug support
-- This table allows users to have multiple slugs (max 2) all pointing to same profile
CREATE TABLE `user_slugs` (
  `slug_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `slug` VARCHAR(50) NOT NULL,
  `is_primary` TINYINT(1) DEFAULT 0 COMMENT '1 = primary slug, 0 = alias slug',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`slug_id`),
  UNIQUE KEY `unique_slug` (`slug`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_user_primary` (`user_id`, `is_primary`),
  CONSTRAINT `fk_user_slugs_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`user_id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci 
COMMENT='Multiple slugs per user - all point to same profile';

-- Step 3: Migrate existing page_slug data to user_slugs table
-- All existing slugs will be marked as primary (is_primary = 1)
INSERT INTO `user_slugs` (`user_id`, `slug`, `is_primary`, `created_at`)
SELECT 
  `user_id`, 
  `page_slug`, 
  1 AS `is_primary`,
  `created_at`
FROM `users`
WHERE `page_slug` IS NOT NULL AND `page_slug` != '';

-- Step 4: Verify migration
-- This query should return the count of migrated slugs
-- It should match the total number of users
SELECT 
  COUNT(*) AS total_migrated_slugs,
  (SELECT COUNT(*) FROM `users`) AS total_users
FROM `user_slugs`;

-- Step 5: Add OTP verification table extension (if needed)
-- The existing email_verifications table can be reused for slug change OTP
-- But let's add a new column to track the purpose
ALTER TABLE `email_verifications`
ADD COLUMN `verification_type` ENUM('email', 'slug_change') DEFAULT 'email'
COMMENT 'Type of verification: email registration or slug change';

-- ========================================
-- ROLLBACK SCRIPT (Use with caution!)
-- ========================================
-- Uncomment the following lines to rollback this migration
/*
DROP TABLE IF EXISTS `user_slugs`;
ALTER TABLE `users` DROP COLUMN `last_slug_change_at`;
ALTER TABLE `email_verifications` DROP COLUMN `verification_type`;
*/

-- ========================================
-- VERIFICATION QUERIES
-- ========================================
-- Run these queries to verify the migration

-- Check all users have their slugs migrated
SELECT 
  u.user_id,
  u.username,
  u.page_slug AS old_slug,
  us.slug AS new_slug,
  us.is_primary,
  us.created_at
FROM `users` u
LEFT JOIN `user_slugs` us ON u.user_id = us.user_id
ORDER BY u.user_id;

-- Check for any users without slugs in user_slugs
SELECT 
  u.user_id,
  u.username,
  u.page_slug
FROM `users` u
LEFT JOIN `user_slugs` us ON u.user_id = us.user_id
WHERE us.slug_id IS NULL;

-- Count slugs per user (should all be 1 after initial migration)
SELECT 
  user_id,
  COUNT(*) AS slug_count
FROM `user_slugs`
GROUP BY user_id
ORDER BY slug_count DESC;

-- ========================================
-- USAGE NOTES
-- ========================================
-- 1. All existing page_slug values are migrated to user_slugs as primary
-- 2. users.page_slug column is kept for backward compatibility
-- 3. Application should update users.page_slug when primary slug changes
-- 4. Max 2 slugs per user enforced at application level
-- 5. 30-day cooldown enforced by last_slug_change_at column
-- 6. OTP verification uses existing email_verifications table
-- ========================================
