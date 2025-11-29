# 🚀 Quick Installation Guide - Slug Management Features

## ⚡ Langkah Cepat (5 Menit)

### 1️⃣ Backup Database (PENTING!)

```bash
# Via command line
mysqldump -u root -p linkmy_db > backup_before_slug_feature.sql

# Atau via phpMyAdmin: Export → SQL → GO
```

### 2️⃣ Jalankan Migration

1. Buka **phpMyAdmin**
2. Pilih database `linkmy_db`
3. Klik tab **SQL**
4. Copy semua isi file `database_slug_management.sql`
5. Paste dan klik **Go**
6. Tunggu hingga selesai (seharusnya < 1 detik)

### 3️⃣ Verifikasi Migration

Jalankan query ini untuk memastikan berhasil:

```sql
-- Cek tabel user_slugs ada
SHOW TABLES LIKE 'user_slugs';
-- Result: 1 row

-- Cek data sudah dimigrate
SELECT COUNT(*) as total_slugs FROM user_slugs;
SELECT COUNT(*) as total_users FROM users;
-- Kedua angka harus SAMA

-- Cek kolom baru di users
SHOW COLUMNS FROM users LIKE 'last_slug_change_at';
-- Result: 1 row

-- Cek kolom baru di email_verifications
SHOW COLUMNS FROM email_verifications LIKE 'verification_type';
-- Result: 1 row

-- Lihat sample data
SELECT u.username, u.page_slug, us.slug, us.is_primary
FROM users u
LEFT JOIN user_slugs us ON u.user_id = us.user_id
LIMIT 5;
```

### 4️⃣ Test Fitur

1. Login ke **admin dashboard**: `http://linkmy.iet.ovh/login.php`
2. Klik menu **Settings**
3. Scroll ke bawah, Anda akan melihat:
    - ✅ **Ganti Slug Utama** (dengan badge "Verifikasi OTP")
    - ✅ **Kelola Slug Anda** (dengan badge "Gratis: 2 Slug")

### 5️⃣ Test Workflow

#### Test 1: Real-time Availability Check

1. Ketik slug baru di form "Ganti Slug Utama"
2. Tunggu 500ms
3. Harusnya muncul feedback:
    - ✅ "Slug tersedia!" (hijau) → button enabled
    - ❌ "Slug sudah digunakan" (merah) → button disabled

#### Test 2: Change Primary Slug dengan OTP

1. Masukkan slug baru yang valid
2. Klik "Kirim Kode OTP"
3. Cek email Anda (folder Inbox atau Spam)
4. Copy kode 6 digit
5. Paste di form verifikasi
6. Klik "Verifikasi & Ganti Slug"
7. Slug berhasil diubah!

#### Test 3: Add Second Slug

1. Scroll ke "Kelola Slug Anda"
2. Anda melihat 1 slug (primary)
3. Di form "Tambah Slug Baru", ketik slug kedua
4. Tunggu feedback availability
5. Klik "Tambah Slug"
6. Sekarang Anda punya 2 slug!

#### Test 4: Set Primary Slug

1. Pada slug kedua (alias), klik "Jadikan Utama"
2. Konfirmasi
3. Slug alias menjadi primary
4. Slug lama menjadi alias

#### Test 5: Delete Alias Slug

1. Pada slug alias, klik "Hapus"
2. Konfirmasi
3. Slug alias terhapus
4. Tombol "Hapus" disabled pada primary slug (tidak bisa dihapus)

---

## ✅ Checklist Post-Installation

-   [ ] Migration berhasil (no errors)
-   [ ] Tabel `user_slugs` exists
-   [ ] Kolom `users.last_slug_change_at` exists
-   [ ] Kolom `email_verifications.verification_type` exists
-   [ ] Data existing slugs sudah dimigrate ke `user_slugs`
-   [ ] Real-time slug checker berfungsi
-   [ ] OTP email terkirim
-   [ ] Bisa tambah slug ke-2
-   [ ] Set primary slug berfungsi
-   [ ] Delete alias slug berfungsi
-   [ ] Cooldown 30 hari berfungsi

---

## 🔧 Troubleshooting

### ❌ Error: Table 'user_slugs' doesn't exist

**Penyebab:** Migration belum dijalankan atau gagal  
**Solusi:**

```sql
-- Jalankan ulang migration dari awal
SOURCE database_slug_management.sql;
```

### ❌ Error: Duplicate entry for key 'unique_slug'

**Penyebab:** Slug sudah ada di database  
**Solusi:**

```sql
-- Cek slug yang duplikat
SELECT slug, COUNT(*) FROM user_slugs GROUP BY slug HAVING COUNT(*) > 1;

-- Hapus duplikat (keep oldest)
DELETE us1 FROM user_slugs us1
INNER JOIN user_slugs us2
WHERE us1.slug_id > us2.slug_id AND us1.slug = us2.slug;
```

### ❌ AJAX tidak jalan (real-time check)

**Penyebab:** jQuery tidak loaded  
**Solusi:**

```html
<!-- Cek di browser console -->
typeof jQuery // Harus return: "function"

<!-- Jika undefined, pastikan jQuery loaded sebelum script kita -->
<script src="../assets/js/jquery-3.7.1.min.js"></script>
```

### ❌ OTP tidak terkirim

**Penyebab:** SMTP config salah atau email blocked  
**Solusi:**

1. Cek `config/mail.php`:
    ```php
    $mail->Host = 'smtp.gmail.com'; // Pastikan benar
    $mail->Username = 'your-email@gmail.com'; // Email valid
    $mail->Password = 'your-app-password'; // App password, bukan password Gmail
    ```
2. Test SMTP connection:
    ```php
    // Tambahkan di settings.php untuk debug
    $mail->SMTPDebug = 2; // Enable verbose debug output
    ```
3. Cek folder spam email

### ❌ Cooldown tidak akurat

**Penyebab:** Timezone tidak sync  
**Solusi:**

```php
// Tambahkan di config/db.php
date_default_timezone_set('Asia/Jakarta');

// Dan di MySQL
SET time_zone = '+07:00';
```

---

## 🎉 Fitur Berhasil!

Selamat! Anda sekarang memiliki:

-   ✅ Slug management dengan OTP verification
-   ✅ Multiple slugs (max 2) per user
-   ✅ Real-time availability checker
-   ✅ 30-day cooldown protection
-   ✅ Primary/alias slug system

**Next Steps:**

1. Test semua fitur dengan user test
2. Backup database setelah testing
3. Deploy ke production
4. Monitor logs untuk errors
5. Collect user feedback

---

## 📚 Documentation

**Lengkap:** `SLUG_MANAGEMENT_GUIDE.md` (1500+ lines)  
**Quick:** File ini  
**Migration:** `database_slug_management.sql`

---

**Installation Time:** ~5 menit  
**Tested On:** XAMPP 8.2, MySQL 8.0, PHP 8.2  
**Status:** Production Ready ✅
