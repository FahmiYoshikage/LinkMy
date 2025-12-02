# 🔧 PHP Code Migration Guide (For Later)

> **NOTE:** Kamu bilang "untuk perbaikan kode nanti saja dulu, sekarang fokus menyelesaikan urusan database"  
> File ini untuk NANTI setelah database migration selesai.

---

## 📋 Table & Column Mapping

### Table Name Changes

```php
// OLD → NEW
'old_users'               → 'users'
'old_profiles'            → 'profiles'
'old_links'               → 'links'
'old_user_appearance'     → 'themes' (+ 'theme_boxed' for boxed layout)
'old_sessions'            → 'sessions'
'old_password_resets'     → 'password_resets'
'old_email_verifications' → 'email_verifications'
'old_link_analytics'      → 'clicks'
'link_categories'         → 'categories'
```

### Column Name Changes

**users table:**

```php
'user_id'       → 'id'
'password_hash' → 'password'
'is_verified'   → 'is_verified' (same, but now means verified badge)
'page_slug'     → (removed, use profiles.slug instead)
```

**profiles table:**

```php
'profile_id'            → 'id'
'profile_name'          → 'name'
'profile_title'         → 'title'
'profile_pic_filename'  → 'avatar'
'is_primary'            → (removed, use display_order = 0 instead)
```

**links table:**

```php
'link_id'     → 'id'
'icon_class'  → 'icon'
'order_index' → 'position'
'click_count' → 'clicks'
```

**clicks table:**

```php
'analytics_id' → 'id'
'ip_address'   → 'ip'
```

---

## 🔨 Find & Replace Commands

### Step 1: Backup Your Code First!

```bash
cd /opt/LinkMy
tar -czf code_backup_$(date +%Y%m%d_%H%M%S).tar.gz *.php admin/*.php config/*.php
```

### Step 2: Global Find & Replace

**Via VS Code or Terminal:**

```bash
# Replace table names
find . -type f -name "*.php" -exec sed -i 's/old_users/users/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_profiles/profiles/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_links/links/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_user_appearance/themes/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_sessions/sessions/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_password_resets/password_resets/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_email_verifications/email_verifications/g' {} +
find . -type f -name "*.php" -exec sed -i 's/old_link_analytics/clicks/g' {} +
find . -type f -name "*.php" -exec sed -i 's/link_categories/categories/g' {} +
```

**WARNING:** The above commands will change ALL occurrences. Be careful!

---

## 📝 Manual Code Updates by File

### config/db.php

No changes needed (table names changed in queries, not connection)

---

### admin/dashboard.php

**OLD CODE:**

```php
// Get stats
$stmt = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM old_users) as total_users,
        (SELECT COUNT(*) FROM old_profiles WHERE is_active = 1) as total_profiles,
        (SELECT COUNT(*) FROM old_links WHERE is_active = 1) as total_links,
        (SELECT SUM(click_count) FROM old_links) as total_clicks
");
$stmt->execute();
$stats = $stmt->fetch();
```

**NEW CODE:**

```php
// Get stats
$stmt = $pdo->prepare("
    SELECT
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM profiles WHERE is_active = 1) as total_profiles,
        (SELECT COUNT(*) FROM links WHERE is_active = 1) as total_links,
        (SELECT SUM(clicks) FROM links) as total_clicks
");
$stmt->execute();
$stats = $stmt->fetch();
```

---

### admin/profiles.php

**OLD CODE:**

```php
// Get user profiles
$stmt = $pdo->prepare("
    SELECT
        p.profile_id,
        p.profile_name,
        p.slug,
        p.is_primary,
        COUNT(l.link_id) as link_count,
        SUM(l.click_count) as total_clicks
    FROM old_profiles p
    LEFT JOIN old_links l ON p.profile_id = l.profile_id AND l.is_active = 1
    WHERE p.user_id = ?
    GROUP BY p.profile_id
    ORDER BY p.is_primary DESC, p.created_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
```

**NEW CODE (Option 1: Manual Query):**

```php
// Get user profiles
$stmt = $pdo->prepare("
    SELECT * FROM v_profile_stats WHERE user_id = ?
    ORDER BY display_order ASC, created_at ASC
");
$stmt->execute([$_SESSION['user_id']]);
```

**NEW CODE (Option 2: Stored Procedure - RECOMMENDED):**

```php
// Get user profiles using stored procedure
$stmt = $pdo->prepare("CALL sp_get_user_profiles(?)");
$stmt->execute([$_SESSION['user_id']]);
```

**Fix Loop Variable Names:**

```php
// OLD
foreach ($profiles as $profile) {
    echo $profile['profile_id'];
    echo $profile['profile_name'];
}

// NEW
foreach ($profiles as $profile) {
    echo $profile['id'];
    echo $profile['name'];
}
```

---

### admin/settings.php

**OLD CODE:**

```php
// Get active profile
$stmt = $pdo->prepare("
    SELECT * FROM old_profiles
    WHERE user_id = ? AND is_primary = 1
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

// Get appearance
$stmt = $pdo->prepare("
    SELECT * FROM old_user_appearance WHERE profile_id = ?
");
$stmt->execute([$profile['profile_id']]);
$appearance = $stmt->fetch();
```

