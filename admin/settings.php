<?php
// Prevent caching to always show fresh data
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

// Check for session success message
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Helper function to check if profile page is accessible
function check_profile_accessibility($slug) {
    // Direct query check - more efficient than curl
    global $conn;
    $profile_check = get_single_row(
        "SELECT is_active FROM profiles WHERE slug = ? LIMIT 1",
        [$slug],
        's'
    );
    if (!$profile_check) {
        return false; // Profile not found
    }
    return (int)$profile_check['is_active'] === 1;
}

// Get user data with primary profile slug
$query = "SELECT u.*, p.slug as page_slug FROM users u LEFT JOIN profiles p ON u.id = p.user_id AND p.display_order = 0 WHERE u.id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if (!$user) {
    die('User not found!');
}

// Get all user profiles for slug management with stats (always fresh from DB)
$all_profiles = [];
$user_profiles = [];

$p_query = "SELECT 
            p.id, 
            p.user_id, 
            p.slug, 
            p.name, 
            p.display_order, 
            p.is_active, 
            p.created_at,
            COUNT(l.id) as link_count,
            COALESCE(SUM(l.clicks), 0) as total_clicks
            FROM profiles p 
            LEFT JOIN links l ON l.profile_id = p.id
            WHERE p.user_id = {$current_user_id} 
            GROUP BY p.id, p.user_id, p.slug, p.name, p.display_order, p.is_active, p.created_at
            ORDER BY p.display_order ASC";

$p_result = mysqli_query($conn, $p_query);

