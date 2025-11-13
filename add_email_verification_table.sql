-- Script untuk menambahkan tabel email_verifications jika belum ada
-- Jalankan script ini di phpMyAdmin atau MySQL command line

USE linkmy_db;

-- Buat tabel email_verifications jika belum ada
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL,
    otp_code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used TINYINT(1) DEFAULT 0,
    INDEX idx_email_otp (email, otp_code),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- Bersihkan OTP yang sudah kadaluarsa (opsional - untuk maintenance)
-- DELETE FROM email_verifications WHERE expires_at < NOW() OR is_used = 1;