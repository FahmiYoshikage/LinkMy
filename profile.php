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

    $query = "SELECT * FROM v_public_page_data WHERE page_slug = ?";
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
    $user_id = $user_data['user_id'];
    $profile_title = $user_data['profile_title'] ?? $user_data['username'];
    $bio = $user_data['bio'] ?? '';
    $profile_pic = $user_data['profile_pic_filename'] ?? 'default-avatar.png';
    $bg_image = $user_data['bg_image_filename'] ?? '';
    $theme_name = $user_data['theme_name'] ?? 'light';
    $button_style = $user_data['button_style'] ?? 'rounded';
    
    // V2.0 Advanced Customization
    $gradient_preset = $user_data['gradient_preset'] ?? null;
    $custom_bg_color = $user_data['custom_bg_color'] ?? null;
    $custom_button_color = $user_data['custom_button_color'] ?? null;
    $custom_text_color = $user_data['custom_text_color'] ?? null;
    $custom_link_text_color = $user_data['custom_link_text_color'] ?? null;
    $profile_layout = $user_data['profile_layout'] ?? 'centered';
    $show_profile_border = $user_data['show_profile_border'] ?? 1;
    $enable_animations = $user_data['enable_animations'] ?? 1;
    
    // V2.1 New Features
    $enable_glass_effect = $user_data['enable_glass_effect'] ?? 0;
    $shadow_intensity = $user_data['shadow_intensity'] ?? 'medium';
    
    // V2.2 Linktree Features
    $container_style = $user_data['container_style'] ?? 'wide';
    $enable_categories = $user_data['enable_categories'] ?? 0;
    
    // V2.3 Boxed Layout Features
    $boxed_layout = $user_data['boxed_layout'] ?? 0;
    $outer_bg_type = $user_data['outer_bg_type'] ?? 'gradient';
    $outer_bg_color = $user_data['outer_bg_color'] ?? '#667eea';
    $outer_bg_gradient_start = $user_data['outer_bg_gradient_start'] ?? '#667eea';
    $outer_bg_gradient_end = $user_data['outer_bg_gradient_end'] ?? '#764ba2';
    $container_bg_color = $user_data['container_bg_color'] ?? '#ffffff';
    $container_max_width = $user_data['container_max_width'] ?? 480;
    $container_border_radius = $user_data['container_border_radius'] ?? 30;
    $container_shadow = $user_data['container_shadow'] ?? 1;
    
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

    // Fetch links separately using mysqli_prepare
    // Check if categories table exists first
    $categories_exists = false;
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'categories'");
    if ($check_table && mysqli_num_rows($check_table) > 0) {
        $categories_exists = true;
    }
    
    // Build query based on whether categories table exists
    if ($categories_exists && $enable_categories) {
        $links_query = "SELECT l.*, c.name as category_name, c.icon as category_icon, 
                        c.color as category_color, c.is_expanded as category_expanded
                        FROM links l
                        LEFT JOIN categories c ON l.category_id = c.id
                        WHERE l.user_id = ? AND l.is_visible = 1
                        ORDER BY l.display_order ASC";
    } else {
        // Simple query without categories - select all columns
        $links_query = "SELECT *
                        FROM links
                        WHERE user_id = ? AND is_visible = 1
                        ORDER BY display_order ASC";
    }
    
    $stmt_links = mysqli_prepare($conn, $links_query);
    if (!$stmt_links) {
        // If query fails, show error and continue without links
        error_log("Error preparing links query: " . mysqli_error($conn));
        $links_result = false;
    } else {
        mysqli_stmt_bind_param($stmt_links, 'i', $user_id);
        mysqli_stmt_execute($stmt_links);
        $links_result = mysqli_stmt_get_result($stmt_links);
    }
    
    $links = [];
    $links_by_category = [];
    $categories = [];
    
    if ($links_result) {
        while ($row = mysqli_fetch_assoc($links_result)){
            // Normalize column names for compatibility
            // Handle different possible primary key names
            if (!isset($row['link_id'])) {
                if (isset($row['id'])) {
                    $row['link_id'] = $row['id'];
                } elseif (isset($row['link_unique_id'])) {
                    $row['link_id'] = $row['link_unique_id'];
                }
            }
            
            // Normalize other column names
            if (!isset($row['link_title']) && isset($row['title'])) {
                $row['link_title'] = $row['title'];
            }
            if (!isset($row['icon_class']) && isset($row['icon'])) {
                $row['icon_class'] = $row['icon'];
            }
            
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

    // Determine background based on priority: gradient_preset > custom_bg_color > theme_name
    $background_css = '#ffffff';
    $text_color = $custom_text_color ?? '#333333';
    $is_gradient = false;
    
    // Priority 1: Gradient preset (highest priority - v2.0 feature)
    if (!empty($gradient_preset) && isset($gradient_presets[$gradient_preset])) {
        // Use gradient preset - overrides everything
        $background_css = $gradient_presets[$gradient_preset];
        $text_color = $custom_text_color ?? '#ffffff';
        $is_gradient = true;
    }
    // Priority 2: Custom background color (only if NO gradient preset)
    elseif (empty($gradient_preset) && !empty($custom_bg_color)) {
        // Use custom solid color
        $background_css = $custom_bg_color;
        $text_color = $custom_text_color ?? '#333333';
    }
    // Priority 3: Theme name fallback
    elseif ($theme_name === 'gradient') {
        // Use default gradient theme
        $background_css = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        $text_color = $custom_text_color ?? '#ffffff';
        $is_gradient = true;
    } elseif ($theme_name === 'dark') {
        $background_css = '#1a1a1a';
        $text_color = $custom_text_color ?? '#ffffff';
    } else {
        // Light theme (default)
        $background_css = '#ffffff';
        $text_color = $custom_text_color ?? '#333333';
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
        'shadow_hover' => $shadow_hover
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
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="assets/css/public.css" rel="stylesheet">
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
    <style>
        body {
            background: <?= $current_theme['bg'] ?>;
            color: <?= $current_theme['text'] ?>;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 2rem 0;
            <?php if (!empty($bg_image)): ?>
            background-image: url('uploads/backgrounds/<?= $bg_image ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            <?php else: ?>
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
            background: <?= $theme_name === 'light' ? 'rgba(255,255,255,0.85)' : 'rgba(0,0,0,0.7)' ?>;
            z-index: -1;
        }
        <?php endif; ?>
        
        .profile-container {
            <?php if ($container_style === 'boxed'): ?>
            /* Linktree-style: Small centered box on desktop */
            max-width: 480px;
            background: <?= $theme_name === 'light' ? 'rgba(255,255,255,0.98)' : 'rgba(30,30,30,0.98)' ?>;
            border-radius: 25px;
            padding: 2.5rem 2rem;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2);
            margin: 2rem auto;
            <?php else: ?>
            /* Wide layout: Full width container */
            max-width: <?= $profile_layout === 'minimal' ? '480px' : '680px' ?>;
            padding: 0 1rem;
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
            background: linear-gradient(135deg, <?= $outer_bg_gradient_start ?>, <?= $outer_bg_gradient_end ?>) !important;
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
            background: <?= $container_bg_color ?>;
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
            
            <h1 class="profile-title"><?= htmlspecialchars($profile_title) ?></h1>
            
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
                                <i class="<?= htmlspecialchars($link['icon_class']) ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <p class="link-title" style="color: <?= $current_theme['link_text'] ?>;"><?= htmlspecialchars($link['link_title']) ?></p>
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
                            <i class="<?= htmlspecialchars($link['icon_class']) ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="link-title" style="color: <?= $current_theme['link_text'] ?>;"><?= htmlspecialchars($link['link_title']) ?></p>
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
    
    <!-- Bootstrap JS (Required for Modals) -->
    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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