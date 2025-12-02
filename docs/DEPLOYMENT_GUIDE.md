# 🚀 Deployment Guide untuk VPS dengan Docker

## 📋 Overview

Panduan lengkap untuk deploy LinkMy ke VPS menggunakan Docker Compose dengan workflow CI/CD sederhana (Git push → VPS pull → Docker rebuild).

---

## 🎯 Prasyarat

### Di VPS Anda:

1. **Docker & Docker Compose** sudah terinstall
2. **Git** sudah terinstall
3. **SSH access** ke VPS
4. **Port terbuka**: 80 (HTTP), 443 (HTTPS), 3307 (MySQL - optional)

### Di Workspace Lokal:

1. Git repository sudah diinisialisasi
2. Remote repository (GitHub/GitLab) sudah disiapkan

---

## 🔧 Setup Awal VPS

### 1. Install Docker & Docker Compose

```bash
# Update package list
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group (agar tidak perlu sudo)
sudo usermod -aG docker $USER
newgrp docker

# Install Docker Compose (jika belum ada)
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verifikasi instalasi
docker --version
docker-compose --version
```

### 2. Install Git

```bash
sudo apt install git -y
git --version
```

### 3. Setup Directory di VPS

```bash
# Buat directory untuk project
mkdir -p ~/linkmy
cd ~/linkmy
```

---

## 📦 Deployment Workflow

### **Fase 1: Initial Setup (Hanya Sekali)**

#### A. Clone Repository di VPS

```bash
cd ~/linkmy
git clone https://github.com/YOUR_USERNAME/LinkMy.git .

# Atau jika pakai GitLab
git clone https://gitlab.com/YOUR_USERNAME/LinkMy.git .
```

#### B. Buat File `.env` di VPS

```bash
nano .env
```

Isi file `.env`:

```env
# Database Configuration
DB_ROOT_PASSWORD=your_strong_root_password_here
DB_NAME=linkmy_db
DB_USER=linkmy_user
DB_PASSWORD=your_strong_db_password_here

# Email Configuration (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password_here
MAIL_FROM_EMAIL=noreply@linkmy.iet.ovh
MAIL_FROM_NAME=LinkMy
```

**PENTING:** File `.env` ini **HANYA** ada di VPS, **JANGAN** commit ke Git!

#### C. Build & Run Docker Containers (Pertama Kali)

```bash
cd ~/linkmy

# Build dan jalankan containers
docker-compose up -d --build

# Cek status containers
docker-compose ps

# Lihat logs jika ada error
docker-compose logs -f
```

**Containers yang akan berjalan:**

-   `linkmy_web` - Web server (PHP + Apache) di port 80
-   `linkmy_mysql` - Database MySQL di port 3307
-   `linkmy_phpmyadmin` - phpMyAdmin di port 8083

#### D. Verifikasi Deployment

```bash
# Cek apakah web server sudah jalan
curl http://localhost

# Atau buka di browser
http://YOUR_VPS_IP
```

---

### **Fase 2: Update Workflow (Setiap Kali Ada Perubahan)**

#### Dari Workspace Lokal (Windows):

```powershell
# 1. Commit perubahan
git add .
git commit -m "Deskripsi perubahan"

# 2. Push ke remote repository
git push origin master
```

#### Di VPS:

```bash
# 1. Navigate ke project directory
cd ~/linkmy

# 2. Pull perubahan terbaru dari Git
git pull origin master

# 3. Rebuild & restart Docker containers
docker-compose down
docker-compose up -d --build

# 4. Verifikasi
docker-compose ps
docker-compose logs -f web
```

---

## 🤖 Automasi Deployment (Optional - Script Helper)

### Buat Script di VPS untuk Otomasi Update

```bash
nano ~/linkmy/deploy.sh
```

Isi script:

