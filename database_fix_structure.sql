-- ========================================
-- FIX DATABASE STRUCTURE FOR LINKTREE FEATURES
-- Run this in phpMyAdmin SQL tab
-- ========================================

-- STEP 1: Fix link_categories table structure
-- Change column names to match code expectations
ALTER TABLE link_categories 
CHANGE COLUMN `order_index` `display_order` INT DEFAULT 0,
CHANGE COLUMN `is_active` `is_expanded` TINYINT(1) DEFAULT 1 COMMENT 'Default expanded state for category';

SELECT '✅ Step 1: Fixed link_categories column names' AS status;

-- STEP 2: Update existing view v_public_page_data to include new columns
DROP VIEW IF EXISTS v_public_page_data;

CREATE VIEW v_public_page_data AS
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
    a.font_family,
    a.custom_bg_color,
    a.custom_button_color,
    a.custom_text_color,
    a.custom_link_text_color,
    a.gradient_preset,
    a.profile_layout,
    a.container_style,        -- NEW
    a.enable_categories,       -- NEW
    a.show_profile_border,
    a.enable_animations,
    a.enable_glass_effect,
    a.shadow_intensity,
    l.link_id,
    l.title AS link_title,
    l.url AS link_url,
    l.icon_class,
    l.click_count,
    l.order_index,
    l.category_id,              -- NEW
    lc.category_name,           -- NEW
    lc.category_icon,           -- NEW
    lc.category_color           -- NEW
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
LEFT JOIN links l ON u.user_id = l.user_id AND l.is_active = 1
LEFT JOIN link_categories lc ON l.category_id = lc.category_id
ORDER BY u.user_id ASC, lc.display_order ASC, l.order_index ASC;

SELECT '✅ Step 2: Updated v_public_page_data view' AS status;

-- STEP 3: Create new view v_public_page_data_with_categories (alias for compatibility)
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

SELECT '✅ Step 3: Created v_public_page_data_with_categories view' AS status;

-- STEP 4: Verify structure
SELECT '========== VERIFICATION ==========' AS '';

-- Check link_categories columns
SELECT 
    COLUMN_NAME, 
    DATA_TYPE,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'link_categories'
  AND COLUMN_NAME IN ('display_order', 'is_expanded');

-- Check appearance columns
SELECT 
    COLUMN_NAME, 
    DATA_TYPE,
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'appearance'
  AND COLUMN_NAME IN ('container_style', 'enable_categories');

-- Check views exist
SELECT 
    TABLE_NAME,
    TABLE_TYPE
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME IN ('v_public_page_data', 'v_public_page_data_with_categories');

SELECT '✅✅✅ ALL FIXES COMPLETED! ✅✅✅' AS result;
SELECT 'Now pull latest code: git pull origin master' AS next_step;
