# 🚀 DEPLOYMENT INSTRUCTIONS - LinkMy v2.0

## ⚠️ PENTING - BACA INI DULU!

Sebelum melakukan update ke v2.0, pastikan:

1. ✅ Backup database Anda
2. ✅ Backup semua file PHP
3. ✅ Catat username & password admin
4. ✅ Simpan list semua link yang ada

## 📋 Pre-Deployment Checklist

```
☐ Server Requirements:
  ☐ PHP 7.4 atau lebih baru
  ☐ MySQL 5.7+ atau MariaDB 10.2+
  ☐ Apache/Nginx dengan mod_rewrite
  ☐ Minimal 50MB disk space

☐ Backup Checklist:
  ☐ Database backup (mysqldump)
  ☐ Files backup (zip/tar)
  ☐ Config files backup (db.php, auth_check.php)
  ☐ Upload folder backup (profile_pics, backgrounds)

☐ Permission Checklist:
  ☐ uploads/ folder: 755 atau 777
  ☐ uploads/profile_pics/: 755 atau 777
  ☐ uploads/backgrounds/: 755 atau 777
```

## 🔧 Step-by-Step Deployment

### Step 1: Backup Everything! 🛡️

```bash
# Backup database
cd C:\xampp\mysql\bin
mysqldump -u root -p linkmy_db > C:\backup\linkmy_backup_before_v2.sql

# Backup files (manual)
# Copy folder c:\xampp\htdocs\ ke C:\backup\htdocs_backup\
```

### Step 2: Update Database Schema 🗄️

**Via phpMyAdmin (Recommended):**

```
1. Buka http://localhost/phpmyadmin
2. Pilih database: linkmy_db
3. Klik tab "Import"
4. Choose file: database_update_v2.sql
5. Scroll ke bawah
6. Klik "Go"
7. Tunggu hingga selesai
8. Lihat success message
```

**Via MySQL Command Line:**

```bash
cd C:\xampp\htdocs
C:\xampp\mysql\bin\mysql -u root -p linkmy_db < database_update_v2.sql
```

### Step 3: Verify Database Update ✅

**Run these queries in phpMyAdmin:**

```sql
-- 1. Check new tables exist
SHOW TABLES LIKE '%gradient%';
SHOW TABLES LIKE '%category%';
SHOW TABLES LIKE '%social%';
-- Expected: gradient_presets, link_categories, social_icons

-- 2. Check appearance table columns
DESCRIBE appearance;
-- Expected: Should see 7 new columns:
-- custom_bg_color, custom_button_color, custom_text_color,
-- gradient_preset, profile_layout, show_profile_border, enable_animations

-- 3. Check links table columns
DESCRIBE links;
-- Expected: Should see category_id column

-- 4. Check gradient presets data
SELECT COUNT(*) FROM gradient_presets;
-- Expected: 12

-- 5. Check social icons data
SELECT COUNT(*) FROM social_icons;
-- Expected: 19

-- 6. Check link categories
SELECT * FROM link_categories;
-- Expected: 3 categories per existing user

-- 7. Verify view
SELECT * FROM v_public_page_data LIMIT 1;
-- Expected: Should include new columns without error
```

### Step 4: File Update (Already Done) ✅

File `admin/appearance.php` sudah di-update dengan:

-   ✅ Tab Advanced
-   ✅ Gradient presets UI
-   ✅ Custom color pickers
-   ✅ Profile layout selector
-   ✅ Social icons library
-   ✅ Enhanced CSS
-   ✅ New JavaScript functions

### Step 5: Test Features 🧪

**Test Checklist:**

