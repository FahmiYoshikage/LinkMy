<?php
/**
 * PHPMailer Configuration with Enhanced Error Handling
 * LinkMy Project - Email OTP System
 */

require_once __DIR__ . '/../libs/PHPMailer-7.0.0/PHPMailer-7.0.0/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-7.0.0/PHPMailer-7.0.0/src/SMTP.php';
require_once __DIR__ . '/../libs/PHPMailer-7.0.0/PHPMailer-7.0.0/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==================== CONFIGURATION ====================
// IMPORTANT: Ganti dengan credentials Gmail Anda!
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // TLS
define('MAIL_USERNAME', 'faildegaskar870@gmail.com'); // ⚠️ GANTI INI!
define('MAIL_PASSWORD', 'amyawuwmqpqawtmr'); // ⚠️ GANTI INI (no spaces)!
define('MAIL_FROM_EMAIL', 'noreply@linkmy.fahmi.app');
define('MAIL_FROM_NAME', 'LinkMy');
define('MAIL_DEBUG', 0); // 0=off, 1=client, 2=client+server (SET TO 0 FOR PRODUCTION!)

// ==================== HELPER FUNCTIONS ====================

/**
 * Generate 6-digit OTP
 */
function generate_otp() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Create PHPMailer instance with common settings
 */
function create_mailer() {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = MAIL_DEBUG;
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;
        
        // Timeout settings (important for slow connections)
        $mail->Timeout = 30; // 30 seconds
        $mail->SMTPKeepAlive = true;
        
        // Default sender
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        
        // Encoding
        $mail->CharSet = 'UTF-8';
        
        return $mail;
        
    } catch (Exception $e) {
        error_log("PHPMailer initialization failed: " . $e->getMessage());
        return null;
    }
}

/**
 * Send OTP verification email
 * 
 * @param string $email Recipient email
 * @param string $otp 6-digit OTP code
 * @return array ['success' => bool, 'message' => string]
 */
function send_otp_email($email, $otp) {
    $mail = create_mailer();
    
    if (!$mail) {
        return [
            'success' => false,
            'message' => 'Failed to initialize email system'
        ];
    }
    
    try {
        // Recipients
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Kode Verifikasi LinkMy - ' . $otp;
        $mail->Body = get_otp_email_template($otp);
        $mail->AltBody = "Kode OTP Anda adalah: $otp\n\nKode ini berlaku selama 10 menit.\nJangan bagikan kode ini kepada siapapun.";
        
        // Send
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Email berhasil dikirim'
        ];
        
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo;
        error_log("Email Error: " . $errorMsg);
        
        return [
            'success' => false,
            'message' => 'Gagal mengirim email: ' . $errorMsg
        ];
    }
}

/**
 * Send password reset email
 * 
 * @param string $email Recipient email
 * @param string $resetToken Reset token
 * @return array ['success' => bool, 'message' => string]
 */
function send_password_reset_email($email, $resetToken) {
    $mail = create_mailer();
    
    if (!$mail) {
        return [
            'success' => false,
            'message' => 'Failed to initialize email system'
        ];
    }
    
    try {
        // Reset link - automatically detect domain
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'linkmy.iet.ovh';
        $resetLink = $protocol . "://" . $host . "/reset-password.php?token=" . $resetToken;
        
        // Recipients
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Password LinkMy';
        $mail->Body = get_password_reset_email_template($resetLink);
        $mail->AltBody = "Klik link berikut untuk reset password: $resetLink\n\nLink berlaku selama 1 jam.";
        
        // Send
        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Email reset password berhasil dikirim'
        ];
        
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo;
        error_log("Password Reset Email Error: " . $errorMsg);
        
        return [
            'success' => false,
            'message' => 'Gagal mengirim email: ' . $errorMsg
        ];
    }
}

/**
 * Get OTP email HTML template
 */
