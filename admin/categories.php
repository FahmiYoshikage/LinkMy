<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

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
        die('No profile found for this user!');
    }
}

$active_profile_id = $_SESSION['active_profile_id'];

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
        $last_order = get_single_row("SELECT MAX(position) as max_order FROM categories_v3 WHERE profile_id = ?", [$active_profile_id], 'i');
        $new_order = ($last_order['max_order'] ?? 0) + 1;
        
        $query = "INSERT INTO categories_v3 (profile_id, name, icon, color, position) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'isssi', $active_profile_id, $category_name, $category_icon, $category_color, $new_order);
    
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
        $query = "UPDATE categories_v3 SET name = ?, icon = ?, color = ? WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'sssii', $category_name, $category_icon, $category_color, $category_id, $active_profile_id);
        
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
    
    $check = get_single_row("SELECT COUNT(*) as count FROM links WHERE category_id = ?", [$category_id], 'i');
    
    if ($check['count'] > 0) {
        $error = 'Kategori tidak bisa dihapus karena masih ada ' . $check['count'] . ' link!';
    } else {
        $query = "DELETE FROM categories_v3 WHERE id = ? AND profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $category_id, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Kategori berhasil dihapus!';
        } else {
            $error = 'Gagal menghapus kategori!';
        }
    }
}