**NEW CODE:**

```php
// Get primary profile (first by display_order)
$stmt = $pdo->prepare("
    SELECT * FROM profiles
    WHERE user_id = ?
    ORDER BY display_order ASC, created_at ASC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$profile = $stmt->fetch();

// Get theme using view (includes boxed settings)
$stmt = $pdo->prepare("
    SELECT * FROM v_public_profiles WHERE id = ?
");
$stmt->execute([$profile['id']]);
$theme = $stmt->fetch();
```

**Save Theme Settings:**

```php
// OLD
$stmt = $pdo->prepare("
    UPDATE old_user_appearance
    SET button_style = ?,
        custom_button_color = ?,
        ...30+ columns...
    WHERE profile_id = ?
");

// NEW (Much Simpler!)
$stmt = $pdo->prepare("
    INSERT INTO themes (profile_id, button_style, button_color, ...)
    VALUES (?, ?, ?, ...)
    ON DUPLICATE KEY UPDATE
        button_style = VALUES(button_style),
        button_color = VALUES(button_color),
        ...
");
```

---

### profile.php (Public Profile Page)

**OLD CODE:**

```php
// Get profile by slug
$stmt = $pdo->prepare("
    SELECT
        p.*,
        u.username,
        u.is_verified,
        a.button_style,
        a.custom_button_color,
        ...30+ appearance columns...
    FROM old_profiles p
    JOIN old_users u ON p.user_id = u.user_id
    LEFT JOIN old_user_appearance a ON p.profile_id = a.profile_id
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$profile = $stmt->fetch();

// Get links
$stmt = $pdo->prepare("
    SELECT * FROM old_links
    WHERE profile_id = ? AND is_active = 1
    ORDER BY order_index ASC
");
$stmt->execute([$profile['profile_id']]);
$links = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("
    SELECT * FROM link_categories
    WHERE profile_id = ?
    ORDER BY display_order ASC
");
$stmt->execute([$profile['profile_id']]);
$categories = $stmt->fetchAll();
```

**NEW CODE (Using Stored Procedure - MUCH SIMPLER!):**

```php
// Get complete profile data (profile + links + categories)
$stmt = $pdo->prepare("CALL sp_get_profile_full(?)");
$stmt->execute([$slug]);

// First result set: profile info
$profile = $stmt->fetch();
$stmt->nextRowset(); // Move to next result set

// Second result set: categories
$categories = $stmt->fetchAll();
$stmt->nextRowset(); // Move to next result set

// Third result set: links
$links = $stmt->fetchAll();
```

**Access Theme Data:**

```php
// OLD
echo $profile['custom_button_color'];
echo $profile['button_style'];
echo $profile['profile_pic_filename'];

// NEW
echo $profile['button_color'];
echo $profile['button_style'];
echo $profile['avatar'];
```

---

### redirect.php (Link Click Handler)

**OLD CODE:**

```php
// Get link
$stmt = $pdo->prepare("SELECT * FROM old_links WHERE link_id = ?");
$stmt->execute([$link_id]);
$link = $stmt->fetch();

// Update click count
$stmt = $pdo->prepare("UPDATE old_links SET click_count = click_count + 1 WHERE link_id = ?");
$stmt->execute([$link_id]);

// Log analytics
$stmt = $pdo->prepare("
    INSERT INTO old_link_analytics
    (link_id, ip_address, country, city, user_agent, referrer)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->execute([$link_id, $ip, $country, $city, $user_agent, $referrer]);

// Redirect
header("Location: " . $link['url']);
```

**NEW CODE (Using Stored Procedure - CLEANER!):**

```php
// Get link
$stmt = $pdo->prepare("SELECT * FROM links WHERE id = ?");
$stmt->execute([$link_id]);
$link = $stmt->fetch();

// Increment click & log analytics (single call!)
$stmt = $pdo->prepare("CALL sp_increment_click(?, ?, ?, ?, ?, ?)");
$stmt->execute([$link_id, $ip, $country, $city, $user_agent, $referrer]);

// Redirect
header("Location: " . $link['url']);
```

---

### login.php

**OLD CODE:**

```php
// Check user
$stmt = $pdo->prepare("SELECT * FROM old_users WHERE username = ? OR email = ?");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
}
```

**NEW CODE:**

```php
// Check user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
}
```

---

### register.php

**OLD CODE:**

```php
// Create user
$stmt = $pdo->prepare("
    INSERT INTO old_users (username, email, password_hash)
    VALUES (?, ?, ?)
");
$stmt->execute([$username, $email, $hash]);
$user_id = $pdo->lastInsertId();

// Create default profile
$stmt = $pdo->prepare("
    INSERT INTO old_profiles (user_id, slug, profile_name, is_primary)
    VALUES (?, ?, ?, 1)
");
$stmt->execute([$user_id, $username, $username]);
```

**NEW CODE:**

