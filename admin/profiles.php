<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

// Get current user's profiles with stats - using simple approach
$user_profiles = [];
$query = "SELECT p.* FROM profiles p WHERE p.user_id = ? ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    // Get link count
    $link_query = "SELECT COUNT(*) as count, COALESCE(SUM(click_count), 0) as clicks FROM links WHERE profile_id = ?";
    $link_stmt = mysqli_prepare($conn, $link_query);
    mysqli_stmt_bind_param($link_stmt, 'i', $row['profile_id']);
    mysqli_stmt_execute($link_stmt);
    $link_result = mysqli_stmt_get_result($link_stmt);
    $link_data = mysqli_fetch_assoc($link_result);
    
    $row['link_count'] = intval($link_data['count']);
    $row['total_clicks'] = intval($link_data['clicks']);
    
    $user_profiles[] = $row;
}

// Get active profile
$active_profile_id = $_SESSION['active_profile_id'] ?? null;
if (!$active_profile_id && !empty($user_profiles)) {
    // Set to primary profile
    foreach ($user_profiles as $prof) {
        if ($prof['is_primary']) {
            $active_profile_id = $prof['profile_id'];
            $_SESSION['active_profile_id'] = $active_profile_id;
            break;
        }
    }
}

// HANDLER: Switch active profile
if (isset($_GET['switch_profile'])) {
    $profile_id = intval($_GET['switch_profile']);
    
    // Verify profile belongs to user
    $profile = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$profile_id, $current_user_id],
        'ii'
    );
    
    if ($profile) {
        $_SESSION['active_profile_id'] = $profile_id;
        $_SESSION['page_slug'] = $profile['slug'];
        
        // Update user's active_profile_id
        $update_query = "UPDATE users SET active_profile_id = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'ii', $profile_id, $current_user_id);
        mysqli_stmt_execute($stmt);
        
        $success = "Beralih ke profil: {$profile['profile_name']}";
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Profil tidak ditemukan!";
    }
}

// HANDLER: Create new profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_profile'])) {
    $profile_name = trim($_POST['profile_name']);
    $slug = trim($_POST['slug']);
    $profile_description = trim($_POST['profile_description'] ?? '');
    
    // Sanitize slug
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    if (empty($profile_name) || empty($slug)) {
        $error = "Nama profil dan slug harus diisi!";
    } elseif (strlen($slug) < 3 || strlen($slug) > 50) {
        $error = "Slug harus 3-50 karakter!";
    } else {
        // Check profile count limit (max 2 for free)
        $count = get_single_row(
            "SELECT COUNT(*) as count FROM profiles WHERE user_id = ?",
            [$current_user_id],
            'i'
        )['count'];
        
        if ($count >= 2) {
            $error = "Maksimal 2 profil untuk akun gratis!";
        } else {
            // Check slug availability
            $existing = get_single_row(
                "SELECT profile_id FROM profiles WHERE slug = ?",
                [$slug],
                's'
            );
            
            if ($existing) {
                $error = "Slug sudah digunakan!";
            } else {
                // Create profile
                $query = "INSERT INTO profiles (user_id, slug, profile_name, profile_description, is_primary) 
                          VALUES (?, ?, ?, ?, 0)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'isss', $current_user_id, $slug, $profile_name, $profile_description);
                
                if (mysqli_stmt_execute($stmt)) {
                    $new_profile_id = mysqli_insert_id($conn);
                    
                    // Create default appearance for new profile
                    $app_query = "INSERT INTO user_appearance (user_id, profile_id) VALUES (?, ?)";
                    $app_stmt = mysqli_prepare($conn, $app_query);
                    mysqli_stmt_bind_param($app_stmt, 'ii', $current_user_id, $new_profile_id);
                    mysqli_stmt_execute($app_stmt);
                    
                    // Log activity
                    $log_query = "INSERT INTO profile_activity_log (profile_id, user_id, action_type, ip_address) 
                                  VALUES (?, ?, 'created', ?)";
                    $log_stmt = mysqli_prepare($conn, $log_query);
                    $ip = $_SERVER['REMOTE_ADDR'];
                    mysqli_stmt_bind_param($log_stmt, 'iis', $new_profile_id, $current_user_id, $ip);
                    mysqli_stmt_execute($log_stmt);
                    
                    $success = "Profil '{$profile_name}' berhasil dibuat!";
                    
                    // Reload profiles
                    header("Location: profiles.php?created=1");
                    exit;
                } else {
                    $error = "Gagal membuat profil!";
                }
            }
        }
    }
}