// Load categories for active profile
$categories = get_all_rows("SELECT c.*, COUNT(l.id) as link_count FROM categories_v3 c LEFT JOIN links l ON c.id = l.category_id WHERE c.profile_id = ? GROUP BY c.id ORDER BY c.position ASC", [$active_profile_id], 'i');
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
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh;
            padding-top: 76px;
        }
        
        .container { 
            padding-bottom: 3rem;
            margin-top: 1rem;
        }
        .card { 
            border: none; 
            border-radius: 20px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .category-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #fff 100%);
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--cat-color);
        }
        .category-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            transform: translateY(-3px) translateX(5px);
            border-color: var(--cat-color);
        }
        .category-color-preview {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 0.5rem;
            max-height: 300px;
            overflow-y: auto;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .icon-option {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1.3rem;
        }
        .icon-option:hover {
            background: white;
            border-color: #667eea;
            transform: scale(1.1);
        }
        .icon-option.selected {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .header-card {
            background: rgba(255,255,255,0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container my-5">
        <div class="header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-2">
                        <i class="bi bi-folder-fill" style="color: #667eea;"></i> Link Categories
                    </h2>
                    <p class="text-muted mb-0">Organize your links • <?= count($categories) ?> categories</p>
                </div>
                <button class="btn btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 15px; padding: 0.75rem 2rem; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle me-2"></i> New Category
                </button>
            </div>
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
            <div class="card-body p-4">
                <?php if (empty($categories)): ?>
                    <div class="text-center py-5">
                        <div style="font-size: 5rem; opacity: 0.3;">
                            <i class="bi bi-folder-x"></i>
                        </div>
                        <h4 class="mt-3 fw-bold">No Categories Yet</h4>
                        <p class="text-muted">Start organizing your links by creating your first category!</p>
                        <button class="btn btn-primary btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="bi bi-plus-circle"></i> Create First Category
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="category-card" data-id="<?= $cat['category_id'] ?>" style="--cat-color: <?= htmlspecialchars($cat['category_color']) ?>;">
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <div class="category-color-preview" style="background: <?= htmlspecialchars($cat['category_color']) ?>;">
                                    <i class="<?= htmlspecialchars($cat['category_icon']) ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1 fw-bold"><?= htmlspecialchars($cat['category_name']) ?></h5>
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-link-45deg"></i> <?= $cat['link_count'] ?> links
                                        </span>
                                        <small class="text-muted">
                                            <code><?= htmlspecialchars($cat['category_icon']) ?></code>
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline-primary" onclick="editCategory(<?= htmlspecialchars(json_encode($cat)) ?>)" style="border-radius: 10px;">
                                        <i class="bi bi-pencil-fill"></i> Edit
                                    </button>
                                    <?php if ($cat['link_count'] == 0): ?>
                                        <a href="?delete=<?= $cat['category_id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this category?')" style="border-radius: 10px;">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled title="Cannot delete - has <?= $cat['link_count'] ?> links" style="border-radius: 10px;">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    <?php endif; ?>
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
                            <input type="text" class="form-control form-control-lg" name="category_name" id="add_category_name" required placeholder="e.g., Social Media">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icon</label>
                            <input type="text" class="form-control" name="category_icon" id="add_category_icon" value="bi-folder" required readonly>
                            <div class="icon-picker mt-2">
                                <div class="icon-option selected" data-icon="bi-folder"><i class="bi bi-folder"></i></div>
                                <div class="icon-option" data-icon="bi-heart-fill"><i class="bi bi-heart-fill"></i></div>
                                <div class="icon-option" data-icon="bi-star-fill"><i class="bi bi-star-fill"></i></div>
                                <div class="icon-option" data-icon="bi-briefcase-fill"><i class="bi bi-briefcase-fill"></i></div>
                                <div class="icon-option" data-icon="bi-camera-fill"><i class="bi bi-camera-fill"></i></div>
                                <div class="icon-option" data-icon="bi-music-note-beamed"><i class="bi bi-music-note-beamed"></i></div>
                                <div class="icon-option" data-icon="bi-palette-fill"><i class="bi bi-palette-fill"></i></div>
                                <div class="icon-option" data-icon="bi-controller"><i class="bi bi-controller"></i></div>
                                <div class="icon-option" data-icon="bi-shop"><i class="bi bi-shop"></i></div>
                                <div class="icon-option" data-icon="bi-code-slash"><i class="bi bi-code-slash"></i></div>
                                <div class="icon-option" data-icon="bi-book-fill"><i class="bi bi-book-fill"></i></div>
                                <div class="icon-option" data-icon="bi-trophy-fill"><i class="bi bi-trophy-fill"></i></div>
                                <div class="icon-option" data-icon="bi-lightning-fill"><i class="bi bi-lightning-fill"></i></div>
                                <div class="icon-option" data-icon="bi-fire"><i class="bi bi-fire"></i></div>
                                <div class="icon-option" data-icon="bi-globe"><i class="bi bi-globe"></i></div>
                                <div class="icon-option" data-icon="bi-people-fill"><i class="bi bi-people-fill"></i></div>
                                <div class="icon-option" data-icon="bi-chat-dots-fill"><i class="bi bi-chat-dots-fill"></i></div>
                                <div class="icon-option" data-icon="bi-image-fill"><i class="bi bi-image-fill"></i></div>
                                <div class="icon-option" data-icon="bi-film"><i class="bi bi-film"></i></div>
                                <div class="icon-option" data-icon="bi-bag-fill"><i class="bi bi-bag-fill"></i></div>
                                <div class="icon-option" data-icon="bi-gift-fill"><i class="bi bi-gift-fill"></i></div>
                                <div class="icon-option" data-icon="bi-rocket-takeoff-fill"><i class="bi bi-rocket-takeoff-fill"></i></div>
                                <div class="icon-option" data-icon="bi-cup-hot-fill"><i class="bi bi-cup-hot-fill"></i></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <input type="color" class="form-control form-control-color" name="category_color" id="add_category_color" value="#667eea">
                                <div class="flex-grow-1">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#667eea">Purple</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#f093fb">Pink</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#4facfe">Blue</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#43e97b">Green</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#fa709a">Red</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="category-color-preview" id="add_preview_box" style="background: #667eea; width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="bi-folder" id="add_preview_icon"></i>
                                </div>
                                <div>
                                    <strong>Preview</strong>
                                    <p class="mb-0 text-muted small">How it will look</p>
                                </div>
                            </div>
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
                            <input type="text" class="form-control form-control-lg" name="category_name" id="edit_category_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Icon</label>
                            <input type="text" class="form-control" name="category_icon" id="edit_category_icon" required readonly>
                            <div class="icon-picker mt-2" id="edit_icon_picker">
                                <div class="icon-option" data-icon="bi-folder"><i class="bi bi-folder"></i></div>
                                <div class="icon-option" data-icon="bi-heart-fill"><i class="bi bi-heart-fill"></i></div>
                                <div class="icon-option" data-icon="bi-star-fill"><i class="bi bi-star-fill"></i></div>
                                <div class="icon-option" data-icon="bi-briefcase-fill"><i class="bi bi-briefcase-fill"></i></div>
                                <div class="icon-option" data-icon="bi-camera-fill"><i class="bi bi-camera-fill"></i></div>
                                <div class="icon-option" data-icon="bi-music-note-beamed"><i class="bi bi-music-note-beamed"></i></div>
                                <div class="icon-option" data-icon="bi-palette-fill"><i class="bi bi-palette-fill"></i></div>
                                <div class="icon-option" data-icon="bi-controller"><i class="bi bi-controller"></i></div>
                                <div class="icon-option" data-icon="bi-shop"><i class="bi bi-shop"></i></div>
                                <div class="icon-option" data-icon="bi-code-slash"><i class="bi bi-code-slash"></i></div>
                                <div class="icon-option" data-icon="bi-book-fill"><i class="bi bi-book-fill"></i></div>
                                <div class="icon-option" data-icon="bi-trophy-fill"><i class="bi bi-trophy-fill"></i></div>
                                <div class="icon-option" data-icon="bi-lightning-fill"><i class="bi bi-lightning-fill"></i></div>
                                <div class="icon-option" data-icon="bi-fire"><i class="bi bi-fire"></i></div>
                                <div class="icon-option" data-icon="bi-globe"><i class="bi bi-globe"></i></div>
                                <div class="icon-option" data-icon="bi-people-fill"><i class="bi bi-people-fill"></i></div>
                                <div class="icon-option" data-icon="bi-chat-dots-fill"><i class="bi bi-chat-dots-fill"></i></div>
                                <div class="icon-option" data-icon="bi-image-fill"><i class="bi bi-image-fill"></i></div>
                                <div class="icon-option" data-icon="bi-film"><i class="bi bi-film"></i></div>
                                <div class="icon-option" data-icon="bi-bag-fill"><i class="bi bi-bag-fill"></i></div>
                                <div class="icon-option" data-icon="bi-gift-fill"><i class="bi bi-gift-fill"></i></div>
                                <div class="icon-option" data-icon="bi-rocket-takeoff-fill"><i class="bi bi-rocket-takeoff-fill"></i></div>
                                <div class="icon-option" data-icon="bi-cup-hot-fill"><i class="bi bi-cup-hot-fill"></i></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <div class="d-flex gap-2 align-items-center flex-wrap">
                                <input type="color" class="form-control form-control-color" name="category_color" id="edit_category_color">
                                <div class="flex-grow-1">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#667eea" data-target="edit">Purple</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#f093fb" data-target="edit">Pink</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#4facfe" data-target="edit">Blue</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#43e97b" data-target="edit">Green</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary color-preset" data-color="#fa709a" data-target="edit">Red</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-light border">
                            <div class="d-flex align-items-center gap-3">
                                <div class="category-color-preview" id="edit_preview_box" style="background: #667eea; width: 50px; height: 50px; font-size: 1.2rem;">
                                    <i class="bi-folder" id="edit_preview_icon"></i>
                                </div>
                                <div>
                                    <strong>Preview</strong>
                                    <p class="mb-0 text-muted small">How it will look</p>
                                </div>
                            </div>
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
        // Icon picker
        document.querySelectorAll('.icon-option').forEach(option => {
            option.addEventListener('click', function() {
                const icon = this.dataset.icon;
                const modal = this.closest('.modal');
                const isEditModal = modal && modal.id === 'editModal';
                
                this.parentElement.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                
                if (isEditModal) {
                    document.getElementById('edit_category_icon').value = icon;
                    document.getElementById('edit_preview_icon').className = icon;
                } else {
                    document.getElementById('add_category_icon').value = icon;
                    document.getElementById('add_preview_icon').className = icon;
                }
            });
        });
        
        // Color presets
        document.querySelectorAll('.color-preset').forEach(btn => {
            btn.addEventListener('click', function() {
                const color = this.dataset.color;
                const target = this.dataset.target;
                
                if (target === 'edit') {
                    document.getElementById('edit_category_color').value = color;
                    document.getElementById('edit_preview_box').style.background = color;
                } else {
                    document.getElementById('add_category_color').value = color;
                    document.getElementById('add_preview_box').style.background = color;
                }
            });
        });
        
        // Live color preview
        document.getElementById('add_category_color').addEventListener('input', function() {
            document.getElementById('add_preview_box').style.background = this.value;
        });
        
        document.getElementById('edit_category_color').addEventListener('input', function() {
            document.getElementById('edit_preview_box').style.background = this.value;
        });
        
        function editCategory(cat) {
            document.getElementById('edit_category_id').value = cat.category_id;
            document.getElementById('edit_category_name').value = cat.category_name;
            document.getElementById('edit_category_icon').value = cat.category_icon;
            document.getElementById('edit_category_color').value = cat.category_color;
            
            document.getElementById('edit_preview_icon').className = cat.category_icon;
            document.getElementById('edit_preview_box').style.background = cat.category_color;
            
            document.querySelectorAll('#edit_icon_picker .icon-option').forEach(opt => {
                opt.classList.remove('selected');
                if (opt.dataset.icon === cat.category_icon) {
                    opt.classList.add('selected');
                }
            });
            
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }
    </script>
</body>
</html>