```
☐ Login Test:
  ☐ Login dengan username & password existing
  ☐ Redirect ke dashboard
  ☐ Session berfungsi normal

☐ Appearance Page Test:
  ☐ Buka menu Appearance
  ☐ Tab Advanced muncul dengan badge "New"
  ☐ Klik tab Advanced

☐ Gradient Presets Test:
  ☐ 12 gradient cards tampil
  ☐ Klik salah satu gradient
  ☐ Check badge muncul di card aktif
  ☐ Live preview background berubah
  ☐ Klik "Save Advanced Settings"
  ☐ Success message muncul

☐ Custom Colors Test:
  ☐ Klik color picker Background
  ☐ Pilih warna
  ☐ Hex display update
  ☐ Preview background berubah
  ☐ Test Button Color picker
  ☐ Test Text Color picker
  ☐ Save settings

☐ Profile Layout Test:
  ☐ 3 layout cards tampil
  ☐ Klik "Left Aligned"
  ☐ Check badge pindah
  ☐ Preview layout berubah (visual)
  ☐ Save settings

☐ Social Icons Test:
  ☐ Scroll ke Social Icons section
  ☐ 19 icons tampil dalam grid
  ☐ Klik icon Instagram
  ☐ Toast notification muncul
  ☐ Check clipboard (Ctrl+V)
  ☐ Should be: bi-instagram

☐ Live Preview Test:
  ☐ Preview sidebar tampil di kanan
  ☐ Ketik di Profile Title
  ☐ Preview title update real-time
  ☐ Ketik di Bio
  ☐ Preview bio update
  ☐ Ganti theme
  ☐ Preview theme berubah

☐ Public Page Test:
  ☐ Save semua settings
  ☐ Klik "View Page" di navbar
  ☐ Public page buka di tab baru
  ☐ Gradient/custom colors applied
  ☐ Layout applied correctly
  ☐ Profile border shows/hides correctly
  ☐ Animations work (hover links)
```

### Step 6: Performance Check ⚡

```sql
-- Run these queries to check performance

-- 1. Test view performance
EXPLAIN SELECT * FROM v_public_page_data WHERE page_slug = 'test';

-- 2. Check indexes
SHOW INDEX FROM appearance;
SHOW INDEX FROM links;
SHOW INDEX FROM link_categories;

-- 3. Test query speed (should be < 10ms)
SELECT BENCHMARK(1000,
  (SELECT * FROM gradient_presets WHERE is_default = 1)
);
```

## 🐛 Troubleshooting

### Problem 1: Tab Advanced Tidak Muncul

**Cause:** Database belum di-update  
**Solution:**

```
1. Check error log browser (F12 → Console)
2. Check PHP error log (xampp/apache/logs/error.log)
3. Re-run database_update_v2.sql
4. Clear browser cache (Ctrl+Shift+Del)
5. Hard refresh (Ctrl+F5)
```

### Problem 2: Gradient Presets Kosong

**Cause:** Table gradient_presets tidak terisi  
**Solution:**

```sql
-- Check data
SELECT * FROM gradient_presets;

-- If empty, re-run insert
-- Copy INSERT statements dari database_update_v2.sql
-- Lines 58-70 (INSERT INTO gradient_presets...)
```

### Problem 3: Social Icons Tidak Tampil

**Cause:** Table social_icons tidak terisi  
**Solution:**

```sql
-- Check data
SELECT * FROM social_icons;

-- If empty, re-run insert
-- Copy INSERT statements dari database_update_v2.sql
-- Lines 88-106 (INSERT INTO social_icons...)
```

### Problem 4: Error Saat Save Settings

**Cause:** Kolom baru belum ada di table appearance  
**Solution:**

```sql
-- Check columns
DESCRIBE appearance;

-- If columns missing, re-run ALTER TABLE
-- Copy ALTER statements dari database_update_v2.sql
-- Lines 8-14 (ALTER TABLE appearance ADD COLUMN...)
```

### Problem 5: Preview Tidak Update

**Cause:** JavaScript error  
**Solution:**

```
1. Open browser console (F12)
2. Look for JavaScript errors
3. Check if Bootstrap JS loaded
4. Check if jQuery conflicts
5. Try different browser
6. Disable browser extensions
```

### Problem 6: Warna Custom Tidak Apply

**Cause:** Cache atau form tidak submit  
**Solution:**

```
1. Make sure to click "Save Advanced Settings"
2. Check success message muncul
3. Refresh public page
4. Clear browser cache
5. Check database value updated:
   SELECT custom_bg_color, custom_button_color
   FROM appearance WHERE user_id = YOUR_ID;
```

## 🔄 Rollback Instructions (If Needed)

### Full Rollback to v1.0:

```bash
# Stop Apache & MySQL
# Via XAMPP Control Panel: Stop buttons

# Restore database
cd C:\xampp\mysql\bin
mysql -u root -p linkmy_db < C:\backup\linkmy_backup_before_v2.sql

# Restore files
# Delete c:\xampp\htdocs\*
# Copy dari C:\backup\htdocs_backup\ ke c:\xampp\htdocs\

# Start Apache & MySQL
# Via XAMPP Control Panel: Start buttons

# Test login & basic features
```

