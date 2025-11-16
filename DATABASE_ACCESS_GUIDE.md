# 🗄️ Quick Guide: Database Access di Docker

## 📊 3 Cara Akses Database dalam Docker

### 1️⃣ Via phpMyAdmin (Web Interface) - Paling Mudah ✅

**URL:**

```
http://YOUR_VPS_IP:8083
```

**Login Credentials:**

```
Server: db
Username: linkmy_user  (atau root)
Password: [dari file .env Anda]
```

**Keuntungan:**

-   ✅ Interface grafis, mudah digunakan
-   ✅ Bisa import/export SQL via browser
-   ✅ Visualisasi table dan data

**Catatan Production:**

-   ⚠️ Di production, sebaiknya **disable phpMyAdmin** atau proteksi dengan password tambahan
-   Edit `docker-compose.yml` dan comment/hapus section `phpmyadmin`

---

### 2️⃣ Via MySQL Command Line (di dalam Container)

#### Masuk ke MySQL Container:

```bash
docker exec -it linkmy_mysql bash
```

#### Login ke MySQL:

```bash
mysql -u linkmy_user -p
# Masukkan password dari .env
```

#### Gunakan Database:

```sql
USE linkmy_db;

-- Lihat semua tabel
SHOW TABLES;

-- Query data
SELECT * FROM users;
SELECT * FROM links ORDER BY created_at DESC LIMIT 10;

-- Update data
UPDATE appearance SET theme_name = 'gradient' WHERE user_id = 1;

-- Exit
EXIT;
```

#### Shortcut (langsung query tanpa masuk bash):

```bash
# Direct MySQL access
docker exec -it linkmy_mysql mysql -u linkmy_user -p linkmy_db

# Execute single query
docker exec -it linkmy_mysql mysql -u linkmy_user -pYOUR_PASSWORD linkmy_db -e "SELECT COUNT(*) FROM users;"
```

---

### 3️⃣ Via Remote MySQL Client (dari PC Lokal)

#### Tools yang bisa dipakai:

-   **MySQL Workbench** (https://www.mysql.com/products/workbench/)
-   **DBeaver** (https://dbeaver.io/)
-   **HeidiSQL** (https://www.heidisql.com/)
-   **DataGrip** (https://www.jetbrains.com/datagrip/)

#### Connection Settings:

```
Host: YOUR_VPS_IP
Port: 3307  (bukan 3306! lihat docker-compose.yml)
Username: linkmy_user
Password: [dari .env]
Database: linkmy_db
```

#### Allow Remote Access (jika perlu):

```bash
# Di VPS, allow port MySQL di firewall
sudo ufw allow 3307/tcp

# Restart MySQL container
docker-compose restart db
```

**⚠️ Security Warning:**

-   **TIDAK disarankan** untuk production environment
-   Jika perlu, gunakan SSH tunnel sebagai gantinya

#### SSH Tunnel (Lebih Aman):

```bash
# Dari local Windows PowerShell
ssh -L 3307:localhost:3307 user@YOUR_VPS_IP

# Kemudian connect MySQL client ke:
# Host: localhost
# Port: 3307
```

---

## 🔄 Operasi Database Umum

### Export Database (Backup)

```bash
# Method 1: Via docker exec (dari VPS)
docker exec linkmy_mysql mysqldump -u linkmy_user -pYOUR_PASSWORD linkmy_db > backup_$(date +%Y%m%d).sql

# Method 2: Via MySQL client (dari dalam container)
docker exec -it linkmy_mysql bash
mysqldump -u linkmy_user -p linkmy_db > /tmp/backup.sql
exit

# Copy backup ke host
docker cp linkmy_mysql:/tmp/backup.sql ./backup.sql
```

### Import Database

```bash
# Method 1: Via docker exec
docker exec -i linkmy_mysql mysql -u linkmy_user -pYOUR_PASSWORD linkmy_db < database.sql

# Method 2: Copy file ke container lalu import
docker cp database.sql linkmy_mysql:/tmp/
docker exec -it linkmy_mysql mysql -u linkmy_user -p linkmy_db -e "source /tmp/database.sql"
```

### Reset Database (Fresh Install)

```bash
# Stop containers
docker-compose down

# Hapus MySQL volume (DANGER: semua data hilang!)
docker volume rm linkmy_mysql_data

# Rebuild (database.sql akan otomatis ter-import)
docker-compose up -d
```

---

## 🔧 Troubleshooting

### Issue: "Access denied for user"

**Solution:**

```bash
# Cek .env file
cat .env | grep DB_

# Pastikan password benar
# Restart MySQL container
docker-compose restart db
```

### Issue: "Can't connect to MySQL server"

**Solution:**

```bash
# Cek status container
docker-compose ps

# Jika MySQL not running
docker-compose up -d db

# Lihat logs
docker-compose logs db
```

### Issue: "Table doesn't exist"

**Solution:**

```bash
# Import database.sql
docker exec -i linkmy_mysql mysql -u root -pROOT_PASSWORD linkmy_db < database.sql

# Atau via phpMyAdmin:
# 1. Login ke phpMyAdmin
# 2. Pilih database linkmy_db
# 3. Tab "Import"
# 4. Upload database.sql
```

### Issue: Port 3307 already in use

**Solution:**

```bash
# Cek apa yang pakai port 3307
sudo netstat -tulpn | grep 3307

# Stop service yang conflict atau ubah port di docker-compose.yml
# Edit docker-compose.yml:
# ports:
#   - '3308:3306'  # Ubah ke port lain
```

---

## 📋 Database Structure Reference

### Tables dalam LinkMy v2.1:

```
1. users                 - User accounts
2. appearance           - Profil customization (v2.0 + v2.1)
3. links                - User links dengan categories
4. link_categories      - Custom link categories
5. link_analytics       - Click tracking (untuk charts)
6. email_verifications  - Email verification tokens
7. password_resets      - Password reset tokens
8. gradient_presets     - 24 gradient themes
9. social_icons         - 19 social media icons
```

### Useful Queries:

```sql
-- Statistik user
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as verified_users FROM users WHERE email_verified = 1;

-- Statistik links
SELECT COUNT(*) as total_links FROM links;
SELECT SUM(click_count) as total_clicks FROM links;

-- Top 10 most clicked links
SELECT title, click_count, url FROM links ORDER BY click_count DESC LIMIT 10;

-- Analytics last 7 days
SELECT DATE(clicked_at) as date, COUNT(*) as clicks
FROM link_analytics
WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(clicked_at);
```

---

## 🎯 Best Practices

✅ **DO:**

-   Backup database secara regular (minimal 1x/hari)
-   Gunakan strong passwords untuk database
-   Monitor disk space untuk MySQL data volume
-   Test backup dengan restore berkala

❌ **DON'T:**

-   Expose MySQL port ke internet tanpa proteksi
-   Commit `.env` file ke Git
-   Gunakan default passwords di production
-   Jalankan phpMyAdmin di production tanpa proteksi

---

## 📚 References

-   **MySQL Docker Image**: https://hub.docker.com/_/mysql
-   **phpMyAdmin**: https://www.phpmyadmin.net/
-   **MySQL Dump/Restore**: https://dev.mysql.com/doc/refman/8.0/en/mysqldump.html

---

**💡 Pro Tip:**
Untuk development, gunakan phpMyAdmin. Untuk production, gunakan MySQL command line atau remote client dengan SSH tunnel.
