<?php 
   require_once '../config/auth_check.php';
    require_once '../config/db.php';

    $success = '';
    $error = '';

    $appearance = get_single_row("SELECT * FROM appearance WHERE user_id = ?", [$current_user_id], 'i');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
        $profile_title = trim($_POST['profile_title']);
        $bio = trim($_POST['bio']);
        
        $query = "UPDATE appearance SET profile_title = ?, bio = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $profile_title, $bio, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Info profil berhasil diupdate!';
            $appearance['profile_title'] = $profile_title;
            $appearance['bio'] = $bio;
        } else {
            $error = 'Gagal mengupdate info profil!';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_profile'])) {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $file_type = $_FILES['profile_pic']['type'];
            $file_size = $_FILES['profile_pic']['size'];
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Tipe file tidak diizinkan! Hanya JPG, PNG, atau GIF.';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran file terlalu besar! Maksimal 2MB.';
            } else {
                $upload_dir = '../uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $current_user_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    if ($appearance['profile_pic_filename'] !== 'default-avatar.png' && 
                        file_exists($upload_dir . $appearance['profile_pic_filename'])) {
                        unlink($upload_dir . $appearance['profile_pic_filename']);
                    }
                    $query = "UPDATE appearance SET profile_pic_filename = ? WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'si', $new_filename, $current_user_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Foto profil berhasil diupload!';
                        $appearance['profile_pic_filename'] = $new_filename;
                    } else {
                        $error = 'Gagal menyimpan foto ke database!';
                    }
                } else {
                    $error = 'Gagal mengupload file!';
                }
            }
        } else {
            $error = 'Tidak ada file yang dipilih atau terjadi kesalahan!';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_background'])) {
        if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $file_type = $_FILES['bg_image']['type'];
            $file_size = $_FILES['bg_image']['size'];
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Tipe file tidak diizinkan! Hanya JPG, PNG, atau GIF.';
            } elseif ($file_size > $max_size) {
                $error = 'Ukuran file terlalu besar! Maksimal 5MB.';
            } else {
                $upload_dir = '../uploads/backgrounds/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $extension = pathinfo($_FILES['bg_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'bg_' . $current_user_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $upload_path)) {
                    if (!empty($appearance['bg_image_filename']) && 
                        file_exists($upload_dir . $appearance['bg_image_filename'])) {
                        unlink($upload_dir . $appearance['bg_image_filename']);
                    }
                    $query = "UPDATE appearance SET bg_image_filename = ? WHERE user_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'si', $new_filename, $current_user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Background berhasil diupload!';
                        $appearance['bg_image_filename'] = $new_filename;
                    } else {
                        $error = 'Gagal menyimpan background ke database!';
                    }
                } else {
                    $error = 'Gagal mengupload file!';
                }
            }
        } else {
            $error = 'Tidak ada file yang dipilih atau terjadi kesalahan!';
        }
    }

    if (isset($_GET['remove_bg'])) {
        if (!empty($appearance['bg_image_filename'])) {
            $bg_path = '../uploads/backgrounds/' . $appearance['bg_image_filename'];
            if (file_exists($bg_path)) {
                unlink($bg_path);
            }
            $query = "UPDATE appearance SET bg_image_filename = NULL WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Background berhasil dihapus!';
                $appearance['bg_image_filename'] = null;
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_theme'])) {
        $theme_name = $_POST['theme_name'];
        $button_style = $_POST['button_style'];
        
        $query = "UPDATE appearance SET theme_name = ?, button_style = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $theme_name, $button_style, $current_user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Tema berhasil diupdate!';
            $appearance['theme_name'] = $theme_name;
            $appearance['button_style'] = $button_style;
        } else {
            $error = 'Gagal mengupdate tema!';
        }
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appearance - LinkMy</title>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
        }
        .theme-option {
            border: 3px solid transparent;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        .theme-option:hover {
            transform: translateY(-5px);
        }
        .theme-option.active {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        .theme-light { background: #ffffff; color: #333; }
        .theme-dark { background: #1a1a1a; color: #fff; }
        .theme-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-house-fill"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="appearance.php">
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
        <h2 class="fw-bold mb-4">
            <i class="bi bi-palette-fill"></i> Appearance Settings
        </h2>
        
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
            <!-- Profile Info -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-person-badge"></i> Informasi Profil
                        </h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Judul Profil</label>
                                <input type="text" class="form-control" name="profile_title" 
                                       value="<?= htmlspecialchars($appearance['profile_title'] ?? '') ?>"
                                       placeholder="Nama atau Judul Anda">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Bio</label>
                                <textarea class="form-control" name="bio" rows="3" 
                                          placeholder="Ceritakan tentang diri Anda..."><?= htmlspecialchars($appearance['bio'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" name="update_info" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Info
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Profile Picture -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-image"></i> Foto Profil
                        </h5>
                        
                        <div class="text-center mb-3">
                            <?php
                            $profile_pic_path = '../uploads/profile_pics/' . ($appearance['profile_pic_filename'] ?? 'default-avatar.png');
                            if (!file_exists($profile_pic_path)):
                            ?>
                                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 150px; height: 150px; font-size: 3rem;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            <?php else: ?>
                                <img src="<?= $profile_pic_path ?>" class="preview-image" alt="Profile Picture">
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-area mb-3">
                                <i class="bi bi-cloud-upload display-4 text-muted mb-2"></i>
                                <p class="mb-2">Upload foto profil baru</p>
                                <input type="file" class="form-control" name="profile_pic" 
                                       accept="image/jpeg,image/png,image/gif" required>
                                <small class="text-muted">Maksimal 2MB (JPG, PNG, GIF)</small>
                            </div>
                            <button type="submit" name="upload_profile" class="btn btn-primary w-100">
                                <i class="bi bi-upload"></i> Upload Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Background Image -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-card-image"></i> Background Image
                        </h5>
                        
                        <?php if (!empty($appearance['bg_image_filename'])): ?>
                            <div class="mb-3 text-center">
                                <img src="../uploads/backgrounds/<?= $appearance['bg_image_filename'] ?>" 
                                     class="img-fluid rounded" style="max-height: 200px;" alt="Background">
                                <div class="mt-2">
                                    <a href="?remove_bg=1" class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus background?')">
                                        <i class="bi bi-trash"></i> Hapus Background
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="upload-area">
                                <i class="bi bi-image display-4 text-muted mb-2"></i>
                                <p class="mb-2">Upload background image</p>
                                <input type="file" class="form-control" name="bg_image" 
                                       accept="image/jpeg,image/png,image/gif" required>
                                <small class="text-muted">Maksimal 5MB (JPG, PNG, GIF)</small>
                            </div>
                            <button type="submit" name="upload_background" class="btn btn-primary mt-3">
                                <i class="bi bi-upload"></i> Upload Background
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Theme Settings -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">
                            <i class="bi bi-palette"></i> Tema & Gaya
                        </h5>
                        
                        <form method="POST">
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <label>
                                        <input type="radio" name="theme_name" value="light" 
                                               <?= ($appearance['theme_name'] ?? 'light') === 'light' ? 'checked' : '' ?>
                                               class="d-none theme-radio">
                                        <div class="theme-option theme-light <?= ($appearance['theme_name'] ?? 'light') === 'light' ? 'active' : '' ?>">
                                            <i class="bi bi-sun-fill display-4"></i>
                                            <p class="fw-bold mb-0 mt-2">Light</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>
                                        <input type="radio" name="theme_name" value="dark" 
                                               <?= ($appearance['theme_name'] ?? 'light') === 'dark' ? 'checked' : '' ?>
                                               class="d-none theme-radio">
                                        <div class="theme-option theme-dark <?= ($appearance['theme_name'] ?? 'light') === 'dark' ? 'active' : '' ?>">
                                            <i class="bi bi-moon-stars-fill display-4"></i>
                                            <p class="fw-bold mb-0 mt-2">Dark</p>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label>
                                        <input type="radio" name="theme_name" value="gradient" 
                                               <?= ($appearance['theme_name'] ?? 'light') === 'gradient' ? 'checked' : '' ?>
                                               class="d-none theme-radio">
                                        <div class="theme-option theme-gradient <?= ($appearance['theme_name'] ?? 'light') === 'gradient' ? 'active' : '' ?>">
                                            <i class="bi bi-rainbow display-4"></i>
                                            <p class="fw-bold mb-0 mt-2">Gradient</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gaya Tombol</label>
                                <select class="form-select" name="button_style">
                                    <option value="rounded" <?= ($appearance['button_style'] ?? 'rounded') === 'rounded' ? 'selected' : '' ?>>
                                        Rounded (Sudut Melengkung)
                                    </option>
                                    <option value="sharp" <?= ($appearance['button_style'] ?? 'rounded') === 'sharp' ? 'selected' : '' ?>>
                                        Sharp (Sudut Tajam)
                                    </option>
                                    <option value="pill" <?= ($appearance['button_style'] ?? 'rounded') === 'pill' ? 'selected' : '' ?>>
                                        Pill (Bulat Penuh)
                                    </option>
                                </select>
                            </div>
                            
                            <button type="submit" name="update_theme" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Tema
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle theme option active state
        document.querySelectorAll('.theme-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.theme-option').forEach(opt => {
                    opt.classList.remove('active');
                });
                this.parentElement.querySelector('.theme-option').classList.add('active');
            });
        });
    </script>
</body>
</html>