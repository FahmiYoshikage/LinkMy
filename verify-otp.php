<?php
session_start();

// Redirect jika belum ada email di session
if (!isset($_SESSION['reg_email'])) {
    header('Location: register.php');
    exit;
}

require_once 'config/db.php';
require_once 'config/mail.php';

$email = $_SESSION['reg_email'];
$error = '';
$success = '';

// Calculate time remaining
$otp_sent_at = $_SESSION['otp_sent_at'] ?? time();
$time_elapsed = time() - $otp_sent_at;
$time_remaining = max(0, 600 - $time_elapsed);

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    // CRITICAL: Get OTP from POST
    $otp_input = trim($_POST['otp_code'] ?? '');
    
    error_log("=== OTP VERIFICATION START ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Email: $email");
    error_log("Input OTP: [$otp_input]");
    
    if (empty($otp_input)) {
        $error = 'Kode OTP harus diisi!';
        error_log("ERROR: OTP empty");
    } elseif (!preg_match('/^[0-9]{6}$/', $otp_input)) {
        $error = 'Kode OTP harus 6 digit angka!';
        error_log("ERROR: Invalid format. Length: " . strlen($otp_input));
    } else {
        // Verify OTP
        error_log("Calling verify_otp()...");
        $result = verify_otp($email, $otp_input);
        
        error_log("Result: " . json_encode($result));
        
        // Check success (handle different return types)
        $success_value = isset($result['success']) ? $result['success'] : false;
        $is_success = ($success_value === true || $success_value === 1 || $success_value == true);
        
        error_log("Success value type: " . gettype($success_value));
        error_log("Success value: " . var_export($success_value, true));
        error_log("Is success: " . ($is_success ? 'YES' : 'NO'));
        
        if ($is_success) {
            error_log("✅ VERIFICATION SUCCESS!");
            
            // Set session
            $_SESSION['email_verified'] = true;
            $_SESSION['reg_step'] = 3;
            
            error_log("Session updated:");
            error_log("  email_verified = " . var_export($_SESSION['email_verified'], true));
            error_log("  reg_step = " . $_SESSION['reg_step']);
            
            // Redirect
            error_log("Redirecting to register.php...");
            header('Location: register.php');
            exit;
        } else {
            error_log("❌ VERIFICATION FAILED");
            $error = $result['message'] ?? 'Kode OTP salah atau sudah kadaluarsa!';
            error_log("Error: $error");
        }
    }
    
    error_log("=== OTP VERIFICATION END ===");
}

// Handle resend OTP
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    error_log("=== RESEND OTP ===");
    
    if (!check_otp_rate_limit($email)) {
        $error = 'Terlalu banyak permintaan! Tunggu 10 menit.';
        error_log("Rate limit exceeded");
    } else {
        $new_otp = generate_otp();
        
        if (save_otp_to_database($email, $new_otp)) {
            $email_result = send_otp_email($email, $new_otp);
            
            if ($email_result['success']) {
                $_SESSION['otp_sent_at'] = time();
                $success = 'Kode OTP baru telah dikirim!';
                $time_remaining = 600;
                error_log("Resend SUCCESS");
            } else {
                $error = 'Gagal mengirim OTP.';
                error_log("Email send failed");
            }
        } else {
            $error = 'Gagal menyimpan OTP.';
            error_log("DB save failed");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - LinkMy</title>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .verify-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            margin: 0 auto;
            padding: 40px;
        }
        .brand-logo {
            font-size: 3rem;
            color: #667eea;
            text-align: center;
            margin-bottom: 20px;
        }
        .otp-inputs {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 30px 0;
        }
        .otp-box {
            width: 50px;
            height: 60px;
            font-size: 28px;
            font-weight: bold;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
        }
        .otp-box:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .timer {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="verify-card">
            <div class="brand-logo">
                <i class="bi bi-envelope-check-fill"></i>
            </div>
            
            <h3 class="text-center mb-2">Verifikasi Email</h3>
            <p class="text-center text-muted mb-4">
                Masukkan kode OTP yang dikirim ke<br>
                <strong><?= htmlspecialchars($email) ?></strong>
            </p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <!-- OTP Form -->
            <form method="POST" id="otpForm">
                <label class="form-label text-center d-block mb-3">
                    Masukkan Kode OTP (6 Digit)
                </label>
                
                <!-- Simple OTP Input (Single Field) -->
                <div class="mb-3">
                    <input type="text" 
                           class="form-control form-control-lg text-center" 
                           name="otp_code" 
                           id="otpCode"
                           maxlength="6" 
                           pattern="[0-9]{6}"
                           placeholder="000000"
                           required
                           autocomplete="off"
                           inputmode="numeric"
                           style="font-size: 32px; font-weight: bold; letter-spacing: 10px; font-family: 'Courier New', monospace;">
                    <small class="text-muted d-block text-center mt-2">
                        Ketik atau paste 6 digit angka
                    </small>
                </div>
                
                <!-- Timer -->
                <div class="timer">
                    <i class="bi bi-clock"></i>
                    <span id="timer">
                        <?php
                        $m = floor($time_remaining / 60);
                        $s = $time_remaining % 60;
                        printf('%02d:%02d', $m, $s);
                        ?>
                    </span>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" name="verify_otp" class="btn btn-primary w-100 mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    Verifikasi OTP
                </button>
            </form>
            
            <!-- Resend Link -->
            <div class="text-center">
                <p class="text-muted small mb-2">Tidak menerima kode?</p>
                <a href="?resend=1" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    Kirim Ulang OTP
                </a>
            </div>
            
            <hr class="my-4">
            
            <!-- Info -->
            <div class="alert alert-info small mb-0">
                <strong><i class="bi bi-info-circle me-1"></i>Tips:</strong>
                <ul class="mb-0 mt-2" style="font-size: 12px;">
                    <li>Cek folder Spam jika email tidak masuk</li>
                    <li>Kode berlaku selama 10 menit</li>
                    <li>Jangan bagikan kode ke siapapun</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        // Simple countdown timer
        let timeLeft = <?= $time_remaining ?>;
        const timerEl = document.getElementById('timer');
        
        setInterval(() => {
            if (timeLeft > 0) {
                timeLeft--;
                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                timerEl.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            } else {
                timerEl.textContent = 'KADALUARSA';
                timerEl.style.color = '#dc3545';
            }
        }, 1000);
        
        // Auto-format OTP input
        const otpInput = document.getElementById('otpCode');
        otpInput.addEventListener('input', function(e) {
            // Remove non-digits
            this.value = this.value.replace(/[^0-9]/g, '');
            
            console.log('OTP Input:', this.value);
        });
        
        // Form submit handler
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            const otp = otpInput.value;
            console.log('=== FORM SUBMIT ===');
            console.log('OTP Value:', otp);
            console.log('OTP Length:', otp.length);
            
            if (otp.length !== 6) {
                e.preventDefault();
                alert('Kode OTP harus 6 digit!');
                return false;
            }
            
            console.log('✅ Form will submit to server');
            // Let form submit naturally
        });
    </script>
</body>
</html>