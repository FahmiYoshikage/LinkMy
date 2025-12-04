<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

$user = get_single_row("SELECT * FROM users WHERE user_id = ?", [$current_user_id], 'i');

// Get user's profiles
$user_profiles = [];
$profiles_query = "SELECT * FROM profiles WHERE user_id = ? ORDER BY display_order ASC, created_at ASC";
$profiles_stmt = mysqli_prepare($conn, $profiles_query);
mysqli_stmt_bind_param($profiles_stmt, 'i', $current_user_id);
mysqli_stmt_execute($profiles_stmt);
$profiles_result = mysqli_stmt_get_result($profiles_stmt);
while ($row = mysqli_fetch_assoc($profiles_result)) {
    $user_profiles[] = $row;
}

// Change password
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

// Update email
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

// Delete account
if (isset($_GET['delete_account']) && $_GET['delete_account'] === 'confirm') {
    // Delete all user data
    $queries = [
        "DELETE FROM links WHERE user_id = ?",
        "DELETE FROM link_categories WHERE user_id = ?",
        "DELETE FROM user_appearance WHERE user_id = ?",
        "DELETE FROM profiles WHERE user_id = ?",
        "DELETE FROM users WHERE user_id = ?"
    ];
    
    foreach ($queries as $query) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
        mysqli_stmt_execute($stmt);
    }
    
    session_destroy();
    header('Location: ../index.php?account_deleted=1');
    exit;
}

// Get stats
$total_links = get_single_row("SELECT COUNT(*) as count FROM links WHERE user_id = ?", [$current_user_id], 'i')['count'];
$total_clicks = get_single_row("SELECT SUM(click_count) as total FROM links WHERE user_id = ?", [$current_user_id], 'i')['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - LinkMy</title>
    
    <link rel="stylesheet" href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <style>
        .settings-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        .settings-card .card-header {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.25rem;
        }
        .stat-card {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../partials/admin_nav.php'; ?>
    
    <div class="container mt-4 mb-5">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <h2 class="mb-4"><i class="bi bi-gear-fill"></i> Account Settings</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Account Stats -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= count($user_profiles) ?></div>
                            <div>Total Profiles</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= $total_links ?></div>
                            <div>Total Links</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="stat-card">
                            <div class="stat-number"><?= number_format($total_clicks) ?></div>
                            <div>Total Clicks</div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Management (redirect to profiles.php) -->
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-collection"></i> Multi-Profile Management</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            <strong>Fitur Multi-Profile:</strong> Kelola hingga 2 profil dengan tampilan dan konten yang berbeda!
                        </div>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold">Your Profiles (<?= count($user_profiles) ?>/2):</h6>
                            <div class="list-group">
                                <?php foreach ($user_profiles as $profile): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <?= $profile['display_order'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-circle"></i>' ?>
                                            <strong><?= htmlspecialchars($profile['name']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Slug: <code><?= htmlspecialchars($profile['slug']) ?></code> | 
                                                Link: <strong>linkmy.iet.ovh/<?= htmlspecialchars($profile['slug']) ?></strong>
                                            </small>
                                        </div>
                                        <?php if ($profile['display_order']): ?>
                                            <span class="badge bg-success">Primary</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <a href="profiles.php" class="btn btn-primary">
                            <i class="bi bi-person-badge"></i> Manage Profiles
                        </a>
                        <p class="text-muted mt-2 mb-0">
                            <small>Create, edit, delete, and switch between profiles. Each profile has independent appearance and content.</small>
                        </p>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-person-circle"></i> Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <form method="POST" class="d-flex gap-2">
                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                    <button type="submit" name="update_email" class="btn btn-primary">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Created</label>
                                <input type="text" class="form-control" value="<?= date('d M Y', strtotime($user['created_at'])) ?>" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Account Status</label>
                                <input type="text" class="form-control" value="<?= $user['email_verified'] ? 'âœ“ Verified' : 'âœ— Not Verified' ?>" disabled>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="card settings-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" name="new_password" minlength="6" required>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Delete Account (Danger Zone) -->
                <div class="card border-danger settings-card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Danger Zone</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-danger fw-bold">Delete Account</h6>
                        <p class="text-muted">
                            Once you delete your account, there is no going back. This will permanently delete:
                        </p>
                        <ul class="text-muted">
                            <li>All your profiles (<?= count($user_profiles) ?>)</li>
                            <li>All your links (<?= $total_links ?>)</li>
                            <li>All analytics data (<?= number_format($total_clicks) ?> total clicks)</li>
                            <li>All appearance settings</li>
                        </ul>
                        
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                            <i class="bi bi-trash"></i> Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Account Confirmation Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Account Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="fw-bold">Are you absolutely sure?</p>
                    <p>This action <strong>CANNOT</strong> be undone. This will permanently delete:</p>
                    <ul>
                        <li><?= count($user_profiles) ?> profile(s)</li>
                        <li><?= $total_links ?> link(s)</li>
                        <li><?= number_format($total_clicks) ?> click(s) data</li>
                    </ul>
                    <p class="text-danger">Please type your username <code><?= htmlspecialchars($user['username']) ?></code> to confirm:</p>
                    <input type="text" class="form-control" id="deleteConfirmInput" placeholder="Type username here">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="?delete_account=confirm" class="btn btn-danger" id="confirmDeleteBtn" onclick="return confirmDelete()">
                        Delete My Account
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete() {
            const input = document.getElementById('deleteConfirmInput').value;
            const username = '<?= htmlspecialchars($user['username']) ?>';
            
            if (input !== username) {
                alert('Username tidak cocok! Ketik username Anda untuk konfirmasi.');
                return false;
            }
            
            return confirm('LAST WARNING: This will permanently delete your account and all data. Continue?');
        }
        
        // Disable delete button until username is typed correctly
        document.getElementById('deleteConfirmInput').addEventListener('input', function() {
            const input = this.value;
            const username = '<?= htmlspecialchars($user['username']) ?>';
            const btn = document.getElementById('confirmDeleteBtn');
            
            if (input === username) {
                btn.classList.remove('disabled');
                btn.style.pointerEvents = 'auto';
            } else {
                btn.classList.add('disabled');
                btn.style.pointerEvents = 'none';
            }
        });
    </script>
</body>
</html>



