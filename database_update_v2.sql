-- ============================================
-- LinkMy Database Enhancement v2.0
-- Date: November 15, 2025
-- Description: Add advanced customization features
-- ============================================

-- 1. Add new columns to appearance table for advanced customization
ALTER TABLE `appearance` 
ADD COLUMN `custom_bg_color` VARCHAR(20) DEFAULT NULL COMMENT 'Custom background color hex',
ADD COLUMN `custom_button_color` VARCHAR(20) DEFAULT NULL COMMENT 'Custom button color hex',
ADD COLUMN `custom_text_color` VARCHAR(20) DEFAULT NULL COMMENT 'Custom text color hex',
ADD COLUMN `gradient_preset` VARCHAR(50) DEFAULT NULL COMMENT 'Predefined gradient name',
ADD COLUMN `profile_layout` VARCHAR(20) DEFAULT 'centered' COMMENT 'Profile layout style: centered, left, minimal',
ADD COLUMN `show_profile_border` TINYINT(1) DEFAULT 1 COMMENT 'Show border around profile picture',
ADD COLUMN `enable_animations` TINYINT(1) DEFAULT 1 COMMENT 'Enable hover animations on links';

-- 2. Create link_categories table
CREATE TABLE IF NOT EXISTS `link_categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `category_name` VARCHAR(50) NOT NULL,
  `category_icon` VARCHAR(50) DEFAULT 'bi-folder',
  `category_color` VARCHAR(20) DEFAULT '#667eea',
  `order_index` INT(11) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_category_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. Add category_id column to links table
ALTER TABLE `links` 
ADD COLUMN `category_id` INT(11) DEFAULT NULL COMMENT 'Link category for grouping',
ADD KEY `category_id` (`category_id`),
ADD CONSTRAINT `fk_link_category` FOREIGN KEY (`category_id`) REFERENCES `link_categories` (`category_id`) ON DELETE SET NULL;

-- 4. Create gradient_presets table (predefined gradients)
CREATE TABLE IF NOT EXISTS `gradient_presets` (
  `preset_id` INT(11) NOT NULL AUTO_INCREMENT,
  `preset_name` VARCHAR(50) NOT NULL,
  `gradient_css` TEXT NOT NULL,
  `preview_color_1` VARCHAR(20) NOT NULL,
  `preview_color_2` VARCHAR(20) NOT NULL,
  `is_default` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`preset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 5. Insert default gradient presets
