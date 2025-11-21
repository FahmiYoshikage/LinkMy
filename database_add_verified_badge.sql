-- Add verified badge system for LinkMy founder
-- This adds a verified column to users table and marks fahmiilham029@gmail.com as verified

-- Add verified column if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS is_verified TINYINT(1) DEFAULT 0 COMMENT 'Verified badge (1=verified founder/influencer)';

-- Mark founder as verified
UPDATE users 
SET is_verified = 1 
WHERE email = 'fahmiilham029@gmail.com';

-- Add index for faster queries
ALTER TABLE users 
ADD INDEX idx_verified (is_verified) IF NOT EXISTS;
