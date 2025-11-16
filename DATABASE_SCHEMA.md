# 🗄️ Database Schema - LinkMy v2.0

```
┌─────────────────────────────────────────────────────────────────────┐
│                          LINKMY DATABASE v2.0                        │
└─────────────────────────────────────────────────────────────────────┘

┌──────────────────┐         ┌──────────────────┐         ┌──────────────────┐
│     USERS        │         │   APPEARANCE     │         │ GRADIENT_PRESETS │
├──────────────────┤         ├──────────────────┤         ├──────────────────┤
│ •user_id (PK)    │◄───┐    │ •appearance_id   │         │ •preset_id (PK)  │
│  username        │    │    │  (PK)            │         │  preset_name     │
│  password_hash   │    ├───►│ •user_id (FK)    │         │  gradient_css    │
│  page_slug       │    │    │  profile_title   │         │  preview_color_1 │
│  email           │    │    │  bio             │         │  preview_color_2 │
│  email_verified  │    │    │  profile_pic     │         │  is_default      │
│  created_at      │    │    │  bg_image        │         └──────────────────┘
└──────────────────┘    │    │  theme_name      │
                        │    │  button_style    │
                        │    │  font_family     │
         ┌──────────────┤    │                  │         ┌──────────────────┐
         │              │    │ ★NEW COLUMNS★    │         │  SOCIAL_ICONS    │
         │              │    │  custom_bg_color │         ├──────────────────┤
┌────────▼────────┐    │    │  custom_btn_color│         │ •icon_id (PK)    │
│ LINK_CATEGORIES  │    │    │  custom_txt_color│         │  platform_name   │
├──────────────────┤    │    │  gradient_preset │         │  icon_class      │
│ •category_id(PK) │    │    │  profile_layout  │         │  icon_color      │
│ •user_id (FK)────┼────┘    │  show_border     │         │  base_url        │
│  category_name   │         │  enable_anim     │         └──────────────────┘
│  category_icon   │         │  updated_at      │
│  category_color  │         └──────────────────┘
│  order_index     │
│  is_active       │         ┌──────────────────┐
│  created_at      │         │   SOCIAL_ICONS   │
└──────────────────┘         │   (Reference)    │
         │                   │                  │
         │                   │ 19 preset icons  │
         │                   │ Click to copy    │
         │                   │ Brand colors     │
┌────────▼────────┐         └──────────────────┘
│     LINKS        │
├──────────────────┤
│ •link_id (PK)    │         ┌──────────────────┐
│ •user_id (FK)────┼────┐    │ LINK_ANALYTICS   │
│ •category_id(FK)─┼─┐  │    │   (BONUS)        │
│  title           │ │  │    ├──────────────────┤
│  url             │ │  │    │ •analytics_id    │
│  order_index     │ │  │    │ •link_id (FK)────┼──┐
│  icon_class      │ │  │    │  clicked_at      │  │
│  click_count     │ │  │    │  referrer        │  │
│  is_active       │ │  │    │  user_agent      │  │
│  created_at      │ │  │    │  ip_address      │  │
└──────────────────┘ │  │    │  country         │  │
         └───────────┘  │    └──────────────────┘  │
                        │                           │
                        └───────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      EMAIL_VERIFICATIONS                             │
├─────────────────────────────────────────────────────────────────────┤
│ •id (PK) | email | otp_code | expires_at | is_used | ip_address    │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                      PASSWORD_RESETS                                 │
├─────────────────────────────────────────────────────────────────────┤
│ •id (PK) | email | reset_token | expires_at | is_used | ip_address │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────┐
│                  V_PUBLIC_PAGE_DATA (VIEW)                           │
├─────────────────────────────────────────────────────────────────────┤
│ Combines: users + appearance + links + link_categories             │
│ Purpose: Single query to fetch all public page data                │
│ Includes: All new columns from appearance table                    │
└─────────────────────────────────────────────────────────────────────┘
```

## 🔗 Relationships