function get_otp_email_template($otp) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; }
            .header h1 { color: white; margin: 0; font-size: 28px; }
            .content { padding: 40px 30px; text-align: center; }
            .otp-box { background: #f8f9fa; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; margin: 20px 0; }
            .otp-code { font-size: 48px; font-weight: bold; color: #667eea; letter-spacing: 8px; margin: 10px 0; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0; text-align: left; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔐 Verifikasi Email</h1>
            </div>
            <div class="content">
                <p style="font-size: 16px; color: #333;">Halo!</p>
                <p style="font-size: 16px; color: #666;">Gunakan kode OTP berikut untuk menyelesaikan registrasi Anda di <strong>LinkMy</strong>:</p>
                
                <div class="otp-box">
                    <p style="margin: 0; color: #666; font-size: 14px;">KODE VERIFIKASI</p>
                    <div class="otp-code">' . $otp . '</div>
                    <p style="margin: 0; color: #999; font-size: 12px;">Berlaku selama 10 menit</p>
                </div>
                
                <div class="warning">
                    <strong>⚠️ Penting:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px; text-align: left;">
                        <li>Jangan bagikan kode ini kepada siapapun</li>
                        <li>LinkMy tidak akan pernah meminta kode OTP Anda</li>
                        <li>Abaikan email ini jika Anda tidak melakukan registrasi</li>
                    </ul>
                </div>
            </div>
            <div class="footer">
                <p>Email ini dikirim otomatis oleh sistem LinkMy.<br>Jangan balas email ini.</p>
                <p>&copy; 2024 LinkMy - Your Personal Link Hub</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Get password reset email template
 */
function get_password_reset_email_template($resetLink) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; }
            .header h1 { color: white; margin: 0; font-size: 28px; }
            .content { padding: 40px 30px; }
            .button { display: inline-block; background: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🔑 Reset Password</h1>
            </div>
            <div class="content">
                <p style="font-size: 16px; color: #333;">Halo!</p>
                <p style="font-size: 16px; color: #666;">Kami menerima permintaan untuk reset password akun LinkMy Anda.</p>
                <p style="font-size: 16px; color: #666;">Klik tombol di bawah untuk membuat password baru:</p>
                
                <div style="text-align: center;">
                    <a href="' . $resetLink . '" class="button">Reset Password</a>
                </div>
                
                <p style="font-size: 14px; color: #999; margin-top: 20px;">Atau copy link berikut ke browser Anda:</p>
                <p style="font-size: 12px; color: #667eea; word-break: break-all; background: #f8f9fa; padding: 10px; border-radius: 5px;">' . $resetLink . '</p>
                
                <div class="warning">
                    <strong>⚠️ Penting:</strong>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>Link ini berlaku selama <strong>1 jam</strong></li>
                        <li>Abaikan email ini jika Anda tidak meminta reset password</li>
                        <li>Jangan bagikan link ini kepada siapapun</li>
                    </ul>
                </div>
            </div>
            <div class="footer">
                <p>Email ini dikirim otomatis oleh sistem LinkMy.<br>Jangan balas email ini.</p>
                <p>&copy; 2024 LinkMy - Your Personal Link Hub</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

/**
 * Save OTP to database
 */
function save_otp_to_database($email, $otp) {
    global $conn;
    
    // Calculate expiration (10 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    $stmt = $conn->prepare("
        INSERT INTO email_verifications 
        (email, otp_code, expires_at, ip_address) 
        VALUES (?, ?, ?, ?)
    ");
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt->bind_param("ssss", $email, $otp, $expiresAt, $ipAddress);
    
    return $stmt->execute();
}

/**
 * Verify OTP code
 */
function verify_otp($email, $otp) {
    global $conn;
    
    // Debug log
    error_log("verify_otp() called - Email: $email, OTP: $otp");
    
    $stmt = $conn->prepare("
        SELECT id, expires_at, is_used 
        FROM email_verifications 
        WHERE email = ? AND otp_code = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    
    $stmt->bind_param("ss", $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Debug log
    error_log("verify_otp() - Rows found: " . $result->num_rows);
    
    if ($result->num_rows === 0) {
        error_log("verify_otp() - OTP not found in database");
        return ['success' => false, 'message' => 'Kode OTP tidak valid'];
    }
    
    $row = $result->fetch_assoc();
    
    // Debug log
    error_log("verify_otp() - OTP data: " . json_encode($row));
    
    // Check if already used
    if ($row['is_used'] == 1) {
        error_log("verify_otp() - OTP already used");
        return ['success' => false, 'message' => 'Kode OTP sudah digunakan'];
    }
    
    // Check if expired
    $expiresTimestamp = strtotime($row['expires_at']);
    $currentTimestamp = time();
    
    error_log("verify_otp() - Expires: $expiresTimestamp, Current: $currentTimestamp");
    
    if ($expiresTimestamp < $currentTimestamp) {
        error_log("verify_otp() - OTP expired");
        return ['success' => false, 'message' => 'Kode OTP sudah kadaluarsa'];
    }
    
    // Mark as used
    $updateStmt = $conn->prepare("UPDATE email_verifications SET is_used = 1 WHERE id = ?");
    $updateStmt->bind_param("i", $row['id']);
    $updateStmt->execute();
    
    error_log("verify_otp() - SUCCESS! OTP verified and marked as used");
    
    return ['success' => true, 'message' => 'OTP berhasil diverifikasi'];
}

/**
 * Check OTP rate limit
 * Max 3 requests per 10 minutes
 */
function check_otp_rate_limit($email) {
    global $conn;
    
    $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM email_verifications 
        WHERE email = ? AND created_at > ?
    ");
    
    $stmt->bind_param("ss", $email, $tenMinutesAgo);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] < 3;
}

/**
 * Test email connection
 * Run this from browser: test-email.php
 */
function test_email_connection() {
    $mail = create_mailer();
    
    if (!$mail) {
        return "Failed to create mailer instance";
    }
    
    try {
        // Try to connect
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            echo "Debug level $level; message: $str<br>";
        };
        
        // Test connection
        if ($mail->smtpConnect()) {
            $mail->smtpClose();
            return "✅ SMTP Connection Successful!";
        } else {
            return "❌ SMTP Connection Failed!";
        }
        
    } catch (Exception $e) {
        return "❌ Connection Error: " . $e->getMessage();
    }
}
?>