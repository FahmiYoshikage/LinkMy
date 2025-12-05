<?php 
   require_once '../config/auth_check.php';
   require_once '../config/db.php';
   require_once '../config/session_handler.php';
   error_reporting(E_ALL);
   ini_set('display_errors', 1);

    // Multi-profile: Get active profile
    $active_profile_id = $_SESSION['active_profile_id'] ?? null;
    if (!$active_profile_id) {
        // Get user's primary profile
        $primary_profile = get_single_row(
            "SELECT id FROM profiles WHERE user_id = ? AND display_order = 0 ORDER BY id ASC LIMIT 1",
            [$current_user_id],
            'i'
        );
        if ($primary_profile) {
            $active_profile_id = $primary_profile['id'];
            $_SESSION['active_profile_id'] = $active_profile_id;
        } else {
            // Redirect to settings to create a profile if none exist
            header('Location: settings.php');
            exit;
        }
    }

    // Auto-create upload folders if not exist
    $base_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads';
    $required_folders = [
        $base_upload_dir,
        $base_upload_dir . '/profile_pics',
        $base_upload_dir . '/backgrounds',
        $base_upload_dir . '/folder_pics'
    ];
    
    // Try to create folders with aggressive permissions
    foreach ($required_folders as $folder) {
        if (!is_dir($folder)) {
            $old_umask = umask(0);
            @mkdir($folder, 0777, true);
            umask($old_umask);
            @chmod($folder, 0777);
        } else {
            // Folder exists, ensure writable
            @chmod($folder, 0777);
        }
    }
    
    // Verify uploads directory is writable
    if (!is_writable($base_upload_dir)) {
        // Log error for debugging
        error_log("WARNING: Uploads directory not writable: " . $base_upload_dir);
        error_log("Current user: " . get_current_user());
        error_log("Directory permissions: " . substr(sprintf('%o', fileperms($base_upload_dir)), -4));
    }

    $success = '';
    $error = '';

    // Multi-profile: Load theme and profile for active profile
    $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio 
                                    FROM themes t 
                                    LEFT JOIN profiles p ON t.profile_id = p.id 
                                    WHERE t.profile_id = ?", [$active_profile_id], 'i');
    
    if (!$appearance) {
        // Create default theme if not exists
        $query = "INSERT INTO themes (profile_id, bg_type, bg_value, button_color, text_color) 
                  VALUES (?, 'gradient', 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)', '#0ea5e9', '#333333')";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $active_profile_id);
        mysqli_stmt_execute($stmt);
        
        // Reload appearance
        $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio 
                                        FROM themes t 
                                        LEFT JOIN profiles p ON t.profile_id = p.id 
                                        WHERE t.profile_id = ?", [$active_profile_id], 'i');
    }

    // Load theme_boxed for preview and form defaults
    $theme_id_row = get_single_row("SELECT id FROM themes WHERE profile_id = ? LIMIT 1", [$active_profile_id], 'i');
    if ($theme_id_row) {
        $theme_id = $theme_id_row['id'];
        $boxed = get_single_row("SELECT enabled, outer_bg_type, outer_bg_value, container_max_width, container_radius, container_shadow FROM theme_boxed WHERE theme_id = ? LIMIT 1", [$theme_id], 'i');
        if ($boxed) {
            $appearance['boxed_layout'] = (int)$boxed['enabled'];
            $appearance['outer_bg_type'] = $boxed['outer_bg_type'];
            $appearance['outer_bg_value'] = $boxed['outer_bg_value'];
            $appearance['container_max_width'] = (int)$boxed['container_max_width'];
            $appearance['container_border_radius'] = (int)$boxed['container_radius'];
            $appearance['container_shadow'] = (int)$boxed['container_shadow'];
            
            // Parse outer_bg_value to extract gradient colors or solid color
            if ($boxed['outer_bg_type'] === 'gradient' && !empty($boxed['outer_bg_value'])) {
                // Extract hex colors from gradient string
                preg_match_all('/#[0-9a-fA-F]{6}/', $boxed['outer_bg_value'], $matches);
                if (isset($matches[0][0])) {
                    $appearance['outer_bg_gradient_start'] = $matches[0][0];
                }
                if (isset($matches[0][1])) {
                    $appearance['outer_bg_gradient_end'] = $matches[0][1];
                }
            } elseif ($boxed['outer_bg_type'] === 'color' && !empty($boxed['outer_bg_value'])) {
                // Extract solid color (note: database uses 'color' not 'solid')
                if (preg_match('/#[0-9a-fA-F]{6}/', $boxed['outer_bg_value'], $matches)) {
                    $appearance['outer_bg_color'] = $matches[0];
                } else {
                    $appearance['outer_bg_color'] = $boxed['outer_bg_value'];
                }
            }
            
            // Ensure defaults are set if parsing failed
            if (!isset($appearance['outer_bg_gradient_start'])) {
                $appearance['outer_bg_gradient_start'] = '#0ea5e9';
            }
            if (!isset($appearance['outer_bg_gradient_end'])) {
                $appearance['outer_bg_gradient_end'] = '#06b6d4';
            }
            if (!isset($appearance['outer_bg_color'])) {
                $appearance['outer_bg_color'] = '#0ea5e9';
            }
        } else {
            // Defaults if no boxed row
            $appearance['boxed_layout'] = 0;
            $appearance['outer_bg_type'] = 'gradient';
            $appearance['outer_bg_value'] = 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)';
            $appearance['outer_bg_gradient_start'] = '#0ea5e9';
            $appearance['outer_bg_gradient_end'] = '#06b6d4';
            $appearance['container_max_width'] = 480;
            $appearance['container_border_radius'] = 30;
            $appearance['container_shadow'] = 0;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
        $profile_title = trim($_POST['profile_title']);
        $bio = trim($_POST['bio']);
        
        // Multi-profile: Update profile info in profiles table
        $query = "UPDATE profiles SET title = ?, bio = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssi', $profile_title, $bio, $active_profile_id);
        
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
                // Use absolute path for Docker compatibility
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_pics/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                    chmod($upload_dir, 0777); // Ensure permissions
                }
                $extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $current_user_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $upload_path)) {
                    chmod($upload_path, 0644); // Set file permissions
                    // Delete old file
                    $old_file = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_pics/' . ($appearance['avatar'] ?? '');
                    if (!empty($appearance['avatar']) && $appearance['avatar'] !== 'default-avatar.png' && file_exists($old_file)) {
                        unlink($old_file);
                    }
                    // Multi-profile: Update profile pic for active profile
                    $query = "UPDATE profiles SET avatar = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'si', $new_filename, $active_profile_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Foto profil berhasil diupload!';
                        $appearance['avatar'] = $new_filename;
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
                // Use absolute path for Docker compatibility
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/backgrounds/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                    chmod($upload_dir, 0777); // Ensure permissions
                }
                $extension = pathinfo($_FILES['bg_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'bg_' . $current_user_id . '_' . time() . '.' . $extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['bg_image']['tmp_name'], $upload_path)) {
                    chmod($upload_path, 0644); // Set file permissions
                    // Delete old file if bg_type=image and bg_value is a filename
                    if (!empty($appearance['bg_type']) && $appearance['bg_type'] === 'image' && !empty($appearance['bg_value'])) {
                        $old_file = $_SERVER['DOCUMENT_ROOT'] . '/uploads/backgrounds/' . $appearance['bg_value'];
                        if (file_exists($old_file)) {
                            unlink($old_file);
                        }
                    }
                    // Multi-profile: Update background for active profile
                    $query = "UPDATE themes SET bg_type = 'image', bg_value = ? WHERE profile_id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, 'si', $new_filename, $active_profile_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Background berhasil diupload!';
                        $appearance['bg_type'] = 'image';
                        $appearance['bg_value'] = $new_filename;
                        $_SESSION['show_media_tab'] = true; // Stay on Media tab
                        
                        // Reload appearance data
                        $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio FROM themes t LEFT JOIN profiles p ON t.profile_id = p.id WHERE t.profile_id = ?", [$active_profile_id], 'i');
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
        if (!empty($appearance['bg_type']) && $appearance['bg_type'] === 'image' && !empty($appearance['bg_value'])) {
            $bg_path = '../uploads/backgrounds/' . $appearance['bg_value'];
            if (file_exists($bg_path)) {
                unlink($bg_path);
            }
            // Multi-profile: Remove background for active profile
            $query = "UPDATE themes SET bg_type = 'gradient', bg_value = 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)' WHERE profile_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $active_profile_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Background berhasil dihapus!';
                $appearance['bg_type'] = 'gradient';
                $appearance['bg_value'] = 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)';
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_theme'])) {
        $button_style = $_POST['button_style'];
        
        // ONLY update button_style, DO NOT touch bg_type or bg_value
        $query = "UPDATE themes SET button_style = ? WHERE profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $button_style, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Button style berhasil diupdate!';
            $appearance['button_style'] = $button_style;
            
            // Reload full appearance data
            $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio FROM themes t LEFT JOIN profiles p ON t.profile_id = p.id WHERE t.profile_id = ?", [$active_profile_id], 'i');
        } else {
            $error = 'Gagal mengupdate button style!';
        }
    }

    // Gradient presets CSS mapping (define early for use in POST handlers)
    $gradient_css_map = [
        'Sky Blue' => 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)',
        'Ocean Blue' => 'linear-gradient(135deg, #00c6ff 0%, #0072ff 100%)',
        'Sunset Orange' => 'linear-gradient(135deg, #ff6a00 0%, #ee0979 100%)',
        'Fresh Mint' => 'linear-gradient(135deg, #00b09b 0%, #96c93d 100%)',
        'Pink Lemonade' => 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
        'Royal Purple' => 'linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%)',
        'Fire Blaze' => 'linear-gradient(135deg, #f85032 0%, #e73827 100%)',
        'Emerald Water' => 'linear-gradient(135deg, #348f50 0%, #56b4d3 100%)',
        'Candy Shop' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        'Cool Blues' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        'Warm Flame' => 'linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%)',
        'Deep Sea' => 'linear-gradient(135deg, #2e3192 0%, #1bffff 100%)',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_colors'])) {
        $bg_type = $_POST['bg_type'];
        $bg_value = '';
        
        if ($bg_type === 'gradient') {
            $gradient_name = $_POST['gradient_preset'];
            $bg_value = $gradient_css_map[$gradient_name] ?? $gradient_css_map['Sky Blue'];
        } elseif ($bg_type === 'color') {
            $bg_value = $_POST['solid_color'];
        }
        
        $button_color = $_POST['button_color'];
        $text_color = $_POST['text_color'];
        
        // Multi-profile: Update theme for active profile
        $query = "UPDATE themes SET bg_type = ?, bg_value = ?, button_color = ?, text_color = ? WHERE profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $bg_type, $bg_value, $button_color, $text_color, $active_profile_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Tema berhasil diupdate!';
            $appearance['bg_type'] = $bg_type;
            $appearance['bg_value'] = $bg_value;
            $appearance['button_color'] = $button_color;
            $appearance['text_color'] = $text_color;
        } else {
            $error = 'Gagal mengupdate tema!';
        }
    }

    // Handle Boxed Layout Update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_boxed_layout'])) {
        $theme_id = $theme_id_row['id'] ?? null;
        if ($theme_id) {
            $enabled = isset($_POST['boxed_layout_enabled']) ? 1 : 0;
            $outer_bg_type = $_POST['outer_bg_type'];
            $outer_bg_value = '';
            if ($outer_bg_type === 'gradient') {
                $start = $_POST['outer_bg_gradient_start'];
                $end = $_POST['outer_bg_gradient_end'];
                $outer_bg_value = "linear-gradient(135deg, {$start} 0%, {$end} 100%)";
            } else { // color
                $outer_bg_value = $_POST['outer_bg_color'];
            }
            
            $max_width = (int)$_POST['container_max_width'];
            $radius = (int)$_POST['container_border_radius'];
            $shadow = isset($_POST['container_shadow_enabled']) ? 1 : 0;
            
            // Check if row exists
            $check_boxed = get_single_row("SELECT theme_id FROM theme_boxed WHERE theme_id = ?", [$theme_id], 'i');
            
            if ($check_boxed) {
                $query = "UPDATE theme_boxed SET enabled = ?, outer_bg_type = ?, outer_bg_value = ?, container_max_width = ?, container_radius = ?, container_shadow = ? WHERE theme_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'issiiii', $enabled, $outer_bg_type, $outer_bg_value, $max_width, $radius, $shadow, $theme_id);
            } else {
                $query = "INSERT INTO theme_boxed (theme_id, enabled, outer_bg_type, outer_bg_value, container_max_width, container_radius, container_shadow) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, 'iisssii', $theme_id, $enabled, $outer_bg_type, $outer_bg_value, $max_width, $radius, $shadow);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Pengaturan Boxed Layout berhasil disimpan!';
                // Manually reload all appearance data to reflect changes
                header("Location: appearance.php?tab=boxed"); // Force reload
                exit();
            } else {
                $error = 'Gagal menyimpan pengaturan Boxed Layout: ' . mysqli_error($conn);
            }
        } else {
            $error = 'Theme ID tidak ditemukan, tidak dapat menyimpan pengaturan Boxed Layout.';
        }
    }

    // Extract gradient colors for form defaults
    $gradient_start = '#0ea5e9';
    $gradient_end = '#06b6d4';
    if ($appearance['bg_type'] === 'gradient') {
        preg_match_all('/#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})/', $appearance['bg_value'], $matches);
        if (isset($matches[0][0])) $gradient_start = $matches[0][0];
        if (isset($matches[0][1])) $gradient_end = $matches[0][1];
    }

    $page_title = "Tampilan";
    include '../partials/admin_header.php';
