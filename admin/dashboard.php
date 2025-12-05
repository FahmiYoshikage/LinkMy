<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session_handler.php'; // Modern session management

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Multi-profile: Get active profile
$active_profile_id = $_SESSION['active_profile_id'] ?? null;
if (!$active_profile_id) {
    $primary_profile = get_single_row(
        "SELECT id, slug FROM profiles WHERE user_id = ? AND display_order = 0 ORDER BY id ASC LIMIT 1",
        [$current_user_id],
        'i'
    );
    if ($primary_profile) {
        $active_profile_id = $primary_profile['id'];
        $_SESSION['active_profile_id'] = $active_profile_id;
        $_SESSION['page_slug'] = $primary_profile['slug'];
    } else {
        // Handle case where user has no profiles
        $error = "Tidak ada profil yang ditemukan. Silakan buat profil pertama Anda di pengaturan.";
        // To prevent further errors, let's ensure critical variables are set
        $active_profile_id = null;
        $_SESSION['page_slug'] = '';
    }
}

// Update page slug in session if it has changed
$current_page_slug = get_single_row("SELECT slug FROM profiles WHERE id = ?", [$active_profile_id], 'i')['slug'] ?? '';
$_SESSION['page_slug'] = $current_page_slug;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    if (!$active_profile_id) {
        $_SESSION['error'] = 'Error: Tidak ada profile aktif. Silakan refresh halaman.';
    } else {
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);
        $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

        if (empty($title) || empty($url)) {
            $_SESSION['error'] = 'Judul dan URL harus diisi';
        } else {
            $verify_profile = get_single_row(
                "SELECT id FROM profiles WHERE id = ? AND user_id = ?",
                [$active_profile_id, $current_user_id],
                'ii'
            );

            if (!$verify_profile) {
                $_SESSION['error'] = 'Error: Profile tidak valid. Silakan logout dan login kembali.';
                $_SESSION['active_profile_id'] = null; // Clear invalid session
            } else {
                $last_order_row = get_single_row("SELECT MAX(position) AS max_order FROM links WHERE profile_id = ?", [$active_profile_id], 'i');
                $new_order = ($last_order_row['max_order'] ?? 0) + 1;

                $query = "INSERT INTO links (profile_id, title, url, icon, category_id, position, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
                $stmt = mysqli_prepare($conn, $query);
                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'isssii', $active_profile_id, $title, $url, $icon_class, $category_id, $new_order);
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['success'] = 'Link berhasil ditambahkan';
                    } else {
                        $_SESSION['error'] = 'Gagal menambahkan link: ' . mysqli_error($conn);
                    }
                } else {
                    $_SESSION['error'] = 'Gagal menyiapkan statement';
                }
            }
        }
    }
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_link'])) {
    $link_id = intval($_POST['link_id']);
    $title = trim($_POST['title']);
    $url = trim($_POST['url']);
    $icon_class = trim($_POST['icon_class']);
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;

    if (empty($title) || empty($url)) {
        $_SESSION['error'] = 'Judul dan URL harus diisi!';
    } else {
        $query = "UPDATE links SET title = ?, url = ?, icon = ?, category_id = ? WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssiii', $title, $url, $icon_class, $category_id, $link_id, $active_profile_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Link berhasil diupdate!';
        } else {
            $_SESSION['error'] = 'Gagal mengupdate link!';
        }
    }
    header("Location: dashboard.php");
    exit;
}

if (isset($_GET['delete'])) {
    $link_id = intval($_GET['delete']);
    $query = "DELETE FROM links WHERE id = ? AND profile_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $link_id, $active_profile_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = 'Link berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus link!';
    }
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_data = json_decode($_POST['order_data'], true);
    if ($order_data && $active_profile_id) {
        $conn->begin_transaction();
        try {
            $query = "UPDATE links SET position = ? WHERE id = ? AND profile_id = ?";
            $stmt = $conn->prepare($query);
            foreach ($order_data as $item) {
                $link_id = intval($item['id']);
                $order_index = intval($item['order']);
                $stmt->bind_param('iii', $order_index, $link_id, $active_profile_id);
                $stmt->execute();
            }
            $stmt->close();
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Order updated successfully.']);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit;
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data.']);
    exit;
}

// Data Fetching (only if a profile is active)
$links = [];
$link_performance = [];
$user_categories = [];
$daily_clicks = [];
$click_by_location = [];
$dates_range = [];

if ($active_profile_id) {
    $links = get_all_rows("SELECT id as link_id, title, url, icon as icon_class, position as order_index, clicks as click_count, category_id, is_active FROM links WHERE profile_id = ? ORDER BY position ASC", [$active_profile_id], 'i');
    
    $link_performance = get_all_rows(
        "SELECT title, clicks as click_count FROM links WHERE profile_id = ? AND is_active = 1 ORDER BY clicks DESC LIMIT 10",
        [$active_profile_id],
        'i'
    );
    
    try {
        $user_categories = get_all_rows(
            "SELECT id as category_id, name as category_name, icon as category_icon, color as category_color FROM categories_v3 WHERE profile_id = ? ORDER BY position ASC",
            [$active_profile_id],
            'i'
        ) ?? [];
    } catch (Exception $e) {
        $user_categories = [];
    }

    $daily_clicks = get_all_rows(
        "SELECT DATE(clicked_at) as date, COUNT(*) as clicks
        FROM clicks c
        INNER JOIN links l ON c.link_id = l.id
        WHERE l.profile_id = ? AND DATE(clicked_at) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(clicked_at) ORDER BY date ASC",
        [$active_profile_id],
        'i'
    );
    
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $dates_range[$date] = 0;
    }
    
    foreach ($daily_clicks as $row) {
        $dates_range[$row['date']] = intval($row['clicks']);
    }
    
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
        GROUP BY location ORDER BY clicks DESC LIMIT 10",
        [$active_profile_id],
        'i'
    );
}

