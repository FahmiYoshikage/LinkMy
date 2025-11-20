-- Add boxed layout columns to appearance table
ALTER TABLE `appearance` 
ADD COLUMN `boxed_layout` TINYINT(1) DEFAULT 0 COMMENT '0=full width, 1=boxed mode',
ADD COLUMN `outer_bg_type` VARCHAR(20) DEFAULT 'color' COMMENT 'color, gradient, image',
ADD COLUMN `outer_bg_color` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Outer background color',
ADD COLUMN `outer_bg_gradient_start` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Gradient start color',
ADD COLUMN `outer_bg_gradient_end` VARCHAR(50) DEFAULT '#764ba2' COMMENT 'Gradient end color',
ADD COLUMN `outer_bg_image` VARCHAR(255) DEFAULT NULL COMMENT 'Path to background image',
ADD COLUMN `container_bg_color` VARCHAR(50) DEFAULT '#ffffff' COMMENT 'Inner container background',
ADD COLUMN `container_max_width` INT DEFAULT 480 COMMENT 'Max width in pixels for boxed container',
ADD COLUMN `container_border_radius` INT DEFAULT 30 COMMENT 'Border radius in pixels',
ADD COLUMN `container_shadow` TINYINT(1) DEFAULT 1 COMMENT 'Show shadow on container';
