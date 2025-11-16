<?php
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

    mysqli_data_seek($result, 0);

    $links = [];
    while ($row = mysqli_fetch_assoc($result)){
        if (!empty($row['link_id'])){
            $links[] = $row;
        }
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
            max-width: <?= $profile_layout === 'minimal' ? '480px' : '680px' ?>;
            margin: 0 auto;
            padding: 0 1rem;
            <?= $profile_layout === 'left' ? 'text-align: left;' : '' ?>
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
    </style>
</head>
<body>
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
            <?php else: ?>
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
</body>
</html>