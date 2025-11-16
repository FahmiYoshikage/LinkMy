<?php
/**
 * Forgot Password Page
 * LinkMy - Send reset password email
 */

session_start();
require_once 'config/db.php';
require_once 'config/mail.php';

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $error = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Email not found - inform user to register first
            $error = 'Email belum terdaftar. Silakan <a href="register.php" class="alert-link">daftar terlebih dahulu</a>.';
        } else {
            // Generate reset token
            $resetToken = bin2hex(random_bytes(32)); // 64 character hex
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            
            // Save to database
            $insertStmt = $conn->prepare("
                INSERT INTO password_resets (email, reset_token, expires_at, ip_address) 
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->bind_param("ssss", $email, $resetToken, $expiresAt, $ipAddress);
            
            if ($insertStmt->execute()) {
                // Send email
                $emailResult = send_password_reset_email($email, $resetToken);
                
                if ($emailResult['success']) {
                    $success = 'Link reset password telah dikirim ke email Anda. Cek inbox/spam folder.';
                } else {
                    $error = 'Gagal mengirim email. Silakan coba lagi.';
                    error_log("Password Reset Email Failed: " . $emailResult['message']);
                }
            } else {
                $error = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - LinkMy</title>
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
        }
        .forgot-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-body {
            padding: 40px;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-circle i {
            font-size: 40px;
            color: white;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container">
            <div class="card">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-key"></i>
                    </div>
                    
                    <h3 class="text-center mb-2">Lupa Password?</h3>
                    <p class="text-center text-muted mb-4">
                        Masukkan email Anda dan kami akan mengirimkan link untuk reset password
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   placeholder="email@example.com" required
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-send"></i> Kirim Link Reset Password
                        </button>
                    </form>

                    <div class="text-center">
                        <a href="index.php" class="back-link">
                            <i class="bi bi-arrow-left"></i> Kembali ke Login
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="card mt-3">
                <div class="card-body py-3">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i>
                        <strong>Catatan:</strong> Link reset password berlaku selama 1 jam dan hanya dapat digunakan 1 kali.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>