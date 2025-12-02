# 🚀 CI/CD Quick Commands Cheatsheet

## 📦 Workflow Standard (Push → Pull → Build)

### 🖥️ Di Local Windows (Development)

```powershell
# 1. Check status
git status

# 2. Add semua perubahan
git add .

# 3. Commit dengan message
git commit -m "Add new feature: XYZ"

# 4. Push ke repository
git push origin master

# Done! 🎉
```

---

### 🌐 Di VPS (Production)

#### Method 1: Manual Commands

```bash
# 1. SSH ke VPS
ssh user@YOUR_VPS_IP

# 2. Navigate ke project
cd ~/linkmy

# 3. Pull latest changes
git pull origin master

# 4. Rebuild Docker
docker-compose down
docker-compose up -d --build

# 5. Verify
docker-compose ps
docker-compose logs -f web
```

#### Method 2: Menggunakan Deploy Script

```bash
# 1. SSH ke VPS
ssh user@YOUR_VPS_IP

# 2. Run deploy script
cd ~/linkmy && ./deploy.sh
```

---

## 🔧 Docker Commands Frequently Used

### Container Management

```bash
# Lihat semua containers
docker-compose ps

# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Restart containers
docker-compose restart

# Restart specific container
docker-compose restart web
docker-compose restart db
```

### Logs & Debugging

```bash
# View logs (semua containers)
docker-compose logs -f

# View logs (specific container)
docker-compose logs -f web
docker-compose logs -f db

# Last 100 lines
docker-compose logs --tail=100 web

# Real-time logs
docker-compose logs -f --tail=50 web
```

### Rebuild Containers

```bash
# Rebuild tanpa cache (full rebuild)
docker-compose build --no-cache

# Rebuild dan start
docker-compose up -d --build

# Force recreate containers
docker-compose up -d --force-recreate
```

### Clean Up

```bash
# Stop dan hapus containers
docker-compose down

# Stop, hapus containers + volumes (DANGER!)
docker-compose down -v

# Hapus unused images
docker image prune -a

# Hapus semua yang tidak terpakai
docker system prune -a --volumes
```

---

## 📂 File Management Commands

### Upload File ke VPS

```powershell
# Dari Windows PowerShell
scp file.txt user@YOUR_VPS_IP:~/linkmy/

# Upload folder
scp -r folder/ user@YOUR_VPS_IP:~/linkmy/
```

### Download File dari VPS

```powershell
# Dari Windows PowerShell
scp user@YOUR_VPS_IP:~/linkmy/backup.sql C:\backup\

# Download folder
scp -r user@YOUR_VPS_IP:~/linkmy/uploads/ C:\backup\
```

### Edit File di VPS

```bash
# Menggunakan nano (text editor)
nano .env
nano docker-compose.yml

# Menggunakan vi/vim
vi .env

# Lihat isi file
cat .env
```

---

## 🗄️ Database Commands Quick Reference

### Backup Database

```bash
# Export ke file SQL
docker exec linkmy_mysql mysqldump -u linkmy_user -pPASSWORD linkmy_db > backup.sql

# Dengan timestamp
docker exec linkmy_mysql mysqldump -u linkmy_user -pPASSWORD linkmy_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Import dari file SQL
docker exec -i linkmy_mysql mysql -u linkmy_user -pPASSWORD linkmy_db < backup.sql
```

### Access MySQL

```bash
# Masuk ke MySQL CLI
docker exec -it linkmy_mysql mysql -u linkmy_user -p

# Execute query langsung
docker exec linkmy_mysql mysql -u linkmy_user -pPASSWORD linkmy_db -e "SELECT COUNT(*) FROM users;"
```

---

## 🔥 Troubleshooting Fast Fix

### Website tidak bisa diakses

```bash
# 1. Cek container status
docker-compose ps

# 2. Cek logs
docker-compose logs web

# 3. Restart web container
docker-compose restart web

# 4. Jika masih error, rebuild
docker-compose down && docker-compose up -d --build
```

### Database connection error

```bash
# 1. Cek MySQL container
docker-compose ps db

# 2. Cek logs MySQL
docker-compose logs db

# 3. Restart MySQL
docker-compose restart db

# 4. Verify .env file
cat .env | grep DB_
```

