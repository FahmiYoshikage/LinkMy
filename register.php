<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

require_once 'config/db.php';

$error = '';
$success = '';
$current_step = isset($_SESSION['reg_step']) ? $_SESSION['reg_step'] : 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step1'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $check_email = get_single_row("SELECT user_id FROM users WHERE email = ?", [$email], 's');
        
        if ($check_email) {
            $error = 'Email sudah terdaftar! Gunakan email lain atau <a href="index.php">login</a>.';
        } else {
            $_SESSION['reg_email'] = $email;
            $_SESSION['reg_password'] = $password;
            $_SESSION['reg_step'] = 2;
            $current_step = 2;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['step2'])) {
    if (!isset($_SESSION['reg_email']) || !isset($_SESSION['reg_password'])) {
        $error = 'Session expired. Silakan mulai dari awal.';
        $_SESSION['reg_step'] = 1;
        $current_step = 1;
    } else {
        $username = trim($_POST['username']);
        $page_slug = trim($_POST['page_slug']);
        if (empty($username) || empty($page_slug)) {
            $error = 'Username dan page slug harus diisi!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Username hanya boleh mengandung huruf, angka, dan underscore!';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $page_slug)) {
            $error = 'Page slug hanya boleh mengandung huruf, angka, underscore, dan dash!';
        } elseif (strlen($username) < 3) {
            $error = 'Username minimal 3 karakter!';
        } elseif (strlen($page_slug) < 3) {
            $error = 'Page slug minimal 3 karakter!';
        } else {
            $check_username = get_single_row("SELECT user_id FROM users WHERE username = ?", [$username], 's');
            if ($check_username) {
                $error = 'Username sudah digunakan! Pilih yang lain.';
            } else {
                $check_slug = get_single_row("SELECT user_id FROM users WHERE page_slug = ?", [$page_slug], 's');
                if ($check_slug) {
                    $error = 'Page slug sudah digunakan! Pilih yang lain.';
                } else {
                    $email = $_SESSION['reg_email'];
                    $password = $_SESSION['reg_password'];
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $query = "INSERT INTO users (username, password_hash, page_slug, email) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'ssss', $username, $password_hash, $page_slug, $email);
                    if (mysqli_stmt_execute($stmt)) {
                        $new_user_id = mysqli_insert_id($conn);
                        $appearance_query = "INSERT INTO appearance (user_id, profile_title, bio) VALUES (?, ?, ?)";
                        $default_bio = "Welcome to my LinkMy page!";
                        $stmt2 = mysqli_prepare($conn, $appearance_query);
                        mysqli_stmt_bind_param($stmt2, 'iss', $new_user_id, $username, $default_bio);
                        mysqli_stmt_execute($stmt2);
                        unset($_SESSION['reg_email']);
                        unset($_SESSION['reg_password']);
                        unset($_SESSION['reg_step']);
                        header('Location: index.php?registered=1');
                        exit;
                    } else {
                        $error = 'Terjadi kesalahan saat registrasi. Coba lagi!';
                    }
                }
            }
        }
    }
}
if (isset($_GET['back']) && $_GET['back'] == '1') {
    $_SESSION['reg_step'] = 1;
    $current_step = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - LinkMy</title>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .register-card {
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            background: white;
            padding: 2.5rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .brand-logo {
            font-size: 3rem;
            color: #667eea;
        }
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .slug-preview {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            color: #666;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
            position: relative;
        }
        .step.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            width: 80px;
            height: 2px;
            background: #e9ecef;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 10px;
        }
        .step.completed:not(:last-child)::after {
            background: #28a745;
        }
        .step-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-card">
            <div class="brand-header">
                <div class="brand-logo">
                    <i class="bi bi-link-45deg"></i>
                </div>
                <h2 class="fw-bold mb-2">Daftar LinkMy</h2>
                <p class="text-muted">Buat akun Anda dan mulai berbagi link!</p>
            </div>
            <div class="step-indicator">
                <div class="step-container">
                    <div class="step <?= $current_step >= 1 ? 'active' : '' ?> <?= $current_step > 1 ? 'completed' : '' ?>">
                        1
                    </div>
                    <div class="step <?= $current_step >= 2 ? 'active' : '' ?>">
                        2
                    </div>
                </div>
            </div>
            
            <div class="text-center mb-4">
                <h5 class="fw-bold">
                    <?= $current_step == 1 ? 'Langkah 1: Akun Anda' : 'Langkah 2: Profil Publik' ?>
                </h5>
                <p class="text-muted small mb-0">
                    <?= $current_step == 1 ? 'Masukkan email dan password untuk akun Anda' : 'Pilih username dan URL publik Anda' ?>
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($current_step == 1): ?>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope-fill me-1"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="email@example.com" required autofocus
                               value="<?= isset($_SESSION['reg_email']) ? htmlspecialchars($_SESSION['reg_email']) : '' ?>">
                        <small class="text-muted">Email ini akan digunakan untuk login</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Minimal 6 karakter" required minlength="6">
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-lock-fill me-1"></i>Konfirmasi Password
                        </label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Ulangi password" required>
                    </div>
                    
                    <button type="submit" name="step1" class="btn btn-primary btn-register w-100 mb-3">
                        Lanjut ke Step 2 <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    
                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? 
                            <a href="index.php" class="text-decoration-none fw-semibold">Login di sini</a>
                        </p>
                    </div>
                </form>
            
            <?php else: ?>
                <form method="POST" action="" id="registerForm">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-fill me-1"></i>Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               placeholder="Pilih username unik" required 
                               pattern="[a-zA-Z0-9_]+" 
                               minlength="3"
                               title="Hanya huruf, angka, dan underscore (min 3 karakter)">
                        <small class="text-muted">Username untuk tampilan profil Anda</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="page_slug" class="form-label">
                            <i class="bi bi-link-45deg me-1"></i>Page Slug (URL Publik)
                        </label>
                        <input type="text" class="form-control" id="page_slug" name="page_slug" 
                               placeholder="namakamu" required 
                               pattern="[a-zA-Z0-9_-]+" 
                               minlength="3"
                               title="Hanya huruf, angka, underscore, dan dash (min 3 karakter)">
                        <div class="slug-preview mt-2">
                            <i class="bi bi-globe me-1"></i>
                            Link Publik Anda: <span class="fw-bold" id="slugPreview">linkmy.fahmi.app/namakamu</span>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="?back=1" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" name="step2" class="btn btn-primary btn-register flex-fill">
                            <i class="bi bi-check-circle-fill me-2"></i>Daftar
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0 small text-muted">
                            Email: <strong><?= isset($_SESSION['reg_email']) ? htmlspecialchars($_SESSION['reg_email']) : '' ?></strong>
                        </p>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pageSlugInput = document.getElementById('page_slug');
        if (pageSlugInput) {
            pageSlugInput.addEventListener('input', function() {
                const slugValue = this.value || 'namakamu';
                document.getElementById('slugPreview').textContent = 'linkmy.fahmi.app/' + slugValue;
            });
        }
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                if (passwordInput.value !== this.value) {
                    this.setCustomValidity('Password tidak cocok');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        const usernameInput = document.getElementById('username');
        if (usernameInput && pageSlugInput) {
            usernameInput.addEventListener('input', function() {
                if (!pageSlugInput.value) {
                    pageSlugInput.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');
                    pageSlugInput.dispatchEvent(new Event('input'));
                }
            });
        }
    </script>
</body>
</html>