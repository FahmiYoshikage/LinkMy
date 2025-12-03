-- Migration: Add CASCADE DELETE to Foreign Keys
-- Purpose: Ensure when profile/user deleted, all related data (links, themes, etc) also deleted
-- Date: December 3, 2025

USE linkmy_db;

-- 1. Drop existing foreign keys first
ALTER TABLE `links` DROP FOREIGN KEY IF EXISTS `links_ibfk_1`;
ALTER TABLE `themes` DROP FOREIGN KEY IF EXISTS `themes_ibfk_1`;
ALTER TABLE `theme_boxed` DROP FOREIGN KEY IF EXISTS `theme_boxed_ibfk_1`;
ALTER TABLE `categories_v3` DROP FOREIGN KEY IF EXISTS `categories_v3_ibfk_1`;
ALTER TABLE `profiles` DROP FOREIGN KEY IF EXISTS `profiles_ibfk_1`;

-- 2. Add CASCADE DELETE foreign keys

-- Links table: Delete links when profile deleted
ALTER TABLE `links`
  ADD CONSTRAINT `links_profile_cascade` 
  FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Themes table: Delete theme when profile deleted
ALTER TABLE `themes`
  ADD CONSTRAINT `themes_profile_cascade` 
  FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Theme_boxed table: Delete boxed settings when theme deleted
ALTER TABLE `theme_boxed`
  ADD CONSTRAINT `theme_boxed_theme_cascade` 
  FOREIGN KEY (`theme_id`) REFERENCES `themes` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Categories_v3 table: Delete categories when profile deleted
ALTER TABLE `categories_v3`
  ADD CONSTRAINT `categories_profile_cascade` 
  FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- Profiles table: Delete profiles when user deleted
ALTER TABLE `profiles`
  ADD CONSTRAINT `profiles_user_cascade` 
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE;

-- 3. Clean up orphaned data (optional but recommended)
-- Delete links with non-existent profile_id
DELETE l FROM links l
LEFT JOIN profiles p ON l.profile_id = p.id
WHERE p.id IS NULL;

-- Delete themes with non-existent profile_id
DELETE t FROM themes t
LEFT JOIN profiles p ON t.profile_id = p.id
WHERE p.id IS NULL;

-- Delete categories with non-existent profile_id
DELETE c FROM categories_v3 c
LEFT JOIN profiles p ON c.profile_id = p.id
WHERE p.id IS NULL;

-- Delete profiles with non-existent user_id
DELETE p FROM profiles p
LEFT JOIN users u ON p.user_id = u.id
WHERE u.id IS NULL;

SELECT 'Migration completed: CASCADE DELETE constraints added' AS status;
