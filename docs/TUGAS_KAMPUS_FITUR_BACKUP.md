# TUGAS KAMPUS - Dokumentasi 12 Fitur Wajib + Fitur Tambahan

## Informasi Project
**Nama Project:** LinkMy - Bio Link Manager  
**Teknologi:** PHP 8.1, MySQL 8.0, Bootstrap 5.3.8, Highcharts  
**Deployment:** Docker + VPS Ubuntu  
**Tanggal:** November 2024

---

## A. 12 FITUR WAJIB TUGAS KAMPUS

### 1. Penggunaan HTML, PHP, dan Database 

**Lokasi Implementasi:** Semua file .php di project

**File Utama:**
- index.php - Landing page dengan HTML5 semantic
- login.php - Form login dengan PHP processing
- 
egister.php - Form registrasi + validation
- profile.php - Public profile page (HTML + PHP dynamic)
- dmin/dashboard.php - Admin panel dengan chart
- config/db.php - Database connection handler

**Cara Kerja Teknikal:**

**HTML Structure:**
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkMy</title>
</head>
<body>
    <!-- Semantic HTML5 tags -->
    <header>, <nav>, <main>, <section>, <article>, <footer>
</body>
</html>
```

**PHP Database Connection:**
```php
// config/db.php
$servername = "localhost";
$username = "root";
$password = "";
$database = "linkmy_db";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
```

**PHP-HTML Integration:**
```php
<!-- profile.php -->
<?php
require_once 'config/db.php';
$username = $_GET['username'];
$user_data = get_single_row("SELECT * FROM v_public_page_data WHERE username = ?", [$username], 's');
?>
<h1><?php echo htmlspecialchars($user_data['display_name']); ?></h1>
<p><?php echo htmlspecialchars($user_data['bio']); ?></p>
```

---

### 2. Penggunaan CSS 

**Lokasi Implementasi:**
- ssets/css/admin.css - Admin panel styling (284 lines)
- ssets/css/public.css - Public profile styling
- Inline CSS di profile.php - Dynamic styling dari database

**Cara Kerja Teknikal:**

**External CSS:**
```css
/* assets/css/admin.css */
.navbar-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar-custom .nav-link {
    color: #ffffff !important;
    font-weight: 500;
    transition: all 0.3s ease;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
```

**Dynamic CSS dari Database:**
```php
<!-- profile.php - Dynamic styling -->
<style>
    :root {
        --bg-color: <?php echo $user_data['background_color']; ?>;
        --button-style: <?php echo $user_data['button_style']; ?>;
    }
    
    .profile-container {
        background: var(--bg-color);
    }
    
    <?php if ($user_data['background_type'] === 'image'): ?>
    .outer-background {
        background-image: url('<?php echo $user_data['background_image']; ?>');
        background-size: cover;
    }
    <?php endif; ?>
</style>
```

**Responsive CSS:**
```css
/* Mobile-first responsive design */
@media (max-width: 768px) {
    .profile-container {
        max-width: 100%;
        padding: 20px 15px;
    }
    
    .link-button {
        font-size: 14px;
    }
}
```

---

### 3. Penggunaan Chart 

**Lokasi Implementasi:**
- dmin/dashboard.php lines 783-1100
- Library: **Highcharts** dari CDN

**Cara Kerja Teknikal:**

**Chart 1: Click Trends (Area Chart)**
```php
<!-- Load Highcharts CDN -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>

<div id="clickTrendsChart" style="height: 400px;"></div>

<script>
Highcharts.chart('clickTrendsChart', {
    chart: {
        type: 'areaspline'
    },
    title: {
        text: 'Click Trends - Last 7 Days',
        align: 'left'
    },
    xAxis: {
        categories: [
            <?php 
            foreach ($dates_range as $date => $clicks) {
                echo "'" . date('d M', strtotime($date)) . "',";
            }
            ?>
        ]
    },
    yAxis: {
        title: { text: 'Total Clicks' }
    },
    series: [{
        name: 'Clicks',
        data: [<?php echo implode(',', array_values($dates_range)); ?>],
        color: '#667eea'
    }],
    credits: { enabled: false }
});
</script>
```

**Chart 2: Traffic by Location (Donut Chart)**
```php
<div id="trafficSourcesChart" style="height: 400px;"></div>

<script>
Highcharts.chart('trafficSourcesChart', {
    chart: {
        type: 'pie'
    },
    title: {
        text: 'Traffic by Location',
        align: 'left'
    },
    subtitle: {
        text: 'Geographic distribution of your visitors'
    },
    plotOptions: {
        pie: {
            innerSize: '50%', // Donut style
            depth: 45,
            dataLabels: {
                enabled: true,
                format: '{point.name}: {point.percentage:.1f}%'
            }
        }
    },
    series: [{
        name: 'Traffic Share',
        colorByPoint: true,
        data: [
            <?php 
            foreach ($click_by_location as $loc) {
                echo "{ name: '" . htmlspecialchars($loc['location']) . "', ";
                echo "y: " . intval($loc['clicks']) . " },";
            }
            ?>
        ]
    }]
});
</script>
```

**Query Data untuk Chart:**
```php
// Click Trends - Last 7 days including today
$daily_clicks = get_all_rows(
    "SELECT DATE(clicked_at) as date, COUNT(*) as clicks
     FROM link_analytics la
     INNER JOIN links l ON la.link_id = l.link_id
     WHERE l.user_id = ? 
       AND DATE(clicked_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
       AND DATE(clicked_at) <= CURDATE()
     GROUP BY DATE(clicked_at)
     ORDER BY date ASC",
    [$current_user_id], 'i'
);

// Traffic by Location - Top 10 countries/cities
$click_by_location = get_all_rows(
    "SELECT 
        CASE 
            WHEN city IS NOT NULL AND city != '' THEN CONCAT(city, ', ', COALESCE(country, 'Unknown'))
            ELSE COALESCE(NULLIF(country, ''), 'Unknown')
        END as location,
        COUNT(*) as clicks
     FROM link_analytics la
     INNER JOIN links l ON la.link_id = l.link_id
     WHERE l.user_id = ?
     GROUP BY location
     ORDER BY clicks DESC
     LIMIT 10",
    [$current_user_id], 'i'
);
```

---

### 4. Penggunaan Table Relasi 

**Lokasi Implementasi:** Database linkmy_db

**Cara Kerja Teknikal:**

**Relasi 1: users  appearance (One-to-One)**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    page_slug VARCHAR(50) UNIQUE NOT NULL,
    email_verified TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE appearance (
    appearance_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    profile_picture VARCHAR(255),
    display_name VARCHAR(100),
    bio TEXT,
    background_type ENUM('color', 'image') DEFAULT 'color',
    background_color VARCHAR(50) DEFAULT '#ffffff',
    outer_background_type ENUM('color', 'image', 'gradient') DEFAULT 'gradient',
    outer_background_color VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Relasi 2: users  links (One-to-Many)**
```sql
CREATE TABLE links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon_class VARCHAR(50),
    category_id INT,
    order_index INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    click_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES link_categories(category_id) ON DELETE SET NULL
);
```

**Relasi 3: links  link_analytics (One-to-Many)**
```sql
CREATE TABLE link_analytics (
    analytics_id INT PRIMARY KEY AUTO_INCREMENT,
    link_id INT NOT NULL,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    referrer VARCHAR(255),
    user_agent TEXT,
    ip_address VARCHAR(45),
    country VARCHAR(50),
    city VARCHAR(100),
    FOREIGN KEY (link_id) REFERENCES links(link_id) ON DELETE CASCADE
);
```

**Relasi 4: users  link_categories  links (Many-to-Many via category)**
```sql
CREATE TABLE link_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    color_code VARCHAR(7) DEFAULT '#6c757d',
    order_index INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Query dengan JOIN
SELECT l.*, lc.category_name, lc.color_code
FROM links l
LEFT JOIN link_categories lc ON l.category_id = lc.category_id
WHERE l.user_id = ? AND l.is_active = 1
ORDER BY lc.order_index, l.order_index;
```

**Diagram Relasi:**
`
users (1)  (1) appearance
  
   (1)  (*) links
                    
                     (1)  (*) link_analytics
  
   (1)  (*) link_categories
                       
                        (1)  (*) links
`

---

### 5. Implementasi View Database 

**Lokasi Implementasi:**
- database_update_view_verified.sql
- Digunakan di: profile.php line 14

**Cara Kerja Teknikal:**

**View 1: v_public_page_data**
```sql
-- Menggabungkan data users + appearance untuk public profile
DROP VIEW IF EXISTS v_public_page_data;

CREATE VIEW v_public_page_data AS
SELECT 
    u.user_id,
    u.username,
    u.page_slug,
    u.is_verified,
    u.created_at,
    a.appearance_id,
    a.profile_picture,
    a.display_name,
    a.bio,
    a.background_type,
    a.background_color,
    a.background_image,
    a.theme_style,
    a.button_style,
    a.font_style,
    a.outer_background_type,
    a.outer_background_color,
    a.outer_background_image,
    a.social_instagram,
    a.social_twitter,
    a.social_facebook
FROM users u
INNER JOIN appearance a ON u.user_id = a.user_id
WHERE u.email_verified = 1;
```

**Penggunaan View di PHP:**
```php
// profile.php
$page_slug = $_GET['username'] ?? '';

// Query menggunakan VIEW (tidak perlu JOIN manual)
$user_data = get_single_row(
    "SELECT * FROM v_public_page_data WHERE page_slug = ?",
    [$page_slug],
    's'
);

if (!$user_data) {
    die('Profile not found');
}

// Langsung akses semua kolom dari 2 tabel
echo $user_data['display_name']; // dari appearance
echo $user_data['username']; // dari users
echo $user_data['is_verified']; // dari users
```

**Keuntungan View:**
1. **Query lebih simple** - Tidak perlu JOIN berulang kali
2. **Konsistensi** - Logic JOIN terpusat di VIEW
3. **Security** - Bisa filter data yang boleh diakses (WHERE email_verified = 1)
4. **Performance** - MySQL optimize query VIEW

---

### 6. Implementasi Insert Database 

**Lokasi Implementasi:**
- 
egister.php - Insert user baru
- dmin/dashboard.php - Insert link baru (AJAX)
- 
edirect.php - Insert analytics tracking

**Cara Kerja Teknikal:**

**Insert 1: User Registration**
```php
// register.php
if ($_POST['action'] === 'register') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // Hash password dengan bcrypt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Generate unique page slug
    $page_slug = strtolower($username) . rand(100, 999);
    
    // Insert user (dengan prepared statement)
    $query = "INSERT INTO users (username, email, password, page_slug, email_verified) 
                VALUES (?, ?, ?, ?, 0)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $hashed_password, $page_slug);
    
    if (mysqli_stmt_execute($stmt)) {
        $user_id = mysqli_insert_id($conn);
        
        // Insert default appearance
        $query2 = "INSERT INTO appearance (user_id, display_name, background_color) 
                     VALUES (?, ?, '#ffffff')";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, 'is', $user_id, $username);
        mysqli_stmt_execute($stmt2);
        
        // Redirect to verify email
        header('Location: verify-otp.php');
    }
}
```

**Insert 2: Add Link (AJAX)**
```php
// admin/dashboard.php
if ($_POST['action'] === 'add_link') {
    $title = sanitize_input($_POST['title']);
    $url = sanitize_input($_POST['url']);
    $icon_class = sanitize_input($_POST['icon_class']);
    $category_id = intval($_POST['category_id']);
    
    // Auto-add https
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
    
    // Get next order index
    $max_order = get_single_row(
        "SELECT MAX(order_index) as max FROM links WHERE user_id = ?",
        [$current_user_id], 'i'
    );
    $order_index = ($max_order['max'] ?? 0) + 1;
    
    // Insert link
    $query = "INSERT INTO links (user_id, title, url, icon_class, category_id, order_index, is_active, click_count) 
                VALUES (?, ?, ?, ?, ?, ?, 1, 0)";
    execute_query($query, [$current_user_id, $	itle, $url, $icon_class, $category_id, $order_index], 'isssii');
    
    echo json_encode(['success' => true, 'link_id' => mysqli_insert_id($conn)]);
}
```

**Insert 3: Analytics Tracking (Realtime)**
```php
// redirect.php
$link_id = intval($_GET['id']);
$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip_address = $_SERVER['REMOTE_ADDR'];