if (!$p_result) {
    die("Query error: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($p_result)) {
    $all_profiles[] = $row;
    $user_profiles[] = $row;
}

// Backup data to prevent corruption during render
$BACKUP_user_profiles = $user_profiles;
$BACKUP_all_profiles = $all_profiles;

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
        // Handle both 'password' and 'password_hash' column names
        $password_field = $user['password'] ?? $user['password_hash'];
        if (password_verify($current_password, $password_field)) {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Check which column exists in database
            $check_col = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'password_hash'");
            $has_password_hash = ($check_col && mysqli_num_rows($check_col) > 0);
            
            $column_name = $has_password_hash ? 'password_hash' : 'password';
            $query = "UPDATE users SET {$column_name} = ? WHERE id = ?";
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
    $confirm_password = $_POST['confirm_password_email'] ?? '';
    
    if (empty($new_email) || !filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid!';
    } elseif (empty($confirm_password)) {
        $error = 'Password konfirmasi harus diisi untuk keamanan!';
    } else {
        // Verify password before allowing email change
        $password_field = $user['password'] ?? $user['password_hash'];
        if (!password_verify($confirm_password, $password_field)) {
            $error = 'Password salah! Email tidak dapat diubah.';
        } else {
            // Check if email already used by another user
            $check_email = get_single_row(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$new_email, $current_user_id],
                'si'
            );
            
            if ($check_email) {
                $error = 'Email sudah digunakan oleh user lain!';
            } else {
                $query = "UPDATE users SET email = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'si', $new_email, $current_user_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success = 'Email berhasil diupdate! Silakan login kembali dengan email baru.';
                    $user['email'] = $new_email;
                } else {
                    $error = 'Gagal mengupdate email!';
                }
            }
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
        "SELECT p.id, p.user_id FROM profiles p WHERE p.slug = ? AND p.user_id != ?", 
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
        // Cooldown check removed for v3 compatibility
        
        if (!$error) {
            // Check availability
            $existing = get_single_row("SELECT id FROM profiles WHERE slug = ?", [$new_slug], 's');
            
            if ($existing) {
                $error = 'Slug sudah digunakan orang lain!';
            } else {
                // Generate OTP
                $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                
                // Store OTP
                $query = "INSERT INTO email_verifications (email, otp, expires_at, is_used, type, ip) 
                              VALUES (?, ?, ?, 0, 'slug_change', ?)";
                $ip = $_SERVER['REMOTE_ADDR'];
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'ssss', $user['email'], $otp, $expires_at, $ip);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Send OTP email using custom mailer
                    $mail = create_mailer();
                    
                    if ($mail) {
                        try {
                            // Use email part as username if username column doesn't exist
                            $display_name = isset($user['username']) ? $user['username'] : explode('@', $user['email'])[0];
                            
                            $mail->addAddress($user['email'], $display_name);
                            $mail->isHTML(true);
                            $mail->Subject = "Konfirmasi Perubahan Slug - LinkMy";
                            $mail->Body = "
                                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                                    <h2 style='color: #0ea5e9;'>Konfirmasi Perubahan Slug</h2>
                                    <p>Halo <strong>{$display_name}</strong>,</p>
                                    <p>Anda meminta untuk mengganti slug profil menjadi <strong>{$new_slug}</strong>.</p>
                                    <p>Kode OTP Anda adalah:</p>
                                    <div style='background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); color: white; padding: 20px; text-align: center; border-radius: 10px; font-size: 32px; font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
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
                                $_SESSION['pending_slug_change_id'] = intval($_POST['target_profile_id']);
                                $_SESSION['slug_change_scroll'] = true; // Keep scroll position
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
        $target_profile_id = $_SESSION['pending_slug_change_id'] ?? 0;
        
        // Verify OTP
        $verification = get_single_row(
            "SELECT * FROM email_verifications 
             WHERE email = ? AND otp = ? AND is_used = 0 
             AND expires_at > NOW() AND type = 'slug_change'
             ORDER BY id DESC LIMIT 1",
            [$user['email'], $otp],
            'ss'
        );
        
        if (!$verification) {
            $error = 'Kode OTP salah atau sudah kadaluarsa!';
        } else {
            // Update slug for specific profile
            if ($target_profile_id > 0) {
                $query = "UPDATE profiles SET slug = ? WHERE id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'sii', $new_slug, $target_profile_id, $current_user_id);
            } else {
                // Fallback to primary if ID missing (should not happen)
                $query = "UPDATE profiles SET slug = ? WHERE user_id = ? AND display_order = 0";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'si', $new_slug, $current_user_id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                // Removed last_slug_change_at update for v3 compatibility
                
                // Mark OTP as used
                $query3 = "UPDATE email_verifications SET is_used = 1 WHERE id = ?";
                $stmt3 = mysqli_prepare($conn, $query3);
                mysqli_stmt_bind_param($stmt3, 'i', $verification['id']);
                mysqli_stmt_execute($stmt3);
                
                // Update session
                if ($target_profile_id > 0) {
                    // Check if we updated the primary profile
                    $updated_profile = get_single_row("SELECT display_order FROM profiles WHERE id = ?", [$target_profile_id], 'i');
                    if ($updated_profile && $updated_profile['display_order'] == 0) {
                        $_SESSION['page_slug'] = $new_slug;
                    }
                } else {
                    $_SESSION['page_slug'] = $new_slug;
                }
                
                unset($_SESSION['pending_slug_change']);
                unset($_SESSION['pending_slug_change_id']);
                
                $success = "Slug berhasil diubah menjadi: {$new_slug}";
                
                // Refresh user data
                $query = "SELECT u.*, p.slug as page_slug FROM users u LEFT JOIN profiles p ON u.id = p.user_id AND p.display_order = 0 WHERE u.id = ? LIMIT 1";
                $user = get_single_row($query, [$current_user_id], 'i');
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
            $existing = get_single_row("SELECT id FROM profiles WHERE slug = ?", [$new_slug], 's');
            
            if ($existing) {
                $error = 'Slug sudah digunakan!';
            } else {
                // Add new profile with slug
                $name = ucfirst($new_slug) . ' Profile';
                $query = "INSERT INTO profiles (user_id, slug, name, display_order, is_active) VALUES (?, ?, ?, 0, 1)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'iss', $current_user_id, $new_slug, $name);
                
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
    $id = intval($_GET['delete_slug']);
    
    // Check if it's not primary and belongs to user
    $profile_data = get_single_row(
        "SELECT * FROM profiles WHERE id = ? AND user_id = ?",
        [$id, $current_user_id],
        'ii'
    );
    
    if (!$profile_data) {
        $error = 'Profile tidak ditemukan!';
    } elseif ($profile_data['display_order'] == 0) {
        $error = 'Tidak bisa menghapus profile utama! Ganti ke profile lain sebagai utama terlebih dahulu.';
    } else {
        $query = "DELETE FROM profiles WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Profile '{$profile_data['slug']}' berhasil dihapus!";
        } else {
            $error = 'Gagal menghapus profile!';
        }
    }
}

// Toggle profile active/inactive status
if (isset($_GET['toggle_active'])) {
    $id = intval($_GET['toggle_active']);
    
    // Check if profile belongs to user
    $profile_data = get_single_row(
        "SELECT * FROM profiles WHERE id = ? AND user_id = ?",
        [$id, $current_user_id],
        'ii'
    );
    
    if (!$profile_data) {
        $error = 'Profile tidak ditemukan!';
    } else {
        // Toggle is_active status (1 -> 0 or 0 -> 1)
        $new_status = $profile_data['is_active'] ? 0 : 1;
        
        $query = "UPDATE profiles SET is_active = ? WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'iii', $new_status, $id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $status_text = $new_status ? 'diaktifkan' : 'dinonaktifkan';
            $_SESSION['success_message'] = "Profile '{$profile_data['slug']}' berhasil {$status_text}!";
            
            // Redirect to avoid form resubmission and ensure clean URL
            header("Location: settings.php");
            exit();
        } else {
            $error = 'Gagal mengubah status profile!';
        }
    }
}

if (isset($_GET['delete_account']) && $_GET['delete_account'] === 'confirm') {
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        session_destroy();
        header('Location: ../index.php?account_deleted=1');
        exit;
    }
}

// Use data already loaded at the top with stats
// Data already populated in $user_profiles from lines 30-48
$user_slugs = $user_profiles;

// Totals across user's profiles
$total_links = get_single_row("SELECT COUNT(*) as count FROM links WHERE profile_id IN (SELECT id FROM profiles WHERE user_id = ?)", [$current_user_id], 'i')['count'];
$total_clicks = get_single_row("SELECT COALESCE(SUM(clicks), 0) as total FROM links WHERE profile_id IN (SELECT id FROM profiles WHERE user_id = ?)", [$current_user_id], 'i')['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - LinkMy</title>
    <!-- Disable Cloudflare optimizations that might corrupt data -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script>if(typeof Rocketloader !== 'undefined'){Rocketloader.stop();}</script>
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        body {
            padding-top: 76px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .stat-box {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
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
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="fw-bold">Pengaturan Akun</h1>
                <p class="text-muted">Kelola informasi akun, profil, dan keamanan Anda.</p>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Profile Management Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-people-fill me-2"></i>Kelola Profil</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Anda dapat membuat beberapa profil di bawah satu akun. Setiap profil memiliki link dan tampilannya sendiri.</p>
                        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addProfileModal">
                            <i class="bi bi-plus-circle me-2"></i>Buat Profil Baru
                        </button>
                        <div class="list-group">
                            <?php foreach ($user_profiles as $profile): ?>
                                <div class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold">
                                            <?= htmlspecialchars($profile['name']) ?>
                                            <?php if ($profile['display_order'] == 0): ?>
                                                <span class="badge bg-warning text-dark ms-2">Utama</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small>Dibuat: <?= date('d M Y', strtotime($profile['created_at'])) ?></small>
                                    </div>
                                    <p class="mb-1">Slug: <a href="../<?= htmlspecialchars($profile['slug']) ?>" target="_blank">/<?= htmlspecialchars($profile['slug']) ?></a></p>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div>
                                            <span class="badge bg-light text-dark border me-2"><i class="bi bi-link-45deg"></i> <?= $profile['link_count'] ?> Links</span>
                                            <span class="badge bg-light text-dark border"><i class="bi bi-graph-up"></i> <?= $profile['total_clicks'] ?> Clicks</span>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-secondary me-1" onclick="openEditProfileModal(<?= htmlspecialchars(json_encode($profile), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="bi bi-pencil-fill"></i> Edit
                                            </button>
                                            <?php if ($profile['display_order'] != 0): ?>
                                                <a href="../scripts/delete_profile.php?id=<?= $profile['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus profil ini? Semua link di dalamnya akan ikut terhapus.')">
                                                    <i class="bi bi-trash-fill"></i> Hapus
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Account Security Card -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2"></i>Keamanan Akun</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="securityTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email-pane" type="button">Ubah Email</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button">Ubah Password</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="email-pane" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Email Saat Ini</label>
                                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Baru</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password_email" class="form-label">Konfirmasi Password Anda</label>
                                        <input type="password" class="form-control" id="confirm_password_email" name="confirm_password_email" required>
                                    </div>
                                    <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="password-pane" role="tabpanel">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Password Lama</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="change_password" class="btn btn-primary">Ganti Password</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Profile Modal -->
    <div class="modal fade" id="addProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../scripts/add_profile.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Buat Profil Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="add_profile_name" class="form-label">Nama Profil</label>
                            <input type="text" class="form-control" id="add_profile_name" name="profile_name" required placeholder="Contoh: Profil Bisnis">
                        </div>
                        <div class="mb-3">
                            <label for="add_profile_slug" class="form-label">Slug Profil</label>
                            <input type="text" class="form-control" id="add_profile_slug" name="profile_slug" required placeholder="contoh-slug-unik">
                            <div id="addSlugFeedback" class="form-text"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../scripts/edit_profile.php" method="POST">
                    <input type="hidden" name="profile_id" id="edit_profile_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_profile_name" class="form-label">Nama Profil</label>
                            <input type="text" class="form-control" id="edit_profile_name" name="profile_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_profile_slug" class="form-label">Slug Profil</label>
                            <input type="text" class="form-control" id="edit_profile_slug" name="profile_slug" required>
                            <div id="editSlugFeedback" class="form-text"></div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="set_as_primary" name="set_as_primary">
                            <label class="form-check-label" for="set_as_primary">
                                Jadikan sebagai profil utama
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../partials/admin_footer.php'; ?>

    <script>
    function openEditProfileModal(profile) {
        document.getElementById('edit_profile_id').value = profile.id;
        document.getElementById('edit_profile_name').value = profile.name;
        document.getElementById('edit_profile_slug').value = profile.slug;
        document.getElementById('set_as_primary').checked = profile.display_order == 0;
        document.getElementById('set_as_primary').disabled = profile.display_order == 0;
        
        var myModal = new bootstrap.Modal(document.getElementById('editProfileModal'), {});
        myModal.show();
    }
    // Add slug validation script if needed
    </script>
</body>
</html>
