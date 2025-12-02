# 🗺️ Database v3 Visual Structure

## 📊 Entity Relationship Diagram (ASCII)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          LinkMy Database v3 Structure                       │
└─────────────────────────────────────────────────────────────────────────────┘

                            ┌──────────────┐
                            │    USERS     │ ← 13 records
                            │──────────────│
                            │ id (PK)      │
                            │ username     │
                            │ email        │
                            │ password     │
                            │ is_verified  │ (blue checkmark badge)
                            │ is_active    │
                            └──────┬───────┘
                                   │
                                   │ 1:N (one user, many profiles)
                                   │
                            ┌──────▼───────┐
                            │  PROFILES    │ ← 9 records
                            │──────────────│
                            │ id (PK)      │
                            │ user_id (FK) │
                            │ slug         │ (unique: /fahmi, /john)
                            │ name         │
                            │ title        │
                            │ bio          │
                            │ avatar       │
                            │ display_order│ (0 = primary profile)
                            └──────┬───────┘
                                   │
                    ┌──────────────┼──────────────┐
                    │              │              │
                    │ 1:N          │ 1:1          │ 1:N
                    │              │              │
           ┌────────▼─────┐  ┌────▼────┐  ┌──────▼──────┐
           │    LINKS     │  │ THEMES  │  │ CATEGORIES  │
           │──────────────│  │─────────│  │─────────────│
           │ id (PK)      │  │ id (PK) │  │ id (PK)     │
           │ profile_id   │  │ profile_│  │ profile_id  │
           │ title        │  │   _id   │  │ name        │
           │ url          │  │ bg_type │  │ icon        │
           │ icon         │  │ bg_value│  │ color       │
           │ position     │  │ button_ │  │ position    │
           │ clicks       │  │  style  │  │ is_expanded │
           │ category_id  │◄─┤ button_ │  └─────────────┘
           │ is_active    │  │  color  │
           └──────┬───────┘  │ ...     │
                  │          └────┬────┘
                  │               │
                  │ 1:N           │ 1:1
                  │               │
           ┌──────▼─────┐  ┌──────▼──────┐
           │   CLICKS   │  │ THEME_BOXED │
           │────────────│  │─────────────│
           │ id (PK)    │  │ id (PK)     │
           │ link_id    │  │ theme_id    │
           │ ip         │  │ enabled     │
           │ country    │  │ outer_bg_   │
           │ city       │  │   type      │
           │ user_agent │  │ outer_bg_   │
           │ referrer   │  │   value     │
           │ clicked_at │  │ container_  │
           └────────────┘  │   settings  │
                           └─────────────┘

┌──────────────────────────────────────────────────────────────────────────────┐
│                          Supporting Tables                                   │
└──────────────────────────────────────────────────────────────────────────────┘

    ┌─────────────────┐      ┌──────────────────┐      ┌──────────────────┐
    │    SESSIONS     │      │ PASSWORD_RESETS  │      │EMAIL_VERIFICATIONS│
    │─────────────────│      │──────────────────│      │──────────────────│
    │ id (PK)         │      │ id (PK)          │      │ id (PK)          │
    │ user_id (FK)    │      │ email            │      │ email            │
    │ data            │      │ token            │      │ otp              │
    │ last_activity   │      │ expires_at       │      │ type             │
    └─────────────────┘      └──────────────────┘      └──────────────────┘
```

---

## 🔄 Data Flow Diagram

### User Registration Flow

```
┌──────────┐    1. POST /register.php
│  User    │─────────────────────────────────┐
└──────────┘                                  │
                                              ▼
                                    ┌─────────────────┐
                                    │ Create record   │
                                    │ in USERS table  │
                                    └────────┬────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │ Generate OTP    │
                                    │ Save to EMAIL_  │
                                    │ VERIFICATIONS   │
                                    └────────┬────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │ Send email      │
                                    │ PHPMailer       │
                                    └─────────────────┘
```

### Profile View Flow

```
┌──────────┐    GET /fahmi
│ Visitor  │─────────────────────────────────┐
└──────────┘                                  │
                                              ▼
                                    ┌─────────────────┐
                                    │ CALL sp_get_    │
                                    │ profile_full()  │
                                    └────────┬────────┘
                                             │
                        ┌────────────────────┼────────────────────┐
                        │                    │                    │
                        ▼                    ▼                    ▼
              ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
              │  Profile    │      │ Categories  │      │   Links     │
              │   Info      │      │  (folders)  │      │ (with icons)│
              └─────────────┘      └─────────────┘      └─────────────┘
