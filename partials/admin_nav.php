<?php
// Admin Navigation Bar
if (!isset($current_username) || !isset($current_page_slug)) {
    // Redirect if not authenticated
    header('Location: ../login.php');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
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
                        <span class="badge bg-success ms-1">New</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $current_page === 'settings.php' ? 'active' : '' ?>" href="settings.php">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../profile.php?slug=<?= htmlspecialchars($current_page_slug) ?>" target="_blank">
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
