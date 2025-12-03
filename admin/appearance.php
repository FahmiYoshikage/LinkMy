<?php 
   require_once '../config/auth_check.php';
   require_once '../config/db.php';
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
                  VALUES (?, 'gradient', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea', '#333333')";
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
            } elseif ($boxed['outer_bg_type'] === 'solid' && !empty($boxed['outer_bg_value'])) {
                // Extract solid color
                if (preg_match('/#[0-9a-fA-F]{6}/', $boxed['outer_bg_value'], $matches)) {
                    $appearance['outer_bg_color'] = $matches[0];
                } else {
                    $appearance['outer_bg_color'] = $boxed['outer_bg_value'];
                }
            }
        } else {
            // Defaults if no boxed row
            $appearance['boxed_layout'] = 0;
            $appearance['outer_bg_type'] = 'gradient';
            $appearance['outer_bg_value'] = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            $appearance['outer_bg_gradient_start'] = '#667eea';
            $appearance['outer_bg_gradient_end'] = '#764ba2';
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
        if (!empty($appearance['bg_image_filename'])) {
            $bg_path = '../uploads/backgrounds/' . $appearance['bg_image_filename'];
            if (file_exists($bg_path)) {
                unlink($bg_path);
            }
            // Multi-profile: Remove background for active profile
            $query = "UPDATE themes SET bg_type = 'gradient', bg_value = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' WHERE profile_id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $active_profile_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Background berhasil dihapus!';
                $appearance['bg_image_filename'] = null;
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
        'Purple Dream' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
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
        'Nebula Night' => 'linear-gradient(135deg, #3a1c71 0%, #d76d77 50%, #ffaf7b 100%)',
        'Aurora Borealis' => 'linear-gradient(135deg, #00c9ff 0%, #92fe9d 100%)',
        'Crimson Tide' => 'linear-gradient(135deg, #c31432 0%, #240b36 100%)',
        'Golden Hour' => 'linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 50%, #e17055 100%)',
        'Midnight Blue' => 'linear-gradient(135deg, #000428 0%, #004e92 100%)',
        'Rose Petal' => 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
        'Electric Violet' => 'linear-gradient(135deg, #4776e6 0%, #8e54e9 100%)',
        'Jungle Green' => 'linear-gradient(135deg, #134e5e 0%, #71b280 100%)',
        'Peach Cream' => 'linear-gradient(135deg, #ff9a8b 0%, #ff6a88 50%, #ff99ac 100%)',
        'Arctic Ice' => 'linear-gradient(135deg, #667db6 0%, #0082c8 50%, #0082c8 100%, #667db6 100%)',
        'Sunset Glow' => 'linear-gradient(135deg, #ffa751 0%, #ffe259 100%)',
        'Purple Haze' => 'linear-gradient(135deg, #c471f5 0%, #fa71cd 100%)'
    ];

    // DEBUG: Log all POST data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("POST received: " . print_r($_POST, true));
    }

    // Handle advanced customization updates
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_advanced'])) {
        error_log("Advanced update triggered!");
        error_log("RAW POST gradient_preset: " . ($_POST['gradient_preset'] ?? 'NOT SET'));
        
        $gradient_preset = !empty($_POST['gradient_preset']) ? $_POST['gradient_preset'] : null;
        $custom_bg_color = !empty($_POST['custom_bg_color']) ? $_POST['custom_bg_color'] : null;
        $custom_button_color = !empty($_POST['custom_button_color']) ? $_POST['custom_button_color'] : null;
        $custom_text_color = !empty($_POST['custom_text_color']) ? $_POST['custom_text_color'] : null;
        $custom_link_text_color = !empty($_POST['custom_link_text_color']) ? $_POST['custom_link_text_color'] : null;
        $button_style = !empty($_POST['button_style']) ? $_POST['button_style'] : 'rounded';
        $profile_layout = !empty($_POST['profile_layout']) ? $_POST['profile_layout'] : 'centered';
        $container_style = !empty($_POST['container_style']) ? $_POST['container_style'] : 'wide';
        $enable_categories = isset($_POST['enable_categories']) ? 1 : 0;
        $show_profile_border = isset($_POST['show_profile_border']) ? 1 : 0;
        $enable_animations = isset($_POST['enable_animations']) ? 1 : 0;
        $enable_glass_effect = isset($_POST['enable_glass_effect']) ? 1 : 0;
        $shadow_intensity = !empty($_POST['shadow_intensity']) ? $_POST['shadow_intensity'] : 'medium';
        
        error_log("PARSED gradient=$gradient_preset, layout=$profile_layout, container=$container_style, categories=$enable_categories, border=$show_profile_border, anim=$enable_animations, glass=$enable_glass_effect, shadow=$shadow_intensity");
        error_log("COLORS: bg=$custom_bg_color, btn=$custom_button_color, txt=$custom_text_color, link_txt=$custom_link_text_color");
        
        // Determine bg_value: prefer gradient preset CSS, fallback to custom color
        // BUT: Only update if user changed gradient/color fields
        // Check if current bg_type is 'image' - if so, don't overwrite unless new gradient selected
        $current_bg_type = $appearance['bg_type'] ?? 'gradient';
        $current_bg_value = $appearance['bg_value'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        
        // Check for custom gradient first
        $custom_gradient_start = !empty($_POST['custom_gradient_start']) ? $_POST['custom_gradient_start'] : null;
        $custom_gradient_end = !empty($_POST['custom_gradient_end']) ? $_POST['custom_gradient_end'] : null;
        
        // Determine if user made color/gradient changes
        $bg_changed = false;
        
        if ($custom_gradient_start && $custom_gradient_end) {
            // User created custom gradient
            $bg_value = "linear-gradient(135deg, {$custom_gradient_start} 0%, {$custom_gradient_end} 100%)";
            $bg_type = 'gradient';
            $bg_changed = true;
            // Store gradient colors for editing
            $appearance['custom_gradient_start'] = $custom_gradient_start;
            $appearance['custom_gradient_end'] = $custom_gradient_end;
        } elseif ($gradient_preset && !empty($gradient_preset)) {
            // User selected a gradient preset - always apply it
            $bg_value = $gradient_css_map[$gradient_preset] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            $bg_type = 'gradient';
            $bg_changed = ($bg_value !== $current_bg_value); // Check if different from current
        } elseif ($custom_bg_color && !empty($custom_bg_color)) {
            // User entered custom solid color - always apply if provided
            $bg_value = $custom_bg_color;
            $bg_type = 'color';
            $bg_changed = ($bg_value !== $current_bg_value || $bg_type !== $current_bg_type);
        } else {
            // No new gradient/color selected - keep existing
            $bg_value = $current_bg_value;
            $bg_type = $current_bg_type;
        }
        
        $query = "UPDATE themes SET bg_type = ?, bg_value = ?, button_style = ?, button_color = ?, text_color = ?, layout = ?, container_style = ?, enable_animations = ?, enable_glass_effect = ?, shadow_intensity = ? WHERE profile_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if (!$stmt) {
            $error = 'Database prepare error: ' . mysqli_error($conn);
            error_log("PREPARE ERROR: " . mysqli_error($conn));
        } else {
            // Multi-profile: Update advanced settings for active profile
            $bind_result = mysqli_stmt_bind_param($stmt, 'sssssssiisi', 
                $bg_type, $bg_value, $button_style, $custom_button_color, $custom_text_color,
                $profile_layout, $container_style, $enable_animations, $enable_glass_effect, $shadow_intensity, $active_profile_id);
            
            if (!$bind_result) {
                $error = 'Bind param error: ' . mysqli_stmt_error($stmt);
                error_log("BIND ERROR: " . mysqli_stmt_error($stmt));
            } elseif (mysqli_stmt_execute($stmt)) {
                $affected = mysqli_stmt_affected_rows($stmt);
                if ($affected > 0) {
                    $success = '✅ Kustomisasi lanjutan berhasil disimpan dan diupdate! (' . $affected . ' baris diubah)';
                } else {
                    $success = '✅ Kustomisasi tersimpan! (Data sama dengan sebelumnya, tidak ada perubahan)';
                }
                error_log("SUCCESS! Affected rows: $affected");
                
                // Reload data from database to ensure we have latest - Multi-profile
                $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio FROM themes t LEFT JOIN profiles p ON t.profile_id = p.id WHERE t.profile_id = ?", [$active_profile_id], 'i');
                
                error_log("After save - gradient: " . ($appearance['gradient_preset'] ?? 'NULL'));
                
                // Set flag to switch to Advanced tab after reload
                $_SESSION['show_advanced_tab'] = true;
            } else {
                $error = 'Gagal menyimpan: ' . mysqli_stmt_error($stmt);
                error_log("EXECUTE ERROR: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Handle Boxed Layout Update (migrated to theme_boxed)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_boxed_layout'])) {
        $boxed_enabled = isset($_POST['boxed_layout']) ? 1 : 0;
        $outer_bg_type = $_POST['outer_bg_type'] ?? 'gradient';
        $outer_bg_color = $_POST['outer_bg_color'] ?? '#667eea';
        $outer_bg_gradient_start = $_POST['outer_bg_gradient_start'] ?? '#667eea';
        $outer_bg_gradient_end = $_POST['outer_bg_gradient_end'] ?? '#764ba2';
        $container_max_width = intval($_POST['container_max_width'] ?? 480);
        $container_border_radius = intval($_POST['container_border_radius'] ?? 30);
        $container_shadow = isset($_POST['container_shadow']) ? 1 : 0;

        // Resolve theme_id for active profile
        $theme_row = get_single_row("SELECT id FROM themes WHERE profile_id = ? LIMIT 1", [$active_profile_id], 'i');
        if (!$theme_row) {
            // Create default theme if missing
            $create = mysqli_prepare($conn, "INSERT INTO themes (profile_id, bg_type, bg_value, button_color, text_color) VALUES (?, 'gradient', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', '#667eea', '#333333')");
            mysqli_stmt_bind_param($create, 'i', $active_profile_id);
            mysqli_stmt_execute($create);
            mysqli_stmt_close($create);
            $theme_row = get_single_row("SELECT id FROM themes WHERE profile_id = ? LIMIT 1", [$active_profile_id], 'i');
        }
        $theme_id = $theme_row['id'];

        // Ensure theme_boxed row exists
        $boxed_row = get_single_row("SELECT id FROM theme_boxed WHERE theme_id = ? LIMIT 1", [$theme_id], 'i');
        if (!$boxed_row) {
            $ins = mysqli_prepare($conn, "INSERT INTO theme_boxed (theme_id, enabled, outer_bg_type, outer_bg_value, container_max_width, container_radius, container_shadow) VALUES (?, 0, 'gradient', 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)', 480, 30, 0)");
            mysqli_stmt_bind_param($ins, 'i', $theme_id);
            mysqli_stmt_execute($ins);
            mysqli_stmt_close($ins);
            $boxed_row = get_single_row("SELECT id FROM theme_boxed WHERE theme_id = ? LIMIT 1", [$theme_id], 'i');
        }

        // Compute outer_bg_value based on type
        $outer_bg_value = $outer_bg_type === 'gradient'
            ? "linear-gradient(135deg, {$outer_bg_gradient_start} 0%, {$outer_bg_gradient_end} 100%)"
            : $outer_bg_color;

        // Update boxed settings
        $upd = mysqli_prepare($conn, "UPDATE theme_boxed SET enabled = ?, outer_bg_type = ?, outer_bg_value = ?, container_max_width = ?, container_radius = ?, container_shadow = ? WHERE theme_id = ?");
        mysqli_stmt_bind_param($upd, 'issiiii', $boxed_enabled, $outer_bg_type, $outer_bg_value, $container_max_width, $container_border_radius, $container_shadow, $theme_id);

        if (mysqli_stmt_execute($upd)) {
            $success = '✅ Boxed Layout berhasil disimpan!';
            
            // Reload full appearance data including boxed settings
            $appearance = get_single_row("SELECT t.*, p.avatar, p.title as profile_title, p.bio FROM themes t LEFT JOIN profiles p ON t.profile_id = p.id WHERE t.profile_id = ?", [$active_profile_id], 'i');
            
            // CRITICAL: Also reload boxed layout data with correct mapping
            $boxed_data = get_single_row("SELECT * FROM theme_boxed WHERE theme_id = ?", [$theme_id], 'i');
            if ($boxed_data) {
                $appearance['boxed_layout'] = (int)$boxed_data['enabled']; // Map enabled -> boxed_layout
                $appearance['outer_bg_type'] = $boxed_data['outer_bg_type'];
                $appearance['outer_bg_value'] = $boxed_data['outer_bg_value'];
                $appearance['container_max_width'] = (int)$boxed_data['container_max_width'];
                $appearance['container_border_radius'] = (int)$boxed_data['container_radius'];
                $appearance['container_shadow'] = (int)$boxed_data['container_shadow'];
            } else {
                $appearance['boxed_layout'] = 0; // Ensure defaults
            }
            
            $_SESSION['show_boxed_tab'] = true;
        } else {
            $error = 'Gagal menyimpan Boxed Layout!';
        }
        mysqli_stmt_close($upd);
    }

    // Fetch gradient presets (guard if table missing)
    $gradient_presets = [];
    $presets_query = "SELECT * FROM gradient_presets WHERE is_default = 1 ORDER BY preset_name";
    try {
        $gradient_presets = get_all_rows($presets_query, [], '');
    } catch (Throwable $e) {
        $gradient_presets = [];
        error_log('gradient_presets table missing or query failed: ' . $e->getMessage());
    }
    
    // If DB presets missing, fall back to hard-coded list from CSS map
    if (empty($gradient_presets)) {
        foreach ($gradient_css_map as $name => $css) {
            // Try to extract two colors from the CSS string for preview dots
            $matches = [];
            preg_match_all('/#([0-9a-fA-F]{3,6})/', $css, $matches);
            $c1 = isset($matches[0][0]) ? $matches[0][0] : '#667eea';
            $c2 = isset($matches[0][1]) ? $matches[0][1] : '#764ba2';
            $gradient_presets[] = [
                'preset_name' => $name,
                'gradient_css' => $css,
                'preview_color_1' => $c1,
                'preview_color_2' => $c2
            ];
        }
    }

    // Determine preview background (use v3 schema: bg_type and bg_value)
    // ALWAYS show inner box background in preview (bg_value from themes table)
    $preview_bg = '#ffffff'; // default
    $preview_bg_image = null;
    
    if (!empty($appearance['bg_type']) && $appearance['bg_type'] === 'image' && !empty($appearance['bg_value'])) {
        // Image background: set both bg and image URL
        $preview_bg = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; // fallback gradient
        $preview_bg_image = '../uploads/backgrounds/' . $appearance['bg_value'];
    } elseif (!empty($appearance['bg_value'])) {
        // Use stored bg_value directly from themes table (gradient or color)
        $preview_bg = $appearance['bg_value'];
    } else {
        // Fallback to defaults
        $preview_bg = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    }
    
    // Extract custom gradient colors from bg_value if it's a gradient
    if (!empty($appearance['bg_type']) && $appearance['bg_type'] === 'gradient' && !empty($appearance['bg_value'])) {
        // Parse gradient to extract colors: linear-gradient(135deg, #667eea 0%, #764ba2 100%)
        preg_match_all('/(#[0-9a-fA-F]{6}|#[0-9a-fA-F]{3})/', $appearance['bg_value'], $gradient_colors);
        if (!empty($gradient_colors[0])) {
            $appearance['custom_gradient_start'] = $gradient_colors[0][0] ?? '#667eea';
            $appearance['custom_gradient_end'] = $gradient_colors[0][1] ?? '#764ba2';
        }
    }
    
    // Set defaults if not set
    if (empty($appearance['custom_gradient_start'])) $appearance['custom_gradient_start'] = '#667eea';
    if (empty($appearance['custom_gradient_end'])) $appearance['custom_gradient_end'] = '#764ba2';
    if (empty($appearance['custom_bg_color'])) $appearance['custom_bg_color'] = '#ffffff';
    
    // Reverse-map bg_value to gradient_preset name for active state detection
    if (!empty($appearance['bg_type']) && $appearance['bg_type'] === 'gradient' && !empty($appearance['bg_value'])) {
        foreach ($gradient_css_map as $preset_name => $gradient_css) {
            if (trim($appearance['bg_value']) === trim($gradient_css)) {
                $appearance['gradient_preset'] = $preset_name;
                break;
            }
        }
    }
    
    // Fetch social icons
    $social_icons = get_all_rows("SELECT * FROM social_icons ORDER BY platform_name", [], '');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appearance - LinkMy</title>
    <?php require_once __DIR__ . '/../partials/favicons.php'; ?>
    <link href="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        body { 
            background: #f5f7fa;
            padding-top: 76px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        /* Enhanced Upload Area */
        .upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
            transform: translateY(-2px);
        }
        .upload-area.has-image {
            border-style: solid;
            border-color: #667eea;
            padding: 1rem;
        }
        .upload-area .image-preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        
        /* Theme Cards */
        .theme-card {
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .theme-card.active {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
        }
        .theme-card .check-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            z-index: 10;
        }
        .theme-card.active .check-badge {
            display: flex;
            animation: popIn 0.3s ease-out;
        }
        
        @keyframes popIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        /* Button Preview Styles */
        .button-preview {
            padding: 14px 28px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            text-align: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
        }
        .button-preview:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Live Preview Container */
        .preview-container {
            position: sticky;
            top: 20px;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
        }
        
        .preview-phone {
            max-width: 320px;
            margin: 0 auto;
            border: 4px solid #333;
            border-radius: 35px;
            padding: 50px 20px 20px;
            background: white;
            box-shadow: 0 15px 50px rgba(0,0,0,0.25);
            position: relative;
        }
        .preview-phone::before {
            content: '';
            position: absolute;
            top: 18px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 6px;
            background: #333;
            border-radius: 10px;
        }
        .preview-phone::after {
            content: '';
            position: absolute;
            top: 20px;
            right: 30px;
            width: 12px;
            height: 12px;
            background: #333;
            border-radius: 50%;
        }
        
        .preview-content {
            border-radius: 20px;
            padding: 25px 20px;
            min-height: 450px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .preview-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 12px;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .preview-link {
            padding: 14px 20px;
            margin-bottom: 12px;
            border-radius: 12px;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            font-weight: 600;
        }
        .preview-link:hover {
            transform: translateY(-2px);
        }
        
        /* Font Preview */
        .font-option {
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 10px;
        }
        .font-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        .font-option.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        }
        
        /* Tab Styling */
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            padding: 12px 20px;
        }
        .nav-tabs .nav-link.active {
            color: #667eea;
            background: white;
            border-bottom: 3px solid #667eea !important;
        }
        
        /* Color Picker */
        .color-picker-wrapper {
            position: relative;
            display: inline-block;
        }
        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            border: 3px solid #dee2e6;
            cursor: pointer;
            transition: all 0.3s;
        }
        .color-preview:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        
        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .loading-overlay.active {
            display: flex;
        }
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Gradient Preset Cards */
        .gradient-preset-card {
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            text-align: center;
        }
        .gradient-preset-card .check-badge {
            opacity: 0;
        }
        .gradient-preset-card.active .check-badge {
            opacity: 1;
        }
        .gradient-preview {
            width: 100%;
            height: 100px;
            border-radius: 12px;
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .gradient-preset-card:hover .gradient-preview {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .gradient-colors {
            position: absolute;
            bottom: 8px;
            right: 8px;
            display: flex;
            gap: 5px;
        }
        .color-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .gradient-name {
            margin: 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #333;
        }
        
        /* Color Picker */
        .color-picker-wrapper {
            position: relative;
        }
        .form-control-color {
            height: 50px;
            width: 100%;
            border-radius: 10px;
            cursor: pointer;
        }
        .form-control-color::-webkit-color-swatch-wrapper {
            padding: 0;
        }
        .form-control-color::-webkit-color-swatch {
            border: none;
            border-radius: 8px;
        }
        
        /* Layout Cards */
        .layout-card {
            padding: 20px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            background: white;
        }
        .layout-card:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }
        .layout-card.active {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        }
        .layout-card .check-badge {
            opacity: 0;
        }
        .layout-card.active .check-badge {
            opacity: 1;
        }
        .layout-preview {
            width: 100%;
            height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        .layout-preview.left {
            align-items: flex-start;
            padding-left: 15px;
        }
        .layout-preview.minimal {
            flex-direction: row;
            gap: 12px;
        }
        .layout-icon {
            width: 40px;
            height: 40px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        .layout-icon.small {
            width: 30px;
            height: 30px;
            font-size: 16px;
        }
        .layout-lines {
            display: flex;
            flex-direction: column;
            gap: 5px;
            width: 100%;
        }
        .layout-preview.minimal .layout-lines {
            flex: 1;
        }
        .line {
            height: 8px;
            background: #dee2e6;
            border-radius: 4px;
            width: 100%;
        }
        .line.short {
            width: 60%;
            margin: 0 auto;
        }
        .layout-preview.left .line.short {
            margin: 0;
        }
        .line.thin {
            height: 6px;
        }
        
        /* Social Icons Grid */
        .social-icons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 15px;
        }
        .social-icon-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 15px;
            border: 2px solid #f0f0f0;
            border-radius: 10px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .social-icon-item:hover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }
        .social-icon-item i {
            font-size: 2rem;
        }
        .social-icon-item span {
            font-size: 0.75rem;
            font-weight: 600;
            color: #666;
            text-align: center;
        }
        
        /* Badges */
        .badge {
            font-size: 0.7rem;
            padding: 3px 8px;
        }
    </style>
</head>
<body>
    <?php include '../partials/admin_nav.php'; ?>
    
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner mb-3"></div>
            <p>Uploading...</p>
        </div>
    </div>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="fw-bold">
                    <i class="bi bi-palette-fill text-primary"></i> Customize Your Appearance
                </h2>
                <p class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Personalisasi tampilan halaman profil Anda dengan berbagai pilihan tema, warna, dan gaya
                </p>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- DEBUG: Show saved data -->
        <?php if (isset($_GET['debug'])): ?>
            <div class="alert alert-info">
                <h5>🔍 Debug - Saved Data in Database:</h5>
                <ul style="font-family: monospace; font-size: 12px;">
                    <li>gradient_preset: <strong><?= $appearance['gradient_preset'] ?? 'NULL' ?></strong></li>
                    <li>custom_bg_color: <strong><?= $appearance['custom_bg_color'] ?? 'NULL' ?></strong></li>
                    <li>custom_button_color: <strong><?= $appearance['custom_button_color'] ?? 'NULL' ?></strong></li>
                    <li>custom_text_color: <strong><?= $appearance['custom_text_color'] ?? 'NULL' ?></strong></li>
                    <li>profile_layout: <strong><?= $appearance['profile_layout'] ?? 'NULL' ?></strong></li>
                    <li>show_profile_border: <strong><?= $appearance['show_profile_border'] ?? 'NULL' ?></strong></li>
                    <li>enable_animations: <strong><?= $appearance['enable_animations'] ?? 'NULL' ?></strong></li>
                </ul>
                <?php if ($success): ?>
                    <p class="text-success mb-0">✅ Last action: <?= htmlspecialchars($success) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#profile-tab">
                            <i class="bi bi-person-badge me-2"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#theme-tab">
                            <i class="bi bi-palette me-2"></i>Theme & Colors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#media-tab">
                            <i class="bi bi-image me-2"></i>Media
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#boxed-layout-tab">
                            <i class="bi bi-bounding-box me-2"></i>Boxed Layout
                            <span class="badge bg-success ms-1">New</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile-tab">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-4">
                                    <i class="bi bi-person-badge text-primary"></i> Profile Information
                                </h5>
                                <form method="POST" id="profileForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Profile Title</label>
                                        <input type="text" class="form-control form-control-lg" name="profile_title" 
                                               id="profileTitle"
                                               value="<?= htmlspecialchars($appearance['profile_title'] ?? $current_username) ?>"
                                               placeholder="Your name or title">
                                        <small class="text-muted">This will be displayed as your main heading</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Bio</label>
                                        <textarea class="form-control" name="bio" rows="4" id="profileBio"
                                                  placeholder="Tell your audience about yourself..."><?= htmlspecialchars($appearance['bio'] ?? '') ?></textarea>
                                        <small class="text-muted">A short description that appears below your title</small>
                                    </div>
                                    <button type="submit" name="update_info" class="btn btn-primary btn-lg">
                                        <i class="bi bi-save me-2"></i>Save Profile Info
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Theme & Colors Tab (Merged from Theme + Advanced) -->
                    <div class="tab-pane fade" id="theme-tab">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-4">
                                    <i class="bi bi-palette2 text-primary"></i> Background & Colors
                                </h5>
                                <form method="POST" id="advancedForm">
                                    <!-- Gradient Presets -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-rainbow text-primary me-2"></i>Gradient Backgrounds
                                    </h6>
                                    <p class="text-muted mb-3">Pilih gradient preset yang sudah jadi</p>
                                    
                                    <div class="row g-3 mb-4">
                                        <?php $shown_presets = array_slice($gradient_presets, 0, 12); // Show 12 gradients ?>
                                        <?php foreach ($shown_presets as $preset): ?>
                                        <div class="col-md-3 col-6">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="gradient_preset" value="<?= htmlspecialchars($preset['preset_name']) ?>" 
                                                       <?= ($appearance['gradient_preset'] ?? '') == $preset['preset_name'] ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectGradientFromRadio(this, '<?= htmlspecialchars($preset['gradient_css']) ?>')">
                                                <div class="gradient-preset-card <?= ($appearance['gradient_preset'] ?? '') == $preset['preset_name'] ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="gradient-preview" style="background: <?= htmlspecialchars($preset['gradient_css']) ?>;">
                                                        <div class="gradient-colors">
                                                            <span class="color-dot" style="background: <?= htmlspecialchars($preset['preview_color_1']) ?>;"></span>
                                                            <span class="color-dot" style="background: <?= htmlspecialchars($preset['preview_color_2']) ?>;"></span>
                                                        </div>
                                                    </div>
                                                    <p class="gradient-name"><?= htmlspecialchars($preset['preset_name']) ?></p>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Custom Colors Section -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-droplet text-primary me-2"></i>Custom Colors
                                    </h6>
                                    <p class="text-muted small mb-3">Atau buat kombinasi warna sendiri</p>
                                    
                                    <!-- Custom Gradient Builder -->
                                    <div class="card bg-light mb-3">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-3">
                                                <i class="bi bi-brush text-primary me-2"></i>Custom Gradient Builder
                                                <span class="badge bg-primary ms-2">New!</span>
                                            </h6>
                                            <p class="text-muted small mb-3">Buat gradient sendiri dari 2 warna</p>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Gradient Start Color</label>
                                                    <div class="color-picker-wrapper">
                                                        <input type="color" class="form-control form-control-color" 
                                                               name="custom_gradient_start" id="customGradientStart"
                                                               value="<?= htmlspecialchars($appearance['custom_gradient_start'] ?? '#667eea') ?>">
                                                        <input type="text" class="form-control form-control-sm mt-1" 
                                                               value="<?= htmlspecialchars($appearance['custom_gradient_start'] ?? '#667eea') ?>"
                                                               id="customGradientStartHex" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Gradient End Color</label>
                                                    <div class="color-picker-wrapper">
                                                        <input type="color" class="form-control form-control-color" 
                                                               name="custom_gradient_end" id="customGradientEnd"
                                                               value="<?= htmlspecialchars($appearance['custom_gradient_end'] ?? '#764ba2') ?>">
                                                        <input type="text" class="form-control form-control-sm mt-1" 
                                                               value="<?= htmlspecialchars($appearance['custom_gradient_end'] ?? '#764ba2') ?>"
                                                               id="customGradientEndHex" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-12">
                                                    <div class="gradient-preview" id="customGradientPreview" 
                                                         style="height: 80px; border-radius: 12px; background: linear-gradient(135deg, <?= htmlspecialchars($appearance['custom_gradient_start'] ?? '#667eea') ?> 0%, <?= htmlspecialchars($appearance['custom_gradient_end'] ?? '#764ba2') ?> 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                                        <i class="bi bi-eye me-2"></i>Preview Custom Gradient
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Background Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_bg_color" id="customBgColor"
                                                       value="<?= htmlspecialchars($appearance['custom_bg_color'] ?? '#ffffff') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['custom_bg_color'] ?? '#ffffff') ?>"
                                                       id="customBgColorHex" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Button Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_button_color" id="customButtonColor"
                                                       value="<?= htmlspecialchars($appearance['button_color'] ?? '#667eea') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['button_color'] ?? '#667eea') ?>"
                                                       id="customButtonColorHex" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Text Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_text_color" id="customTextColor"
                                                       value="<?= htmlspecialchars($appearance['text_color'] ?? '#ffffff') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['text_color'] ?? '#ffffff') ?>"
                                                       id="customTextColorHex" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Shadow Intensity</label>
                                            <select class="form-select" name="shadow_intensity" id="shadowIntensity">
                                                <option value="none" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'none' ? 'selected' : '' ?>>None</option>
                                                <option value="light" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'light' ? 'selected' : '' ?>>Light</option>
                                                <option value="medium" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'medium' ? 'selected' : '' ?>>Medium</option>
                                                <option value="heavy" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'heavy' ? 'selected' : '' ?>>Heavy</option>
                                            </select>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Button Style -->
                                    <h6 class="fw-bold mb-3"><i class="bi bi-square text-primary me-2"></i>Button Style</h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block;" onclick="selectButtonStyle('rounded')">
                                                <input type="radio" name="button_style" value="rounded" <?= ($appearance['button_style'] ?? 'rounded') == 'rounded' ? 'checked' : '' ?> hidden>
                                                <div class="card text-center h-100 theme-card <?= ($appearance['button_style'] ?? 'rounded') == 'rounded' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="card-body">
                                                        <div class="button-preview" style="border-radius: 12px;"><i class="bi bi-link-45deg me-2"></i>Link</div>
                                                        <p class="mt-3 mb-0 fw-semibold">Rounded</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block;" onclick="selectButtonStyle('sharp')">
                                                <input type="radio" name="button_style" value="sharp" <?= ($appearance['button_style'] ?? '') == 'sharp' ? 'checked' : '' ?> hidden>
                                                <div class="card text-center h-100 theme-card <?= ($appearance['button_style'] ?? '') == 'sharp' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="card-body">
                                                        <div class="button-preview" style="border-radius: 0;"><i class="bi bi-link-45deg me-2"></i>Link</div>
                                                        <p class="mt-3 mb-0 fw-semibold">Sharp</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block;" onclick="selectButtonStyle('pill')">
                                                <input type="radio" name="button_style" value="pill" <?= ($appearance['button_style'] ?? '') == 'pill' ? 'checked' : '' ?> hidden>
                                                <div class="card text-center h-100 theme-card <?= ($appearance['button_style'] ?? '') == 'pill' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="card-body">
                                                        <div class="button-preview" style="border-radius: 50px;"><i class="bi bi-link-45deg me-2"></i>Link</div>
                                                        <p class="mt-3 mb-0 fw-semibold">Pill</p>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Layout Options -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-layout-text-window text-primary me-2"></i>Layout & Advanced Options
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Profile Layout</label>
                                            <select class="form-select" name="profile_layout" id="profileLayout" onchange="updatePreviewLayout()">
                                                <option value="centered" <?= ($appearance['layout'] ?? 'centered') == 'centered' ? 'selected' : '' ?>>Centered</option>
                                                <option value="left" <?= ($appearance['layout'] ?? '') == 'left' ? 'selected' : '' ?>>Left Aligned</option>
                                                <option value="minimal" <?= ($appearance['layout'] ?? '') == 'minimal' ? 'selected' : '' ?>>Minimal</option>
                                            </select>
                                            <small class="text-muted">Posisi konten di halaman</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Additional Effects</label>
                                            <div class="d-flex gap-4 flex-wrap mt-2">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enable_animations" 
                                                           id="enableAnimations" value="1" <?= ($appearance['enable_animations'] ?? 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="enableAnimations">
                                                        <i class="bi bi-stars"></i> Animations
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="enable_glass_effect" 
                                                           id="enableGlassEffect" value="1" <?= ($appearance['enable_glass_effect'] ?? 0) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="enableGlassEffect">
                                                        <i class="bi bi-window"></i> Glass Effect
                                                    </label>
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="show_profile_border" 
                                                           id="showProfileBorder" value="1" <?= ($appearance['show_profile_border'] ?? 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="showProfileBorder">
                                                        <i class="bi"></i> Profile Border
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="container_style" value="<?= $appearance['container_style'] ?? 'wide' ?>">

                                    <button type="submit" name="update_advanced" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-save me-2"></i>Save Theme & Colors
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Media Tab -->
                    <div class="tab-pane fade" id="media-tab">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-4">
                                    <i class="bi bi-person-circle text-primary"></i> Profile Picture
                                </h5>
                                <form method="POST" enctype="multipart/form-data" id="profilePicForm" onsubmit="showLoading()">
                                    <div class="upload-area mb-3 <?= !empty($appearance['avatar']) && $appearance['avatar'] != 'default-avatar.png' ? 'has-image' : '' ?>" onclick="document.getElementById('profilePicInput').click()">
                                        <?php if (!empty($appearance['avatar']) && $appearance['avatar'] != 'default-avatar.png'): ?>
                                            <img src="../uploads/profile_pics/<?= htmlspecialchars($appearance['avatar']) ?>" id="profilePicPreview" class="image-preview">
                                        <?php else: ?>
                                            <i class="bi bi-person-circle text-muted" style="font-size: 4rem;"></i>
                                            <p class="mb-2 fw-semibold"><i class="bi bi-cloud-upload me-2"></i>Click to Upload Profile Photo</p>
                                            <small class="text-muted">Max 2MB • JPG, PNG, GIF, WebP</small>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file" id="profilePicInput" name="profile_pic" accept="image/*" style="display: none;" onchange="openCropModal(this)">
                                    <input type="hidden" id="croppedImageData" name="cropped_image_data">
                                    <button type="submit" name="upload_profile" class="btn btn-primary btn-lg w-100" id="uploadProfileBtn" disabled>
                                        <i class="bi bi-upload me-2"></i>Upload Profile Picture
                                    </button>
                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-info-circle"></i> Foto akan otomatis di-crop menjadi lingkaran. Anda bisa adjust posisi dan zoom.
                                    </small>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-4">
                                    <i class="bi bi-card-image text-primary"></i> Background Image
                                </h5>
                                
                                <div class="alert alert-info mb-3">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <strong>Rekomendasi:</strong> 1080x1920px (portrait) atau 1920x1080px (landscape) • Max 5MB
                                </div>
                                
                                <div class="upload-area mb-3 <?= !empty($appearance['bg_image_filename']) ? 'has-image' : '' ?>" onclick="document.getElementById('bgImageInput').click()">
                                    <?php if (!empty($appearance['bg_image_filename'])): ?>
                                        <img src="../uploads/backgrounds/<?= htmlspecialchars($appearance['bg_image_filename']) ?>?t=<?= time() ?>" id="bgImagePreview" class="image-preview">
                                        <div class="mt-2">
                                            <button type="button" class="btn btn-primary btn-sm me-2" onclick="event.stopPropagation(); document.getElementById('bgImageInput').click()">
                                                <i class="bi bi-arrow-repeat"></i> Change
                                            </button>
                                            <a href="?remove_bg=1" class="btn btn-danger btn-sm" onclick="event.stopPropagation(); return confirm('Remove background image?')">
                                                <i class="bi bi-trash"></i> Remove
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                        <p class="mb-2 fw-semibold"><i class="bi bi-cloud-upload me-2"></i>Click to Upload Background</p>
                                        <small class="text-muted">Pilih gambar landscape/portrait untuk background profil Anda</small>
                                    <?php endif; ?>
                                </div>
                                <input type="file" id="bgImageInput" name="bg_image" accept="image/*" style="display: none;" onchange="handleBackgroundUpload(this)">
                                <form id="bgUploadForm" method="POST" enctype="multipart/form-data" style="display: none;">
                                    <input type="hidden" name="bg_image_data" id="bgImageData">
                                    <input type="hidden" name="upload_background" value="1">
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Crop Modal -->
                    <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-scissors"></i> Crop & Adjust Profile Picture
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetCropper()"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="crop-container" style="max-height: 400px; overflow: hidden; background: #f8f9fa; border-radius: 10px;">
                                                <img id="cropImage" style="max-width: 100%; display: block;">
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <div class="d-flex flex-column gap-3">
                                                <div>
                                                    <h6 class="fw-semibold mb-2"><i class="bi bi-eye"></i> Preview</h6>
                                                    <div class="text-center">
                                                        <div id="cropPreview" style="width: 150px; height: 150px; border-radius: 50%; overflow: hidden; margin: 0 auto; border: 3px solid #667eea; background: #f8f9fa;"></div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="fw-semibold mb-2"><i class="bi bi-sliders"></i> Controls</h6>
                                                    <div class="btn-group w-100 mb-2" role="group">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(0.1)" title="Zoom In">
                                                            <i class="bi bi-zoom-in"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.zoom(-0.1)" title="Zoom Out">
                                                            <i class="bi bi-zoom-out"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(-45)" title="Rotate Left">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.rotate(45)" title="Rotate Right">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    </div>
                                                    <div class="btn-group w-100 mb-2" role="group">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.move(-10, 0)" title="Move Left">
                                                            <i class="bi bi-arrow-left"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.move(10, 0)" title="Move Right">
                                                            <i class="bi bi-arrow-right"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.move(0, -10)" title="Move Up">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cropper.move(0, 10)" title="Move Down">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="cropper.reset()" title="Reset">
                                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-info mt-3 mb-0">
                                        <small>
                                            <i class="bi bi-info-circle"></i> <strong>Tips:</strong> 
                                            Gunakan scroll mouse untuk zoom, drag untuk move position. 
                                            Foto akan otomatis di-crop menjadi lingkaran sempurna.
                                        </small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetCropper()">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="applyCrop()">
                                        <i class="bi bi-check-circle"></i> Apply & Save
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Background Crop Modal -->
                    <div class="modal fade" id="bgCropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold">
                                        <i class="bi bi-crop"></i> Adjust Background Image
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="resetBgCropper()"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-lg-9">
                                            <div class="crop-container" style="max-height: 500px; overflow: hidden; background: #000; border-radius: 10px;">
                                                <img id="bgCropImage" style="max-width: 100%; display: block;">
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <h6 class="fw-semibold mb-2"><i class="bi bi-sliders"></i> Controls</h6>
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bgCropper.zoom(0.1)">
                                                    <i class="bi bi-zoom-in"></i> Zoom In
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bgCropper.zoom(-0.1)">
                                                    <i class="bi bi-zoom-out"></i> Zoom Out
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bgCropper.rotate(-90)">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Rotate Left
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bgCropper.rotate(90)">
                                                    <i class="bi bi-arrow-clockwise"></i> Rotate Right
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="bgCropper.reset()">
                                                    <i class="bi bi-arrow-repeat"></i> Reset
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetBgCropper()">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="applyBgCrop()">
                                        <i class="bi bi-check-circle"></i> Save Background
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Tab (MERGED INTO THEME TAB) -->
                    <div class="tab-pane fade" id="advanced-tab" style="display:none;">
                        <!-- Gradient Presets -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3">
                                    <i class="bi bi-palette2 text-primary"></i> Gradient Backgrounds
                                    <span class="badge bg-success ms-2">New!</span>
                                </h5>
                                <p class="text-muted mb-4">Pilih gradient preset yang sudah jadi atau buat custom color sendiri</p>
                                
                                <form method="POST" id="advancedForm">
                                    <div class="row g-3 mb-4">
                                        <?php foreach ($gradient_presets as $preset): ?>
                                        <div class="col-md-4 col-6">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="gradient_preset" value="<?= htmlspecialchars($preset['preset_name']) ?>" 
                                                       <?= ($appearance['gradient_preset'] ?? '') == $preset['preset_name'] ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectGradientFromRadio(this, '<?= htmlspecialchars($preset['gradient_css']) ?>')">
                                                <div class="gradient-preset-card <?= ($appearance['gradient_preset'] ?? '') == $preset['preset_name'] ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="gradient-preview" style="background: <?= htmlspecialchars($preset['gradient_css']) ?>;">
                                                        <div class="gradient-colors">
                                                            <span class="color-dot" style="background: <?= htmlspecialchars($preset['preview_color_1']) ?>;"></span>
                                                            <span class="color-dot" style="background: <?= htmlspecialchars($preset['preview_color_2']) ?>;"></span>
                                                        </div>
                                                    </div>
                                                    <p class="gradient-name"><?= htmlspecialchars($preset['preset_name']) ?></p>
                                                </div>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Custom Colors -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-droplet text-primary me-2"></i>Custom Colors
                                    </h6>
                                    <p class="text-muted small mb-3">Atau buat kombinasi warna sendiri (akan override gradient preset)</p>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Background Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_bg_color" id="customBgColor"
                                                       value="<?= htmlspecialchars($appearance['custom_bg_color'] ?? '#ffffff') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['custom_bg_color'] ?? '#ffffff') ?>"
                                                       id="customBgColorHex" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Button Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_button_color" id="customButtonColor"
                                                       value="<?= htmlspecialchars($appearance['custom_button_color'] ?? '#667eea') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['custom_button_color'] ?? '#667eea') ?>"
                                                       id="customButtonColorHex" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Text Color</label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_text_color" id="customTextColor"
                                                       value="<?= htmlspecialchars($appearance['custom_text_color'] ?? '#333333') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['custom_text_color'] ?? '#333333') ?>"
                                                       id="customTextColorHex" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-type text-info me-1"></i>Link Text Color
                                                <span class="badge bg-info ms-1">v2.1</span>
                                            </label>
                                            <div class="color-picker-wrapper">
                                                <input type="color" class="form-control form-control-color" 
                                                       name="custom_link_text_color" id="customLinkTextColor"
                                                       value="<?= htmlspecialchars($appearance['custom_link_text_color'] ?? '#333333') ?>">
                                                <input type="text" class="form-control form-control-sm mt-1" 
                                                       value="<?= htmlspecialchars($appearance['custom_link_text_color'] ?? '#333333') ?>"
                                                       id="customLinkTextColorHex" readonly>
                                            </div>
                                            <small class="text-muted">Warna text pada link cards</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">
                                                <i class="bi bi-shadow text-warning me-1"></i>Shadow Intensity
                                                <span class="badge bg-warning text-dark ms-1">v2.1</span>
                                            </label>
                                            <select class="form-select" name="shadow_intensity" id="shadowIntensity">
                                                <option value="none" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'none' ? 'selected' : '' ?>>None - No shadow</option>
                                                <option value="light" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'light' ? 'selected' : '' ?>>Light - Subtle</option>
                                                <option value="medium" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'medium' ? 'selected' : '' ?>>Medium - Default</option>
                                                <option value="heavy" <?= ($appearance['shadow_intensity'] ?? 'medium') == 'heavy' ? 'selected' : '' ?>>Heavy - Strong</option>
                                            </select>
                                            <small class="text-muted">Intensity shadow pada link cards</small>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Profile Layout -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-layout-text-window text-primary me-2"></i>Profile Layout
                                    </h6>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="profile_layout" value="centered" 
                                                       <?= ($appearance['profile_layout'] ?? 'centered') == 'centered' ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectLayoutFromRadio(this)">
                                                <div class="layout-card <?= ($appearance['profile_layout'] ?? 'centered') == 'centered' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="layout-preview">
                                                        <div class="layout-icon">⬤</div>
                                                        <div class="layout-lines">
                                                            <div class="line"></div>
                                                            <div class="line short"></div>
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 fw-semibold">Centered</p>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="profile_layout" value="left" 
                                                       <?= ($appearance['profile_layout'] ?? '') == 'left' ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectLayoutFromRadio(this)">
                                                <div class="layout-card <?= ($appearance['profile_layout'] ?? '') == 'left' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="layout-preview left">
                                                        <div class="layout-icon">⬤</div>
                                                        <div class="layout-lines">
                                                            <div class="line"></div>
                                                            <div class="line short"></div>
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 fw-semibold">Left Aligned</p>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-4">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="profile_layout" value="minimal" 
                                                       <?= ($appearance['profile_layout'] ?? '') == 'minimal' ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectLayoutFromRadio(this)">
                                                <div class="layout-card <?= ($appearance['profile_layout'] ?? '') == 'minimal' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="layout-preview minimal">
                                                        <div class="layout-icon small">⬤</div>
                                                        <div class="layout-lines">
                                                            <div class="line thin"></div>
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 fw-semibold">Minimal</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Container Style (NEW - Linktree Style) -->
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-bounding-box text-primary me-2"></i>Container Style
                                        <span class="badge bg-success ms-2">New!</span>
                                    </h6>
                                    <p class="text-muted mb-3">Pilih style container untuk halaman profile Anda</p>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="container_style" value="wide" 
                                                       <?= ($appearance['container_style'] ?? 'wide') == 'wide' ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectContainerStyle(this)">
                                                <div class="layout-card <?= ($appearance['container_style'] ?? 'wide') == 'wide' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="layout-preview">
                                                        <div style="width: 100%; height: 60px; background: linear-gradient(135deg, #667eea 20%, #764ba2 80%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px;">
                                                            <div style="text-align: center;">
                                                                <div style="font-weight: bold;">Wide Layout</div>
                                                                <div style="font-size: 8px; opacity: 0.8;">Full width container</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 fw-semibold mt-2">Wide - Default</p>
                                                    <small class="text-muted">Container lebar penuh</small>
                                                </div>
                                            </label>
                                        </div>
                                        <div class="col-md-6">
                                            <label style="cursor: pointer; display: block; margin: 0;">
                                                <input type="radio" name="container_style" value="boxed" 
                                                       <?= ($appearance['container_style'] ?? '') == 'boxed' ? 'checked' : '' ?>
                                                       style="position: absolute; opacity: 0; pointer-events: none;"
                                                       onchange="selectContainerStyle(this)">
                                                <div class="layout-card <?= ($appearance['container_style'] ?? '') == 'boxed' ? 'active' : '' ?>">
                                                    <div class="check-badge"><i class="bi bi-check-lg"></i></div>
                                                    <div class="layout-preview">
                                                        <div style="width: 100%; height: 60px; background: linear-gradient(135deg, #e0e0e0 0%, #f5f5f5 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; padding: 8px;">
                                                            <div style="width: 70%; height: 100%; background: linear-gradient(135deg, #667eea 20%, #764ba2 80%); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-size: 9px; font-weight: bold; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                                                <div style="text-align: center;">
                                                                    <div>Boxed</div>
                                                                    <div style="font-size: 7px; opacity: 0.9;">Linktree Style</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <p class="mb-0 fw-semibold mt-2">Boxed - Linktree Style 🔥</p>
                                                    <small class="text-muted">Kotak kecil di tengah background</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Additional Options -->
                                    <hr class="my-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="bi bi-gear text-primary me-2"></i>Additional Options
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="show_profile_border" 
                                                       id="showProfileBorder" <?= ($appearance['show_profile_border'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="showProfileBorder">
                                                    <strong>Show Profile Border</strong><br>
                                                    <small class="text-muted">Border around profile pic</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="enable_animations" 
                                                       id="enableAnimations" <?= ($appearance['enable_animations'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="enableAnimations">
                                                    <strong>Enable Animations</strong><br>
                                                    <small class="text-muted">Hover effects on links</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="enable_glass_effect" 
                                                       id="enableGlassEffect" <?= ($appearance['enable_glass_effect'] ?? 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="enableGlassEffect">
                                                    <strong>Glass Morphism</strong> <span class="badge bg-info">v2.1</span><br>
                                                    <small class="text-muted">Frosted glass effect</small>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="enable_categories" 
                                                       id="enableCategories" <?= ($appearance['enable_categories'] ?? 0) ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="enableCategories">
                                                    <strong>Link Categories</strong> <span class="badge bg-success">New!</span><br>
                                                    <small class="text-muted">Group links by category</small>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div id="categoriesHelp" class="alert alert-info mt-3" style="display: <?= ($appearance['enable_categories'] ?? 0) ? 'block' : 'none' ?>;">
                                        <h6 class="fw-bold mb-2"><i class="bi bi-info-circle"></i> Cara Menggunakan Categories</h6>
                                        <ol class="mb-2 small">
                                            <li>Buat kategori baru di <a href="categories.php" class="alert-link fw-bold">Categories Management</a></li>
                                            <li>Edit link Anda dan assign ke kategori tertentu</li>
                                            <li>Links akan digroup otomatis berdasarkan kategori di profile page</li>
                                        </ol>
                                        <p class="mb-0 small text-muted">
                                            <i class="bi bi-lightbulb"></i> <strong>Tip:</strong> Kategori berguna untuk membagi link social media, work projects, portfolio, dll.
                                        </p>
                                    </div>

                                    <hr class="my-4">

                                    <!-- Hidden input to ensure update_advanced is sent -->
                                    <input type="hidden" name="update_advanced" value="1">
                                    
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-save me-2"></i>Save Advanced Settings
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Social Icons Reference -->
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3">
                                    <i class="bi bi-collection text-primary"></i> Available Social Icons
                                </h5>
                                <p class="text-muted mb-3">Gunakan icon ini saat menambah link di Dashboard</p>
                                <div class="social-icons-grid">
                                    <?php foreach ($social_icons as $icon): ?>
                                    <div class="social-icon-item" title="<?= htmlspecialchars($icon['platform_name']) ?>">
                                        <i class="<?= htmlspecialchars($icon['icon_class']) ?>" 
                                           style="color: <?= htmlspecialchars($icon['icon_color'] ?? '#667eea') ?>;"></i>
                                        <span><?= htmlspecialchars($icon['platform_name']) ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boxed Layout Tab -->
                    <div class="tab-pane fade" id="boxed-layout-tab">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title fw-bold mb-3">
                                    <i class="bi bi-bounding-box text-primary"></i> Boxed Layout Mode
                                </h5>
                                <p class="text-muted mb-4">
                                    Tampilkan profil Anda dalam kotak dengan background luar yang dapat dikustomisasi - gaya Linktree!
                                </p>

                                <form method="POST" id="boxedLayoutForm">
                                    <!-- Enable Boxed Layout -->
                                    <div class="mb-4 p-3 bg-light rounded">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="boxed_layout" 
                                                   id="boxedLayoutEnable" value="1" 
                                                   <?= ($appearance['boxed_layout'] ?? 0) ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="boxedLayoutEnable">
                                                Enable Boxed Layout
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Aktifkan untuk menampilkan konten dalam kotak dengan background luar
                                        </small>
                                    </div>

                                    <div id="boxedLayoutSettings" style="display: <?= ($appearance['boxed_layout'] ?? 0) ? 'block' : 'none' ?>;">
                                        <!-- Outer Background Settings -->
                                        <div class="card mb-3 border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <i class="bi bi-image"></i> Outer Background
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Background Type</label>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <span class="badge bg-info" id="bgTypeIndicator">
                                                            <i class="bi bi-palette-fill"></i> 
                                                            Current: <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'gradient' ? 'Gradient' : 'Solid Color' ?>
                                                        </span>
                                                    </div>
                                                    <select class="form-select" name="outer_bg_type" id="outerBgType" onchange="updateBgTypeIndicator()">
                                                        <option value="color" <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'color' ? 'selected' : '' ?>>Solid Color</option>
                                                        <option value="gradient" <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'gradient' ? 'selected' : '' ?>>Gradient</option>
                                                    </select>
                                                </div>

                                                <!-- Solid Color Option -->
                                                <div id="solidColorOption" style="display: <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'color' ? 'block' : 'none' ?>;">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Background Color</label>
                                                        <div class="input-group">
                                                            <input type="color" class="form-control form-control-color" 
                                                                   name="outer_bg_color" id="outerBgColor"
                                                                   value="<?= $appearance['outer_bg_color'] ?? '#667eea' ?>">
                                                            <input type="text" class="form-control" 
                                                                   value="<?= $appearance['outer_bg_color'] ?? '#667eea' ?>"
                                                                   id="outerBgColorHex" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Gradient Option -->
                                                <div id="gradientOption" style="display: <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'gradient' ? 'block' : 'none' ?>;">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Gradient Start</label>
                                                            <div class="input-group">
                                                                <input type="color" class="form-control form-control-color" 
                                                                       name="outer_bg_gradient_start" id="gradientStart"
                                                                       value="<?= $appearance['outer_bg_gradient_start'] ?? '#667eea' ?>">
                                                                <input type="text" class="form-control" 
                                                                       value="<?= $appearance['outer_bg_gradient_start'] ?? '#667eea' ?>"
                                                                       id="gradientStartHex" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label fw-semibold">Gradient End</label>
                                                            <div class="input-group">
                                                                <input type="color" class="form-control form-control-color" 
                                                                       name="outer_bg_gradient_end" id="gradientEnd"
                                                                       value="<?= $appearance['outer_bg_gradient_end'] ?? '#764ba2' ?>">
                                                                <input type="text" class="form-control" 
                                                                       value="<?= $appearance['outer_bg_gradient_end'] ?? '#764ba2' ?>"
                                                                       id="gradientEndHex" readonly>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Container Settings -->
                                        <div class="card mb-3 border-info">
                                            <div class="card-header bg-info text-white">
                                                <i class="bi bi-box"></i> Container Settings
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <i class="bi bi-info-circle"></i> <strong>Catatan:</strong> Warna background container menggunakan pengaturan dari tab "Tema & Warna". Boxed layout hanya mengatur ukuran dan border.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Container Width</label>
                                                    <div class="input-group">
                                                        <input type="range" class="form-range" name="container_max_width" 
                                                               id="containerWidth" min="320" max="600" step="10"
                                                               value="<?= $appearance['container_max_width'] ?? 480 ?>">
                                                        <span class="input-group-text" id="containerWidthValue">
                                                            <?= $appearance['container_max_width'] ?? 480 ?>px
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">Lebar maksimal container (320-600px)</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Border Radius</label>
                                                    <div class="input-group">
                                                        <input type="range" class="form-range" name="container_border_radius" 
                                                               id="containerRadius" min="0" max="50" step="5"
                                                               value="<?= $appearance['container_border_radius'] ?? 30 ?>">
                                                        <span class="input-group-text" id="containerRadiusValue">
                                                            <?= $appearance['container_border_radius'] ?? 30 ?>px
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">Kelengkungan sudut container</small>
                                                </div>

                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="container_shadow" 
                                                           id="containerShadow" value="1"
                                                           <?= ($appearance['container_shadow'] ?? 1) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="containerShadow">
                                                        <strong>Container Shadow</strong><br>
                                                        <small class="text-muted">Tambahkan bayangan pada container</small>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Preview Box -->
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="fw-bold mb-3 text-center">
                                                    <i class="bi bi-eye"></i> Preview
                                                </h6>
                                                <div id="boxedPreview" class="mx-auto" style="height: 300px; border-radius: 15px; display: flex; align-items: center; justify-content: center; background: <?= ($appearance['outer_bg_type'] ?? 'gradient') == 'gradient' ? 'linear-gradient(135deg, ' . ($appearance['outer_bg_gradient_start'] ?? '#667eea') . ', ' . ($appearance['outer_bg_gradient_end'] ?? '#764ba2') . ')' : ($appearance['outer_bg_color'] ?? '#667eea') ?>;">
                                                    <div id="boxedPreviewInner" style="width: <?= $appearance['container_max_width'] ?? 480 ?>px; max-width: 90%; background: <?= !empty($appearance['gradient_preset']) ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : ($appearance['custom_bg_color'] ?? '#ffffff') ?>; padding: 30px; border-radius: <?= $appearance['container_border_radius'] ?? 30 ?>px; <?= ($appearance['container_shadow'] ?? 1) ? 'box-shadow: 0 10px 40px rgba(0,0,0,0.2);' : '' ?>">
                                                        <div class="text-center">
                                                            <div class="bg-secondary rounded-circle mx-auto mb-3" style="width: 80px; height: 80px;"></div>
                                                            <h6 class="fw-bold">Your Name</h6>
                                                            <p class="text-muted small mb-3">Your bio here</p>
                                                            <div class="bg-light rounded p-2 mb-2">Sample Link</div>
                                                            <div class="bg-light rounded p-2">Sample Link</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    <input type="hidden" name="update_boxed_layout" value="1">
                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                        <i class="bi bi-save me-2"></i>Save Boxed Layout Settings
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Live Preview Sidebar -->
            <div class="col-lg-4">
                <div class="preview-container">
                    <h5 class="fw-bold mb-3 text-center">
                        <i class="bi bi-phone me-2"></i>Live Preview
                    </h5>
                    <p class="text-muted text-center mb-4" style="font-size: 0.85rem;">
                        See changes in real-time
                    </p>
                    
                    <div class="preview-phone">
                        <?php
                        // Determine if boxed layout is enabled for preview
                        $is_boxed = ($appearance['boxed_layout'] ?? 0);
                        $outer_preview_bg = '';
                        if ($is_boxed) {
                            if (($appearance['outer_bg_type'] ?? 'gradient') == 'gradient') {
                                $outer_preview_bg = 'linear-gradient(135deg, ' . ($appearance['outer_bg_gradient_start'] ?? '#667eea') . ', ' . ($appearance['outer_bg_gradient_end'] ?? '#764ba2') . ')';
                            } else {
                                $outer_preview_bg = $appearance['outer_bg_color'] ?? '#667eea';
                            }
                        }
                        ?>
                        <?php if ($is_boxed): ?>
                        <!-- Boxed Layout Preview: Outer container with inner box -->
                        <div id="previewOuterBox" style="background: <?= $outer_preview_bg ?>; padding: 20px; min-height: 450px; border-radius: 20px; display: flex; align-items: center; justify-content: center;">
                            <div class="preview-content" id="previewContent" 
                                 style="background: <?= $preview_bg ?>; <?= $preview_bg_image ? 'background-image: url(' . $preview_bg_image . '); background-size: cover; background-position: center;' : '' ?>; width: 100%; max-width: 260px; border-radius: 15px; padding: 20px;">
                        <?php else: ?>
                        <!-- Regular Layout Preview: Full background -->
                        <div class="preview-content" id="previewContent" 
                             style="background: <?= $preview_bg ?>; <?= $preview_bg_image ? 'background-image: url(' . $preview_bg_image . '); background-size: cover; background-position: center;' : '' ?>">
                        <?php endif; ?>
                            
                            <?php
                            $profile_pic_url = '../uploads/profile_pics/' . (($appearance['avatar'] ?? '') ?: 'default-avatar.png');
                            if (!file_exists($profile_pic_url)) {
                                $profile_pic_url = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="90" height="90"%3E%3Ccircle cx="45" cy="45" r="45" fill="%236c757d"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="white" font-size="40" font-family="Arial"%3E👤%3C/text%3E%3C/svg%3E';
                            }
                            ?>
                            <img src="<?= $profile_pic_url ?>" class="preview-avatar" id="previewAvatar" alt="Avatar">
                            
                            <?php
                            $button_radius = '12px';
                            if (($appearance['button_style'] ?? 'rounded') == 'sharp') {
                                $button_radius = '0';
                            } elseif (($appearance['button_style'] ?? '') == 'pill') {
                                $button_radius = '50px';
                            }
                            
                            // Use actual saved colors from v3 schema
                            $has_gradient = ($appearance['bg_type'] ?? 'color') === 'gradient';
                            $btn_bg = $appearance['button_color'] ?? ($has_gradient ? 'rgba(255,255,255,0.2)' : '#f8f9fa');
                            $btn_border = $has_gradient ? 'rgba(255,255,255,0.3)' : '#dee2e6';
                            $btn_color = $appearance['text_color'] ?? ($has_gradient ? '#fff' : '#333');
                            
                            // Text colors from database
                            $preview_text_color = $appearance['text_color'] ?? ($has_gradient ? '#fff' : '#333');
                            $preview_bio_color = $appearance['text_color'] ?? ($has_gradient ? 'rgba(255,255,255,0.8)' : '#666');
                            
                            // Shadow
                            $shadow_map = [
                                'none' => 'none',
                                'light' => '0 2px 8px rgba(0,0,0,0.08)',
                                'medium' => '0 2px 10px rgba(0,0,0,0.15)',
                                'heavy' => '0 4px 15px rgba(0,0,0,0.3)'
                            ];
                            $btn_shadow = $shadow_map[$appearance['shadow_intensity'] ?? 'medium'];
                            
                            // Glass effect
                            $glass_style = '';
                            if ($appearance['enable_glass_effect'] ?? 0) {
                                $glass_style = 'backdrop-filter: blur(20px) saturate(180%); -webkit-backdrop-filter: blur(20px) saturate(180%); background: rgba(255,255,255,0.15) !important; border: 1px solid rgba(255,255,255,0.3);';
                            }
                            ?>
                            
                            <h5 class="fw-bold mb-2" id="previewTitle" style="color: <?= $preview_text_color ?>;">
                                <?= htmlspecialchars($appearance['profile_title'] ?? $current_username) ?>
                            </h5>
                            
                            <p class="mb-4" id="previewBio" style="color: <?= $preview_bio_color ?>; font-size: 0.9rem;">
                                <?= htmlspecialchars($appearance['bio'] ?? 'Your bio will appear here...') ?>
                            </p>
                            
                            <a href="#" class="preview-link" id="previewLink1"
                               style="background: <?= $btn_bg ?>; border: 2px solid <?= $btn_border ?>; color: <?= $btn_color ?>; border-radius: <?= $button_radius ?>; box-shadow: <?= $btn_shadow ?>; <?= $glass_style ?>">
                                <i class="bi bi-link-45deg me-2"></i>
                                <span>Sample Link 1</span>
                            </a>
                            
                            <a href="#" class="preview-link" id="previewLink2"
                               style="background: <?= $btn_bg ?>; border: 2px solid <?= $btn_border ?>; color: <?= $btn_color ?>; border-radius: <?= $button_radius ?>; box-shadow: <?= $btn_shadow ?>; <?= $glass_style ?>">
                                <i class="bi bi-instagram me-2"></i>
                                <span>Sample Link 2</span>
                            </a>
                            
                            <a href="#" class="preview-link" id="previewLink3"
                               style="background: <?= $btn_bg ?>; border: 2px solid <?= $btn_border ?>; color: <?= $btn_color ?>; border-radius: <?= $button_radius ?>; box-shadow: <?= $btn_shadow ?>; <?= $glass_style ?>">
                                <i class="bi bi-github me-2"></i>
                                <span>Sample Link 3</span>
                            </a>
                        </div>
                        <?php if ($is_boxed): ?>
                        </div> <!-- Close previewOuterBox -->
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="../profile.php?slug=<?= $current_page_slug ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Full Page
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
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
                previewContent.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
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
                        <div class="toast show" role="alert">
                            <div class="toast-header bg-success text-white">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong class="me-auto">Copied!</strong>
                                <button type="button" class="btn-close btn-close-white" onclick="this.closest('.position-fixed').remove()"></button>
                            </div>
                            <div class="toast-body">
                                Icon class copied: <code>${iconClass}</code>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                });
            });
        });
        
        // Initialize preview with current settings on page load
        window.addEventListener('DOMContentLoaded', function() {
            // Apply current border setting
            const showBorder = document.getElementById('showProfileBorder');
            const avatar = document.getElementById('previewAvatar');
            if (showBorder && showBorder.checked) {
                avatar.style.border = '4px solid white';
                avatar.style.boxShadow = '0 0 0 2px rgba(0,0,0,0.1)';
            }
            
            // Apply current animation setting
            const enableAnim = document.getElementById('enableAnimations');
            const previewLinks = document.querySelectorAll('.preview-link');
            if (enableAnim && enableAnim.checked) {
                previewLinks.forEach(link => {
                    link.style.transition = 'all 0.3s ease';
                });
            }
            
            // Apply current layout
            const layoutRadios = document.querySelectorAll('input[name="profile_layout"]');
            layoutRadios.forEach(radio => {
                if (radio.checked) {
                    const layout = radio.value;
                    const previewContent = document.getElementById('previewContent');
                    
                    if (layout === 'left') {
                        previewContent.style.textAlign = 'left';
                        previewContent.style.paddingLeft = '30px';
                    } else if (layout === 'minimal') {
                        previewContent.style.textAlign = 'center';
                        previewContent.style.padding = '20px 15px';
                    } else {
                        previewContent.style.textAlign = 'center';
                        previewContent.style.padding = '30px 20px';
                    }
                }
            });
        });
        
        // DEBUG: Log form submission
        const advancedForm = document.getElementById('advancedForm');
        if (advancedForm) {
            advancedForm.addEventListener('submit', function(e) {
                console.log('Form submitting!');
                console.log('Form data:', new FormData(this));
                
                // Log all form elements
                const formData = new FormData(this);
                for (let [key, value] of formData.entries()) {
                    console.log(key + ': ' + value);
                }
                
                // Add timestamp to prevent browser cache
                const timestamp = new Date().getTime();
                this.action = this.action || window.location.href;
                if (this.action.indexOf('?') > -1) {
                    this.action += '&_=' + timestamp;
                } else {
                    this.action += '?_=' + timestamp;
                }
                
                // Don't prevent default - let form submit naturally
            });
        }
        
        // ===== BOXED LAYOUT JAVASCRIPT =====
        
        // Toggle boxed layout settings visibility
        document.getElementById('boxedLayoutEnable')?.addEventListener('change', function() {
            document.getElementById('boxedLayoutSettings').style.display = this.checked ? 'block' : 'none';
            
            // Toggle main live preview between boxed and regular mode
            const previewPhone = document.querySelector('.preview-phone');
            const previewContent = document.getElementById('previewContent');
            
            if (this.checked) {
                // Enable boxed layout preview
                const outerBg = 'linear-gradient(135deg, #667eea, #764ba2)';
                const outerBox = document.createElement('div');
                outerBox.id = 'previewOuterBox';
                outerBox.style.cssText = `background: ${outerBg}; padding: 20px; min-height: 450px; border-radius: 20px; display: flex; align-items: center; justify-content: center;`;
                
                // Wrap preview content
                previewContent.parentNode.insertBefore(outerBox, previewContent);
                outerBox.appendChild(previewContent);
                
                // Adjust inner box styling
                previewContent.style.width = '100%';
                previewContent.style.maxWidth = '260px';
                previewContent.style.borderRadius = '15px';
                previewContent.style.padding = '20px';
            } else {
                // Disable boxed layout preview - unwrap
                const outerBox = document.getElementById('previewOuterBox');
                if (outerBox) {
                    const parent = outerBox.parentNode;
                    parent.insertBefore(previewContent, outerBox);
                    parent.removeChild(outerBox);
                    
                    // Reset inner box styling
                    previewContent.style.width = '';
                    previewContent.style.maxWidth = '';
                    previewContent.style.borderRadius = '20px';
                    previewContent.style.padding = '25px 20px';
                }
            }
        });
        
        // Toggle background type options
        document.getElementById('outerBgType')?.addEventListener('change', function() {
            if (this.value === 'color') {
                document.getElementById('solidColorOption').style.display = 'block';
                document.getElementById('gradientOption').style.display = 'none';
                updateBoxedPreview();
            } else {
                document.getElementById('solidColorOption').style.display = 'none';
                document.getElementById('gradientOption').style.display = 'block';
                updateBoxedPreview();
            }
        });
        
        // Update preview for all boxed layout inputs
        function updateBoxedPreview() {
            const preview = document.getElementById('boxedPreview');
            const bgType = document.getElementById('outerBgType').value;
            
            // Update the boxed layout dedicated preview
            if (bgType === 'gradient') {
                const start = document.getElementById('gradientStart').value;
                const end = document.getElementById('gradientEnd').value;
                preview.style.background = `linear-gradient(135deg, ${start}, ${end})`;
                
                // Also update main live preview outer box if it exists
                const outerBox = document.getElementById('previewOuterBox');
                if (outerBox) {
                    outerBox.style.background = `linear-gradient(135deg, ${start}, ${end})`;
                }
            } else {
                const color = document.getElementById('outerBgColor').value;
                preview.style.background = color;
                
                // Also update main live preview outer box if it exists
                const outerBox = document.getElementById('previewOuterBox');
                if (outerBox) {
                    outerBox.style.background = color;
                }
            }
            
            const containerWidth = document.getElementById('containerWidth').value;
            const containerRadius = document.getElementById('containerRadius').value;
            const containerShadow = document.getElementById('containerShadow').checked;
            
            const container = document.getElementById('boxedPreviewInner');
            // Note: Container background uses main background from Theme tab
            container.style.width = containerWidth + 'px';
            container.style.borderRadius = containerRadius + 'px';
            container.style.boxShadow = containerShadow ? '0 10px 40px rgba(0,0,0,0.2)' : 'none';
            
            // Update main live preview inner box styling if it exists
            const previewContent = document.getElementById('previewContent');
            if (previewContent && document.getElementById('previewOuterBox')) {
                previewContent.style.borderRadius = (containerRadius * 0.5) + 'px'; // Scale down for preview
            }
        }
        
        // Color pickers with hex display for boxed layout
        document.getElementById('outerBgColor')?.addEventListener('input', function() {
            document.getElementById('outerBgColorHex').value = this.value;
            updateBoxedPreview();
        });
        
        document.getElementById('gradientStart')?.addEventListener('input', function() {
            document.getElementById('gradientStartHex').value = this.value;
            updateBoxedPreview();
        });
        
        document.getElementById('gradientEnd')?.addEventListener('input', function() {
            document.getElementById('gradientEndHex').value = this.value;
            updateBoxedPreview();
        });
        
        // Sliders with value display
        document.getElementById('containerWidth')?.addEventListener('input', function() {
            document.getElementById('containerWidthValue').textContent = this.value + 'px';
            updateBoxedPreview();
        });
        
        document.getElementById('containerRadius')?.addEventListener('input', function() {
            document.getElementById('containerRadiusValue').textContent = this.value + 'px';
            updateBoxedPreview();
        });
        
        document.getElementById('containerShadow')?.addEventListener('change', function() {
            updateBoxedPreview();
        });
        
        // Update background type indicator
        function updateBgTypeIndicator() {
            const bgType = document.getElementById('outerBgType').value;
            const indicator = document.getElementById('bgTypeIndicator');
            if (indicator) {
                indicator.innerHTML = `<i class="bi bi-palette-fill"></i> Current: ${bgType === 'gradient' ? 'Gradient' : 'Solid Color'}`;
                indicator.className = bgType === 'gradient' ? 'badge bg-info' : 'badge bg-primary';
            }
        }
        
        // Scroll to alert and switch to tab after page load (if success/error exists)
        window.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['show_advanced_tab'])): ?>
            // Switch to Advanced tab
            const advancedTab = document.querySelector('[href="#advanced-tab"]');
            if (advancedTab) {
                const tab = new bootstrap.Tab(advancedTab);
                tab.show();
            }
            <?php 
                unset($_SESSION['show_advanced_tab']); 
            endif; 
            ?>
            
            <?php if (isset($_SESSION['show_media_tab'])): ?>
            // Switch to Media tab
            const mediaTab = document.querySelector('[href="#media-tab"]');
            if (mediaTab) {
                const tab = new bootstrap.Tab(mediaTab);
                tab.show();
            }
            <?php 
                unset($_SESSION['show_media_tab']); 
            endif; 
            ?>
            
            <?php if (isset($_GET['uploaded']) && !empty($appearance['bg_type']) && $appearance['bg_type'] === 'image' && !empty($appearance['bg_value'])): ?>
            // Update preview background with uploaded image
            const previewContent = document.getElementById('previewContent');
            if (previewContent) {
                const bgImage = '../uploads/backgrounds/<?= $appearance['bg_value'] ?>';
                previewContent.style.backgroundImage = `url(${bgImage})`;
                previewContent.style.backgroundSize = 'cover';
                previewContent.style.backgroundPosition = 'center';
            }
            <?php endif; ?>
            
            <?php if (isset($_SESSION['show_boxed_tab'])): ?>
            // Switch to Boxed Layout tab
            const boxedTab = document.querySelector('[href="#boxed-layout-tab"]');
            if (boxedTab) {
                const tab = new bootstrap.Tab(boxedTab);
                tab.show();
            }
            <?php 
                unset($_SESSION['show_boxed_tab']); 
            endif; 
            ?>
            
            // Scroll to alert
            const alert = document.querySelector('.alert-success, .alert-danger');
            if (alert) {
                setTimeout(() => {
                    alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    alert.style.animation = 'pulse 0.5s ease-in-out 3';
                }, 300);
            }
        });
    </script>
    <style>
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
    </style>
</body>
</html>
<?php
// Close the connection
mysqli_close($conn);
?>