$page_title = "Dashboard";
include '../partials/admin_header.php';
?>
<body>
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="fw-bold mb-1">Halo, <?= htmlspecialchars($current_username) ?>! 👋</h1>
                <p class="text-muted">Kelola semua link dan profil Anda dari sini.</p>
            </div>
        </div>

        <?php if (!$active_profile_id): ?>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Anda belum memiliki profil.</strong> Silakan buka <a href="settings.php" class="alert-link">Pengaturan</a> untuk membuat profil pertama Anda.
            </div>
        <?php endif; ?>

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

        <?php if ($active_profile_id): ?>
        <!-- Stat Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-2">Total Links</h6>
                        <h2 class="fw-bold mb-0"><?= count($links) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-2">Total Klik</h6>
                        <h2 class="fw-bold mb-0"><?= array_sum(array_column($links, 'click_count')) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="stat-card card h-100">
                    <div class="card-body">
                        <h6 class="text-uppercase text-muted mb-2">Page Slug Aktif</h6>
                        <h2 class="fw-bold mb-0 fs-5 text-truncate" title="<?= htmlspecialchars($current_page_slug) ?>"><?= htmlspecialchars($current_page_slug) ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Charts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up"></i> Analytics Dashboard</h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="analyticsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="trends-tab" data-bs-toggle="tab" data-bs-target="#trends" type="button">
                                    <i class="bi bi-graph-up-arrow"></i> Tren Klik
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button">
                                    <i class="bi bi-bar-chart"></i> Performa Link
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="traffic-tab" data-bs-toggle="tab" data-bs-target="#traffic" type="button">
                                    <i class="bi bi-pie-chart"></i> Sumber Traffic
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3" id="analyticsTabContent">
                            <div class="tab-pane fade show active" id="trends" role="tabpanel">
                                <div id="clickTrendsChart" style="height: 400px;"></div>
                            </div>
                            <div class="tab-pane fade" id="performance" role="tabpanel">
                                <div id="linkPerformanceChart" style="height: 400px;"></div>
                            </div>
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
                            <i class="bi bi-info-circle"></i> Drag & drop untuk mengubah urutan.
                        </p>

                        <div id="linksList">
                            <?php if (empty($links)): ?>
                                <div class="text-center py-5 border rounded-3">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <p class="text-muted mt-3 mb-0">Belum ada link. Tambahkan link pertama Anda!</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($links as $link): ?>
                                    <div class="link-item card card-body mb-2" data-id="<?= $link['link_id'] ?>">
                                        <div class="d-flex align-items-center">
                                            <div class="drag-handle me-3 text-muted" style="cursor: grab;">
                                                <i class="bi bi-grip-vertical fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="<?= htmlspecialchars($link['icon_class']) ?> me-2 fs-5"></i>
                                                    <strong class="text-break"><?= htmlspecialchars($link['title']) ?></strong>
                                                </div>
                                                <small class="text-muted text-break d-block">
                                                    <i class="bi bi-link-45deg"></i> <?= htmlspecialchars($link['url']) ?>
                                                </small>
                                                <div class="mt-2">
                                                    <span class="badge bg-light text-dark border">
                                                        <i class="bi bi-mouse"></i> <?= $link['click_count'] ?> klik
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ms-2">
                                                <button class="btn btn-sm btn-outline-secondary me-1"
                                                        onclick="editLink(<?= htmlspecialchars(json_encode($link), ENT_QUOTES, 'UTF-8') ?>)">
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
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">
                            <i class="bi bi-lightbulb-fill text-warning"></i> Tips Cepat
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>Gunakan judul yang jelas.</li>
                            <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>Pilih ikon yang sesuai.</li>
                            <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>Urutkan link terpenting di atas.</li>
                            <li class="mb-2 d-flex"><i class="bi bi-check-circle-fill text-success me-2 mt-1"></i>Cek statistik klik secara berkala.</li>
                        </ul>
                        <hr>
                        <h6 class="fw-bold mb-2">Link Publik Anda:</h6>
                        <div class="input-group">
                            <input type="text" class="form-control"
                                   value="https://linkmy.iet.ovh/<?= htmlspecialchars($current_page_slug) ?>"
                                   id="publicLink" readonly>
                            <button class="btn btn-outline-secondary" onclick="copyLink(this)">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; // end of if($active_profile_id) ?>
    </div>

    <!-- Add Link Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle"></i> Tambah Link Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Judul Link</label>
                            <input type="text" class="form-control" name="title" required placeholder="Contoh: Instagram Saya">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">URL</label>
                            <input type="url" class="form-control" name="url" required placeholder="https://instagram.com/username">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kategori (Opsional)</label>
                            <select class="form-select" name="category_id">
                                <option value="">Tanpa Kategori</option>
                                <?php foreach ($user_categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Kelompokkan link dalam kategori. <a href="categories.php">Kelola Kategori</a></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ikon (Bootstrap Icons)</label>
                            <div class="input-group mb-2">
                                <span class="input-group-text"><i id="iconPreview" class="bi bi-link-45deg fs-5"></i></span>
                                <input type="text" class="form-control" name="icon_class" id="iconInput" value="bi-link-45deg" placeholder="Contoh: bi-instagram">
                            </div>
                            <div class="alert alert-info py-2 px-3 mb-2 d-flex align-items-center">
                                <i class="bi bi-info-circle me-2"></i
                                ><small>Gunakan format <code>bi-[nama-ikon]</code>. Contoh: <code>bi-github</code>. Lihat <a href="https://icons.getbootstrap.com/" target="_blank" class="alert-link">Bootstrap Icons</a>.</small>
                            </div>
                            <div>
                                <label class="form-label text-muted small mb-1">Ikon Populer:</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php $popular_icons = ['instagram', 'facebook', 'twitter-x', 'tiktok', 'youtube', 'linkedin', 'github', 'whatsapp', 'telegram', 'discord', 'spotify', 'envelope-fill', 'globe', 'link-45deg']; ?>
                                    <?php foreach ($popular_icons as $icon): ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary icon-template" data-icon="bi-<?= $icon ?>" title="<?= ucfirst($icon) ?>">
                                        <i class="bi bi-<?= $icon ?>"></i>
                                    </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_link" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="link_id" id="edit_link_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill"></i> Edit Link</h5>
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
                            <label class="form-label fw-semibold">Kategori</label>
                            <select class="form-select" name="category_id" id="edit_category">
                                <option value="">Tanpa Kategori</option>
                                <?php foreach ($user_categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ikon</label>
                            <input type="text" class="form-control" name="icon_class" id="edit_icon_class">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_link" class="btn btn-primary"><i class="bi bi-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../partials/admin_footer.php'; ?>
    
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Live icon preview
        const iconInput = document.getElementById('iconInput');
        const iconPreview = document.getElementById('iconPreview');
        if(iconInput) {
            iconInput.addEventListener('input', function(e) {
                iconPreview.className = 'bi ' + (e.target.value.trim() || 'bi-link-45deg') + ' fs-5';
            });
        }

        document.querySelectorAll('.icon-template').forEach(btn => {
            btn.addEventListener('click', function() {
                const iconClass = this.getAttribute('data-icon');
                if(iconInput) iconInput.value = iconClass;
                if(iconPreview) iconPreview.className = 'bi ' + iconClass + ' fs-5';
                
                document.querySelectorAll('.icon-template.active').forEach(b => b.classList.remove('active', 'btn-primary'));
                this.classList.add('active', 'btn-primary');
            });
        });

        // SortableJS for drag & drop
        const linksList = document.getElementById('linksList');
        if (linksList) {
            new Sortable(linksList, {
                animation: 150,
                handle: '.drag-handle',
                onUpdate: function (evt) {
                    const order = Array.from(evt.to.children).map((el, index) => ({
                        id: el.dataset.id,
                        order: index + 1
                    }));

                    fetch('dashboard.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            update_order: true,
                            order_data: order
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            console.error('Failed to save order:', data.message);
                            alert('Gagal menyimpan urutan baru.');
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan urutan.');
                    });
                }
            });
        }

        // Highcharts Theme Integration
        const isDarkMode = document.documentElement.getAttribute('data-theme') === 'dark';
        const chartBackgroundColor = isDarkMode ? '#212529' : '#ffffff';
        const chartTextColor = isDarkMode ? '#f8f9fa' : '#333333';
        const chartGridLineColor = isDarkMode ? '#495057' : '#e6e6e6';

        Highcharts.setOptions({
            colors: ['#0ea5e9', '#06b6d4', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
            chart: {
                backgroundColor: chartBackgroundColor,
                style: {
                    fontFamily: 'Inter, sans-serif',
                    color: chartTextColor
                }
            },
            title: { style: { color: chartTextColor } },
            subtitle: { style: { color: chartTextColor } },
            xAxis: {
                labels: { style: { color: chartTextColor } },
                lineColor: chartGridLineColor,
                tickColor: chartGridLineColor,
                title: { style: { color: chartTextColor } }
            },
            yAxis: {
                labels: { style: { color: chartTextColor } },
                gridLineColor: chartGridLineColor,
                lineColor: chartGridLineColor,
                tickColor: chartGridLineColor,
                title: { style: { color: chartTextColor } }
            },
            legend: {
                itemStyle: { color: chartTextColor },
                itemHoverStyle: { color: '#ffffff' },
                itemHiddenStyle: { color: '#606063' }
            },
            credits: { enabled: false },
            tooltip: {
                backgroundColor: isDarkMode ? 'rgba(33, 37, 41, 0.85)' : 'rgba(255, 255, 255, 0.85)',
                style: { color: chartTextColor }
            }
        });

        // 1. Click Trends Chart
        if (document.getElementById('clickTrendsChart')) {
            Highcharts.chart('clickTrendsChart', {
                chart: { type: 'area' },
                title: { text: 'Tren Klik - 7 Hari Terakhir', align: 'left' },
                xAxis: {
                    categories: <?= json_encode(array_keys($dates_range)) ?>,
                    labels: {
                        formatter: function() {
                            const parts = this.value.split('-');
                            const date = new Date(parts[0], parts[1] - 1, parts[2]);
                            return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
                        }
                    }
                },
                yAxis: { title: { text: 'Jumlah Klik' }, min: 0 },
                tooltip: { shared: true, valueSuffix: ' klik' },
                plotOptions: {
                    area: {
                        fillColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [ [0, 'rgba(14, 165, 233, 0.5)'], [1, 'rgba(14, 165, 233, 0.05)'] ]
                        },
                        marker: { radius: 4 },
                        lineWidth: 2,
                    }
                },
                series: [{ name: 'Klik Harian', data: <?= json_encode(array_values($dates_range)) ?>, color: '#0ea5e9' }]
            });
        }

        // 2. Link Performance Chart
        if (document.getElementById('linkPerformanceChart')) {
            Highcharts.chart('linkPerformanceChart', {
                chart: { type: 'column' },
                title: { text: 'Top 10 Link Paling Banyak Diklik', align: 'left' },
                xAxis: { categories: <?= json_encode(array_column($link_performance, 'title')) ?>, labels: { rotation: -45, style: { fontSize: '11px' } } },
                yAxis: { min: 0, title: { text: 'Total Klik' } },
                tooltip: { pointFormat: 'Klik: <b>{point.y}</b>' },
                series: [{ name: 'Clicks', data: <?= json_encode(array_map('intval', array_column($link_performance, 'click_count'))) ?>, colorByPoint: true, showInLegend: false }]
            });
        }

        // 3. Traffic Sources Chart
        if (document.getElementById('trafficSourcesChart')) {
            Highcharts.chart('trafficSourcesChart', {
                chart: { type: 'pie' },
                title: { text: 'Traffic Berdasarkan Lokasi', align: 'left' },
                tooltip: { pointFormat: '<b>{point.percentage:.1f}%</b> ({point.y} klik)' },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: { enabled: true, format: '{point.name}: {point.percentage:.1f} %' },
                        showInLegend: true
                    }
                },
                series: [{
                    name: 'Traffic Share',
                    colorByPoint: true,
                    data: [
                        <?php 
                        $sources_data = [];
                        foreach ($click_by_location as $location) {
                            $sources_data[] = '{ name: "' . htmlspecialchars($location['location'], ENT_QUOTES) . '", y: ' . intval($location['clicks']) . ' }';
                        }
                        echo implode(',', $sources_data);
                        ?>
                    ]
                }]
            });
        }
    });

    function editLink(link) {
        document.getElementById('edit_link_id').value = link.link_id;
        document.getElementById('edit_title').value = link.title;
        document.getElementById('edit_url').value = link.url;
        document.getElementById('edit_icon_class').value = link.icon_class;
        document.getElementById('edit_category').value = link.category_id || '';
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function copyLink(button) {
        const input = document.getElementById('publicLink');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(input.value).then(() => {
            const originalIcon = button.innerHTML;
            button.innerHTML = '<i class="bi bi-check-lg"></i>';
            setTimeout(() => {
                button.innerHTML = originalIcon;
            }, 2000);
        }).catch(err => {
            alert('Gagal menyalin link.');
        });
    }
    </script>
</body>
</html>