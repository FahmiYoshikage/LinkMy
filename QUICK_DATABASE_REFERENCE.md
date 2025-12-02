# 🎯 Quick Command Reference - Database v3

## 📦 Files You Need

```
✅ database_reconstruction_v3.sql  (Main migration)
✅ database_rollback.sql           (Safety rollback)
✅ DATABASE_RECONSTRUCTION_GUIDE.md (Full docs)
```

---

## 🚀 One-Line Migration Commands

### Via Docker (Recommended)

```bash
cd /opt/LinkMy && docker exec -i linkmy-mysql mysql -u linkmy_user -p'admin123' linkmy_db < database_reconstruction_v3.sql
```

### Via phpMyAdmin

1. Open: `http://your-server/phpmyadmin`
2. Select: `linkmy_db`
3. Import: `database_reconstruction_v3.sql`

---

## ✅ Quick Verification

```sql
-- Check record counts
SELECT 'Users' as T, COUNT(*) as N FROM users
UNION SELECT 'Profiles', COUNT(*) FROM profiles
UNION SELECT 'Links', COUNT(*) FROM links
UNION SELECT 'Clicks', COUNT(*) FROM clicks;

-- Expected output:
-- Users     | 13
-- Profiles  | 9
-- Links     | 30
-- Clicks    | 35+
```

---

## 🔥 Emergency Rollback

```bash
# If something goes wrong
docker exec -i linkmy-mysql mysql -u linkmy_user -p'admin123' linkmy_db < database_rollback.sql
```

---

## 📋 Table Name Changes

| Old Name                  | New Name                 | Why             |
| ------------------------- | ------------------------ | --------------- |
| `old_users`               | `users`                  | Remove prefix   |
| `old_profiles`            | `profiles`               | Remove prefix   |
| `old_links`               | `links`                  | Remove prefix   |
| `old_user_appearance`     | `themes` + `theme_boxed` | Simplified      |
| `old_sessions`            | `sessions`               | Remove prefix   |
| `old_password_resets`     | `password_resets`        | Remove prefix   |
| `old_email_verifications` | `email_verifications`    | Remove prefix   |
| `old_link_analytics`      | `clicks`                 | Better name     |
| `old_profile_analytics`   | _(removed)_              | Not used        |
| `link_categories`         | `categories`             | Shorter name    |
| `gradient_presets`        | _(removed)_              | Hardcode in CSS |
| `social_icons`            | _(removed)_              | Hardcode in CSS |

---

## 📊 Column Name Changes

| Old Column             | New Column   | Table    |
| ---------------------- | ------------ | -------- |
| `user_id`              | `id`         | users    |
| `profile_id`           | `id`         | profiles |
| `link_id`              | `id`         | links    |
| `password_hash`        | `password`   | users    |
| `profile_pic_filename` | `avatar`     | profiles |
| `profile_name`         | `name`       | profiles |
| `profile_title`        | `title`      | profiles |
| `order_index`          | `position`   | links    |
| `click_count`          | `clicks`     | links    |
| `analytics_id`         | `id`         | clicks   |
| `clicked_at`           | `clicked_at` | clicks   |

---

## 🎯 New Stored Procedures

```sql
-- Get user's profiles with stats
CALL sp_get_user_profiles(1); -- Replace 1 with user_id

-- Get full profile data (info + links + categories)
CALL sp_get_profile_full('fahmi'); -- Replace with actual slug

-- Increment click count and log analytics
CALL sp_increment_click(link_id, ip, country, city, user_agent, referrer);
```

---

## 📈 New Views

```sql
-- Profile stats (link count, total clicks)
SELECT * FROM v_profile_stats WHERE user_id = 1;

-- Public profile data (with theme)
SELECT * FROM v_public_profiles WHERE slug = 'fahmi';
```

---

## 🧹 Cleanup After Verification (1-2 weeks later)

```sql
-- Drop backup tables (ONLY after 100% sure!)
DROP TABLE IF EXISTS backup_users, backup_profiles, backup_links,
                     backup_user_appearance, backup_sessions,
                     backup_password_resets, backup_email_verifications,
                     backup_link_analytics, backup_profile_analytics;

-- Drop old unused tables
DROP TABLE IF EXISTS gradient_presets, social_icons;
```

---

## 🐛 Common Errors & Fixes

### "Table 'users' doesn't exist"

**Fix:** Run migration script

### "Cannot add foreign key constraint"

**Cause:** Already fixed in migration script (orphan check)

### "Unknown column 'user_id' in 'field list'"

**Fix:** Update PHP code to use `id` instead of `user_id` (later)

### "Call to undefined procedure sp_get_user_profiles"

**Fix:** Re-run migration script (procedures weren't created)

---

## 📞 Quick Health Check

```bash
# Check MySQL is running
docker ps | grep linkmy-mysql

# Check for errors
docker logs linkmy-mysql --tail 50

# Check database size
docker exec linkmy-mysql mysql -u linkmy_user -p'admin123' -e "
SELECT
  table_name AS 'Table',
  ROUND((data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
FROM information_schema.tables
WHERE table_schema = 'linkmy_db'
ORDER BY (data_length + index_length) DESC;
"
```

---

## 🎉 Success Checklist

-   [ ] Backup created before migration
-   [ ] Migration script ran without errors
-   [ ] Record counts match (13 users, 9 profiles, 30 links)
-   [ ] Admin can login
-   [ ] Profile pages load correctly
-   [ ] `backup_*` tables exist in database
-   [ ] No SQL errors in `docker logs`

---

**Quick Link:** Full guide → `DATABASE_RECONSTRUCTION_GUIDE.md`
