# 📋 LinkMy v2.0 Deployment Checklist

> Use this checklist to ensure a smooth deployment from v1.0 to v2.0

## Pre-Deployment Phase

### 🔍 Verify Current Setup

-   [ ] LinkMy v1.0 is working correctly
-   [ ] All existing users can log in
-   [ ] Links are displaying properly on public pages
-   [ ] Database backups are available
-   [ ] Server meets requirements (PHP 7.4+, MySQL 5.7+)

### 📦 Prepare v2.0 Files

-   [ ] Download/have `database_update_v2.sql` file
-   [ ] Download/have updated `admin/appearance.php` file
-   [ ] All documentation files present (6 markdown files)
-   [ ] Read through DEPLOYMENT.md guide
-   [ ] Read through QUICK_START.md for feature overview

### 💾 Create Backups

-   [ ] Backup database: `mysqldump -u root -p linkmy_db > backup_v1.sql`
-   [ ] Backup `admin/appearance.php` file
-   [ ] Backup entire project folder
-   [ ] Store backups in safe location (outside web root)
-   [ ] Verify backup files are not corrupted

---

## Database Migration Phase

### 🗄️ Apply Database Updates

-   [ ] Open phpMyAdmin or MySQL CLI
-   [ ] Select `linkmy_db` database
-   [ ] Import `database_update_v2.sql` file
-   [ ] Wait for "Success" message
-   [ ] Check for any error messages

### ✅ Verify Database Changes

#### Check New Tables Created

-   [ ] `gradient_presets` table exists
-   [ ] `social_icons` table exists
-   [ ] `link_categories` table exists
-   [ ] `link_analytics` table exists

#### Verify Table Counts

Run these queries and confirm expected counts:

```sql
-- Should return 12
SELECT COUNT(*) FROM gradient_presets;

-- Should return 19
SELECT COUNT(*) FROM social_icons;

-- Should return 1+ (default category per user)
SELECT COUNT(*) FROM link_categories;
```

-   [ ] `gradient_presets` has 12 rows
-   [ ] `social_icons` has 19 rows
-   [ ] `link_categories` has rows for each user

#### Check New Columns in Appearance Table

Run this query:

```sql
DESCRIBE appearance;
```

Verify these 7 new columns exist:

-   [ ] `custom_bg_color` (VARCHAR 7, NULL)
-   [ ] `custom_button_color` (VARCHAR 7, NULL)
-   [ ] `custom_text_color` (VARCHAR 7, NULL)
-   [ ] `gradient_preset` (VARCHAR 50, NULL)
-   [ ] `profile_layout` (VARCHAR 20, DEFAULT 'centered')
-   [ ] `show_profile_border` (BOOLEAN, DEFAULT 0)
-   [ ] `enable_animations` (BOOLEAN, DEFAULT 1)

#### Check New Column in Links Table

-   [ ] `category_id` column exists in `links` table
-   [ ] Foreign key constraint is active

#### Verify View Update

```sql
SELECT * FROM v_public_page_data LIMIT 1;
```

-   [ ] View returns data without errors
-   [ ] New columns visible in result set

---

## File Deployment Phase

### 📁 Update Application Files

#### If appearance.php was already modified:

-   [ ] Backup current `admin/appearance.php`
-   [ ] Compare with new version (check diff)
-   [ ] Merge any custom changes carefully
-   [ ] Replace with new `admin/appearance.php`

#### If appearance.php is unmodified:

-   [ ] Simply replace `admin/appearance.php` with new version

### 🔒 Set Permissions

```bash
chmod 644 admin/appearance.php
chmod 755 uploads/
chmod 755 uploads/profile_pics/
chmod 755 uploads/backgrounds/
```

-   [ ] Files have 644 permissions
-   [ ] Directories have 755 permissions
-   [ ] Web server can read all files
-   [ ] Web server can write to uploads/

---

## Testing Phase

### 🧪 Test Basic Functionality

#### Login and Navigation

-   [ ] Can log in with existing account
-   [ ] Dashboard loads without errors
-   [ ] Settings page accessible
-   [ ] No console errors in browser

#### Test Appearance Page

-   [ ] Navigate to `admin/appearance.php`
-   [ ] "Advanced" tab is visible with "New" badge
-   [ ] Profile tab still works
-   [ ] Theme tab still works
-   [ ] Media tab still works

### 🎨 Test Advanced Tab Features

#### Gradient Presets

-   [ ] All 12 gradient cards display
-   [ ] Color dots show for each gradient
-   [ ] Click on gradient card - becomes active
-   [ ] Preview updates when gradient selected
-   [ ] Selected gradient saved after "Save Changes"

#### Custom Colors