```bash
#!/bin/bash

echo "🚀 Starting deployment..."

# Navigate to project directory
cd ~/linkmy

# Pull latest changes
echo "📥 Pulling latest changes from Git..."
git pull origin master

# Stop containers
echo "🛑 Stopping containers..."
docker-compose down

# Rebuild and start containers
echo "🔨 Building and starting containers..."
docker-compose up -d --build

# Show status
echo "✅ Deployment complete! Container status:"
docker-compose ps

echo ""
echo "📊 To view logs, run: docker-compose logs -f"
```

Buat executable:

```bash
chmod +x ~/linkmy/deploy.sh
```

**Cara pakai:**

```bash
cd ~/linkmy
./deploy.sh
```

---

## 🗄️ Database Management di VPS

### Akses Database via phpMyAdmin

```
http://YOUR_VPS_IP:8083

Login:
- Server: db
- Username: linkmy_user (atau root)
- Password: [dari .env]
```

### Akses Database via Command Line

```bash
# Masuk ke container MySQL
docker exec -it linkmy_mysql mysql -u linkmy_user -p

# Masukkan password dari .env
# Kemudian gunakan database
USE linkmy_db;
SHOW TABLES;
```

### Export Database dari VPS

```bash
# Export database ke file SQL
docker exec linkmy_mysql mysqldump -u linkmy_user -p linkmy_db > backup_$(date +%Y%m%d).sql

# Download ke local (dari local Windows PowerShell)
scp user@YOUR_VPS_IP:~/linkmy/backup_*.sql C:\backup\
```

### Import Database ke VPS

```bash
# Dari VPS
docker exec -i linkmy_mysql mysql -u linkmy_user -p linkmy_db < database.sql

# Atau upload file SQL baru lalu rebuild
# database.sql akan otomatis ter-import saat container pertama kali dibuat
# (jika database belum ada data)
```

---

## 🔐 Security & Production Setup

### 1. Update Docker Compose untuk Production

Edit `docker-compose.yml`:

```yaml
services:
    # Hapus atau comment phpmyadmin untuk production
    # phpmyadmin:
    #   ...

    # Update port web ke standard HTTP/HTTPS
    web:
        ports:
            - '80:80'
            - '443:443' # Jika pakai SSL
```

### 2. Setup SSL/HTTPS (Recommended)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Generate SSL certificate
sudo certbot --apache -d linkmy.iet.ovh

# Auto-renewal (certbot otomatis setup cronjob)
sudo certbot renew --dry-run
```

### 3. Firewall Configuration

```bash
# Install UFW jika belum ada
sudo apt install ufw -y

# Allow SSH
sudo ufw allow ssh

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Optional: Allow MySQL port (jika butuh akses remote)
# sudo ufw allow 3307/tcp

# Enable firewall
sudo ufw enable
sudo ufw status
```

### 4. Database Security

```bash
# Update .env dengan password yang kuat
# Jangan gunakan default password!

# Contoh generate random password
openssl rand -base64 32
```

---

## 📊 Monitoring & Troubleshooting

### Melihat Logs

```bash
# Semua containers
docker-compose logs -f

# Specific container
docker-compose logs -f web
docker-compose logs -f db

# Last 100 lines
docker-compose logs --tail=100 web
```

### Restart Container

```bash
# Restart semua
docker-compose restart

# Restart specific
docker-compose restart web
docker-compose restart db
```

### Troubleshooting Database Connection

```bash
# Cek apakah MySQL container berjalan
docker-compose ps

# Masuk ke web container dan test connection
docker exec -it linkmy_web bash
php -r "echo mysqli_connect('db', 'linkmy_user', 'password', 'linkmy_db') ? 'Connected' : 'Failed';"
```

### Disk Space Issues

```bash
# Cek disk usage
df -h

# Hapus unused Docker images
docker system prune -a

# Hapus volumes yang tidak terpakai
docker volume prune
```

---

## 🔄 Backup & Restore Strategy

### Backup Otomatis (Cron Job)

```bash
# Edit crontab
crontab -e