// HANDLER: Delete profile
if (isset($_GET['delete_profile'])) {
    $profile_id = intval($_GET['delete_profile']);
    
    // Get profile data
    $profile = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$profile_id, $current_user_id],
        'ii'
    );
    
    if (!$profile) {
        $error = "Profil tidak ditemukan!";
    } elseif ($profile['is_primary']) {
        $error = "Tidak bisa menghapus profil utama!";
    } else {
        // Delete profile (CASCADE will delete related data)
        $query = "DELETE FROM profiles WHERE profile_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $profile_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // If deleted profile was active, switch to primary
            if ($active_profile_id == $profile_id) {
                $primary = get_single_row(
                    "SELECT profile_id FROM profiles WHERE user_id = ? AND is_primary = 1",
                    [$current_user_id],
                    'i'
                );
                $_SESSION['active_profile_id'] = $primary['profile_id'];
            }
            
            $success = "Profil '{$profile['profile_name']}' berhasil dihapus!";
            header("Location: profiles.php?deleted=1");
            exit;
        } else {
            $error = "Gagal menghapus profil!";
        }
    }
}

// HANDLER: Set primary profile
if (isset($_GET['set_primary_profile'])) {
    $profile_id = intval($_GET['set_primary_profile']);
    
    $profile = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$profile_id, $current_user_id],
        'ii'
    );
    
    if (!$profile) {
        $error = "Profil tidak ditemukan!";
    } else {
        // Unset all primary flags
        $query1 = "UPDATE profiles SET is_primary = 0 WHERE user_id = ?";
        $stmt1 = mysqli_prepare($conn, $query1);
        mysqli_stmt_bind_param($stmt1, 'i', $current_user_id);
        mysqli_stmt_execute($stmt1);
        
        // Set new primary
        $query2 = "UPDATE profiles SET is_primary = 1 WHERE profile_id = ? AND user_id = ?";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, 'ii', $profile_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt2)) {
            // Update users.page_slug (trigger will handle this)
            $success = "'{$profile['profile_name']}' sekarang profil utama!";
            header("Location: profiles.php?primary_changed=1");
            exit;
        } else {
            $error = "Gagal mengubah profil utama!";
        }
    }
}

