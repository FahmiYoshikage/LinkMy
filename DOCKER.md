# 🐳 LinkMy Docker Deployment Guide

This guide explains how to run LinkMy v2.1 using Docker containers.

## 📋 Table of Contents

-   [Prerequisites](#prerequisites)
-   [Quick Start](#quick-start)
-   [Configuration](#configuration)
-   [Services](#services)
-   [Management Commands](#management-commands)
-   [Troubleshooting](#troubleshooting)
-   [Production Deployment](#production-deployment)

---

## 🔧 Prerequisites

### Required Software

1. **Docker Desktop** (Windows/Mac) or **Docker Engine** (Linux)

    - Version 20.10+ recommended
    - Download: https://www.docker.com/products/docker-desktop

2. **Docker Compose**
    - Version 1.29+ or 2.0+
    - Included with Docker Desktop
    - Linux: `sudo apt install docker-compose`

### System Requirements

-   **RAM**: 2GB minimum, 4GB recommended
-   **Disk Space**: 2GB minimum for images and data
-   **Ports**: 83, 3307, 8083 (configurable)

### Verify Installation

```bash
# Check Docker
docker --version
# Output: Docker version 20.10.x

# Check Docker Compose
docker-compose --version
# Output: docker-compose version 1.29.x or 2.x
```

---

## 🚀 Quick Start

### Windows Users

1. **Open PowerShell or Command Prompt** in the project directory

2. **Run the setup script:**

    ```cmd
    docker-setup.bat
    ```

3. **Choose option 1** (Build and start containers)

4. **Access the application:**
    - Web: http://localhost:83
    - phpMyAdmin: http://localhost:8083

### Linux/Mac Users

1. **Open Terminal** in the project directory

2. **Make the script executable:**

    ```bash
    chmod +x docker-setup.sh
    ```

3. **Run the setup script:**

    ```bash
    ./docker-setup.sh
    ```

4. **Choose option 1** (Build and start containers)

5. **Access the application:**
    - Web: http://localhost:83
    - phpMyAdmin: http://localhost:8083

### Manual Start (Any OS)

```bash
# Build and start containers
docker-compose up -d --build

# View logs
docker-compose logs -f

# Stop containers
docker-compose down
```

---

## ⚙️ Configuration

### Port Configuration

Edit `docker-compose.yml` to change ports:

```yaml
services:
    linkmy-web:
        ports:
            - '83:80' # Change 83 to your desired port

    linkmy-db:
        ports:
            - '3307:3306' # Change 3307 to your desired MySQL port

    linkmy-phpmyadmin:
        ports:
            - '8083:80' # Change 8083 to your desired phpMyAdmin port
```

### Database Credentials

Edit `docker-compose.yml` to change database settings:

```yaml
environment:
    MYSQL_ROOT_PASSWORD: linkmy_root_pass # Change this
    MYSQL_DATABASE: linkmy_db
    MYSQL_USER: linkmy_user # Change this
    MYSQL_PASSWORD: linkmy_pass # Change this
```

**Important:** If you change database credentials, also update `config/db.docker.php`

### PHP Configuration

Edit `Dockerfile` to modify PHP settings:

```dockerfile
RUN echo "upload_max_filesize = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 10M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini
```

---

## 🏗️ Services

### 1. Web Application (linkmy-web)

-   **Container**: `linkmy_web`
-   **Image**: Custom PHP 8.2 + Apache
-   **Port**: 83 → 80
-   **Features**:
    -   PHP 8.2 with MySQL extensions
    -   Apache with mod_rewrite
    -   Auto-configured upload directories
    -   Health checks enabled

### 2. Database (linkmy-db)

-   **Container**: `linkmy_mysql`
-   **Image**: MySQL 8.0
-   **Port**: 3307 → 3306
-   **Features**:
    -   Auto-imports schema on first run
    -   Persistent data volume
    -   Health checks enabled

### 3. phpMyAdmin (linkmy-phpmyadmin)

-   **Container**: `linkmy_phpmyadmin`
-   **Image**: phpMyAdmin latest
-   **Port**: 8083 → 80
-   **Features**:
    -   Pre-configured to connect to linkmy-db
    -   Auto-login enabled

---

## 🛠️ Management Commands

### Start/Stop Services

```bash
# Start all services
docker-compose up -d

# Start specific service
docker-compose up -d linkmy-web

# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: deletes data)
docker-compose down -v
```

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f linkmy-web

# Last 100 lines
docker-compose logs --tail=100
```

### Container Management

```bash
# List running containers
docker-compose ps

# Restart services
docker-compose restart

# Restart specific service
docker-compose restart linkmy-web

# View container stats
docker stats linkmy_web linkmy_mysql
```

### Shell Access

```bash
# Access web container
docker exec -it linkmy_web bash

# Access database container
docker exec -it linkmy_mysql bash

# Run MySQL commands directly
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db
```

### Database Operations

```bash
# Backup database
docker exec linkmy_mysql mysqldump -u linkmy_user -plinkmy_pass linkmy_db > backup.sql

# Restore database
docker exec -i linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db < backup.sql

# Access MySQL CLI
docker exec -it linkmy_mysql mysql -u root -plinkmy_root_pass

# View tables
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db -e "SHOW TABLES;"
```

---

## 🐛 Troubleshooting

### Port Already in Use

**Error:**

```
Error starting userland proxy: listen tcp 0.0.0.0:83: bind: address already in use
```

**Solution:**

1. Check what's using the port:

    ```bash
    # Windows
    netstat -ano | findstr :83

    # Linux/Mac
    lsof -i :83
    ```

2. Either stop the conflicting service or change the port in `docker-compose.yml`

### Database Connection Failed

**Error:** "Connection failed: No such host is known"

**Solution:**

1. Ensure containers are on the same network:

    ```bash
    docker network ls
    docker network inspect linkmy_linkmy-network
    ```

2. Check if database is running:

    ```bash
    docker-compose ps linkmy-db
    ```

3. Verify database config in `config/db.php`:
    ```php
    define('DB_HOST', 'linkmy-db');  // NOT 'localhost'
    ```

### Upload Permission Denied

**Error:** "Failed to upload file"

**Solution:**

```bash
# Fix permissions from host
chmod -R 777 uploads/

# Or from inside container
docker exec -it linkmy_web chmod -R 777 /var/www/html/uploads
```

### Container Won't Start

**Check logs:**

```bash
docker-compose logs linkmy-web
```

**Common issues:**

1. Port conflict (see above)
2. Missing `database.sql` file
3. Syntax error in `docker-compose.yml`

**Solution:**

```bash
# Rebuild from scratch
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Database Not Initialized

**Symptom:** Tables don't exist

**Solution:**

```bash
# Remove database volume and restart
docker-compose down -v
docker-compose up -d

# Or manually import
docker exec -i linkmy_mysql mysql -u root -plinkmy_root_pass linkmy_db < database.sql
docker exec -i linkmy_mysql mysql -u root -plinkmy_root_pass linkmy_db < database_update_v2.1.sql
```

---

## 🚀 Production Deployment

### Environment Variables

Create `.env` file:

```env
# Database
DB_ROOT_PASSWORD=your_secure_root_password
DB_NAME=linkmy_db
DB_USER=linkmy_user
DB_PASSWORD=your_secure_password

# Ports
WEB_PORT=83
DB_PORT=3307
PMA_PORT=8083

# PHP
PHP_MEMORY_LIMIT=256M
PHP_UPLOAD_MAX_SIZE=10M
```

Update `docker-compose.yml` to use variables:

```yaml
environment:
    MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    MYSQL_DATABASE: ${DB_NAME}
    MYSQL_USER: ${DB_USER}
    MYSQL_PASSWORD: ${DB_PASSWORD}
```

### Security Hardening

1. **Change default passwords** in `docker-compose.yml`

2. **Disable phpMyAdmin** in production:

    ```yaml
    # Comment out or remove linkmy-phpmyadmin service
    ```

3. **Use secrets** for sensitive data:

    ```yaml
    secrets:
        db_password:
            file: ./secrets/db_password.txt
    ```

4. **Restrict network access**:

    ```yaml
    linkmy-db:
        ports: [] # Don't expose database port
    ```

5. **Enable SSL/TLS** with reverse proxy (Nginx/Traefik)

### Resource Limits

Add to `docker-compose.yml`:

```yaml
services:
    linkmy-web:
        deploy:
            resources:
                limits:
                    cpus: '1.0'
                    memory: 512M
                reservations:
                    memory: 256M

    linkmy-db:
        deploy:
            resources:
                limits:
                    cpus: '2.0'
                    memory: 1G
                reservations:
                    memory: 512M
```

### Backup Strategy

```bash
#!/bin/bash
# backup.sh - Automated backup script

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="./backups"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
docker exec linkmy_mysql mysqldump \
  -u linkmy_user -plinkmy_pass linkmy_db \
  > $BACKUP_DIR/db_$DATE.sql

# Backup uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz uploads/

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
```

Schedule with cron:

```bash
# Run daily at 2 AM
0 2 * * * /path/to/backup.sh
```

### Monitoring

```bash
# Container health status
docker inspect --format='{{.State.Health.Status}}' linkmy_web

# Resource usage
docker stats --no-stream

# Disk usage
docker system df
```

---

## 📊 Architecture Diagram

```
┌─────────────────────────────────────────────┐
│             Host Machine                     │
│                                              │
│  ┌──────────────────────────────────────┐  │
│  │     Docker Network (bridge)          │  │
│  │                                       │  │
│  │  ┌──────────────┐  ┌──────────────┐ │  │
│  │  │ linkmy-web   │  │ linkmy-db    │ │  │
│  │  │ PHP + Apache │◄─┤ MySQL 8.0    │ │  │
│  │  │ Port 83      │  │ Port 3307    │ │  │
│  │  └──────┬───────┘  └──────────────┘ │  │
│  │         │                  ▲         │  │
│  │         │         ┌────────┘         │  │
│  │         │         │                  │  │
│  │  ┌──────▼─────────┴──┐              │  │
│  │  │ linkmy-phpmyadmin │              │  │
│  │  │ Port 8083         │              │  │
│  │  └───────────────────┘              │  │
│  │                                       │  │
│  └──────────────────────────────────────┘  │
│                                              │
│  Volumes:                                   │
│  • linkmy-db-data (MySQL data)             │
│  • ./uploads (uploaded files)              │
│  • ./config (configuration)                │
└─────────────────────────────────────────────┘
```

---

## 🎯 Summary

**Quick Reference:**

| Service    | URL                   | Credentials               |
| ---------- | --------------------- | ------------------------- |
| Web App    | http://localhost:83   | (your user account)       |
| phpMyAdmin | http://localhost:8083 | linkmy_user / linkmy_pass |
| MySQL      | localhost:3307        | linkmy_user / linkmy_pass |

**Common Commands:**

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Logs
docker-compose logs -f

# Restart
docker-compose restart

# Clean up
docker-compose down -v
```

---

## 🤝 Support

Need help? Check:

-   [Main README](README.md)
-   [Troubleshooting](#troubleshooting)
-   [Docker Documentation](https://docs.docker.com/)

---

**Made with ❤️ for LinkMy v2.1**
