# Boxed Layout Feature - LinkMy v2.3

## Overview

Fitur Boxed Layout memungkinkan pengguna menampilkan profil mereka dalam kotak (container) yang lebih kecil dengan background luar yang dapat dikustomisasi - mirip dengan tampilan Linktree.

## Files Modified

1. **database_add_boxed_layout.sql** - SQL script untuk menambahkan kolom baru
2. **admin/appearance.php** - Menambahkan tab "Boxed Layout" dan form settings
3. **profile.php** - Memodifikasi rendering untuk mendukung boxed mode

## Database Changes

Kolom baru di tabel `appearance`:

-   `boxed_layout` - TINYINT(1), aktifkan/nonaktifkan boxed mode
-   `outer_bg_type` - VARCHAR(20), tipe background luar (color/gradient)
-   `outer_bg_color` - VARCHAR(50), warna solid untuk background luar
-   `outer_bg_gradient_start` - VARCHAR(50), warna awal gradient
-   `outer_bg_gradient_end` - VARCHAR(50), warna akhir gradient
-   `container_bg_color` - VARCHAR(50), warna background container
-   `container_max_width` - INT, lebar maksimal container (px)
-   `container_border_radius` - INT, border radius container (px)
-   `container_shadow` - TINYINT(1), aktifkan/nonaktifkan shadow

## Features

### Admin Panel (Appearance Tab)

-   Toggle untuk mengaktifkan Boxed Layout
-   Pilihan background luar: Solid Color atau Gradient
-   Color picker untuk semua warna
-   Slider untuk container width (320-600px)
-   Slider untuk border radius (0-50px)
-   Toggle untuk container shadow
-   Live preview untuk melihat perubahan real-time

### Public Profile View

-   Konten ditampilkan dalam container dengan max-width yang bisa dikustom
-   Background luar full-width dengan color/gradient pilihan user
-   Container memiliki rounded corners dan optional shadow
-   Button action (LinkMy dan Share) ditempatkan di dalam container
-   Fully responsive untuk mobile

## How to Use

1. **Setup Database**: Jalankan `database_add_boxed_layout.sql` di phpMyAdmin atau MySQL client
2. **Access Settings**: Buka Admin → Appearance → Tab "Boxed Layout"
3. **Enable Mode**: Centang "Enable Boxed Layout"
4. **Customize**:
    - Pilih background type (Solid Color atau Gradient)
    - Atur warna background luar
    - Atur warna background container
    - Sesuaikan width container (default 480px)
    - Sesuaikan border radius (default 30px)
    - Toggle shadow sesuai preferensi
5. **Preview**: Lihat perubahan di bagian preview
6. **Save**: Klik "Save Boxed Layout Settings"
7. **View Profile**: Klik "View Page" di navbar untuk melihat hasil

## Responsive Design

-   Desktop: Full boxed effect dengan shadow
-   Mobile: Container menyesuaikan dengan lebar layar
-   Border radius dikurangi otomatis di mobile untuk pengalaman optimal

## Default Values

-   Container Width: 480px
-   Border Radius: 30px
-   Outer Background: Purple gradient (#667eea → #764ba2)
-   Container Background: White (#ffffff)
-   Shadow: Enabled

## Notes

-   Fitur ini tidak mengubah tema atau style yang sudah ada
-   Boxed layout bekerja bersama dengan semua fitur appearance lainnya
-   Background image original tetap bisa digunakan dalam boxed mode
-   Button positioning otomatis disesuaikan untuk boxed vs full-width mode

## Browser Compatibility

-   Chrome/Edge: ✅ Full support
-   Firefox: ✅ Full support
-   Safari: ✅ Full support
-   Mobile browsers: ✅ Full support

## Version

LinkMy v2.3 - Boxed Layout Feature
Release Date: November 20, 2025
