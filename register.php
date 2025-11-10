<?php
    session_start();
    if(isset($_SESSION['user_id'])){
        header('Location: admin/dashboard.php');
        exit;
    }

    require_once 'config/db.php';

    $error = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])){
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $page_slug = trim($_POST['page_slug']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($username) || empty($email) || empty($page_slug) || empty($password) || empty($confirm_password)){
            $error = 'Semua field harus di isi lah ngabs!';
        } elseif ($password !== $confirm_password) {
            $error = 'Password dan konfirmasi password tidak cocok ngabs!';
        } elseif (strlen($password) < 6){
            $error = 'Password minimal 6 karakter cuy!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)){
            $error = 'Username hanya boleh mengandung huruf, angka, dan underscore!';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $page_slug)){
            $error = 'Page slug hanya boleh mengandung huruf, angka, underscore dan dash!';
        } else {
            $check_username = get_single_row("SELECT user_id FROM users WHERE username = ?", [$username], 's');
            if ($check_username){
                $error = 'Username sudah digunakan ngabs!';
            } else {
                $check_slug = get_single_row("SELECT user_id FROM users WHERE page_slug = ?", [$page_slug], 's');
                if ($check_slug){
                    $error = 'Page slug sudah digunakan! Pilih yang lain cuy!';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $query = "INSERT INTO users (username, password_hash, page_slug, email) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'ssss', $username, $password_hash, $page_slug, $email);

                    if (mysqli_stmt_execute($stmt)){
                        $new_user_id = mysqli_insert_id($conn);
                        $appearance_query = "INSERT INTO appearance (user_id, profile_title, bio) VALUES (?, ?, ?)";
                        $stmt2 = mysqli_prepare($conn, $appearance_query);
                        mysqli_stmt_bind_param($stmt2, 'iss', $new_user_id, $username, $default_bio);
                        mysqli_stmt_execute($stmt2);

                        header('Location: index.php?registered=1');
                        exit;
                    } else {
                        $error = 'Terjadi kesalahan saat registrasi. Coba lagi nanti ngabs!';
                    }
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
    <title>Registrasi - LinkMy</title>
    <link href="assets/bootstrap-5.3/css/bootstrap.min.css" rel="stylesheet">
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
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-fill me-1"></i>Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Pilih username unik" required 
                           pattern="[a-zA-Z0-9_]+" 
                           title="Hanya huruf, angka, dan underscore">
                    <small class="text-muted">Hanya huruf, angka, dan underscore (_)</small>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope-fill me-1"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="email@example.com" required>
                </div>
                
                <div class="mb-3">
                    <label for="page_slug" class="form-label">
                        <i class="bi bi-link-45deg me-1"></i>Page Slug (URL Publik Anda)
                    </label>
                    <input type="text" class="form-control" id="page_slug" name="page_slug" 
                           placeholder="namakamu" required 
                           pattern="[a-zA-Z0-9_-]+" 
                           title="Hanya huruf, angka, underscore, dan dash">
                    <div class="slug-preview mt-2">
                        <i class="bi bi-globe me-1"></i>
                        Link Anda: <span class="fw-bold" id="slugPreview">linkmy.com/</span>
                    </div>
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
                
                <button type="submit" name="register" class="btn btn-primary btn-register w-100 mb-3">
                    <i class="bi bi-person-plus-fill me-2"></i>Daftar Sekarang
                </button>
                
                <div class="text-center">
                    <p class="mb-0">Sudah punya akun? 
                        <a href="index.php" class="text-decoration-none fw-semibold">Login di sini</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="assets/bootstrap-5.3/js/bootstrap.bundle.min.js"></script>
    <script>
        // Live preview slug
        document.getElementById('page_slug').addEventListener('input', function() {
            const slugValue = this.value || 'namakamu';
            document.getElementById('slugPreview').textContent = 'linkmy.com/' + slugValue;
        });
        
        // Validate password match
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
            }
        });
    </script>
</body>
</html>