```
users (1) ──────── (*) appearance    "One user has one appearance"
users (1) ──────── (*) links          "One user has many links"
users (1) ──────── (*) link_categories "One user has many categories"

link_categories (1) ──────── (*) links "One category has many links"
links (1) ──────── (*) link_analytics  "One link has many analytics records"

gradient_presets (standalone)          "Global presets for all users"
social_icons (standalone)              "Global icon library"
```

## 📊 Data Flow

```
┌─────────────┐
│   USER      │
│  selects    │
└──────┬──────┘
       │
       ├─► Gradient Preset ──► appearance.gradient_preset
       │
       ├─► Custom Colors ───► appearance.custom_bg_color
       │                  ──► appearance.custom_button_color
       │                  ──► appearance.custom_text_color
       │
       ├─► Profile Layout ──► appearance.profile_layout
       │
       ├─► Options ─────────► appearance.show_profile_border
       │                  ──► appearance.enable_animations
       │
       └─► Social Icon ─────► links.icon_class (copied from social_icons)
```

## 🎨 Feature Mapping

```
TAB: ADVANCED
├── Section: Gradient Backgrounds
│   └── Data: gradient_presets table (12 rows)
│       └── Saves to: appearance.gradient_preset
│
├── Section: Custom Colors
│   ├── Background Color → appearance.custom_bg_color
│   ├── Button Color → appearance.custom_button_color
│   └── Text Color → appearance.custom_text_color
│
├── Section: Profile Layout
│   └── Layout Choice → appearance.profile_layout
│       ├── centered
│       ├── left
│       └── minimal
│
├── Section: Additional Options
│   ├── Show Border → appearance.show_profile_border (boolean)
│   └── Enable Animations → appearance.enable_animations (boolean)
│
└── Section: Social Icons Library
    └── Data: social_icons table (19 rows)
        └── Copy to: links.icon_class
```

## 🔄 Update Priority

```
Priority 1: REQUIRED
├── appearance table → Add 7 new columns
├── links table → Add category_id column
└── Create views → Update v_public_page_data

Priority 2: CORE FEATURES
├── Create gradient_presets table + populate
├── Create social_icons table + populate
└── Create link_categories table + seed data

Priority 3: FUTURE FEATURES
└── Create link_analytics table (bonus)
```

## 💾 Storage Requirements

```
Estimated Storage per User:
├── appearance row: ~500 bytes (with new columns)
├── gradient_presets: 0 bytes (shared, one-time ~2KB total)
├── social_icons: 0 bytes (shared, one-time ~1KB total)
├── link_categories: ~300 bytes × 3 = 900 bytes
└── links: ~200 bytes × average links (10) = 2KB

Total per user: ~3.4 KB
For 1000 users: ~3.4 MB (negligible)
```

## ⚡ Query Performance

```
Most Common Queries:
1. SELECT from v_public_page_data WHERE page_slug = ?
   → Optimized view with indexes
   → Response time: < 10ms

2. SELECT from gradient_presets
   → 12 rows only, always fast
   → Response time: < 1ms

3. SELECT from social_icons
   → 19 rows only, always fast
   → Response time: < 1ms

4. UPDATE appearance SET ... WHERE user_id = ?
   → Direct update with index
   → Response time: < 5ms
```

## 🔐 Security Considerations

```
✅ All foreign keys with CASCADE DELETE
✅ Default values for new columns
✅ NULL allowed for optional fields
✅ Indexes on user_id columns
✅ Prepared statements in PHP
✅ htmlspecialchars() on all outputs
✅ Color values validated as hex
✅ File uploads sanitized
```

## 📈 Scalability

```
Current Design Supports:
├── Unlimited users
├── Unlimited links per user
├── Unlimited categories per user
├── Shared gradient presets (efficient)
├── Shared social icons (efficient)
└── Analytics tracking (bonus)

Future Expansion Possible:
├── Custom gradient creation
├── User-uploaded icons
├── Advanced analytics
├── Category sharing
└── Theme marketplace
```

---

**Legend:**

-   PK = Primary Key
-   FK = Foreign Key
-   (1) = One
-   (\*) = Many
-   ★ = New in v2.0

**Version:** 2.0.0  
**Last Updated:** November 15, 2025
