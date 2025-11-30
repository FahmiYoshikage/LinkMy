<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

// Get user data
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

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

// AJAX: Check slug availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_slug_availability') {
    header('Content-Type: application/json');
    
    $slug = trim($_POST['slug'] ?? '');
    
    // Sanitize slug: lowercase, alphanumeric and hyphens only
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    if (strlen($slug) < 3 || strlen($slug) > 50) {
        echo json_encode(['available' => false, 'message' => 'Slug harus 3-50 karakter', 'slug' => $slug]);
        exit;
    }
    
    // Check if slug exists (excluding user's own profiles)
    $existing = get_single_row(
        "SELECT p.profile_id, p.user_id FROM profiles p WHERE p.slug = ? AND p.user_id != ?", 
        [$slug, $current_user_id], 
        'si'
    );
    
    if ($existing) {
        echo json_encode(['available' => false, 'message' => 'Slug sudah digunakan', 'slug' => $slug]);
    } else {
        echo json_encode(['available' => true, 'message' => 'Slug tersedia!', 'slug' => $slug]);
    }
    exit;
}

// Request slug change with OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_slug_change'])) {
    require_once '../config/mail.php';
    
    $new_slug = trim($_POST['new_slug']);
    
    // Sanitize slug
    $new_slug = strtolower($new_slug);
    $new_slug = preg_replace('/[^a-z0-9-]/', '', $new_slug);
    
    if (strlen($new_slug) < 3 || strlen($new_slug) > 50) {
        $error = 'Slug harus 3-50 karakter, hanya huruf, angka, dan tanda hubung!';
    } else {
        // Check cooldown (30 days)
        if ($user['last_slug_change_at']) {
            $last_change = strtotime($user['last_slug_change_at']);
            $days_since = (time() - $last_change) / (60 * 60 * 24);
            
            if ($days_since < 30) {
                $days_left = ceil(30 - $days_since);
                $error = "Anda bisa mengganti slug lagi dalam {$days_left} hari!";
            }
        }
        
        if (!$error) {
            // Check availability
            $existing = get_single_row("SELECT slug_id FROM user_slugs WHERE slug = ?", [$new_slug], 's');
            
            if ($existing) {
                $error = 'Slug sudah digunakan orang lain!';
            } else {
                // Generate OTP
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Store OTP
                $query = "INSERT INTO email_verifications (email, otp_code, expires_at, is_used, verification_type, ip_address) 
                          VALUES (?, ?, ?, 0, 'slug_change', ?)";
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssss', $user['email'], $otp, $expires_at, $ip);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Send OTP email using custom mailer
                    $mail = create_mailer();
                    
                    if ($mail) {
                        try {
                            $mail->addAddress($user['email'], $user['username']);
                            $mail->isHTML(true);
                            $mail->Subject = "Konfirmasi Perubahan Slug - LinkMy";
                            $mail->Body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                    <h2 style='color: #667eea;'>Konfirmasi Perubahan Slug</h2>
                                    <p>Halo <strong>{$user['username']}</strong>,</p>
                                    <p>Anda meminta untuk mengganti slug dari <strong>{$user['page_slug']}</strong> menjadi <strong>{$new_slug}</strong>.</p>
                                    <p>Kode OTP Anda adalah:</p>
                                    <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                                        {$otp}
                                    </div>
                                    <p><strong>⏰ Kode ini berlaku selama 15 menit.</strong></p>
                                    <p>Jika Anda tidak meminta perubahan ini, abaikan email ini.</p>
                                    <hr style='border: 1px solid #eee; margin: 20px 0;'>
                                    <p style='color: #888; font-size: 12px;'>
                                        Salam,<br>
                                        <strong>Tim LinkMy</strong><br>
                                        <a href='https://linkmy.iet.ovh'>linkmy.iet.ovh</a>
                                    </p>
                                </div>
                            ";
                            
                            if ($mail->send()) {
                                $_SESSION['pending_slug_change'] = $new_slug;
                                $success = "Kode OTP telah dikirim ke {$user['email']}. Silakan cek email Anda!";
                            } else {
                                $error = 'Gagal mengirim email OTP!';
                            }
                        } catch (Exception $e) {
                            $error = 'Gagal mengirim email: ' . $mail->ErrorInfo;
                        }
                    } else {
                        $error = 'Gagal membuat koneksi email!';
                    }
                } else {
                    $error = 'Gagal membuat OTP!';
                }
            }
        }
    }
}

