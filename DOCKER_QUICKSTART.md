# 🚀 Panduan Cepat Docker - LinkMy v2.1

Panduan singkat untuk menjalankan LinkMy menggunakan Docker dalam 5 menit!

## 📋 Prasyarat

-   ✅ Docker Desktop terinstall ([Download](https://www.docker.com/products/docker-desktop))
-   ✅ 2GB RAM tersedia
-   ✅ Port 83, 3307, dan 8083 tidak digunakan

## ⚡ Quick Start

### Untuk Windows

1. **Buka PowerShell atau Command Prompt** di folder project

2. **Jalankan script setup:**

    ```cmd
    docker-setup.bat
    ```

3. **Pilih opsi 1** (Build and start containers)

4. **Tunggu 1-2 menit** sambil container di-build dan database di-setup

5. **Buka browser:**
    - Aplikasi: http://localhost:83
    - phpMyAdmin: http://localhost:8083

### Untuk Linux/Mac

1. **Buka Terminal** di folder project

2. **Buat script executable:**

    ```bash
    chmod +x docker-setup.sh
    ```

3. **Jalankan script:**

    ```bash
    ./docker-setup.sh
    ```

4. **Pilih opsi 1** (Build and start containers)

5. **Buka browser:**
    - Aplikasi: http://localhost:83
    - phpMyAdmin: http://localhost:8083

### Manual (Tanpa Script)

```bash
# Start semua services
docker-compose up -d --build

# Lihat logs (optional)
docker-compose logs -f

# Cek status
docker-compose ps
```

## 🔑 Login Pertama Kali

Setelah aplikasi running:

1. **Buka**: http://localhost:83
2. **Klik**: "Register" untuk membuat akun baru
3. **Atau login** dengan akun default (jika ada di database.sql):
    - Username: `admin`
    - Password: `admin123`

## 🗄️ Akses Database

### Via phpMyAdmin

-   **URL**: http://localhost:8083
-   **Server**: db
-   **Username**: linkmy_user
-   **Password**: linkmy_password

### Via MySQL Client

```bash
mysql -h 127.0.0.1 -P 3307 -u linkmy_user -plinkmy_password linkmy_db
```

## 🛠️ Perintah Berguna

### Start/Stop

```bash
# Start containers
docker-compose up -d

# Stop containers (data tetap tersimpan)
docker-compose down

# Stop dan hapus data (HATI-HATI!)
docker-compose down -v
```

### Lihat Logs

```bash
# Semua services
docker-compose logs -f

# Hanya web
docker-compose logs -f web

# Hanya database
docker-compose logs -f db
```

### Restart Services

```bash
# Restart semua
docker-compose restart

# Restart web saja
docker-compose restart web
```

### Akses Shell Container

```bash
# Shell web container
docker exec -it linkmy_web bash

# Shell database container
docker exec -it linkmy_mysql bash

# MySQL CLI langsung
docker exec -it linkmy_mysql mysql -u linkmy_user -plinkmy_password linkmy_db
```

## 🐛 Troubleshooting Cepat

### Error: Port sudah digunakan

**Gejala:** `bind: address already in use`

**Solusi:**

```bash
# Windows - cek port 83
netstat -ano | findstr :83

# Atau ubah port di docker-compose.yml
ports:
  - '84:80'  # Ganti 83 ke 84
```

### Error: Database connection failed

**Solusi:**

```bash
# Restart database container
docker-compose restart db

# Atau rebuild semua
docker-compose down -v
docker-compose up -d --build
```

### Upload file gagal

**Solusi:**

```bash
# Fix permissions
docker exec -it linkmy_web chmod -R 777 /var/www/html/uploads
```

### Database kosong

**Solusi:**

```bash
# Import manual
docker exec -i linkmy_mysql mysql -u root -prootpassword linkmy_db < database.sql

# Jika ada update v2.1
docker exec -i linkmy_mysql mysql -u root -prootpassword linkmy_db < database_update_v2.1.sql
```

## 📊 Verifikasi Setup

Cek apakah semua berjalan dengan baik:

```bash
# Cek container status (semua harus "Up")
docker-compose ps

# Cek logs untuk error
docker-compose logs

# Test web server
curl http://localhost:83

# Test database connection
docker exec linkmy_mysql mysql -u linkmy_user -plinkmy_password -e "SHOW DATABASES;"
```

Output yang benar:

```
NAME                  IMAGE           STATUS        PORTS
linkmy_web            htdocs_web      Up 2 minutes  0.0.0.0:83->80/tcp
linkmy_mysql          mysql:8.0       Up 2 minutes  0.0.0.0:3307->3306/tcp
linkmy_phpmyadmin     phpmyadmin      Up 2 minutes  0.0.0.0:8083->80/tcp
```

## 🎯 Next Steps

Setelah setup berhasil:

1. ✅ **Buat akun** di http://localhost:83
2. ✅ **Login** ke dashboard
3. ✅ **Tambah links** di menu Dashboard
4. ✅ **Customize** appearance di menu Appearance
5. ✅ **Lihat profil** di http://localhost:83/profile.php?u=username

## 📚 Dokumentasi Lengkap

-   **Docker Detail**: [DOCKER.md](DOCKER.md)
-   **Panduan Lengkap**: [README.md](README.md)
-   **Deployment**: [DEPLOYMENT.md](DEPLOYMENT.md)
-   **Features v2.1**: [FEATURES_V2.md](FEATURES_V2.md)

## 🆘 Butuh Bantuan?

**Masalah umum:**

-   Container tidak start → Lihat [Troubleshooting](#-troubleshooting-cepat)
-   Database error → Check logs dengan `docker-compose logs db`
-   Web tidak bisa diakses → Pastikan port 83 tidak digunakan

**Dokumentasi:**

-   Baca file [DOCKER.md](DOCKER.md) untuk panduan lengkap
-   Cek [README.md](README.md) untuk fitur aplikasi

**Reset total:**

```bash
# Hapus semua dan mulai dari awal
docker-compose down -v
docker-compose up -d --build
```

---

**🎉 Selamat! LinkMy sudah berjalan di Docker dengan port 83!**

**Akses sekarang**: http://localhost:83
