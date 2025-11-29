<?php
// Admin Navigation Bar
if (!isset($current_username) || !isset($current_page_slug)) {
    // Redirect if not authenticated
    header('Location: ../login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

// Multi-profile: Load all user profiles for dropdown
require_once __DIR__ . '/../config/db.php';

$user_profiles = [];
$active_profile_name = 'Profile';
if (isset($_SESSION['user_id'])) {
    $stmt = mysqli_prepare($conn, "SELECT profile_id, profile_name, profile_slug, is_primary FROM profiles WHERE user_id = ? ORDER BY is_primary DESC, created_at ASC");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $user_profiles[] = $row;
        if (isset($_SESSION['active_profile_id']) && $row['profile_id'] == $_SESSION['active_profile_id']) {
            $active_profile_name = $row['profile_name'];
        }
    }
    mysqli_stmt_close($stmt);
}
?>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-link-45deg"></i> LinkMy
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                        <i class="bi bi-house-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'appearance.php' ? 'active' : '' ?>" href="appearance.php">
                        <i class="bi bi-palette-fill"></i> Appearance
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'categories.php' ? 'active' : '' ?>" href="categories.php">
                        <i class="bi bi-folder-fill"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'profiles.php' ? 'active' : '' ?>" href="profiles.php">
                        <i class="bi bi-person-badge"></i> Profiles
                        <span class="badge bg-primary ms-1">Multi</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                </li>
                
                <!-- Multi-Profile Switcher Dropdown -->
                <?php if (count($user_profiles) > 1): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-collection"></i> <?= htmlspecialchars($active_profile_name) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <li><h6 class="dropdown-header">Switch Profile</h6></li>
                        <?php foreach ($user_profiles as $profile): ?>
                            <li>
                                <a class="dropdown-item <?= (isset($_SESSION['active_profile_id']) && $profile['profile_id'] == $_SESSION['active_profile_id']) ? 'active' : '' ?>" 
                                   href="profiles.php?action=switch&profile_id=<?= $profile['profile_id'] ?>">
                                    <?= $profile['is_primary'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-circle"></i>' ?>
                                    <?= htmlspecialchars($profile['profile_name']) ?>
                                    <small class="text-muted">(<?= htmlspecialchars($profile['profile_slug']) ?>)</small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profiles.php"><i class="bi bi-plus-circle"></i> Manage Profiles</a></li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link" href="../<?= htmlspecialchars($current_page_slug) ?>" target="_blank">
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
