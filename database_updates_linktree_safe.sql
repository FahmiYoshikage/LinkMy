-- Safe Database Migration for Linktree Features
-- This script checks for existing columns/tables before creating them

-- =======================
-- STEP 1: Backup check
-- =======================
-- IMPORTANT: Make sure you have database backup before running!

-- =======================
-- STEP 2: Update appearance table
-- =======================
-- Check and add container_style column
SELECT 'Checking container_style column...' AS status;

ALTER TABLE appearance 
ADD COLUMN IF NOT EXISTS container_style VARCHAR(20) DEFAULT 'wide' COMMENT 'wide|boxed' AFTER profile_layout;

-- Check and add enable_categories column
SELECT 'Checking enable_categories column...' AS status;

ALTER TABLE appearance 
ADD COLUMN IF NOT EXISTS enable_categories TINYINT(1) DEFAULT 0 COMMENT 'Enable link categories/folders' AFTER container_style;

-- =======================
-- STEP 3: Create link_categories table
-- =======================
SELECT 'Creating link_categories table...' AS status;

CREATE TABLE IF NOT EXISTS link_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    category_icon VARCHAR(50) DEFAULT 'bi-folder',
    category_color VARCHAR(7) DEFAULT '#667eea',
    display_order INT DEFAULT 0,
    is_expanded TINYINT(1) DEFAULT 1 COMMENT 'Default expanded state',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_order (user_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================
-- STEP 4: Add category_id to links table (SAFE)
-- =======================
SELECT 'Checking category_id column in links table...' AS status;

-- MySQL 5.7+ doesn't support ADD COLUMN IF NOT EXISTS in ALTER TABLE
-- So we need to check first
SET @col_exists = (
    SELECT COUNT(*) 
    FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'links' 
    AND COLUMN_NAME = 'category_id'
);

-- Only add if doesn't exist
SET @query = IF(@col_exists = 0,
    'ALTER TABLE links ADD COLUMN category_id INT NULL COMMENT "NULL for uncategorized" AFTER user_id',
    'SELECT "Column category_id already exists" AS info'
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =======================
-- STEP 5: Add foreign key (SAFE)
-- =======================
SELECT 'Checking foreign key constraint...' AS status;

-- Check if FK exists
SET @fk_exists = (
    SELECT COUNT(*) 
    FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
    AND TABLE_NAME = 'links' 
    AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND CONSTRAINT_NAME LIKE '%category%'
);

-- Only add FK if doesn't exist
SET @fk_query = IF(@fk_exists = 0,
    'ALTER TABLE links ADD CONSTRAINT fk_links_category FOREIGN KEY (category_id) REFERENCES link_categories(category_id) ON DELETE SET NULL',
    'SELECT "Foreign key already exists" AS info'
);

PREPARE fk_stmt FROM @fk_query;
EXECUTE fk_stmt;
DEALLOCATE PREPARE fk_stmt;

-- =======================
-- STEP 6: Create/Replace view
-- =======================
SELECT 'Creating view v_public_page_data_with_categories...' AS status;

DROP VIEW IF EXISTS v_public_page_data_with_categories;

CREATE VIEW v_public_page_data_with_categories AS
SELECT 
    u.user_id,
    u.username,
    u.page_slug,
    u.email,
    a.profile_title,
    a.bio,
    a.profile_pic_filename,
    a.bg_image_filename,
    a.theme_name,
    a.button_style,
    a.gradient_preset,
    a.custom_bg_color,
    a.custom_button_color,
    a.custom_text_color,
    a.custom_link_text_color,
    a.profile_layout,
    a.container_style,
    a.enable_categories,
    a.show_profile_border,
    a.enable_animations,
    a.enable_glass_effect,
    a.shadow_intensity,
    l.link_id,
    l.title AS link_title,
    l.url,
    l.icon_class,
    l.click_count,
    l.is_active,
    l.order_index,
    l.category_id,
    c.category_name,
    c.category_icon,
    c.category_color,
    c.is_expanded AS category_expanded
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
LEFT JOIN links l ON u.user_id = l.user_id AND l.is_active = 1
LEFT JOIN link_categories c ON l.category_id = c.category_id
ORDER BY c.display_order ASC, l.order_index ASC;

-- =======================
-- STEP 7: Verification
-- =======================
SELECT 'Verifying installation...' AS status;

-- Check appearance columns
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    COLUMN_DEFAULT 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'appearance' 
AND COLUMN_NAME IN ('container_style', 'enable_categories');

-- Check links column
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'links' 
AND COLUMN_NAME = 'category_id';

-- Check link_categories table
SELECT COUNT(*) as category_table_exists 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'link_categories';

SELECT '✅ Migration completed successfully!' AS result;
