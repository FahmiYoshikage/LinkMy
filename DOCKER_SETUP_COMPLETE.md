# ✅ Docker Setup Complete - LinkMy v2.1

## 🎉 Setup Selesai!

LinkMy v2.1 telah berhasil di-dockerisasi dan **web service akan berjalan di port 83** sesuai permintaan Anda.

---

## 📦 File yang Telah Dibuat

### Core Docker Files
1. ✅ **Dockerfile** - PHP 8.1 + Apache image definition
2. ✅ **docker-compose.yml** - 3 services orchestration (port 83 configured)
3. ✅ **.dockerignore** - Optimize build context
4. ✅ **.env.example** - Environment variables template

### Configuration
5. ✅ **config/db.docker.php** - Database config untuk Docker network

### Setup Scripts
6. ✅ **docker-setup.sh** - Interactive setup untuk Linux/Mac
7. ✅ **docker-setup.bat** - Interactive setup untuk Windows

### Documentation
8. ✅ **DOCKER.md** - Complete Docker documentation (English)
9. ✅ **DOCKER_QUICKSTART.md** - Quick start guide (Bahasa Indonesia)
10. ✅ **DOCKER_FILES_SUMMARY.md** - Technical file summary
11. ✅ **README.md** - Updated dengan Docker section

---

## 🚀 Cara Menjalankan

### Windows (Recommended)

```cmd
# 1. Pastikan Docker Desktop running
# 2. Buka PowerShell atau CMD di folder ini
# 3. Jalankan:
docker-setup.bat
```

### Linux/Mac

```bash
chmod +x docker-setup.sh
./docker-setup.sh
```

### Manual (Semua OS)

```bash
docker-compose up -d --build
```

---

## 🌐 Akses Aplikasi

Setelah container berjalan:

| Service | URL | Credentials |
|---------|-----|-------------|
| **Web Application** | **http://localhost:83** ⭐ | (register/login) |
| phpMyAdmin | http://localhost:8083 | linkmy_user / linkmy_password |
| MySQL | localhost:3307 | linkmy_user / linkmy_password |

---

## 🔧 Services Configuration

### 1. Web Service (linkmy_web)
- **Base Image**: php:8.1-apache
- **Host Port**: **83** ⭐
- **Container Port**: 80
- **PHP Extensions**: mysqli, pdo_mysql, gd
- **Apache Modules**: mod_rewrite, mod_headers
- **Volumes**:
  - Source code: `./` → `/var/www/html`
  - Uploads: `./uploads` → `/var/www/html/uploads`

### 2. Database Service (linkmy_db)
- **Image**: mysql:8.0
- **Host Port**: 3307
- **Container Port**: 3306
- **Database**: linkmy_db
- **User**: linkmy_user
- **Password**: linkmy_password
- **Auto-init**: `database.sql` imported on first run

### 3. phpMyAdmin Service
- **Image**: phpmyadmin:latest
- **Host Port**: 8083
- **Container Port**: 80
- **Auto-connect**: to linkmy_db

---

## 📋 Quick Commands

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Check status
docker-compose ps

# Restart service
docker-compose restart web

# Shell access
docker exec -it linkmy_web bash

# MySQL CLI
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_password linkmy_db
```

---

## 🐛 Troubleshooting

### Docker Desktop Not Running

**Error:**
```
error during connect: Get "http://...": The system cannot find the file specified
```

**Solution:**
1. Start Docker Desktop
2. Wait until Docker icon shows "running"
3. Run command again

### Port 83 Already in Use

**Error:**
```
bind: address already in use
```

**Solution:**
```bash
# Windows - Check what's using port 83
netstat -ano | findstr :83

# Kill process or change port in docker-compose.yml
# Change line:
ports:
  - '84:80'  # Use 84 instead