-   [ ] Background color picker displays
-   [ ] Button color picker displays
-   [ ] Text color picker displays
-   [ ] Hex values display and sync
-   [ ] Picking color updates preview
-   [ ] Custom colors override gradient when set

#### Profile Layouts

-   [ ] Centered layout card displays
-   [ ] Left Aligned layout card displays
-   [ ] Minimal layout card displays
-   [ ] Clicking layout updates preview
-   [ ] Selected layout saved after "Save Changes"

#### Additional Options

-   [ ] "Show Profile Border" toggle works
-   [ ] "Enable Animations" toggle works
-   [ ] Toggling updates preview
-   [ ] Settings saved after "Save Changes"

#### Social Icons Library

-   [ ] All 19 social icons display
-   [ ] Icons show correct brand colors
-   [ ] Clicking icon shows "Copied!" toast
-   [ ] Class name copied to clipboard
-   [ ] Can paste into Dashboard link icon field

### 🌐 Test Public Profile

#### Visit Public Profile

Navigate to: `http://localhost/profile.php?u=testuser`

-   [ ] Page loads without errors
-   [ ] Selected gradient/colors display
-   [ ] Profile layout applied correctly
-   [ ] Border shows/hides based on setting
-   [ ] Animations work if enabled
-   [ ] Links display correctly
-   [ ] Link clicks tracked in database

#### Test on Multiple Devices

-   [ ] Desktop (1920x1080)
-   [ ] Laptop (1366x768)
-   [ ] Tablet (768x1024)
-   [ ] Mobile (375x667)
-   [ ] All features responsive

### 📊 Test Database Operations

#### Test Link Categories

```sql
-- Try creating a category via application later (v2.1)
-- For now, verify foreign key works
SELECT l.title, lc.category_name
FROM links l
LEFT JOIN link_categories lc ON l.category_id = lc.category_id
WHERE l.user_id = 1;
```

-   [ ] Query runs without errors
-   [ ] Links can have categories

#### Test Analytics

```sql
-- After clicking some links, check analytics
SELECT * FROM link_analytics ORDER BY clicked_at DESC LIMIT 5;
```

-   [ ] Link clicks recorded
-   [ ] Referrer captured
-   [ ] User agent captured

---

## Post-Deployment Phase

### 📢 Notify Users

#### Create Announcement

-   [ ] Announce v2.0 features to users
-   [ ] Create tutorial/guide for users
-   [ ] Send email notification (optional)
-   [ ] Update help documentation

#### Sample Announcement:

```
🎉 LinkMy v2.0 is LIVE!

New Features:
✨ 12 Beautiful Gradient Presets
🎨 Custom Color Picker
📐 3 Profile Layouts
📱 19 Social Icons Library
⚙️ Enhanced Options

Access the new features:
1. Go to Appearance > Advanced tab
2. Explore and customize!
3. Check out our Quick Start guide

Enjoy! 🚀
```

### 📝 Update Documentation

-   [ ] Update main README.md (already done)
-   [ ] Ensure all 6 doc files accessible
-   [ ] Add links to docs in dashboard (optional)
-   [ ] Create video tutorial (optional)

### 🔍 Monitor Performance

#### Day 1-3: Intensive Monitoring

-   [ ] Check error logs hourly
-   [ ] Monitor database performance
-   [ ] Watch for unusual queries
-   [ ] Check server resource usage
-   [ ] Verify backups still running

#### Week 1: Regular Monitoring

-   [ ] Check error logs daily
-   [ ] Review user feedback
-   [ ] Fix any reported bugs
-   [ ] Optimize slow queries if any

#### Monitor These Metrics:

```sql
-- Page load performance
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_links FROM links;
SELECT COUNT(*) as total_appearances FROM appearance;

-- Feature adoption
SELECT gradient_preset, COUNT(*) as count
FROM appearance
WHERE gradient_preset IS NOT NULL
GROUP BY gradient_preset;

SELECT profile_layout, COUNT(*) as count
FROM appearance
GROUP BY profile_layout;
```

-   [ ] Track feature adoption rates
-   [ ] Identify popular gradients
-   [ ] Monitor database size growth
-   [ ] Check query performance

---

## Rollback Procedure (If Needed)

### 🔙 If Critical Issues Occur

#### Rollback Database

```bash
# Stop accepting new data first!
mysql -u root -p linkmy_db < backup_v1.sql
```

-   [ ] Stop application (maintenance mode)
-   [ ] Restore database from backup
-   [ ] Restore old appearance.php file
-   [ ] Test basic functionality
-   [ ] Investigate issue before retrying

#### Partial Rollback (Keep New Features)

If only specific features have issues:

```sql
-- Disable only problematic features
UPDATE appearance SET gradient_preset = NULL;
-- Or
UPDATE appearance SET custom_bg_color = NULL;
```

---

## Success Criteria

### ✅ Deployment is Successful When:

-   [ ] All database tables created without errors
-   [ ] 12 gradients + 19 social icons populated
-   [ ] Advanced tab accessible and functional
-   [ ] All existing v1.0 features still work
-   [ ] No errors in PHP error logs
-   [ ] No JavaScript console errors
-   [ ] Public profiles display correctly
-   [ ] Link clicks still tracked
-   [ ] User sessions stable
-   [ ] Performance acceptable (< 2s page load)

### 🎉 Additional Success Indicators:

-   [ ] Users exploring Advanced tab
-   [ ] New gradients being used
-   [ ] Custom colors being set
-   [ ] Social icons being copied
-   [ ] Positive user feedback
-   [ ] No support tickets about bugs

---

## Timeline Estimate

### Recommended Deployment Schedule:

**Phase 1: Preparation (30 min)**

-   Backup everything
-   Read documentation
-   Prepare files

**Phase 2: Database Migration (15 min)**

-   Apply database_update_v2.sql
-   Verify all changes
-   Test queries

**Phase 3: File Deployment (10 min)**

-   Update appearance.php
-   Set permissions
-   Clear caches

**Phase 4: Testing (45 min)**

-   Test all new features
-   Test existing features
-   Test on multiple devices
-   Test public profiles

**Phase 5: Go Live (5 min)**

-   Remove maintenance mode (if used)
-   Announce to users
-   Begin monitoring

**Total Time: ~2 hours** (including buffer)

**Best Time to Deploy:**

-   Low traffic period (early morning/late night)
-   Weekday (avoid weekends for faster support)
-   When you can monitor for 2-3 hours after
-   When rollback help available if needed

---

## Emergency Contacts

### Keep These Ready During Deployment:

**Technical Resources:**

-   [ ] Database admin credentials
-   [ ] Server SSH/FTP access
-   [ ] Backup locations documented
-   [ ] Documentation files accessible
-   [ ] Rollback scripts ready

**Support Contacts:**

-   [ ] Database administrator
-   [ ] Server administrator
-   [ ] Team members for testing
-   [ ] Documentation accessible

**Tools Ready:**

-   [ ] phpMyAdmin open
-   [ ] MySQL CLI access tested
-   [ ] FTP/SFTP client ready
-   [ ] Text editor ready
-   [ ] Browser dev tools open

---

## Post-Deployment Checklist

### After 24 Hours:

-   [ ] Review error logs
-   [ ] Check analytics data
-   [ ] Verify backups completed
-   [ ] Collect user feedback
-   [ ] Document any issues
-   [ ] Update this checklist if needed

### After 1 Week:

-   [ ] Review feature adoption
-   [ ] Identify popular features
-   [ ] Plan next improvements (v2.1)
-   [ ] Update roadmap
-   [ ] Celebrate successful deployment! 🎉

---

## Notes & Issues Log

Use this space to document any issues during deployment:

```
Date: ________________
Issue: _______________________________________________
Solution: ____________________________________________
Time to Resolve: _____________________________________

Date: ________________
Issue: _______________________________________________
Solution: ____________________________________________
Time to Resolve: _____________________________________
```

---

## Final Verification Commands

Run these commands to verify everything:

```sql
-- Verify all tables exist
SHOW TABLES;

-- Should show: users, links, appearance, gradient_presets,
--              social_icons, link_categories, link_analytics

-- Check data population
SELECT 'gradient_presets' as table_name, COUNT(*) as rows FROM gradient_presets
UNION ALL
SELECT 'social_icons', COUNT(*) FROM social_icons
UNION ALL
SELECT 'link_categories', COUNT(*) FROM link_categories
UNION ALL
SELECT 'users', COUNT(*) FROM users
UNION ALL
SELECT 'links', COUNT(*) FROM links
UNION ALL
SELECT 'appearance', COUNT(*) FROM appearance;

-- Should show reasonable counts for all tables
```

---

**🎊 Congratulations on deploying LinkMy v2.0!**

If you've checked off all items above, your deployment is complete and successful!

Need help? Check:

-   📖 [DEPLOYMENT.md](DEPLOYMENT.md) - Full deployment guide
-   🚀 [QUICK_START.md](QUICK_START.md) - User guide
-   📋 [FEATURES_V2.md](FEATURES_V2.md) - Feature documentation
-   🐛 [CHANGELOG.md](CHANGELOG.md) - Known issues

**Made with ❤️ by LinkMy Team**

Version: 2.0.0 | Last Updated: November 15, 2024
