-- Fix Bug: Link dengan profile_id salah
-- Purpose: Update links yang punya profile_id tidak exist ke profile yang benar
-- Date: December 4, 2025

USE linkmy_db;

-- 1. Show problematic links (links with non-existent profile_id)
SELECT 
    l.id as link_id,
    l.profile_id as current_profile_id,
    l.title,
    l.url,
    p.id as valid_profile_id,
    p.slug
FROM links l
LEFT JOIN profiles p ON l.profile_id = p.id
WHERE p.id IS NULL
ORDER BY l.id DESC;

-- 2. For user classtriforce (assuming user_id known), fix orphaned links
-- Find user's valid profile
SET @classtriforce_user_id = (SELECT id FROM users WHERE email = 'classtriforce@gmail.com' LIMIT 1);
SET @valid_profile_id = (SELECT id FROM profiles WHERE user_id = @classtriforce_user_id ORDER BY created_at DESC LIMIT 1);

-- Show what will be fixed
SELECT 
    CONCAT('Link ID ', l.id, ' (', l.title, ') will be moved from profile_id ', l.profile_id, ' to ', @valid_profile_id) as action
FROM links l
LEFT JOIN profiles p ON l.profile_id = p.id
WHERE p.id IS NULL AND @valid_profile_id IS NOT NULL;

-- 3. Update orphaned links to valid profile
UPDATE links l
LEFT JOIN profiles p ON l.profile_id = p.id
SET l.profile_id = @valid_profile_id
WHERE p.id IS NULL AND @valid_profile_id IS NOT NULL;

-- 4. Verify fix
SELECT 
    l.id as link_id,
    l.profile_id,
    p.slug as profile_slug,
    l.title,
    l.is_active
FROM links l
INNER JOIN profiles p ON l.profile_id = p.id
WHERE p.user_id = @classtriforce_user_id
ORDER BY l.created_at DESC;

SELECT 'Orphaned links fixed' AS result;
