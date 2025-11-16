<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}
require_once 'config/db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $query = "SELECT user_id, username, password_hash, page_slug, email FROM users WHERE email = ?";
        $user = get_single_row($query, [$email], 's');
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['page_slug'] = $user['page_slug'];
            $_SESSION['last_activity'] = time();
            
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Email atau password salah!';
        }
    }
}
if (isset($_GET['registered'])) {
    $success = 'Registrasi berhasil! Silakan login dengan email Anda.';
}
if (isset($_GET['logout'])) {
    $success = 'Anda telah logout.';
}
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'not_logged_in') {
        $error = 'Silakan login terlebih dahulu.';
    } elseif ($_GET['error'] === 'session_expired') {
        $error = 'Session Anda telah berakhir. Silakan login kembali.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LinkMy</title>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .brand-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
        }
        .brand-logo {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .form-section {
            padding: 3rem 2rem;
            background: white;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card login-card">
                    <div class="row g-0">
                        <!-- Brand Section -->
                        <div class="col-md-5 brand-section d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <div class="brand-logo">
                                    <i class="bi bi-link-45deg"></i>
                                </div>
                                <h2 class="fw-bold mb-3">LinkMy</h2>
                                <p class="mb-0">Your Personal Link Hub</p>
                                <p class="small">Kelola semua link penting Anda dalam satu tempat</p>
                            </div>
                        </div>
                        
                        <!-- Form Section -->
                        <div class="col-md-7 form-section">
                            <h3 class="fw-bold mb-4">Selamat Datang!</h3>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <?= htmlspecialchars($error) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle-fill me-2"></i>
                                    <?= htmlspecialchars($success) ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="email@example.com" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" 
                                               placeholder="Masukkan password" required>
                                    </div>
                                </div>
                                
                                <button type="submit" name="login" class="btn btn-primary btn-login w-100 mb-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Login
                                </button>
                                
                                <div class="text-center mb-3">
                                    <a href="forgot-password.php" class="text-decoration-none small">
                                        <i class="bi bi-question-circle me-1"></i>Lupa Password?
                                    </a>
                                </div>
                                
                                <div class="text-center">
                                    <p class="mb-0">Belum punya akun? 
                                        <a href="register.php" class="text-decoration-none fw-semibold">Daftar Sekarang</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
