# Troubleshooting Guide - Boxed Layout

## Bug Fixes Applied

### 1. ✅ Fixed bind_param Error

**Error**: `ArgumentCountError: The number of elements in the type definition string must match`

**Problem**: Type definition string had 9 characters but 10 variables were passed.

**Solution**: Changed from `'isssssiii'` to `'isssssiiii'` (10 characters for 10 variables)

-   i = integer (boxed_layout)
-   s = string (outer_bg_type)
-   s = string (outer_bg_color)
-   s = string (outer_bg_gradient_start)
-   s = string (outer_bg_gradient_end)
-   s = string (container_bg_color)
-   i = integer (container_max_width)
-   i = integer (container_border_radius)
-   i = integer (container_shadow)
-   i = integer (user_id)

### 2. ✅ Improved SQL Script

**File**: `database_add_boxed_layout.sql`

**Changes**:

-   Added safe column addition with IF NOT EXISTS checks
-   Prevents duplicate column errors if run multiple times
-   Uses stored procedure for better control
-   Changed default `outer_bg_type` from 'color' to 'gradient' (lebih menarik)

### 3. ℹ️ Visual Issues

**Double Radio Button**:

-   Checked the code - hanya ada 1 checkbox/toggle switch
-   Kemungkinan masalah cache browser
-   **Solution**: Hard refresh (Ctrl + Shift + R atau Ctrl + F5)

**Navbar Issue**:

-   Navbar sudah tidak menggunakan `navbar-dark` class
-   Sudah ada CSS custom dengan `!important`
-   **Solution**: Clear browser cache

## How to Fix

### Step 1: Clear Browser Cache

```
Chrome/Edge: Ctrl + Shift + Delete
Firefox: Ctrl + Shift + Delete
Safari: Command + Option + E
```

Or do a hard refresh: `Ctrl + Shift + R` atau `Ctrl + F5`

### Step 2: Re-run SQL (Safe Version)

The new SQL script is safe to run multiple times. It will:

1. Check if columns exist
2. Only add missing columns
3. Not cause errors if already exists

```bash
# Via MySQL command line
mysql -u root linkmy_db < database_add_boxed_layout.sql

# Or copy-paste into phpMyAdmin SQL tab
```

### Step 3: Test the Feature

1. Login to admin panel
2. Go to Appearance → Boxed Layout tab
3. Enable boxed layout
4. Configure settings
5. Click "Save Boxed Layout Settings"
6. Should see: "✅ Boxed Layout berhasil disimpan!"
7. View profile to see changes

## Debugging Tips

### Check if columns exist:

```sql
DESCRIBE appearance;
```

Should show all boxed_layout columns.

### Check current values:

```sql
SELECT
    boxed_layout,
    outer_bg_type,
    container_max_width,
    container_border_radius
FROM appearance
WHERE user_id = YOUR_USER_ID;
```

### Reset to defaults:

```sql
UPDATE appearance
SET
    boxed_layout = 0,
    outer_bg_type = 'gradient',
    outer_bg_color = '#667eea',
    outer_bg_gradient_start = '#667eea',
    outer_bg_gradient_end = '#764ba2',
    container_bg_color = '#ffffff',
    container_max_width = 480,
    container_border_radius = 30,
    container_shadow = 1
WHERE user_id = YOUR_USER_ID;
```

## Common Issues

### Issue: Form tidak save

**Check**:

1. Apakah SQL sudah dijalankan?
2. Apakah ada error di browser console? (F12)
3. Apakah kolom database sudah ada?

### Issue: Navbar tidak berwarna

**Solution**:

1. Hard refresh browser (Ctrl + Shift + R)
2. Clear all browser cache
3. Check if admin.css is loaded (View Source)

### Issue: Visual double checkbox

**Solution**:

1. Hard refresh (Ctrl + F5)
2. Clear browser cache
3. Check browser console for CSS errors

## Browser Console Debugging

Open browser console (F12) and check for:

-   Red errors
-   Failed network requests
-   CSS loading issues

## Need More Help?

Check these files:

-   `admin/appearance.php` - Line 289 (bind_param fix)
-   `database_add_boxed_layout.sql` - Safe SQL script
-   `profile.php` - Boxed layout rendering
-   `BOXED_LAYOUT_FEATURE.md` - Full documentation
