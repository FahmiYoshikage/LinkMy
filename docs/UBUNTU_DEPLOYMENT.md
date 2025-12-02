# 🐧 Ubuntu Server Deployment Guide

## 📋 Quick Commands for Ubuntu

### 1️⃣ Initial Setup (First Time)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add your user to docker group (no more sudo needed)
sudo usermod -aG docker $USER
newgrp docker

# Install Docker Compose
sudo apt install docker-compose -y

# Verify installation
docker --version
docker-compose --version
```

---

### 2️⃣ Clone Repository

```bash
# Via HTTPS
git clone https://github.com/FahmiYoshikage/LinkMy.git
cd LinkMy

# Or via SSH (if you have SSH key)
git clone git@github.com:FahmiYoshikage/LinkMy.git
cd LinkMy
```

---

### 3️⃣ Configure Environment

```bash
# Copy example env file
cp .env.example .env

# Edit with your settings
nano .env
```

Example `.env`:

```env
DB_ROOT_PASSWORD=your_secure_password
DB_NAME=linkmy_db
DB_USER=linkmy_user
DB_PASSWORD=your_db_password

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_EMAIL=noreply@linkmy.iet.ovh
MAIL_FROM_NAME=LinkMy
```

---

### 4️⃣ First Time Start

```bash
# Make sure ports are available
sudo netstat -tulpn | grep -E ':(83|443|3307|8083)'

# Start containers
docker-compose up -d

# Check status
docker-compose ps

# Check logs
docker-compose logs -f
```

**Wait 30-60 seconds** for MySQL to initialize on first run.

---

### 5️⃣ Upgrade to Latest Stack

```bash
# Run automated upgrade script
chmod +x upgrade_docker.sh
./upgrade_docker.sh
```

This will:

-   ✅ Backup database
-   ✅ Upgrade to MySQL 8.4 LTS
-   ✅ Upgrade to PHP 8.3
-   ✅ Upgrade to phpMyAdmin 5.2
-   ✅ Fix all SQL mode issues

---

### 6️⃣ Database Migration

After upgrade, open browser:

```
http://your-server-ip:83/migrate_to_v2.php
```

Or via curl:

```bash
curl http://localhost:83/migrate_to_v2.php
```

---

## 🔧 Common Management Commands

### Container Management

```bash
# Start all containers
docker-compose up -d

# Stop all containers
docker-compose down

# Restart specific container
docker-compose restart web
docker-compose restart db

# View logs
docker-compose logs -f web
docker-compose logs -f db --tail=100

# Check status
docker-compose ps

# View resource usage
docker stats
```

### Database Management

```bash
# Backup database
docker exec linkmy_mysql mysqldump -u root -prootpassword linkmy_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore database
docker exec -i linkmy_mysql mysql -u root -prootpassword linkmy_db < backup.sql

# Access MySQL shell
docker exec -it linkmy_mysql mysql -u root -prootpassword linkmy_db

# Check MySQL version
docker exec linkmy_mysql mysql -u root -prootpassword -e "SELECT VERSION();"
```

### File Management

```bash
# View container files
docker exec linkmy_web ls -la /var/www/html

# Copy file to container
docker cp local_file.php linkmy_web:/var/www/html/

# Copy file from container
docker cp linkmy_web:/var/www/html/file.php ./

# Check uploads directory
docker exec linkmy_web ls -la /var/www/html/uploads
```

### Logs & Debugging

```bash
# All logs
docker-compose logs -f

# Last 100 lines
docker-compose logs --tail=100

# Web server logs
docker exec linkmy_web tail -f /var/log/apache2/error.log

# PHP errors
docker exec linkmy_web cat /var/www/html/error_log

# Check Apache status
docker exec linkmy_web apache2ctl -S
```

---

## 🌐 Nginx Reverse Proxy (Production)

If using domain name, setup Nginx as reverse proxy:

```bash
# Install Nginx
sudo apt install nginx -y

