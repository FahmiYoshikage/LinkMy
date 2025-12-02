# ✅ Database v3 Migration Checklist

**Print this and check off as you go!**

---

## 📅 PRE-MIGRATION (Do This First)

### Documentation Review

-   [ ] Read `START_HERE.md` (this tells you where to start)
-   [ ] Read `ULTRA_QUICK_REFERENCE.md` (2 min - quick commands)
-   [ ] Read `DATABASE_V3_SUMMARY.md` (5 min - overview)
-   [ ] Skim `TROUBLESHOOTING_FLOWCHART.md` (know where to look if errors)

### Environment Check

-   [ ] SSH access to Ubuntu server working
-   [ ] Docker installed and running
-   [ ] MySQL container status: `docker ps | grep linkmy-mysql` shows "Up"
-   [ ] PHP container status: `docker ps | grep linkmy-web` shows "Up"
-   [ ] Current database accessible via phpMyAdmin

### Backup Preparation

-   [ ] Created backup directory: `mkdir -p /opt/LinkMy/backups`
-   [ ] Database backup created: `mysqldump ... > backup_YYYYMMDD.sql`
-   [ ] Backup file size reasonable (should be ~50-100KB)
-   [ ] Test backup can be read: `head -20 backup_YYYYMMDD.sql`

### File Upload

-   [ ] Upload `database_reconstruction_v3.sql` to `/opt/LinkMy/`
-   [ ] Upload `database_rollback.sql` to `/opt/LinkMy/`
-   [ ] Verify files uploaded: `ls -lh /opt/LinkMy/*.sql`
-   [ ] File permissions OK: `chmod 644 *.sql`

---

## 🚀 MIGRATION DAY (The Big Moment!)

### Final Checks

-   [ ] Noted current time: ****\_\_****
-   [ ] Docker containers healthy: `docker ps` shows all "healthy"
-   [ ] MySQL logs clean: `docker logs linkmy-mysql --tail 20`
-   [ ] Current record counts noted:
    -   Users: **\_\_\_** (should be 13)
    -   Profiles: **\_\_\_** (should be 9)
    -   Links: **\_\_\_** (should be 30)

### Run Migration

-   [ ] Navigate to project: `cd /opt/LinkMy`
-   [ ] Run migration command:
    ```bash
    docker exec -i linkmy-mysql mysql -u linkmy_user -p'admin123' linkmy_db < database_reconstruction_v3.sql
    ```
-   [ ] Script completed without errors? (YES/NO) ****\_\_****
-   [ ] Time taken: ****\_\_**** (should be 2-5 seconds)

### Immediate Verification

-   [ ] Check MySQL logs: `docker logs linkmy-mysql --tail 50`
    -   [ ] No error messages
    -   [ ] No warnings about foreign keys
-   [ ] Verify tables exist: `SHOW TABLES;`
    -   [ ] New tables: users, profiles, links, themes, clicks, etc.
    -   [ ] Backup tables: backup_users, backup_profiles, etc.
-   [ ] Check record counts:
    ```bash
    docker exec linkmy-mysql mysql -u linkmy_user -p'admin123' -e "
    USE linkmy_db;
    SELECT 'users' as t, COUNT(*) as n FROM users
    UNION SELECT 'profiles', COUNT(*) FROM profiles
    UNION SELECT 'links', COUNT(*) FROM links
    UNION SELECT 'clicks', COUNT(*) FROM clicks;
    "
    ```
    -   [ ] Users: **\_\_\_** (expected: 13) ✅
    -   [ ] Profiles: **\_\_\_** (expected: 9) ✅
    -   [ ] Links: **\_\_\_** (expected: 30) ✅
    -   [ ] Clicks: **\_\_\_** (expected: 35+) ✅

---

## 🧪 POST-MIGRATION TESTING (Test Everything!)

### Database Structure Tests

