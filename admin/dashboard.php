<?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once __DIR__ . '/../config/auth_check.php';
    require_once __DIR__ .  '/../config/db.php';
    
    $success = '';
    $error = '';
    
    // Multi-profile: Get active profile
    $active_profile_id = $_SESSION['active_profile_id'] ?? null;
    if (!$active_profile_id) {
        // Get user's primary profile
        $primary_profile = get_single_row(
            "SELECT id, slug FROM profiles WHERE user_id = ? AND display_order = 0 ORDER BY id ASC LIMIT 1",
            [$current_user_id],
            'i'
        );
        if ($primary_profile) {
            $active_profile_id = $primary_profile['id'];
            $_SESSION['active_profile_id'] = $active_profile_id;
            $_SESSION['page_slug'] = $primary_profile['slug'];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])){
        // CRITICAL: Validate active_profile_id exists
        if (!$active_profile_id) {
            $error = 'Error: Tidak ada profile aktif. Silakan refresh halaman.';
        } else {
            $title = trim($_POST['title']);
            $url = trim($_POST['url']);
            $icon_class = trim($_POST['icon_class']);
            $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

            if (empty($title) || empty($url)){
                $error = 'Judul dan URL harus diisi';
            } else {
                // Verify profile belongs to user before adding link
                $verify_profile = get_single_row(
                    "SELECT id FROM profiles WHERE id = ? AND user_id = ?",
                    [$active_profile_id, $current_user_id],
                    'ii'
                );
                
                if (!$verify_profile) {
                    $error = 'Error: Profile tidak valid. Silakan logout dan login kembali.';
                    $_SESSION['active_profile_id'] = null; // Clear invalid session
                } else {
                    // Multi-profile: Use active_profile_id for new link
                    $last_order_row = get_single_row("SELECT MAX(position) AS max_order FROM links WHERE profile_id = ?", [$active_profile_id], 'i');
            $last_order = $last_order_row['max_order'] ?? 0;
            $new_order = $last_order + 1;

            // Check if category_id column exists
            $check_col = mysqli_query($conn, "SHOW COLUMNS FROM links LIKE 'category_id'");
            $has_categories = ($check_col && mysqli_num_rows($check_col) > 0);

            if ($has_categories) {
                $query = "INSERT INTO links (profile_id, title, url, icon, category_id, position, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt){
                    mysqli_stmt_bind_param($stmt, 'isssii', $active_profile_id, $title, $url, $icon_class, $category_id, $new_order);
                    if (mysqli_stmt_execute($stmt)){
                        $success = 'Link berhasil ditambahkan';
                    } else {
                        $error = 'Gagal menambahkan link';
                    }
                } else {
                    $error = 'Gagal menyiapkan statement';
                }
            } else {
                // Fallback to schema without category_id
                $query = "INSERT INTO links (profile_id, title, url, icon, position, is_active) VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt){
                    mysqli_stmt_bind_param($stmt, 'isssi', $active_profile_id, $title, $url, $icon_class, $new_order);
                    if (mysqli_stmt_execute($stmt)){
                        $success = 'Link berhasil ditambahkan';
                    } else {
                        $error = 'Gagal menambahkan link';
                    }
                } else {
                    $error = 'Gagal menyiapkan statement';
                }
            }
                }
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_link'])) {
        $link_id = intval($_POST['link_id']);
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
        
        if (empty($title) || empty($url)) {
            $error = 'Judul dan URL harus diisi!';
        } else {
            // V3 schema: always has category_id
            $query = "UPDATE links SET title = ?, url = ?, icon = ?, category_id = ? WHERE id = ? AND profile_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssiii', $title, $url, $icon_class, $category_id, $link_id, $active_profile_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Link berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate link!';
            }
        }
    }

    // Multi-profile: Delete link from active profile
    if (isset($_GET['delete'])) {
        $link_id = intval($_GET['delete']);
        
        $query = "DELETE FROM links WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $link_id, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Link berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus link!';
        }
    }

    // Multi-profile: Reorder links within active profile
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
        $order_data = json_decode($_POST['order_data'], true);
        
        if ($order_data) {
            foreach ($order_data as $item) {
                $link_id = intval($item['id']);
                $order_index = intval($item['order']);
                
                $query = "UPDATE links SET position = ? WHERE id = ? AND profile_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'iii', $order_index, $link_id, $active_profile_id);
                mysqli_stmt_execute($stmt);
            }
            
            echo json_encode(['success' => true]);
            exit;
        }
    }
    // Multi-profile: Load links for active profile only (v3 schema)
    $links = get_all_rows("SELECT id as link_id, title, url, icon as icon_class, position as order_index, clicks as click_count, category_id, is_active FROM links WHERE profile_id = ? ORDER BY position ASC", [$active_profile_id], 'i');
    
    // Get analytics data for charts
    // Get link performance data (top 10 most clicked links) - Multi-profile
    $link_performance = get_all_rows(
        "SELECT title, clicks as click_count FROM links WHERE profile_id = ? AND is_active = 1 ORDER BY clicks DESC LIMIT 10",
        [$active_profile_id],
        'i'
    );
    
    // Get user categories for dropdown (with error handling)
    $user_categories = [];
    try {
        // Check if table exists first
        $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'categories_v3'");
        if ($check_table && mysqli_num_rows($check_table) > 0) {
            // Multi-profile: Load categories for active profile
            $user_categories = get_all_rows(
                "SELECT id as category_id, name as category_name, icon as category_icon, color as category_color FROM categories_v3 WHERE profile_id = ? ORDER BY position ASC",
                [$active_profile_id],
                'i'
            ) ?? [];
        }
    } catch (Exception $e) {
        // Silently fail if categories not available yet
        $user_categories = [];
    }

    // Get daily clicks for last 7 days (including today for realtime updates) - Multi-profile
    $daily_clicks = get_all_rows(
        "SELECT 
            DATE(clicked_at) as date,
            COUNT(*) as clicks
        FROM clicks c
        INNER JOIN links l ON c.link_id = l.id
        WHERE l.profile_id = ? 
          AND DATE(clicked_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
          AND DATE(clicked_at) <= CURDATE()
        GROUP BY DATE(clicked_at)
        ORDER BY date ASC",
        [$active_profile_id],
        'i'
    );
    
    // Fill missing dates with 0 clicks
    $dates_range = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates_range[$date] = 0;
    }
    
    foreach ($daily_clicks as $row) {
        $dates_range[$row['date']] = intval($row['clicks']);
    }
    
    // Get click distribution by geographic location (city + country) - Multi-profile
    $click_by_location = get_all_rows(
        "SELECT 
            CASE 
                WHEN city IS NOT NULL AND city != '' THEN CONCAT(city, ', ', COALESCE(country, 'Unknown'))
                ELSE COALESCE(NULLIF(country, ''), 'Unknown')
            END as location,
            COUNT(*) as clicks
        FROM clicks c
        INNER JOIN links l ON c.link_id = l.id
        WHERE l.profile_id = ?
        GROUP BY location
        ORDER BY clicks DESC
        LIMIT 10",
        [$active_profile_id],
        'i'
    );
    
    // If no country data, show IP-based location summary - Multi-profile
    if (empty($click_by_location)) {
        $click_by_location = get_all_rows(
            "SELECT 
                CASE 
                    WHEN ip LIKE '172.%' OR ip LIKE '192.168.%' THEN 'Local Network'
                    WHEN ip IS NULL OR ip = '' THEN 'Unknown'
                    ELSE CONCAT('IP: ', SUBSTRING(ip, 1, 10), '...')
                END as location,
                COUNT(*) as clicks
            FROM clicks c
            INNER JOIN links l ON c.link_id = l.id
            WHERE l.profile_id = ?
            GROUP BY location
            ORDER BY clicks DESC
            LIMIT 10",
            [$active_profile_id],
            'i'
        );
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LinkMy</title>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
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
        .link-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: move;
            transition: all 0.3s;
        }
        .link-item:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        }
        .link-item.dragging {
            opacity: 0.5;
        }
        .drag-handle {
            cursor: grab;
            color: #999;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
        }

        /* Drag Overlay for Mobile - Blur everything EXCEPT links container */
        .drag-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            z-index: 998;
            display: none;
            pointer-events: none;
        }
        .drag-overlay.active {
            display: block;
        }
        /* Highlight all links container when dragging */
        #linksList.dragging-active {
            position: relative;
            z-index: 999;
            background: white;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.8);
        }
        .link-item.dragging {
            z-index: 1000;
            position: relative;
            opacity: 0.9;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .link-item {
                padding: 1rem;
                border: 2px solid #e9ecef;
                border-radius: 12px;
                margin-bottom: 0.75rem;
            }
            .link-item .d-flex {
                flex-direction: column;
                align-items: stretch !important;
                gap: 0.75rem;
            }
            /* Top row: drag handle + icon + title */
            .link-item .d-flex > .d-flex:first-child {
                flex-direction: row !important;
                align-items: center !important;
            }
            .link-item .flex-grow-1 {
                width: 100%;
            }
            .link-item .flex-grow-1 > .d-flex {
                margin-bottom: 0.5rem;
            }
            .link-item .flex-grow-1 > .d-flex i {
                font-size: 20px;
            }
            .link-item .flex-grow-1 strong {
                font-size: 15px;
                flex: 1;
            }
            .link-item small {
                font-size: 11px;
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                max-width: 100%;
            }
            .link-item .badge {
                font-size: 11px;
                padding: 0.35em 0.65em;
            }
            .drag-handle {
                font-size: 22px;
                padding: 4px;
                touch-action: none;
                margin-right: 0.5rem;
            }
            /* Action buttons - horizontal at bottom */
            .link-item > .d-flex > div:last-child {
                display: flex;
                gap: 0.5rem;
                justify-content: flex-end;
                width: 100%;
            }
            .link-item .btn {
                padding: 8px 16px;
                font-size: 13px;
                flex: 1;
                max-width: 48%;
            }
            .link-item .btn i {
                font-size: 13px;
            }
            .stat-card {
                padding: 1rem;
            }
            .stat-card h6 {
                font-size: 12px;
            }
            .stat-card h2 {
                font-size: 24px;
            }
            .card-body {
                padding: 1rem;
            }
            /* Better spacing for mobile */
            .mb-3 {
                margin-bottom: 1rem !important;
            }
            /* Stack elements vertically on mobile */
            .d-flex {
                flex-wrap: wrap !important;
            }
        }

        @media (max-width: 576px) {
            h2 {
                font-size: 24px;
            }
            .link-item {
                padding: 10px;
            }
            .card {
                border-radius: 10px;
            }
            /* Touch-friendly buttons */
            .btn {
                min-height: 44px;
            }
        }
    </style>