// HANDLER: Clone profile
if (isset($_GET['clone_profile'])) {
    $source_profile_id = intval($_GET['clone_profile']);
    
    $source_profile = get_single_row(
        "SELECT * FROM profiles WHERE profile_id = ? AND user_id = ?",
        [$source_profile_id, $current_user_id],
        'ii'
    );
    
    if (!$source_profile) {
        $error = "Profil sumber tidak ditemukan!";
    } else {
        // Check limit
        $count = get_single_row(
            "SELECT COUNT(*) as count FROM profiles WHERE user_id = ?",
            [$current_user_id],
            'i'
        )['count'];
        
        if ($count >= 2) {
            $error = "Maksimal 2 profil!";
        } else {
            // Generate new slug
            $new_slug = $source_profile['slug'] . '-copy';
            $counter = 1;
            while (get_single_row("SELECT profile_id FROM profiles WHERE slug = ?", [$new_slug], 's')) {
                $new_slug = $source_profile['slug'] . '-copy' . $counter;
                $counter++;
            }
            
            // Clone profile
            $query = "INSERT INTO profiles (user_id, slug, profile_name, profile_description, profile_title, bio, profile_pic_filename, is_primary)
                      VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = mysqli_prepare($conn, $query);
            $new_name = $source_profile['profile_name'] . ' (Copy)';
            mysqli_stmt_bind_param($stmt, 'issssss', 
                $current_user_id, 
                $new_slug, 
                $new_name,
                $source_profile['profile_description'],
                $source_profile['profile_title'],
                $source_profile['bio'],
                $source_profile['profile_pic_filename']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $new_profile_id = mysqli_insert_id($conn);
                
                // Clone appearance
                $appearance = get_single_row(
                    "SELECT * FROM user_appearance WHERE profile_id = ?",
                    [$source_profile_id],
                    'i'
                );
                
                if ($appearance) {
                    $app_cols = array_keys($appearance);
                    $exclude = ['appearance_id', 'profile_id', 'user_id'];
                    $app_cols = array_diff($app_cols, $exclude);
                    
                    $cols_str = implode(', ', $app_cols);
                    $placeholders = implode(', ', array_fill(0, count($app_cols), '?'));
                    
                    $clone_app_query = "INSERT INTO user_appearance (user_id, profile_id, $cols_str) 
                                        SELECT user_id, ?, $cols_str FROM user_appearance WHERE profile_id = ?";
                    $clone_stmt = mysqli_prepare($conn, $clone_app_query);
                    mysqli_stmt_bind_param($clone_stmt, 'ii', $new_profile_id, $source_profile_id);
                    mysqli_stmt_execute($clone_stmt);
                }
                
                // Clone links
                $clone_links_query = "INSERT INTO links (user_id, profile_id, title, url, order_index, icon_class, category_id)
                                      SELECT user_id, ?, title, url, order_index, icon_class, category_id 
                                      FROM links WHERE profile_id = ?";
                $links_stmt = mysqli_prepare($conn, $clone_links_query);
                mysqli_stmt_bind_param($links_stmt, 'ii', $new_profile_id, $source_profile_id);
                mysqli_stmt_execute($links_stmt);
                
                // Clone categories
                $clone_cats_query = "INSERT INTO link_categories (user_id, profile_id, category_name, category_icon, category_color, display_order)
                                     SELECT user_id, ?, category_name, category_icon, category_color, display_order
                                     FROM link_categories WHERE profile_id = ?";
                $cats_stmt = mysqli_prepare($conn, $clone_cats_query);
                mysqli_stmt_bind_param($cats_stmt, 'ii', $new_profile_id, $source_profile_id);
                mysqli_stmt_execute($cats_stmt);
                
                // Log
                $log_query = "INSERT INTO profile_activity_log (profile_id, user_id, action_type, action_details, ip_address)
                              VALUES (?, ?, 'cloned', ?, ?)";
                $log_stmt = mysqli_prepare($conn, $log_query);
                $details = json_encode(['source_profile_id' => $source_profile_id]);
                $ip = $_SERVER['REMOTE_ADDR'];
                mysqli_stmt_bind_param($log_stmt, 'iiss', $new_profile_id, $current_user_id, $details, $ip);
                mysqli_stmt_execute($log_stmt);
                
                $success = "Profil berhasil di-clone!";
                header("Location: profiles.php?cloned=1");
                exit;
            } else {
                $error = "Gagal clone profil!";
            }
        }
    }
}

// AJAX: Check slug availability for new profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_profile_slug') {
    header('Content-Type: application/json');
    
    $slug = trim($_POST['slug'] ?? '');
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    if (strlen($slug) < 3 || strlen($slug) > 50) {
        echo json_encode(['available' => false, 'message' => 'Slug harus 3-50 karakter']);
        exit;
    }
    
    $existing = get_single_row("SELECT profile_id FROM profiles WHERE slug = ?", [$slug], 's');
    
    if ($existing) {
        echo json_encode(['available' => false, 'message' => 'Slug sudah digunakan']);
    } else {
        echo json_encode(['available' => true, 'message' => 'Slug tersedia!']);
    }
    exit;
}