-   [ ] Views exist: `SELECT * FROM v_profile_stats LIMIT 1;` returns data
-   [ ] Views work: `SELECT * FROM v_public_profiles LIMIT 1;` returns data
-   [ ] Procedures exist: `SHOW PROCEDURE STATUS WHERE Db='linkmy_db';` shows 3
-   [ ] Procedure test: `CALL sp_get_user_profiles(1);` returns profiles

### Application Tests (Critical Path)

-   [ ] Homepage loads: `http://your-server/` → Works?
-   [ ] Login page loads: `http://your-server/login.php` → Works?
-   [ ] Admin login: username=admin → Success? (YES/NO) ****\_\_****
-   [ ] Dashboard loads: `http://your-server/admin/dashboard.php` → Works?

### Profile Tests (Public Pages)

-   [ ] Profile page loads: `http://your-server/fahmi` → Works?
-   [ ] Profile data displayed:
    -   [ ] Avatar shows
    -   [ ] Name/title shows
    -   [ ] Bio shows (if exists)
    -   [ ] Links displayed
-   [ ] Link click works: Click a link → Redirects? (YES/NO) ****\_\_****
-   [ ] Click counter increments? (check database or dashboard)

### Data Integrity Tests

-   [ ] Check foreign keys:
    ```sql
    SELECT * FROM links WHERE profile_id NOT IN (SELECT id FROM profiles);
    -- Should return 0 rows
    ```
-   [ ] Check user-profile relationship:
    ```sql
    SELECT u.id, u.username, COUNT(p.id) as profiles
    FROM users u
    LEFT JOIN profiles p ON u.id = p.user_id
    GROUP BY u.id;
    -- All users should have ≥1 profile
    ```
-   [ ] Check themes exist:
    ```sql
    SELECT COUNT(*) FROM themes;
    -- Should match profile count (or close to it)
    ```

---

## 🐛 IF ERRORS OCCURRED (Don't Panic!)

### Error Triage

-   [ ] Note the exact error message: **************\_\_\_\_**************
-   [ ] Check which step failed: **************\_\_\_\_**************
-   [ ] Check MySQL logs: `docker logs linkmy-mysql --tail 50`
-   [ ] Open `TROUBLESHOOTING_FLOWCHART.md` and find your error

### Rollback Decision

Do I need to rollback?

-   [ ] Critical errors (data loss, login broken, all pages error) → YES, ROLLBACK NOW
-   [ ] Minor PHP errors (some table names wrong) → NO, fix PHP code
-   [ ] Click tracking not working → NO, check procedures

### Execute Rollback (if needed)

-   [ ] Run rollback command:
    ```bash
    docker exec -i linkmy-mysql mysql -u linkmy_user -p'admin123' linkmy_db < database_rollback.sql
    ```
-   [ ] Verify old tables restored: `SHOW TABLES LIKE 'old_%';`
-   [ ] Test login works: `http://your-server/login.php`
-   [ ] Test profile loads: `http://your-server/fahmi`
-   [ ] Debug the issue before trying migration again

---

## 📊 MONITORING PERIOD (First 24-48 Hours)

### Day 1 Checks

-   [ ] Morning: Check logs `docker logs linkmy-mysql --tail 20`
-   [ ] Noon: Test login and profile pages
-   [ ] Evening: Check logs again
-   [ ] No critical errors reported by users? (YES/NO) ****\_\_****

### Day 2 Checks

-   [ ] Morning: Check logs
-   [ ] Verify click tracking working
-   [ ] Check analytics data accumulating
-   [ ] All features working? (YES/NO) ****\_\_****

### Issues Log

| Time | Issue | Severity | Fixed? |
| ---- | ----- | -------- | ------ |
|      |       |          |        |
|      |       |          |        |
|      |       |          |        |

---

## 🔧 CODE UPDATES (Later - As You Said)

**Quote:** "untuk perbaikan kode nanti saja dulu, sekarang fokus menyelesaikan urusan database"

### When Ready (1-2 weeks after migration):

