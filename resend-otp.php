<?php
session_start();
require_once 'config/db.php';
require_once 'config/mail.php';

if (!isset($_SESSION['reg_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['reg_email'];
$query = "SELECT COUNT(*) as count FROM email_verifications 
          WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";
$result = get_single_row($query, [$email], 's');

if ($result['count'] >= 3) {
    $_SESSION['error'] = 'Terlalu banyak permintaan! Tunggu 10 menit.';
    header('Location: verify-email.php');
    exit;
}
$otp_code = generate_otp();
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
$ip_address = $_SERVER['REMOTE_ADDR'];

// Save to database
$query = "INSERT INTO email_verifications (email, otp_code, expires_at, ip_address) 
          VALUES (?, ?, ?, ?)";
execute_query($query, [$email, $otp_code, $expires_at, $ip_address], 'ssss');

// Send email
if (send_otp_email($email, $otp_code)) {
    $_SESSION['success'] = 'Kode OTP baru telah dikirim ke email Anda!';
} else {
    $_SESSION['error'] = 'Gagal mengirim email. Coba lagi!';
}

header('Location: verify-email.php');
exit;
?>