// Stats already included in the main query above (lines 10-25)
// No need to fetch stats again per profile

$profile_count = count($user_profiles);
$profile_limit = 2; // Free tier limit
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Profil - LinkMy</title>
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            padding-top: 76px;
        }
        .profile-card {
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .profile-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
        }
        .profile-card.primary {
            border-color: #28a745;
        }
        .profile-stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .stat-badge {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/admin_nav.php'; ?>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">
                <i class="bi bi-collection"></i> Kelola Profil
            </h2>
            <?php if ($profile_count < $profile_limit): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProfileModal">
                <i class="bi bi-plus-circle"></i> Buat Profil Baru
            </button>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i>Profil berhasil dibuat!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            <strong>Multi-Profile System:</strong> Kelola hingga 2 profil berbeda dengan tampilan dan konten yang unik!
            <br>
            <small>Profil Anda: <strong><?= $profile_count ?>/<?= $profile_limit ?></strong></small>
        </div>
        
        <div class="row">
            <?php foreach ($user_profiles as $profile): ?>
            <div class="col-md-6 mb-4">
                <div class="card profile-card <?= $profile['profile_id'] == $active_profile_id ? 'active' : '' ?> <?= $profile['is_primary'] ? 'primary' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    <?= htmlspecialchars($profile['profile_name']) ?>
                                    <?php if ($profile['is_primary']): ?>
                                        <span class="badge bg-success ms-2">Utama</span>
                                    <?php endif; ?>
                                    <?php if ($profile['profile_id'] == $active_profile_id): ?>
                                        <span class="badge bg-primary ms-2">Aktif</span>
                                    <?php endif; ?>
                                </h5>
                                <code class="text-muted"><?= htmlspecialchars($profile['slug']) ?></code>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($profile['profile_id'] != $active_profile_id): ?>
                                    <li>
                                        <a class="dropdown-item" href="?switch_profile=<?= $profile['profile_id'] ?>">
                                            <i class="bi bi-arrow-left-right"></i> Aktifkan Profil Ini
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (!$profile['is_primary']): ?>
                                    <li>
                                        <a class="dropdown-item" href="?set_primary_profile=<?= $profile['profile_id'] ?>"
                                           onclick="return confirm('Jadikan <?= htmlspecialchars($profile['profile_name']) ?> sebagai profil utama?')">
                                            <i class="bi bi-star"></i> Jadikan Utama
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($profile_count < $profile_limit): ?>
                                    <li>
                                        <a class="dropdown-item" href="?clone_profile=<?= $profile['profile_id'] ?>"
                                           onclick="return confirm('Clone profil ini?')">
                                            <i class="bi bi-files"></i> Clone Profil
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="../<?= htmlspecialchars($profile['slug']) ?>" target="_blank">
                                            <i class="bi bi-eye"></i> Lihat Profil
                                        </a>
                                    </li>
                                    <?php if (!$profile['is_primary']): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="?delete_profile=<?= $profile['profile_id'] ?>"
                                           onclick="return confirm('Hapus profil <?= htmlspecialchars($profile['profile_name']) ?>? Semua link dan pengaturan akan dihapus!')">
                                            <i class="bi bi-trash"></i> Hapus Profil
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <?php if (!empty($profile['profile_description'])): ?>
                        <p class="text-muted small mb-2">
                            <?= htmlspecialchars($profile['profile_description']) ?>
                        </p>
                        <?php endif; ?>
                        
                        <div class="profile-stats">
                            <div class="stat-badge">
                                <i class="bi bi-link-45deg"></i>
                                <strong><?= $profile['link_count'] ?? 0 ?></strong> Links
                            </div>
                            <div class="stat-badge">
                                <i class="bi bi-cursor-fill"></i>
                                <strong><?= $profile['total_clicks'] ?? 0 ?></strong> Klik
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                Dibuat: <?= date('d M Y', strtotime($profile['created_at'] ?? 'now')) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if ($profile_count < $profile_limit): ?>
            <div class="col-md-6 mb-4">
                <div class="card profile-card border-dashed" data-bs-toggle="modal" data-bs-target="#createProfileModal" style="border-style: dashed !important; min-height: 250px; display: flex; align-items: center; justify-content: center;">
                    <div class="card-body text-center">
                        <i class="bi bi-plus-circle" style="font-size: 3rem; color: #ccc;"></i>
                        <h5 class="mt-3 text-muted">Buat Profil Baru</h5>
                        <p class="text-muted small">Klik untuk membuat profil kedua</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Profile Modal -->
    <div class="modal fade" id="createProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle"></i> Buat Profil Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="createProfileForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Profil</label>
                            <input type="text" class="form-control" name="profile_name" 
                                   placeholder="contoh: Bisnis Saya" required>
                            <small class="text-muted">Nama internal untuk identifikasi (tidak tampil di publik)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug</label>
                            <input type="text" class="form-control" name="slug" id="new_profile_slug"
                                   placeholder="contoh: nama-bisnis" pattern="[a-z0-9-]+" 
                                   minlength="3" maxlength="50" required>
                            <div id="profile_slug_feedback" class="form-text"></div>
                            <small class="text-muted">URL unik untuk profil ini (hanya huruf kecil, angka, dan tanda hubung)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="profile_description" rows="3" 
                                      placeholder="Catatan tentang profil ini..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-lightbulb-fill me-2"></i>
                            Setelah dibuat, Anda bisa mengatur tampilan, links, dan konten secara terpisah untuk profil ini!
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="create_profile" class="btn btn-primary" id="createProfileBtn" disabled>
                            <i class="bi bi-plus-circle"></i> Buat Profil
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script>
        // Debounce function
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func(...args), wait);
            };
        }
        
        // Check profile slug availability
        function checkProfileSlugAvailability(slug) {
            if (slug.length < 3) {
                $('#profile_slug_feedback').html('<span class="text-muted">Minimal 3 karakter</span>');
                $('#createProfileBtn').prop('disabled', true);
                return;
            }
            
            slug = slug.toLowerCase().replace(/[^a-z0-9-]/g, '');
            $('#profile_slug_feedback').html('<span class="text-muted"><i class="bi bi-hourglass-split"></i> Memeriksa...</span>');
            $('#createProfileBtn').prop('disabled', true);
            
            $.ajax({
                url: 'profiles.php',
                method: 'POST',
                data: { action: 'check_profile_slug', slug: slug },
                dataType: 'json',
                success: function(response) {
                    if (response.available) {
                        $('#profile_slug_feedback').html('<span class="text-success"><i class="bi bi-check-circle-fill"></i> ' + response.message + '</span>');
                        $('#createProfileBtn').prop('disabled', false);
                    } else {
                        $('#profile_slug_feedback').html('<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + response.message + '</span>');
                        $('#createProfileBtn').prop('disabled', true);
                    }
                },
                error: function() {
                    $('#profile_slug_feedback').html('<span class="text-danger">Error checking availability</span>');
                    $('#createProfileBtn').prop('disabled', true);
                }
            });
        }
        
        const debouncedCheck = debounce(function() {
            checkProfileSlugAvailability($('#new_profile_slug').val());
        }, 500);
        
        $(document).ready(function() {
            $('#new_profile_slug').on('input', function() {
                let val = $(this).val().toLowerCase().replace(/[^a-z0-9-]/g, '');
                $(this).val(val);
                debouncedCheck();
            });
        });
    </script>
</body>
</html>
