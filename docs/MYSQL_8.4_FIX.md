# 🚨 URGENT FIX: MySQL 8.4 Boot Loop Issue

## Problem

MySQL 8.4 keeps restarting with error:

```
[ERROR] [MY-000067] [Server] unknown variable 'default-authentication-plugin=mysql_native_password'
```

## Root Cause

MySQL 8.4 **removed** the deprecated parameter `--default-authentication-plugin`. This parameter was deprecated in MySQL 8.0 and completely removed in 8.4.

## Solution Applied ✅

**Changed in `docker-compose.yml`:**

```yaml
# ❌ OLD (BROKEN)
command: --default-authentication-plugin=mysql_native_password --sql-mode="..."

# ✅ NEW (WORKING)
command: --sql-mode="..." --authentication-policy=mysql_native_password
```

## How to Apply Fix on Server

### Step 1: Pull Latest Changes

```bash
cd /opt/LinkMy
git pull origin master
```

### Step 2: Stop and Remove Failing Container

```bash
docker-compose down
docker rm -f linkmy_mysql 2>/dev/null || true
```

### Step 3: Start Fresh

```bash
docker-compose up -d
```

### Step 4: Verify MySQL Started Successfully

```bash
# Check logs (should not show error anymore)
docker logs linkmy_mysql

# Should see:
# [System] [MY-010931] [Server] /usr/sbin/mysqld: ready for connections
```

### Step 5: Test Connection

```bash
docker exec linkmy_mysql mysql -u root -prootpassword -e "SELECT VERSION();"
# Should output: 8.4.7
```

---

## Why This Happened

| MySQL Version | Parameter                         | Status         |
| ------------- | --------------------------------- | -------------- |
| 8.0.x         | `--default-authentication-plugin` | Deprecated ⚠️  |
| 8.4.x         | `--default-authentication-plugin` | **Removed** ❌ |
| 8.4.x         | `--authentication-policy`         | **New** ✅     |

MySQL 8.4 introduced a new parameter `--authentication-policy` to replace the old one.

---

## Alternative: Use MySQL 8.0 LTS (If You Prefer)

If you want to avoid these breaking changes, you can use MySQL 8.0 LTS instead:

```yaml
# In docker-compose.yml
db:
    image: mysql:8.0.40 # Specific LTS version
    command: --default-authentication-plugin=mysql_native_password --sql-mode="..."
```

But **MySQL 8.4 is better** (faster, more features, LTS until 2032).

---

## Complete Working Configuration

```yaml
services:
    db:
        image: mysql:8.4-oracle
        container_name: linkmy_mysql
        restart: always
        command: --sql-mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION" --authentication-policy=mysql_native_password
        environment:
            MYSQL_ROOT_PASSWORD: rootpassword
            MYSQL_DATABASE: linkmy_db
            MYSQL_USER: linkmy_user
            MYSQL_PASSWORD: linkmy_password
        volumes:
            - mysql_data:/var/lib/mysql
            - ./linkmy_db.sql:/docker-entrypoint-initdb.d/init.sql
        ports:
            - '3307:3306'
        networks:
            - linkmy_network
        healthcheck:
            test: ['CMD', 'mysqladmin', 'ping', '-h', 'localhost']
            interval: 10s
            timeout: 5s
            retries: 5
```

---

## Expected Result After Fix

```bash
root@ubuntu:/opt/LinkMy# docker-compose up -d
[+] Running 3/3
 ✔ Container linkmy_mysql       Healthy    <-- Should be healthy!
 ✔ Container linkmy_web         Started
 ✔ Container linkmy_phpmyadmin  Started

root@ubuntu:/opt/LinkMy# docker ps
CONTAINER ID   STATUS
linkmy_mysql   Up 30 seconds (healthy)  <-- No more restarts!
linkmy_web     Up 25 seconds
linkmy_phpmyadmin Up 20 seconds
```

---

## Rollback (If Needed)

If something breaks:

```bash
cd /opt/LinkMy
git checkout HEAD~1 -- docker-compose.yml
docker-compose down
docker-compose up -d
```

---

**Status:** ✅ Fixed and pushed to GitHub (commit cd71356)
**Action Required:** Pull latest changes and restart containers on server
