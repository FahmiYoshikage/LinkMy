<?php
// Demo page - showcase LinkMy features
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo - LinkMy</title>
    <link href="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .demo-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .profile-header {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            margin-bottom: 1rem;
        }
        .profile-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        .profile-bio {
            color: #718096;
            font-size: 16px;
            margin-bottom: 0;
        }
        .demo-link {
            display: block;
            background: white;
            padding: 1.25rem;
            border-radius: 15px;
            margin-bottom: 1rem;
            text-decoration: none;
            color: #2d3748;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .demo-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .demo-link i {
            font-size: 24px;
            margin-right: 15px;
        }
        .demo-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .back-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: white;
            color: #667eea;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.3s;
            z-index: 1000;
        }
        .back-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.2);
        }
        .demo-footer {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            margin-top: 2rem;
            backdrop-filter: blur(10px);
        }
        .demo-footer h5 {
            color: white;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .demo-footer .btn {
            background: white;
            color: #667eea;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            border: none;
            transition: all 0.3s;
        }
        .demo-footer .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }
        
        @media (max-width: 768px) {
            .profile-header {
                padding: 1.5rem;
            }
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            .profile-title {
                font-size: 24px;
            }
            .demo-link {
                padding: 1rem;
            }
            .back-btn {
                top: 10px;
                left: 10px;
                padding: 0.5rem 1rem;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <a href="landing.php" class="back-btn">
        <i class="bi bi-arrow-left me-2"></i>Kembali
    </a>

    <div class="demo-container">
        <div class="profile-header">
            <div class="demo-badge">
                <i class="bi bi-stars me-1"></i>DEMO ACCOUNT
            </div>
            <img src="https://ui-avatars.com/api/?name=LinkMy+Demo&size=120&background=667eea&color=fff&bold=true" alt="Demo Avatar" class="profile-avatar">
            <h1 class="profile-title">LinkMy Demo</h1>
            <p class="profile-bio">Contoh halaman profil LinkMy dengan berbagai fitur menarik 🚀</p>
        </div>

        <div class="links-container">
            <a href="https://www.instagram.com/linkmy.official" class="demo-link" target="_blank">
                <i class="bi bi-instagram text-danger"></i>
                Instagram Official
            </a>
            
            <a href="https://www.youtube.com/@linkmy" class="demo-link" target="_blank">
                <i class="bi bi-youtube text-danger"></i>
                YouTube Channel
            </a>
            
            <a href="https://github.com/FahmiYoshikage/LinkMy" class="demo-link" target="_blank">
                <i class="bi bi-github text-dark"></i>
                GitHub Repository
            </a>
            
            <a href="https://www.linkedin.com/company/linkmy" class="demo-link" target="_blank">
                <i class="bi bi-linkedin text-primary"></i>
                LinkedIn Company
            </a>
            
            <a href="https://twitter.com/linkmy_official" class="demo-link" target="_blank">
                <i class="bi bi-twitter text-info"></i>
                Twitter / X
            </a>
            
            <a href="https://www.tiktok.com/@linkmy.official" class="demo-link" target="_blank">
                <i class="bi bi-tiktok text-dark"></i>
                TikTok Official
            </a>
            
            <a href="https://discord.gg/linkmy" class="demo-link" target="_blank">
                <i class="bi bi-discord text-primary"></i>
                Discord Community
            </a>
            
            <a href="mailto:support@linkmy.iet.ovh" class="demo-link">
                <i class="bi bi-envelope-fill text-secondary"></i>
                Email Support
            </a>
            
            <a href="https://linkmy.iet.ovh" class="demo-link" target="_blank">
                <i class="bi bi-globe text-success"></i>
                Official Website
            </a>
        </div>

        <div class="demo-footer">
            <h5>
                <i class="bi bi-heart-fill text-danger me-2"></i>
                Suka dengan demo ini?
            </h5>
            <p class="text-white mb-3">Buat halaman link-in-bio Anda sendiri sekarang!</p>
            <a href="register.php" class="btn">
                <i class="bi bi-rocket-takeoff me-2"></i>Daftar Gratis
            </a>
        </div>
    </div>

    <script src="assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
