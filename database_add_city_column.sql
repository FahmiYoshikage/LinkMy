-- Add city column to link_analytics for detailed geographic tracking
-- Run this on VPS database to enable city-level analytics

USE linkmy_db;

-- Add city column if not exists
ALTER TABLE link_analytics 
ADD COLUMN IF NOT EXISTS city VARCHAR(100) DEFAULT NULL AFTER country;

-- Add index for performance on location queries
ALTER TABLE link_analytics 
ADD INDEX IF NOT EXISTS idx_location (country, city);

-- Verify structure
DESCRIBE link_analytics;

SELECT 'City column added successfully to link_analytics table' as status;