INSERT INTO `gradient_presets` (`preset_name`, `gradient_css`, `preview_color_1`, `preview_color_2`, `is_default`) VALUES
('Purple Dream', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea', '#764ba2', 1),
('Ocean Blue', 'linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)', '#00c6ff', '#0072ff', 1),
('Sunset Orange', 'linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)', '#ff6a00', '#ee0979', 1),
('Fresh Mint', 'linear-gradient(135deg, #00b09b 0%, #96c93d 100%)', '#00b09b', '#96c93d', 1),
('Pink Lemonade', 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)', '#ff9a9e', '#fecfef', 1),
('Royal Purple', 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)', '#8e2de2', '#4a00e0', 1),
('Fire Blaze', 'linear-gradient(135deg, #f85032 0%, #e73827 100%)', '#f85032', '#e73827', 1),
('Emerald Water', 'linear-gradient(135deg, #348f50 0%, #56b4d3 100%)', '#348f50', '#56b4d3', 1),
('Candy Shop', 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)', '#f093fb', '#f5576c', 1),
('Cool Blues', 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)', '#4facfe', '#00f2fe', 1),
('Warm Flame', 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)', '#ff9a56', '#ff6a88', 1),
('Deep Sea', 'linear-gradient(135deg, #2e3192 0%, #1bffff 100%)', '#2e3192', '#1bffff', 1);

-- 6. Create social_icons table (common social media icons)
CREATE TABLE IF NOT EXISTS `social_icons` (
  `icon_id` INT(11) NOT NULL AUTO_INCREMENT,
  `platform_name` VARCHAR(50) NOT NULL,
  `icon_class` VARCHAR(50) NOT NULL,
  `icon_color` VARCHAR(20) DEFAULT NULL,
  `base_url` VARCHAR(100) DEFAULT NULL COMMENT 'Base URL pattern for the platform',
  PRIMARY KEY (`icon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 7. Insert common social media icons
INSERT INTO `social_icons` (`platform_name`, `icon_class`, `icon_color`, `base_url`) VALUES
('Instagram', 'bi-instagram', '#E4405F', 'https://instagram.com/'),
('Facebook', 'bi-facebook', '#1877F2', 'https://facebook.com/'),
('Twitter/X', 'bi-twitter-x', '#000000', 'https://twitter.com/'),
('LinkedIn', 'bi-linkedin', '#0A66C2', 'https://linkedin.com/in/'),
('GitHub', 'bi-github', '#181717', 'https://github.com/'),
('YouTube', 'bi-youtube', '#FF0000', 'https://youtube.com/'),
('TikTok', 'bi-tiktok', '#000000', 'https://tiktok.com/@'),
('WhatsApp', 'bi-whatsapp', '#25D366', 'https://wa.me/'),
('Telegram', 'bi-telegram', '#26A5E4', 'https://t.me/'),
('Discord', 'bi-discord', '#5865F2', 'https://discord.gg/'),
('Twitch', 'bi-twitch', '#9146FF', 'https://twitch.tv/'),
('Spotify', 'bi-spotify', '#1DB954', 'https://open.spotify.com/'),
('Medium', 'bi-medium', '#000000', 'https://medium.com/@'),
('Reddit', 'bi-reddit', '#FF4500', 'https://reddit.com/u/'),
('Pinterest', 'bi-pinterest', '#E60023', 'https://pinterest.com/'),
('Snapchat', 'bi-snapchat', '#FFFC00', 'https://snapchat.com/add/'),
('Email', 'bi-envelope-fill', '#EA4335', 'mailto:'),
('Website', 'bi-globe', '#667eea', 'https://'),
('Link', 'bi-link-45deg', '#6c757d', NULL);

-- 8. Insert default categories for existing users (optional)
INSERT INTO `link_categories` (`user_id`, `category_name`, `category_icon`, `category_color`, `order_index`)
SELECT DISTINCT `user_id`, 'Social Media', 'bi-people-fill', '#667eea', 1
FROM `users`
WHERE NOT EXISTS (SELECT 1 FROM `link_categories` WHERE `link_categories`.`user_id` = `users`.`user_id`);

INSERT INTO `link_categories` (`user_id`, `category_name`, `category_icon`, `category_color`, `order_index`)
SELECT DISTINCT `user_id`, 'Professional', 'bi-briefcase-fill', '#28a745', 2
FROM `users`
WHERE NOT EXISTS (SELECT 1 FROM `link_categories` lc WHERE lc.`user_id` = `users`.`user_id` AND lc.`category_name` = 'Professional');

INSERT INTO `link_categories` (`user_id`, `category_name`, `category_icon`, `category_color`, `order_index`)
SELECT DISTINCT `user_id`, 'Content', 'bi-play-circle-fill', '#dc3545', 3
FROM `users`
WHERE NOT EXISTS (SELECT 1 FROM `link_categories` lc WHERE lc.`user_id` = `users`.`user_id` AND lc.`category_name` = 'Content');

-- 9. Create analytics table for tracking link performance (bonus feature)
CREATE TABLE IF NOT EXISTS `link_analytics` (
  `analytics_id` INT(11) NOT NULL AUTO_INCREMENT,
  `link_id` INT(11) NOT NULL,
  `clicked_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `referrer` VARCHAR(255) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `country` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY (`analytics_id`),
  KEY `link_id` (`link_id`),
  KEY `clicked_at` (`clicked_at`),
  CONSTRAINT `fk_analytics_link` FOREIGN KEY (`link_id`) REFERENCES `links` (`link_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 10. Update the view to include new fields
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
    a.gradient_preset,
    a.profile_layout,
    a.show_profile_border,
    a.enable_animations,
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
-- DONE! Database enhancement completed.
-- Run this SQL file to add all new features.
-- ============================================