// Get geolocation from IP
$geo_data = @file_get_contents("http://ip-api.com/json/{$ip_address}?fields=status,country,city");
$geo = json_decode($geo_data, true);
$country = $geo['country'] ?? 'Unknown';
$city = $geo['city'] ?? '';

// Insert analytics
$query = "INSERT INTO link_analytics (link_id, referrer, user_agent, ip_address, country, city, clicked_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'isssss', $link_id, $
eferrer, $user_agent, $ip_address, $country, $city);
mysqli_stmt_execute($stmt);
```

---

### 7. Implementasi Update Database 

**Lokasi Implementasi:**
- dmin/appearance.php - Update profile/background
- dmin/dashboard.php - Update link (AJAX)
- dmin/settings.php - Update account info

**Cara Kerja Teknikal:**

**Update 1: Profile Appearance**
```php
// admin/appearance.php
if ($_POST['action'] === 'update_appearance') {
    $display_name = sanitize_input($_POST['display_name']);
    $io = sanitize_input($_POST['bio']);
    $ackground_type = $_POST['background_type'];
    $ackground_color = sanitize_input($_POST['background_color']);
    
    $query = "UPDATE appearance 
                SET display_name = ?, 
                    bio = ?, 
                    background_type = ?, 
                    background_color = ?
                WHERE user_id = ?";
    
    execute_query($query, 
        [$display_name, $io, $ackground_type, $ackground_color, $current_user_id],
        'ssssi'
    );
    
    $_SESSION['success'] = 'Appearance updated successfully!';
    header('Location: appearance.php');
}
```

**Update 2: Edit Link**
```php
// admin/dashboard.php (AJAX endpoint)
if ($_POST['action'] === 'update_link') {
    $link_id = intval($_POST['link_id']);
    $	itle = sanitize_input($_POST['title']);
    $url = sanitize_input($_POST['url']);
    $icon_class = sanitize_input($_POST['icon_class']);
    $category_id = intval($_POST['category_id']);
    
    // Validate ownership
    $check = get_single_row(
        "SELECT user_id FROM links WHERE link_id = ?",
        [$link_id], 'i'
    );
    
    if ($check['user_id'] !== $current_user_id) {
        die('Unauthorized');
    }
    
    $query = "UPDATE links 
                SET title = ?, url = ?, icon_class = ?, category_id = ?
                WHERE link_id = ? AND user_id = ?";
    
    execute_query($query, [$	itle, $url, $icon_class, $category_id, $link_id, $current_user_id], 'sssiii');
    
    echo json_encode(['success' => true]);
}
```

**Update 3: Toggle Link Status**
```php
// admin/dashboard.php
if ($_POST['action'] === 'toggle_link_status') {
    $link_id = intval($_POST['link_id']);
    $is_active = intval($_POST['is_active']); // 0 or 1
    
    $query = "UPDATE links SET is_active = ? WHERE link_id = ? AND user_id = ?";
    execute_query($query, [$is_active, $link_id, $current_user_id], 'iii');
    
    echo json_encode(['success' => true]);
}
```

**Update 4: Click Counter (Auto Increment)**
```php
// redirect.php
// Update click count setiap link diklik
$update_query = "UPDATE links SET click_count = click_count + 1 WHERE link_id = ?";
$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, 'i', $link_id);
mysqli_stmt_execute($stmt);
```

---

### 8. Implementasi Delete Database 

**Lokasi Implementasi:**
- dmin/dashboard.php - Delete link (AJAX)
- dmin/categories.php - Delete category
- dmin/settings.php - Delete account (CASCADE)

**Cara Kerja Teknikal:**

**Delete 1: Delete Link**
```php
// admin/dashboard.php (AJAX endpoint)
if ($_POST['action'] === 'delete_link') {
    $link_id = intval($_POST['link_id']);
    
    // Validate ownership
    $check = get_single_row(
        "SELECT user_id FROM links WHERE link_id = ?",
        [$link_id], 'i'
    );
    
    if ($check['user_id'] !== $current_user_id) {
        die('Unauthorized');
    }
    
    // Delete link (CASCADE akan auto-delete analytics)
    $query = "DELETE FROM links WHERE link_id = ? AND user_id = ?";
    execute_query($query, [$link_id, $current_user_id], 'ii');
    
    echo json_encode(['success' => true, 'message' => 'Link deleted']);
}
```

**Delete 2: Delete Category**
```php
// admin/categories.php
if ($_POST['action'] === 'delete_category') {
    $category_id = intval($_POST['category_id']);
    
    // Set links in this category to NULL (ON DELETE SET NULL)
    $query = "DELETE FROM link_categories WHERE category_id = ? AND user_id = ?";
    execute_query($query, [$category_id, $current_user_id], 'ii');
    
    // Links dengan category_id ini akan auto set ke NULL
    $_SESSION['success'] = 'Category deleted';
    header('Location: categories.php');
}
```

**Delete 3: Delete Account (CASCADE)**
```php
// admin/settings.php
if ($_POST['action'] === 'delete_account' && isset($_POST['confirm'])) {
    $password = $_POST['password'];
    
    // Verify password
    $user = get_single_row("SELECT password FROM users WHERE user_id = ?", [$current_user_id], 'i');
    
    if (password_verify($password, $user['password'])) {
        // Delete user (CASCADE akan delete semua data terkait)
        $query = "DELETE FROM users WHERE user_id = ?";
        execute_query($query, [$current_user_id], 'i');
        
        // Logout
        session_destroy();
        header('Location: ../index.php?deleted=1');
    }
}
```

**Cascade Delete Behavior:**
```sql
-- Ketika user dihapus, otomatis hapus:
-- 1. appearance (ON DELETE CASCADE)
-- 2. links (ON DELETE CASCADE)
-- 3. link_categories (ON DELETE CASCADE)
-- 4. link_analytics (CASCADE via links)