// Verify slug change OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_slug_change'])) {
    $otp = trim($_POST['otp_code']);
    $new_slug = $_SESSION['pending_slug_change'] ?? '';
    
    if (empty($new_slug)) {
        $error = 'Tidak ada permintaan perubahan slug!';
    } else {
        // Verify OTP
        $verification = get_single_row(
            "SELECT * FROM email_verifications 
             WHERE email = ? AND otp_code = ? AND is_used = 0 
             AND expires_at > NOW() AND verification_type = 'slug_change'
             ORDER BY id DESC LIMIT 1",
            [$user['email'], $otp],
            'ss'
        );
        
        if (!$verification) {
            $error = 'Kode OTP salah atau sudah kadaluarsa!';
        } else {
            // Update primary slug
            $query = "UPDATE profiles SET slug = ? WHERE user_id = ? AND is_primary = 1";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $new_slug, $current_user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Update users.page_slug for backward compatibility
                $query2 = "UPDATE users SET page_slug = ?, last_slug_change_at = NOW() WHERE user_id = ?";
                $stmt2 = mysqli_prepare($conn, $query2);
                mysqli_stmt_bind_param($stmt2, 'si', $new_slug, $current_user_id);
                mysqli_stmt_execute($stmt2);
                
                // Mark OTP as used
                $query3 = "UPDATE email_verifications SET is_used = 1 WHERE id = ?";
                $stmt3 = mysqli_prepare($conn, $query3);
                mysqli_stmt_bind_param($stmt3, 'i', $verification['id']);
                mysqli_stmt_execute($stmt3);
                
                // Update session
                $_SESSION['page_slug'] = $new_slug;
                unset($_SESSION['pending_slug_change']);
                
                $success = "Slug berhasil diubah menjadi: {$new_slug}";
                
                // Refresh user data
                $user = get_single_row("SELECT * FROM users WHERE user_id = ?", [$current_user_id], 'i');
            } else {
                $error = 'Gagal mengubah slug!';
            }
        }
    }
}

// Add new slug (max 2 total)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slug'])) {
    $new_slug = trim($_POST['new_slug']);
    
    // Sanitize slug
    $new_slug = strtolower($new_slug);
    $new_slug = preg_replace('/[^a-z0-9-]/', '', $new_slug);
    
    if (strlen($new_slug) < 3 || strlen($new_slug) > 50) {
        $error = 'Slug harus 3-50 karakter, hanya huruf, angka, dan tanda hubung!';
    } else {
        // Check user's current slug count
        $slug_count = get_single_row(
            "SELECT COUNT(*) as count FROM profiles WHERE user_id = ?",
            [$current_user_id],
            'i'
        )['count'];
        
        if ($slug_count >= 2) {
            $error = 'Anda sudah memiliki 2 slug (maksimal untuk akun gratis)!';
        } else {
            // Check availability
            $existing = get_single_row("SELECT profile_id FROM profiles WHERE slug = ?", [$new_slug], 's');
            
            if ($existing) {
                $error = 'Slug sudah digunakan!';
            } else {
                // Add new profile with slug
                $profile_name = ucfirst($new_slug) . ' Profile';
                $query = "INSERT INTO profiles (user_id, slug, profile_name, is_primary, is_active) VALUES (?, ?, ?, 0, 1)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'iss', $current_user_id, $new_slug, $profile_name);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = "Profile baru '{$new_slug}' berhasil ditambahkan!";
                } else {
                    $error = 'Gagal menambahkan profile!';
                }
            }
        }
    }
}