### Port already in use

```bash
# Cek port yang digunakan
sudo netstat -tulpn | grep :80

# Stop service yang conflict
sudo systemctl stop apache2
sudo systemctl stop nginx

# Atau ubah port di docker-compose.yml
```

### Git pull conflict

```bash
# Option 1: Stash local changes
git stash
git pull origin master
git stash pop

# Option 2: Hard reset (DANGER! local changes hilang)
git fetch origin
git reset --hard origin/master
```

### Disk space penuh

```bash
# Cek disk space
df -h

# Cleanup Docker
docker system prune -a
docker volume prune

# Hapus old logs
sudo journalctl --vacuum-size=100M

# Hapus old backups
rm -f backup_*.sql
```

---

## ⚡ One-Liner Quick Commands

### Full Deploy (Pull + Build)

```bash
cd ~/linkmy && git pull origin master && docker-compose down && docker-compose up -d --build && docker-compose ps
```

### Quick Restart

```bash
cd ~/linkmy && docker-compose restart && docker-compose ps
```

### Backup Everything

```bash
cd ~/linkmy && docker exec linkmy_mysql mysqldump -u linkmy_user -pPASSWORD linkmy_db > backup_$(date +%Y%m%d).sql && tar -czf uploads_backup_$(date +%Y%m%d).tar.gz uploads/
```

### View Recent Logs

```bash
cd ~/linkmy && docker-compose logs --tail=50 -f
```

---

## 🎯 Daily Workflow Example

### Morning - Deploy New Features

```bash
# Local
git add .
git commit -m "Add analytics dashboard"
git push origin master

# VPS
ssh user@VPS_IP
cd ~/linkmy
./deploy.sh
exit
```

### Afternoon - Check Logs

```bash
ssh user@VPS_IP
cd ~/linkmy
docker-compose logs --tail=100 web
exit
```

### Evening - Backup

```bash
ssh user@VPS_IP
cd ~/linkmy
docker exec linkmy_mysql mysqldump -u linkmy_user -pPASSWORD linkmy_db > backup_$(date +%Y%m%d).sql
exit
```

---

## 📱 Mobile SSH Apps (Deploy dari HP)

**Android:**

-   Termux
-   JuiceSSH
-   ConnectBot

**iOS:**

-   Terminus
-   Prompt 3
-   Blink Shell

**Deploy dari HP:**

```bash
# Login SSH
ssh user@VPS_IP

# Deploy
cd ~/linkmy && ./deploy.sh
```

---

## 🆘 Emergency Commands

### Website Down - Quick Recovery

```bash
docker-compose restart
```

### Database Corrupted - Restore Backup

```bash
docker exec -i linkmy_mysql mysql -u root -pROOT_PASSWORD linkmy_db < backup_latest.sql
docker-compose restart web
```

### Container Won't Start - Full Reset

```bash
docker-compose down
docker system prune -f
docker-compose up -d --build
```

### Out of Memory - Clean Everything

```bash
docker system prune -a --volumes
docker-compose up -d --build
```

---

## 📊 Monitoring Commands

### Check Resource Usage

```bash
# Container stats
docker stats

# Disk usage
df -h

# Memory usage
free -h

# CPU load
uptime
```

### Check Active Connections

```bash
# Web connections
docker exec linkmy_web netstat -an | grep :80 | wc -l

# MySQL connections
docker exec linkmy_mysql mysql -u root -p -e "SHOW PROCESSLIST;"
```

---

## 🔐 Security Commands

### Update Passwords

```bash
# Edit .env
nano .env

# Restart containers
docker-compose restart
```

### Check Failed Login Attempts

```bash
# View auth logs
docker-compose logs web | grep "Failed"

# View MySQL access logs
docker-compose logs db | grep "Access denied"
```

### Block IP (if needed)

```bash
# Using UFW
sudo ufw deny from MALICIOUS_IP
```

---

**🎉 Simpan cheatsheet ini dan akses kapan saja untuk referensi cepat!**

**Pro Tips:**

-   Bookmark file ini di browser
-   Print dan tempel di meja kerja
-   Buat alias commands di `.bashrc` untuk shortcut
-   Gunakan `history | grep docker` untuk cari command yang pernah dijalankan