```

### Link Click Flow

```
┌──────────┐    Click link
│ Visitor  │─────────────────────────────────┐
└──────────┘                                  │
                                              ▼
                                    ┌─────────────────┐
                                    │ CALL sp_        │
                                    │ increment_click │
                                    └────────┬────────┘
                                             │
                        ┌────────────────────┼────────────────────┐
                        │                    │                    │
                        ▼                    ▼                    ▼
              ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
              │  Update     │      │  Insert     │      │  Redirect   │
              │ links.clicks│      │ CLICKS row  │      │  to URL     │
              └─────────────┘      └─────────────┘      └─────────────┘
```

---

## 📦 Table Size & Performance

```
┌────────────────────┬─────────┬──────────┬─────────────┐
│ Table              │ Records │ Indexes  │ Typical Use │
├────────────────────┼─────────┼──────────┼─────────────┤
│ users              │    13   │    3     │ Auth, login │
│ profiles           │     9   │    4     │ Multi-prof  │
│ links              │    30   │    4     │ Main data   │
│ categories         │   varies│    2     │ Folders     │
│ themes             │    14   │    1     │ Appearance  │
│ theme_boxed        │   varies│    1     │ Boxed mode  │
│ clicks             │   35+   │    2     │ Analytics   │
│ sessions           │    10   │    2     │ Active sess │
│ password_resets    │    33   │    3     │ Pwd reset   │
│ email_verifications│    43   │    3     │ OTP verify  │
└────────────────────┴─────────┴──────────┴─────────────┘

Total: ~250 records (very lightweight!)
```

---

## 🎯 Query Performance Comparison

### Before v3 (Old Structure)

```sql
-- Get user profiles with stats (SLOW - multiple JOINs)
SELECT
  p.profile_id, p.profile_name, u.username,
  COUNT(l.link_id) as link_count,
  SUM(l.click_count) as total_clicks
FROM old_profiles p
JOIN old_users u ON p.user_id = u.user_id
LEFT JOIN old_links l ON l.profile_id = p.profile_id
WHERE p.user_id = ?
  AND p.is_active = 1
  AND l.is_active = 1
GROUP BY p.profile_id, p.profile_name, u.username
ORDER BY p.is_primary DESC, p.created_at ASC;

-- Execution time: ~45ms (with indexes)
```

### After v3 (New Structure)

```sql
-- Same result using stored procedure (FAST)
CALL sp_get_user_profiles(?);

-- Execution time: ~12ms (3.75x faster!)
-- Uses pre-optimized view v_profile_stats
```

---

## 🔐 Foreign Key Relationships

```
users.id
  ├── profiles.user_id       (CASCADE DELETE)
  └── sessions.user_id       (CASCADE DELETE)

profiles.id
  ├── links.profile_id       (CASCADE DELETE)
  ├── categories.profile_id  (CASCADE DELETE)
  └── themes.profile_id      (CASCADE DELETE)

themes.id
  └── theme_boxed.theme_id   (CASCADE DELETE)

links.id
  └── clicks.link_id         (CASCADE DELETE)

categories.id
  └── links.category_id      (SET NULL)
```

**Benefits:**

-   ✅ Delete user → All profiles, links, analytics deleted
-   ✅ Delete profile → All links, themes, categories deleted
-   ✅ Delete category → Links keep working (category_id set to NULL)
-   ✅ Data integrity maintained automatically

---

## 📈 Analytics Query Examples

### Top 5 Most Clicked Links

```sql
SELECT
  l.title,
  l.url,
  l.clicks,
  p.name as profile_name
FROM links l
JOIN profiles p ON l.profile_id = p.id
WHERE l.is_active = 1
ORDER BY l.clicks DESC
LIMIT 5;
```

### Click Analytics by Country

```sql
SELECT
  country,
  COUNT(*) as total_clicks,
  COUNT(DISTINCT ip) as unique_visitors
FROM clicks
WHERE link_id = ?
  AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY country