?>
<body>
    <?php include '../partials/admin_nav.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1 class="fw-bold">Customize Your Appearance</h1>
                <p class="text-muted">Personalisasi tampilan halaman profil Anda dengan berbagai pilihan tema, warna, dan gaya</p>
            </div>
        </div>

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

        <div class="row">
            <!-- Settings Column -->
            <div class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <ul class="nav nav-pills mb-4" id="appearanceTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                    <i class="bi bi-person-badge me-2"></i>Profile
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="theme-colors-tab" data-bs-toggle="tab" data-bs-target="#theme-colors" type="button" role="tab">
                                    <i class="bi bi-palette-fill me-2"></i>Theme & Colors
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab">
                                    <i class="bi bi-image-fill me-2"></i>Media
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="boxed-layout-tab" data-bs-toggle="tab" data-bs-target="#boxed-layout" type="button" role="tab">
                                    <i class="bi bi-layout-split me-2"></i>Boxed Layout <span class="badge bg-info ms-1">New</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="appearanceTabContent">
                            <!-- Profile Information Tab -->
                            <div class="tab-pane fade show active" id="profile" role="tabpanel">
                                <h4 class="fw-bold mb-3">Profile Information</h4>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="profileTitle" class="form-label fw-semibold">Profile Title</label>
                                        <input type="text" class="form-control" id="profileTitle" name="profile_title" value="<?= htmlspecialchars($appearance['profile_title'] ?? '') ?>">
                                        <small class="text-muted">This will be displayed as your main heading.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="profileBio" class="form-label fw-semibold">Bio</label>
                                        <textarea class="form-control" id="profileBio" name="bio" rows="3"><?= htmlspecialchars($appearance['bio'] ?? '') ?></textarea>
                                        <small class="text-muted">A short description that appears below your title.</small>
                                    </div>
                                    <button type="submit" name="update_info" class="btn btn-primary">
                                        <i class="bi bi-save-fill me-2"></i>Save Profile Info
                                    </button>
                                </form>
                            </div>

                            <!-- Theme & Colors Tab -->
                            <div class="tab-pane fade" id="theme-colors" role="tabpanel">
                                <h4 class="fw-bold mb-3">Theme & Colors</h4>
                                <form method="POST" id="themeForm">
                                    <!-- Background Type -->
                                    <div class="mb-4">
                                        <h5 class="fw-semibold">Background</h5>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="bg_type" id="bgGradient" value="gradient" <?= ($appearance['bg_type'] ?? 'gradient') === 'gradient' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="bgGradient">Gradient</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="bg_type" id="bgSolid" value="color" <?= ($appearance['bg_type'] ?? '') === 'color' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="bgSolid">Solid Color</label>
                                        </div>
                                    </div>

                                    <!-- Gradient Options -->
                                    <div id="gradientOptions" class="mb-4" style="display: <?= ($appearance['bg_type'] ?? 'gradient') === 'gradient' ? 'block' : 'none' ?>;">
                                        <label for="gradientPreset" class="form-label fw-semibold">Gradient Preset</label>
                                        <select class="form-select" id="gradientPreset" name="gradient_preset">
                                            <?php foreach ($gradient_css_map as $name => $css): ?>
                                                <option value="<?= $name ?>" <?= ($appearance['bg_value'] ?? '') === $css ? 'selected' : '' ?>><?= $name ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- Solid Color Options -->
                                    <div id="solidColorOptions" class="mb-4" style="display: <?= ($appearance['bg_type'] ?? '') === 'color' ? 'block' : 'none' ?>;">
                                        <label for="solidColor" class="form-label fw-semibold">Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="solidColor" name="solid_color" value="<?= ($appearance['bg_type'] === 'color') ? htmlspecialchars($appearance['bg_value']) : '#ffffff' ?>">
                                    </div>

                                    <!-- Other Colors -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="buttonColor" class="form-label fw-semibold">Button Color</label>
                                            <input type="color" class="form-control form-control-color" id="buttonColor" name="button_color" value="<?= htmlspecialchars($appearance['button_color'] ?? '#0ea5e9') ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="textColor" class="form-label fw-semibold">Text Color</label>
                                            <input type="color" class="form-control form-control-color" id="textColor" name="text_color" value="<?= htmlspecialchars($appearance['text_color'] ?? '#333333') ?>">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_colors" class="btn btn-primary">
                                        <i class="bi bi-save-fill me-2"></i>Save Theme
                                    </button>
                                </form>
                            </div>

                            <!-- Media Tab -->
                            <div class="tab-pane fade" id="media" role="tabpanel">
                                <h4 class="fw-bold mb-3">Media</h4>
                                <!-- Profile Picture -->
                                <div class="mb-4">
                                    <h5 class="fw-semibold">Profile Picture</h5>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="profilePic" class="form-label">Upload new picture</label>
                                            <input class="form-control" type="file" id="profilePic" name="profile_pic" accept="image/png, image/jpeg, image/gif">
                                        </div>
                                        <button type="submit" name="upload_profile" class="btn btn-secondary">
                                            <i class="bi bi-upload me-2"></i>Upload Picture
                                        </button>
                                    </form>
                                </div>
                                <hr>
                                <!-- Background Image -->
                                <div>
                                    <h5 class="fw-semibold">Background Image</h5>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="bgImage" class="form-label">Upload new background</label>
                                            <input class="form-control" type="file" id="bgImage" name="bg_image" accept="image/png, image/jpeg, image/gif">
                                        </div>
                                        <button type="submit" name="upload_background" class="btn btn-secondary">
                                            <i class="bi bi-upload me-2"></i>Upload Background
                                        </button>
                                        <?php if (($appearance['bg_type'] ?? '') === 'image'): ?>
                                            <a href="?remove_bg=1" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to remove the background image?')">
                                                <i class="bi bi-trash-fill me-2"></i>Remove Background
                                            </a>
                                        <?php endif; ?>
                                    </form>
                                </div>
                            </div>

                            <!-- Boxed Layout Tab -->
                            <div class="tab-pane fade" id="boxed-layout" role="tabpanel">
                                <h4 class="fw-bold mb-3">Boxed Layout Settings</h4>
                                <form method="POST">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="boxedLayoutEnabled" name="boxed_layout_enabled" <?= ($appearance['boxed_layout'] ?? 0) == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="boxedLayoutEnabled">Enable Boxed Layout</label>
                                    </div>

                                    <div id="boxedOptions" style="display: <?= ($appearance['boxed_layout'] ?? 0) == 1 ? 'block' : 'none' ?>;">
                                        <h5 class="fw-semibold mt-4">Outer Background</h5>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="outer_bg_type" id="outerBgGradient" value="gradient" <?= ($appearance['outer_bg_type'] ?? 'gradient') === 'gradient' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="outerBgGradient">Gradient</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="outer_bg_type" id="outerBgSolid" value="color" <?= ($appearance['outer_bg_type'] ?? '') === 'color' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="outerBgSolid">Solid Color</label>
                                        </div>

                                        <div id="outerGradientOptions" class="row mt-3" style="display: <?= ($appearance['outer_bg_type'] ?? 'gradient') === 'gradient' ? 'block' : 'none' ?>;">
                                            <div class="col-md-6 mb-3">
                                                <label for="outerBgGradientStart" class="form-label">Gradient Start</label>
                                                <input type="color" class="form-control form-control-color" id="outerBgGradientStart" name="outer_bg_gradient_start" value="<?= htmlspecialchars($appearance['outer_bg_gradient_start'] ?? '#0ea5e9') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="outerBgGradientEnd" class="form-label">Gradient End</label>
                                                <input type="color" class="form-control form-control-color" id="outerBgGradientEnd" name="outer_bg_gradient_end" value="<?= htmlspecialchars($appearance['outer_bg_gradient_end'] ?? '#06b6d4') ?>">
                                            </div>
                                        </div>

                                        <div id="outerSolidOptions" class="mt-3" style="display: <?= ($appearance['outer_bg_type'] ?? '') === 'color' ? 'block' : 'none' ?>;">
                                            <label for="outerBgColor" class="form-label">Solid Color</label>
                                            <input type="color" class="form-control form-control-color" id="outerBgColor" name="outer_bg_color" value="<?= htmlspecialchars($appearance['outer_bg_color'] ?? '#f8f9fa') ?>">
                                        </div>

                                        <h5 class="fw-semibold mt-4">Container Style</h5>
                                        <div class="mb-3">
                                            <label for="containerMaxWidth" class="form-label">Max Width: <span id="maxWidthValue"><?= $appearance['container_max_width'] ?? 480 ?></span>px</label>
                                            <input type="range" class="form-range" id="containerMaxWidth" name="container_max_width" min="320" max="800" step="10" value="<?= $appearance['container_max_width'] ?? 480 ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="containerBorderRadius" class="form-label">Border Radius: <span id="radiusValue"><?= $appearance['container_border_radius'] ?? 30 ?></span>px</label>
                                            <input type="range" class="form-range" id="containerBorderRadius" name="container_border_radius" min="0" max="50" value="<?= $appearance['container_border_radius'] ?? 30 ?>">
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="containerShadowEnabled" name="container_shadow_enabled" <?= ($appearance['container_shadow'] ?? 0) == 1 ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="containerShadowEnabled">Enable Container Shadow</label>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_boxed_layout" class="btn btn-primary mt-3">
                                        <i class="bi bi-save-fill me-2"></i>Save Boxed Layout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Column -->
            <div class="col-lg-5">
                <div class="sticky-top" style="top: 20px;">
                    <h4 class="fw-bold">Live Preview</h4>
                    <p class="text-muted">See changes in real-time.</p>
                    <div class="mobile-preview-container">
                        <div class="mobile-frame">
                            <div class="preview-content" id="livePreview">
                                <!-- Profile Info -->
                                <div class="profile-info text-center">
                                    <img id="previewAvatar" src="../uploads/profile_pics/<?= htmlspecialchars($appearance['avatar'] ?? 'default-avatar.png') ?>" alt="Avatar" class="avatar">
                                    <h5 id="previewTitle" class="mt-3"><?= htmlspecialchars($appearance['profile_title'] ?? 'Your Name') ?></h5>
                                    <p id="previewBio"><?= htmlspecialchars($appearance['bio'] ?? 'Your bio goes here.') ?></p>
                                </div>
                                <!-- Sample Links -->
                                <div class="sample-links">
                                    <div class="sample-link">Sample Link 1</div>
                                    <div class="sample-link">Sample Link 2</div>
                                    <div class="sample-link">Sample Link 3</div>
                                </div>
                            </div>
                        </div>
                        <a href="../<?= $current_page_slug ?>" target="_blank" class="btn btn-outline-secondary w-100 mt-3">
                            <i class="bi bi-arrows-fullscreen me-2"></i>View Full Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../partials/admin_footer.php'; ?>

    <script>
        // Cropper.js variables
        let cropper = null;
        let cropModal = null;
        
        function openCropModal(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 2MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const image = document.getElementById('cropImage');
                    image.src = e.target.result;
                    
                    // Open modal
                    cropModal = new bootstrap.Modal(document.getElementById('cropModal'));
                    cropModal.show();
                    
                    // Initialize cropper after modal is shown
                    document.getElementById('cropModal').addEventListener('shown.bs.modal', function() {
                        if (cropper) {
                            cropper.destroy();
                        }
                        
                        cropper = new Cropper(image, {
                            aspectRatio: 1,
                            viewMode: 1,
                            dragMode: 'move',
                            autoCropArea: 1,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: false,
                            cropBoxResizable: false,
                            toggleDragModeOnDblclick: false,
                            ready: function() {
                                updateCropPreview();
                            },
                            crop: function() {
                                updateCropPreview();
                            }
                        });
                    }, { once: true });
                };
                reader.readAsDataURL(file);
            }
        }
        
        function updateCropPreview() {
            if (!cropper) return;
            
            const canvas = cropper.getCroppedCanvas({
                width: 300,
                height: 300,
                imageSmoothingQuality: 'high'
            });
            
            const preview = document.getElementById('cropPreview');
            preview.innerHTML = '';
            
            if (canvas) {
                const img = document.createElement('img');
                img.src = canvas.toDataURL();
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            }
        }
        
        function applyCrop() {
            if (!cropper) return;
            
            // Get cropped canvas
            const canvas = cropper.getCroppedCanvas({
                width: 500,
                height: 500,
                imageSmoothingQuality: 'high'
            });
            
            if (canvas) {
                // Convert to blob and create file
                canvas.toBlob(function(blob) {
                    // Create a new File object
                    const file = new File([blob], 'profile_pic.jpg', { type: 'image/jpeg' });
                    
                    // Create a new DataTransfer to update the file input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    document.getElementById('profilePicInput').files = dataTransfer.files;
                    
                    // Update preview
                    const previewImg = document.getElementById('profilePicPreview');
                    if (previewImg) {
                        previewImg.src = canvas.toDataURL();
                        previewImg.style.display = 'block';
                    } else {
                        const uploadArea = document.querySelector('.upload-area');
                        uploadArea.innerHTML = '<img src="' + canvas.toDataURL() + '" id="profilePicPreview" class="image-preview">';
                        uploadArea.classList.add('has-image');
                    }
                    
                    // Enable upload button
                    document.getElementById('uploadProfileBtn').disabled = false;
                    
                    // Close modal
                    cropModal.hide();
                    resetCropper();
                }, 'image/jpeg', 0.9);
            }
        }
        
        function resetCropper() {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
        }
        
        // Background Image Cropper
        let bgCropper = null;
        let bgCropModal = null;
        
        function handleBackgroundUpload(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const image = document.getElementById('bgCropImage');
                    image.src = e.target.result;
                    
                    // Open modal
                    bgCropModal = new bootstrap.Modal(document.getElementById('bgCropModal'));
                    bgCropModal.show();
                    
                    // Initialize cropper after modal is shown
                    document.getElementById('bgCropModal').addEventListener('shown.bs.modal', function() {
                        if (bgCropper) {
                            bgCropper.destroy();
                        }
                        
                        bgCropper = new Cropper(image, {
                            viewMode: 2,
                            dragMode: 'move',
                            autoCropArea: 1,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false
                        });
                    }, { once: true });
                };
                reader.readAsDataURL(file);
            }
        }
        
        function applyBgCrop() {
            if (!bgCropper) return;
            
            // Get cropped canvas
            const canvas = bgCropper.getCroppedCanvas({
                maxWidth: 1920,
                maxHeight: 1920,
                imageSmoothingQuality: 'high'
            });
            
            if (!canvas) return;
            
            // Close modal first
            if (bgCropModal) {
                bgCropModal.hide();
            }
            
            // Show loading
            showLoading();
            
            // Convert to blob and upload
            canvas.toBlob(function(blob) {
                const formData = new FormData();
                formData.append('bg_image', blob, 'background.jpg');
                formData.append('upload_background', '1');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Upload failed');
                    }
                    return response.text();
                })
                .then(() => {
                    // Reload to Media tab to show uploaded image
                    window.location.href = window.location.protocol + '//' + window.location.host + window.location.pathname + '?tab=media&uploaded=1';
                })
                .catch(error => {
                    alert('Error uploading background: ' + error.message);
                    document.getElementById('loadingOverlay')?.classList.remove('show');
                    resetBgCropper();
                });
            }, 'image/jpeg', 0.92);
        }
        
        function resetBgCropper() {
            if (bgCropper) {
                bgCropper.destroy();
                bgCropper = null;
            }
            document.getElementById('bgImageInput').value = '';
        }
        
        // Show loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }
        
        // Theme selection
        function selectTheme(theme) {
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="theme_name"][value="${theme}"]`).checked = true;
            
            // Update preview
            const previewContent = document.getElementById('previewContent');
            const previewTitle = document.getElementById('previewTitle');
            const previewBio = document.getElementById('previewBio');
            const previewLinks = [
                document.getElementById('previewLink1'),
                document.getElementById('previewLink2'),
                document.getElementById('previewLink3')
            ];
            
            if (theme === 'light') {
                previewContent.style.background = '#ffffff';
                previewTitle.style.color = '#333';
                previewBio.style.color = '#666';
                previewLinks.forEach(link => {
                    link.style.background = '#f8f9fa';
                    link.style.borderColor = '#dee2e6';
                    link.style.color = '#333';
                });
            } else if (theme === 'dark') {
                previewContent.style.background = '#1a1a1a';
                previewTitle.style.color = '#fff';
                previewBio.style.color = 'rgba(255,255,255,0.8)';
                previewLinks.forEach(link => {
                    link.style.background = '#2d2d2d';
                    link.style.borderColor = '#444';
                    link.style.color = '#fff';
                });
            } else {
                previewContent.style.background = 'linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%)';
                previewTitle.style.color = '#fff';
                previewBio.style.color = 'rgba(255,255,255,0.9)';
                previewLinks.forEach(link => {
                    link.style.background = 'rgba(255,255,255,0.2)';
                    link.style.borderColor = 'rgba(255,255,255,0.3)';
                    link.style.color = '#fff';
                });
            }
        }
        
        // Button style selection
        function selectButtonStyle(style) {
            document.querySelectorAll('[onclick^="selectButtonStyle"]').forEach(card => {
                card.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="button_style"][value="${style}"]`).checked = true;
            
            // Update preview
            let borderRadius = '12px';
            if (style === 'sharp') borderRadius = '0';
            if (style === 'pill') borderRadius = '50px';
            
            document.querySelectorAll('.preview-link').forEach(link => {
                link.style.borderRadius = borderRadius;
            });
        }
        
        // Live update preview on input
        document.getElementById('profileTitle')?.addEventListener('input', function() {
            document.getElementById('previewTitle').textContent = this.value || 'Your Name';
        });
        
        document.getElementById('profileBio')?.addEventListener('input', function() {
            document.getElementById('previewBio').textContent = this.value || 'Your bio will appear here...';
        });
        
        // Image preview on file select
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.getElementById(previewId);
                    
                    // Create img element if not exists (for background)
                    if (!img && previewId === 'bgImagePreview') {
                        const uploadArea = input.closest('.upload-area');
                        if (uploadArea) {
                            img = document.createElement('img');
                            img.id = previewId;
                            img.className = 'image-preview';
                            uploadArea.insertBefore(img, uploadArea.firstChild);
                        }
                    }
                    
                    if (img) {
                        img.src = e.target.result;
                        img.style.display = 'block';
                    }
                    
                    // Update live preview if profile pic
                    if (previewId === 'profilePicPreview') {
                        const previewAvatar = document.getElementById('previewAvatar');
                        if (previewAvatar) {
                            previewAvatar.src = e.target.result;
                        }
                    }
                    
                    // Update live preview if background image
                    if (previewId === 'bgImagePreview') {
                        const previewContent = document.getElementById('previewContent');
                        if (previewContent) {
                            previewContent.style.backgroundImage = `url(${e.target.result})`;
                            previewContent.style.backgroundSize = 'cover';
                            previewContent.style.backgroundPosition = 'center';
                        }
                    }
                    
                    // Show image in upload area
                    const uploadArea = input.closest('.upload-area');
                    if (uploadArea) {
                        uploadArea.classList.add('has-image');
                        const icon = uploadArea.querySelector('i.bi-person-circle, i.bi-image');
                        const text = uploadArea.querySelector('p');
                        const small = uploadArea.querySelector('small');
                        
                        if (icon) icon.style.display = 'none';
                        if (text) text.style.display = 'none';
                        if (small) small.style.display = 'none';
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Gradient preset selection
        function selectGradient(name, css) {
            document.querySelectorAll('.gradient-preset-card').forEach(card => {
                card.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="gradient_preset"][value="${name}"]`).checked = true;
            
            // Update preview background
            document.getElementById('previewContent').style.background = css;
        }
        
        function selectGradientFromRadio(radio, css) {
            // Set bg_choice to 'preset' when gradient preset selected
            document.getElementById('bgChoice').value = 'preset';
            
            // Remove active from all cards
            document.querySelectorAll('.gradient-preset-card').forEach(card => {
                card.classList.remove('active');
            });
            // Add active to clicked card
            radio.closest('label').querySelector('.gradient-preset-card').classList.add('active');
            // Update preview
            document.getElementById('previewContent').style.background = css;
        }
        
        // Layout selection
        function selectLayout(layout) {
            document.querySelectorAll('.layout-card').forEach(card => {
                card.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            document.querySelector(`input[name="profile_layout"][value="${layout}"]`).checked = true;
            
            // Update preview layout
            const previewContent = document.getElementById('previewContent');
        }
        
        function selectLayoutFromRadio(radio) {
            // Remove active from all cards
            document.querySelectorAll('.layout-card').forEach(card => {
                card.classList.remove('active');
            });
            // Add active to clicked card
            radio.closest('label').querySelector('.layout-card').classList.add('active');
            
            // Update preview layout
            const layout = radio.value;
            const previewContent = document.getElementById('previewContent');
            previewContent.className = 'preview-phone-content';
        }
        
        // Container style selection (NEW)
        function selectContainerStyle(radio) {
            // Remove active from all cards
            const cards = document.querySelectorAll('input[name="container_style"] ~ .layout-card');
            cards.forEach(card => {
                card.classList.remove('active');
            });
            // Add active to clicked card
            radio.closest('label').querySelector('.layout-card').classList.add('active');
            
            if (layout === 'left') {
                previewContent.style.textAlign = 'left';
                previewContent.style.paddingLeft = '30px';
            } else if (layout === 'minimal') {
                previewContent.style.textAlign = 'center';
                previewContent.style.padding = '20px 15px';
            } else {
                // centered
                previewContent.style.textAlign = 'center';
                previewContent.style.padding = '30px 20px';
            }
        }
        
        // Color picker hex display
        document.getElementById('customBgColor')?.addEventListener('input', function() {
            document.getElementById('customBgColorHex').value = this.value;
            // Set bg_choice to 'solid' when solid color changed
            document.getElementById('bgChoice').value = 'solid';
            // Update preview
            document.getElementById('previewContent').style.background = this.value;
        });
        
        document.getElementById('customButtonColor')?.addEventListener('input', function() {
            document.getElementById('customButtonColorHex').value = this.value;
            // Update preview buttons
            document.querySelectorAll('.preview-link').forEach(link => {
                link.style.background = this.value;
            });
        });
        
        document.getElementById('customTextColor')?.addEventListener('input', function() {
            document.getElementById('customTextColorHex').value = this.value;
            // Update preview text
            document.getElementById('previewTitle').style.color = this.value;
            document.getElementById('previewBio').style.color = this.value;
        });
        
        // Custom gradient color pickers
        document.getElementById('customGradientStart')?.addEventListener('input', function() {
            document.getElementById('customGradientStartHex').value = this.value;
            // Set bg_choice to 'custom' when custom gradient changed
            document.getElementById('bgChoice').value = 'custom';
            updateCustomGradientPreview();
        });
        
        document.getElementById('customGradientEnd')?.addEventListener('input', function() {
            document.getElementById('customGradientEndHex').value = this.value;
            // Set bg_choice to 'custom' when custom gradient changed
            document.getElementById('bgChoice').value = 'custom';
            updateCustomGradientPreview();
        });
        
        function updateCustomGradientPreview() {
            const start = document.getElementById('customGradientStart')?.value || '#0ea5e9';
            const end = document.getElementById('customGradientEnd')?.value || '#06b6d4';
            const gradient = `linear-gradient(135deg, ${start} 0%, ${end} 100%)`;
            document.getElementById('customGradientPreview').style.background = gradient;
            document.getElementById('previewContent').style.background = gradient;
        }
        
        document.getElementById('customLinkTextColor')?.addEventListener('input', function() {
            document.getElementById('customLinkTextColorHex').value = this.value;
            // Update preview link text
            document.querySelectorAll('.preview-link').forEach(link => {
                link.style.color = this.value;
            });
        });
        
        // Shadow Intensity selector
        document.getElementById('shadowIntensity')?.addEventListener('change', function() {
            const shadows = {
                'none': 'none',
                'light': '0 2px 8px rgba(0,0,0,0.08)',
                'medium': '0 2px 10px rgba(0,0,0,0.15)',
                'heavy': '0 4px 15px rgba(0,0,0,0.3)'
            };
            document.querySelectorAll('.preview-link').forEach(link => {
                link.style.boxShadow = shadows[this.value] || shadows.medium;
            });
        });
        
        // Glass Effect toggle
        document.getElementById('enableGlassEffect')?.addEventListener('change', function() {
            document.querySelectorAll('.preview-link').forEach(link => {
                if (this.checked) {
                    link.style.backdropFilter = 'blur(20px) saturate(180%)';
                    link.style.webkitBackdropFilter = 'blur(20px) saturate(180%)';
                    link.style.background = 'rgba(255,255,255,0.15)';
                    link.style.border = '1px solid rgba(255,255,255,0.3)';
                } else {
                    link.style.backdropFilter = 'none';
                    link.style.webkitBackdropFilter = 'none';
                    // Reset to original background based on current button color
                    const btnColor = document.getElementById('customButtonColor')?.value;
                    if (btnColor) {
                        link.style.background = btnColor;
                    }
                }
            });
        });
        
        // Toggle switches for Additional Options
        document.getElementById('showProfileBorder')?.addEventListener('change', function() {
            const avatar = document.getElementById('previewAvatar');
            if (this.checked) {
                avatar.style.border = '4px solid white';
                avatar.style.boxShadow = '0 0 0 2px rgba(0,0,0,0.1)';
            } else {
                avatar.style.border = 'none';
                avatar.style.boxShadow = 'none';
            }
        });
        
        document.getElementById('enableAnimations')?.addEventListener('change', function() {
            const previewLinks = document.querySelectorAll('.preview-link');
            if (this.checked) {
                previewLinks.forEach(link => {
                    link.style.transition = 'all 0.3s ease';
                });
            } else {
                previewLinks.forEach(link => {
                    link.style.transition = 'none';
                });
            }
        });
        
        // Copy icon class to clipboard
        // Button style selection function
        function selectButtonStyle(style) {
            // Remove active class from all cards
            document.querySelectorAll('.theme-card').forEach(card => {
                card.classList.remove('active');
            });
            
            // Add active class to selected card
            const selectedLabel = event.currentTarget;
            const selectedCard = selectedLabel.querySelector('.theme-card');
            selectedCard.classList.add('active');
            
            // Check the hidden radio button
            const radio = selectedLabel.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Update preview buttons
            const previewLinks = document.querySelectorAll('.preview-link');
            previewLinks.forEach(link => {
                if (style === 'pill') {
                    link.style.borderRadius = '50px';
                } else if (style === 'sharp') {
                    link.style.borderRadius = '0';
                } else {
                    link.style.borderRadius = '12px';
                }
            });
        }
        
        // Custom gradient builder preview
        const gradientStart = document.getElementById('customGradientStart');
        const gradientEnd = document.getElementById('customGradientEnd');
        const gradientPreview = document.getElementById('customGradientPreview');
        const gradientStartHex = document.getElementById('customGradientStartHex');
        const gradientEndHex = document.getElementById('customGradientEndHex');
        
        function updateGradientPreview() {
            const startColor = gradientStart.value;
            const endColor = gradientEnd.value;
            gradientPreview.style.background = `linear-gradient(135deg, ${startColor} 0%, ${endColor} 100%)`;
            gradientStartHex.value = startColor;
            gradientEndHex.value = endColor;
        }
        
        gradientStart.addEventListener('input', updateGradientPreview);
        gradientEnd.addEventListener('input', updateGradientPreview);
        
        // Profile layout live preview
        function updatePreviewLayout() {
            const layout = document.getElementById('profileLayout').value;
            const previewContent = document.getElementById('previewContent');
            
            if (layout === 'left') {
                previewContent.style.textAlign = 'left';
                previewContent.style.paddingLeft = '30px';
                previewContent.style.paddingRight = '20px';
            } else if (layout === 'minimal') {
                previewContent.style.textAlign = 'center';
                previewContent.style.padding = '20px 15px';
            } else {
                previewContent.style.textAlign = 'center';
                previewContent.style.padding = '30px 20px';
            }
        }
        
        document.querySelectorAll('.social-icon-item').forEach(item => {
            item.addEventListener('click', function() {
                const iconClass = this.querySelector('i').className;
                navigator.clipboard.writeText(iconClass).then(() => {
                    // Show toast notification
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed bottom-0 end-0 p-3';
                    toast.style.zIndex = '11';
                    toast.innerHTML = `





