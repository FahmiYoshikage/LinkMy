# 🔗 LinkMy v2.1 - Advanced Customization Platform

> A powerful link-in-bio platform with advanced customization features

[![Version](https://img.shields.io/badge/version-2.1.0-blue.svg)](https://github.com/yourusername/linkmy)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg?logo=docker)](https://www.docker.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**LinkMy** adalah aplikasi web berbasis PHP untuk membuat halaman profil link pribadi yang dapat disesuaikan, mirip dengan Linktree atau Linktr.ee. Project ini dibuat dengan **PHP Native**, **MySQL**, dan **Bootstrap 5.3**.

## ✨ What's New in v2.0

### 🎨 Advanced Customization Features

-   **12 Gradient Presets** - Beautiful pre-designed gradients ready to use
-   **Custom Color Picker** - Full control over background, button, and text colors
-   **Profile Layouts** - Choose from Centered, Left Aligned, or Minimal layouts
-   **Social Icons Library** - 19 social media icons with brand colors
-   **Enhanced Options** - Toggle profile border and animations
-   **Live Preview** - See changes in real-time before publishing

### 🗄️ Database Enhancements

-   New tables: `gradient_presets`, `social_icons`, `link_categories`, `link_analytics`
-   Enhanced `appearance` table with 7 new customization columns
-   Improved `links` table with category support
-   Optimized views and indexes for better performance

## 🚀 Quick Start

### Option 1: Docker (Recommended) 🐳

**Paling mudah dan cepat!** Tidak perlu install PHP, MySQL, atau Apache.

```bash
# Windows
docker-setup.bat

# Linux/Mac
chmod +x docker-setup.sh
./docker-setup.sh
```

**Akses aplikasi:**

-   Web: http://localhost:83
-   phpMyAdmin: http://localhost:8083

📖 **Panduan lengkap**: [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md) | [DOCKER.md](DOCKER.md)

---

### Option 2: Manual Installation

**Prerequisites:**

-   PHP 7.4 or higher
-   MySQL 5.7+ or MariaDB 10.2+
-   Apache/Nginx with mod_rewrite
-   50MB+ disk space

**Installation Steps:**

1. **Setup database**

    ```bash
    # Create database
    mysql -u root -p -e "CREATE DATABASE linkmy_db"

    # Import initial schema
    mysql -u root -p linkmy_db < database.sql

    # Apply v2.1 updates
    mysql -u root -p linkmy_db < database_update_v2.1.sql
    ```

2. **Configure database connection**
   Edit `config/db.php` dengan kredensial database Anda

3. **Set folder permissions**

    ```bash
    chmod 755 uploads/
    chmod 755 uploads/profile_pics/
    chmod 755 uploads/backgrounds/
    ```

4. **Access the application**
    ```
    http://localhost/admin/appearance.php
    ```
    Klik tab "**Advanced**" untuk mengakses fitur baru!

## 📚 Documentation

### Essential Guides (NEW!)

-   📖 [**DEPLOYMENT.md**](DEPLOYMENT.md) - Complete deployment instructions
-   🚀 [**QUICK_START.md**](QUICK_START.md) - Quick start guide for users
-   📋 [**FEATURES_V2.md**](FEATURES_V2.md) - Full feature documentation
-   📊 [**DATABASE_SCHEMA.md**](DATABASE_SCHEMA.md) - Database structure and relationships
-   🎨 [**VISUAL_GUIDE.md**](VISUAL_GUIDE.md) - Visual guide with UI mockups
-   📝 [**CHANGELOG.md**](CHANGELOG.md) - Version history and changes
-   📄 [**UPDATE_SUMMARY.md**](UPDATE_SUMMARY.md) - Summary of v2.0 updates

---

## 📋 Fitur Lengkap

### ✅ Fitur Core (v1.0)

1. **Penggunaan HTML, PHP, dan Database** - Struktur lengkap dengan MVC sederhana
2. **Penggunaan CSS** - Custom CSS untuk styling unik
3. **Penggunaan Framework Bootstrap** - Bootstrap 5.3 untuk UI responsif
4. **Penggunaan Table Relasi** - Relasi 1-to-N (users-links) dan 1-to-1 (users-appearance)
5. **Implementasi View Database** - `v_public_page_data` untuk query efisien
6. **Implementasi Insert Database** - Tambah user, link, dan appearance
7. **Implementasi Update Database** - Edit link, update appearance, ganti password
8. **Implementasi Delete Database** - Hapus link dan akun
9. **Hosting Website** - Siap di-deploy ke shared hosting
10. **Upload Foto** - Upload foto profil dan background image
11. **Penggunaan Session Login** - Sistem autentikasi lengkap
12. **Penggunaan Session Logout** - Destroy session dengan aman
13. **Drag & Drop Reordering** - Ubah urutan link dengan drag & drop
14. **Link Analytics** - Tracking jumlah klik per link
15. **Icon Support** - Bootstrap Icons untuk setiap link
16. **Theme Customization** - 3 tema (Light, Dark, Gradient)
17. **Button Style Options** - 3 gaya tombol (Rounded, Sharp, Pill)
18. **Custom Bio** - Bio/deskripsi profil yang dapat disesuaikan
19. **Background Image** - Upload custom background untuk halaman publik
20. **URL Rewriting** - URL cantik menggunakan .htaccess
21. **Responsive Design** - Mobile-friendly di semua device

### ✨ Fitur Advanced (v2.0 - NEW!)

22. **12 Gradient Presets** ⭐

    -   Purple Dream, Ocean Blue, Sunset Orange
    -   Fresh Mint, Pink Lemonade, Royal Purple
    -   Fire Blaze, Emerald Water, Candy Shop
    -   Cool Blues, Warm Flame, Deep Sea

23. **Custom Color System** 🎨

    -   Background color picker
    -   Button color picker
    -   Text color picker
    -   Hex value display & sync
    -   Real-time preview

24. **Profile Layouts** 📐

    -   Centered: Classic centered layout
    -   Left Aligned: Modern left-side layout
    -   Minimal: Compact minimal design

25. **Social Icons Library** 📱

    -   19 social media platforms
    -   Brand-colored icons
    -   Click-to-copy functionality
    -   Instagram, Facebook, Twitter/X, LinkedIn
    -   GitHub, YouTube, TikTok, WhatsApp
    -   Telegram, Discord, Snapchat, Pinterest
    -   Reddit, Twitch, Spotify, SoundCloud
    -   Medium, Behance, Dribbble

26. **Advanced Options** ⚙️

    -   Show/hide profile border
    -   Enable/disable animations
    -   Layout customization

27. **Link Categories** 📂

    -   Organize links by category
    -   Custom category icons & colors
    -   User-owned categories

28. **Enhanced Analytics** 📊 (Bonus)
    -   Link click tracking
    -   Referrer information
    -   User agent logging
    -   Geographic data (optional)

---

## 🖼️ Screenshots

### Advanced Customization Interface (NEW!)

```
┌─────────────────────────────────────────────┐
│  Profile │ Theme │ Media │ Advanced [New!] │
├─────────────────────────────────────────────┤
│  🎨 Gradient Backgrounds (12 Presets)       │
│  ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐  │
│  │ ✓   │ │     │ │     │ │     │ │     │  │
│  │████ │ │████ │ │████ │ │████ │ │████ │  │
│  │🟣🟣 │ │🔵🔵 │ │🟠🟠 │ │🟢🟢 │ │🔴🔴 │  │
│  └─────┘ └─────┘ └─────┘ └─────┘ └─────┘  │
│  Purple   Ocean    Sunset   Fresh   Pink    │
│                                              │
│  🎨 Custom Colors (Override Presets)        │
│  [Background] [Button] [Text]               │
│   #667eea     #4c6ef5   #ffffff             │
│                                              │
│  📐 Profile Layout                          │
│  [Centered ✓] [Left Aligned] [Minimal]     │
│                                              │
│  ⚙️ Additional Options                     │
│  ☑ Show Profile Border                     │
│  ☑ Enable Animations                       │
│                                              │
│  📱 Social Icons Library (Click to Copy)    │
│  [Instagram] [Facebook] [Twitter/X]         │
│  [LinkedIn] [GitHub] [YouTube] [TikTok]     │
└─────────────────────────────────────────────┘
```

### Live Preview Panel

```
┌──────────────┐
│ 📱 Preview   │
├──────────────┤
│  ┌────────┐  │
│  │ Phone  │  │  ← Real-time updates
│  │ Mockup │  │     as you customize
│  │        │  │
│  │  😊    │  │  ← Profile picture
│  │ John   │  │  ← Display name
│  │ Bio... │  │  ← Bio text
│  │        │  │
│  │ [Link] │  │  ← Your links
│  │ [Link] │  │     with colors
│  │ [Link] │  │     and styles
│  └────────┘  │
└──────────────┘
```

---

## 🗂️ Struktur Folder Lengkap

```
/LinkMy/
│
├── 📄 index.php                      # Halaman Login
├── 📄 register.php                   # Form Registrasi
├── 📄 logout.php                     # Proses Logout
├── 📄 profile.php                    # Halaman Publik User
├── 📄 redirect.php                   # Track Link Clicks
├── 📄 .htaccess                      # URL Rewriting
├── 📄 README.md                      # Dokumentasi Utama
├── 📄 database.sql                   # Database Schema v1.0
├── 📄 database_update_v2.sql         # v2.0 Migration (NEW!)
│
├── 📁 admin/                         # Area Admin (Protected)
│   ├── 📄 dashboard.php               # CRUD Links
│   ├── 📄 appearance.php              # Customization Hub (UPDATED!)
│   └── 📄 settings.php                # User Settings
│
├── 📁 config/
│   ├── 📄 db.php                      # Database Connection
│   └── 📄 auth_check.php              # Session Guard
│
├── 📁 assets/
│   ├── 📁 bootstrap-5.3.8-dist/       # Bootstrap Framework
│   │   ├── 📁 css/
│   │   │   └── 📄 bootstrap.min.css
│   │   └── 📁 js/
│   │       └── 📄 bootstrap.bundle.min.js
│   ├── 📁 css/
│   │   ├── 📄 admin.css               # Admin Custom CSS
│   │   └── 📄 public.css              # Public Page CSS
│   └── 📁 js/
│       └── 📄 admin.js                # Drag & Drop Logic
│
├── 📁 uploads/                       # Upload Directory (755)
│   ├── 📁 profile_pics/
│   ├── 📁 backgrounds/
│   └── 📁 folder_pics/
│
└── 📁 Documentation/                 # Complete Docs (NEW!)
    ├── 📄 DEPLOYMENT.md               # Deployment Guide
    ├── 📄 QUICK_START.md              # Quick Start
    ├── 📄 FEATURES_V2.md              # Feature Docs
    ├── 📄 DATABASE_SCHEMA.md          # DB Schema
    ├── 📄 VISUAL_GUIDE.md             # Visual Guide
    ├── 📄 CHANGELOG.md                # Version History
    └── 📄 UPDATE_SUMMARY.md           # Update Summary
```

---

## 📊 Database Schema (Enhanced in v2.0)

### Core Tables

```sql
users                           appearance (Enhanced)
├── user_id (PK)               ├── appearance_id (PK)
├── username                   ├── user_id (FK)
├── email                      ├── profile_title
├── password                   ├── bio
└── page_slug                  ├── theme_name
                               ├── button_style
                               ├── custom_bg_color      ← NEW
                               ├── custom_button_color  ← NEW
                               ├── custom_text_color    ← NEW
                               ├── gradient_preset      ← NEW
                               ├── profile_layout       ← NEW
                               ├── show_profile_border  ← NEW
                               └── enable_animations    ← NEW

links (Enhanced)                link_categories (NEW)
├── link_id (PK)               ├── category_id (PK)
├── user_id (FK)               ├── user_id (FK)
├── category_id (FK) ← NEW     ├── category_name
├── title                      ├── category_icon
├── url                        └── category_color
├── icon_class
└── display_order

gradient_presets (NEW)          social_icons (NEW)
├── preset_id (PK)             ├── icon_id (PK)
├── preset_name                ├── platform_name
├── gradient_css               ├── icon_class
└── color_stops                ├── brand_color
                               └── base_url

link_analytics (NEW - Bonus)
├── analytics_id (PK)
├── link_id (FK)
├── clicked_at
├── referrer
└── user_agent
```

### Total Statistics

-   **Tables**: 8 (4 new in v2.0)
-   **Views**: 1 enhanced view
-   **Foreign Keys**: 7 relationships
-   **Indexes**: 12 optimized indexes

---

## ⚙️ Instalasi Lengkap

### 1. Persiapan Environment

**Requirements:**

-   PHP 7.4 atau lebih tinggi
-   MySQL 5.7 atau MariaDB 10.2+
-   Apache Server dengan mod_rewrite enabled
-   Web Browser modern

### 2. Clone/Download Project

```bash
# Clone repository (jika menggunakan Git)
git clone [repository-url] LinkMy

# Atau extract ZIP file ke folder web server
# Untuk XAMPP: C:\xampp\htdocs\LinkMy
# Untuk LAMP: /var/www/html/LinkMy
```

### 3. Download Bootstrap 5.3

**Download Bootstrap:**

1. Kunjungi: https://getbootstrap.com/docs/5.3/getting-started/download/
2. Download versi **Compiled CSS and JS**
3. Extract file bootstrap.min.css ke `assets/bootstrap-5.3/css/`
4. Extract file bootstrap.bundle.min.js ke `assets/bootstrap-5.3/js/`

**Struktur yang benar:**

```
/assets/bootstrap-5.3/
  ├── css/
  │   └── bootstrap.min.css
  └── js/
      └── bootstrap.bundle.min.js
```

### 4. Setup Database

**Via phpMyAdmin:**

1. Buka `http://localhost/phpmyadmin`
2. Klik tab **SQL**
3. Copy semua isi file `database.sql`
4. Paste dan klik **Go**

**Via MySQL Command Line:**

```bash
mysql -u root -p < database.sql
```

### 5. Konfigurasi Database Connection

Edit file `config/db.php`:

```php
define('DB_HOST', 'localhost');    # Biasanya localhost
define('DB_USER', 'root');         # Username MySQL
define('DB_PASS', '');             # Password MySQL (kosong di XAMPP)
define('DB_NAME', 'linkmy_db');    # Nama database
```

### 6. Set Permissions (Linux/Mac)

```bash
chmod 755 /path/to/LinkMy
chmod 777 /path/to/LinkMy/uploads
chmod 777 /path/to/LinkMy/uploads/profile_pics
chmod 777 /path/to/LinkMy/uploads/backgrounds
```

### 7. Configure .htaccess

Edit file `.htaccess` sesuai lokasi folder:

```apache
# Jika di root domain (http://localhost/)
RewriteBase /

# Jika di subfolder (http://localhost/LinkMy/)
RewriteBase /LinkMy/
```

### 8. Test Instalasi

Buka browser dan akses:

-   **Homepage:** `http://localhost/LinkMy/`
-   **Login Default:**
    -   Username: `admin`
    -   Password: `admin123`

---

## 🚀 Cara Menggunakan

### Untuk Admin

1. **Login** ke dashboard menggunakan username dan password
2. **Dashboard** - Kelola semua link Anda:
    - Klik "Tambah Link" untuk menambah link baru
    - Drag & drop untuk mengubah urutan
    - Klik "Edit" untuk mengubah link
    - Klik "Hapus" untuk menghapus link
3. **Appearance** - Kustomisasi tampilan:
    - Upload foto profil
    - Upload background image
    - Pilih tema (Light/Dark/Gradient)
    - Atur gaya tombol
4. **Settings** - Kelola akun:
    - Ganti password
    - Update email
    - Hapus akun (jika perlu)
5. **View Page** - Lihat halaman publik Anda

### Untuk Pengunjung

1. Akses halaman publik user via: `http://localhost/LinkMy/[username]`
    - Contoh: `http://localhost/LinkMy/admin`
2. Klik link yang tersedia
3. Otomatis redirect ke URL tujuan dan tercatat di analytics

---

## 📊 Database Schema

### Tabel `users`

```sql
- user_id (PK, AUTO_INCREMENT)
- username (UNIQUE)
- password_hash
- page_slug (UNIQUE)
- email
- created_at
```

### Tabel `links` (Relasi 1-to-N dengan users)

```sql
- link_id (PK, AUTO_INCREMENT)
- user_id (FK -> users.user_id)
- title
- url
- order_index
- icon_class
- click_count
- is_active
- created_at
```

### Tabel `appearance` (Relasi 1-to-1 dengan users)

```sql
- appearance_id (PK, AUTO_INCREMENT)
- user_id (FK -> users.user_id, UNIQUE)
- profile_title
- bio
- profile_pic_filename
- bg_image_filename
- theme_name
- button_style
- font_family
- updated_at
```

### View `v_public_page_data`

```sql
SELECT u.*, a.*, l.*
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
LEFT JOIN links l ON u.user_id = l.user_id
WHERE l.is_active = 1
ORDER BY u.user_id, l.order_index
```

---

## 🐳 Deploy dengan Docker (Recommended)

### Quick Start dengan Docker

```bash
# Windows - Jalankan script setup
docker-setup.bat

# Linux/Mac - Jalankan script setup
chmod +x docker-setup.sh
./docker-setup.sh
```

### Manual Docker Deployment

```bash
# Build dan start semua services
docker-compose up -d --build

# Cek status containers
docker-compose ps

# View logs
docker-compose logs -f

# Stop containers
docker-compose down
```

### Akses Aplikasi

-   **Web Application**: http://localhost:83
-   **phpMyAdmin**: http://localhost:8083
-   **MySQL**: localhost:3307

### Database Credentials (Docker)

```
Host:     db (dalam container) atau localhost:3307 (dari host)
Database: linkmy_db
User:     linkmy_user
Password: linkmy_password
```

**📖 Dokumentasi lengkap Docker**: Lihat [DOCKER.md](DOCKER.md)

---

## 🌐 Deploy ke Hosting (Traditional)

### 1. Persiapan File

1. Compress semua file project ke ZIP
2. Exclude folder `node_modules` (jika ada)
3. Include semua folder: `admin/`, `config/`, `assets/`, `uploads/`

### 2. Upload via FTP/cPanel

**Via cPanel File Manager:**

1. Login ke cPanel hosting
2. Buka **File Manager**
3. Navigasi ke `public_html/`
4. Upload ZIP file
5. Extract ZIP file

**Via FTP:**

```bash
# Menggunakan FileZilla atau FTP client lain
# Upload semua file ke folder public_html/
```

### 3. Create Database di Hosting

1. Login ke **cPanel**
2. Buka **MySQL Database**
3. Buat database baru: `namadomain_linkmy`
4. Buat user dan berikan privilege
5. Import `database.sql` via phpMyAdmin

### 4. Update Database Config

Edit `config/db.php` dengan kredensial hosting:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'namadomain_user');
define('DB_PASS', 'password_hosting');
define('DB_NAME', 'namadomain_linkmy');
```

### 5. Update .htaccess

```apache
# Jika domain langsung (https://linkmy.com/)
RewriteBase /

# Update ErrorDocument
ErrorDocument 404 /index.php
```

### 6. Set Permissions

Via cPanel File Manager:

-   `uploads/` → 755 atau 777
-   `uploads/profile_pics/` → 755 atau 777
-   `uploads/backgrounds/` → 755 atau 777

### 7. Test Website

-   Akses `https://yourdomain.com/`
-   Test login, upload, dan semua fitur
-   Test halaman publik: `https://yourdomain.com/username`

---

## 🔒 Keamanan

### Implementasi Keamanan:

1. **Password Hashing** - Menggunakan `password_hash()` dengan bcrypt
2. **Prepared Statements** - Mencegah SQL Injection
3. **Session Management** - Session timeout dan validation
4. **File Upload Validation** - Cek tipe dan ukuran file
5. **XSS Protection** - `htmlspecialchars()` untuk output
6. **CSRF Protection** - Session-based validation
7. **Directory Protection** - `.htaccess` untuk folder config
8. **Input Validation** - Server-side validation untuk semua input

### Tips Tambahan:

-   Ganti password default admin setelah instalasi
-   Gunakan HTTPS di production
-   Backup database secara berkala
-   Update PHP dan MySQL ke versi terbaru
-   Monitor log error secara rutin

---

## 🐛 Troubleshooting

### Error: "Page not found"

-   **Solusi:** Pastikan mod_rewrite enabled di Apache
-   Check `.htaccess` RewriteBase sesuai folder

### Error: "Upload failed"

-   **Solusi:** Set permission folder `uploads/` ke 777
-   Check `php.ini`: `upload_max_filesize` dan `post_max_size`

### Error: "Database connection failed"

-   **Solusi:** Check kredensial di `config/db.php`
-   Pastikan MySQL service running

### Drag & Drop tidak bekerja

-   **Solusi:** Pastikan file `assets/js/admin.js` ter-load
-   Check browser console untuk error JavaScript

### Bootstrap tidak ter-load

-   **Solusi:** Download Bootstrap 5.3 dan letakkan di folder yang benar
-   Check path di `<link>` dan `<script>` tags

---

## 📝 Checklist Fitur untuk Penilaian

### Fitur Wajib:

-   [x] HTML, PHP, dan Database
-   [x] CSS Custom
-   [x] Bootstrap Framework
-   [x] Table Relasi (users → links, users → appearance)
-   [x] Database View (v_public_page_data)
-   [x] Insert (Register, Add Link)
-   [x] Update (Edit Link, Update Appearance)
-   [x] Delete (Delete Link, Delete Account)
-   [x] Hosting Ready
-   [x] Upload Foto (Profile Pic, Background)
-   [x] Session Login
-   [x] Session Logout

### Fitur Tambahan:

-   [x] Drag & Drop Reordering
-   [x] Link Analytics (Click Counter)
-   [x] Icon Support
-   [x] Theme Customization
-   [x] Button Style Options
-   [x] Custom Bio
-   [x] Background Image Upload
-   [x] URL Rewriting
-   [x] Responsive Design

---

## 🎯 How to Use v2.0 Advanced Features

### Step 1: Apply Database Update

```bash
# Open phpMyAdmin or MySQL CLI
mysql -u root -p linkmy_db < database_update_v2.sql

# Verify installation
# You should see 12 gradient presets and 19 social icons
```

### Step 2: Access Advanced Tab

```
1. Login to your LinkMy dashboard
2. Go to: admin/appearance.php
3. Click the "Advanced" tab (with "New" badge)
4. You'll see 5 new sections!
```

### Step 3: Customize Your Profile

**🎨 Gradient Backgrounds**

-   Click any of the 12 gradient preset cards
-   See instant preview on the right
-   Click "Save Changes" to apply

**🖌️ Custom Colors**

-   Use color pickers to override gradient
-   Pick: Background, Button, and Text colors
-   See hex codes update in real-time

**📐 Profile Layout**

-   Choose from 3 layout options:
    -   **Centered**: Classic centered design
    -   **Left Aligned**: Modern asymmetric layout
    -   **Minimal**: Clean minimal style
-   Click card to select, preview updates instantly

**⚙️ Additional Options**

-   Toggle "Show Profile Border" for card effect
-   Toggle "Enable Animations" for smooth transitions

**📱 Social Icons**

-   Browse 19 social media icons
-   Click icon to copy class name
-   Paste into link icon field in Dashboard

### Step 4: View Your Public Profile

```
Visit: http://localhost/profile.php?u=yourusername
```

---

## 🔐 Security Features

-   ✅ Password hashing with `password_hash()` (bcrypt)
-   ✅ SQL injection prevention (prepared statements)
-   ✅ XSS protection (`htmlspecialchars()`)
-   ✅ CSRF protection with tokens
-   ✅ Session management with timeout
-   ✅ File upload validation (type, size, extension)
-   ✅ Input sanitization on all forms
-   ✅ Foreign key constraints for data integrity

---

## 🗺️ Roadmap

### ✅ Completed (v2.0)

-   [x] 12 Gradient Presets
-   [x] Custom Color Picker System
-   [x] Profile Layout Selector
-   [x] Social Icons Library (19 platforms)
-   [x] Enhanced Database Schema
-   [x] Link Categories Table
-   [x] Link Analytics Table
-   [x] Comprehensive Documentation (6+ files)

### 🚀 Coming Next (v2.1)

-   [ ] Category Management Interface
-   [ ] Link Analytics Dashboard with Charts
-   [ ] Font Family Selector (Google Fonts)
-   [ ] Background Pattern Library
-   [ ] QR Code Generator for Profile
-   [ ] Social Share Buttons
-   [ ] User-uploaded custom icons

### 🔮 Future (v2.2+)

-   [ ] Link Scheduling (publish on specific date/time)
-   [ ] A/B Testing for Links
-   [ ] Custom Domain Support
-   [ ] Team Collaboration Features
-   [ ] API Access for Developers
-   [ ] Mobile App (iOS & Android)
-   [ ] Theme Marketplace
-   [ ] Export/Import Settings

---

## 🤝 Contributing

Contributions are welcome! Jika Anda ingin berkontribusi:

1. Fork repository ini
2. Buat branch fitur baru (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

**Areas yang membutuhkan kontribusi:**

-   [ ] Unit tests untuk PHP functions
-   [ ] Mobile app development
-   [ ] UI/UX improvements
-   [ ] Performance optimization
-   [ ] Security enhancements
-   [ ] Documentation translations

---

## 📞 Support & Community

### Get Help

-   📖 Read the [Quick Start Guide](QUICK_START.md)
-   📋 Check [Full Documentation](FEATURES_V2.md)
-   🐛 Report bugs via GitHub Issues
-   💬 Join our Discord community (coming soon)

### Troubleshooting

Jika mengalami masalah:

1. **Database errors**: Check [DEPLOYMENT.md](DEPLOYMENT.md)
2. **Missing features**: Ensure `database_update_v2.sql` was run
3. **Permission errors**: Set folders to 755, files to 644
4. **Styling issues**: Clear browser cache, hard reload (Ctrl+F5)

---

## 👨‍💻 Developer

Project ini dibuat sebagai tugas akhir mata kuliah Pemrograman Web dan dikembangkan menjadi platform yang lebih advance.

**Tech Stack:**

-   **Frontend**: HTML5, CSS3, Bootstrap 5.3.8, Bootstrap Icons 1.11, JavaScript (Vanilla)
-   **Backend**: PHP 8.2+ (Native, No Framework)
-   **Database**: MySQL 5.7+ / MariaDB 10.4+
-   **Server**: Apache 2.4+ dengan mod_rewrite
-   **Version Control**: Git

**Code Statistics (v2.0):**

-   **Total Lines**: 3000+ lines of code
-   **PHP**: 1500+ lines
-   **CSS**: 800+ lines
-   **JavaScript**: 400+ lines
-   **SQL**: 300+ lines
-   **Documentation**: 2500+ lines (6 markdown files)

---

## 📄 License

Project ini dibuat untuk keperluan edukasi dan dapat digunakan secara bebas dengan **MIT License**.

```
MIT License

Copyright (c) 2024 LinkMy

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
```

---

## 🙏 Credits & Acknowledgments

**Frameworks & Libraries:**

-   **Bootstrap 5.3.8** - https://getbootstrap.com/
-   **Bootstrap Icons 1.11** - https://icons.getbootstrap.com/
-   **PHP** - https://www.php.net/
-   **MySQL** - https://www.mysql.com/
-   **PHPMailer** - https://github.com/PHPMailer/PHPMailer

**Inspiration:**

-   **Linktree** - https://linktr.ee/
-   **Bio.link** - https://bio.link/
-   **Beacons** - https://beacons.ai/

**Special Thanks:**

-   Dosen Pembimbing Pemrograman Web
-   Beta testers and early adopters
-   Open source community

---

## ⭐ Star This Project

If you like LinkMy v2.0, please give it a star! ⭐

It helps others discover this project and motivates us to keep improving.

---

## 📈 Project Status

![GitHub last commit](https://img.shields.io/badge/last%20commit-November%202024-brightgreen)
![GitHub issues](https://img.shields.io/badge/issues-0%20open-success)
![GitHub pull requests](https://img.shields.io/badge/PRs-welcome-brightgreen)
![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)

**Current Version**: v2.0.0 (November 15, 2024)
**Status**: ✅ Production Ready
**Last Updated**: November 15, 2024

---

**🔗 LinkMy v2.0 - Your Personal Link Hub, Elevated**

**Made with ❤️ in Indonesia**

© 2024 LinkMy - Personal Link Hub. All rights reserved.
