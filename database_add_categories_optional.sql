-- Create categories table (optional feature)
-- Run this if you want to enable categories feature

-- Step 1: Create categories table
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

-- Step 2: Check and add category_id column to links table
-- Use stored procedure to check if column exists before adding
DELIMITER $$

DROP PROCEDURE IF EXISTS add_category_column$$

CREATE PROCEDURE add_category_column()
BEGIN
    -- Check if category_id column exists
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = 'linkmy_db'
        AND TABLE_NAME = 'links'
        AND COLUMN_NAME = 'category_id'
    ) THEN
        -- Add category_id column
        ALTER TABLE `links` 
        ADD COLUMN `category_id` int DEFAULT NULL AFTER `display_order`;
        
        -- Add index
        ALTER TABLE `links` 
        ADD KEY `category_id` (`category_id`);
        
        -- Add foreign key constraint
        ALTER TABLE `links` 
        ADD CONSTRAINT `links_ibfk_2` FOREIGN KEY (`category_id`) 
        REFERENCES `categories` (`id`) ON DELETE SET NULL;
    END IF;
END$$

DELIMITER ;

-- Execute the procedure
CALL add_category_column();

-- Clean up
DROP PROCEDURE IF EXISTS add_category_column;

-- Note: Categories feature is optional
-- If you don't need it, the profile.php will work fine without this table
