@echo off
REM LinkMy Docker Setup Script for Windows
REM This script helps you set up and run LinkMy in Docker on Windows

echo ==========================================
echo   LinkMy v2.1 - Docker Setup (Windows)
echo ==========================================
echo.

REM Check if Docker is running
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running. Please start Docker Desktop first.
    pause
    exit /b 1
)
echo [OK] Docker is running

REM Check if required files exist
echo.
echo Checking required files...
if not exist "database.sql" (
    echo [ERROR] database.sql not found!
    pause
    exit /b 1
)
echo [OK] database.sql found

if not exist "database_update_v2.1.sql" (
    echo [WARNING] database_update_v2.1.sql not found (optional for v2.1 features)
) else (
    echo [OK] database_update_v2.1.sql found
)

REM Create necessary directories
echo.
echo Creating directories...
if not exist "uploads\profile_pics" mkdir uploads\profile_pics
if not exist "uploads\backgrounds" mkdir uploads\backgrounds
if not exist "uploads\folder_pics" mkdir uploads\folder_pics
echo [OK] Upload directories created

REM Copy database config for Docker
echo.
echo Setting up database configuration...
if exist "config\db.docker.php" (
    if exist "config\db.php" (
        copy /Y "config\db.php" "config\db.backup.php" >nul 2>&1
    )
    copy /Y "config\db.docker.php" "config\db.php" >nul
    echo [OK] Database configuration updated for Docker
) else (
    echo [WARNING] config\db.docker.php not found, using existing config\db.php
)

REM Ask user for action
echo.
echo What would you like to do?
echo 1) Build and start containers
echo 2) Start existing containers
echo 3) Stop containers
echo 4) Rebuild containers (fresh start)
echo 5) View logs
echo 6) Remove containers and volumes (clean up)
echo.
set /p choice="Enter your choice (1-6): "

if "%choice%"=="1" goto build_start
if "%choice%"=="2" goto start
if "%choice%"=="3" goto stop
if "%choice%"=="4" goto rebuild
if "%choice%"=="5" goto logs
if "%choice%"=="6" goto cleanup
echo [ERROR] Invalid choice
pause
exit /b 1

:build_start
echo.
echo Building and starting containers...
docker-compose up -d --build
echo [OK] Containers are starting!
goto wait_and_show

:start
echo.
echo Starting existing containers...
docker-compose up -d
echo [OK] Containers started!
goto wait_and_show

:stop
echo.
echo Stopping containers...
docker-compose down
echo [OK] Containers stopped!
pause
exit /b 0

:rebuild
echo.
echo Rebuilding containers (this will remove existing containers)...
docker-compose down
docker-compose build --no-cache
docker-compose up -d
echo [OK] Containers rebuilt and started!
goto wait_and_show

:logs
echo.
echo Showing logs (press Ctrl+C to exit)...
docker-compose logs -f
pause
exit /b 0

:cleanup
echo.
set /p confirm="WARNING: This will remove all containers and data! Are you sure? (yes/no): "
if /i "%confirm%"=="yes" (
    docker-compose down -v
    echo [OK] Containers and volumes removed!
) else (
    echo [WARNING] Cleanup cancelled
)
pause
exit /b 0

:wait_and_show
REM Wait for services to be ready
echo.
echo Waiting for services to be ready...
timeout /t 10 /nobreak >nul

REM Check container status
echo.
echo Container status:
docker-compose ps

REM Print access information
echo.
echo ==========================================
echo [SUCCESS] LinkMy is now running!
echo ==========================================
echo.
echo Web Application: http://localhost:83
echo phpMyAdmin:      http://localhost:8083
echo.
echo Database credentials:
echo   Host:     linkmy-db (inside Docker) or localhost:3307 (from host)
echo   Database: linkmy_db
echo   User:     linkmy_user
echo   Password: linkmy_pass
echo.
echo Useful commands:
echo   View logs:        docker-compose logs -f
echo   Stop containers:  docker-compose down
echo   Restart:          docker-compose restart
echo   Shell access:     docker exec -it linkmy_web bash
echo.
echo ==========================================
pause