</head>
<body>
    <!-- Drag Overlay for Mobile -->
    <div class="drag-overlay" id="dragOverlay"></div>

    <?php include '../partials/admin_nav.php'; ?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">Halo, <?= htmlspecialchars($current_username) ?>! 👋</h2>
                <p class="text-muted">Kelola link Anda di bawah ini</p>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-uppercase mb-2">Total Links</h6>
                    <h2 class="fw-bold mb-0"><?= count($links) ?></h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-uppercase mb-2">Total Klik</h6>
                    <h2 class="fw-bold mb-0"><?= array_sum(array_column($links, 'click_count')) ?></h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card">
                    <h6 class="text-uppercase mb-2">Page Slug</h6>
                    <h2 class="fw-bold mb-0"><?= htmlspecialchars($current_page_slug) ?></h2>
                </div>
            </div>
        </div>
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
        
        <!-- Analytics Charts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Analytics Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="analyticsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="trends-tab" data-bs-toggle="tab" data-bs-target="#trends" type="button">
                                    <i class="bi bi-graph-up-arrow"></i> Click Trends (7 Days)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button">
                                    <i class="bi bi-bar-chart"></i> Link Performance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="traffic-tab" data-bs-toggle="tab" data-bs-target="#traffic" type="button">
                                    <i class="bi bi-pie-chart"></i> Traffic Sources
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="analyticsTabContent">
                            <!-- Click Trends Chart -->
                            <div class="tab-pane fade show active" id="trends" role="tabpanel">
                                <div id="clickTrendsChart" style="height: 400px;"></div>
                            </div>
                            
                            <!-- Link Performance Chart -->
                            <div class="tab-pane fade" id="performance" role="tabpanel">
                                <div id="linkPerformanceChart" style="height: 400px;"></div>
                            </div>
                            
                            <!-- Traffic Sources Chart -->
                            <div class="tab-pane fade" id="traffic" role="tabpanel">
                                <div id="trafficSourcesChart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0 fw-bold">
                                <i class="bi bi-list-ul"></i> Daftar Link Anda
                            </h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="bi bi-plus-circle"></i> Tambah Link
                            </button>
                        </div>

                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle"></i> Drag & drop untuk mengubah urutan
                        </p>

                        <div id="linksList">
                            <?php if (empty($links)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <p class="text-muted mt-3">Belum ada link. Tambahkan link pertama Anda!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($links as $link): ?>
                                    <div class="link-item" data-id="<?= $link['link_id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="drag-handle me-3">
                                                <i class="bi bi-grip-vertical fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="<?= htmlspecialchars($link['icon_class']) ?> me-2"></i>
                                                    <strong><?= htmlspecialchars($link['title']) ?></strong>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="bi bi-link-45deg"></i> <?= htmlspecialchars($link['url']) ?>
                                                </small>
                                                <div class="mt-1">
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-mouse"></i> <?= $link['click_count'] ?> klik
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                        onclick="editLink(<?= htmlspecialchars(json_encode($link)) ?>)">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <a href="?delete=<?= $link['link_id'] ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus link ini?')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="bi bi-lightbulb-fill text-warning"></i> Tips
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Gunakan judul yang jelas dan menarik
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Pilih ikon yang sesuai dengan platform
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Urutkan link berdasarkan prioritas
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Cek statistik klik secara berkala
                            </li>
                        </ul>

                        <hr>

                        <h6 class="fw-bold mb-2">Link Publik Anda:</h6>
                        <div class="input-group">
                            <input type="text" class="form-control"
                                   value="linkmy.iet.ovh/<?= $current_page_slug ?>"
                                   id="publicLink" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyLink()">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle"></i> Tambah Link Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul Link</label>
                            <input type="text" class="form-control" name="title" required
                                   placeholder="Contoh: Instagram Saya">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="url" class="form-control" name="url" required
                                   placeholder="https://instagram.com/username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category (Optional) <span class="badge bg-success">New!</span></label>
                            <select class="form-select" name="category_id">
                                <option value="">None (Uncategorized)</option>
                                <?php foreach ($user_categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>">
                                        <i class="<?= htmlspecialchars($cat['category_icon']) ?>"></i>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Group this link under a category. <a href="categories.php">Manage Categories</a></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ikon (Bootstrap Icons)</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text">
                                    <i id="iconPreview" class="bi-link-45deg" style="font-size: 1.5rem;"></i>
                                </span>
                                <input type="text" class="form-control" name="icon_class" id="iconInput"
                                       value="bi-link-45deg"
                                       placeholder="Example: bi-instagram">
                            </div>
                            <div class="alert alert-info py-2 px-3 mb-2">
                                <small>
                                    <strong>💡 Format:</strong> Gunakan prefix <code>bi-</code> diikuti nama icon<br>
                                    <strong>📖 Example:</strong> <code>bi-instagram</code>, <code>bi-github</code>, <code>bi-linkedin</code>
                                </small>
                            </div>
                            
                            <!-- Popular Icons Template -->
                            <div class="mb-2">
                                <label class="form-label text-muted small mb-1">🔥 Popular Icons:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-instagram" title="Instagram">
                                        <i class="bi-instagram"></i> Instagram
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-facebook" title="Facebook">
                                        <i class="bi-facebook"></i> Facebook
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-twitter-x" title="Twitter/X">
                                        <i class="bi-twitter-x"></i> Twitter
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-tiktok" title="TikTok">
                                        <i class="bi-tiktok"></i> TikTok
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-youtube" title="YouTube">
                                        <i class="bi-youtube"></i> YouTube
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-linkedin" title="LinkedIn">
                                        <i class="bi-linkedin"></i> LinkedIn
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-github" title="GitHub">
                                        <i class="bi-github"></i> GitHub
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-whatsapp" title="WhatsApp">
                                        <i class="bi-whatsapp"></i> WhatsApp
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-telegram" title="Telegram">
                                        <i class="bi-telegram"></i> Telegram
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-discord" title="Discord">
                                        <i class="bi-discord"></i> Discord
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-spotify" title="Spotify">
                                        <i class="bi-spotify"></i> Spotify
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-envelope-fill" title="Email">
                                        <i class="bi-envelope-fill"></i> Email
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-globe" title="Website">
                                        <i class="bi-globe"></i> Website
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-link-45deg" title="Link">
                                        <i class="bi-link-45deg"></i> Link
                                    </button>
                                </div>
                            </div>
                            
                            <small class="text-muted">
                                📚 Browse more: <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons Library</a>
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_link" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="link_id" id="edit_link_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-fill"></i> Edit Link
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul Link</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="url" class="form-control" name="url" id="edit_url" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category <span class="badge bg-success">New!</span></label>
                            <select class="form-select" name="category_id" id="edit_category">
                                <option value="">None (Uncategorized)</option>
                                <?php foreach ($user_categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>">
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ikon</label>
                            <input type="text" class="form-control" name="icon_class" id="edit_icon">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_link" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Live icon preview
        document.getElementById('iconInput')?.addEventListener('input', function(e) {
            const iconClass = e.target.value.trim();
            const preview = document.getElementById('iconPreview');
            if (preview) {
                preview.className = iconClass || 'bi-link-45deg';
            }
        });
        
        // Icon template selector
        document.querySelectorAll('.icon-template').forEach(btn => {
            btn.addEventListener('click', function() {
                const iconClass = this.getAttribute('data-icon');
                const input = document.getElementById('iconInput');
                const preview = document.getElementById('iconPreview');
                
                if (input) input.value = iconClass;
                if (preview) preview.className = iconClass;
                
                // Visual feedback
                document.querySelectorAll('.icon-template').forEach(b => b.classList.remove('active', 'btn-primary'));
                document.querySelectorAll('.icon-template').forEach(b => b.classList.add('btn-outline-secondary'));
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary', 'active');
            });
        });
        
        function editLink(link) {
            document.getElementById('edit_link_id').value = link.link_id;
            document.getElementById('edit_title').value = link.title;
            document.getElementById('edit_url').value = link.url;
            document.getElementById('edit_icon').value = link.icon_class;
            document.getElementById('edit_category').value = link.category_id || '';
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        function copyLink() {
            const input = document.getElementById('publicLink');
            input.select();
            document.execCommand('copy');
            alert('Link berhasil disalin!');
        }
        
        // Highcharts Global Options
        Highcharts.setOptions({
            colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'],
            chart: {
                style: {
                    fontFamily: 'Inter, sans-serif'
                }
            },
            credits: {
                enabled: false
            }
        });
        
        // 1. Click Trends Chart (Line Chart - Last 7 Days)
        Highcharts.chart('clickTrendsChart', {
            chart: {
                type: 'area',
                backgroundColor: '#f8f9fa'
            },
            title: {
                text: 'Click Trends - Last 7 Days',
                align: 'left',
                style: {
                    fontSize: '18px',
                    fontWeight: 'bold'
                }
            },
            subtitle: {
                text: 'Total clicks per day on your links',
                align: 'left'
            },
            xAxis: {
                categories: <?= json_encode(array_keys($dates_range)) ?>,
                crosshair: true,
                labels: {
                    formatter: function() {
                        // Parse YYYY-MM-DD format manually to avoid timezone issues
                        const parts = this.value.split('-');
                        const date = new Date(parts[0], parts[1] - 1, parts[2]);
                        return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
                    }
                }
            },
            yAxis: {
                title: {
                    text: 'Number of Clicks'
                },
                min: 0
            },
            tooltip: {
                shared: true,
                valueSuffix: ' clicks',
                formatter: function() {
                    // this.x is category index, get the actual date string from categories
                    const dateString = this.points[0].point.category;
                    const parts = dateString.split('-');
                    const date = new Date(parts[0], parts[1] - 1, parts[2]);
                    return '<b>' + date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) + '</b><br/>' +
                           this.points.map(p => p.series.name + ': <b>' + p.y + ' clicks</b>').join('<br/>');
                }
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, 'rgba(102, 126, 234, 0.3)'],
                            [1, 'rgba(102, 126, 234, 0.05)']
                        ]
                    },
                    marker: {
                        radius: 4,
                        fillColor: '#667eea',
                        lineWidth: 2,
                        lineColor: '#fff'
                    },
                    lineWidth: 3,
                    states: {
                        hover: {
                            lineWidth: 4
                        }
                    },
                    threshold: null
                }
            },
            series: [{
                name: 'Daily Clicks',
                data: <?= json_encode(array_values($dates_range)) ?>,
                color: '#667eea'
            }],
            exporting: {
                enabled: true,
                buttons: {
                    contextButton: {
                        menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                    }
                }
            }
        });
        
        // 2. Link Performance Chart (Bar Chart)
        Highcharts.chart('linkPerformanceChart', {
            chart: {
                type: 'column',
                backgroundColor: '#f8f9fa'
            },
            title: {
                text: 'Top 10 Most Clicked Links',
                align: 'left',
                style: {
                    fontSize: '18px',
                    fontWeight: 'bold'
                }
            },
            subtitle: {
                text: 'Performance comparison of your links',
                align: 'left'
            },
            xAxis: {
                categories: <?= json_encode(array_column($link_performance, 'title')) ?>,
                crosshair: true,
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '11px'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Total Clicks'
                }
            },
            tooltip: {
                headerFormat: '<span style="font-size:14px"><b>{point.key}</b></span><br/>',
                pointFormat: '<span style="color:{point.color}">●</span> Clicks: <b>{point.y}</b>',
                shared: true,
                useHTML: true
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0,
                    dataLabels: {
                        enabled: true,
                        format: '{point.y}',
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        }
                    },
                    colorByPoint: true
                }
            },
            series: [{
                name: 'Clicks',
                data: <?= json_encode(array_map('intval', array_column($link_performance, 'click_count'))) ?>,
                showInLegend: false
            }],
            exporting: {
                enabled: true,
                buttons: {
                    contextButton: {
                        menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                    }
                }
            }
        });
        
        // 3. Traffic Sources Chart (Pie Chart)
        Highcharts.chart('trafficSourcesChart', {
            chart: {
                type: 'pie',
                backgroundColor: '#f8f9fa',
                options3d: {
                    enabled: true,
                    alpha: 45
                }
            },
            title: {
                text: 'Traffic by Location',
                align: 'left',
                style: {
                    fontSize: '18px',
                    fontWeight: 'bold'
                }
            },
            subtitle: {
                text: 'Geographic distribution of your visitors',
                align: 'left'
            },
            tooltip: {
                pointFormat: '<b>{point.percentage:.1f}%</b><br/>Total Clicks: <b>{point.y}</b>',
                style: {
                    fontSize: '13px'
                }
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    innerSize: '50%',
                    depth: 45,
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage:.1f}%<br>{point.y} clicks',
                        style: {
                            fontSize: '12px',
                            fontWeight: 'bold'
                        },
                        connectorColor: 'silver'
                    },
                    showInLegend: true
                }
            },
            legend: {
                align: 'right',
                verticalAlign: 'middle',
                layout: 'vertical',
                itemStyle: {
                    fontSize: '13px'
                }
            },
            series: [{
                name: 'Traffic Share',
                colorByPoint: true,
                data: [
                    <?php 
                    $sources_data = [];
                    foreach ($click_by_location as $location) {
                        $sources_data[] = '{
                            name: "' . htmlspecialchars($location['location']) . '",
                            y: ' . intval($location['clicks']) . '
                        }';
                    }
                    echo implode(',', $sources_data);
                    ?>
                ]
            }],
            exporting: {
                enabled: true,
                buttons: {
                    contextButton: {
                        menuItems: ['downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                    }
                }
            }
        });
    </script>
</body>
</html>