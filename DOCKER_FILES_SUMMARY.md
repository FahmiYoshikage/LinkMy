# 📦 Docker Files Summary - LinkMy v2.1

Dokumentasi lengkap untuk semua file Docker yang telah ditambahkan.

## 📁 File Structure

```
LinkMy/
├── Dockerfile                    # Image definition untuk web container
├── docker-compose.yml            # Orchestration untuk semua services
├── .dockerignore                 # Files yang di-exclude dari build
├── .env.example                  # Template environment variables
├── config/
│   └── db.docker.php            # Database config untuk Docker
├── docker-setup.sh              # Setup script untuk Linux/Mac
├── docker-setup.bat             # Setup script untuk Windows
├── DOCKER.md                     # Dokumentasi lengkap Docker
└── DOCKER_QUICKSTART.md         # Panduan cepat Docker (Bahasa Indonesia)
```

---

## 📄 File Details

### 1. Dockerfile

**Fungsi:** Mendefinisikan image Docker untuk web application

**Isi:**
- Base image: `php:8.1-apache`
- Extensions: mysqli, pdo_mysql, gd
- Apache modules: mod_rewrite, mod_headers
- Upload directories dengan permissions yang tepat
- PHP configuration untuk upload

**Key Features:**
```dockerfile
FROM php:8.1-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql gd
RUN a2enmod rewrite headers
```

---

### 2. docker-compose.yml

**Fungsi:** Orchestrate 3 services (web, database, phpMyAdmin)

**Services:**

#### Service 1: web (LinkMy Application)
- **Container**: linkmy_web
- **Port**: 83:80 ⭐ (Port 83 di host → Port 80 di container)
- **Volumes**: 
  - `./:/var/www/html` (source code)
  - `./uploads:/var/www/html/uploads` (user uploads)
- **Environment**: DB credentials, Mail config

#### Service 2: db (MySQL Database)
- **Container**: linkmy_mysql
- **Image**: mysql:8.0
- **Port**: 3307:3306 (Avoid conflict dengan MySQL di host)
- **Volumes**:
  - `mysql_data:/var/lib/mysql` (persistent data)
  - `./database.sql` (auto-import on first run)
- **Credentials**:
  - Root: rootpassword
  - User: linkmy_user
  - Pass: linkmy_password
  - DB: linkmy_db

#### Service 3: phpmyadmin (Database Management)
- **Container**: linkmy_phpmyadmin
- **Image**: phpmyadmin:latest
- **Port**: 8083:80
- **Auto-connect**: ke service 'db'

**Network:**
- Name: linkmy_network
- Driver: bridge
- All services connected

---

### 3. .dockerignore

**Fungsi:** Exclude files dari Docker build context

**Excludes:**
- Git files (.git, .gitignore)
- Documentation (*.md, Documentation/)
- IDE files (.vscode, .idea)
- Logs (*.log)
- Node modules
- Temporary files

**Why Important:**
- Faster build time
- Smaller image size
- Security (tidak include sensitive files)

---

### 4. .env.example

**Fungsi:** Template untuk environment variables

**Variables:**

```bash
# Database
DB_ROOT_PASSWORD=linkmy_root_secure_pass_2024
DB_NAME=linkmy_db
DB_USER=linkmy_user
DB_PASSWORD=linkmy_secure_pass_2024

# Ports
WEB_PORT=83          ⭐ Web application port
DB_PORT=3307         # MySQL port
PHPMYADMIN_PORT=8083 # phpMyAdmin port

# Email (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password

# PHP Settings
PHP_MEMORY_LIMIT=256M
PHP_UPLOAD_MAX_SIZE=10M
```

**Usage:**
```bash
# 1. Copy file
cp .env.example .env

# 2. Edit dengan values yang sesuai
nano .env

# 3. Docker Compose otomatis load .env
docker-compose up -d
```

---

### 5. config/db.docker.php

**Fungsi:** Database configuration untuk Docker environment

**Key Differences dari db.php:**
```php
// db.php (XAMPP/Manual)
define('DB_HOST', 'localhost');

// db.docker.php (Docker)
define('DB_HOST', 'db');  // ← Service name di docker-compose.yml
```

**Why Needed:**
- Container network menggunakan service name sebagai hostname
- `localhost` di dalam container = container itu sendiri, bukan host machine
- `db` = hostname dari MySQL container

---

### 6. docker-setup.sh (Linux/Mac)

**Fungsi:** Interactive setup script untuk Linux/Mac

**Features:**
1. ✅ Check Docker & Docker Compose installed
2. ✅ Check required files (database.sql, etc)
3. ✅ Create upload directories
4. ✅ Copy database config untuk Docker
5. ✅ Interactive menu:
   - Build and start containers
   - Start existing containers
   - Stop containers
   - Rebuild (clean start)
   - View logs
   - Clean up (remove data)

**Usage:**
```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

**Output:**
```
==========================================
  LinkMy v2.1 - Docker Setup
==========================================
✓ Docker is installed
✓ Docker Compose is installed
✓ database.sql found
✓ Upload directories created

