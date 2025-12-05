<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';
require_once '../config/session_handler.php';

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Get current user's profiles with stats
$user_profiles = get_all_rows(
    "SELECT p.id, p.slug, p.name, p.display_order, p.is_active, p.created_at,
            p.title, p.bio, p.avatar,
            (SELECT COUNT(*) FROM links WHERE profile_id = p.id) as link_count,
            (SELECT COALESCE(SUM(clicks), 0) FROM links WHERE profile_id = p.id) as total_clicks
     FROM profiles p
     WHERE p.user_id = ?
     ORDER BY p.display_order ASC, p.created_at ASC",
    [$current_user_id],
    'i'
);

// Get active profile
$active_profile_id = $_SESSION['active_profile_id'] ?? null;

// HANDLER: Create new profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_profile'])) {
    $profile_name = trim($_POST['profile_name']);
    $slug = trim($_POST['slug']);
    
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
    
    if (empty($profile_name) || empty($slug)) {
        $_SESSION['error'] = "Nama profil dan slug harus diisi!";
    } elseif (strlen($slug) < 3 || strlen($slug) > 50) {
        $_SESSION['error'] = "Slug harus 3-50 karakter!";
    } else {
        $count = count($user_profiles);
        if ($count >= 2) {
            $_SESSION['error'] = "Maksimal 2 profil untuk akun gratis!";
        } else {
            $existing = get_single_row("SELECT id FROM profiles WHERE slug = ?", [$slug], 's');
            if ($existing) {
                $_SESSION['error'] = "Slug sudah digunakan!";
            } else {
                $query = "INSERT INTO profiles (user_id, slug, name, display_order, is_active) VALUES (?, ?, ?, ?, 1)";
                $display_order = $count > 0 ? 1 : 0; // First profile is primary
                
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'issi', $current_user_id, $slug, $profile_name, $display_order);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success'] = "Profil '{$profile_name}' berhasil dibuat!";
                } else {
                    $_SESSION['error'] = "Gagal membuat profil!";
                }
            }
        }
    }
    header("Location: profiles.php");
    exit;
}

// HANDLER: Delete profile
if (isset($_GET['delete_profile'])) {
    $profile_id = intval($_GET['delete_profile']);
    
    $profile = get_single_row("SELECT * FROM profiles WHERE id = ? AND user_id = ?", [$profile_id, $current_user_id], 'ii');
    
    if (!$profile) {
        $_SESSION['error'] = "Profil tidak ditemukan!";
    } elseif ($profile['display_order'] == 0) {
        $_SESSION['error'] = "Tidak bisa menghapus profil utama!";
    } else {
        $query = "DELETE FROM profiles WHERE id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $profile_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            if ($active_profile_id == $profile_id) {
                $primary = get_single_row("SELECT id FROM profiles WHERE user_id = ? AND display_order = 0", [$current_user_id], 'i');
                $_SESSION['active_profile_id'] = $primary['id'];
            }
            $_SESSION['success'] = "Profil '{$profile['name']}' berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus profil!";
        }
    }
    header("Location: profiles.php");
    exit;
}

// HANDLER: Set primary profile
if (isset($_GET['set_primary'])) {
    $profile_id = intval($_GET['set_primary']);
    
    $profile = get_single_row("SELECT * FROM profiles WHERE id = ? AND user_id = ?", [$profile_id, $current_user_id], 'ii');
    
    if (!$profile) {
        $_SESSION['error'] = "Profil tidak ditemukan!";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // Unset all primary flags
            execute_query("UPDATE profiles SET display_order = 1 WHERE user_id = ? AND display_order = 0", [$current_user_id], 'i');
            
            // Set new primary
            execute_query("UPDATE profiles SET display_order = 0 WHERE id = ? AND user_id = ?", [$profile_id, $current_user_id], 'ii');
            
            mysqli_commit($conn);
            $_SESSION['success'] = "'{$profile['name']}' sekarang profil utama!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $_SESSION['error'] = "Gagal mengubah profil utama!";
        }
    }
    header("Location: profiles.php");
    exit;
}

$profile_count = count($user_profiles);
$profile_limit = 2;

