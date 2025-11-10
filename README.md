# LinkMy - Personal Link Hub 🔗

**LinkMy** adalah aplikasi web berbasis PHP untuk membuat halaman profil link pribadi yang dapat disesuaikan, mirip dengan Linktree atau Linktr.ee. Project ini dibuat dengan **PHP Native**, **MySQL**, dan **Bootstrap 5.3**.

---

## 📋 Fitur Lengkap

### ✅ Fitur Wajib (12 Fitur)

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

### ⭐ Fitur Tambahan (6+ Fitur)

13. **Drag & Drop Reordering** - Ubah urutan link dengan drag & drop (JavaScript)
14. **Link Analytics** - Tracking jumlah klik per link
15. **Icon Support** - Bootstrap Icons untuk setiap link
16. **Theme Customization** - 3 tema (Light, Dark, Gradient)
17. **Button Style Options** - 3 gaya tombol (Rounded, Sharp, Pill)
18. **Custom Bio** - Bio/deskripsi profil yang dapat disesuaikan
19. **Background Image** - Upload custom background untuk halaman publik
20. **URL Rewriting** - URL cantik menggunakan .htaccess
21. **Responsive Design** - Mobile-friendly di semua device

---

## 🗂️ Struktur Folder Lengkap

```
/LinkMy/
│
├── 📄 index.php                 # Halaman Login
├── 📄 register.php              # Form Registrasi
├── 📄 logout.php                # Proses Logout
├── 📄 profile.php               # Halaman Publik User
├── 📄 redirect.php              # Track Link Clicks
├── 📄 .htaccess                 # URL Rewriting
├── 📄 README.md                 # Dokumentasi
├── 📄 database.sql              # Database Schema
│
├── 📁 admin/                    # Area Admin (Protected)
│   ├── 📄 dashboard.php          # CRUD Links
│   ├── 📄 appearance.php         # Upload & Theme Settings
│   └── 📄 settings.php           # User Settings
│
├── 📁 config/
│   ├── 📄 db.php                 # Database Connection
│   └── 📄 auth_check.php         # Session Guard
│
├── 📁 assets/
│   ├── 📁 bootstrap-5.3/         # Bootstrap Framework
│   │   ├── 📁 css/
│   │   │   └── 📄 bootstrap.min.css
│   │   └── 📁 js/
│   │       └── 📄 bootstrap.bundle.min.js
│   ├── 📁 css/
│   │   ├── 📄 admin.css          # Admin Custom CSS
│   │   └── 📄 public.css         # Public Page CSS
│   └── 📁 js/
│       └── 📄 admin.js           # Drag & Drop Logic
│
└── 📁 uploads/                  # Upload Directory (777)
    ├── 📁 profile_pics/
    └── 📁 backgrounds/
```

---

## ⚙️ Instalasi

### 1. Persiapan Environment

**Requirements:**
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.2+
- Apache Server dengan mod_rewrite enabled
- Web Browser modern

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
- **Homepage:** `http://localhost/LinkMy/`
- **Login Default:**
  - Username: `admin`
  - Password: `admin123`

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

## 🌐 Deploy ke Hosting

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
- `uploads/` → 755 atau 777
- `uploads/profile_pics/` → 755 atau 777
- `uploads/backgrounds/` → 755 atau 777

### 7. Test Website

- Akses `https://yourdomain.com/`
- Test login, upload, dan semua fitur
- Test halaman publik: `https://yourdomain.com/username`

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

- Ganti password default admin setelah instalasi
- Gunakan HTTPS di production
- Backup database secara berkala
- Update PHP dan MySQL ke versi terbaru
- Monitor log error secara rutin

---

## 🐛 Troubleshooting

### Error: "Page not found"
- **Solusi:** Pastikan mod_rewrite enabled di Apache
- Check `.htaccess` RewriteBase sesuai folder

### Error: "Upload failed"
- **Solusi:** Set permission folder `uploads/` ke 777
- Check `php.ini`: `upload_max_filesize` dan `post_max_size`

### Error: "Database connection failed"
- **Solusi:** Check kredensial di `config/db.php`
- Pastikan MySQL service running

### Drag & Drop tidak bekerja
- **Solusi:** Pastikan file `assets/js/admin.js` ter-load
- Check browser console untuk error JavaScript

### Bootstrap tidak ter-load
- **Solusi:** Download Bootstrap 5.3 dan letakkan di folder yang benar
- Check path di `<link>` dan `<script>` tags

---

## 📝 Checklist Fitur untuk Penilaian

### Fitur Wajib:
- [x] HTML, PHP, dan Database
- [x] CSS Custom
- [x] Bootstrap Framework
- [x] Table Relasi (users → links, users → appearance)
- [x] Database View (v_public_page_data)
- [x] Insert (Register, Add Link)
- [x] Update (Edit Link, Update Appearance)
- [x] Delete (Delete Link, Delete Account)
- [x] Hosting Ready
- [x] Upload Foto (Profile Pic, Background)
- [x] Session Login
- [x] Session Logout

### Fitur Tambahan:
- [x] Drag & Drop Reordering
- [x] Link Analytics (Click Counter)
- [x] Icon Support
- [x] Theme Customization
- [x] Button Style Options
- [x] Custom Bio
- [x] Background Image Upload
- [x] URL Rewriting
- [x] Responsive Design

---

## 👨‍💻 Developer

Project ini dibuat sebagai tugas akhir mata kuliah Pemrograman Web.

**Tech Stack:**
- Frontend: HTML5, CSS3, Bootstrap 5.3, JavaScript (Vanilla)
- Backend: PHP 7.4+ (Native, No Framework)
- Database: MySQL 5.7+ / MariaDB
- Server: Apache dengan mod_rewrite

---

## 📄 License

Project ini dibuat untuk keperluan edukasi dan dapat digunakan secara bebas.

---

## 🙏 Credits

- **Bootstrap** - https://getbootstrap.com/
- **Bootstrap Icons** - https://icons.getbootstrap.com/
- **PHP** - https://www.php.net/
- **MySQL** - https://www.mysql.com/

---

**© 2024 LinkMy - Personal Link Hub**