ALTER TABLE appearance
ADD CONSTRAINT fk_appearance_user 
FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

ALTER TABLE links
ADD CONSTRAINT fk_links_user
FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;

ALTER TABLE link_analytics
ADD CONSTRAINT fk_analytics_link
FOREIGN KEY (link_id) REFERENCES links(link_id) ON DELETE CASCADE;
```

---

### 9. Hosting Website 

**Lokasi Implementasi:** VPS Ubuntu dengan Docker

**Cara Kerja Teknikal:**

**Setup 1: Docker Compose**
```yaml
# docker-compose.yml
version: '3.8'

services:
  web:
    build: .
    container_name: linkmy_web
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./uploads:/var/www/html/uploads
    environment:
      - MYSQL_HOST=db
      - MYSQL_DATABASE=linkmy_db
      - MYSQL_USER=root
      - MYSQL_PASSWORD=your_password
    depends_on:
      - db
    networks:
      - linkmy_network

  db:
    image: mysql:8.0
    container_name: linkmy_db
    environment:
      MYSQL_ROOT_PASSWORD: your_password
      MYSQL_DATABASE: linkmy_db
    volumes:
      - db_data:/var/lib/mysql
      - ./linkmy_db.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
    networks:
      - linkmy_network

volumes:
  db_data:

networks:
  linkmy_network:
    driver: bridge
```

**Setup 2: Dockerfile**
```dockerfile
# Dockerfile
FROM php:8.1-apache

# Install extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80 443
```

**Setup 3: Apache Config**
```apache
# apache-config.conf
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

**Setup 4: .htaccess (URL Rewrite)**
```apache
# .htaccess
RewriteEngine On

# Redirect www to non-www
RewriteCond %{HTTP_HOST} ^www\.(.*)$ [NC]
RewriteRule ^(.*)$ http://%1/ [R=301,L]

# Clean URL untuk profile
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9_-]+)$ profile.php?username= [L,QSA]
```

**Deployment Steps:**
```bash
# 1. Clone repo ke VPS
git clone https://github.com/FahmiYoshikage/LinkMy.git
cd LinkMy

# 2. Setup environment
cp .env.example .env
nano .env  # Edit database credentials

# 3. Build & run Docker
docker-compose up -d --build

# 4. Import database
docker exec -i linkmy_db mysql -uroot -ppassword linkmy_db < linkmy_db.sql

# 5. Set permissions
docker exec linkmy_web chown -R www-data:www-data /var/www/html/uploads

# 6. Check status
docker-compose ps
```

**Domain & SSL:**
```bash
# Install Certbot untuk SSL
sudo apt install certbot python3-certbot-apache

# Generate SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

---

### 10. Upload Foto 

**Lokasi Implementasi:**
- dmin/appearance.php - Profile picture & background upload
- uploads/ directory structure

**Cara Kerja Teknikal:**

**Upload 1: Profile Picture**
```php
// admin/appearance.php
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
    $ile = $_FILES['profile_picture'];
    
    // 1. Validate file type
    $llowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($info, $ile['tmp_name']);
    finfo_close($info);
    
    if (!in_array($mime_type, $llowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.';
        exit;
    }
    
    // 2. Validate file size (max 2MB)
    if ($ile['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = 'File too large. Max 2MB.';
        exit;
    }
    
    // 3. Generate unique filename
    $ext = pathinfo($ile['name'], PATHINFO_EXTENSION);
    $ilename = 'profile_' . $current_user_id . '_' . time() . '.' . $ext;
    $upload_dir = '../uploads/profile_pics/';
    $upload_path = $upload_dir . $ilename;
    
    // 4. Create directory if not exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // 5. Delete old profile picture
    $old_pic = get_single_row("SELECT profile_picture FROM appearance WHERE user_id = ?", [$current_user_id], 'i');
    if ($old_pic['profile_picture'] && file_exists('../' . $old_pic['profile_picture'])) {
        unlink('../' . $old_pic['profile_picture']);
    }
    
    // 6. Move uploaded file
    if (move_uploaded_file($ile['tmp_name'], $upload_path)) {
        // 7. Update database
        $db_path = 'uploads/profile_pics/' . $ilename;
        $query = "UPDATE appearance SET profile_picture = ? WHERE user_id = ?";
        execute_query($query, [$db_path, $current_user_id], 'si');
        
        $_SESSION['success'] = 'Profile picture updated!';
    } else {
        $_SESSION['error'] = 'Upload failed';
    }
}
```

**Upload 2: Background Image**
```php
// admin/appearance.php
if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === 0) {
    $ile = $_FILES['background_image'];
    
    // Validation (same as profile picture)
    // ...
    
    $ilename = 'bg_' . $current_user_id . '_' . time() . '.' . $ext;
    $upload_dir = '../uploads/backgrounds/';
    $upload_path = $upload_dir . $ilename;
    
    if (move_uploaded_file($ile['tmp_name'], $upload_path)) {
        $db_path = 'uploads/backgrounds/' . $ilename;
        $query = "UPDATE appearance 
                    SET background_type = 'image', background_image = ? 
                    WHERE user_id = ?";
        execute_query($query, [$db_path, $current_user_id], 'si');
    }
}
```

**Upload Form HTML:**
```html
<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="profile_picture" class="form-label">Profile Picture</label>
        <input type="file" 
               class="form-control" 
               id="profile_picture" 
               name="profile_picture" 
               accept="image/jpeg,image/png,image/gif,image/webp">
        <small class="text-muted">Max 2MB. JPG, PNG, GIF, or WEBP.</small>
    </div>
    
    <!-- Preview -->
    <div id="preview">
        <img src="<?php echo $user_data['profile_picture']; ?>" 
             alt="Current" 
             style="max-width: 150px; border-radius: 50%;">
    </div>
    
    <button type="submit" class="btn btn-primary">Upload</button>