# Create config
sudo nano /etc/nginx/sites-available/linkmy
```

Nginx config:

```nginx
server {
    listen 80;
    server_name linkmy.iet.ovh www.linkmy.iet.ovh;

    location / {
        proxy_pass http://localhost:83;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Enable site:

```bash
sudo ln -s /etc/nginx/sites-available/linkmy /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### SSL with Let's Encrypt

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get certificate
sudo certbot --nginx -d linkmy.iet.ovh -d www.linkmy.iet.ovh

# Auto-renewal (cron)
sudo certbot renew --dry-run
```

---

## 🔥 Firewall Setup (UFW)

```bash
# Install UFW
sudo apt install ufw -y

# Allow SSH (IMPORTANT!)
sudo ufw allow 22/tcp

# Allow HTTP & HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Allow Docker ports (if accessing directly)
sudo ufw allow 83/tcp
sudo ufw allow 3307/tcp
sudo ufw allow 8083/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

---

## 📊 Monitoring & Maintenance

### Disk Space

```bash
# Check disk usage
df -h

# Check Docker disk usage
docker system df

# Clean up
docker system prune -a --volumes
```

### Performance

```bash
# CPU & Memory usage
htop

# Docker stats
docker stats --no-stream

# Container resource limits
docker-compose config | grep -A 5 "resources"
```

### Auto-start on Boot

```bash
# Enable Docker on boot
sudo systemctl enable docker

# Containers will auto-restart (restart: always in docker-compose.yml)
```

---

## 🔄 Update Application

```bash
# Pull latest code
cd /path/to/LinkMy
git pull origin master

# Rebuild and restart
docker-compose down
docker-compose build --no-cache web
docker-compose up -d

# Check logs
docker-compose logs -f web
```

---

## 🐛 Troubleshooting

### Port Already in Use

```bash
# Find what's using port 83
sudo lsof -i :83
sudo netstat -tulpn | grep :83

# Kill process
sudo kill -9 <PID>
```

### Permission Issues

```bash
# Fix uploads directory permissions
docker exec linkmy_web chown -R www-data:www-data /var/www/html/uploads
docker exec linkmy_web chmod -R 755 /var/www/html/uploads
```

### MySQL Won't Start

```bash
# Check logs
docker-compose logs db

# Remove and recreate volume
docker-compose down
docker volume rm linkmy_mysql_data
docker-compose up -d
```

### Container Keeps Restarting

```bash
# Check why
docker-compose logs web --tail=50

# Common fixes:
# 1. Check db.php connection settings
# 2. Wait for MySQL to be ready
# 3. Check file permissions
```

---

## 📚 Useful Aliases

Add to `~/.bashrc`:

```bash
# LinkMy aliases
alias linkmy-start='cd ~/LinkMy && docker-compose up -d'
alias linkmy-stop='cd ~/LinkMy && docker-compose down'
alias linkmy-logs='cd ~/LinkMy && docker-compose logs -f'
alias linkmy-restart='cd ~/LinkMy && docker-compose restart'
alias linkmy-backup='cd ~/LinkMy && docker exec linkmy_mysql mysqldump -u root -prootpassword linkmy_db > backup_$(date +%Y%m%d).sql'
alias linkmy-shell='docker exec -it linkmy_web bash'
alias linkmy-mysql='docker exec -it linkmy_mysql mysql -u root -prootpassword linkmy_db'
```

Apply:

```bash
source ~/.bashrc
```

---

## 🔐 Security Checklist

-   [ ] Change default database passwords in `.env`
-   [ ] Disable phpMyAdmin in production (comment out in docker-compose.yml)
-   [ ] Setup UFW firewall
-   [ ] Setup SSL with Let's Encrypt
-   [ ] Use Nginx reverse proxy (hide port 83)
-   [ ] Regular backups (cron job)
-   [ ] Keep Docker & containers updated
-   [ ] Monitor logs for suspicious activity
-   [ ] Limit MySQL external access (only localhost)

---

## 📦 Production Deployment Checklist

-   [ ] Server setup (Ubuntu 20.04/22.04 LTS)
-   [ ] Docker & Docker Compose installed
-   [ ] Repository cloned
-   [ ] `.env` configured
-   [ ] Upgrade script executed (`./upgrade_docker.sh`)
-   [ ] Database migrated (`migrate_to_v2.php`)
-   [ ] Nginx reverse proxy configured
-   [ ] SSL certificate installed
-   [ ] Firewall enabled (UFW)
-   [ ] Backups automated (cron)
-   [ ] Domain DNS pointed to server
-   [ ] Test all features
-   [ ] Monitor logs for 24 hours

---

## 🚀 Quick Production Setup

```bash
# Full setup in one go
git clone https://github.com/FahmiYoshikage/LinkMy.git
cd LinkMy
cp .env.example .env
nano .env  # Edit your settings
chmod +x upgrade_docker.sh
./upgrade_docker.sh
# Wait for completion, then open browser to http://your-ip:83
```

---

**Need help?** Check logs: `docker-compose logs -f`
