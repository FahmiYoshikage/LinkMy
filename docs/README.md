# 🔗 LinkMy v2.1 - Advanced Link-in-Bio Platform

> Platform link-in-bio yang powerful untuk menghubungkan semua profil online Anda dalam satu tempat

[![Version](https://img.shields.io/badge/version-2.1.0-blue.svg)](https://github.com/FahmiYoshikage/LinkMy)
[![PHP](https://img.shields.io/badge/PHP-8.1+-purple.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange.svg)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED.svg?logo=docker)](https://www.docker.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**LinkMy** adalah aplikasi web berbasis PHP untuk membuat halaman profil link pribadi yang dapat disesuaikan, mirip dengan Linktree. Dibangun dengan **PHP Native**, **MySQL**, dan **Bootstrap 5.3**.

🌐 **Live Demo**: [https://linkmy.iet.ovh](https://linkmy.iet.ovh)

---

## 📑 Table of Contents

-   [Features](#-features)
-   [What's New in v2.1](#-whats-new-in-v21)
-   [Quick Start](#-quick-start)
-   [Configuration](#%EF%B8%8F-configuration)
-   [Usage Guide](#-usage-guide)
-   [Docker Guide](#-docker-deployment-guide)
-   [Forgot Password Feature](#-forgot-password-feature)
-   [Database Schema](#%EF%B8%8F-database-schema)
-   [Production Deployment](#-production-deployment)
-   [Troubleshooting](#-troubleshooting)
-   [License](#-license)

---

## ✨ Features

### 🎨 Advanced Customization

-   **12 Gradient Presets** - Beautiful pre-designed gradients
-   **Custom Color Picker** - Full control over background, button, and text colors
-   **Profile Layouts** - Centered, Left Aligned, or Minimal layouts
-   **Button Styles** - Rounded, Square, or Pill-shaped buttons
-   **Font Selection** - Multiple Google Font options
-   **Profile Border** - Toggle border around profile picture
-   **Animations** - Enable/disable hover effects

### 📱 Social Features

-   **19 Social Icons** - Pre-configured with brand colors
-   **Link Categories** - Organize links by category
-   **Analytics** - Track link clicks and engagement
-   **Public Profiles** - Share via: `linkmy.iet.ovh/username`

### 🔒 Security & Auth

-   **Email Verification** - OTP-based email confirmation
-   **Forgot Password** - Secure password reset via email
-   **Session Management** - Auto-logout on inactivity
-   **Password Hashing** - Bcrypt encryption
-   **SQL Injection Protection** - Prepared statements

### 📊 Admin Dashboard

-   **Link Management** - Add, edit, delete, reorder links
-   **Appearance Editor** - Real-time preview
-   **Profile Settings** - Update bio, photos, slug
-   **Analytics Dashboard** - View click statistics

### 🐳 Docker Ready

-   **One-Command Setup** - `docker-setup.bat` or `docker-setup.sh`
-   **Port 83** - Custom web service port
-   **phpMyAdmin** - Included on port 8083
-   **Persistent Data** - MySQL volume for data retention

---

## 🆕 What's New in v2.1

### Landing Page

-   ✅ Beautiful landing page explaining LinkMy
-   ✅ Feature showcase with animations
-   ✅ "How It Works" section
-   ✅ Clear CTA to register

### Forgot Password Feature

-   ✅ Email-based password reset
-   ✅ Secure token generation (64-char hex)
-   ✅ Token expiry (1 hour)
-   ✅ One-time use tokens
-   ✅ Email verification check
-   ✅ Password strength indicator

### Domain Configuration

-   ✅ All references updated to `linkmy.iet.ovh`
-   ✅ Auto-detect domain in emails
-   ✅ Production-ready URLs

### Documentation

-   ✅ Comprehensive README (this file)
-   ✅ All docs consolidated
-   ✅ Clean workspace

---

## 🚀 Quick Start

### Prerequisites

-   **PHP** 8.1+ (with mysqli, gd extensions)
-   **MySQL** 8.0+ or **MariaDB** 10.6+
-   **Apache** or **Nginx** (with mod_rewrite/URL rewriting)
-   **Composer** (optional, for PHPMailer)

### Option 1: Docker (Recommended) 🐳

**Paling mudah dan cepat!** Tidak perlu install PHP, MySQL, atau Apache.

#### Windows

```cmd
docker-setup.bat
```

#### Linux/Mac

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

**Akses aplikasi:**

-   Web: http://localhost:83
-   phpMyAdmin: http://localhost:8083
-   MySQL: localhost:3307

**Default Login:**

-   Username: `admin`
-   Password: `admin123`

---

### Option 2: Manual Installation

#### 1. Clone Repository

```bash
git clone https://github.com/FahmiYoshikage/LinkMy.git
cd LinkMy
```

#### 2. Setup Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE linkmy_db;
USE linkmy_db;
SOURCE database.sql;
EXIT;
```

#### 3. Configure Database Connection

Edit `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'linkmy_db');
define('DB_USER', 'your_mysql_user');
define('DB_PASS', 'your_mysql_password');
```

#### 4. Setup Email (Optional)

Edit `config/mail.php` for forgot password feature:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password'; // Gmail App Password
$mail->Port = 587;
```

#### 5. Configure URL Rewriting

**Apache (.htaccess already included):**

```apache
RewriteEngine On
RewriteBase /
RewriteRule ^([a-zA-Z0-9_-]+)$ redirect.php?slug=$1 [L,QSA]
```

**Nginx:**

```nginx
location / {
    try_files $uri $uri/ /redirect.php?slug=$uri&$args;
}
```

#### 6. Set Permissions

```bash
# Linux/Mac
chmod 755 uploads/
chmod 755 uploads/profiles/
chmod 755 uploads/backgrounds/
chmod 755 uploads/folder_pics/

# Windows - Right-click folders → Properties → Security → Edit permissions
```

#### 7. Access Application

-   Homepage: `http://localhost/LinkMy/` or `http://linkmy.iet.ovh/`
-   Register: `http://linkmy.iet.ovh/register.php`
-   Login: `http://linkmy.iet.ovh/login.php`

---

## ⚙️ Configuration

### Environment Variables (Docker)

Create `.env` file:

```env
# Database
MYSQL_ROOT_PASSWORD=linkmy_root_pass
MYSQL_DATABASE=linkmy_db
MYSQL_USER=linkmy_user
MYSQL_PASSWORD=linkmy_pass

# Email (optional)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@linkmy.iet.ovh
MAIL_FROM_NAME=LinkMy
```

### URL Configuration

For production, update these files:

**1. config/db.php** - Database credentials
**2. config/mail.php** - SMTP settings
**3. .htaccess** - RewriteBase if in subfolder

### Domain Configuration

All references now use `linkmy.iet.ovh`:

-   Email templates
-   Registration previews
-   Password reset links
-   Social share links

---

## 📖 Usage Guide

### 1. Register Account

1. Visit landing page → Click "Daftar Gratis"
2. Fill form: username, email, password
3. Verify email with OTP code
4. Login with your credentials

### 2. Setup Your Profile

1. Login → Dashboard
2. Go to **Appearance** tab
3. Customize:
    - Upload profile picture
    - Choose gradient preset or custom colors
    - Select layout style (Centered/Left/Minimal)
    - Pick font family
    - Configure button style

### 3. Add Links

1. Go to **Dashboard** tab
2. Click "Add New Link"
3. Fill:
    - Title (e.g., "Instagram")
    - URL (https://instagram.com/username)
    - Icon (choose from 19 social icons)
4. Drag to reorder links
5. Click "Save Order"

### 4. Share Your Profile

Your public link: `https://linkmy.iet.ovh/username`

Share on:

-   Instagram bio
-   TikTok bio
-   YouTube description
-   Email signature
-   Business card

### 5. View Analytics

-   Dashboard shows click count for each link
-   Track which links perform best
-   Optimize your content strategy

---

## 🐳 Docker Deployment Guide

### Quick Commands

```bash
# Start containers
docker-compose up -d

# View logs
docker-compose logs -f

# Stop containers
docker-compose down

# Rebuild after code changes
docker-compose up -d --build

# Enter web container
docker exec -it linkmy_web bash

# Enter MySQL container
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db

# View container status
docker-compose ps
```

### Docker Services

| Service    | Container         | Port | Access                |
| ---------- | ----------------- | ---- | --------------------- |
| Web App    | linkmy_web        | 83   | http://localhost:83   |
| MySQL      | linkmy_mysql      | 3307 | localhost:3307        |
| phpMyAdmin | linkmy_phpmyadmin | 8083 | http://localhost:8083 |

### Port Configuration

Edit `docker-compose.yml` to change ports:

```yaml
services:
    linkmy-web:
        ports:
            - '83:80' # Change 83 to your desired port
```

### Database Access

**From Host Machine:**

```bash
mysql -h 127.0.0.1 -P 3307 -u linkmy_user -plinkmy_pass linkmy_db
```

**From Docker Container:**

```bash
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db
```

**Via phpMyAdmin:**

-   URL: http://localhost:8083
-   Server: `db`
-   Username: `linkmy_user`
-   Password: `linkmy_pass`

### Persistent Data

Data is stored in Docker volume `mysql_data`:

```bash
# Backup database
docker exec linkmy_mysql mysqldump -u linkmy_user -plinkmy_pass linkmy_db > backup.sql

# Restore database
docker exec -i linkmy_mysql mysql -u linkmy_user -plinkmy_pass linkmy_db < backup.sql

# Remove volume (⚠️ deletes all data)
docker-compose down -v
```

---

## 🔐 Forgot Password Feature

### User Flow

1. **Request Reset**

    - Click "Lupa Password?" on login page
    - Enter registered email
    - System checks if email exists
    - If not registered → Show "Daftar Akun Baru" link

2. **Receive Email**

    - Email sent with reset link
    - Link format: `https://linkmy.iet.ovh/reset-password.php?token=xxx`
    - Token valid for 1 hour
    - Token is one-time use only

3. **Reset Password**

    - Click link in email
    - System validates:
        - ✅ Token exists
        - ✅ Token not expired
        - ✅ Token not used
    - Enter new password (min 6 characters)
    - Password strength indicator shows

4. **Login**
    - Password updated successfully
    - Auto-redirect to login page (3 seconds)
    - Login with new password

### Security Features

-   **Token Generation**: `bin2hex(random_bytes(32))` = 64-character hex
-   **Token Expiry**: 1 hour from creation
-   **One-Time Use**: Token marked as used after reset
-   **Password Hashing**: Bcrypt with cost factor 10
-   **Email Verification**: Only registered emails can reset
-   **IP Tracking**: Store IP address of reset request

### Database Table

```sql
CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (reset_token),
    INDEX idx_email (email),
    INDEX idx_expires_at (expires_at)
);
```

### Configuration

**Setup Gmail SMTP** (recommended):

1. Enable 2-Factor Authentication in Gmail
2. Generate App Password:

    - Google Account → Security → 2-Step Verification → App passwords
    - Select app: Mail
    - Select device: Other (Custom name)
    - Copy 16-character password

3. Edit `config/mail.php`:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'abcd efgh ijkl mnop'; // App Password
$mail->Port = 587;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
```

---

## 🗄️ Database Schema

### Core Tables

#### 1. users

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    page_slug VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. links

```sql
CREATE TABLE links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    order_index INT DEFAULT 0,
    icon_class VARCHAR(50) DEFAULT 'bi-link-45deg',
    click_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    category_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

#### 3. appearance

```sql
CREATE TABLE appearance (
    appearance_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    profile_title VARCHAR(100),
    bio TEXT,
    profile_pic_filename VARCHAR(255) DEFAULT 'default-avatar.png',
    bg_image_filename VARCHAR(255),
    theme_name VARCHAR(20) DEFAULT 'light',
    button_style VARCHAR(20) DEFAULT 'rounded',
    font_family VARCHAR(50) DEFAULT 'Inter',
    custom_bg_color VARCHAR(7),
    custom_button_color VARCHAR(7),
    custom_text_color VARCHAR(7),
    gradient_preset VARCHAR(50),
    profile_layout VARCHAR(20) DEFAULT 'centered',
    show_profile_border TINYINT(1) DEFAULT 1,
    enable_animations TINYINT(1) DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

#### 4. password_resets

```sql
CREATE TABLE password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    reset_token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (reset_token),
    INDEX idx_email (email),
    INDEX idx_expires_at (expires_at)
);
```

#### 5. email_verifications

```sql
CREATE TABLE email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used TINYINT(1) DEFAULT 0,
    INDEX idx_email_otp (email, otp_code),
    INDEX idx_expires (expires_at)
);
```

---

## 📦 Production Deployment

### Pre-Deployment Checklist

#### 1. Security

-   [ ] Change default admin password
-   [ ] Update database credentials
-   [ ] Set strong `DB_PASS` in production
-   [ ] Enable HTTPS/SSL certificate
-   [ ] Configure CSP headers
-   [ ] Hide error messages (`display_errors = Off`)

#### 2. Database

-   [ ] Export clean database with `database.sql`
-   [ ] Remove test data
-   [ ] Optimize tables (`OPTIMIZE TABLE`)
-   [ ] Setup automated backups

#### 3. Files to Remove for Production

```bash
# Test files
test-forgot-password.html
cekidot.php

# Development docs (keep only README.md)
CHANGELOG.md
DEPLOYMENT.md
DEPLOYMENT_CHECKLIST.md
DOCKER.md
DOCKER_FILES_SUMMARY.md
DOCKER_QUICKSTART.md
DOCKER_SETUP_COMPLETE.md
FEATURES_V2.md
FORGOT_PASSWORD_DOCUMENTATION.md
FORGOT_PASSWORD_SUMMARY.md
TROUBLESHOOTING_FIXES.md
UPDATE_SUMMARY.md
VISUAL_GUIDE.md
QUICK_START.md
DOCUMENTATION_INDEX.md
DATABASE_SCHEMA.md

# SQL migration files (keep only database.sql)
database_update_v2.sql
database_update_v2.1.sql
linkmy_db.sql
add_email_verification_table.sql

# Docker files (if not using Docker in production)
docker-compose.yml
Dockerfile
.dockerignore
docker-setup.bat
docker-setup.sh
```

#### 4. Performance

-   [ ] Enable OpCache in PHP
-   [ ] Enable Gzip compression
-   [ ] Minify CSS/JS (optional)
-   [ ] Setup CDN for assets (optional)
-   [ ] Configure caching headers

#### 5. Monitoring

-   [ ] Setup error logging
-   [ ] Configure uptime monitoring
-   [ ] Enable slow query log
-   [ ] Setup analytics (optional)

### Production Configuration

#### Apache Virtual Host

```apache
<VirtualHost *:80>
    ServerName linkmy.iet.ovh
    ServerAlias www.linkmy.iet.ovh
    DocumentRoot /var/www/linkmy

    <Directory /var/www/linkmy>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/linkmy-error.log
    CustomLog ${APACHE_LOG_DIR}/linkmy-access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerName linkmy.iet.ovh
    ServerAlias www.linkmy.iet.ovh
    DocumentRoot /var/www/linkmy

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/privkey.pem
    SSLCertificateChainFile /path/to/chain.pem

    <Directory /var/www/linkmy>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/linkmy-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/linkmy-ssl-access.log combined
</VirtualHost>
```

#### PHP Production Settings

```ini
; php.ini
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 30
session.cookie_httponly = 1
session.cookie_secure = 1
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000
```

### Database Backup Script

```bash
#!/bin/bash
# backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/linkmy"
DB_NAME="linkmy_db"
DB_USER="linkmy_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR

mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/linkmy_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "linkmy_*.sql.gz" -mtime +7 -delete

echo "Backup completed: linkmy_$DATE.sql.gz"
```

**Setup cron job:**

```cron
# Daily backup at 2 AM
0 2 * * * /path/to/backup-database.sh
```

---

## 🐛 Troubleshooting

### Common Issues

#### 1. Database Connection Error

**Symptom:** `SQLSTATE[HY000] [1045] Access denied`

**Solution:**

```bash
# Check credentials in config/db.php
# Test connection
mysql -u linkmy_user -p -h localhost linkmy_db
```

#### 2. Upload Directory Not Writable

**Symptom:** Profile picture upload fails

**Solution:**

```bash
# Linux/Mac
chmod 755 uploads/
chmod 755 uploads/profiles/
chmod 755 uploads/backgrounds/

# Or
chown -R www-data:www-data uploads/
```

#### 3. Email Not Sending

**Symptom:** Forgot password email not received

**Solution:**

-   Check SMTP credentials in `config/mail.php`
-   Use Gmail App Password (not regular password)
-   Check spam folder
-   Verify PHPMailer path is correct

#### 4. Public Profile 404 Error

**Symptom:** `linkmy.iet.ovh/username` returns 404

**Solution:**

-   Check `.htaccess` exists
-   Enable mod_rewrite: `sudo a2enmod rewrite`
-   Restart Apache: `sudo systemctl restart apache2`
-   Check `redirect.php` exists

#### 5. Docker Container Won't Start

**Symptom:** `docker-compose up` fails

**Solution:**

```bash
# Check port conflicts
netstat -ano | findstr :83
netstat -ano | findstr :3307

# Stop conflicting services
# Rebuild containers
docker-compose down
docker-compose up --build
```

#### 6. Session Expired Error

**Symptom:** Logged out frequently

**Solution:**
Edit `config/auth_check.php`:

```php
define('SESSION_TIMEOUT', 3600); // 1 hour
// Increase to 7200 for 2 hours
```

---

## 📄 License

This project is licensed under the MIT License.

```
MIT License

Copyright (c) 2025 FahmiYoshikage

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Support

-   **GitHub**: [https://github.com/FahmiYoshikage/LinkMy](https://github.com/FahmiYoshikage/LinkMy)
-   **Email**: admin@linkmy.iet.ovh
-   **Website**: [https://linkmy.iet.ovh](https://linkmy.iet.ovh)

---

## 🎉 Credits

-   **Developer**: FahmiYoshikage
-   **Framework**: PHP Native + Bootstrap 5.3
-   **Icons**: Bootstrap Icons 1.11.0
-   **Email**: PHPMailer 7.0.0
-   **Fonts**: Google Fonts
-   **Inspiration**: Linktree, Carrd, Bio.fm

---

**Made with ❤️ for creators everywhere | LinkMy v2.1 | 2025**
