<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

$user = get_single_row("SELECT * FROM users WHERE user_id = ?", [$current_user_id], 'i');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Password baru dan konfirmasi tidak cocok!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } else {
        if (password_verify($current_password, $user['password_hash'])) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password_hash = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $new_hash, $current_user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Password berhasil diubah!';
            } else {
                $error = 'Gagal mengubah password!';
            }
        } else {
            $error = 'Password lama salah!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $new_email = trim($_POST['email']);
    
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid!';
    } else {
        $query = "UPDATE users SET email = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $new_email, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Email berhasil diupdate!';
            $user['email'] = $new_email;
        } else {
            $error = 'Gagal mengupdate email!';
        }
    }
}

if (isset($_GET['delete_account']) && $_GET['delete_account'] === 'confirm') {
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        session_destroy();
        header('Location: ../index.php?account_deleted=1');
        exit;
    }
}

$total_links = get_single_row("SELECT COUNT(*) as count FROM links WHERE user_id = ?", [$current_user_id], 'i')['count'];
$total_clicks = get_single_row("SELECT SUM(click_count) as total FROM links WHERE user_id = ?", [$current_user_id], 'i')['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - LinkMy</title>
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f7fa;
            padding-top: 76px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
        }
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 10px;
            padding: 1.5rem;
            background: #fff5f5;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="bi bi-link-45deg"></i> LinkMy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appearance.php">
                            <i class="bi bi-palette-fill"></i> Appearance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="bi bi-folder-fill"></i> Categories
                            <span class="badge bg-success ms-1">New</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="settings.php">
                            <i class="bi bi-gear-fill"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../profile.php?slug=<?= $current_page_slug ?>" target="_blank">
                            <i class="bi bi-eye-fill"></i> View Page
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container py-4">
        <h2 class="fw-bold mb-4">
            <i class="bi bi-gear-fill"></i> Settings
        </h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Account Stats -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-person-circle"></i> Info Akun
                        </h5>
                        
                        <div class="mb-3">
                            <small class="text-muted">Username</small>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($user['username']) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Page Slug</small>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($user['page_slug']) ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Email</small>
                            <p class="fw-bold mb-0"><?= htmlspecialchars($user['email'] ?? 'Belum diset') ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Bergabung Sejak</small>
                            <p class="fw-bold mb-0">
                                <?= date('d M Y', strtotime($user['created_at'])) ?>
                            </p>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="stat-box mb-2">
                                    <h3 class="fw-bold mb-0"><?= $total_links ?></h3>
                                    <small>Total Links</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-box mb-2">
                                    <h3 class="fw-bold mb-0"><?= $total_clicks ?></h3>
                                    <small>Total Klik</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Forms -->
            <div class="col-lg-8">
                <!-- Change Password -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-shield-lock"></i> Ganti Password
                        </h5>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Lama</label>
                                <input type="password" class="form-control" name="current_password" 
                                       placeholder="Masukkan password lama" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <input type="password" class="form-control" name="new_password" 
                                       placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                                <input type="password" class="form-control" name="confirm_password" 
                                       placeholder="Ulangi password baru" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-save"></i> Ganti Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Update Email -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-envelope"></i> Update Email
                        </h5>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Baru</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="email@example.com" required>
                            </div>
                            
                            <button type="submit" name="update_email" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Email
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Danger Zone -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-danger mb-4">
                            <i class="bi bi-exclamation-triangle-fill"></i> Danger Zone
                        </h5>
                        
                        <div class="danger-zone">
                            <h6 class="fw-bold">Hapus Akun</h6>
                            <p class="text-muted mb-3">
                                Tindakan ini tidak dapat dibatalkan. Semua data Anda termasuk links, 
                                appearance, dan statistik akan dihapus permanen.
                            </p>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="bi bi-trash"></i> Hapus Akun Saya
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus Akun
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Apakah Anda yakin ingin menghapus akun Anda?</p>
                    <p class="text-muted mb-0">
                        Tindakan ini akan menghapus:
                    </p>
                    <ul class="text-muted">
                        <li>Semua link Anda (<?= $total_links ?> links)</li>
                        <li>Pengaturan appearance</li>
                        <li>Statistik klik (<?= $total_clicks ?> total klik)</li>
                        <li>Semua data profil</li>
                    </ul>
                    <p class="text-danger fw-bold">Tindakan ini TIDAK DAPAT dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="?delete_account=confirm" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Ya, Hapus Akun Saya
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>