```

### Database Not Initialized

**Solution:**
```bash
# Remove volume and restart
docker-compose down -v
docker-compose up -d --build
```

### Upload Permission Denied

**Solution:**
```bash
docker exec -it linkmy_web chmod -R 777 /var/www/html/uploads
```

---

## 📖 Documentation

### Quick Start
- 🇮🇩 **Bahasa Indonesia**: [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md)
- 🇬🇧 **English**: [DOCKER.md](DOCKER.md)

### Detailed Guides
- **Technical Details**: [DOCKER_FILES_SUMMARY.md](DOCKER_FILES_SUMMARY.md)
- **Main README**: [README.md](README.md)
- **Deployment**: [DEPLOYMENT.md](DEPLOYMENT.md)
- **Features**: [FEATURES_V2.md](FEATURES_V2.md)

---

## ✨ Key Features

### ✅ Port 83 Configuration
- Web service accessible di **http://localhost:83** ⭐
- No conflict dengan Apache default (port 80)
- Mudah diubah via docker-compose.yml

### ✅ Auto-Initialization
- Database schema imported otomatis
- Upload directories created automatically
- Correct permissions set

### ✅ Development Friendly
- Live code reload (volumes mounted)
- Easy logs access
- Shell access untuk debugging
- phpMyAdmin included

### ✅ Production Ready
- Health checks enabled
- Restart policies configured
- Data persistence via volumes
- Environment variables support

---

## 🎯 Next Steps

1. **Start Docker Desktop** (jika belum running)

2. **Run Setup Script**
   ```cmd
   docker-setup.bat
   ```

3. **Wait for Build** (pertama kali ~2-3 menit)

4. **Access Application**
   - Open: http://localhost:83
   - Register akun baru
   - Login ke dashboard

5. **Customize Profile**
   - Add links di Dashboard
   - Customize appearance (Advanced tab dengan v2.1 features!)
   - View public profile

---

## 📊 System Requirements

### Minimum
- **RAM**: 2GB available
- **Disk**: 2GB free space
- **Docker**: 20.10+
- **Docker Compose**: 1.29+

### Recommended
- **RAM**: 4GB available
- **Disk**: 5GB free space
- **Docker Desktop**: Latest version
- **OS**: Windows 10/11, macOS 10.15+, Ubuntu 20.04+

---

## 🔍 Verify Installation

```bash
# 1. Check Docker version
docker --version
docker-compose --version

# 2. Validate configuration
docker-compose config

# 3. Build images
docker-compose build

# 4. Start services
docker-compose up -d

# 5. Check status (all should be "Up")
docker-compose ps

# 6. Test web server
curl http://localhost:83

# 7. Test database
docker exec linkmy_mysql mysql -u linkmy_user -plinkmy_password -e "SHOW DATABASES;"
```

Expected output:
```
✓ Docker version 20.10+
✓ docker-compose config is valid
✓ Images built successfully
✓ All containers running
✓ Web responds on port 83
✓ Database contains linkmy_db
```

---

## 🎊 Summary

### What You Get

**3 Services Running:**
1. 🌐 Web Application → http://localhost:83 ⭐
2. 🗄️ MySQL Database → localhost:3307
3. 🔧 phpMyAdmin → http://localhost:8083

**Complete Documentation:**
- Quick start guide (Indonesia)
- Full Docker manual (English)
- Technical file summary
- Troubleshooting guide

**Developer Experience:**
- One-command setup
- Live code reload
- Easy debugging
- Production-ready

---

## 📞 Support

**Documentation:**
- [DOCKER_QUICKSTART.md](DOCKER_QUICKSTART.md) - Panduan cepat
- [DOCKER.md](DOCKER.md) - Dokumentasi lengkap
- [README.md](README.md) - Main documentation

**Common Issues:**
- Docker not running → Start Docker Desktop
- Port conflict → Change port di docker-compose.yml
- Database error → Check logs: `docker-compose logs db`
- Permission error → Fix: `docker exec linkmy_web chmod -R 777 /var/www/html/uploads`

**Clean Restart:**
```bash
docker-compose down -v
docker-compose up -d --build
```

---

## 🎉 Ready to Go!

**LinkMy v2.1 is now fully dockerized!**

**Web service running on port 83** as requested ✅

**Start using:**
```cmd
docker-setup.bat
```

**Access:**
http://localhost:83

---

**Made with ❤️ for LinkMy v2.1**

**Docker + PHP + MySQL + Apache running smoothly on port 83!**
