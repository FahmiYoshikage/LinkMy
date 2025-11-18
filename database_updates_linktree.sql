-- Database Updates untuk Linktree Style Layout & Categories
-- Run this SQL in your MySQL database

-- 1. Add new layout option to appearance table
ALTER TABLE appearance 
ADD COLUMN container_style VARCHAR(20) DEFAULT 'wide' COMMENT 'wide|boxed' AFTER profile_layout,
ADD COLUMN enable_categories TINYINT(1) DEFAULT 0 COMMENT 'Enable link categories/folders' AFTER container_style;

-- 2. Create link_categories table
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

-- 3. Add category_id to links table (SAFE - checks if exists)
SET @exist := (SELECT COUNT(*) FROM information_schema.COLUMNS 
               WHERE TABLE_SCHEMA = DATABASE() 
               AND TABLE_NAME = 'links' 
               AND COLUMN_NAME = 'category_id');

SET @sqlstmt := IF(@exist = 0, 
    'ALTER TABLE links ADD COLUMN category_id INT NULL COMMENT ''NULL for uncategorized'' AFTER user_id',
    'SELECT ''Column category_id already exists'' AS message');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;

-- Add foreign key if not exists
SET @fk_exist := (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
                  WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'links' 
                  AND CONSTRAINT_NAME = 'links_ibfk_category');

SET @fk_stmt := IF(@fk_exist = 0,
    'ALTER TABLE links ADD CONSTRAINT links_ibfk_category FOREIGN KEY (category_id) REFERENCES link_categories(category_id) ON DELETE SET NULL',
    'SELECT ''Foreign key already exists'' AS message');
PREPARE fk_stmt FROM @fk_stmt;
EXECUTE fk_stmt;

-- 4. Create view for profile with categories
CREATE OR REPLACE VIEW v_public_page_data_with_categories AS
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

-- 5. Insert sample categories for existing users (optional)
-- Uncomment if you want default categories
/*
INSERT INTO link_categories (user_id, category_name, category_icon, category_color, display_order)
SELECT 
    user_id,
    'Social Media',
    'bi-share',
    '#667eea',
    1
FROM users
WHERE user_id NOT IN (SELECT DISTINCT user_id FROM link_categories);

INSERT INTO link_categories (user_id, category_name, category_icon, category_color, display_order)
SELECT 
    user_id,
    'Work & Projects',
    'bi-briefcase',
    '#764ba2',
    2
FROM users
WHERE user_id NOT IN (SELECT DISTINCT user_id FROM link_categories WHERE category_name = 'Work & Projects');
*/

-- Done! Now run the PHP updates