What would you like to do?
1) Build and start containers
2) Start existing containers
...
```

---

### 7. docker-setup.bat (Windows)

**Fungsi:** Interactive setup script untuk Windows

**Same features** sebagai docker-setup.sh tapi untuk:
- Command Prompt
- PowerShell
- Windows batch syntax

**Usage:**
```cmd
docker-setup.bat
```

**Key Commands:**
```batch
@echo off
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not running
)
```

---

### 8. DOCKER.md

**Fungsi:** Dokumentasi lengkap Docker deployment

**Sections:**
1. Prerequisites
2. Quick Start
3. Configuration
4. Services Detail
5. Management Commands
6. Troubleshooting
7. Production Deployment
8. Architecture Diagram

**Target Audience:** Developers dan DevOps

**Content:** Technical deep-dive

---

### 9. DOCKER_QUICKSTART.md

**Fungsi:** Panduan cepat Docker (Bahasa Indonesia)

**Sections:**
1. Prasyarat
2. Quick Start (Windows & Linux)
3. Login pertama kali
4. Akses database
5. Perintah berguna
6. Troubleshooting cepat

**Target Audience:** End users dan beginners

**Content:** Simple, step-by-step instructions

---

## 🚀 Workflow Usage

### Development Workflow

```bash
# 1. Clone repository
git clone [repo] linkmy
cd linkmy

# 2. Start Docker containers
docker-compose up -d --build

# 3. Access application
# http://localhost:83

# 4. Make code changes
# Edit files di host machine

# 5. Refresh browser
# Changes appear immediately (volume mounted)

# 6. View logs jika ada error
docker-compose logs -f web

# 7. Restart jika perlu
docker-compose restart web
```

### Production Workflow

```bash
# 1. Clone di production server
git clone [repo] linkmy
cd linkmy

# 2. Create .env dari template
cp .env.example .env
nano .env  # Edit credentials

# 3. Build dengan production settings
docker-compose -f docker-compose.yml up -d --build

# 4. Check health
docker-compose ps
docker-compose logs

# 5. Setup backup cron job
# (See DOCKER.md Production section)
```

---

## 🔧 Configuration Examples

### Change Port to 8080

Edit `docker-compose.yml`:
```yaml
services:
  web:
    ports:
      - '8080:80'  # Changed from 83
```

### Use External MySQL

Edit `docker-compose.yml`:
```yaml
services:
  web:
    environment:
      DB_HOST: mysql.example.com
      DB_USER: external_user
      DB_PASSWORD: external_pass
      
  # Comment out db service
  # db:
  #   ...
```

### Add SSL/TLS

1. Get SSL certificates
2. Edit `docker-compose.yml`:
```yaml
services:
  web:
    ports:
      - '83:80'
      - '443:443'
    volumes:
      - ./ssl:/etc/apache2/ssl
```

3. Configure Apache SSL in Dockerfile

---

## 📊 Port Mapping Summary

| Service | Container Port | Host Port | URL |
|---------|---------------|-----------|-----|
| Web App | 80 | **83** ⭐ | http://localhost:83 |
| MySQL | 3306 | 3307 | localhost:3307 |
| phpMyAdmin | 80 | 8083 | http://localhost:8083 |

**Why Port 83?**
- User request: "dockerisasi supaya service web nya berjalan di port 83"
- Avoid conflict dengan services lain di port 80
- Easy to remember: 8**3** = LinkM**y**

---

## 🎯 Key Achievements

✅ **Full Docker Support**
- Multi-container setup (web + db + phpMyAdmin)
- Auto-initialization with database.sql
- Volume persistence untuk data
- Health checks untuk semua services

✅ **Custom Port 83** ⭐
- Web application accessible di port 83
- No conflict dengan default Apache (port 80)
- Easy to change via docker-compose.yml

✅ **Production Ready**
- Environment variables support
- Security best practices
- Backup/restore procedures
- Resource limits

✅ **Developer Friendly**
- Interactive setup scripts (Windows & Linux)
- Comprehensive documentation
- Quick start guide dalam Bahasa Indonesia
- Troubleshooting common issues

---

## 🔍 Verification Checklist

Setelah setup, verify dengan:

```bash
# 1. Container status
docker-compose ps
# All containers should be "Up"

# 2. Web application
curl http://localhost:83
# Should return HTML

# 3. Database connection
docker exec linkmy_mysql mysql -u linkmy_user -plinkmy_password -e "SHOW DATABASES;"
# Should show linkmy_db

# 4. phpMyAdmin
curl http://localhost:8083
# Should return phpMyAdmin login page

# 5. Upload directories
docker exec linkmy_web ls -la /var/www/html/uploads
# Should show profile_pics, backgrounds, folder_pics

# 6. PHP extensions
docker exec linkmy_web php -m | grep -E "mysqli|pdo_mysql|gd"
# Should show all 3 extensions
```

Expected output:
```
✓ All containers running
✓ Web accessible on port 83
✓ Database initialized with tables
✓ phpMyAdmin working
✓ Upload directories exist with correct permissions
✓ PHP extensions loaded
```

---

## 📚 Related Documentation

- **Main README**: [README.md](README.md)
- **Docker Full Guide**: [DOCKER.md](DOCKER.md)
- **Quick Start**: [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md)
- **Deployment Guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- **Features**: [FEATURES_V2.md](FEATURES_V2.md)

---

## 🎉 Summary

**What We Built:**
1. 🐳 Complete Docker environment
2. 🔧 3 services: web (port 83), MySQL (port 3307), phpMyAdmin (port 8083)
3. 📜 Automated setup scripts
4. 📖 Comprehensive documentation
5. 🛡️ Production-ready configuration

**Benefits:**
- ⚡ Fast setup (< 5 minutes)
- 🔒 Isolated environment
- 🔄 Portable across machines
- 📦 No manual PHP/MySQL installation needed
- 🎯 **Custom port 83 as requested** ⭐

---

**Made with ❤️ for LinkMy v2.1 Docker Deployment**

**Service running on port 83** ✅
