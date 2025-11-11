<?php
    require_once __DIR__ . '/../config/auth_check.php';
    require_once __DIR__ .  '/../config/db.php';
    
    $success = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])){
        $tittle = trim($_POST['title']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);

        if (empty($tittle) || empty($url)){
            $error = 'Judul dan URL harus di isi';
        } else {
            $last_order = get_single_row("SELECT MAX(order_index) as max_order FROM links where user_id = ?", [$current_user_id], 'i');
            $new_order = ($last_order('max_order') ?? 0) + 1;

            $query = "INSERT INTO links (user_id, title, url, icon_class, order_index) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'isssi', $current_user_id, $title, $url, $icon_class, $new_order);
            if (mysqli_stmt_execute($stmt)){
                $success = 'Link berhasil ditambahkan';
            } else {
                $error = 'Gagal menambahkan link';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_link'])) {
        $link_id = intval($_POST['link_id']);
        $title = trim($_POST['title']);
        $url = trim($_POST['url']);
        $icon_class = trim($_POST['icon_class']);
        
        if (empty($title) || empty($url)) {
            $error = 'Judul dan URL harus diisi!';
        } else {
            $query = "UPDATE links SET title = ?, url = ?, icon_class = ? WHERE link_id = ? AND user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssii', $title, $url, $icon_class, $link_id, $current_user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Link berhasil diupdate!';
            } else {
                $error = 'Gagal mengupdate link!';
            }
        }
    }

    if (isset($_GET['delete'])) {
        $link_id = intval($_GET['delete']);
        
        $query = "DELETE FROM links WHERE link_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $link_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Link berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus link!';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
        $order_data = json_decode($_POST['order_data'], true);
        
        if ($order_data) {
            foreach ($order_data as $item) {
                $link_id = intval($item['id']);
                $order_index = intval($item['order']);
                
                $query = "UPDATE links SET order_index = ? WHERE link_id = ? AND user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'iii', $order_index, $link_id, $current_user_id);
                mysqli_stmt_execute($stmt);
            }
            
            echo json_encode(['success' => true]);
            exit;
        }
    }
    $links = get_all_rows("SELECT * FROM links WHERE user_id = ? ORDER BY order_index ASC", [$current_user_id], 'i');
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
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-house-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appearance.php">
                            <i class="bi bi-palette-fill"></i> Appearance
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="bi bi-gear-fill"></i> Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../profile.php?slug=<?= $current_page_slug ?>" target="_blank">
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
                                   value="linkmy.fahmi.app/<?= $current_page_slug ?>"
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
                            <label class="form-label fw-semibold">Ikon (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="icon_class"
                                   value="bi-link-45deg"
                                   placeholder="bi-instagram">
                            <small class="text-muted">
                                Lihat: <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
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
    <script src="../assets/js/admin.js"></script>
    <script>
        function editLink(link) {
            document.getElementById('edit_link_id').value = link.link_id;
            document.getElementById('edit_title').value = link.title;
            document.getElementById('edit_url').value = link.url;
            document.getElementById('edit_icon').value = link.icon_class;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
        function copyLink() {
            const input = document.getElementById('publicLink');
            input.select();
            document.execCommand('copy');
            alert('Link berhasil disalin!');
        }
    </script>
</body>
</html>