$page_title = "Kelola Profil";
include '../partials/admin_header.php';
?>
<body>
    <?php include '../partials/admin_nav.php'; ?>
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bold mb-1">Kelola Profil</h1>
                <p class="text-muted mb-0">Anda memiliki <strong><?= $profile_count ?>/<?= $profile_limit ?></strong> profil. Profil utama akan menjadi halaman default Anda.</p>
            </div>
            <?php if ($profile_count < $profile_limit): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProfileModal">
                <i class="bi bi-plus-circle me-2"></i> Buat Profil Baru
            </button>
            <?php endif; ?>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <?php foreach ($user_profiles as $profile): ?>
            <div class="col-md-6 mb-4">
                <div class="card profile-card h-100 shadow-sm <?= $profile['id'] == $active_profile_id ? 'active' : '' ?> <?= $profile['display_order'] == 0 ? 'primary' : '' ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                                    <?= htmlspecialchars($profile['name']) ?>
                                    <?php if ($profile['display_order'] == 0): ?>
                                        <span class="badge bg-success-soft text-success">Utama</span>
                                    <?php endif; ?>
                                    <?php if ($profile['id'] == $active_profile_id): ?>
                                        <span class="badge bg-primary-soft text-primary">Aktif</span>
                                    <?php endif; ?>
                                </h5>
                                <a href="../<?= htmlspecialchars($profile['slug']) ?>" target="_blank" class="text-muted text-decoration-none">
                                    <small>../<?= htmlspecialchars($profile['slug']) ?> <i class="bi bi-box-arrow-up-right"></i></small>
                                </a>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if ($profile['id'] != $active_profile_id): ?>
                                    <li>
                                        <a class="dropdown-item" href="../scripts/switch_profile.php?id=<?= $profile['id'] ?>">
                                            <i class="bi bi-arrow-left-right me-2"></i> Aktifkan Profil
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if ($profile['display_order'] != 0): ?>
                                    <li>
                                        <a class="dropdown-item" href="?set_primary=<?= $profile['id'] ?>" onclick="return confirm('Jadikan profil ini sebagai yang utama?')">
                                            <i class="bi bi-star-fill me-2"></i> Jadikan Utama
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <?php if ($profile['display_order'] != 0): ?>
                                    <li>
                                        <a class="dropdown-item text-danger" href="?delete_profile=<?= $profile['id'] ?>" onclick="return confirm('Yakin ingin menghapus profil ini? Semua link dan data terkait akan hilang permanen!')">
                                            <i class="bi bi-trash-fill me-2"></i> Hapus Profil
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-3 mt-3 pt-3 border-top">
                            <div class="stat-badge">
                                <i class="bi bi-link-45deg"></i>
                                <strong><?= intval($profile['link_count'] ?? 0) ?></strong> Links
                            </div>
                            <div class="stat-badge">
                                <i class="bi bi-graph-up"></i>
                                <strong><?= intval($profile['total_clicks'] ?? 0) ?></strong> Klik
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if ($profile_count < $profile_limit): ?>
            <div class="col-md-6 mb-4">
                <div class="card profile-card-add h-100" data-bs-toggle="modal" data-bs-target="#createProfileModal">
                    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                        <i class="bi bi-plus-circle-dotted display-3 text-muted"></i>
                        <h5 class="mt-3 text-muted fw-bold">Buat Profil Baru</h5>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Create Profile Modal -->
    <div class="modal fade" id="createProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="createProfileForm">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Buat Profil Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Profil</label>
                            <input type="text" class="form-control" name="profile_name" placeholder="contoh: Profil Bisnis" required>
                            <small class="text-muted">Nama ini untuk identifikasi Anda, tidak ditampilkan publik.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Slug (URL)</label>
                            <input type="text" class="form-control" name="slug" id="new_profile_slug" placeholder="contoh: bisnis-keren" pattern="[a-z0-9-]+" minlength="3" maxlength="50" required>
                            <div id="profile_slug_feedback" class="form-text mt-1"></div>
                            <small class="text-muted">URL unik profil Anda. Hanya huruf kecil, angka, dan strip (-).</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="create_profile" class="btn btn-primary" id="createProfileBtn">Buat Profil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../partials/admin_footer.php'; ?>

    <style>
        .profile-card, .profile-card-add {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            transition: all 0.2s ease-in-out;
        }
        .profile-card-add {
            cursor: pointer;
            border-style: dashed;
        }
        .profile-card-add:hover {
            background-color: var(--bs-secondary-bg);
            border-color: var(--bs-primary);
        }
        .profile-card.active {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 3px var(--bs-primary-bg-subtle);
        }
        .profile-card.primary {
            border-left: 5px solid var(--bs-success);
        }
        .stat-badge {
            background-color: var(--bs-secondary-bg);
            padding: 0.35rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            color: var(--bs-body-color);
        }
        .bg-success-soft { background-color: var(--bs-success-bg-subtle); }
        .text-success { color: var(--bs-success-text-emphasis) !important; }
        .bg-primary-soft { background-color: var(--bs-primary-bg-subtle); }
        .text-primary { color: var(--bs-primary-text-emphasis) !important; }
    </style>

    <script>
        // Simple slug availability check via AJAX could be added here if needed
    </script>
</body>
</html>




