# 🔧 Database v3 Troubleshooting Flowchart

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                     MIGRATION TROUBLESHOOTING GUIDE                         │
└─────────────────────────────────────────────────────────────────────────────┘


START HERE: Did migration script complete?
│
├─ NO (Got errors) ─────────────────────────────────┐
│                                                    │
│  What's the error?                                │
│                                                    │
│  ├─ "Table already exists"                        │
│  │   └─> Solution:                                │
│  │       docker exec linkmy-mysql mysql ... -e "  │
│  │       SET FOREIGN_KEY_CHECKS=0;                │
│  │       DROP TABLE users, profiles, links, ...;  │
│  │       SET FOREIGN_KEY_CHECKS=1;"               │
│  │       Then re-run migration script             │
│  │                                                 │
│  ├─ "Cannot add foreign key constraint"           │
│  │   └─> Solution:                                │
│  │       Already handled in script with           │
│  │       WHERE profile_id IS NOT NULL checks      │
│  │       This shouldn't happen - check logs!      │
│  │                                                 │
│  ├─ "Unknown column"                              │
│  │   └─> Solution:                                │
│  │       Script may have partially run            │
│  │       Run rollback first:                      │
│  │       docker exec -i linkmy-mysql mysql ... <  │
│  │         database_rollback.sql                  │
│  │       Then try migration again                 │
│  │                                                 │
│  └─ "Access denied" or "Permission denied"        │
│      └─> Solution:                                │
│          Check MySQL credentials:                 │
│          User: linkmy_user                        │
│          Pass: admin123                           │
│          Or check docker-compose.yml              │
│                                                    │
└────────────────────────────────────────────────────┘


START HERE: Migration completed but got errors AFTER?
│
├─ Login doesn't work ──────────────────────────────┐
│                                                    │
│  Error: "Table old_users doesn't exist"           │
│  └─> Cause: PHP code still using old table names  │
│  └─> Fix NOW:                                     │
│      Option 1: Use rollback (go back to old)      │
│      Option 2: Update PHP files (see guide below) │
│                                                    │
│  Error: "Unknown column 'user_id'"                │
│  └─> Cause: PHP queries old column name           │
│  └─> Fix: Change user_id to id in WHERE clauses   │
│      Example:                                      │
│      OLD: WHERE user_id = ?                       │
│      NEW: WHERE id = ?                            │
│                                                    │
└────────────────────────────────────────────────────┘


START HERE: Profile pages don't load?
│
├─ Error: "Call to undefined procedure"            │
│   └─> Cause: Stored procedures not created        │
│   └─> Fix: Re-run migration script                │
│       (procedures are at end of script)           │
│                                                    │
├─ Error: "Unknown database 'linkmy_db'"            │
│   └─> Cause: Wrong database name                  │
│   └─> Fix: Check docker-compose.yml and db.php    │
│                                                    │
├─ Blank page (no error)                            │
│   └─> Cause: PHP fatal error                      │
│   └─> Check: docker logs linkmy-web --tail 50     │
│   └─> Likely: Old table names in profile.php      │
│                                                    │
└────────────────────────────────────────────────────┘


START HERE: Click tracking doesn't work?
│
├─ Clicks not incrementing                          │
│   └─> Check: Is sp_increment_click() procedure    │
│       created?                                     │
│       Test: CALL sp_increment_click(1, '1.1.1.1', │
│             NULL, NULL, 'test', NULL);            │
│   └─> Check: Does links table have 'clicks'       │
│       column?                                      │
│       Test: SELECT id, title, clicks FROM links    │
│             LIMIT 5;                               │
│                                                    │
├─ Analytics not saving                             │
│   └─> Check: Does 'clicks' table exist?           │
│       Test: SHOW TABLES LIKE 'clicks';            │
│   └─> Check: Foreign key link_id valid?           │
│       Test: SELECT * FROM clicks WHERE link_id    │
│             NOT IN (SELECT id FROM links);        │
│                                                    │
└────────────────────────────────────────────────────┘


