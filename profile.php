<?php
    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    
    require_once 'config/db.php';
    
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
    if (empty($slug)){
        die('Page not found!');
    }

    // Multi-profile support: Load profile by slug using v3 view
    $query = "SELECT * FROM v_public_profiles WHERE slug = ?";
    $result = execute_query($query, [$slug], 's');

    if (!$result || mysqli_num_rows($result) === 0){
?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>404 - LinkMy</title>
            <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                }
            </style>
        </head>
        <body>
            <div class="text-center">
                <h1 class="display-1 fw-bold">404</h1>
                <p class="fs-3">Halaman tidak ditemukan!</p>
                <a href="index.php" class="btn btn-light btn-lg mt-3">Kembali ke Home</a>
            </div>
        </body>
        </html>
<?php
    exit;
    }

    $user_data = mysqli_fetch_assoc($result);
    // v3 schema mapping: view returns id, slug, name, title, bio, avatar, username, is_verified, bg_type, bg_value, etc.
    $profile_id = $user_data['id']; // v3: profiles.id
    $profile_title = $user_data['title'] ?? $user_data['username'];
    $bio = $user_data['bio'] ?? '';
    $profile_pic = $user_data['avatar'] ?? 'default-avatar.png';
    
    // Gradient presets mapping (v2.0 + v2.1)
    $gradient_presets = [
        // V2.0 Original
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
        // V2.1 New
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

    $bg_type = $user_data['bg_type'] ?? null;
    $bg_value = $user_data['bg_value'] ?? null;
    
    // Handle background images
    $bg_image = null;
    if ($bg_type === 'image' && !empty($bg_value)) {
        $bg_image = $bg_value; // This is the filename
    }
    
    // Fallback logic for missing v3 columns
    if (empty($bg_type)) {
        $theme_name = $user_data['theme_name'] ?? 'light';
        if ($theme_name === 'light') {
            $bg_type = 'color';
            $bg_value = $user_data['custom_bg_color'] ?? '#ffffff';
        } elseif ($theme_name === 'dark') {
            $bg_type = 'color';
            $bg_value = $user_data['custom_bg_color'] ?? '#000000';
        } elseif ($theme_name === 'gradient') {
            $bg_type = 'gradient';
            $preset_name = $user_data['gradient_preset'] ?? 'Purple Dream';
            $bg_value = $gradient_presets[$preset_name] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        } elseif ($theme_name === 'image') {
            $bg_type = 'image';
            $bg_value = $user_data['bg_image_filename'] ?? '';
        } else {
            $bg_type = 'gradient';
            $bg_value = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    }

    $theme_name = ($bg_type === 'color') ? 'light' : 'gradient'; // backward compat
    $button_style = $user_data['button_style'] ?? 'rounded';
    
    // V3 schema: themes table fields
    $custom_button_color = $user_data['button_color'] ?? '#667eea';
    $custom_text_color = $user_data['text_color'] ?? '#333333';
    $font_family = $user_data['font'] ?? 'Inter';
    $profile_layout = $user_data['layout'] ?? 'centered';
    $container_style = $user_data['container_style'] ?? 'wide';
    $enable_animations = $user_data['enable_animations'] ?? 1;
    $enable_glass_effect = $user_data['enable_glass_effect'] ?? 0;
    $shadow_intensity = $user_data['shadow_intensity'] ?? 'medium';
    
    // V3 boxed layout (from theme_boxed table)
    $boxed_layout = $user_data['boxed_enabled'] ?? 0;
    $outer_bg_type = $user_data['outer_bg_type'] ?? 'gradient';
    $outer_bg_value = $user_data['outer_bg_value'] ?? '';
    
    // Parse outer_bg_value to extract gradient colors or solid color
    $outer_bg_gradient_start = '#667eea';
    $outer_bg_gradient_end = '#764ba2';
    $outer_bg_color = '#f0f0f0';
    
    if ($outer_bg_type === 'gradient' && !empty($outer_bg_value)) {
        // Extract colors from gradient string like "linear-gradient(135deg, #667eea 0%, #764ba2 100%)"
        if (preg_match('/#[0-9a-fA-F]{6}/', $outer_bg_value, $matches, PREG_OFFSET_CAPTURE, 0)) {
            $outer_bg_gradient_start = $matches[0][0];
        }
        if (preg_match('/#[0-9a-fA-F]{6}/', $outer_bg_value, $matches, PREG_OFFSET_CAPTURE, strlen($matches[0][0]))) {
            $outer_bg_gradient_end = $matches[0][0];
        }
        // Fallback: try to match all hex colors
        preg_match_all('/#[0-9a-fA-F]{6}/', $outer_bg_value, $all_colors);
        if (isset($all_colors[0][0])) $outer_bg_gradient_start = $all_colors[0][0];
        if (isset($all_colors[0][1])) $outer_bg_gradient_end = $all_colors[0][1];
    } elseif ($outer_bg_type === 'solid' && !empty($outer_bg_value)) {
        // Extract solid color
        if (preg_match('/#[0-9a-fA-F]{6}/', $outer_bg_value, $matches)) {
            $outer_bg_color = $matches[0];
        } else {
            $outer_bg_color = $outer_bg_value;
        }
    }
    $container_bg_color = $user_data['container_bg_color'] ?? '#ffffff';
    $container_max_width = $user_data['container_max_width'] ?? 480;
    $container_border_radius = $user_data['container_radius'] ?? 30;
    $container_shadow = $user_data['container_shadow'] ?? 1;
    $show_profile_border = $user_data['show_profile_border'] ?? 1;
    
    // Parse bg_value for gradient or image (already set above at line 90)
    $gradient_css = ($bg_type === 'gradient') ? $bg_value : '';
    $custom_bg_color = ($bg_type === 'color') ? $bg_value : null;
    $enable_categories = 1; // v3 always supports categories
    $container_border_radius = $user_data['container_border_radius'] ?? 30;
    $container_shadow = $user_data['container_shadow'] ?? 1;

    // Fetch links with correct column names from VPS database structure
    // Column mapping: id (PK), profile_id, title, url, position, icon, is_active, category_id
    
    $categories_exists = false;
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'categories_v3'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        $categories_exists = true;
    }
    
    // Build query with actual column names from database
    if ($categories_exists && $enable_categories) {
        // Query with categories JOIN using v3 schema (categories_v3)
        // Multi-profile: Filter by profile_id instead of user_id
        $links_query = "SELECT l.id as link_id, l.profile_id, l.title, l.url, l.position as order_index, 
                        l.icon, l.clicks as click_count, l.is_active, l.created_at, l.category_id,
                        c.name as category_name, c.icon as category_icon, 
                        c.color as category_color, c.is_expanded as category_expanded
                        FROM links l
                        LEFT JOIN categories_v3 c ON l.category_id = c.id
                        WHERE l.profile_id = ? AND l.is_active = 1
                        ORDER BY l.position ASC, l.id ASC";
    } else {
        // Simple query without categories (v3 schema)
        $links_query = "SELECT l.id as link_id, l.profile_id, l.title, l.url, l.position as order_index, 
                        l.icon, l.clicks as click_count, l.is_active, l.created_at, l.category_id
                        FROM links l
                        WHERE l.profile_id = ? AND l.is_active = 1
                        ORDER BY l.position ASC, l.id ASC";
    }
    
    $stmt_links = mysqli_prepare($conn, $links_query);
    if (!$stmt_links) {
        // If query fails, show error and continue without links
        error_log("Error preparing links query: " . mysqli_error($conn));
        $links_result = false;
    } else {
        mysqli_stmt_bind_param($stmt_links, 'i', $profile_id);
        mysqli_stmt_execute($stmt_links);
        $links_result = mysqli_stmt_get_result($stmt_links);
    }
    
    $links = [];
    $links_by_category = [];
    $categories = [];
    
    if ($links_result) {
        while ($row = mysqli_fetch_assoc($links_result)){
            // Add alias for compatibility with profile rendering code
            // Database v3 uses: link_id, title, icon
            // Code expects: link_id, link_title, icon_class
            $row['link_title'] = $row['title'];
            $row['icon_class'] = $row['icon'] ?? 'bi-link-45deg';
            
            $links[] = $row;
            
            if ($categories_exists && $enable_categories && isset($row['category_id']) && $row['category_id']) {
                $cat_id = $row['category_id'];
                
                // Store category info (only if we have category data)
                if (!isset($categories[$cat_id])) {
                    $categories[$cat_id] = [
                        'category_id' => $cat_id,
                        'category_name' => $row['category_name'] ?? 'Uncategorized',
                        'category_icon' => $row['category_icon'] ?? 'bi-folder',
                        'category_color' => $row['category_color'] ?? '#667eea',
                        'category_expanded' => $row['category_expanded'] ?? 1
                    ];
                }
                
                // Group links under category
                if (!isset($links_by_category[$cat_id])) {
                    $links_by_category[$cat_id] = [];
                }
                $links_by_category[$cat_id][] = $row;
            } else {
                // Uncategorized links (default)
                if (!isset($links_by_category[0])) {
                    $links_by_category[0] = [];
                }
                $links_by_category[0][] = $row;
            }
        }
    }
    
    // Close prepared statement
    if (isset($stmt_links)) {
        mysqli_stmt_close($stmt_links);
    }

    // Determine background based on themes.bg_type and bg_value (v3)
    $background_css = '#ffffff';
    $text_color = $custom_text_color ?? '#333333';
    $is_gradient = false;
    // Note: $bg_image already set at line 90-93, preserve it
    
    // Use bg_type and bg_value directly from DB
    if ($bg_type === 'image' && !empty($bg_value)) {
        // Background image - ensure $bg_image is set
        if (empty($bg_image)) {
            $bg_image = $bg_value; // Make sure it's set
        }
        $background_css = 'linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%)'; // semi-transparent gradient overlay
        $text_color = $custom_text_color ?? '#ffffff';
    } elseif ($bg_type === 'gradient' && !empty($bg_value)) {
        // Gradient background from DB
        $background_css = $bg_value;
        $text_color = $custom_text_color ?? '#ffffff';
        $is_gradient = true;
    } elseif ($bg_type === 'color' && !empty($bg_value)) {
        // Solid color background from DB
        $background_css = $bg_value;
        $text_color = $custom_text_color ?? '#333333';
    } else {
        // Fallback: default gradient
        $background_css = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $text_color = $custom_text_color ?? '#ffffff';
        $is_gradient = true;
    }
    
    // Button/link colors
    $button_bg = $custom_button_color ?? ($is_gradient ? 'rgba(255,255,255,0.2)' : '#ffffff');
    $button_hover = $custom_button_color ?? ($is_gradient ? 'rgba(255,255,255,0.3)' : '#667eea');
    
    // Link text color (NEW v2.1)
    $link_text_color = $custom_link_text_color ?? ($is_gradient ? '#ffffff' : '#333333');
    $link_hover_text_color = '#ffffff'; // Always white on hover for contrast
    
    // Shadow intensity mapping (NEW v2.1)
    $shadow_values = [
        'none' => ['normal' => 'none', 'hover' => 'none'],
        'light' => ['normal' => '0 2px 8px rgba(0,0,0,0.08)', 'hover' => '0 4px 15px rgba(0,0,0,0.12)'],
        'medium' => ['normal' => '0 2px 10px rgba(0,0,0,0.15)', 'hover' => '0 5px 20px rgba(0,0,0,0.25)'],
        'heavy' => ['normal' => '0 4px 15px rgba(0,0,0,0.3)', 'hover' => '0 8px 30px rgba(0,0,0,0.4)']
    ];
    $shadow_normal = $shadow_values[$shadow_intensity]['normal'];
    $shadow_hover = $shadow_values[$shadow_intensity]['hover'];
    
    $current_theme = [
        'bg' => $background_css,
        'text' => $text_color,
        'link_bg' => $button_bg,
        'link_hover' => $button_hover,
        'link_text' => $link_text_color,
        'link_hover_text' => $link_hover_text_color,
        'shadow_normal' => $shadow_normal,
        'shadow_hover' => $shadow_hover,
        'shadow' => 'rgba(0, 0, 0, 0.2)' // For profile pic border
    ];
    $button_classes = [
        'rounded' => 'border-radius: 12px;',
        'sharp' => 'border-radius: 0;',
        'pill' => 'border-radius: 50px;'
    ];

    $current_button_style = $button_classes[$button_style];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profile_title) ?> - LinkMy</title>
    
    <!-- Performance: Preconnect to CDN -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    
    <!-- Critical CSS: Load Bootstrap first -->
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Defer non-critical CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"></noscript>
    
    <link href="assets/css/public.css" rel="stylesheet">
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
    <style>
        body {
            color: <?= $current_theme['text'] ?>;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
            position: relative;
            
            <?php if (!empty($bg_image)): ?>
            /* Background Image Mode */
            background: <?= $background_css ?> url('uploads/backgrounds/<?= $bg_image ?>') no-repeat center center fixed;
            background-size: cover;
            <?php elseif ($boxed_layout): ?>
            /* Boxed mode: Use outer background */
            background: <?= $outer_bg_value ?: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' ?>;
            background-attachment: fixed;
            <?php else: ?>
            /* Non-boxed mode: Use theme background directly */
            background: <?= $background_css ?>;
            <?php if ($is_gradient): ?>
            background-attachment: fixed;
            <?php endif; ?>
            <?php endif; ?>
        }
        
        <?php if (!empty($bg_image)): ?>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: <?= $background_css ?>; /* Use the semi-transparent gradient overlay */
            z-index: 0;
            pointer-events: none;
        }
        
        body > * {
            position: relative;
            z-index: 1;
        }
        <?php endif; ?>
        
        .profile-container {
            <?php if ($boxed_layout): ?>
            /* Boxed Layout: Transparent container, styling applied to boxed-wrapper */
            max-width: 100%;
            background: transparent;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            margin: 0;
            <?php else: ?>
            /* Non-Boxed / Wide Layout: Background transparan, gradient di body */
            max-width: <?= $profile_layout === 'minimal' ? '480px' : '680px' ?>;
            padding: 0 1rem;
            background: transparent;
            <?php endif; ?>
            margin-left: auto;
            margin-right: auto;
            <?= $profile_layout === 'left' ? 'text-align: left;' : '' ?>
        }
        
        /* Mobile responsiveness for boxed */
        @media (max-width: 576px) {
            .profile-container {
                <?php if ($container_style === 'boxed'): ?>
                max-width: 100%;
                margin: 0.5rem;
                padding: 2rem 1.5rem;
                border-radius: 20px;
                <?php endif; ?>
            }
        }
        
        .profile-header {
            text-align: <?= $profile_layout === 'left' ? 'left' : 'center' ?>;
            margin-bottom: 2rem;
            <?php if ($profile_layout === 'left'): ?>
            display: flex;
            align-items: center;
            gap: 1.5rem;
            <?php endif; ?>
        }
        
        .profile-pic {
            width: <?= $profile_layout === 'minimal' ? '80px' : ($profile_layout === 'left' ? '100px' : '120px') ?>;
            height: <?= $profile_layout === 'minimal' ? '80px' : ($profile_layout === 'left' ? '100px' : '120px') ?>;
            border-radius: 50%;
            object-fit: cover;
            border: <?= $show_profile_border ? '5px' : '0' ?> solid <?= $current_theme['link_bg'] ?>;
            box-shadow: 0 5px 20px <?= $current_theme['shadow'] ?>;
            margin-bottom: <?= $profile_layout === 'left' ? '0' : '1rem' ?>;
            <?php if ($profile_layout === 'left'): ?>
            flex-shrink: 0;
            <?php endif; ?>
        }
        
        .profile-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .profile-bio {
            color: <?= $current_theme['text'] ?>;
            opacity: 0.8;
            margin-bottom: 2rem;
        }
        
        .link-card {
            background: <?= $current_theme['link_bg'] ?>;
            padding: <?= $profile_layout === 'minimal' ? '1rem 1.2rem' : '1.2rem 1.5rem' ?>;
            margin-bottom: 1rem;
            <?= $current_button_style ?>
            box-shadow: <?= $current_theme['shadow_normal'] ?>;
            transition: <?= $enable_animations ? 'all 0.3s ease' : 'none' ?>;
            text-decoration: none;
            display: flex;
            align-items: center;
            color: <?= $current_theme['link_text'] ?>;
            border: 2px solid transparent;
            <?php if ($enable_glass_effect): ?>
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            background: <?= $is_gradient ? 'rgba(255,255,255,0.15)' : 'rgba(255,255,255,0.8)' ?> !important;
            border: 1px solid rgba(255,255,255,0.3);
            <?php elseif ($is_gradient): ?>
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            <?php endif; ?>
        }
        
        .link-card:hover {
            <?= $enable_animations ? 'transform: translateY(-3px);' : '' ?>
            box-shadow: <?= $current_theme['shadow_hover'] ?>;
            background: <?= $current_theme['link_hover'] ?> !important;
            color: <?= $current_theme['link_hover_text'] ?> !important;
            border-color: <?= $current_theme['link_hover'] ?>;
        }
        
        .link-card:hover .link-icon,
        .link-card:hover .link-title {
            color: <?= $current_theme['link_hover_text'] ?> !important;
        }
        
        .link-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .link-title {
            font-weight: 600;
            font-size: <?= $profile_layout === 'minimal' ? '1rem' : '1.1rem' ?>;
            margin: 0;
        }
        
        .footer-branding {
            text-align: center;
            margin-top: 3rem;
            opacity: 0.7;
        }
        
        .footer-branding a {
            color: <?= $current_theme['text'] ?>;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-branding a:hover {
            text-decoration: underline;
        }
        /* Boxed Layout Styles */
        <?php if ($boxed_layout): ?>
        body {
            <?php if ($outer_bg_type == 'gradient'): ?>
            background: linear-gradient(135deg, <?= $outer_bg_gradient_start ?> 0%, <?= $outer_bg_gradient_end ?> 100%) !important;
            <?php else: ?>
            background: <?= $outer_bg_color ?> !important;
            <?php endif; ?>
            background-attachment: fixed !important;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 0.5rem;
        }
        .boxed-wrapper {
            /* Inner box background - use theme background */
            background: <?= $background_css ?> !important;
            max-width: <?= $container_max_width ?>px;
            width: 100%;
            border-radius: <?= $container_border_radius ?>px;
            <?php if ($container_shadow): ?>
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            <?php endif; ?>
            padding: 2.5rem 2rem;
            margin: 0 auto;
            position: relative;
        }
        .profile-container {
            max-width: 100% !important;
            padding: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 0 !important;
        }
        /* Adjust buttons for boxed layout */
        .boxed-wrapper .btn-top-action {
            position: absolute !important;
            top: 20px !important;
        }
        .boxed-wrapper .btn-top-action.left {
            left: 20px !important;
        }
        .boxed-wrapper .btn-top-action.right {
            right: 20px !important;
        }
        @media (max-width: 576px) {
            body {
                padding: 1rem 0.5rem;
            }
            .boxed-wrapper {
                padding: 2rem 1.5rem;
                border-radius: <?= max(15, $container_border_radius - 10) ?>px;
            }
        }
        <?php endif; ?>
    </style>
</head>
<body>
    <?php if ($boxed_layout): ?>
    <div class="boxed-wrapper">
        <!-- Top Action Buttons Inside Boxed Wrapper -->
        <div class="btn-top-action left">
            <button class="btn btn-light rounded-circle shadow" style="width: 45px; height: 45px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.9);" data-bs-toggle="modal" data-bs-target="#linkmyModal" title="Create your LinkMy">
                <i class="bi bi-link-45deg" style="font-size: 1.3rem; color: #667eea;"></i>
            </button>
        </div>
        
        <div class="btn-top-action right">
            <button class="btn btn-light rounded-circle shadow" style="width: 45px; height: 45px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.9);" data-bs-toggle="modal" data-bs-target="#shareModal" title="Share Profile">
                <i class="bi bi-share-fill" style="font-size: 1.1rem; color: #667eea;"></i>
            </button>
        </div>
    <?php else: ?>
        <!-- Top Action Buttons Fixed Position -->
        <div style="position: fixed; top: 20px; left: 20px; z-index: 1000;">
            <button class="btn btn-light rounded-circle shadow" style="width: 50px; height: 50px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.9);" data-bs-toggle="modal" data-bs-target="#linkmyModal" title="Create your LinkMy">
                <i class="bi bi-link-45deg" style="font-size: 1.5rem; color: #667eea;"></i>
            </button>
        </div>
        
        <div style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
            <button class="btn btn-light rounded-circle shadow" style="width: 50px; height: 50px; backdrop-filter: blur(10px); background: rgba(255,255,255,0.9);" data-bs-toggle="modal" data-bs-target="#shareModal" title="Share Profile">
                <i class="bi bi-share-fill" style="font-size: 1.2rem; color: #667eea;"></i>
            </button>
        </div>
    <?php endif; ?>
    
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <?php
            $profile_pic_path = 'uploads/profile_pics/' . $profile_pic;
            if (file_exists($profile_pic_path)):
            ?>
                <img src="<?= $profile_pic_path ?>" alt="Profile" class="profile-pic">
            <?php else: ?>
                <div class="bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center profile-pic">
                    <i class="bi bi-person-fill" style="font-size: 3rem;"></i>
                </div>
            <?php endif; ?>
            
            <h1 class="profile-title">
                <?= htmlspecialchars($profile_title) ?>
                <?php if (isset($user_data['is_verified']) && $user_data['is_verified'] == 1): ?>
                <i class="bi bi-patch-check-fill" style="color: #1DA1F2; font-size: 0.8em; vertical-align: middle;" title="Verified Founder"></i>
                <?php endif; ?>
            </h1>
            
            <?php if (!empty($bio)): ?>
                <p class="profile-bio"><?= nl2br(htmlspecialchars($bio)) ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Links List -->
        <div class="links-list">
            <?php if (empty($links)): ?>
                <div class="text-center" style="opacity: 0.6;">
                    <i class="bi bi-inbox display-4"></i>
                    <p class="mt-3">Belum ada link yang ditambahkan</p>
                </div>
            <?php elseif ($enable_categories && !empty($categories)): ?>
                <!-- CATEGORIZED VIEW -->
                <?php foreach ($links_by_category as $cat_id => $cat_links): ?>
                    <?php if ($cat_id > 0): ?>
                        <!-- Category Header -->
                        <?php $cat = $categories[$cat_id]; ?>
                        <div class="category-header" style="border-left: 4px solid <?= $cat['category_color'] ?>; padding-left: 1rem; margin-bottom: 1rem; margin-top: 1.5rem;">
                            <h5 style="margin-bottom: 0.5rem; color: <?= $text_color ?>;">
                                <i class="<?= htmlspecialchars($cat['category_icon']) ?>" style="color: <?= $cat['category_color'] ?>;"></i>
                                <?= htmlspecialchars($cat['category_name']) ?>
                                <span style="opacity: 0.6; font-size: 0.8rem; font-weight: normal;">(<?= count($cat_links) ?>)</span>
                            </h5>
                        </div>
                    <?php endif; ?>
                    
                    <?php foreach ($cat_links as $link): ?>
                        <a href="redirect.php?id=<?= $link['link_id'] ?>" 
                           class="link-card" 
                           target="_blank"
                           rel="noopener noreferrer">
                            <div class="link-icon" style="color: <?= $current_theme['link_text'] ?>;">
                                    <i class="<?= htmlspecialchars($link['icon'] ?? 'bi-link-45deg') ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="link-title" style="color: <?= $current_theme['link_text'] ?>;"><?= htmlspecialchars($link['link_title'] ?? '') ?></p>
                            </div>
                            <div style="color: <?= $current_theme['link_text'] ?>;">
                                <i class="bi bi-arrow-right"></i>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- FLAT VIEW (No categories) -->
                <?php foreach ($links as $link): ?>
                    <a href="redirect.php?id=<?= $link['link_id'] ?>" 
                       class="link-card" 
                       target="_blank"
                       rel="noopener noreferrer">
                        <div class="link-icon" style="color: <?= $current_theme['link_text'] ?>;">
                            <i class="<?= htmlspecialchars($link['icon_class'] ?? 'bi-link-45deg') ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="link-title" style="color: <?= $current_theme['link_text'] ?>;"><?= htmlspecialchars($link['link_title'] ?? '') ?></p>
                        </div>
                        <div style="color: <?= $current_theme['link_text'] ?>;">
                            <i class="bi bi-arrow-right"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Footer Branding -->
        <div class="footer-branding">
            <p class="mb-2">
                <i class="bi bi-link-45deg"></i> 
                Dibuat dengan <a href="index.php">LinkMy</a>
            </p>
            <p class="small mb-0">
                Buat halaman link Anda sendiri secara gratis!
            </p>
        </div>
    </div>
    
    <!-- Share Modal -->
    <div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Share Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <!-- Profile Preview Card -->
                    <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 15px;">
                        <div class="card-body py-4">
                            <?php
                            $profile_pic_path = 'uploads/profile_pics/' . $profile_pic;
                            if (file_exists($profile_pic_path)):
                            ?>
                                <img src="<?= $profile_pic_path ?>" alt="Profile" class="rounded-circle mb-3" style="width: 80px; height: 80px; object-fit: cover; border: 3px solid white;">
                            <?php else: ?>
                                <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-fill" style="font-size: 2.5rem;"></i>
                                </div>
                            <?php endif; ?>
                            <h5 class="text-white mb-1"><?= htmlspecialchars($profile_title) ?></h5>
                            <p class="text-white-50 small mb-0">🌐 linkmy.iet.ovh/<?= htmlspecialchars($slug) ?></p>
                        </div>
                    </div>
                    
                    <!-- Share Options -->
                    <div class="d-flex justify-content-center gap-3 mb-3 flex-wrap">
                        <a href="javascript:void(0)" onclick="copyToClipboard()" class="btn btn-outline-secondary rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;" title="Copy Link">
                            <i class="bi bi-clipboard" style="font-size: 1.5rem;"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://linkmy.iet.ovh/profile.php?slug=' . $slug) ?>&text=Check out my LinkMy profile!" target="_blank" class="btn rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #000; color: white;" title="Share on X">
                            <i class="bi bi-twitter-x" style="font-size: 1.5rem;"></i>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://linkmy.iet.ovh/profile.php?slug=' . $slug) ?>" target="_blank" class="btn rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #1877f2; color: white;" title="Share on Facebook">
                            <i class="bi bi-facebook" style="font-size: 1.5rem;"></i>
                        </a>
                        <a href="https://wa.me/?text=Check out my LinkMy profile: <?= urlencode('https://linkmy.iet.ovh/profile.php?slug=' . $slug) ?>" target="_blank" class="btn rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #25d366; color: white;" title="Share on WhatsApp">
                            <i class="bi bi-whatsapp" style="font-size: 1.5rem;"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('https://linkmy.iet.ovh/profile.php?slug=' . $slug) ?>" target="_blank" class="btn rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #0a66c2; color: white;" title="Share on LinkedIn">
                            <i class="bi bi-linkedin" style="font-size: 1.5rem;"></i>
                        </a>
                        <a href="https://telegram.me/share/url?url=<?= urlencode('https://linkmy.iet.ovh/profile.php?slug=' . $slug) ?>&text=Check out my LinkMy profile!" target="_blank" class="btn rounded-circle" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; background: #0088cc; color: white;" title="Share on Telegram">
                            <i class="bi bi-telegram" style="font-size: 1.5rem;"></i>
                        </a>
                    </div>
                    
                    <!-- Copy Link Input -->
                    <div class="input-group mt-3">
                        <input type="text" class="form-control" id="profileLink" value="https://linkmy.iet.ovh/profile.php?slug=<?= htmlspecialchars($slug) ?>" readonly style="border-radius: 10px 0 0 10px;">
                        <button class="btn btn-primary" type="button" onclick="copyToClipboard()" style="border-radius: 0 10px 10px 0;">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                    <div id="copyFeedback" class="text-success small mt-2" style="display: none;">
                        ✅ Link copied to clipboard!
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- LinkMy Signup Modal -->
    <div class="modal fade" id="linkmyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-body text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="bi bi-link-45deg text-white" style="font-size: 3rem;"></i>
                        </div>
                        <h3 class="fw-bold mb-2">Create Your LinkMy</h3>
                        <p class="text-muted mb-4">Join thousands of creators managing all their links in one place</p>
                    </div>
                    
                    <div class="row text-start mb-4">
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Free Forever</h6>
                                    <p class="text-muted small mb-0">No credit card required</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-speedometer2 text-primary" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Track Analytics</h6>
                                    <p class="text-muted small mb-0">See who clicks your links</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <i class="bi bi-palette-fill text-warning" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 fw-semibold">Custom Design</h6>
                                    <p class="text-muted small mb-0">Match your brand style</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="register.php" class="btn btn-lg w-100 text-white fw-semibold mb-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 12px; padding: 15px;">
                        <i class="bi bi-rocket-takeoff me-2"></i>Create Free Account
                    </a>
                    <p class="text-muted small mb-0">Already have an account? <a href="landing.php" class="fw-semibold">Login</a></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS (Required for Modals) - Deferred for performance -->
    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js" defer></script>
    
    <script defer>
        function copyToClipboard() {
            const input = document.getElementById('profileLink');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile
            
            navigator.clipboard.writeText(input.value).then(() => {
                const feedback = document.getElementById('copyFeedback');
                feedback.style.display = 'block';
                setTimeout(() => {
                    feedback.style.display = 'none';
                }, 3000);
            });
        }
    </script>
    
    <?php if ($boxed_layout): ?>
    </div> <!-- Close boxed-wrapper -->
    <?php endif; ?>
</body>
</html>