# 📌 SLUG MANAGEMENT FEATURES - LinkMy

## 🎯 Overview

LinkMy sekarang memiliki dua fitur penting untuk mengelola slug profil Anda:

1. **Ganti Slug Utama** - Ubah slug dengan verifikasi OTP (cooldown 30 hari)
2. **Multiple Slugs** - Miliki hingga 2 slug berbeda yang mengarah ke profil yang sama

---

## 📦 Database Migration

### Langkah Instalasi:

1. Buka phpMyAdmin atau MySQL client Anda
2. Pilih database `linkmy_db`
3. Jalankan file: `database_slug_management.sql`
4. Verifikasi dengan query:
    ```sql
    SELECT COUNT(*) FROM user_slugs;
    -- Harus sama dengan jumlah users
    ```

### Perubahan Database:

#### 1. Tabel `users` - Kolom Baru:

```sql
last_slug_change_at DATETIME NULL
```

-   Menyimpan waktu terakhir user mengubah slug utama
-   Digunakan untuk cooldown 30 hari

#### 2. Tabel Baru: `user_slugs`

```sql
CREATE TABLE user_slugs (
  slug_id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  slug VARCHAR(50) UNIQUE NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Kolom Penting:**

-   `slug` - Slug unik (maksimal 50 karakter)
-   `is_primary` - 1 = slug utama, 0 = slug alias
-   Setiap user bisa punya max 2 slugs

#### 3. Tabel `email_verifications` - Kolom Baru:

```sql
verification_type ENUM('email', 'slug_change') DEFAULT 'email'
```

-   Membedakan OTP untuk registrasi vs perubahan slug

---

## 🔧 Fitur 1: Ganti Slug Utama

### Cara Kerja:

1. User memasukkan slug baru di form
2. Sistem cek ketersediaan secara real-time (AJAX)
3. Sistem cek cooldown (30 hari sejak perubahan terakhir)
4. Kirim kode OTP 6 digit ke email user
5. User verifikasi dengan memasukkan OTP
6. Slug berhasil diubah

### Validasi:

-   ✅ Minimal 3 karakter, maksimal 50 karakter
-   ✅ Hanya huruf kecil, angka, dan tanda hubung (-)
-   ✅ Slug harus unik (belum dipakai user lain)
-   ✅ Cooldown 30 hari antara perubahan
-   ✅ Verifikasi OTP wajib (berlaku 15 menit)

### Kode Penting:

**POST Handler - Request OTP:**

```php
if ($_POST['request_slug_change']) {
    // Check cooldown
    if ($user['last_slug_change_at']) {
        $days_since = (time() - strtotime($user['last_slug_change_at'])) / (60*60*24);
        if ($days_since < 30) {
            $error = "Cooldown {$days_left} hari lagi";
        }
    }

    // Generate OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    // Send email via PHPMailer
    send_email($user['email'], $subject, $body);
}
```

**POST Handler - Verify OTP:**

```php
if ($_POST['verify_slug_change']) {
    // Verify OTP from database
    $verification = get_single_row(
        "SELECT * FROM email_verifications
         WHERE email = ? AND otp_code = ?
         AND is_used = 0 AND expires_at > NOW()
         AND verification_type = 'slug_change'"
    );

    // Update slug
    UPDATE user_slugs SET slug = ? WHERE user_id = ? AND is_primary = 1;
    UPDATE users SET page_slug = ?, last_slug_change_at = NOW();
}
```

### UI Components:

-   **Alert Info** - Menampilkan slug saat ini
-   **Alert Warning** - Menampilkan cooldown countdown
-   **Form Input** - Slug baru dengan validasi real-time
-   **Feedback Badge** - Slug tersedia/tidak tersedia
-   **OTP Modal** - Form verifikasi kode 6 digit

---

## 🔗 Fitur 2: Multiple Slugs (Max 2)

### Konsep:

-   Setiap user bisa punya hingga **2 slug** (gratis)
-   Satu slug = **Primary** (utama)
-   Slug lainnya = **Alias** (pengarah)
-   Semua slug mengarah ke **profil yang sama**

### Use Cases:

1. **Personal Branding**

    - Slug 1: `fahmi` (nama asli)
    - Slug 2: `fahmi-portfolio` (untuk CV)

2. **Bisnis + Personal**

    - Slug 1: `john-doe` (pribadi)
    - Slug 2: `johndoe-store` (bisnis)

3. **Rebranding**
    - Slug 1: `old-brand` (tetap aktif)
    - Slug 2: `new-brand` (utama)

### Operasi:

#### A. Tambah Slug Baru

```php
if ($_POST['add_slug']) {
    // Check limit
    $count = get_single_row("SELECT COUNT(*) FROM user_slugs WHERE user_id = ?");
    if ($count >= 2) {
        $error = "Maksimal 2 slug!";
    }

    // Add as alias (is_primary = 0)
    INSERT INTO user_slugs (user_id, slug, is_primary) VALUES (?, ?, 0);
}
```

#### B. Hapus Slug

```php
if ($_GET['delete_slug']) {
    // Cannot delete primary
    if ($slug_data['is_primary'] == 1) {
        $error = "Tidak bisa hapus slug utama!";
    }

    DELETE FROM user_slugs WHERE slug_id = ?;
}
```

#### C. Set Primary Slug

```php
if ($_GET['set_primary']) {
    // Unset all primary flags
    UPDATE user_slugs SET is_primary = 0 WHERE user_id = ?;

    // Set new primary
    UPDATE user_slugs SET is_primary = 1 WHERE slug_id = ?;

    // Update users.page_slug for backward compatibility
    UPDATE users SET page_slug = ? WHERE user_id = ?;
}
```

### UI Components:

-   **Badge Counter** - Menampilkan "2/2 Slug"
-   **List Group** - Daftar semua slug dengan badge (Primary/Alias)
-   **Action Buttons**:
    -   "Jadikan Utama" - Set slug sebagai primary
    -   "Hapus" - Delete slug alias (disabled untuk primary)
-   **Add Form** - Form tambah slug baru (hidden jika sudah 2)

---

## 🎨 Frontend: AJAX Slug Checker

### Real-time Validation

User mengetik → Debounce 500ms → AJAX check → Feedback

```javascript
function checkSlugAvailability(slug, feedbackElement, buttonElement) {
    $.ajax({
        url: 'settings.php',
        method: 'POST',
        data: { action: 'check_slug_availability', slug: slug },
        success: function (response) {
            if (response.available) {
                feedbackElement.innerHTML = '✅ Slug tersedia!';
                buttonElement.disabled = false;
            } else {
                feedbackElement.innerHTML = '❌ Slug sudah dipakai';
                buttonElement.disabled = true;
            }
        },
    });
}