-   [ ] Database stable for 1-2 weeks
-   [ ] Ready to update PHP code
-   [ ] Read `PHP_CODE_MIGRATION_GUIDE.md`
-   [ ] Backup code: `tar -czf code_backup.tar.gz *.php admin/*.php`
-   [ ] Update files one by one:
    -   [ ] admin/dashboard.php
    -   [ ] admin/profiles.php
    -   [ ] admin/settings.php
    -   [ ] login.php
    -   [ ] register.php
    -   [ ] profile.php
    -   [ ] redirect.php
-   [ ] Test after each file update
-   [ ] All code updated and tested

---

## 🧹 CLEANUP (After 2-4 Weeks)

**Only do this when 100% confident everything works!**

### Backup Tables Removal

-   [ ] Database stable for 2+ weeks
-   [ ] All features working perfectly
-   [ ] No rollback needed
-   [ ] Ready to drop backup tables

### Execute Cleanup

-   [ ] Double-check everything works
-   [ ] Drop backup tables:
    ```sql
    DROP TABLE IF EXISTS backup_users, backup_profiles, backup_links,
                         backup_user_appearance, backup_sessions,
                         backup_password_resets, backup_email_verifications,
                         backup_link_analytics, backup_profile_analytics;
    ```
-   [ ] Verify tables dropped: `SHOW TABLES LIKE 'backup_%';` (should be empty)
-   [ ] Check database size reduced: `SELECT table_schema, SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.tables GROUP BY table_schema;`

### Old Files Cleanup

-   [ ] Remove old SQL dumps (keep latest 2-3)
-   [ ] Archive documentation to backup folder
-   [ ] Update production documentation

---

## 📈 SUCCESS METRICS (Final Validation)

### Technical Metrics

-   [ ] Database structure matches design (10 clean tables)
-   [ ] All record counts preserved (13, 9, 30, 35+)
-   [ ] Query performance improved (stored procedures faster)
-   [ ] No orphaned records (FK constraints working)
-   [ ] Indexes optimized (EXPLAIN shows "Using index")

### User Experience Metrics

-   [ ] Login works reliably
-   [ ] Profile pages load fast
-   [ ] Link clicks track accurately
-   [ ] Admin dashboard shows correct stats
-   [ ] No user complaints
-   [ ] No downtime

### Code Quality Metrics (after code updates)

-   [ ] No more old\_\* table references
-   [ ] Consistent column naming (id everywhere)
-   [ ] Using stored procedures where appropriate
-   [ ] Using views for complex queries
-   [ ] Code cleaner and more maintainable

---

## 🎉 CELEBRATION CHECKLIST

Once everything is working:

-   [ ] Mark project as COMPLETE
-   [ ] Document lessons learned
-   [ ] Update team/personal notes
-   [ ] Archive this checklist for future reference
-   [ ] Treat yourself! You restructured a database with 0 data loss! 🎊

---

## 📝 NOTES & OBSERVATIONS

Use this space for notes during migration:

```
_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

_____________________________________________________________________________

```

---

## 📞 EMERGENCY CONTACTS & COMMANDS

**MySQL Container:**

```bash
docker exec -it linkmy-mysql bash
mysql -u linkmy_user -p'admin123' linkmy_db
```

**Check Logs:**

```bash
docker logs linkmy-mysql --tail 50
docker logs linkmy-web --tail 50
```

**Quick Rollback:**

```bash
docker exec -i linkmy-mysql mysql -u linkmy_user -p'admin123' linkmy_db < database_rollback.sql
```

**Record Counts:**

```bash
docker exec linkmy-mysql mysql -u linkmy_user -p'admin123' -e "
USE linkmy_db;
SELECT 'users' as t, COUNT(*) as n FROM users
UNION SELECT 'profiles', COUNT(*) FROM profiles
UNION SELECT 'links', COUNT(*) FROM links;
"
```

---

**Migration Date:** ********\_\_********  
**Migrated By:** ********\_\_********  
**Server:** ********\_\_********  
**Status:** [ ] Success [ ] Rolled Back [ ] In Progress

---

**Print this checklist and keep it handy during migration!** 📋✅
