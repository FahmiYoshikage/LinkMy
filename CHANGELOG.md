# 📝 CHANGELOG - LinkMy

## [2.0.0] - 2025-11-15

### 🎉 Major Release - Advanced Customization Features

#### ✨ Added

**New Tab - "Advanced"**

-   Added new "Advanced" tab in Appearance page with badge "New"
-   Complete advanced customization interface with multiple sections

**Gradient Backgrounds System**

-   Added 12 pre-designed gradient presets:
    -   Purple Dream (#667eea → #764ba2)
    -   Ocean Blue (#00c6ff → #0072ff)
    -   Sunset Orange (#ff6a00 → #ee0979)
    -   Fresh Mint (#00b09b → #96c93d)
    -   Pink Lemonade (#ff9a9e → #fecfef)
    -   Royal Purple (#8e2de2 → #4a00e0)
    -   Fire Blaze (#f85032 → #e73827)
    -   Emerald Water (#348f50 → #56b4d3)
    -   Candy Shop (#f093fb → #f5576c)
    -   Cool Blues (#4facfe → #00f2fe)
    -   Warm Flame (#ff9a56 → #ff6a88)
    -   Deep Sea (#2e3192 → #1bffff)
-   Visual gradient cards with color indicators
-   Click to select with active state highlighting
-   Real-time preview integration

**Custom Color System**

-   Added 3 color pickers for full customization:
    -   Background Color picker with hex display
    -   Button Color picker with hex display
    -   Text Color picker with hex display
-   Live preview updates on color change
-   Hex value display synced with color picker
-   Override gradient presets with custom colors

**Profile Layout Options**

-   Added 3 profile layout styles:
    -   Centered: Classic centered layout (default)
    -   Left Aligned: Modern left-side layout
    -   Minimal: Compact minimal design
-   Visual layout preview cards
-   Icon + lines representation of each layout
-   Active state indication
-   Real-time layout switching

**Social Icons Library**

-   Added 19 social media platform icons:
    -   Instagram (#E4405F)
    -   Facebook (#1877F2)
    -   Twitter/X (#000000)
    -   LinkedIn (#0A66C2)
    -   GitHub (#181717)
    -   YouTube (#FF0000)
    -   TikTok (#000000)
    -   WhatsApp (#25D366)
    -   Telegram (#26A5E4)
    -   Discord (#5865F2)
    -   Twitch (#9146FF)
    -   Spotify (#1DB954)
    -   Medium (#000000)
    -   Reddit (#FF4500)
    -   Pinterest (#E60023)
    -   Snapchat (#FFFC00)
    -   Email (#EA4335)
    -   Website (#667eea)
    -   Generic Link (#6c757d)
-   Brand-colored icons in grid display
-   Click-to-copy icon class functionality
-   Toast notification on successful copy
-   Base URL hints for each platform

**Additional Customization Options**

-   Show/Hide profile picture border toggle
-   Enable/Disable hover animations toggle
-   Form switches with descriptions
-   Persistent settings storage

#### 🗄️ Database Changes

**New Tables:**

-   `gradient_presets` - Store gradient configurations
    -   Columns: preset_id, preset_name, gradient_css, preview_color_1, preview_color_2, is_default
    -   Pre-populated with 12 gradient designs
-   `social_icons` - Social media icons library
    -   Columns: icon_id, platform_name, icon_class, icon_color, base_url
    -   Pre-populated with 19 platform icons
-   `link_categories` - Organize links by category
    -   Columns: category_id, user_id, category_name, category_icon, category_color, order_index, is_active
    -   Foreign key to users table
    -   Default categories: Social Media, Professional, Content
-   `link_analytics` - Track link performance (bonus)
    -   Columns: analytics_id, link_id, clicked_at, referrer, user_agent, ip_address, country
    -   Foreign key to links table

**Updated Tables:**

-   `appearance` - Added 7 new columns:

    -   `custom_bg_color` VARCHAR(20) - Custom background color
    -   `custom_button_color` VARCHAR(20) - Custom button color
    -   `custom_text_color` VARCHAR(20) - Custom text color
    -   `gradient_preset` VARCHAR(50) - Selected gradient name
    -   `profile_layout` VARCHAR(20) - Layout: centered/left/minimal
    -   `show_profile_border` TINYINT(1) - Show profile border flag
    -   `enable_animations` TINYINT(1) - Enable animations flag

-   `links` - Added 1 new column:
    -   `category_id` INT(11) - Foreign key to link_categories

**Updated Views:**

-   `v_public_page_data` - Enhanced to include:
    -   All new appearance columns
    -   Category information from link_categories
    -   Maintains backward compatibility

#### 🎨 UI/UX Improvements

**CSS Additions (200+ lines):**

-   `.gradient-preset-card` - Gradient selection cards
-   `.gradient-preview` - Visual gradient display
-   `.color-dot` - Gradient color indicators
-   `.color-picker-wrapper` - Custom color picker styling
-   `.layout-card` - Layout option cards
-   `.layout-preview` - Visual layout representation
-   `.layout-icon` - Layout avatar representation
-   `.layout-lines` - Layout text lines representation
-   `.social-icons-grid` - Responsive icon grid
-   `.social-icon-item` - Individual icon styling
-   Smooth hover transitions and animations
-   Active state styling for all selectable items
-   Responsive design for mobile devices

**JavaScript Enhancements:**

-   `selectGradient(name, css)` - Handle gradient preset selection
-   `selectLayout(layout)` - Handle layout option selection
-   Color picker event listeners for real-time updates
-   Hex display synchronization with color pickers
-   Click-to-copy functionality for social icons
-   Toast notification system for user feedback
-   Enhanced live preview updates
-   Background color preview on gradient select
-   Button color preview on custom color change
-   Text color preview on custom color change

#### 📱 Enhanced Live Preview

**New Preview Features:**

-   Real-time gradient changes
-   Instant custom color updates
-   Layout structure visualization
-   Profile border toggle preview
-   Animation state preview
-   Maintains all existing preview functionality

#### 📚 Documentation

**New Documentation Files:**

-   `database_update_v2.sql` - Complete database migration script
-   `FEATURES_V2.md` - Comprehensive feature documentation
-   `QUICK_START.md` - Quick start guide for users
-   `UPDATE_SUMMARY.md` - Summary of all changes
-   `DATABASE_SCHEMA.md` - Visual database schema
-   `CHANGELOG.md` - This file

#### 🔧 Technical Improvements

-   Backward compatible with v1.x
-   Optimized database queries
-   Indexed foreign keys for performance
-   Default values for all new columns
-   Cascade delete for referential integrity
-   Prepared statements for security
-   XSS protection on all user inputs
-   Hex color validation

---

## [1.0.0] - 2025-11-10

### Initial Release

#### Features

-   User registration & authentication
-   Email verification with OTP
-   Profile customization (basic)
    -   Profile title
    -   Bio
    -   Profile picture upload
    -   Background image upload
-   Theme selection (Light/Dark/Gradient)
-   Button styles (Rounded/Sharp/Pill)
-   Link management
    -   Add/Edit/Delete links
    -   Drag & drop reordering
    -   Icon selection
    -   Click tracking
-   Public profile pages
-   Dashboard for link management
-   Settings page
-   Favicon system

#### Database Tables

-   `users` - User accounts
-   `appearance` - Profile appearance settings
-   `links` - User links
-   `email_verifications` - OTP verification
-   `password_resets` - Password reset tokens
-   `v_public_page_data` - Public page data view

#### Security

-   Password hashing with bcrypt
-   Session management
-   CSRF protection
-   SQL injection prevention
-   XSS protection

---

## Version History

| Version | Date       | Changes                         |
| ------- | ---------- | ------------------------------- |
| 2.0.0   | 2025-11-15 | Advanced customization features |
| 1.0.0   | 2025-11-10 | Initial release                 |

---

## Upgrade Guide

### From 1.0.0 to 2.0.0

**Prerequisites:**

-   Backup your database before upgrading
-   PHP 7.4+ required
-   MySQL 5.7+ or MariaDB 10.2+

**Steps:**

1. **Backup Database**

    ```bash
    mysqldump -u root -p linkmy_db > backup_v1.sql
    ```

2. **Run Database Update**

    ```bash
    mysql -u root -p linkmy_db < database_update_v2.sql
    ```

    Or import via phpMyAdmin

3. **Verify Update**

    ```sql
    -- Check new tables
    SHOW TABLES;

    -- Check new columns
    DESCRIBE appearance;
    ```

4. **Test Features**

    - Login to admin panel
    - Navigate to Appearance → Advanced
    - Test all new features
    - Verify public page displays correctly

5. **Optional: Clear Cache**
    ```bash
    # Clear browser cache
    Ctrl + Shift + Del (Windows/Linux)
    Cmd + Shift + Del (Mac)
    ```

**Rollback (if needed):**

```bash
# Restore from backup
mysql -u root -p linkmy_db < backup_v1.sql
```

---

## Known Issues

**v2.0.0:**

-   None reported yet

**v1.0.0:**

-   ✅ Fixed in v2.0.0: Limited customization options
-   ✅ Fixed in v2.0.0: No gradient presets
-   ✅ Fixed in v2.0.0: Limited color customization

---

## Credits

**Development Team:**

-   LinkMy Core Team

**Libraries Used:**

-   Bootstrap 5.3.8
-   Bootstrap Icons 1.11.0
-   PHPMailer 7.0.0

**Special Thanks:**

-   All beta testers
-   Community contributors

---

## License

MIT License - See LICENSE file for details

---

**For support and updates:**

-   Documentation: See FEATURES_V2.md
-   Quick Start: See QUICK_START.md
-   Database Schema: See DATABASE_SCHEMA.md

**Last Updated:** November 15, 2025
