<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';
require_once '../config/session_handler.php';

// Multi-profile: Initialize active profile
if (!isset($_SESSION['active_profile_id'])) {
    // Get user's primary profile
    $primary_profile = get_single_row(
        "SELECT id FROM profiles WHERE user_id = ? AND display_order = 0 ORDER BY id ASC LIMIT 1",
        [$current_user_id],
        'i'
    );
    
    if ($primary_profile) {
        $_SESSION['active_profile_id'] = $primary_profile['id'];
    } else {
        // If no profiles, redirect to create one
        header('Location: settings.php');
        exit;
    }
}

$active_profile_id = $_SESSION['active_profile_id'];

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']) ?: 'bi-folder';
    $category_color = trim($_POST['category_color']) ?: '#0d6efd';
    
    if (empty($category_name)) {
        $_SESSION['error'] = 'Nama kategori harus diisi!';
    } else {
        $last_order = get_single_row("SELECT MAX(position) as max_order FROM categories_v3 WHERE profile_id = ?", [$active_profile_id], 'i');
        $new_order = ($last_order['max_order'] ?? 0) + 1;
        
        $query = "INSERT INTO categories_v3 (profile_id, name, icon, color, position) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssi', $active_profile_id, $category_name, $category_icon, $category_color, $new_order);
    
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Kategori berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Gagal menambahkan kategori!';
        }
    }
    header("Location: categories.php");
    exit;
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = intval($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']) ?: 'bi-folder';
    $category_color = trim($_POST['category_color']) ?: '#0d6efd';
    
    if (empty($category_name)) {
        $_SESSION['error'] = 'Nama kategori harus diisi!';
    } else {
        $query = "UPDATE categories_v3 SET name = ?, icon = ?, color = ? WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssii', $category_name, $category_icon, $category_color, $category_id, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Kategori berhasil diupdate!';
        } else {
            $_SESSION['error'] = 'Gagal mengupdate kategori!';
        }
    }
    header("Location: categories.php");
    exit;
}

// Delete category
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    $check = get_single_row("SELECT COUNT(*) as count FROM links WHERE category_id = ?", [$category_id], 'i');
    
    if ($check['count'] > 0) {
        $_SESSION['error'] = 'Kategori tidak bisa dihapus karena masih ada ' . $check['count'] . ' link yang terhubung!';
    } else {
        $query = "DELETE FROM categories_v3 WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $category_id, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = 'Kategori berhasil dihapus!';
        } else {
            $_SESSION['error'] = 'Gagal menghapus kategori!';
        }
    }
    header("Location: categories.php");
    exit;
}

// Load categories for active profile
$categories = get_all_rows("SELECT c.id as category_id, c.name as category_name, c.icon as category_icon, c.color as category_color, c.position, c.is_expanded, COUNT(l.id) as link_count FROM categories_v3 c LEFT JOIN links l ON c.id = l.category_id WHERE c.profile_id = ? GROUP BY c.id ORDER BY c.position ASC", [$active_profile_id], 'i');

$page_title = "Kategori";
include '../partials/admin_header.php';
?>
<body>
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h1 class="fw-bold mb-1">Kategori Link</h1>
                <p class="text-muted mb-0">Kelola kategori untuk mengelompokkan link Anda. Total: <?= count($categories) ?> kategori.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle me-2"></i> Buat Kategori Baru
            </button>
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

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x display-1 text-muted"></i>
                        <h4 class="mt-3 fw-bold">Belum Ada Kategori</h4>
                        <p class="text-muted">Mulai kelompokkan link Anda dengan membuat kategori pertama!</p>
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle me-2"></i> Buat Kategori Pertama
                        </button>
                    </div>
                <?php else: ?>
                    <div class="list-group">
                    <?php foreach ($categories as $cat): ?>
                        <div class="list-group-item" style="--cat-color: <?= htmlspecialchars($cat['category_color'] ?? '#0d6efd') ?>;">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="category-color-preview" style="background-color: <?= htmlspecialchars($cat['category_color'] ?? '#0d6efd') ?>;">
                                        <i class="<?= htmlspecialchars($cat['category_icon'] ?? 'bi-folder') ?> fs-4 text-white"></i>
                                    </div>
                                </div>
                                <div class="col">
                                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($cat['category_name']) ?></h5>
                                    <small class="text-muted">
                                        <span class="badge bg-light text-dark border me-2"><i class="bi bi-link-45deg"></i> <?= $cat['link_count'] ?> links</span>
                                        Icon: <code><?= htmlspecialchars($cat['category_icon'] ?? 'bi-folder') ?></code>
                                    </small>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-outline-secondary me-1" onclick='editCategory(<?= json_encode($cat) ?>)'>
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <?php if ($cat['link_count'] == 0): ?>
                                        <a href="?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-secondary" disabled title="Tidak dapat dihapus karena berisi link">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modals -->
    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Kategori Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warna</label>
                            <input type="color" class="form-control form-control-color" name="category_color" value="#0d6efd">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ikon</label>
                            <input type="text" class="form-control" name="category_icon" value="bi-folder">
                            <small class="text-muted">Contoh: <code>bi-instagram</code>. Lihat <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold"><i class="bi bi-pencil-fill me-2"></i>Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control" name="category_name" id="edit_category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Warna</label>
                            <input type="color" class="form-control form-control-color" name="category_color" id="edit_category_color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ikon</label>
                            <input type="text" class="form-control" name="category_icon" id="edit_category_icon">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="edit_category" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../partials/admin_footer.php'; ?>

    <script>
    function editCategory(category) {
        document.getElementById('edit_category_id').value = category.category_id;
        document.getElementById('edit_category_name').value = category.category_name;
        document.getElementById('edit_category_icon').value = category.category_icon;
        document.getElementById('edit_category_color').value = category.category_color;
        var myModal = new bootstrap.Modal(document.getElementById('editModal'));
        myModal.show();
    }
    </script>
    <style>
        .category-color-preview {
            width: 50px;
            height: 50px;
            border-radius: 0.5rem;
            border: 2px solid rgba(0,0,0,0.1);
        }
        .list-group-item {
            border-left: 5px solid var(--cat-color);
            margin-bottom: 0.5rem;
            border-radius: 0.5rem !important;
        }
    </style>
</body>
</html>


