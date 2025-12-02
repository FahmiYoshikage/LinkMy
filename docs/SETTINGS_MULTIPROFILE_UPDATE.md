# Settings.php Multi-Profile Update Guide

## MASALAH CURRENT

-   `settings.php` masih pakai sistem LAMA `user_slugs`
-   Database BARU sudah pakai `profiles` table
-   Perlu migrasi settings.php ke sistem profiles

## SOLUSI

Settings.php perlu di-refactor untuk:

1. ❌ **HAPUS** semua referensi ke `user_slugs` table
2. ✅ **GANTI** ke `profiles` table
3. ✅ **REDIRECT** user ke `admin/profiles.php` untuk manage multi-profile

## QUICK FIX

Karena fitur slug management sudah tersedia di `admin/profiles.php`, maka:

-   **Settings.php** → fokus ke account settings (password, email, delete account)
-   **Profiles.php** → fokus ke profile management (create, edit, delete, switch profiles)

## ACTION REQUIRED

Jalankan di VPS untuk backup:

```bash
cp /opt/LinkMy/admin/settings.php /opt/LinkMy/admin/settings.php.backup
```

Lalu update settings.php untuk remove slug management section dan redirect ke profiles.php
