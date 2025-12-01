@echo off
REM ===================================================
REM LinkMy Docker Stack Upgrade Script
REM Upgrades to: MySQL 8.4 LTS, PHP 8.3, phpMyAdmin 5.2
REM ===================================================

echo ========================================
echo    LinkMy Docker Stack Upgrade
echo ========================================
echo.

REM Check if Docker is running
docker version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Docker Desktop is not running!
    echo.
    echo Please start Docker Desktop first:
    echo 1. Open Docker Desktop
    echo 2. Wait for it to start
    echo 3. Run this script again
    echo.
    pause
    exit /b 1
)

echo [OK] Docker is running
echo.

REM Ask for confirmation
echo This will:
echo - Stop current containers
echo - Pull MySQL 8.4 LTS (~500MB)
echo - Pull PHP 8.3 (~450MB)  
echo - Pull phpMyAdmin 5.2 (~150MB)
echo - Rebuild web container
echo - Start new stack
echo.
set /p confirm="Continue? (Y/N): "
if /i not "%confirm%"=="Y" (
    echo Cancelled.
    pause
    exit /b 0
)

echo.
echo [STEP 1/6] Creating backup...
echo ========================================
if exist backup_before_upgrade.sql (
    echo Backup already exists, skipping...
) else (
    docker exec linkmy_mysql mysqldump -u root -prootpassword linkmy_db > backup_before_upgrade.sql 2>nul
    if errorlevel 1 (
        echo [WARNING] Could not create backup - MySQL might not be running
        echo Continuing anyway...
    ) else (
        echo [OK] Database backed up to backup_before_upgrade.sql
    )
)
echo.

echo [STEP 2/6] Stopping current containers...
echo ========================================
docker-compose down
echo [OK] Containers stopped
echo.

echo [STEP 3/6] Pulling new images...
echo ========================================
echo This may take 5-10 minutes depending on your internet speed...
docker-compose pull
if errorlevel 1 (
    echo [ERROR] Failed to pull images!
    pause
    exit /b 1
)
echo [OK] Images pulled successfully
echo.

echo [STEP 4/6] Rebuilding web container...
echo ========================================
docker-compose build --no-cache web
if errorlevel 1 (
    echo [ERROR] Failed to build web container!
    pause
    exit /b 1
)
echo [OK] Web container rebuilt
echo.

echo [STEP 5/6] Starting new stack...
echo ========================================
docker-compose up -d
if errorlevel 1 (
    echo [ERROR] Failed to start containers!
    pause
    exit /b 1
)
echo [OK] Containers started
echo.

echo [STEP 6/6] Waiting for MySQL to be healthy...
echo ========================================
timeout /t 10 /nobreak >nul
docker-compose ps
echo.

echo ========================================
echo    Upgrade Complete!
echo ========================================
echo.
echo New versions running:
docker exec linkmy_mysql mysql -u root -prootpassword -e "SELECT VERSION();" 2>nul
docker exec linkmy_web php -v | findstr "PHP"
echo.
echo Services:
echo - Website:     http://localhost:83
echo - phpMyAdmin:  http://localhost:8083
echo - Migration:   http://localhost:83/migrate_to_v2.php
echo.
echo Next steps:
echo 1. Open http://localhost:83 to verify website works
echo 2. Check http://localhost:83/admin/profiles.php for stats
echo 3. Run migration: http://localhost:83/migrate_to_v2.php
echo.
echo Rollback (if needed):
echo   docker-compose down
echo   git checkout HEAD -- docker-compose.yml Dockerfile
echo   docker-compose up -d
echo.

pause
