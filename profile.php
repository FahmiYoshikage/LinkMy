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

    mysqli_data_seek($result, 0);

    $links = [];
    while ($row = mysqli_fetch_assoc($result)){
        if (!empty($row['link_id'])){
            $links[] = $row;
        }
    }

    $theme_styles = [
        'light' => [
            'bg' => '#ffffff',
            'text' => '#333333',
            'card' => '#f8f9fa',
            'link_bg' => '#ffffff',
            'link_hover' => '#667eea',
            'shadow' => 'rgba(0,0,0,0.1)'
        ],
        'dark' => [
            'bg' => '#1a1a1a',
            'text' => '#ffffff',
            'card' => '#2d2d2d',
            'link_bg' => '#2d2d2d',
            'link_hover' => '#667eea',
            'shadow' => 'rgba(0,0,0,0.3)'
        ],
        'gradient' => [
            'bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
            'text' => '#ffffff',
            'card' => 'rgba(255,255,255,0.1)',
            'link_bg' => 'rgba(255,255,255,0.2)',
            'link_hover' => 'rgba(255,255,255,0.3)',
            'shadow' => 'rgba(0,0,0,0.2)'
        ]
    ];

    $current_theme = $theme_styles[$theme_name];
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
            max-width: 680px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid <?= $current_theme['link_bg'] ?>;
            box-shadow: 0 5px 20px <?= $current_theme['shadow'] ?>;
            margin-bottom: 1rem;
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
            padding: 1.2rem 1.5rem;
            margin-bottom: 1rem;
            <?= $current_button_style ?>
            box-shadow: 0 2px 10px <?= $current_theme['shadow'] ?>;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            color: <?= $current_theme['text'] ?>;
            border: 2px solid transparent;
        }
        
        .link-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px <?= $current_theme['shadow'] ?>;
            background: <?= $current_theme['link_hover'] ?>;
            color: white;
            border-color: <?= $current_theme['link_hover'] ?>;
        }
        
        .link-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .link-title {
            font-weight: 600;
            font-size: 1.1rem;
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
                        <div class="link-icon">
                            <i class="<?= htmlspecialchars($link['icon_class']) ?>"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="link-title"><?= htmlspecialchars($link['link_title']) ?></p>
                        </div>
                        <div>
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