# Tambahkan job untuk backup harian jam 2 pagi
0 2 * * * cd ~/linkmy && docker exec linkmy_mysql mysqldump -u linkmy_user -pYOUR_PASSWORD linkmy_db > backup_$(date +\%Y\%m\%d).sql
```

### Backup Manual

```bash
# Backup database
docker exec linkmy_mysql mysqldump -u linkmy_user -p linkmy_db > backup.sql

# Backup uploads folder
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### Restore dari Backup

```bash
# Restore database
docker exec -i linkmy_mysql mysql -u linkmy_user -p linkmy_db < backup.sql

# Restore uploads
tar -xzf uploads_backup_YYYYMMDD.tar.gz
```

---

## 🎯 Production Checklist

✅ Checklist sebelum production:

-   [ ] File `.env` sudah dibuat di VPS dengan password kuat
-   [ ] Database backup otomatis sudah disetup
-   [ ] SSL/HTTPS sudah aktif
-   [ ] Firewall (UFW) sudah dikonfigurasi
-   [ ] phpMyAdmin di-disable atau protected
-   [ ] Email SMTP sudah dikonfigurasi dan di-test
-   [ ] Domain DNS sudah pointing ke VPS IP
-   [ ] Git repository sudah di-push
-   [ ] File testing sudah dihapus (jalankan `cleanup-production.ps1`)
-   [ ] Log monitoring sudah disetup

---

## 📝 Workflow Ringkasan

### Development → Production Flow:

```
[Local Windows]
1. Edit code
2. Test di localhost (XAMPP)
3. git add . && git commit -m "message"
4. git push origin master

[VPS - Manual Update]
5. ssh ke VPS
6. cd ~/linkmy
7. git pull origin master
8. docker-compose down && docker-compose up -d --build

[VPS - Automated Script]
5. ssh ke VPS
6. cd ~/linkmy && ./deploy.sh
```

---

## 🆘 Common Issues & Solutions

### Issue 1: Database tidak terkoneksi

**Solution:**

```bash
# Cek apakah MySQL container running
docker-compose ps

# Restart MySQL
docker-compose restart db

# Cek logs
docker-compose logs db
```

### Issue 2: Permission denied pada uploads/

**Solution:**

```bash
# Masuk ke web container
docker exec -it linkmy_web bash

# Set permission
chmod -R 777 /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
```

### Issue 3: Port sudah digunakan

**Solution:**

```bash
# Cek port yang digunakan
sudo netstat -tulpn | grep :80

# Ubah port di docker-compose.yml
# Contoh: '8080:80' instead of '80:80'
```

### Issue 4: Git pull conflict

**Solution:**

```bash
# Stash local changes (jika ada)
git stash

# Pull latest
git pull origin master

# Re-apply stashed changes (jika perlu)
git stash pop
```

---

## 📞 Support & Resources

-   **Docker Docs**: https://docs.docker.com/
-   **Docker Compose**: https://docs.docker.com/compose/
-   **Certbot SSL**: https://certbot.eff.org/
-   **UFW Firewall**: https://help.ubuntu.com/community/UFW

---

## 🎓 Advanced: CI/CD dengan GitHub Actions (Optional)

Jika ingin full automation, bisa setup GitHub Actions untuk auto-deploy ke VPS.

**File: `.github/workflows/deploy.yml`**

```yaml
name: Deploy to VPS

on:
    push:
        branches: [master]

jobs:
    deploy:
        runs-on: ubuntu-latest
        steps:
            - name: Deploy via SSH
              uses: appleboy/ssh-action@master
              with:
                  host: ${{ secrets.VPS_HOST }}
                  username: ${{ secrets.VPS_USER }}
                  key: ${{ secrets.VPS_SSH_KEY }}
                  script: |
                      cd ~/linkmy
                      git pull origin master
                      docker-compose down
                      docker-compose up -d --build
```

**Setup GitHub Secrets:**

1. Go to: Repository → Settings → Secrets → Actions
2. Add:
    - `VPS_HOST`: IP VPS Anda
    - `VPS_USER`: SSH username
    - `VPS_SSH_KEY`: Private SSH key

---

**✨ Selamat! LinkMy Anda siap production di VPS! ✨**
