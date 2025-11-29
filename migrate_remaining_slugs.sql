-- =====================================================
-- MIGRATE OLD USER_SLUGS TO PROFILES
-- Date: 2025-11-29
-- Purpose: Convert remaining slugs in user_slugs to profiles
-- =====================================================

USE linkmy_db;

-- STEP 1: Check what slugs exist in user_slugs but not in profiles
SELECT 
    'Slugs in user_slugs but NOT in profiles' as info,
    us.slug_id,
    us.user_id,
    us.slug,
    us.is_primary,
    us.created_at
FROM user_slugs us
LEFT JOIN profiles p ON us.slug = p.slug AND us.user_id = p.user_id
WHERE p.profile_id IS NULL;

-- STEP 2: Create profiles from user_slugs for user 12
-- This will create "triforce" profile
INSERT INTO profiles (user_id, slug, profile_name, is_primary, is_active, created_at)
SELECT 
    us.user_id,
    us.slug,
    CONCAT(UPPER(LEFT(us.slug, 1)), SUBSTRING(us.slug, 2), ' Profile') as profile_name,
    0 as is_primary, -- Not primary since "fahmi" is already primary
    1 as is_active,
    us.created_at
FROM user_slugs us
LEFT JOIN profiles p ON us.slug = p.slug AND us.user_id = p.user_id  
WHERE us.user_id = 12 
  AND us.slug = 'triforce'
  AND p.profile_id IS NULL;

-- STEP 3: Verify new profile was created
SELECT * FROM profiles WHERE user_id = 12 ORDER BY is_primary DESC, created_at ASC;

-- STEP 4: Now DROP user_slugs table (it's obsolete)
-- UNCOMMENT after verifying above worked
-- DROP TABLE IF EXISTS user_slugs;

-- STEP 5: Final verification
SELECT 
    'Final check' as info,
    u.user_id,
    u.username,
    u.page_slug as users_page_slug,
    u.active_profile_id,
    p.profile_id,
    p.slug as profile_slug,
    p.profile_name,
    p.is_primary
FROM users u
LEFT JOIN profiles p ON u.user_id = p.user_id
WHERE u.user_id = 12
ORDER BY p.is_primary DESC;