### Partial Rollback (Keep v2.0 but revert data):

```sql
-- Revert appearance table to defaults
UPDATE appearance SET
  custom_bg_color = NULL,
  custom_button_color = NULL,
  custom_text_color = NULL,
  gradient_preset = NULL,
  profile_layout = 'centered',
  show_profile_border = 1,
  enable_animations = 1;

-- Remove categories
DELETE FROM link_categories WHERE user_id = YOUR_USER_ID;

-- Remove category references
UPDATE links SET category_id = NULL;
```

## 📊 Post-Deployment Monitoring

### Check These After 24 Hours:

```
☐ Performance Monitoring:
  ☐ Page load time < 2 seconds
  ☐ Database queries < 50ms
  ☐ No memory leaks
  ☐ No PHP errors in logs

☐ Feature Usage:
  ☐ Users accessing Advanced tab
  ☐ Gradient presets being used
  ☐ Custom colors being saved
  ☐ Layouts being changed

☐ Error Monitoring:
  ☐ Check Apache error log
  ☐ Check PHP error log
  ☐ Check MySQL slow query log
  ☐ Check browser console errors
```

## 🎉 Success Indicators

Deployment SUKSES jika:

```
✅ Tab Advanced muncul di Appearance page
✅ 12 gradient presets tampil
✅ Color pickers berfungsi
✅ 19 social icons tampil di library
✅ Live preview update real-time
✅ Settings bisa di-save tanpa error
✅ Public page menampilkan perubahan
✅ Tidak ada PHP errors di log
✅ Database queries cepat (< 10ms)
✅ Users bisa menggunakan semua fitur baru
```

## 📝 Maintenance Notes

### Database Maintenance:

```sql
-- Run setiap minggu untuk optimasi
OPTIMIZE TABLE gradient_presets;
OPTIMIZE TABLE social_icons;
OPTIMIZE TABLE link_categories;
OPTIMIZE TABLE link_analytics;

-- Check table health
CHECK TABLE appearance;
CHECK TABLE links;
```

### File Maintenance:

```bash
# Clear old session files (jika pakai file sessions)
# Delete c:\xampp\tmp\sess_*

# Clear PHP opcode cache (jika pakai)
# Restart Apache via XAMPP
```

## 🔐 Security Checklist

```
☐ Verify all inputs sanitized with htmlspecialchars()
☐ Check prepared statements used everywhere
☐ Verify file upload restrictions (2MB/5MB limits)
☐ Check session security (timeout, regeneration)
☐ Test SQL injection vectors
☐ Test XSS attack vectors
☐ Verify CSRF protection
☐ Check password hashing (bcrypt)
```

## 📞 Support Contacts

**Documentation:**

-   Full features: FEATURES_V2.md
-   Quick start: QUICK_START.md
-   Database schema: DATABASE_SCHEMA.md
-   Visual guide: VISUAL_GUIDE.md
-   Changelog: CHANGELOG.md

**Troubleshooting:**

-   Check UPDATE_SUMMARY.md untuk overview lengkap
-   Check browser console untuk JavaScript errors
-   Check Apache error log untuk PHP errors
-   Check MySQL error log untuk database errors

---

## ✅ Final Checklist

Sebelum declare deployment sukses:

```
☐ Database backup tersimpan aman
☐ File backup tersimpan aman
☐ Database updated successfully
☐ All new tables exist
☐ All new columns exist
☐ Data populated (gradients, icons, categories)
☐ Advanced tab visible
☐ All features tested
☐ Performance acceptable
☐ No errors in logs
☐ Public pages work correctly
☐ Users can save settings
☐ Preview updates real-time
☐ Documentation accessible
```

---

## 🎊 Congratulations!

Jika semua checklist di atas ✅, deployment Anda SUKSES!

**LinkMy v2.0 dengan Advanced Customization sudah LIVE! 🚀**

Selamat menggunakan fitur-fitur baru:

-   🎨 12 Gradient Presets
-   🎨 Custom Color Picker
-   📐 3 Profile Layouts
-   📱 19 Social Icons
-   ⚙️ Additional Options
-   📱 Enhanced Live Preview

---

**Version:** 2.0.0  
**Deployment Date:** November 15, 2025  
**Status:** Production Ready ✅