</form>
```

**Directory Structure:**
`
uploads/
 profile_pics/
    profile_1_1699999999.jpg
    profile_2_1699999998.png
    .htaccess (allow images only)
 backgrounds/
    bg_1_1699999997.jpg
    bg_2_1699999996.png
 folder_pics/ (for future use)
`

**Security .htaccess:**
```apache
# uploads/.htaccess
# Only allow image access
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Deny PHP execution
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>
```

---

### 11. Penggunaan Session Login 

**Lokasi Implementasi:**
- login.php - Session creation
- config/auth_check.php - Session validation
- Semua file di dmin/ - Protected pages

**Cara Kerja Teknikal:**

**Login Process:**
```php
// login.php
session_start();

if ($_POST['action'] === 'login') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    // 1. Get user from database
    $user = get_single_row(
        "SELECT user_id, username, password, email_verified FROM users WHERE email = ?",
        [$email], 's'
    );
    
    if (!$user) {
        $error = 'Invalid email or password';
    } else {
        // 2. Verify password
        if (password_verify($password, $user['password'])) {
            // 3. Check email verification
            if ($user['email_verified'] != 1) {
                $error = 'Please verify your email first';
            } else {
                // 4. Create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $email;
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                
                // 5. Regenerate session ID (security)
                session_regenerate_id(true);
                
                // 6. Redirect to dashboard
                header('Location: admin/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
```

**Session Validation (Auth Check):**
```php
// config/auth_check.php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    $_SESSION['error'] = 'Please login first';
    header('Location: ../login.php');
    exit;
}

// 2. Check session timeout (30 minutes)
$timeout_duration = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > $timeout_duration) {
    
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}

// 3. Update last activity time
$_SESSION['last_activity'] = time();

// 4. Store user_id for use in protected pages
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];
```

**Protected Page Usage:**
```php
// admin/dashboard.php
<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// User is authenticated, $current_user_id is available
$user_data = get_single_row("SELECT * FROM users WHERE user_id = ?", [$current_user_id], 'i');
?>

<h1>Welcome, <?php echo htmlspecialchars($current_username); ?>!</h1>
```

**Session Storage:**
```php
// Session variables stored:
$_SESSION = [
    'user_id' => 1,
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'logged_in' => true,
    'login_time' => 1699999999,
    'last_activity' => 1700000100,
    'cache' => [...] // Performance cache
];
```

---

### 12. Penggunaan Session Logout 

**Lokasi Implementasi:** logout.php

**Cara Kerja Teknikal:**

**Logout Process:**
```php
// logout.php
<?php
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// 3. Destroy session file on server
session_destroy();

// 4. Redirect to login page
header('Location: login.php?logged_out=1');
exit;
?>
```

**Logout Button in Navbar:**
```php
// partials/admin_nav.php
<ul class="navbar-nav ms-auto">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username']; ?>
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="../profile.php?username=<?php echo $_SESSION['username']; ?>">
                <i class="bi bi-eye"></i> View Profile
            </a></li>
            <li><a class="dropdown-item" href="settings.php">
                <i class="bi bi-gear"></i> Settings
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a></li>
        </ul>
    </li>
</ul>
```

**Logout Confirmation (Optional):**
```javascript
// assets/js/admin.js
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../logout.php';
    }
}

