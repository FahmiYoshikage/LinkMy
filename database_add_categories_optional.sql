-- Create categories table (optional feature)
-- Run this if you want to enable categories feature

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'bi-folder',
  `color` varchar(20) DEFAULT '#667eea',
  `is_expanded` tinyint(1) DEFAULT 1,
  `display_order` int DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add category_id column to links table if not exists
ALTER TABLE `links` 
ADD COLUMN IF NOT EXISTS `category_id` int DEFAULT NULL AFTER `display_order`,
ADD KEY IF NOT EXISTS `category_id` (`category_id`),
ADD CONSTRAINT `links_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

-- Note: Categories feature is optional
-- If you don't need it, the profile.php will work fine without this table
