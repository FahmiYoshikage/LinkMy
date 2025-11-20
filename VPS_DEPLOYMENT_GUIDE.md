# VPS Deployment Guide - Boxed Layout Feature

## 🚨 Masalah yang Terjadi

### Symptoms:

1. ❌ Profile menampilkan background putih (seharusnya gradient pink)
2. ❌ Link tidak muncul di profile
3. ❌ Boxed layout tidak aktif meskipun sudah disave

### Root Cause:

-   **Database VPS belum diupdate** dengan kolom boxed_layout
-   **View `v_public_page_data` belum diupdate** untuk include kolom baru
-   Data tersimpan di tabel `appearance` tapi **view tidak meload kolom tersebut**

---

## 🔧 Solusi Deployment ke VPS

### Option 1: Automated Script (Recommended)

1. **Upload files ke VPS**:

```bash
# Di local (Windows PowerShell)
scp deploy_boxed_layout.sh user@your-vps:/path/to/linkmy/
scp database_add_boxed_layout.sql user@your-vps:/path/to/linkmy/
scp database_update_view_boxed_layout.sql user@your-vps:/path/to/linkmy/
```

2. **SSH ke VPS dan jalankan**:

```bash
ssh user@your-vps
cd /path/to/linkmy/
chmod +x deploy_boxed_layout.sh
./deploy_boxed_layout.sh
```

Script akan otomatis:

-   ✅ Check apakah kolom sudah ada
-   ✅ Add kolom jika belum ada
-   ✅ Update view v_public_page_data
-   ✅ Verify installation

---

### Option 2: Manual SQL (Alternative)

Jika script tidak bisa jalan, jalankan SQL manual:

**Step 1: SSH ke VPS**

```bash
ssh user@your-vps
mysql -u root -p linkmy_db
```

**Step 2: Add Boxed Layout Columns**

```sql
-- Check if columns exist first
SHOW COLUMNS FROM appearance LIKE '%boxed%';

-- If empty, run this:
ALTER TABLE `appearance`
ADD COLUMN `boxed_layout` TINYINT(1) DEFAULT 0 COMMENT '0=full width, 1=boxed mode',
ADD COLUMN `outer_bg_type` VARCHAR(20) DEFAULT 'gradient' COMMENT 'color, gradient, image',
ADD COLUMN `outer_bg_color` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Outer background color',
ADD COLUMN `outer_bg_gradient_start` VARCHAR(50) DEFAULT '#667eea' COMMENT 'Gradient start color',
ADD COLUMN `outer_bg_gradient_end` VARCHAR(50) DEFAULT '#764ba2' COMMENT 'Gradient end color',
ADD COLUMN `outer_bg_image` VARCHAR(255) DEFAULT NULL COMMENT 'Path to background image',
ADD COLUMN `container_bg_color` VARCHAR(50) DEFAULT '#ffffff' COMMENT 'Inner container background',
ADD COLUMN `container_max_width` INT DEFAULT 480 COMMENT 'Max width in pixels for boxed container',
ADD COLUMN `container_border_radius` INT DEFAULT 30 COMMENT 'Border radius in pixels',
ADD COLUMN `container_shadow` TINYINT(1) DEFAULT 1 COMMENT 'Show shadow on container';
```

**Step 3: Update View**

```sql
-- Drop and recreate view
DROP VIEW IF EXISTS v_public_page_data;

CREATE VIEW v_public_page_data AS
SELECT
    u.user_id,
    u.username,
    u.page_slug,
    a.profile_title,
    a.bio,
    a.profile_pic_filename,
    a.bg_image_filename,
    a.theme_name,
    a.button_style,
    a.font_family,
    a.custom_bg_color,
    a.custom_button_color,
    a.custom_text_color,
    a.custom_link_text_color,
    a.gradient_preset,
    a.profile_layout,
    a.container_style,
    a.show_profile_border,
    a.enable_animations,
    a.enable_glass_effect,
    a.shadow_intensity,
    a.enable_categories,
    -- Boxed Layout columns
    a.boxed_layout,
    a.outer_bg_type,
    a.outer_bg_color,
    a.outer_bg_gradient_start,
    a.outer_bg_gradient_end,
    a.outer_bg_image,
    a.container_bg_color,
    a.container_max_width,
    a.container_border_radius,
    a.container_shadow
FROM users u
INNER JOIN appearance a ON u.user_id = a.user_id
WHERE u.email_verified = 1;
```

**Step 4: Verify**

```sql
-- Check view columns
DESCRIBE v_public_page_data;

-- Check your data
SELECT
    user_id,
    boxed_layout,
    outer_bg_type,
    container_max_width,
    container_bg_color
FROM appearance
WHERE user_id = YOUR_USER_ID;
```

---

## 🔄 Full Deployment Workflow

### Cara yang Benar untuk Deploy ke VPS:

```bash
# 1. Di Local (Windows)
git add .
git commit -m "Add boxed layout feature"
git push origin master

# 2. Di VPS
cd /var/www/html  # or your web root
git pull origin master

# 3. Run database updates (PENTING!)
mysql -u root -p linkmy_db < database_add_boxed_layout.sql
mysql -u root -p linkmy_db < database_update_view_boxed_layout.sql

# 4. Restart services
sudo systemctl restart apache2  # or nginx
sudo systemctl restart php8.1-fpm  # adjust PHP version

# 5. Clear cache
sudo rm -rf /tmp/php_*
```

---

## 🐛 Debugging: Kenapa Link Tidak Muncul