// Usage
<a href="#" onclick="confirmLogout(); return false;">Logout</a>
```

**Auto Logout on Timeout:**
```php
// config/auth_check.php already handles this
if (isset($_SESSION['last_activity']) && 
    (time() - $_SESSION['last_activity']) > 1800) {
    
    session_unset();
    session_destroy();
    header('Location: ../login.php?timeout=1');
    exit;
}
```

**Login Page After Logout:**
```php
// login.php
<?php
if (isset($_GET['logged_out'])) {
    echo '<div class="alert alert-success">You have been logged out successfully.</div>';
}

if (isset($_GET['timeout'])) {
    echo '<div class="alert alert-warning">Your session has expired. Please login again.</div>';
}
?>
```

---

## B. FITUR TAMBAHAN (5+)

### 1. Email Verification dengan OTP 

**Lokasi:** erify-otp.php, 
esend-otp.php

**Cara Kerja:**
```php
// verify-email.php - Generate OTP
$otp_code = sprintf("%06d", mt_rand(1, 999999)); // 6 digit
$expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// Store in sessions table
$query = "INSERT INTO sessions (user_id, otp_code, expires_at) VALUES (?, ?, ?)";
execute_query($query, [$user_id, $otp_code, $expires_at], 'iss');

// Send email via PHPMailer
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'app-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('noreply@linkmy.com', 'LinkMy');
$mail->addAddress($user_email);
$mail->Subject = 'Your OTP Code - LinkMy';
$mail->Body = "Your OTP: $otp_code (expires in 15 minutes)";

