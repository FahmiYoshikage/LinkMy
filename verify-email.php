<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['reg_email'])) {
    header('Location: register.php');
    exit;
}

$email = $_SESSION['reg_email'];
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = trim($_POST['otp_code']);
    
    $query = "SELECT * FROM email_verifications 
              WHERE email = ? AND otp_code = ? 
              AND expires_at > NOW() AND is_used = 0 
              ORDER BY id DESC LIMIT 1";
    $otp_record = get_single_row($query, [$email, $otp_input], 'ss');
    if ($otp_record) {
        execute_query("UPDATE email_verifications SET is_used = 1 WHERE id = ?", 
                     [$otp_record['id']], 'i');
        
        $_SESSION['email_verified'] = true;
        header('Location: register.php');
        exit;
    } else {
        $error = 'Kode OTP salah atau sudah kadaluarsa!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email - LinkMy</title>
    <link href="assets/bootstrap-5.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .verify-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .otp-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 1rem;
            font-weight: bold;
        }
        .timer {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="text-center mb-4">
                <i class="bi bi-envelope-check" style="font-size: 4rem; color: #667eea;"></i>
                <h2 class="fw-bold mt-3">Verifikasi Email</h2>
                <p class="text-muted">Kami telah mengirim kode OTP ke:</p>
                <p class="fw-bold"><?= htmlspecialchars($email) ?></p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Masukkan Kode OTP</label>
                    <input type="text" class="form-control otp-input" name="otp_code" 
                           placeholder="000000" maxlength="6" pattern="[0-9]{6}" 
                           required autofocus>
                    <small class="text-muted">Kode 6 digit yang dikirim ke email Anda</small>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-check-circle"></i> Verifikasi
                </button>
            </form>
            
            <div class="text-center">
                <p class="mb-2 small">Tidak menerima kode?</p>
                <a href="resend-otp.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-repeat"></i> Kirim Ulang OTP
                </a>
                <p class="mt-3 small text-muted">
                    Kode akan kadaluarsa dalam <span class="timer" id="timer">10:00</span>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        // Countdown timer
        let timeLeft = 600; // 10 minutes
        const timerElement = document.getElementById('timer');
        
        setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = 
                    String(minutes).padStart(2, '0') + ':' + 
                    String(seconds).padStart(2, '0');
            } else {
                timerElement.textContent = 'Kadaluarsa!';
            }
        }, 1000);
    </script>
</body>
</html>