// Delete profile (cannot delete primary)
if (isset($_GET['delete_slug'])) {
    $profile_id = intval($_GET['delete_slug']);
    
    // Check if it's not primary and belongs to user
    $profile_data = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$profile_id, $current_user_id],
        'ii'
    );
    
    if (!$profile_data) {
        $error = 'Profile tidak ditemukan!';
    } elseif ($profile_data['is_primary'] == 1) {
        $error = 'Tidak bisa menghapus profile utama! Ganti ke profile lain sebagai utama terlebih dahulu.';
    } else {
        $query = "DELETE FROM profiles WHERE profile_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $profile_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Profile '{$profile_data['slug']}' berhasil dihapus!";
        } else {
            $error = 'Gagal menghapus profile!';
        }
    }
}

// Set primary profile
if (isset($_GET['set_primary'])) {
    $profile_id = intval($_GET['set_primary']);
    
    // Check if profile belongs to user
    $profile_data = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$profile_id, $current_user_id],
        'ii'
    );
    
    if (!$profile_data) {
        $error = 'Profile tidak ditemukan!';
    } else {
        // Unset all primary flags for this user
        $query1 = "UPDATE profiles SET is_primary = 0 WHERE user_id = ?";
        $stmt1 = mysqli_prepare($conn, $query1);
        mysqli_stmt_bind_param($stmt1, 'i', $current_user_id);
        mysqli_stmt_execute($stmt1);
        
        // Set new primary
        $query2 = "UPDATE profiles SET is_primary = 1 WHERE profile_id = ? AND user_id = ?";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, 'ii', $profile_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt2)) {
            // Update users.page_slug and active_profile_id
            $query3 = "UPDATE users SET page_slug = ?, active_profile_id = ? WHERE user_id = ?";
            $stmt3 = mysqli_prepare($conn, $query3);
            mysqli_stmt_bind_param($stmt3, 'sii', $profile_data['slug'], $profile_id, $current_user_id);
            mysqli_stmt_execute($stmt3);
            
            // Update session
            $_SESSION['page_slug'] = $profile_data['slug'];
            $_SESSION['active_profile_id'] = $profile_id;
            
            $success = "Profile '{$profile_data['slug']}' sekarang menjadi profile utama!";
            
            // Refresh user data
            $user = get_single_row("SELECT * FROM users WHERE user_id = ?", [$current_user_id], 'i');
        } else {
            $error = 'Gagal mengubah profile utama!';
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

// Get user's all slugs
$user_slugs = [];
$slugs_query = "SELECT * FROM user_slugs WHERE user_id = ? ORDER BY is_primary DESC, created_at ASC";
$slugs_stmt = mysqli_prepare($conn, $slugs_query);
mysqli_stmt_bind_param($slugs_stmt, 'i', $current_user_id);
mysqli_stmt_execute($slugs_stmt);
$slugs_result = mysqli_stmt_get_result($slugs_stmt);
while ($row = mysqli_fetch_assoc($slugs_result)) {
    $user_slugs[] = $row;
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
    <link href="../assets/css/admin.css" rel="stylesheet">
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
    <nav class="navbar navbar-expand-lg navbar-custom">
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
                        <a class="nav-link" href="../<?= $current_page_slug ?>" target="_blank">
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
                
                <!-- Change Primary Slug -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-link-45deg"></i> Ganti Slug Utama
                            <span class="badge bg-warning text-dark ms-2">Verifikasi OTP</span>
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Slug saat ini:</strong> 
                            <code class="bg-white px-2 py-1 rounded"><?= htmlspecialchars($user['page_slug']) ?></code>
                            <br>
                            <small class="text-muted">
                                Link profil Anda: <strong>linkmy.iet.ovh/<?= htmlspecialchars($user['page_slug']) ?></strong>
                            </small>
                        </div>
                        
                        <?php if ($user['last_slug_change_at']): 
                            $days_since = (time() - strtotime($user['last_slug_change_at'])) / (60 * 60 * 24);
                            $days_left = max(0, ceil(30 - $days_since));
                        ?>
                            <?php if ($days_left > 0): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-clock-fill me-2"></i>
                                    Anda bisa mengganti slug lagi dalam <strong><?= $days_left ?> hari</strong>.
                                    <br>
                                    <small>Terakhir diubah: <?= date('d M Y', strtotime($user['last_slug_change_at'])) ?></small>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if (!isset($_SESSION['pending_slug_change'])): ?>
                        <form method="POST" id="requestSlugChangeForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Slug Baru</label>
                                <input type="text" class="form-control" name="new_slug" id="new_slug_input"
                                       placeholder="contoh: nama-anda" pattern="[a-z0-9-]+" 
                                       minlength="3" maxlength="50" required>
                                <div id="slug_check_feedback" class="form-text"></div>
                                <small class="text-muted">
                                    Hanya huruf kecil, angka, dan tanda hubung (-). Minimal 3 karakter.
                                </small>
                            </div>
                            
                            <button type="submit" name="request_slug_change" class="btn btn-primary" 
                                    id="requestSlugBtn" disabled>
                                <i class="bi bi-send"></i> Kirim Kode OTP
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-success">
                            <i class="bi bi-envelope-check-fill me-2"></i>
                            Kode OTP telah dikirim ke email Anda! Silakan masukkan kode di bawah.
                        </div>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode OTP (6 digit)</label>
                                <input type="text" class="form-control" name="otp_code" 
                                       placeholder="123456" pattern="[0-9]{6}" maxlength="6" required>
                                <small class="text-muted">Cek email Anda untuk kode OTP.</small>
                            </div>
                            
                            <button type="submit" name="verify_slug_change" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Verifikasi & Ganti Slug
                            </button>
                            <a href="settings.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Manage Multiple Slugs -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-collection"></i> Kelola Slug Anda
                            <span class="badge bg-info ms-2">Gratis: 2 Slug</span>
                        </h5>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <strong>Fitur Multi-Slug:</strong> Anda bisa membuat hingga 2 slug yang mengarah ke profil yang sama!
                            <br>
                            <small>Contoh: <code>fahmi</code> dan <code>fahmi-portfolio</code> untuk berbagai keperluan.</small>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Profile Anda (<?= count($user_profiles) ?>/2):</h6>
                            
                            <?php if (empty($user_profiles)): ?>
                                <p class="text-muted">Belum ada profile. Silakan tambahkan profile pertama Anda!</p>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($user_profiles as $profile): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <code class="fs-5"><?= htmlspecialchars($profile['slug']) ?></code>
                                                <?php if ($profile['is_primary']): ?>
                                                    <span class="badge bg-success ms-2">Profile Utama</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary ms-2">Profile Tambahan</span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted">
                                                    Dibuat: <?= date('d M Y', strtotime($profile['created_at'])) ?>
                                                    | Link: <strong>linkmy.iet.ovh/<?= htmlspecialchars($profile['slug']) ?></strong>
                                                </small>
                                            </div>
                                            <div>
                                                <?php if (!$profile['is_primary']): ?>
                                                    <a href="?set_primary=<?= $profile['profile_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       onclick="return confirm('Jadikan <?= htmlspecialchars($profile['slug']) ?> sebagai profile utama?')">
                                                        <i class="bi bi-star"></i> Jadikan Utama
                                                    </a>
                                                    <a href="?delete_slug=<?= $profile['profile_id'] ?>" 
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Hapus profile <?= htmlspecialchars($profile['slug']) ?>?')">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </a>
                                                <?php else: ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-star-fill"></i> Aktif
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (count($user_profiles) < 2): ?>
                        <hr>
                        <h6 class="fw-bold mb-3">Tambah Slug Baru:</h6>
                        <form method="POST" id="addSlugForm">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Slug Baru</label>
                                <input type="text" class="form-control" name="new_slug" id="add_slug_input"
                                       placeholder="contoh: nama-bisnis" pattern="[a-z0-9-]+" 
                                       minlength="3" maxlength="50" required>
                                <div id="add_slug_feedback" class="form-text"></div>
                                <small class="text-muted">
                                    Slug alias akan mengarah ke profil yang sama dengan slug utama.
                                </small>
                            </div>
                            
                            <button type="submit" name="add_slug" class="btn btn-success" 
                                    id="addSlugBtn" disabled>
                                <i class="bi bi-plus-circle"></i> Tambah Slug
                            </button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Anda sudah mencapai batas maksimal 2 slug untuk akun gratis.
                        </div>
                        <?php endif; ?>
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
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script>
        // Debounce function to limit API calls
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        
        // Check slug availability via AJAX
        function checkSlugAvailability(slug, feedbackElement, buttonElement) {
            if (slug.length < 3) {
                feedbackElement.innerHTML = '<span class="text-muted">Minimal 3 karakter</span>';
                buttonElement.disabled = true;
                return;
            }
            
            // Sanitize slug client-side
            slug = slug.toLowerCase().replace(/[^a-z0-9-]/g, '');
            
            feedbackElement.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Memeriksa...</span>';
            buttonElement.disabled = true;
            
            $.ajax({
                url: 'settings.php',
                method: 'POST',
                data: {
                    action: 'check_slug_availability',
                    slug: slug
                },
                dataType: 'json',
                success: function(response) {
                    if (response.available) {
                        feedbackElement.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> ' + response.message + '</span>';
                        buttonElement.disabled = false;
                    } else {
                        feedbackElement.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + response.message + '</span>';
                        buttonElement.disabled = true;
                    }
                },
                error: function() {
                    feedbackElement.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Gagal memeriksa ketersediaan</span>';
                    buttonElement.disabled = true;
                }
            });
        }
        
        // Debounced slug check for "Change Slug" form
        const debouncedCheckNewSlug = debounce(function() {
            const slug = $('#new_slug_input').val();
            const feedback = document.getElementById('slug_check_feedback');
            const button = document.getElementById('requestSlugBtn');
            checkSlugAvailability(slug, feedback, button);
        }, 500);
        
        // Debounced slug check for "Add Slug" form
        const debouncedCheckAddSlug = debounce(function() {
            const slug = $('#add_slug_input').val();
            const feedback = document.getElementById('add_slug_feedback');
            const button = document.getElementById('addSlugBtn');
            checkSlugAvailability(slug, feedback, button);
        }, 500);
        
        // Attach event listeners
        $(document).ready(function() {
            // For "Change Slug" form
            $('#new_slug_input').on('input', function() {
                // Auto-sanitize input in real-time
                let val = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '');
                $(this).val(val);
                debouncedCheckNewSlug();
            });
            
            // For "Add Slug" form
            $('#add_slug_input').on('input', function() {
                // Auto-sanitize input in real-time
                let val = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '');
                $(this).val(val);
                debouncedCheckAddSlug();
            });
            
            // Form submission validation
            $('#requestSlugChangeForm').on('submit', function(e) {
                const button = document.getElementById('requestSlugBtn');
                if (button.disabled) {
                    e.preventDefault();
                    alert('Slug tidak tersedia atau tidak valid!');
                    return false;
                }
            });
            
            $('#addSlugForm').on('submit', function(e) {
                const button = document.getElementById('addSlugBtn');
                if (button.disabled) {
                    e.preventDefault();
                    alert('Slug tidak tersedia atau tidak valid!');
                    return false;
                }
            });
        });
    </script>
</body>
</html>