### Check 1: Verify Links Exist

```sql
SELECT * FROM links WHERE user_id = YOUR_USER_ID;
```

### Check 2: Check profile.php Query

Di `profile.php` ada query untuk fetch links. Pastikan tidak ada error.

Temporary debug: Tambahkan di `profile.php` setelah line `$user_data = mysqli_fetch_assoc($result);`:

```php
// DEBUG: Check what data is loaded
echo '<pre style="background:black;color:lime;padding:20px;margin:20px;">';
echo "DEBUG INFO:\n";
echo "Boxed Layout: " . ($boxed_layout ?? 'NULL') . "\n";
echo "Outer BG Type: " . ($outer_bg_type ?? 'NULL') . "\n";
echo "Container BG: " . ($container_bg_color ?? 'NULL') . "\n";
echo "Container Width: " . ($container_max_width ?? 'NULL') . "\n";
echo "\nUser Data Keys:\n";
print_r(array_keys($user_data));
echo '</pre>';
```

### Check 3: Links Query

Cari di `profile.php` bagian fetch links:

```php
// Fetch links
$query = "SELECT * FROM links WHERE user_id = ? AND is_visible = 1 ORDER BY display_order, created_at";
$links_result = execute_query($query, [$user_id], 'i');
```

Pastikan query ini ada dan tidak error.

---

## 🎯 Expected Data in Database

Setelah deployment berhasil, data di VPS harus seperti ini:

```sql
-- Check your appearance data
SELECT
    user_id,
    boxed_layout,
    outer_bg_type,
    outer_bg_color,
    outer_bg_gradient_start,
    outer_bg_gradient_end,
    container_bg_color,
    container_max_width,
    container_border_radius,
    container_shadow,
    gradient_preset  -- Your current background
FROM appearance
WHERE user_id = YOUR_USER_ID;
```

Should return something like:

```
user_id: 1
boxed_layout: 1
outer_bg_type: gradient
outer_bg_gradient_start: #667eea
outer_bg_gradient_end: #764ba2
container_bg_color: #ffffff (or rgba if using glass)
container_max_width: 480
container_border_radius: 30
container_shadow: 1
gradient_preset: Pink Lemonade (your current theme)
```

---

## 🔍 Troubleshooting Common Issues

### Issue: Container background putih padahal set ke "Current BG"

**Cause**: JavaScript `setContainerBg('current')` tidak save value yang benar

**Fix**: Saat save, pastikan value yang tersimpan adalah:

-   Jika pakai gradient: `rgba(255,255,255,0.95)` atau solid color
-   Jika pakai custom color: color value itu sendiri

**Manual fix** di database:

```sql
-- Set container bg to match your gradient (semi-transparent white)
UPDATE appearance
SET container_bg_color = 'rgba(255,255,255,0.95)'
WHERE user_id = YOUR_USER_ID;
```

### Issue: Links tidak muncul

**Possible causes**:

1. Links `is_visible = 0`
2. PHP error di query
3. View tidak return data dengan benar

**Debug**:

```sql
-- Check links
SELECT id, title, url, is_visible, display_order
FROM links
WHERE user_id = YOUR_USER_ID
ORDER BY display_order;

-- Check if view returns your user
SELECT * FROM v_public_page_data
WHERE page_slug = 'your-username';
```

---

## 📝 Deployment Checklist

### Before Deploy:

-   [ ] Commit all changes locally
-   [ ] Push to GitHub
-   [ ] Backup VPS database: `mysqldump -u root -p linkmy_db > backup.sql`

### After Pull on VPS:

-   [ ] Run `database_add_boxed_layout.sql`
-   [ ] Run `database_update_view_boxed_layout.sql`
-   [ ] Verify columns: `DESCRIBE appearance;`
-   [ ] Verify view: `DESCRIBE v_public_page_data;`
-   [ ] Restart web server
-   [ ] Restart PHP-FPM
-   [ ] Clear browser cache
-   [ ] Test profile page

### Verification:

-   [ ] Profile displays with boxed layout
-   [ ] Outer background shows gradient
-   [ ] Container has correct background
-   [ ] Links are visible
-   [ ] Responsive on mobile

---

## 🚀 Quick Commands for VPS

```bash
# Connect to VPS
ssh your-user@your-vps-ip

# Go to web root
cd /var/www/html

# Pull latest code
git pull origin master

# Run SQL updates
mysql -u root -p linkmy_db < database_add_boxed_layout.sql
mysql -u root -p linkmy_db < database_update_view_boxed_layout.sql

# Verify
mysql -u root -p linkmy_db -e "DESCRIBE v_public_page_data;"

# Restart services
sudo systemctl restart apache2 php8.1-fpm

# Check logs if error
tail -f /var/log/apache2/error.log
```

---

## 📞 If Still Not Working

1. **Check PHP error log** on VPS:

```bash
tail -f /var/log/apache2/error.log
# or
tail -f /var/log/nginx/error.log
```

2. **Enable PHP errors temporarily** in `profile.php`:

```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

3. **Check MySQL slow query log**

4. **Verify file permissions**:

```bash
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
```

---

## 🎯 Summary

**Root cause**: Database structure on VPS not updated

**Solution**:

1. Run SQL migrations on VPS
2. Update view to include new columns
3. Restart services
4. Clear cache

**Files to run on VPS**:

-   `database_add_boxed_layout.sql`
-   `database_update_view_boxed_layout.sql`

**Or use automated script**:

-   `deploy_boxed_layout.sh`