// Debounce untuk mengurangi load server
const debouncedCheck = debounce(checkSlugAvailability, 500);

$('#new_slug_input').on('input', debouncedCheck);
```

### Auto-Sanitize Input

```javascript
$('#new_slug_input').on('input', function () {
    // Convert to lowercase, remove non-alphanumeric except hyphen
    let val = $(this)
        .val()
        .toLowerCase()
        .replace(/[^a-z0-9-]/g, '');
    $(this).val(val);
});
```

---

## 🔐 Security Features

### 1. Cooldown Protection

-   **30 hari** antara perubahan slug
-   Mencegah abuse dan spam
-   Simpan timestamp di `users.last_slug_change_at`

### 2. OTP Verification

-   Kode 6 digit random
-   Expire dalam **15 menit**
-   One-time use (flag `is_used`)
-   Dikirim via email (PHPMailer)

### 3. Slug Validation

-   **Server-side** + **client-side**
-   Pattern: `^[a-z0-9-]{3,50}$`
-   Unique constraint di database
-   Sanitize sebelum query

### 4. Rate Limiting

-   Debounce 500ms untuk AJAX
-   Cooldown 30 hari untuk perubahan
-   Max 2 slugs per user

---

## 🧪 Testing Checklist

### ✅ Fitur Ganti Slug:

-   [ ] Slug baru valid (3-50 karakter, alphanumeric + hyphen)
-   [ ] Real-time check availability bekerja
-   [ ] Cooldown 30 hari berfungsi
-   [ ] OTP dikirim ke email
-   [ ] OTP expire setelah 15 menit
-   [ ] Slug berhasil diubah setelah verifikasi
-   [ ] Session `page_slug` terupdate
-   [ ] Link profil dengan slug baru berfungsi

### ✅ Fitur Multiple Slugs:

-   [ ] Bisa tambah slug ke-2
-   [ ] Tidak bisa tambah slug ke-3 (max 2)
-   [ ] Slug alias mengarah ke profil yang sama
-   [ ] Bisa set slug alias sebagai primary
-   [ ] Tidak bisa hapus slug primary
-   [ ] Bisa hapus slug alias
-   [ ] Badge "Primary" dan "Alias" muncul
-   [ ] Link dengan semua slug berfungsi

### ✅ Security:

-   [ ] Slug unik (tidak duplikat)
-   [ ] Input disanitize (lowercase, no special chars)
-   [ ] OTP hanya berlaku sekali
-   [ ] Cooldown mencegah spam
-   [ ] Email verification required

---

## 📊 Database Schema Diagram

```
users
├── user_id (PK)
├── username
├── page_slug (untuk backward compatibility)
├── last_slug_change_at (NEW! - cooldown tracking)
└── ...

