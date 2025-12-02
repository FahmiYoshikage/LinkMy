-- =====================================================
-- CLEANUP SCRIPT: Remove Old user_slugs System
-- Date: 2025-11-29
-- Purpose: Clean up old user_slugs table that's no longer used
--          after migration to profiles table
-- =====================================================

USE linkmy_db;

-- STEP 1: Verify all users have profiles
SELECT 
    'Users without profiles:' as check_name,
    COUNT(*) as count 
FROM users u 
LEFT JOIN profiles p ON u.user_id = p.user_id 
WHERE p.profile_id IS NULL;

-- STEP 2: Verify all users have a primary profile
SELECT 
    'Users without primary profile:' as check_name,
    COUNT(*) as count 
FROM users u 
LEFT JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1 
WHERE p.profile_id IS NULL;

-- STEP 3: Check if user_slugs table exists
SELECT 
    TABLE_NAME,
    TABLE_ROWS,
    CREATE_TIME
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_slugs';

-- STEP 4: Compare data between user_slugs and profiles (if table exists)
SELECT 
    'Data comparison' as info,
    (SELECT COUNT(*) FROM user_slugs WHERE user_id = 12) as user_slugs_count,
    (SELECT COUNT(*) FROM profiles WHERE user_id = 12) as profiles_count;

-- STEP 5: Drop user_slugs table if it exists (ONLY AFTER VERIFICATION)
-- UNCOMMENT THIS AFTER VERIFYING ABOVE QUERIES
-- DROP TABLE IF EXISTS user_slugs;

-- STEP 6: Verify users.active_profile_id is set correctly
SELECT 
    u.user_id,
    u.username,
    u.page_slug as user_page_slug,
    u.active_profile_id,
    p.profile_id,
    p.slug as profile_slug,
    p.is_primary
FROM users u
LEFT JOIN profiles p ON u.active_profile_id = p.profile_id
WHERE u.user_id IN (1, 7, 8, 9, 10, 11, 12, 13)
ORDER BY u.user_id;

-- STEP 7: Fix users without active_profile_id
UPDATE users u
INNER JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1
SET u.active_profile_id = p.profile_id
WHERE u.active_profile_id IS NULL;

-- STEP 8: Verify fix
SELECT 
    'Users with active_profile_id set' as check_name,
    COUNT(*) as count
FROM users
WHERE active_profile_id IS NOT NULL;

-- =====================================================
-- VERIFICATION COMPLETE
-- Manual Steps After Running This Script:
-- 1. Verify all output looks correct
-- 2. Uncomment DROP TABLE line in STEP 5
-- 3. Update settings.php to remove slug management
-- 4. Test in browser
-- =====================================================
