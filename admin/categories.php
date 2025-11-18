<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

$success = '';
$error = '';

// Add new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']) ?: 'bi-folder';
    $category_color = trim($_POST['category_color']) ?: '#667eea';
    
    if (empty($category_name)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        // Get last order
        $last_order = get_single_row("SELECT MAX(display_order) as max_order FROM link_categories WHERE user_id = ?", [$current_user_id], 'i');
        $new_order = ($last_order['max_order'] ?? 0) + 1;
        
        $query = "INSERT INTO link_categories (user_id, category_name, category_icon, category_color, display_order) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssi', $current_user_id, $category_name, $category_icon, $category_color, $new_order);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Kategori berhasil ditambahkan!';
        } else {
            $error = 'Gagal menambahkan kategori!';
        }
    }
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $category_id = intval($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    $category_icon = trim($_POST['category_icon']) ?: 'bi-folder';
    $category_color = trim($_POST['category_color']) ?: '#667eea';
    
    if (empty($category_name)) {
        $error = 'Nama kategori harus diisi!';
    } else {
        $query = "UPDATE link_categories SET category_name = ?, category_icon = ?, category_color = ? WHERE category_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssii', $category_name, $category_icon, $category_color, $category_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Kategori berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate kategori!';
        }
    }
}

// Delete category
if (isset($_GET['delete'])) {
    $category_id = intval($_GET['delete']);
    
    // Check if category has links
    $check = get_single_row("SELECT COUNT(*) as count FROM links WHERE category_id = ?", [$category_id], 'i');
    
    if ($check['count'] > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih ada ' . $check['count'] . ' link! Pindahkan atau hapus link terlebih dahulu.';
    } else {
        $query = "DELETE FROM link_categories WHERE category_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $category_id, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori!';
        }
    }
}

// Get all categories
$categories = get_all_rows("SELECT c.*, COUNT(l.link_id) as link_count FROM link_categories c LEFT JOIN links l ON c.category_id = l.category_id WHERE c.user_id = ? GROUP BY c.category_id ORDER BY c.display_order ASC", [$current_user_id], 'i');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - LinkMy</title>
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .category-card {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: move;
        }
        .category-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .category-color-preview {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-folder-fill text-primary"></i> Link Categories
                </h2>
                <p class="text-muted mb-0">Kelola kategori untuk grouping links Anda</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                <i class="bi bi-plus-circle"></i> Add Category
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-folder-x display-1 text-muted"></i>
                        <p class="text-muted mt-3">Belum ada kategori. Tambahkan kategori pertama Anda!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-card" data-id="<?= $cat['category_id'] ?>">
                            <div class="d-flex align-items-center">
                                <div class="drag-handle me-3">
                                    <i class="bi bi-grip-vertical fs-4 text-muted"></i>
                                </div>
                                <div class="category-color-preview me-3" style="background: <?= htmlspecialchars($cat['category_color']) ?>;"></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="<?= htmlspecialchars($cat['category_icon']) ?> me-2" style="color: <?= htmlspecialchars($cat['category_color']) ?>;"></i>
                                        <strong><?= htmlspecialchars($cat['category_name']) ?></strong>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-link-45deg"></i> <?= $cat['link_count'] ?> links
                                    </small>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <a href="?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> How to Use</h6>
            <ol class="mb-0 small">
                <li>Create categories (e.g., "Social Media", "Work", "Portfolio")</li>
                <li>Go to Dashboard and edit your links</li>
                <li>Assign each link to a category</li>
                <li>Enable "Link Categories" in Appearance → Advanced tab</li>
                <li>Your links will be grouped by category on your profile page!</li>
            </ol>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-plus-circle"></i> Add Category
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required placeholder="e.g., Social Media">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icon (Bootstrap Icons)</label>
                            <input type="text" class="form-control" name="category_icon" value="bi-folder" placeholder="bi-folder">
                            <small class="text-muted">
                                Browse: <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a>
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="color" class="form-control form-control-color" name="category_color" value="#667eea">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="category_id" id="edit_category_id">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">
                            <i class="bi bi-pencil-fill"></i> Edit Category
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name</label>
                            <input type="text" class="form-control" name="category_name" id="edit_category_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icon</label>
                            <input type="text" class="form-control" name="category_icon" id="edit_category_icon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <input type="color" class="form-control form-control-color" name="category_color" id="edit_category_color">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_category" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(cat) {
            document.getElementById('edit_category_id').value = cat.category_id;
            document.getElementById('edit_category_name').value = cat.category_name;
            document.getElementById('edit_category_icon').value = cat.category_icon;
            document.getElementById('edit_category_color').value = cat.category_color;
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