user_slugs (NEW TABLE!)
├── slug_id (PK)
├── user_id (FK → users.user_id)
├── slug (UNIQUE)
├── is_primary (1=primary, 0=alias)
└── created_at

email_verifications
├── id (PK)
├── email
├── otp_code
├── expires_at
├── is_used
├── verification_type (NEW! - 'email' or 'slug_change')
└── ...
```

**Relasi:**

-   `user_slugs.user_id` → `users.user_id` (1:N, max 2)
-   `email_verifications.email` → `users.email` (1:N)

---

## 🚀 Future Enhancements

### Premium Features (Future):

1. **Unlimited Slugs** - Premium user unlimited slugs
2. **Custom Domains** - `custom.com/slug` instead of `linkmy.iet.ovh/slug`
3. **Slug Analytics** - Track which slug gets most clicks
4. **Slug History** - View all previous slugs
5. **Instant Change** - Premium bypass 30-day cooldown
6. **Slug Transfer** - Transfer slug ke user lain

### Technical Improvements:

1. **Redis Cache** - Cache slug lookups
2. **Rate Limiting** - IP-based rate limit for OTP
3. **Email Template** - Better HTML email design
4. **Slug Redirect** - Auto-redirect old slug to new (optional)
5. **Slug Reservation** - Reserve slug before delete

---

## 🐛 Troubleshooting

### Problem: OTP tidak terkirim

**Solution:**

-   Cek konfigurasi SMTP di `config/mail.php`
-   Pastikan PHPMailer terinstall
-   Cek folder spam email

### Problem: Slug availability check tidak jalan

**Solution:**

-   Pastikan jQuery loaded
-   Cek console browser untuk error
-   Pastikan AJAX endpoint `settings.php` accessible

### Problem: Cooldown tidak akurat

**Solution:**

-   Cek timezone server vs database
-   Pastikan `last_slug_change_at` diupdate
-   Query: `SELECT last_slug_change_at FROM users WHERE user_id = ?`

### Problem: Slug duplikat error

**Solution:**

-   UNIQUE constraint di database mencegah ini
-   Jika terjadi, rebuild index:
    ```sql
    ALTER TABLE user_slugs DROP INDEX unique_slug;
    ALTER TABLE user_slugs ADD UNIQUE KEY unique_slug (slug);
    ```

---

## 📝 Changelog

### Version 1.0 (Current)

-   ✅ Slug change dengan OTP verification
-   ✅ Multiple slugs (max 2 untuk gratis)
-   ✅ Real-time availability checker
-   ✅ 30-day cooldown protection
-   ✅ Primary/alias slug management
-   ✅ Database migration script
-   ✅ Full UI implementation

---

## 👨‍💻 Development Notes

### Files Modified:

1. **admin/settings.php** - Added slug management UI & handlers
2. **database_slug_management.sql** - Migration script

### New Dependencies:

-   jQuery (already exists in `assets/js/jquery-3.7.1.min.js`)
-   PHPMailer (already exists in `libs/PHPMailer-7.0.0/`)

### API Endpoints:

-   `POST /admin/settings.php?action=check_slug_availability` - Check slug
-   `POST /admin/settings.php?request_slug_change` - Request OTP
-   `POST /admin/settings.php?verify_slug_change` - Verify & change slug
-   `POST /admin/settings.php?add_slug` - Add new slug
-   `GET /admin/settings.php?delete_slug={id}` - Delete slug
-   `GET /admin/settings.php?set_primary={id}` - Set primary slug

### Session Variables:

-   `$_SESSION['pending_slug_change']` - Temporary store pending slug during OTP flow

---

## 📞 Support

Jika ada masalah atau pertanyaan:

1. Cek dokumentasi ini terlebih dahulu
2. Lihat error log di browser console atau PHP error log
3. Test dengan user baru untuk isolate issue
4. Rollback database jika perlu (lihat migration file)

---

**Created by:** Fahmi - LinkMy Developer Team
**Date:** November 2024
**Version:** 1.0