VERIFICATION CHECKLIST (Run after migration):
│
├─ [ ] Check tables created                         │
│     docker exec linkmy-mysql mysql ... -e "        │
│       USE linkmy_db; SHOW TABLES;"                │
│     Expected: users, profiles, links, categories,  │
│               themes, theme_boxed, clicks,         │
│               sessions, password_resets,           │
│               email_verifications,                 │
│               backup_users, backup_profiles, ...   │
│                                                    │
├─ [ ] Check record counts                          │
│     docker exec linkmy-mysql mysql ... -e "        │
│       USE linkmy_db;                               │
│       SELECT 'users' as t, COUNT(*) as n           │
│       FROM users                                   │
│       UNION SELECT 'profiles', COUNT(*)            │
│       FROM profiles                                │
│       UNION SELECT 'links', COUNT(*) FROM links;"  │
│     Expected: users=13, profiles=9, links=30       │
│                                                    │
├─ [ ] Check views exist                            │
│     docker exec linkmy-mysql mysql ... -e "        │
│       USE linkmy_db;                               │
│       SELECT * FROM v_profile_stats LIMIT 1;"      │
│     Should return data (not error)                 │
│                                                    │
├─ [ ] Check procedures exist                       │
│     docker exec linkmy-mysql mysql ... -e "        │
│       USE linkmy_db;                               │
│       SHOW PROCEDURE STATUS WHERE Db='linkmy_db';" │
│     Should show 3 procedures:                      │
│     - sp_get_user_profiles                         │
│     - sp_get_profile_full                          │
│     - sp_increment_click                           │
│                                                    │
├─ [ ] Check foreign keys                           │
│     docker exec linkmy-mysql mysql ... -e "        │
│       USE linkmy_db;                               │
│       SELECT                                       │
│         TABLE_NAME,                                │
│         CONSTRAINT_NAME,                           │
│         REFERENCED_TABLE_NAME                      │
│       FROM information_schema.KEY_COLUMN_USAGE     │
│       WHERE TABLE_SCHEMA = 'linkmy_db'             │
│         AND REFERENCED_TABLE_NAME IS NOT NULL;"    │
│     Should show multiple foreign keys              │
│                                                    │
└────────────────────────────────────────────────────┘


QUICK FIX COMMANDS:
│
├─ Reset everything (nuclear option):               │
│   docker exec -i linkmy-mysql mysql \              │
│     -u linkmy_user -p'admin123' linkmy_db \        │
│     < database_rollback.sql                        │
│                                                    │
├─ Re-run migration (if partially failed):          │
│   # First, drop new tables                         │
│   docker exec linkmy-mysql mysql \                 │
│     -u linkmy_user -p'admin123' -e "               │
│     USE linkmy_db;                                 │
│     SET FOREIGN_KEY_CHECKS=0;                      │
│     DROP TABLE IF EXISTS theme_boxed, themes,      │
│       clicks, categories, links, profiles,         │
│       sessions, password_resets,                   │
│       email_verifications, users;                  │
│     SET FOREIGN_KEY_CHECKS=1;"                     │
│   # Then re-run migration                          │
│   docker exec -i linkmy-mysql mysql \              │
│     -u linkmy_user -p'admin123' linkmy_db \        │
│     < database_reconstruction_v3.sql               │
│                                                    │
├─ Check MySQL is running:                          │
│   docker ps | grep linkmy-mysql                    │
│   (should show "Up" status)                        │
│                                                    │
├─ Restart MySQL container:                         │
│   docker restart linkmy-mysql                      │
│   docker logs linkmy-mysql --tail 50               │
│                                                    │
└────────────────────────────────────────────────────┘


