# 🎯 MULTI-PROFILE SYSTEM - LinkMy v2.0

## 📋 Table of Contents

1. [Overview](#overview)
2. [Database Migration](#database-migration)
3. [Implementation Progress](#implementation-progress)
4. [Architecture](#architecture)
5. [Features](#features)
6. [Installation Guide](#installation-guide)
7. [API Documentation](#api-documentation)
8. [Testing](#testing)
9. [Roadmap](#roadmap)

---

## 🌟 Overview

### What is Multi-Profile System?

LinkMy v2.0 memperkenalkan **Multi-Profile System** - fitur revolusioner yang memungkinkan 1 user account untuk mengelola **multiple profiles** yang completely independent:

```
User: Fahmi (1 Login)
├─ Profile 1: "Personal" (slug: fahmi)
│  ├─ Appearance: Purple gradient, boxed layout
│  ├─ Links: GitHub, LinkedIn, Portfolio
│  ├─ Bio: "Software Developer"
│  └─ Analytics: 150 views, 45 clicks
│
└─ Profile 2: "Business" (slug: fahmi-store)
   ├─ Appearance: Green theme, full width
   ├─ Links: Shopee, Tokopedia, WhatsApp
   ├─ Bio: "Toko Online Terpercaya"
   └─ Analytics: 320 views, 89 clicks
```

**Key Features:**

-   ✅ 2 profiles per user (free tier)
-   ✅ Each profile has unique slug
-   ✅ Independent appearance settings
-   ✅ Separate links & categories
-   ✅ Individual analytics
-   ✅ One-click profile switching
-   ✅ Profile cloning
-   ✅ Primary profile designation

---

## 💾 Database Migration

### Migration File

`database_multiprofile_system.sql` - Complete migration script

### What Gets Created/Modified:

#### 1. **New Tables**

**`profiles`** - Core table for multi-profile

```sql
CREATE TABLE profiles (
  profile_id INT PRIMARY KEY,
  user_id INT NOT NULL,
  slug VARCHAR(50) UNIQUE,
  profile_name VARCHAR(100),
  profile_description TEXT,
  profile_title VARCHAR(100),
  bio TEXT,
  profile_pic_filename VARCHAR(255),
  is_primary TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  last_accessed_at TIMESTAMP
);
```

**`profile_analytics`** - Per-profile statistics

```sql
CREATE TABLE profile_analytics (
  analytics_id INT PRIMARY KEY,
  profile_id INT,
  date DATE,
  page_views INT DEFAULT 0,
  unique_visitors INT DEFAULT 0,
  total_clicks INT DEFAULT 0,
  avg_time_on_page INT DEFAULT 0,
  bounce_rate DECIMAL(5,2) DEFAULT 0
);
```

**`profile_activity_log`** - Audit trail

```sql
CREATE TABLE profile_activity_log (
  log_id INT PRIMARY KEY,
  profile_id INT,
  user_id INT,
  action_type ENUM('created', 'updated', 'deleted', 'cloned', 'activated', 'deactivated'),
  action_details TEXT,
  ip_address VARCHAR(45),
  created_at TIMESTAMP
);
```

#### 2. **Updated Tables**

**`links`** - Add profile_id

```sql
ALTER TABLE links ADD COLUMN profile_id INT NOT NULL;
ALTER TABLE links ADD FOREIGN KEY (profile_id) REFERENCES profiles(profile_id) ON DELETE CASCADE;
```

**`link_categories`** - Add profile_id

```sql
ALTER TABLE link_categories ADD COLUMN profile_id INT NOT NULL;
ALTER TABLE link_categories ADD FOREIGN KEY (profile_id) REFERENCES profiles(profile_id) ON DELETE CASCADE;
```

**`user_appearance`** - Add profile_id

```sql
ALTER TABLE user_appearance ADD COLUMN profile_id INT NOT NULL;
ALTER TABLE user_appearance ADD FOREIGN KEY (profile_id) REFERENCES profiles(profile_id) ON DELETE CASCADE;
ALTER TABLE user_appearance ADD UNIQUE KEY unique_profile (profile_id);
```

**`users`** - Add active_profile_id

```sql
ALTER TABLE users ADD COLUMN active_profile_id INT NULL;
ALTER TABLE users ADD FOREIGN KEY (active_profile_id) REFERENCES profiles(profile_id) ON DELETE SET NULL;
```

#### 3. **Data Migration**

Migration automatically:

1. Creates 1 primary profile per existing user
2. Migrates current page_slug to profiles table
3. Links all existing links to primary profile
4. Links all categories to primary profile
5. Links appearance settings to primary profile

#### 4. **Views Updated**

```sql
-- v_public_page_data now includes profile_id
CREATE VIEW v_public_page_data AS
SELECT
  p.profile_id,
  p.user_id,
  p.slug AS page_slug,
  p.profile_title,
  p.bio,
  -- ... all appearance fields
FROM profiles p
JOIN users u ON p.user_id = u.user_id
LEFT JOIN user_appearance ua ON p.profile_id = ua.profile_id
WHERE p.is_active = 1;
```

#### 5. **Triggers**

Auto-sync primary profile slug with users.page_slug:

```sql
CREATE TRIGGER sync_primary_slug_on_update
AFTER UPDATE ON profiles
FOR EACH ROW
BEGIN
  IF NEW.is_primary = 1 THEN
    UPDATE users SET page_slug = NEW.slug WHERE user_id = NEW.user_id;
  END IF;
END;
```

---

## 🚀 Implementation Progress

### ✅ Phase 1: Database (COMPLETED)

-   [x] Create profiles table
-   [x] Create profile_analytics table
-   [x] Create profile_activity_log table
-   [x] Add profile_id to links
-   [x] Add profile_id to link_categories
-   [x] Add profile_id to user_appearance
-   [x] Migrate existing data
-   [x] Update views
-   [x] Create triggers
-   [x] Verification queries

### ✅ Phase 2: Profile Management (COMPLETED)

-   [x] Create admin/profiles.php
-   [x] Profile list UI with stats
-   [x] Create new profile handler
-   [x] Delete profile handler
-   [x] Set primary profile handler
-   [x] Clone profile handler
-   [x] Switch active profile handler
-   [x] AJAX slug availability checker
-   [x] Profile creation modal

### 🔄 Phase 3: Navigation (IN PROGRESS)

-   [x] Create admin_nav.php with profile switcher
-   [ ] Update all admin pages to include new nav
-   [ ] Session management for active profile
-   [ ] Profile context awareness

### ⏳ Phase 4: Dashboard Updates (PENDING)

-   [ ] Show stats for active profile only
-   [ ] Profile selector in header
-   [ ] Links filtered by profile_id
-   [ ] Analytics per profile

### ⏳ Phase 5: Appearance Updates (PENDING)

-   [ ] Load appearance for active profile
-   [ ] Save appearance to active profile_id
-   [ ] Profile context in all operations

### ⏳ Phase 6: Categories Updates (PENDING)

-   [ ] Filter categories by profile_id
-   [ ] Create categories for active profile
-   [ ] Update/delete with profile context

### ⏳ Phase 7: Profile Routing (PENDING)

-   [ ] Update profile.php to load by slug
-   [ ] Support multiple slugs per user
-   [ ] Route to correct profile data
-   [ ] Analytics tracking per profile

### ⏳ Phase 8: Analytics System (PENDING)

-   [ ] Track views per profile
-   [ ] Track clicks per profile
-   [ ] Daily analytics aggregation
-   [ ] Charts per profile

### ⏳ Phase 9: Testing & Polish (PENDING)

-   [ ] Test all CRUD operations
-   [ ] Test profile switching
-   [ ] Test data isolation
-   [ ] Test cloning
-   [ ] UI/UX improvements
-   [ ] Mobile responsiveness

### ⏳ Phase 10: Documentation (PENDING)

-   [x] Database migration docs
-   [x] Architecture overview
-   [ ] User guide
-   [ ] API documentation
-   [ ] Troubleshooting guide

---

## 🏗️ Architecture

### Database Schema

```
┌─────────────────┐
│     users       │
│  - user_id (PK) │
│  - username     │
│  - email        │
│  - active_profile_id (FK) ← Currently managing
└────────┬────────┘
         │ 1
         │
         │ N
┌────────▼────────────┐
│     profiles        │
│  - profile_id (PK)  │
│  - user_id (FK)     │
│  - slug (UNIQUE)    │
│  - profile_name     │
│  - is_primary       │ ← One primary per user
│  - is_active        │
└─────────┬───────────┘
          │ 1
          │
          ├─────────────────┐
          │ N               │ N
┌─────────▼──────┐   ┌──────▼─────────────┐
│     links      │   │  user_appearance   │
│  - link_id     │   │  - appearance_id   │
│  - profile_id  │   │  - profile_id      │
│  - title       │   │  - theme_name      │
│  - url         │   │  - gradient_preset │
└────────────────┘   └────────────────────┘
          │ N
┌─────────▼──────────┐
│  link_categories   │
│  - category_id     │
│  - profile_id      │
│  - category_name   │
└────────────────────┘
```

### Session Management

```php
$_SESSION['user_id']           // Logged in user
$_SESSION['active_profile_id']  // Currently managing profile
$_SESSION['page_slug']          // Active profile's slug
```

### Profile Context Flow

```
User Login
    ↓
Load Profiles (ORDER BY is_primary DESC)
    ↓
Set active_profile_id to primary
    ↓
Store in session & users.active_profile_id
    ↓
All queries filtered by active_profile_id
    ↓
User can switch profile → Update session
```

---

## ✨ Features

### 1. Profile Management

#### Create New Profile

-   Wizard dengan validasi
-   Real-time slug availability check
-   Auto-create default appearance
-   Activity logging

#### Delete Profile

-   Cannot delete primary profile
-   CASCADE delete all related data
-   Switch to primary if deleting active

#### Set Primary Profile

-   One primary per user
-   Auto-sync users.page_slug
-   Trigger-based sync

#### Clone Profile

-   Duplicate all settings
-   Copy appearance
-   Copy all links
-   Copy all categories
-   Auto-generate new slug

### 2. Profile Switching

#### Session-Based

```php
// Switch profile
$_SESSION['active_profile_id'] = $new_profile_id;

// All queries automatically filtered
SELECT * FROM links WHERE profile_id = $_SESSION['active_profile_id'];
```

#### UI Components

-   Navbar dropdown
-   Profile selector
-   Visual indicator (active badge)
-   Quick switch

### 3. Independent Content

#### Per-Profile Data:

-   **Appearance**: Theme, colors, layout, fonts
-   **Links**: Title, URL, icon, order
-   **Categories**: Name, icon, color
-   **Analytics**: Views, clicks, engagement
-   **Branding**: Profile pic, bio, title

#### Shared Data:

-   User account (username, email, password)
-   Subscription status
-   Account settings

### 4. Analytics Isolation

```sql
-- Track per profile
INSERT INTO profile_analytics (profile_id, date, page_views, total_clicks)
VALUES (?, CURDATE(), ?, ?);

-- Query per profile
SELECT * FROM profile_analytics WHERE profile_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

---

## 📥 Installation Guide

### Prerequisites

-   MySQL 5.7+ or MariaDB 10.2+
-   PHP 7.4+
-   Existing LinkMy installation
-   Backup of database

### Step 1: Backup

```bash
# Via command line
mysqldump -u root -p linkmy_db > backup_before_multiprofile.sql

# Via phpMyAdmin
Export → SQL format → Go
```

### Step 2: Run Migration

```sql
-- Open phpMyAdmin
-- Select linkmy_db database
-- Go to SQL tab
-- Copy & paste contents of database_multiprofile_system.sql
-- Click Go
-- Wait for completion (~10-30 seconds depending on data size)
```

### Step 3: Verify Migration

```sql
-- Check tables created
SHOW TABLES LIKE 'profile%';
-- Should show: profiles, profile_analytics, profile_activity_log

-- Check data migrated
SELECT COUNT(*) as profiles FROM profiles;
SELECT COUNT(*) as users FROM users;
-- Should be equal (1 profile per user initially)

-- Check foreign keys
SELECT * FROM links WHERE profile_id IS NULL;
-- Should return 0 rows

-- Check primary profiles
SELECT user_id, COUNT(*) FROM profiles WHERE is_primary = 1 GROUP BY user_id HAVING COUNT(*) != 1;
-- Should return 0 rows (each user has exactly 1 primary)
```

### Step 4: Upload New Files

```
/admin/
  ├─ profiles.php (NEW)
  └─ ... (existing files will be updated)

/partials/
  └─ admin_nav.php (UPDATED - profile switcher)
```

### Step 5: Test

1. Login to admin panel
2. Check new "Profiles" menu
3. Create second profile
4. Switch between profiles
5. Verify data isolation

---

## 📡 API Documentation

### Profile Management API

#### Create Profile

```php
POST /admin/profiles.php
Parameters:
  - profile_name: string (required)
  - slug: string (required, 3-50 chars, alphanumeric + hyphen)
  - profile_description: string (optional)

Response:
  - Success: Redirect to profiles.php?created=1
  - Error: $error message
```

#### Delete Profile

```php
GET /admin/profiles.php?delete_profile={profile_id}

Validations:
  - Profile must belong to user
  - Cannot delete primary profile
  - Confirmation required

Response:
  - Success: Redirect with success message
  - Error: $error message
```

#### Switch Profile

```php
GET /admin/profiles.php?switch_profile={profile_id}

Effects:
  - Updates $_SESSION['active_profile_id']
  - Updates $_SESSION['page_slug']
  - Updates users.active_profile_id
  - Redirects to dashboard

Response:
  - Success: Redirect to dashboard.php
  - Error: $error message
```

#### Set Primary

```php
GET /admin/profiles.php?set_primary_profile={profile_id}

Effects:
  - Unsets all is_primary flags for user
  - Sets new profile as primary
  - Trigger auto-updates users.page_slug

Response:
  - Success: Redirect with success message
  - Error: $error message
```

#### Clone Profile

```php
GET /admin/profiles.php?clone_profile={profile_id}

Clones:
  - Profile metadata
  - Appearance settings
  - All links
  - All categories

Generates:
  - New unique slug (original-copy, original-copy2, etc.)
  - New profile_name (Original (Copy))

Response:
  - Success: Redirect to profiles.php?cloned=1
  - Error: $error message
```

#### Check Slug Availability

```php
POST /admin/profiles.php
Parameters:
  - action: 'check_profile_slug'
  - slug: string

Response (JSON):
  {
    "available": true|false,
    "message": "Slug tersedia!" | "Slug sudah digunakan"
  }
```

---

## 🧪 Testing

### Test Scenarios

#### 1. Profile Creation

```
Given: User has 1 profile
When: User creates second profile with slug "test-profile"
Then:
  - Profile created in database
  - Default appearance created
  - Activity logged
  - Can switch to new profile
```

#### 2. Data Isolation

```
Given: User has 2 profiles
When: User adds link to Profile 1
Then:
  - Link only appears in Profile 1
  - Link NOT visible in Profile 2
  - Public URL shows correct profile's links
```

#### 3. Profile Switching

```
Given: User managing Profile 1
When: User switches to Profile 2
Then:
  - Session updated
  - Dashboard shows Profile 2 data
  - Links belong to Profile 2
  - Appearance from Profile 2
```

#### 4. Profile Cloning

```
Given: Profile 1 has 5 links and custom theme
When: User clones Profile 1
Then:
  - New profile created with slug "original-copy"
  - All 5 links copied
  - Theme settings copied
  - Can edit independently
```

#### 5. Primary Profile

```
Given: User has 2 profiles
When: User sets Profile 2 as primary
Then:
  - Profile 2 has is_primary = 1
  - Profile 1 has is_primary = 0
  - users.page_slug = Profile 2's slug
  - Old URLs still work via trigger
```

### SQL Test Queries

```sql
-- Test 1: Each user has exactly 1 primary
SELECT user_id, SUM(is_primary) as primary_count
FROM profiles
GROUP BY user_id
HAVING primary_count != 1;
-- Expected: 0 rows

-- Test 2: All links have valid profile_id
SELECT COUNT(*) FROM links l
LEFT JOIN profiles p ON l.profile_id = p.profile_id
WHERE p.profile_id IS NULL;
-- Expected: 0

-- Test 3: Profile count respects limit
SELECT user_id, COUNT(*) as profile_count
FROM profiles
GROUP BY user_id
HAVING profile_count > 2;
-- Expected: 0 rows (for free tier)

-- Test 4: Active profiles match session
SELECT u.user_id, u.active_profile_id, p.profile_id
FROM users u
LEFT JOIN profiles p ON u.active_profile_id = p.profile_id
WHERE u.active_profile_id IS NOT NULL AND p.profile_id IS NULL;
-- Expected: 0 rows

-- Test 5: Analytics isolated per profile
SELECT profile_id, COUNT(*) as analytics_count
FROM profile_analytics
GROUP BY profile_id;
-- Each profile should have separate counts
```

---

## 🗺️ Roadmap

### Immediate (Current Sprint)

-   [x] ✅ Database migration
-   [x] ✅ Profile management page
-   [ ] 🔄 Update all admin pages
-   [ ] 🔄 Profile routing
-   [ ] 🔄 Testing

### Near Term (Next 2 Weeks)

-   [ ] Per-profile analytics
-   [ ] Profile templates
-   [ ] Bulk operations
-   [ ] Profile export/import

### Mid Term (1-2 Months)

-   [ ] Premium tier (5 profiles)
-   [ ] Profile sharing/transfer
-   [ ] Advanced analytics
-   [ ] A/B testing between profiles

### Long Term (3-6 Months)

-   [ ] Unlimited profiles (business tier)
-   [ ] Custom domains per profile
-   [ ] Team collaboration
-   [ ] API access

---

## 🐛 Troubleshooting

### Issue: Migration fails with foreign key error

**Cause:** Orphaned data in links/categories  
**Solution:**

```sql
-- Find orphaned links
SELECT link_id, user_id FROM links l
LEFT JOIN users u ON l.user_id = u.user_id
WHERE u.user_id IS NULL;

-- Delete orphaned data
DELETE FROM links WHERE user_id NOT IN (SELECT user_id FROM users);
```

### Issue: Profile switcher not showing

**Cause:** Session not initialized  
**Solution:**

```php
// In admin_nav.php, ensure $user_profiles is loaded
if (!isset($user_profiles)) {
    $user_profiles = []; // Load from database
}
```

### Issue: Links disappearing after migration

**Cause:** profile_id not set  
**Solution:**

```sql
-- Check links without profile
SELECT COUNT(*) FROM links WHERE profile_id IS NULL;

-- Fix: assign to primary profile
UPDATE links l
JOIN profiles p ON l.user_id = p.user_id AND p.is_primary = 1
SET l.profile_id = p.profile_id
WHERE l.profile_id IS NULL;
```

### Issue: Cannot create second profile

**Cause:** Limit check failing  
**Solution:**

```sql
-- Verify profile count
SELECT user_id, COUNT(*) as count FROM profiles GROUP BY user_id;

-- If >2, delete extras
DELETE FROM profiles WHERE profile_id IN (
  SELECT profile_id FROM (
    SELECT profile_id, ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY is_primary DESC, created_at) as rn
    FROM profiles
  ) sub WHERE rn > 2
);
```

---

## 📞 Support

**Documentation:** `/MULTIPROFILE_GUIDE.md` (this file)  
**Migration Script:** `/database_multiprofile_system.sql`  
**Code:** `/admin/profiles.php`

**Common Issues:**

1. Check error logs: `tail -f /var/log/apache2/error.log`
2. Verify database: Run verification queries in migration file
3. Clear sessions: Logout and login again
4. Check permissions: Ensure profile_id is accessible

---

**Version:** 2.0.0  
**Last Updated:** November 29, 2025  
**Author:** Fahmi - LinkMy Development Team  
**Status:** 🔄 In Active Development
