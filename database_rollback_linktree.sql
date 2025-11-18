-- ROLLBACK Script - Undo Linktree Features Migration
-- Use this if you encounter errors and need to revert changes

-- WARNING: This will delete all category data!
-- Make sure to backup first!

-- 1. Drop view
DROP VIEW IF EXISTS v_public_page_data_with_categories;

-- 2. Remove foreign key from links
ALTER TABLE links DROP FOREIGN KEY IF EXISTS fk_links_category;
ALTER TABLE links DROP FOREIGN KEY IF EXISTS links_ibfk_category;

-- 3. Remove category_id column from links
ALTER TABLE links DROP COLUMN IF EXISTS category_id;

-- 4. Drop link_categories table (WARNING: deletes all categories!)
DROP TABLE IF EXISTS link_categories;

-- 5. Remove appearance columns
ALTER TABLE appearance DROP COLUMN IF EXISTS enable_categories;
ALTER TABLE appearance DROP COLUMN IF EXISTS container_style;

-- Verification
SELECT 'Rollback completed!' AS status;

-- Check if columns removed
SELECT COUNT(*) as remaining_columns
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND ((TABLE_NAME = 'appearance' AND COLUMN_NAME IN ('container_style', 'enable_categories'))
     OR (TABLE_NAME = 'links' AND COLUMN_NAME = 'category_id'));

-- Should return 0 if successful
