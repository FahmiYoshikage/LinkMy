-- Add boxed layout columns to appearance table
-- Safe version: Check if columns exist before adding

-- Drop procedure if exists
DROP PROCEDURE IF EXISTS add_boxed_layout_columns;

DELIMITER $$

CREATE PROCEDURE add_boxed_layout_columns()
BEGIN
    -- Check and add boxed_layout
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'boxed_layout'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `boxed_layout` TINYINT(1) DEFAULT 0 COMMENT '0=full width, 1=boxed mode';
    END IF;
    
    -- Check and add outer_bg_type
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'outer_bg_type'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `outer_bg_type` VARCHAR(20) DEFAULT 'gradient' COMMENT 'color, gradient, image';
    END IF;
    
    -- Check and add outer_bg_color
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'outer_bg_color'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `outer_bg_color` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Outer background color';
    END IF;
    
    -- Check and add outer_bg_gradient_start
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'outer_bg_gradient_start'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `outer_bg_gradient_start` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Gradient start color';
    END IF;
    
    -- Check and add outer_bg_gradient_end
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'outer_bg_gradient_end'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `outer_bg_gradient_end` VARCHAR(50) DEFAULT '#764ba2' COMMENT 'Gradient end color';
    END IF;
    
    -- Check and add outer_bg_image
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'outer_bg_image'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `outer_bg_image` VARCHAR(255) DEFAULT NULL COMMENT 'Path to background image';
    END IF;
    
    -- Check and add container_bg_color
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'container_bg_color'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `container_bg_color` VARCHAR(50) DEFAULT '#ffffff' COMMENT 'Inner container background';
    END IF;
    
    -- Check and add container_max_width
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'container_max_width'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `container_max_width` INT DEFAULT 480 COMMENT 'Max width in pixels for boxed container';
    END IF;
    
    -- Check and add container_border_radius
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'container_border_radius'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `container_border_radius` INT DEFAULT 30 COMMENT 'Border radius in pixels';
    END IF;
    
    -- Check and add container_shadow
    IF NOT EXISTS (
        SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'appearance' 
        AND COLUMN_NAME = 'container_shadow'
    ) THEN
        ALTER TABLE `appearance` ADD COLUMN `container_shadow` TINYINT(1) DEFAULT 1 COMMENT 'Show shadow on container';
    END IF;
    
END$$

DELIMITER ;

-- Execute the procedure
CALL add_boxed_layout_columns();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS add_boxed_layout_columns;

-- Verify columns were added
SELECT 'Boxed Layout columns added successfully!' AS Status;