```php
// Create user
$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password)
    VALUES (?, ?, ?)
");
$stmt->execute([$username, $email, $hash]);
$user_id = $pdo->lastInsertId();

// Create default profile (display_order = 0 means primary)
$stmt = $pdo->prepare("
    INSERT INTO profiles (user_id, slug, name, display_order)
    VALUES (?, ?, ?, 0)
");
$stmt->execute([$user_id, $username, $username]);
```

---

## 🧪 Testing Script

Create a test file to verify all queries work:

**test_database_v3.php:**

```php
<?php
require_once 'config/db.php';

echo "<h1>Database v3 Test</h1>";

// Test 1: Get users
echo "<h2>Test 1: Users</h2>";
$stmt = $pdo->query("SELECT id, username, email FROM users LIMIT 5");
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";

// Test 2: Get profiles with stats
echo "<h2>Test 2: Profile Stats (View)</h2>";
$stmt = $pdo->query("SELECT * FROM v_profile_stats LIMIT 5");
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";

// Test 3: Stored procedure - get user profiles
echo "<h2>Test 3: User Profiles (Stored Procedure)</h2>";
$stmt = $pdo->prepare("CALL sp_get_user_profiles(?)");
$stmt->execute([1]); // Replace 1 with actual user_id
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";

// Test 4: Stored procedure - get profile full
echo "<h2>Test 4: Full Profile (Stored Procedure)</h2>";
$stmt = $pdo->prepare("CALL sp_get_profile_full(?)");
$stmt->execute(['fahmi']); // Replace with actual slug

echo "<h3>Profile Info:</h3>";
echo "<pre>" . print_r($stmt->fetch(), true) . "</pre>";
$stmt->nextRowset();

echo "<h3>Categories:</h3>";
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";
$stmt->nextRowset();

echo "<h3>Links:</h3>";
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";

// Test 5: Check foreign keys work
echo "<h2>Test 5: Foreign Key Relationships</h2>";
$stmt = $pdo->query("
    SELECT
        u.id as user_id,
        u.username,
        COUNT(DISTINCT p.id) as profile_count,
        COUNT(DISTINCT l.id) as link_count
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    LEFT JOIN links l ON p.id = l.profile_id
    GROUP BY u.id
    LIMIT 5
");
echo "<pre>" . print_r($stmt->fetchAll(), true) . "</pre>";

echo "<h2>All Tests Complete!</h2>";
?>
```

---

## 🔄 Gradual Migration Strategy

Instead of updating all files at once, update **ONE FILE AT A TIME** and test:

### Phase 1: Backend (Admin) Files

1. ✅ Update `admin/dashboard.php` → Test
2. ✅ Update `admin/profiles.php` → Test
3. ✅ Update `admin/settings.php` → Test
4. ✅ Update `admin/categories.php` → Test

### Phase 2: Authentication Files

1. ✅ Update `login.php` → Test login
2. ✅ Update `register.php` → Test registration
3. ✅ Update `forgot-password.php` → Test
4. ✅ Update `reset-password.php` → Test

### Phase 3: Public Files

1. ✅ Update `profile.php` → Test profile pages
2. ✅ Update `redirect.php` → Test link clicks
3. ✅ Update `index.php` → Test homepage

### Phase 4: Config Files

1. ✅ Update `config/auth_check.php` (if needed)
2. ✅ Update `config/session_handler.php` (if needed)

---

## 📊 Before/After Query Performance

### Example: Get User Profiles

**OLD (Complex JOIN):**

```php
// Execution time: ~45ms
$stmt = $pdo->prepare("
    SELECT
        p.profile_id, p.profile_name, p.slug, u.username,
        COUNT(l.link_id) as link_count,
        SUM(l.click_count) as total_clicks
    FROM old_profiles p
    JOIN old_users u ON p.user_id = u.user_id
    LEFT JOIN old_links l ON l.profile_id = p.profile_id AND l.is_active = 1
    WHERE p.user_id = ? AND p.is_active = 1
    GROUP BY p.profile_id, p.profile_name, p.slug, u.username
    ORDER BY p.is_primary DESC, p.created_at ASC
");
$stmt->execute([$user_id]);
```

**NEW (Stored Procedure with View):**

```php
// Execution time: ~12ms (3.75x faster!)
$stmt = $pdo->prepare("CALL sp_get_user_profiles(?)");
$stmt->execute([$user_id]);
```

---

## ✅ Code Update Checklist

-   [ ] Backup all PHP files
-   [ ] Create test_database_v3.php
-   [ ] Run test script - verify queries work
-   [ ] Update admin files (Phase 1)
-   [ ] Test admin dashboard
-   [ ] Update auth files (Phase 2)
-   [ ] Test login/register
-   [ ] Update public files (Phase 3)
-   [ ] Test profile pages
-   [ ] Update config files (Phase 4)
-   [ ] Full application test
-   [ ] Deploy to production

---

**Remember:** Kamu bilang "untuk perbaikan kode nanti saja dulu"  
Jadi file ini untuk **REFERENSI NANTI** setelah database migration selesai dan verified.

Database dulu, code belakangan. ✅
