-- =============================================
-- DATABASE SCHEMA UNTUK LINKMY
-- File: database.sql
-- =============================================

CREATE DATABASE IF NOT EXISTS linkmy_db;
USE linkmy_db;

-- 1. TABEL USERS
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    page_slug VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. TABEL LINKS (Relasi 1-to-N dengan users)
CREATE TABLE links (
    link_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    order_index INT DEFAULT 0,
    icon_class VARCHAR(50) DEFAULT 'bi-link-45deg',
    click_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. TABEL APPEARANCE (Relasi 1-to-1 dengan users)
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. DATABASE VIEW untuk Public Page
CREATE OR REPLACE VIEW v_public_page_data AS
SELECT 
    u.user_id,
    u.username,
    u.page_slug,
    a.profile_title,
    a.bio,
    a.profile_pic_filename,
    a.bg_image_filename,
    a.theme_name,
    a.button_style,
    a.font_family,
    l.link_id,
    l.title AS link_title,
    l.url AS link_url,
    l.icon_class,
    l.click_count,
    l.order_index
FROM users u
LEFT JOIN appearance a ON u.user_id = a.user_id
LEFT JOIN links l ON u.user_id = l.user_id AND l.is_active = 1
ORDER BY u.user_id, l.order_index ASC;

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password_hash, page_slug, email) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@linkmy.com');

-- Insert default appearance for admin
INSERT INTO appearance (user_id, profile_title, bio) 
VALUES (1, 'Admin LinkMy', 'Welcome to LinkMy - Your Personal Link Hub');

-- Insert sample links
INSERT INTO links (user_id, title, url, icon_class, order_index) VALUES
(1, 'Instagram', 'https://instagram.com', 'bi-instagram', 1),
(1, 'GitHub', 'https://github.com', 'bi-github', 2),
(1, 'LinkedIn', 'https://linkedin.com', 'bi-linkedin', 3);