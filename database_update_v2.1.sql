-- ============================================
-- LinkMy v2.1 - Additional Customization Features
-- ============================================
-- This update adds more customization options:
-- 1. Link text color control
-- 2. Glass morphism effect toggle
-- 3. Shadow intensity control
-- 4. 12 new gradient presets (total 24)
-- ============================================

-- 1. Add new appearance columns
ALTER TABLE `appearance` 
ADD COLUMN IF NOT EXISTS `custom_link_text_color` VARCHAR(20) DEFAULT NULL COMMENT 'Custom color for link text' AFTER `custom_text_color`,
ADD COLUMN IF NOT EXISTS `enable_glass_effect` TINYINT(1) DEFAULT 0 COMMENT 'Enable glass morphism effect on links' AFTER `enable_animations`,
ADD COLUMN IF NOT EXISTS `shadow_intensity` ENUM('none', 'light', 'medium', 'heavy') DEFAULT 'medium' COMMENT 'Shadow intensity for links' AFTER `enable_glass_effect`;

-- 2. Insert 12 new gradient presets
INSERT INTO `gradient_presets` (`preset_name`, `gradient_css`, `preview_color_1`, `preview_color_2`, `is_default`) VALUES
('Nebula Night', 'linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%)', '#3a1c71', '#ffaf7b', 1),
('Aurora Borealis', 'linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%)', '#00c9ff', '#92fe9d', 1),
('Crimson Tide', 'linear-gradient(135deg, #c31432 0%, #240b36 100%)', '#c31432', '#240b36', 1),
('Golden Hour', 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 50%, #e17055 100%)', '#ffeaa7', '#e17055', 1),
('Midnight Blue', 'linear-gradient(135deg, #000428 0%, #004e92 100%)', '#000428', '#004e92', 1),
('Rose Petal', 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)', '#ffecd2', '#fcb69f', 1),
('Electric Violet', 'linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)', '#4776e6', '#8e54e9', 1),
('Jungle Green', 'linear-gradient(135deg, #134e5e 0%, #71b280 100%)', '#134e5e', '#71b280', 1),
('Peach Cream', 'linear-gradient(135deg, #ff9a8b 0%, #ff6a88 50%, #ff99ac 100%)', '#ff9a8b', '#ff99ac', 1),
('Arctic Ice', 'linear-gradient(135deg, #667db6 0%, #0082c8 50%, #0082c8 100%, #667db6 100%)', '#667db6', '#0082c8', 1),
('Sunset Glow', 'linear-gradient(135deg, #ffa751 0%, #ffe259 100%)', '#ffa751', '#ffe259', 1),
('Purple Haze', 'linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)', '#c471f5', '#fa71cd', 1);

-- 3. Update v_public_page_data view to include new columns
DROP VIEW IF EXISTS `v_public_page_data`;

CREATE VIEW `v_public_page_data` AS
SELECT 
    u.user_id,
    u.username,
    u.page_slug,
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
    l.category_id,
    lc.category_name,
    lc.category_icon,
    lc.category_color
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
LEFT JOIN links l ON u.user_id = l.user_id AND l.is_active = 1
LEFT JOIN link_categories lc ON l.category_id = lc.category_id
ORDER BY u.user_id, lc.order_index, l.order_index;

-- ============================================
-- DONE! v2.1 Enhancement completed.
-- New features:
-- - Link text color customization
-- - Glass morphism effect
-- - Shadow intensity control
-- - 12 new gradient presets
-- ============================================