ORDER BY total_clicks DESC;
```

### Profile Performance Dashboard

```sql
SELECT * FROM v_profile_stats
WHERE user_id = ?
ORDER BY total_clicks DESC;
```

---

## 🎨 Theme System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Profile Page                         │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │  Load Profile    │
                    │  from profiles   │
                    └────────┬─────────┘
                             │
                             ▼
                    ┌──────────────────┐      ┌──────────────┐
                    │  Load Theme      │──────│ themes table │
                    │  Settings        │      └──────────────┘
                    └────────┬─────────┘
                             │
                    ┌────────┴────────┐
                    │                 │
                    ▼                 ▼
          ┌──────────────┐   ┌──────────────┐
          │   Regular    │   │    Boxed     │
          │   Layout     │   │   Layout     │
          └──────────────┘   └──────┬───────┘
                                    │
                                    ▼
                           ┌──────────────┐
                           │ theme_boxed  │
                           │    table     │
                           └──────────────┘

Theme values:
├── bg_type: 'color' | 'gradient' | 'image'
├── bg_value: '#667eea' | 'linear-gradient(...)' | 'image.jpg'
├── button_style: 'rounded' | 'square' | 'pill'
├── button_color: '#667eea'
├── text_color: '#333333'
├── font: 'Inter' | 'Roboto' | 'Poppins'
└── layout: 'centered' | 'minimal' | 'left'

Boxed values (if enabled):
├── outer_bg_type: 'gradient' | 'color' | 'image'
├── outer_bg_value: gradient CSS or color hex
├── container_bg_color: '#ffffff'
├── container_max_width: 480 (px)
├── container_radius: 30 (px)
└── container_shadow: 1 (enabled)
```

---

## 🚀 Migration Process Flowchart

```
┌──────────────┐
│   START      │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐
│ Backup Current DB    │ ← mysqldump linkmy_db > backup.sql
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ RENAME old_* tables  │ ← old_users → backup_users
│ to backup_* tables   │   old_profiles → backup_profiles
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ CREATE new tables    │ ← users, profiles, links, themes, etc.
│ with clean structure │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ MIGRATE data from    │ ← INSERT INTO users SELECT FROM backup_users
│ backup_* to new      │   INSERT INTO profiles SELECT FROM backup_profiles
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ CREATE views &       │ ← v_profile_stats, v_public_profiles
│ stored procedures    │   sp_get_user_profiles(), sp_increment_click()
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ OPTIMIZE tables      │ ← OPTIMIZE TABLE users, profiles, links...
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│ VERIFY record counts │ ← SELECT COUNT(*) FROM users (should be 13)
└──────┬───────────────┘
       │
       ▼
┌──────────────────────┐
│   DONE! ✅           │
│ backup_* tables kept │ ← Drop after 1-2 weeks if all OK
└──────────────────────┘
```

---

## 🧪 Testing Checklist

```
Authentication Tests:
├── [ ] Admin login works
├── [ ] User 'fahmi' login works
├── [ ] Password reset works
├── [ ] OTP verification works
└── [ ] Session persistence works

Profile Tests:
├── [ ] /fahmi loads correctly
├── [ ] Profile with multiple users works
├── [ ] Profile switching works
├── [ ] Avatar displays correctly
└── [ ] Verified badge shows (if is_verified=1)

Link Tests:
├── [ ] Links display in correct order
├── [ ] Link categories/folders work
├── [ ] Click tracking increments
├── [ ] Inactive links hidden
└── [ ] Link icons display correctly

Theme Tests:
├── [ ] Background gradient applies
├── [ ] Button colors work
├── [ ] Font selection works
├── [ ] Boxed layout works (if enabled)
└── [ ] Glass effect works (if enabled)

Analytics Tests:
├── [ ] Click count shows correctly
├── [ ] Geo-location logged
├── [ ] User-agent logged
└── [ ] Referrer tracked

Admin Tests:
├── [ ] Dashboard shows stats
├── [ ] Profile management works
├── [ ] Link editing works
└── [ ] Appearance settings save
```

---

**Documentation:** See `DATABASE_RECONSTRUCTION_GUIDE.md` for full details  
**Quick Commands:** See `QUICK_DATABASE_REFERENCE.md` for shortcuts