$mail->send();
```

**Verify OTP:**
```php
// verify-otp.php
if ($_POST['action'] === 'verify') {
    $otp_input = $_POST['otp_code'];
    
    $session = get_single_row(
        "SELECT * FROM sessions 
         WHERE user_id = ? AND otp_code = ? AND expires_at > NOW()",
        [$user_id, $otp_input], 'is'
    );
    
    if ($session) {
        // Valid OTP
        execute_query("UPDATE users SET email_verified = 1 WHERE user_id = ?", [$user_id], 'i');
        execute_query("DELETE FROM sessions WHERE user_id = ?", [$user_id], 'i');
        
        $_SESSION['success'] = 'Email verified!';
        header('Location: admin/dashboard.php');
    } else {
        $error = 'Invalid or expired OTP';
    }
}
```

---

### 2. Password Reset dengan Token 

**Lokasi:** orgot-password.php, 
eset-password.php

**Cara Kerja:**
```php
// forgot-password.php - Generate reset token
$token = bin2hex(random_bytes(32)); // 64 character token
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

$query = "INSERT INTO sessions (user_id, reset_token, expires_at) VALUES (?, ?, ?)";
execute_query($query, [$user_id, $	oken, $expires_at], 'iss');

// Send reset link
$reset_link = "https://yourdomain.com/reset-password.php?token=$token";
$mail->Body = "Click here to reset your password: $reset_link";
$mail->send();
```

**Reset Password:**
```php
// reset-password.php
$	oken = $_GET['token'];

// Validate token
$session = get_single_row(
    "SELECT user_id FROM sessions WHERE reset_token = ? AND expires_at > NOW()",
    [$	oken], 's'
);

if (!$session) {
    die('Invalid or expired token');
}

if ($_POST['action'] === 'reset') {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    execute_query("UPDATE users SET password = ? WHERE user_id = ?", [$new_password, $session['user_id']], 'si');
    execute_query("DELETE FROM sessions WHERE reset_token = ?", [$	oken], 's');
    
    header('Location: login.php?reset=success');
}
```

---

### 3. Verified Badge System 

**Lokasi:** profile.php, database migration

**Cara Kerja:**
```sql
-- Add verified column
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0;

-- Grant verification to founder
UPDATE users SET is_verified = 1 WHERE email = 'fahmiilham029@gmail.com';
```

**Display on Profile:**
```php
<!-- profile.php -->
<h1>
    <?php echo htmlspecialchars($user_data['display_name']); ?>
    <?php if ($user_data['is_verified'] == 1): ?>
        <i class="bi bi-patch-check-fill" 
           style="color: #1DA1F2; font-size: 1.2rem;" 
           title="Verified Account"></i>
    <?php endif; ?>