EMERGENCY ROLLBACK DECISION TREE:
│
Should I rollback?
│
├─ Migration completed but:                         │
│   ├─ Website completely broken? → YES, ROLLBACK   │
│   ├─ Login doesn't work? → YES, ROLLBACK          │
│   ├─ Database errors everywhere? → YES, ROLLBACK  │
│   ├─ Lost data? → YES, ROLLBACK IMMEDIATELY!      │
│   └─ Just some PHP errors? → NO, fix PHP code     │
│                                                    │
├─ Migration failed with errors:                    │
│   ├─ "Table already exists"? → Drop tables, retry │
│   ├─ "Foreign key constraint"? → Check logs       │
│   ├─ "Access denied"? → Check credentials         │
│   └─ Other MySQL errors? → YES, ROLLBACK & debug  │
│                                                    │
└─ Rollback command:                                │
    docker exec -i linkmy-mysql mysql \              │
      -u linkmy_user -p'admin123' linkmy_db \        │
      < database_rollback.sql                        │
                                                     │
    After rollback:                                  │
    ├─ Old tables restored (old_users, etc.)         │
    ├─ Website should work again                     │
    └─ Debug issue before trying migration again     │


PHP CODE ERRORS AFTER MIGRATION:
│
Error: "Table 'linkmy_db.old_users' doesn't exist"
│
├─ Location: login.php, register.php, any auth file │
│                                                    │
├─ Quick Fix (temporary):                           │
│   Find: FROM old_users                             │
│   Replace: FROM users                              │
│                                                    │
│   Find: JOIN old_users u                           │
│   Replace: JOIN users u                            │
│                                                    │
├─ Better Fix (permanent):                          │
│   Read PHP_CODE_MIGRATION_GUIDE.md                │
│   Update all files systematically                 │
│                                                    │
└────────────────────────────────────────────────────┘


Error: "Unknown column 'password_hash' in field list"
│
├─ Location: login.php (password verification)      │
│                                                    │
├─ Quick Fix:                                       │
│   Find: password_hash                              │
│   Replace: password                                │
│                                                    │
│   Example:                                         │
│   OLD: password_verify($pass, $user['password_hash'])│
│   NEW: password_verify($pass, $user['password'])   │
│                                                    │
└────────────────────────────────────────────────────┘


