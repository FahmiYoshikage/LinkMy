-- =====================================================
-- LinkMy Database ROLLBACK Script
-- Use this if migration fails or you want to revert
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- Drop new tables
DROP TABLE IF EXISTS theme_boxed;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS clicks;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS links;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS users;

DROP VIEW IF EXISTS v_profile_stats;
DROP VIEW IF EXISTS v_public_profiles;

DROP PROCEDURE IF EXISTS sp_get_user_profiles;
DROP PROCEDURE IF EXISTS sp_get_profile_full;
DROP PROCEDURE IF EXISTS sp_increment_click;

-- Restore original tables
RENAME TABLE `backup_users` TO `old_users`;
RENAME TABLE `backup_profiles` TO `old_profiles`;
RENAME TABLE `backup_links` TO `old_links`;
RENAME TABLE `backup_user_appearance` TO `old_user_appearance`;
RENAME TABLE `backup_sessions` TO `old_sessions`;
RENAME TABLE `backup_password_resets` TO `old_password_resets`;
RENAME TABLE `backup_email_verifications` TO `old_email_verifications`;
RENAME TABLE `backup_link_analytics` TO `old_link_analytics`;
RENAME TABLE `backup_profile_analytics` TO `old_profile_analytics`;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Database rolled back to backup_* tables' as Status;
