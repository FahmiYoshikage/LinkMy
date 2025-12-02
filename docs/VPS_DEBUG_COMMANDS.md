# 🔍 VPS Debugging Command Reference

## Quick Commands to Run on VPS

### 1. View Real-Time Logs

```bash
# Follow Apache error log
docker logs -f linkmy_web

# Last 100 lines
docker logs linkmy_web --tail 100

# View PHP errors inside container
docker exec linkmy_web tail -f /var/log/apache2/error.log
```

### 2. Check PHP Configuration

```bash
# Check PHP version and modules
docker exec linkmy_web php -v
docker exec linkmy_web php -m | grep mysqli

# Check if file exists
docker exec linkmy_web ls -la /var/www/html/profile.php
docker exec linkmy_web ls -la /var/www/html/config/db.php
```

### 3. Test Database Connection

```bash
# Test MySQL connection from web container
docker exec linkmy_web php -r "
require '/var/www/html/config/db.php';
echo 'Database: ' . DB_NAME . PHP_EOL;
echo 'Connection: ' . (mysqli_ping(\$conn) ? 'OK' : 'FAILED') . PHP_EOL;
"
```

### 4. Check File Permissions

```bash
docker exec linkmy_web ls -la /var/www/html/*.php
docker exec linkmy_web stat /var/www/html/profile.php
```

### 5. Test Profile Page Directly

```bash
# Run PHP directly to see errors
docker exec linkmy_web php /var/www/html/debug_profile.php

# Or via curl
curl -v https://linkmy.iet.ovh/debug_profile.php?slug=fahmi
curl -v https://linkmy.iet.ovh/view_errors.php
```

### 6. Check Apache Configuration

```bash
# View Apache error log
docker exec linkmy_web cat /var/log/apache2/error.log | tail -50

# Check if rewrite module enabled
docker exec linkmy_web apache2ctl -M | grep rewrite

# Test Apache config
docker exec linkmy_web apache2ctl configtest
```

### 7. Restart Services

```bash
# Restart container
docker compose restart linkmy_web

# Full rebuild
docker compose up -d --build

# Stop and remove container
docker compose down linkmy_web
docker compose up -d linkmy_web
```

### 8. Check Container Status

```bash
# List all containers
docker ps

# Check container health
docker inspect linkmy_web | grep -A 10 State

# Check resource usage
docker stats linkmy_web --no-stream
```

## Web-Based Debugging Tools

After `git pull`, access these URLs:

1. **Debug Profile Page:**

    ```
    https://linkmy.iet.ovh/debug_profile.php?slug=fahmi
    ```

    Shows step-by-step execution and catches PHP errors

2. **View Error Logs:**

    ```
    https://linkmy.iet.ovh/view_errors.php
    ```

    Displays PHP error log locations and recent errors

3. **Diagnostic Tool:**
    ```
    https://linkmy.iet.ovh/diagnostic_boxed_layout.php?slug=fahmi
    ```
    Checks database structure and data integrity

## Common Issues and Solutions

### Issue: 500 Internal Server Error

**Check:**

```bash
# View last 20 errors
docker logs linkmy_web --tail 20

# Look for PHP fatal errors
docker exec linkmy_web grep "PHP Fatal" /var/log/apache2/error.log
```

**Common Causes:**

-   PHP syntax error
-   Missing function or file
-   Database connection failed
-   File permissions issue

### Issue: Blank White Page

**Check:**

```bash
# Enable error display temporarily
docker exec linkmy_web php -r "
ini_set('display_errors', 1);
error_reporting(E_ALL);
require '/var/www/html/profile.php';
"
```

### Issue: Database Connection Failed

**Check:**

```bash
# Test MySQL container
docker exec linkmy_mysql mysql -u root -p -e "SHOW DATABASES;"

# Check network between containers
docker network inspect linkmy_default

# Verify environment variables
docker exec linkmy_web env | grep DB_
```

### Issue: File Not Found (404)

**Check:**

```bash
# Verify file exists
docker exec linkmy_web ls -la /var/www/html/profile.php

# Check .htaccess rules
docker exec linkmy_web cat /var/www/html/.htaccess

# Test rewrite module
docker exec linkmy_web apache2ctl -M | grep rewrite
```

## Quick Fix Workflow

1. **Pull latest code:**

    ```bash
    cd /opt/LinkMy
    git pull origin master
    ```

2. **Check what changed:**

    ```bash
    git log --oneline -5
    git diff HEAD~1 profile.php
    ```

3. **Rebuild container:**

    ```bash
    docker compose up -d --build
    ```

4. **Monitor logs in real-time:**

    ```bash
    docker logs -f linkmy_web
    ```

5. **Test in browser:**

    ```
    https://linkmy.iet.ovh/debug_profile.php?slug=fahmi
    ```

6. **If still broken, check detailed errors:**

    ```
    https://linkmy.iet.ovh/view_errors.php
    ```

7. **Get container shell for deep debugging:**
    ```bash
    docker exec -it linkmy_web bash
    cd /var/www/html
    php debug_profile.php
    ```

## Emergency Rollback

If new code breaks production:

```bash
cd /opt/LinkMy

# See recent commits
git log --oneline -10

# Rollback to previous working commit
git revert HEAD
# or
git reset --hard <previous-commit-hash>

# Force rebuild
docker compose up -d --build --force-recreate
```

## Monitoring Commands

```bash
# Watch logs continuously
watch -n 2 'docker logs linkmy_web --tail 20'

# Check disk space
docker exec linkmy_web df -h

# Check memory usage
docker exec linkmy_web free -m

# List all running processes
docker exec linkmy_web ps aux
```

## Notes

-   Always `git pull` before debugging
-   Always `docker compose restart` after code changes
-   Check both Apache logs AND PHP error logs
-   Use debug_profile.php for step-by-step diagnosis
-   Navbar issue might be CSS caching - try Ctrl+Shift+R in browser
