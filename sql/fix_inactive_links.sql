-- Quick Fix: Activate all inactive links
-- Purpose: Fix existing links that were created with is_active=0 (before bug fix)
-- Date: December 3, 2025

USE linkmy_db;

-- 1. Show current inactive links
SELECT 
    l.id, 
    l.profile_id, 
    p.slug as profile_slug,
    l.title, 
    l.is_active,
    l.created_at
FROM links l
LEFT JOIN profiles p ON l.profile_id = p.id
WHERE l.is_active = 0 OR l.is_active IS NULL
ORDER BY l.created_at DESC;

-- 2. Activate all inactive links
UPDATE links 
SET is_active = 1 
WHERE is_active = 0 OR is_active IS NULL;

-- 3. Verify the fix
SELECT 
    'Total links activated' as status,
    COUNT(*) as count
FROM links
WHERE is_active = 1;

SELECT 'All links activated successfully' AS result;
