-- =====================================================
-- LinkMy Database Reconstruction v3.0 (SAFE EDITION)
-- =====================================================
-- FOKUS: Membuat struktur baru tanpa menghapus / rename tabel lama.
-- Semua tabel lama (old_*) tetap utuh. Bisa diverifikasi sebelum pembersihan.
-- Jalankan HANYA setelah restore penuh database original jika sebelumnya sempat drop.
-- Tested for MySQL 8.4 (no IF EXISTS in RENAME TABLE)
-- =====================================================

SET SQL_MODE = "STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- SAFETY GUARD
-- =====================================================
-- Abort jika tabel baru sudah ada (menghindari double-run)
SELECT CASE WHEN EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'users')
  THEN 'ABORT: Struktur v3 sudah ada. Jangan jalankan ulang.'
  ELSE 'OK: Lanjut migrasi.' END AS migration_guard_message;

-- Jika users sudah ada, script akan error di CREATE TABLE IF NOT EXISTS => gunakan SELECT di atas dulu.

-- =====================================================
-- OPTIONAL: Rename konflik kala ada tabel lama bernama sama
-- =====================================================
-- Jika tabel categories lama masih ada DAN ingin dipakai nanti, rename manual terlebih dahulu:
--   ALTER TABLE categories RENAME TO legacy_categories;
-- Jika tidak dibutuhkan, biarkan saja dan ubah bagian CREATE TABLE di bawah ke nama lain.
-- Untuk konsistensi, kita buat nama baru categories_v3 agar tidak bentrok.

