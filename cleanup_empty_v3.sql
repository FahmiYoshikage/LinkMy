-- =====================================================
-- CLEANUP: Drop empty v3 tables to prepare for restore
-- =====================================================
-- Hanya jalankan jika tabel v3 KOSONG (0 rows)
-- Aman karena data masih ada di backup_before_v3.sql

SET FOREIGN_KEY_CHECKS = 0;

-- Drop procedures & views dulu
DROP PROCEDURE IF EXISTS sp_get_user_profiles;
DROP PROCEDURE IF EXISTS sp_get_profile_full;
DROP PROCEDURE IF EXISTS sp_increment_click;
DROP VIEW IF EXISTS v_profile_stats;
DROP VIEW IF EXISTS v_public_profiles;

-- Drop tables v3 (urutan penting karena foreign key)
DROP TABLE IF EXISTS theme_boxed;
DROP TABLE IF EXISTS clicks;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS links;
DROP TABLE IF EXISTS categories_v3;
DROP TABLE IF EXISTS profiles;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS email_verifications;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Cleanup selesai. Struktur v3 dihapus. Siap restore backup.' AS status;