</h1>
```

---

### 4. Linktree-Style Boxed Layout 

**Lokasi:** profile.php, dmin/appearance.php

**Cara Kerja:**
```sql
-- Database schema
ALTER TABLE appearance ADD COLUMN outer_background_type ENUM('color', 'image', 'gradient') DEFAULT 'gradient';
ALTER TABLE appearance ADD COLUMN outer_background_color VARCHAR(50) DEFAULT 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
ALTER TABLE appearance ADD COLUMN outer_background_image VARCHAR(255) DEFAULT NULL;
```

**CSS Implementation:**
```html
<style>
.outer-background {
    min-height: 100vh;
    background: <?php echo $user_data['outer_background_color']; ?>;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-container {
    max-width: 600px;
    background: white;
    border-radius: 20px;
    padding: 40px 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}
</style>
```

---

### 5. Real-time Analytics dengan Geolocation 

**Lokasi:** 
edirect.php, dmin/dashboard.php

**Cara Kerja:**
```php
// redirect.php - Capture location
$ip = $_SERVER['REMOTE_ADDR'];
$geo_data = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city");
$geo = json_decode($geo_data, true);

$country = $geo['country'] ?? 'Unknown';
$city = $geo['city'] ?? '';

$query = "INSERT INTO link_analytics (link_id, ip_address, country, city, clicked_at) 
            VALUES (?, ?, ?, ?, NOW())";
execute_query($query, [$link_id, $ip, $country, $city], 'isss');
```

**Display on Dashboard:**
```php
// Traffic by Location chart
$click_by_location = get_all_rows(
    "SELECT 
        CASE 
            WHEN city != '' THEN CONCAT(city, ', ', country)
            ELSE country
        END as location,
        COUNT(*) as clicks
     FROM link_analytics la
     INNER JOIN links l ON la.link_id = l.link_id
     WHERE l.user_id = ?
     GROUP BY location
     ORDER BY clicks DESC LIMIT 10",
    [$user_id], 'i'
);
```

---

### 6. Category System dengan Color Coding 

**Lokasi:** dmin/categories.php, profile.php

**Cara Kerja:**
```sql
CREATE TABLE link_categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    category_name VARCHAR(50) NOT NULL,
    color_code VARCHAR(7) DEFAULT '#6c757d',
    order_index INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Display with Categories:**
```php
$categories = get_all_rows("SELECT * FROM link_categories WHERE user_id = ? ORDER BY order_index", [$user_id], 'i');

foreach ($categories as $category) {
    echo "<div class='category-section'>";
    echo "<h5 style='color: {$category['color_code']}'>";
    echo "<i class='bi bi-folder'></i> {$category['category_name']}";
    echo "</h5>";
    
    $links = get_all_rows("SELECT * FROM links WHERE category_id = ? AND is_active = 1", [$category['category_id']], 'i');
    foreach ($links as $link) {
        // Render link
    }
    echo "</div>";
}
```

---

### 7. Performance Optimization dengan Caching 

**Lokasi:** config/performance.php

**Cara Kerja:**
```php
// SimpleCache class
class SimpleCache {
    public static function remember($key, $callback, $	tl = 300) {
        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = [];
        }
        
        if (isset($_SESSION['cache'][$key]) && 
            $_SESSION['cache'][$key]['expires'] > time()) {
            return $_SESSION['cache'][$key]['data'];
        }
        
        $data = $callback();
        $_SESSION['cache'][$key] = [
            'data' => $data,
            'expires' => time() + $	tl
        ];
        
        return $data;
    }
}

// Usage
$stats = SimpleCache::remember('dashboard_stats_' . $user_id, function() use ($user_id) {
    return [
        'total_clicks' => get_total_clicks($user_id),
        'total_links' => get_total_links($user_id)
    ];
}, 300);
```

---

### 8. Drag & Drop Link Reordering 

**Lokasi:** dmin/dashboard.php dengan jQuery UI

**Cara Kerja:**
```javascript
// jQuery UI Sortable
$('#links-list').sortable({
    handle: '.drag-handle',
    update: function(event, ui) {
        const order = $(this).sortable('toArray', { attribute: 'data-link-id' });
        
        $.ajax({
            url: 'dashboard.php',
            method: 'POST',
            data: { 
                action: 'reorder_links',
                order: JSON.stringify(order)
            }
        });
    }
});
```

```php
// Backend reorder
if ($_POST['action'] === 'reorder_links') {
    $order = json_decode($_POST['order'], true);
    foreach ($order as $index => $link_id) {
        execute_query("UPDATE links SET order_index = ? WHERE link_id = ?", [$index, $link_id], 'ii');
    }
}
```

---

### 9. SEO Optimization (Meta Tags, Sitemap, Schema.org) 

**Lokasi:** profile.php, sitemap.xml

**Cara Kerja:**
```html
<!-- Open Graph & Twitter Card -->
<meta property="og:title" content="<?php echo $user_data['display_name']; ?>">
<meta property="og:description" content="<?php echo $user_data['bio']; ?>">
<meta property="og:image" content="<?php echo $user_data['profile_picture']; ?>">
<meta name="twitter:card" content="summary">

<!-- Schema.org JSON-LD -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Person",
  "name": "<?php echo $user_data['display_name']; ?>",
  "image": "<?php echo $user_data['profile_picture']; ?>",
  "url": "<?php echo $current_url; ?>"
}
</script>
```

---

### 10. AJAX untuk Real-time UI Updates 

**Lokasi:** dmin/dashboard.php, ssets/js/admin.js

**Cara Kerja:**
```javascript
// Add link without page reload
function addLink() {
    $.ajax({
        url: 'dashboard.php',
        method: 'POST',
        data: {
            action: 'add_link',
            title: $('#link_title').val(),
            url: $('#link_url').val(),
            icon_class: $('#icon_class').val()
        },
        success: function(response) {
            const data = JSON.parse(response);
            if (data.success) {
                // Add new link to UI without reload
                $('#links-list').prepend(renderLinkHtml(data));
                $('#addLinkModal').modal('hide');
            }
        }
    });
}
```

---

## C. RINGKASAN FITUR

### Fitur Wajib (12):
1.  HTML + PHP + Database - Semua file .php
2.  CSS - ssets/css/admin.css, inline CSS
3.  Chart - Highcharts di dmin/dashboard.php
4.  Table Relasi - users, appearance, links, categories, analytics
5.  View Database - _public_page_data
6.  Insert Database - Register, add link, analytics tracking
7.  Update Database - Edit profile, links, settings
8.  Delete Database - Delete links, categories, account
9.  Hosting - Docker + VPS Ubuntu
10.  Upload Foto - Profile picture, background image
11.  Session Login - login.php, config/auth_check.php
12.  Session Logout - logout.php

### Fitur Tambahan (10+):
1.  Email OTP Verification (PHPMailer)
2.  Password Reset Token System
3.  Verified Badge (Instagram-style)
4.  Boxed Layout (Linktree-style)
5.  Real-time Geolocation Analytics
6.  Category System with Colors
7.  Performance Caching
8.  Drag & Drop Reordering
9.  SEO Optimization (Meta, Sitemap, Schema)
10.  AJAX Real-time Updates

**Total: 22 Fitur Implementasi Lengkap**

---

**Dibuat:** November 2024  
**Mahasiswa:** FahmiYoshikage  
**Project:** LinkMy - Bio Link Manager  
**Status:** Production Ready 