Error: "Unknown column 'profile_id' in field list"
│
├─ Depends on context:                              │
│                                                    │
│   In SELECT with profiles table:                  │
│   OLD: SELECT profile_id FROM old_profiles         │
│   NEW: SELECT id FROM profiles                     │
│                                                    │
│   In WHERE clause with profiles:                  │
│   OLD: WHERE profile_id = ?                        │
│   NEW: WHERE id = ?                                │
│                                                    │
│   In JOIN with links table:                       │
│   OLD: l.profile_id                                │
│   NEW: l.profile_id (stays same! It's FK)          │
│                                                    │
└────────────────────────────────────────────────────┘


PERFORMANCE ISSUES:
│
Queries running slow after migration?
│
├─ Check indexes were created:                      │
│   docker exec linkmy-mysql mysql ... -e "          │
│     USE linkmy_db;                                 │
│     SHOW INDEX FROM profiles;                      │
│     SHOW INDEX FROM links;"                        │
│   Should show multiple indexes                     │
│                                                    │
├─ Optimize tables manually:                        │
│   docker exec linkmy-mysql mysql ... -e "          │
│     USE linkmy_db;                                 │
│     OPTIMIZE TABLE users, profiles, links,         │
│       categories, themes, clicks;"                 │
│                                                    │
├─ Check query execution plan:                      │
│   EXPLAIN SELECT * FROM v_profile_stats            │
│     WHERE user_id = 1;                             │
│   Look for "Using index" in output                 │
│                                                    │
└────────────────────────────────────────────────────┘


DATA INTEGRITY CHECKS:
│
├─ Check for orphaned records:                      │
│   # Links without profiles                         │
│   SELECT * FROM links WHERE profile_id             │
│     NOT IN (SELECT id FROM profiles);              │
│   (should be 0 rows)                               │
│                                                    │
│   # Profiles without users                         │
│   SELECT * FROM profiles WHERE user_id             │
│     NOT IN (SELECT id FROM users);                 │
│   (should be 0 rows)                               │
│                                                    │
│   # Clicks without links                           │
│   SELECT * FROM clicks WHERE link_id               │
│     NOT IN (SELECT id FROM links);                 │
│   (should be 0 rows)                               │
│                                                    │
├─ Check user data integrity:                       │
│   # Users with profiles                            │
│   SELECT u.id, u.username,                         │
│     COUNT(p.id) as profile_count                   │
│   FROM users u                                     │
│   LEFT JOIN profiles p ON u.id = p.user_id         │
│   GROUP BY u.id;                                   │
│   (all users should have ≥1 profile)               │
│                                                    │
├─ Compare old vs new record counts:                │
│   SELECT                                           │
│     (SELECT COUNT(*) FROM backup_users) as old,    │
│     (SELECT COUNT(*) FROM users) as new;           │
│   (should match: 13 = 13)                          │
│                                                    │
└────────────────────────────────────────────────────┘


DOCKER CONTAINER ISSUES:
│
├─ Container won't start:                           │
│   docker logs linkmy-mysql --tail 50               │
│   Look for authentication or port binding errors   │
│                                                    │
├─ Can't connect to MySQL:                          │
│   docker exec -it linkmy-mysql bash                │
│   mysql -u linkmy_user -p'admin123'                │
│   (should connect without errors)                  │
│                                                    │
├─ File upload issues:                              │
│   docker cp database_reconstruction_v3.sql \       │
│     linkmy-mysql:/tmp/                             │
│   docker exec -i linkmy-mysql mysql \              │
│     -u linkmy_user -p'admin123' linkmy_db \        │
│     < /tmp/database_reconstruction_v3.sql          │
│                                                    │
└────────────────────────────────────────────────────┘


FINAL DECISION MATRIX:
│
┌────────────────────┬──────────────┬──────────────────┐
│ Symptom            │ Severity     │ Action           │
├────────────────────┼──────────────┼──────────────────┤
│ Data loss          │ 🔴 CRITICAL  │ ROLLBACK NOW     │
│ Login broken       │ 🔴 CRITICAL  │ ROLLBACK NOW     │
│ All pages error    │ 🔴 CRITICAL  │ ROLLBACK NOW     │
│ MySQL won't start  │ 🔴 CRITICAL  │ ROLLBACK NOW     │
├────────────────────┼──────────────┼──────────────────┤
│ Some PHP errors    │ 🟡 MEDIUM    │ Fix PHP code     │
│ Click tracking off │ 🟡 MEDIUM    │ Check procedures │
│ Slow queries       │ 🟡 MEDIUM    │ OPTIMIZE tables  │
├────────────────────┼──────────────┼──────────────────┤
│ Minor styling      │ 🟢 LOW       │ Fix later        │
│ Console warnings   │ 🟢 LOW       │ Fix later        │
└────────────────────┴──────────────┴──────────────────┘


┌─────────────────────────────────────────────────────────────────────────────┐
│                          NEED MORE HELP?                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│ 📖 Read full guides:                                                        │
│    - DATABASE_RECONSTRUCTION_GUIDE.md (complete walkthrough)                │
│    - QUICK_DATABASE_REFERENCE.md (cheat sheet)                              │
│    - PHP_CODE_MIGRATION_GUIDE.md (code updates)                             │
│                                                                             │
│ 🔍 Check logs:                                                              │
│    - docker logs linkmy-mysql --tail 100                                    │
│    - docker logs linkmy-web --tail 100                                      │
│                                                                             │
│ 💾 Always have rollback ready:                                             │
│    - database_rollback.sql in /opt/LinkMy/                                  │
│    - Backup before migration: mysqldump > backup.sql                        │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘

```
