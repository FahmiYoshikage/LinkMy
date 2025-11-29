-- =====================================================
-- COMPREHENSIVE VERIFICATION SCRIPT
-- Multi-Profile System Health Check
-- Date: 2025-11-29
-- =====================================================

USE linkmy_db;

-- ======================================
-- SECTION 1: TABLE STRUCTURE VERIFICATION
-- ======================================
SELECT '=== SECTION 1: Table Structure ===' as '';

SELECT 
    'profiles table exists' as check_name,
    COUNT(*) as result
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'linkmy_db' AND TABLE_NAME = 'profiles';

SELECT 
    'user_appearance has profile_id' as check_name,
    COUNT(*) as result
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'user_appearance' 
  AND COLUMN_NAME = 'profile_id';

SELECT 
    'link_categories has profile_id' as check_name,
    COUNT(*) as result
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'link_categories' 
  AND COLUMN_NAME = 'profile_id';

SELECT 
    'links has profile_id' as check_name,
    COUNT(*) as result
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'linkmy_db' 
  AND TABLE_NAME = 'links' 
  AND COLUMN_NAME = 'profile_id';

-- Check if old user_slugs table still exists
SELECT 
    'user_slugs table (OLD - should NOT exist)' as check_name,
    COUNT(*) as result
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'linkmy_db' AND TABLE_NAME = 'user_slugs';

-- ======================================
-- SECTION 2: DATA INTEGRITY CHECKS
-- ======================================
SELECT '=== SECTION 2: Data Integrity ===' as '';

-- Check all users have profiles
SELECT 
    'Users WITHOUT profiles (should be 0)' as check_name,
    COUNT(*) as result
FROM users u
LEFT JOIN profiles p ON u.user_id = p.user_id
WHERE p.profile_id IS NULL;

-- Check all users have a primary profile
SELECT 
    'Users WITHOUT primary profile (should be 0)' as check_name,
    COUNT(*) as result
FROM users u
LEFT JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1
WHERE p.profile_id IS NULL;

-- Check users.active_profile_id is set
SELECT 
    'Users WITHOUT active_profile_id set (should be 0)' as check_name,
    COUNT(*) as result
FROM users
WHERE active_profile_id IS NULL;

-- Check users.active_profile_id points to valid profile
SELECT 
    'Users with INVALID active_profile_id (should be 0)' as check_name,
    COUNT(*) as result
FROM users u
LEFT JOIN profiles p ON u.active_profile_id = p.profile_id
WHERE u.active_profile_id IS NOT NULL AND p.profile_id IS NULL;

-- ======================================
-- SECTION 3: USER 12 (FAHMI) SPECIFIC CHECKS
-- ======================================
SELECT '=== SECTION 3: User 12 (Fahmi) Data ===' as '';

SELECT 
    u.user_id,
    u.username,
    u.page_slug,
    u.active_profile_id,
    p.profile_id,
    p.slug as profile_slug,
    p.profile_name,
    p.is_primary
FROM users u
LEFT JOIN profiles p ON u.user_id = p.user_id
WHERE u.user_id = 12
ORDER BY p.is_primary DESC;

-- Count profiles for user 12
SELECT 
    'User 12 profile count' as check_name,
    COUNT(*) as result
FROM profiles
WHERE user_id = 12;

-- Check categories for user 12
SELECT 
    'User 12 categories count' as check_name,
    COUNT(*) as result
FROM link_categories
WHERE user_id = 12;

-- Check categories with profile_id
SELECT 
    c.category_id,
    c.user_id,
    c.profile_id,
    c.category_name,
    p.slug as profile_slug
FROM link_categories c
LEFT JOIN profiles p ON c.profile_id = p.profile_id
WHERE c.user_id = 12
ORDER BY c.display_order;

-- Check links for user 12
SELECT 
    'User 12 links count' as check_name,
    COUNT(*) as result
FROM links
WHERE user_id = 12;

-- Check appearance for user 12
SELECT 
    a.appearance_id,
    a.user_id,
    a.profile_id,
    a.profile_title,
    p.slug as profile_slug
FROM user_appearance a
LEFT JOIN profiles p ON a.profile_id = p.profile_id
WHERE a.user_id = 12;

-- ======================================
-- SECTION 4: TRIGGER VERIFICATION
-- ======================================
SELECT '=== SECTION 4: Triggers ===' as '';

SELECT 
    TRIGGER_NAME,
    EVENT_MANIPULATION,
    EVENT_OBJECT_TABLE,
    ACTION_TIMING
FROM information_schema.TRIGGERS
WHERE TRIGGER_SCHEMA = 'linkmy_db'
  AND EVENT_OBJECT_TABLE = 'profiles';

-- ======================================
-- SECTION 5: VIEW VERIFICATION
-- ======================================
SELECT '=== SECTION 5: Views ===' as '';

SELECT 
    TABLE_NAME
FROM information_schema.VIEWS
WHERE TABLE_SCHEMA = 'linkmy_db'
  AND TABLE_NAME LIKE '%profile%';

-- ======================================
-- SECTION 6: SESSION DATA CHECK
-- ======================================
SELECT '=== SECTION 6: Active Sessions ===' as '';

SELECT 
    session_id,
    SUBSTRING(session_data, 1, 200) as session_preview,
    expire_time,
    FROM_UNIXTIME(expire_time) as expire_datetime
FROM sessions
WHERE session_data LIKE '%active_profile_id%'
  AND expire_time > UNIX_TIMESTAMP()
ORDER BY expire_time DESC
LIMIT 5;

-- ======================================
-- SUMMARY
-- ======================================
SELECT '=== SUMMARY ===' as '';

SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM profiles) as total_profiles,
    (SELECT COUNT(*) FROM link_categories) as total_categories,
    (SELECT COUNT(*) FROM links) as total_links,
    (SELECT COUNT(DISTINCT user_id) FROM profiles GROUP BY user_id HAVING COUNT(*) > 1) as users_with_multiple_profiles;
