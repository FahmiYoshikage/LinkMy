<?php
// Admin Navigation Bar
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$current_user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);

// Load all user profiles for dropdown
$user_profiles = get_all_rows("SELECT id, name, slug, display_order FROM profiles WHERE user_id = ? ORDER BY display_order ASC, created_at ASC", [$current_user_id], 'i');

// Determine active profile
$active_profile_id = $_SESSION['active_profile_id'] ?? null;
$active_profile_name = 'Pilih Profil';
$current_page_slug = '';

if ($active_profile_id) {
    foreach ($user_profiles as $profile) {
        if ($profile['id'] == $active_profile_id) {
            $active_profile_name = $profile['name'];
            $current_page_slug = $profile['slug'];
            break;
        }
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-link-45deg"></i> LinkMy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="bi bi-house-door-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'appearance.php' ? 'active' : '' ?>" href="appearance.php">
                        <i class="bi bi-palette-fill"></i> Tampilan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                        <i class="bi bi-folder-fill"></i> Kategori
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                        <i class="bi bi-gear-fill"></i> Pengaturan
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <!-- Multi-Profile Switcher Dropdown -->
                <?php if (!empty($user_profiles)): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($active_profile_name) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><h6 class="dropdown-header">Ganti Profil</h6></li>
                        <?php foreach ($user_profiles as $profile): ?>
                            <li>
                                <a class="dropdown-item <?= ($profile['id'] == $active_profile_id) ? 'active' : '' ?>" 
                                   href="../scripts/switch_profile.php?profile_id=<?= $profile['id'] ?>">
                                    <?= $profile['display_order'] == 0 ? '<i class="bi bi-star-fill text-warning me-2"></i>' : '<i class="bi bi-person me-2"></i>' ?>
                                    <?= htmlspecialchars($profile['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Kelola Profil</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <?php if ($current_page_slug): ?>
                <li class="nav-item">
                    <a class="nav-link" href="../<?= htmlspecialchars($current_page_slug) ?>" target="_blank" title="Lihat Halaman Publik">
                        <i class="bi bi-eye-fill"></i> <span class="d-lg-none ms-1">Lihat Halaman</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link" href="../logout.php" title="Logout">
                        <i class="bi bi-box-arrow-right"></i> <span class="d-lg-none ms-1">Logout</span>
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <button class="btn nav-link theme-toggle-btn" id="themeToggle" title="Ganti Tema">
                        <i class="bi" id="themeIcon"></i>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>
