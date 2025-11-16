<?php
/**
 * Reset Password Page
 * LinkMy - Set new password with token verification
 */

session_start();
require_once 'config/db.php';

$error = '';
$success = '';
$validToken = false;
$email = '';

// Get token from URL
$resetToken = $_GET['token'] ?? '';

if (empty($resetToken)) {
    $error = 'Token tidak valid';
} else {
    // Verify token
    $stmt = $conn->prepare("
        SELECT email, expires_at, is_used 
        FROM password_resets 
        WHERE reset_token = ? 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $resetToken);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $error = 'Token tidak ditemukan atau sudah tidak valid';
    } else {
        $row = $result->fetch_assoc();
        $email = $row['email'];
        
        // Check if already used
        if ($row['is_used'] == 1) {
            $error = 'Token ini sudah digunakan';
        }
        // Check if expired
        elseif (strtotime($row['expires_at']) < time()) {
            $error = 'Token sudah kadaluarsa. Silakan request reset password lagi.';
        }
        else {
            $validToken = true;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate passwords
    if (empty($newPassword)) {
        $error = 'Password baru harus diisi';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password minimal 6 karakter';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password tidak cocok';
    } else {
        // Hash new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user password
        $updateStmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $passwordHash, $email);
        
        if ($updateStmt->execute()) {
            // Mark token as used
            $markUsedStmt = $conn->prepare("UPDATE password_resets SET is_used = 1 WHERE reset_token = ?");
            $markUsedStmt->bind_param("s", $resetToken);
            $markUsedStmt->execute();
            
            $success = 'Password berhasil direset! Silakan login dengan password baru Anda.';
            $validToken = false; // Disable form
            
            // Redirect after 3 seconds
            header("refresh:3;url=index.php");
        } else {
            $error = 'Gagal mereset password. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - LinkMy</title>
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
        .reset-container {
            max-width: 500px;
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
        .password-strength {
            height: 5px;
            border-radius: 3px;
            background: #e9ecef;
            margin-top: 8px;
            overflow: hidden;
        }
        .password-strength-bar {
            height: 100%;
            transition: all 0.3s;
            width: 0%;
        }
        .strength-weak { width: 33%; background: #dc3545; }
        .strength-medium { width: 66%; background: #ffc107; }
        .strength-strong { width: 100%; background: #28a745; }
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
        .password-requirements {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .requirement {
            display: flex;
            align-items: center;
            margin-top: 5px;
        }
        .requirement i {
            margin-right: 5px;
        }
        .requirement.met {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="card">
                <div class="card-body">
                    <div class="icon-circle">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    
                    <h3 class="text-center mb-2">Reset Password</h3>
                    <p class="text-center text-muted mb-4">
                        <?php if ($validToken): ?>
                            Buat password baru untuk akun <strong><?php echo htmlspecialchars($email); ?></strong>
                        <?php else: ?>
                            Terjadi masalah dengan link reset password Anda
                        <?php endif; ?>
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="forgot-password.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Request Reset Lagi
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        </div>
                        <div class="text-center">
                            <p class="text-muted">Redirecting ke login page...</p>
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($validToken && !$success): ?>
                        <form method="POST" action="" id="resetForm">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lock"></i> Password Baru
                                </label>
                                <input type="password" class="form-control" name="password" 
                                       id="password" placeholder="Minimal 6 karakter" required>
                                <div class="password-strength">
                                    <div class="password-strength-bar" id="strengthBar"></div>
                                </div>
                                <small class="text-muted" id="strengthText"></small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-lock-fill"></i> Konfirmasi Password
                                </label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       id="confirmPassword" placeholder="Ulangi password" required>
                                <small class="text-danger d-none" id="passwordMismatch">
                                    <i class="bi bi-x-circle"></i> Password tidak cocok
                                </small>
                                <small class="text-success d-none" id="passwordMatch">
                                    <i class="bi bi-check-circle"></i> Password cocok
                                </small>
                            </div>

                            <div class="password-requirements mb-3">
                                <strong>Syarat password:</strong>
                                <div class="requirement" id="req-length">
                                    <i class="bi bi-circle"></i> Minimal 6 karakter
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                <i class="bi bi-check-circle"></i> Reset Password
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const reqLength = document.getElementById('req-length');
        const submitBtn = document.getElementById('submitBtn');

        // Password strength checker
        password?.addEventListener('input', function() {
            const val = this.value;
            const length = val.length;
            
            // Update requirements
            if (length >= 6) {
                reqLength.classList.add('met');
                reqLength.querySelector('i').className = 'bi bi-check-circle-fill';
            } else {
                reqLength.classList.remove('met');
                reqLength.querySelector('i').className = 'bi bi-circle';
            }
            
            // Update strength bar
            strengthBar.className = 'password-strength-bar';
            if (length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
            } else if (length < 6) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Lemah';
            } else if (length < 10) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Sedang';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Kuat';
            }
            
            checkPasswordMatch();
        });

        // Confirm password checker
        confirmPassword?.addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const pwd = password.value;
            const confirm = confirmPassword.value;
            const mismatch = document.getElementById('passwordMismatch');
            const match = document.getElementById('passwordMatch');
            
            if (confirm.length === 0) {
                mismatch.classList.add('d-none');
                match.classList.add('d-none');
                submitBtn.disabled = false;
            } else if (pwd !== confirm) {
                mismatch.classList.remove('d-none');
                match.classList.add('d-none');
                submitBtn.disabled = true;
            } else {
                mismatch.classList.add('d-none');
                match.classList.remove('d-none');
                submitBtn.disabled = false;
            }
        }
    </script>
</body>
</html>