-- =====================================================
-- STEP 1: CREATE NEW STRUCTURE (WITH IF NOT EXISTS)
-- =====================================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `is_verified` TINYINT(1) DEFAULT 0 COMMENT 'Verified badge (influencer/founder)',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User accounts';

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) NULL,
  `bio` TEXT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `is_active` TINYINT(1) DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_slug` (`slug`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User profiles';

CREATE TABLE IF NOT EXISTS `links` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-link-45deg',
  `position` INT DEFAULT 0,
  `clicks` INT UNSIGNED DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `category_id` INT UNSIGNED NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`),
  INDEX `idx_category_id` (`category_id`),
  INDEX `idx_position` (`position`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile links';

-- Menghindari bentrok nama lama categories -> pakai categories_v3
CREATE TABLE IF NOT EXISTS `categories_v3` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `icon` VARCHAR(50) DEFAULT 'bi-folder',
  `color` VARCHAR(20) DEFAULT '#667eea',
  `position` INT DEFAULT 0,
  `is_expanded` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`),
  INDEX `idx_position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link categories/folders (v3)';

ALTER TABLE `links` ADD CONSTRAINT `fk_links_category_v3`
  FOREIGN KEY (`category_id`) REFERENCES `categories_v3`(`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `themes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` INT UNSIGNED NOT NULL UNIQUE,
  `bg_type` ENUM('color','gradient','image') DEFAULT 'gradient',
  `bg_value` TEXT NULL,
  `button_style` VARCHAR(20) DEFAULT 'rounded',
  `button_color` VARCHAR(20) DEFAULT '#667eea',
  `text_color` VARCHAR(20) DEFAULT '#333333',
  `font` VARCHAR(50) DEFAULT 'Inter',
  `layout` VARCHAR(20) DEFAULT 'centered',
  `container_style` VARCHAR(20) DEFAULT 'wide',
  `enable_animations` TINYINT(1) DEFAULT 1,
  `enable_glass_effect` TINYINT(1) DEFAULT 0,
  `shadow_intensity` ENUM('none','light','medium','heavy') DEFAULT 'medium',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`profile_id`) REFERENCES `profiles`(`id`) ON DELETE CASCADE,
  INDEX `idx_profile_id` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Profile appearance settings';

CREATE TABLE IF NOT EXISTS `theme_boxed` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `theme_id` INT UNSIGNED NOT NULL UNIQUE,
  `enabled` TINYINT(1) DEFAULT 0,
  `outer_bg_type` ENUM('color','gradient','image') DEFAULT 'gradient',
  `outer_bg_value` TEXT NULL,
  `container_bg_color` VARCHAR(20) DEFAULT '#ffffff',
  `container_max_width` INT DEFAULT 480,
  `container_radius` INT DEFAULT 30,
  `container_shadow` TINYINT(1) DEFAULT 1,
  FOREIGN KEY (`theme_id`) REFERENCES `themes`(`id`) ON DELETE CASCADE,
  INDEX `idx_theme_id` (`theme_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Boxed layout settings';

CREATE TABLE IF NOT EXISTS `clicks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `link_id` INT UNSIGNED NOT NULL,
  `ip` VARCHAR(45) NULL,
  `country` VARCHAR(50) NULL,
  `city` VARCHAR(100) NULL,
  `user_agent` TEXT NULL,
  `referrer` VARCHAR(255) NULL,
  `clicked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`link_id`) REFERENCES `links`(`id`) ON DELETE CASCADE,
  INDEX `idx_link_id` (`link_id`),
  INDEX `idx_clicked_at` (`clicked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Link click analytics';

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(128) NOT NULL PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `data` TEXT NOT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User sessions';

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `token` VARCHAR(64) NOT NULL UNIQUE,
  `ip` VARCHAR(45) NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_token` (`token`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens';

CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL,
  `otp` VARCHAR(6) NOT NULL,
  `type` ENUM('registration','slug_change') DEFAULT 'registration',
  `ip` VARCHAR(45) NULL,
  `is_used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `expires_at` TIMESTAMP NOT NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_otp` (`otp`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email OTP verifications';

-- =====================================================
-- STEP 2: COPY DATA DARI TABEL LAMA (old_*) KE STRUKTUR BARU
-- =====================================================
-- Pastikan semua tabel old_* masih ada sebelum menjalankan.

-- Users
INSERT INTO `users` (id, username, email, password, is_verified, is_active, created_at)
SELECT user_id, username, email, password_hash, is_verified, 1, created_at
FROM old_users
WHERE NOT EXISTS (SELECT 1 FROM users LIMIT 1); -- cegah double-run (hanya kalau kosong)

-- Profiles
INSERT INTO `profiles` (id, user_id, slug, name, title, bio, avatar, is_active, display_order, created_at, updated_at)
SELECT profile_id, user_id, slug, profile_name, profile_title, bio, COALESCE(profile_pic_filename,'default-avatar.png'), is_active,
       CASE WHEN is_primary=1 THEN 0 ELSE profile_id END, created_at, updated_at
FROM old_profiles
WHERE NOT EXISTS (SELECT 1 FROM profiles LIMIT 1);

-- Categories (ambil dari link_categories lama)
INSERT INTO `categories_v3` (id, profile_id, name, icon, color, position, is_expanded, created_at)
SELECT category_id, profile_id, category_name, category_icon, category_color, display_order, is_expanded, created_at
FROM link_categories
WHERE profile_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM categories_v3 LIMIT 1);

-- Links
INSERT INTO `links` (id, profile_id, title, url, icon, position, clicks, is_active, category_id, created_at)
SELECT link_id, profile_id, title, url, COALESCE(icon_class,'bi-link-45deg'), order_index, click_count, is_active, category_id, created_at
FROM old_links
WHERE profile_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM links LIMIT 1);

-- Themes (dari old_user_appearance)
INSERT INTO `themes` (profile_id, bg_type, bg_value, button_style, button_color, text_color, font, layout, container_style,
                      enable_animations, enable_glass_effect, shadow_intensity, created_at, updated_at)
SELECT profile_id,
       CASE WHEN theme_name='gradient' THEN 'gradient'
            WHEN bg_image_filename IS NOT NULL THEN 'image'
            ELSE 'color' END,
       CASE WHEN gradient_preset IS NOT NULL THEN (SELECT gradient_css FROM gradient_presets WHERE preset_name = gradient_preset LIMIT 1)
            WHEN bg_image_filename IS NOT NULL THEN bg_image_filename
            ELSE COALESCE(custom_bg_color,'#667eea') END,
       button_style,
       COALESCE(custom_button_color,'#667eea'),
       COALESCE(custom_text_color,'#333333'),
       font_family,
       profile_layout,
       container_style,
       enable_animations,
       enable_glass_effect,
       shadow_intensity,
       updated_at,
       updated_at
FROM old_user_appearance
WHERE profile_id IS NOT NULL AND NOT EXISTS (SELECT 1 FROM themes LIMIT 1);

-- Boxed Layout
INSERT INTO `theme_boxed` (theme_id, enabled, outer_bg_type, outer_bg_value, container_bg_color, container_max_width, container_radius, container_shadow)
SELECT t.id,
       a.boxed_layout,
       a.outer_bg_type,
       CASE WHEN a.outer_bg_type='gradient' THEN CONCAT('linear-gradient(135deg, ', a.outer_bg_gradient_start,' 0%, ',a.outer_bg_gradient_end,' 100%)')
            WHEN a.outer_bg_type='image' THEN a.outer_bg_image
            ELSE a.outer_bg_color END,
       COALESCE(a.container_bg_color,'#ffffff'),
       COALESCE(a.container_max_width,480),
       COALESCE(a.container_border_radius,30),
       COALESCE(a.container_shadow,1)
FROM old_user_appearance a
JOIN themes t ON t.profile_id = a.profile_id
WHERE a.boxed_layout = 1 AND NOT EXISTS (SELECT 1 FROM theme_boxed LIMIT 1);

-- Click Analytics
INSERT INTO `clicks` (link_id, ip, country, city, user_agent, referrer, clicked_at)
SELECT link_id, ip_address, country, city, user_agent, referrer, clicked_at
FROM old_link_analytics
WHERE link_id IN (SELECT id FROM links)
  AND NOT EXISTS (SELECT 1 FROM clicks LIMIT 1);

-- Sessions
INSERT INTO `sessions` (id, user_id, data, last_activity)
SELECT session_id, NULL, session_data, session_expire
FROM old_sessions
WHERE NOT EXISTS (SELECT 1 FROM sessions LIMIT 1);

-- Password Resets
INSERT INTO `password_resets` (email, token, ip, is_used, created_at, expires_at)
SELECT email, reset_token, ip_address, is_used, created_at, expires_at
FROM old_password_resets
WHERE NOT EXISTS (SELECT 1 FROM password_resets LIMIT 1);

-- Email Verifications
INSERT INTO `email_verifications` (email, otp, type, ip, is_used, created_at, expires_at)
SELECT email, otp_code,
       CASE verification_type WHEN 'slug_change' THEN 'slug_change' ELSE 'registration' END,
       ip_address, is_used, created_at, expires_at
FROM old_email_verifications
WHERE NOT EXISTS (SELECT 1 FROM email_verifications LIMIT 1);

-- =====================================================
-- STEP 3: VIEWS & PROCEDURES BARU
-- =====================================================

CREATE OR REPLACE VIEW v_profile_stats AS
SELECT p.id, p.user_id, p.slug, p.name, p.title, p.bio, p.avatar, p.is_active,
       u.username, u.email, u.is_verified,
       COUNT(DISTINCT l.id) AS link_count,
       COALESCE(SUM(l.clicks),0) AS total_clicks,
       p.created_at, p.updated_at
FROM profiles p
JOIN users u ON p.user_id = u.id
LEFT JOIN links l ON l.profile_id = p.id AND l.is_active = 1
GROUP BY p.id, p.user_id, p.slug, p.name, p.title, p.bio, p.avatar, p.is_active,
         u.username, u.email, u.is_verified, p.created_at, p.updated_at;

CREATE OR REPLACE VIEW v_public_profiles AS
SELECT p.id, p.slug, p.name, p.title, p.bio, p.avatar,
       u.username, u.is_verified,
       t.bg_type, t.bg_value, t.button_style, t.button_color, t.text_color, t.font,
       t.layout, t.container_style, t.enable_animations, t.enable_glass_effect, t.shadow_intensity,
       tb.enabled AS boxed_enabled, tb.outer_bg_type, tb.outer_bg_value, tb.container_bg_color,
       tb.container_max_width, tb.container_radius, tb.container_shadow
FROM profiles p
JOIN users u ON p.user_id = u.id
LEFT JOIN themes t ON t.profile_id = p.id
LEFT JOIN theme_boxed tb ON tb.theme_id = t.id
WHERE p.is_active = 1;

DELIMITER $$
CREATE PROCEDURE sp_get_user_profiles(IN p_user_id INT)
BEGIN
  SELECT * FROM v_profile_stats WHERE user_id = p_user_id ORDER BY display_order ASC, created_at ASC;
END$$

CREATE PROCEDURE sp_get_profile_full(IN p_slug VARCHAR(50))
BEGIN
  SELECT * FROM v_public_profiles WHERE slug = p_slug LIMIT 1;
  SELECT * FROM categories_v3 WHERE profile_id = (SELECT id FROM profiles WHERE slug = p_slug) ORDER BY position ASC;
  SELECT l.*, c.name AS category_name
  FROM links l
  LEFT JOIN categories_v3 c ON l.category_id = c.id
  WHERE l.profile_id = (SELECT id FROM profiles WHERE slug = p_slug) AND l.is_active = 1
  ORDER BY l.position ASC;
END$$

CREATE PROCEDURE sp_increment_click(
  IN p_link_id INT,
  IN p_ip VARCHAR(45),
  IN p_country VARCHAR(50),
  IN p_city VARCHAR(100),
  IN p_user_agent TEXT,
  IN p_referrer VARCHAR(255)
)
BEGIN
  UPDATE links SET clicks = clicks + 1 WHERE id = p_link_id;
  INSERT INTO clicks (link_id, ip, country, city, user_agent, referrer)
  VALUES (p_link_id, p_ip, p_country, p_city, p_user_agent, p_referrer);
END$$
DELIMITER ;

-- =====================================================
-- STEP 4: FINALIZATION
-- =====================================================

SET FOREIGN_KEY_CHECKS = 1;

-- Auto Increment alignment
SET @mx := (SELECT COALESCE(MAX(id),0)+1 FROM users); SET @s := CONCAT('ALTER TABLE users AUTO_INCREMENT=',@mx); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @mx := (SELECT COALESCE(MAX(id),0)+1 FROM profiles); SET @s := CONCAT('ALTER TABLE profiles AUTO_INCREMENT=',@mx); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @mx := (SELECT COALESCE(MAX(id),0)+1 FROM links); SET @s := CONCAT('ALTER TABLE links AUTO_INCREMENT=',@mx); PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

OPTIMIZE TABLE users, profiles, links, categories_v3, themes, theme_boxed, clicks, sessions;

-- Summary
SELECT '=== MIGRATION SUMMARY (SAFE) ===' AS '';
SELECT 'Users', COUNT(*) FROM users UNION ALL
SELECT 'Profiles', COUNT(*) FROM profiles UNION ALL
SELECT 'Links', COUNT(*) FROM links UNION ALL
SELECT 'Categories_v3', COUNT(*) FROM categories_v3 UNION ALL
SELECT 'Themes', COUNT(*) FROM themes UNION ALL
SELECT 'Clicks', COUNT(*) FROM clicks;
SELECT 'NOTE: Tabel lama old_* tetap utuh. Verifikasi lalu baru bersihkan manual.' AS info;
SELECT 'Jika puas: DROP TABLE old_users, old_profiles, ... (setelah backup tambahan)' AS cleanup_hint;

-- =====================================================
-- END SAFE MIGRATION SCRIPT
-- =====================================================
