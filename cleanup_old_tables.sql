-- =====================================================
-- CLEANUP: Drop tabel legacy old_* setelah migrasi sukses
-- =====================================================
-- ⚠️ HANYA JALANKAN SETELAH:
-- 1. Verifikasi data di tabel baru (users, profiles, links) lengkap
-- 2. Buat backup tambahan jika perlu: mysqldump linkmy_db > post_migration_backup.sql
-- 3. Test aplikasi berjalan normal dengan struktur v3

SET FOREIGN_KEY_CHECKS = 0;

-- Drop legacy tables
DROP TABLE IF EXISTS old_link_analytics;
DROP TABLE IF EXISTS old_links;
DROP TABLE IF EXISTS old_profile_analytics;
DROP TABLE IF EXISTS old_user_appearance;
DROP TABLE IF EXISTS old_profiles;
DROP TABLE IF EXISTS old_users;
DROP TABLE IF EXISTS old_sessions;
DROP TABLE IF EXISTS old_password_resets;
DROP TABLE IF EXISTS old_email_verifications;

-- Drop helper tables yang sudah tidak dipakai
DROP TABLE IF EXISTS link_categories;
DROP TABLE IF EXISTS gradient_presets;
DROP TABLE IF EXISTS categories; -- jika ada (diganti categories_v3)

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Cleanup selesai. Tabel legacy dihapus.' AS status;
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'linkmy_db' AND table_type = 'BASE TABLE'
ORDER BY table_name;
