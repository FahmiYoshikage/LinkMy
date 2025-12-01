## Perbaikan Profile Stats Display

### Masalah

-   Menampilkan **0 Links** dan **0 Klik**
-   Tanggal dibuat menampilkan **01 Jan 1970** (default timestamp)
-   Data tidak sesuai dengan yang ada di database

### Penyebab

Query SQL menggunakan `GROUP BY` dengan `LEFT JOIN` yang menyebabkan:

1. MySQL strict mode (`ONLY_FULL_GROUP_BY`) error atau menghasilkan data kosong
2. Agregasi yang salah pada kolom yang tidak di-group
3. Kompatibilitas berbeda antar versi MySQL

### Solusi

Mengubah query dari metode `GROUP BY + LEFT JOIN` menjadi **subquery method**:

#### Before:

```sql
SELECT p.profile_id, p.slug, p.profile_name, p.created_at,
       COUNT(DISTINCT l.link_id) as link_count,
       COALESCE(SUM(l.click_count), 0) as total_clicks
FROM profiles p
LEFT JOIN links l ON p.profile_id = l.profile_id
WHERE p.user_id = ?
GROUP BY p.profile_id, p.slug, p.profile_name, p.created_at
```

#### After:

```sql
SELECT p.profile_id, p.slug, p.profile_name, p.created_at,
       (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
       (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
FROM profiles p
WHERE p.user_id = ?
ORDER BY p.is_primary DESC, p.created_at ASC
```

### Keuntungan Subquery Method:

1. ✅ Lebih kompatibel dengan MySQL strict mode
2. ✅ Tidak perlu kompleks `GROUP BY` clause
3. ✅ Lebih mudah di-maintain
4. ✅ Hasil lebih akurat dan reliable
5. ✅ Menghindari masalah dengan kolom non-aggregated

### File yang Diubah:

-   `admin/profiles.php` - Line 8-23
-   `admin/settings.php` - Line 368-387

### Testing:

Gunakan file `admin/debug_profiles_stats.php` untuk test query dan verifikasi hasilnya.
