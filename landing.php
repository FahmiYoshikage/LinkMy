<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkMy - Your Personal Link Hub</title>
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
    <link href="/assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow-x: hidden;
        }
        
        .hero-section {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg width="60" height="60" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100%" height="100%" fill="url(%23grid)"/></svg>');
            animation: moveGrid 20s linear infinite;
        }
        
        @keyframes moveGrid {
            0% { transform: translate(0, 0); }
            100% { transform: translate(60px, 60px); }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 20px rgba(0,0,0,0.2);
            margin-bottom: 1.5rem;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .hero-subtitle {
            font-size: 1.5rem;
            color: rgba(255,255,255,0.95);
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }
        
        .hero-description {
            font-size: 1.1rem;
            color: rgba(255,255,255,0.85);
            margin-bottom: 3rem;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-hero {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            border: none;
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }
        
        .btn-primary-custom {
            background: white;
            color: #667eea;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            color: #764ba2;
        }
        
        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        .feature-section {
            padding: 6rem 0;
            background: linear-gradient(to bottom, #f8f9fa, white);
        }
        
        .feature-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            height: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        
        .feature-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #2d3748;
        }
        
        .feature-text {
            color: #718096;
            line-height: 1.7;
        }
        
        .stats-section {
            background: var(--primary-gradient);
            padding: 4rem 0;
            color: white;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .mockup-container {
            position: relative;
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .mockup-phone {
            background: white;
            border-radius: 40px;
            padding: 15px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
            border: 8px solid #1a1a1a;
        }
        
        .mockup-screen {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 2rem;
            min-height: 500px;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 6rem 0;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
        }
        
        .footer {
            background: #1a202c;
            color: white;
            padding: 3rem 0 1.5rem;
        }
        
        .footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer a:hover {
            color: white;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark position-absolute w-100" style="z-index: 100;">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4" href="#">
                <i class="bi bi-link-45deg"></i> LinkMy
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">Cara Kerja</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-light btn-sm rounded-pill px-4" href="register.php">
                            <i class="bi bi-person-plus"></i> Daftar Gratis
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="hero-title">
                        Satu Link.<br>
                        Semua Profil Anda.
                    </h1>
                    <p class="hero-subtitle">
                        Platform link-in-bio yang powerful untuk menghubungkan audiensmu ke semua kontenmu
                    </p>
                    <p class="hero-description">
                        LinkMy membantu Anda mengumpulkan semua link penting - media sosial, portfolio, toko online, dan lainnya - dalam satu halaman yang cantik dan mudah diakses. Sempurna untuk kreator, influencer, bisnis, dan siapa saja yang ingin tampil lebih profesional online.
                    </p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="register.php" class="btn btn-primary-custom btn-hero">
                            <i class="bi bi-rocket-takeoff me-2"></i>Mulai Gratis
                        </a>
                        <a href="#features" class="btn btn-outline-custom btn-hero">
                            <i class="bi bi-play-circle me-2"></i>Lihat Demo
                        </a>
                    </div>
                    <div class="mt-4 text-white-50">
                        <small><i class="bi bi-check-circle-fill text-white me-1"></i> Gratis selamanya</small>
                        <small class="ms-3"><i class="bi bi-check-circle-fill text-white me-1"></i> Tanpa iklan</small>
                        <small class="ms-3"><i class="bi bi-check-circle-fill text-white me-1"></i> Unlimited links</small>
                    </div>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <div class="mockup-container text-center">
                        <div class="mockup-phone d-inline-block">
                            <div class="mockup-screen">
                                <div class="text-center">
                                    <div class="bg-white rounded-circle d-inline-block p-1 mb-3">
                                        <img src="uploads/profiles/default-avatar.png" 
                                             onerror="this.src='https://ui-avatars.com/api/?name=You&background=667eea&color=fff&size=100'"
                                             class="rounded-circle" width="80" height="80" alt="Profile">
                                    </div>
                                    <h4 class="text-white fw-bold">@username</h4>
                                    <p class="text-white-50 small">Kreator • Entrepreneur • Dreamer</p>
                                    <div class="d-grid gap-2 mt-4">
                                        <button class="btn btn-light btn-lg rounded-pill">
                                            <i class="bi bi-instagram"></i> Instagram
                                        </button>
                                        <button class="btn btn-light btn-lg rounded-pill">
                                            <i class="bi bi-youtube"></i> YouTube
                                        </button>
                                        <button class="btn btn-light btn-lg rounded-pill">
                                            <i class="bi bi-tiktok"></i> TikTok
                                        </button>
                                        <button class="btn btn-light btn-lg rounded-pill">
                                            <i class="bi bi-shop"></i> Toko Online
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="feature-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">Fitur Unggulan</h2>
                <p class="lead text-muted">Semua yang Anda butuhkan untuk membuat profil link yang sempurna</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-palette"></i>
                        </div>
                        <h3 class="feature-title">Kustomisasi Penuh</h3>
                        <p class="feature-text">
                            12 gradient preset, custom color picker, berbagai layout, font, dan style button. 
                            Buat halaman yang benar-benar mencerminkan brand Anda.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <h3 class="feature-title">Responsive Design</h3>
                        <p class="feature-text">
                            Tampil sempurna di semua perangkat - smartphone, tablet, atau desktop. 
                            Pengalaman user yang mulus di mana saja.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h3 class="feature-title">Analytics & Tracking</h3>
                        <p class="feature-text">
                            Lacak berapa kali link Anda diklik. Pahami performa konten Anda 
                            dengan data yang jelas dan actionable.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-lightning-charge"></i>
                        </div>
                        <h3 class="feature-title">Super Cepat</h3>
                        <p class="feature-text">
                            Dibangun dengan teknologi modern untuk performa maksimal. 
                            Halaman Anda load dalam sekejap mata.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Aman & Terpercaya</h3>
                        <p class="feature-text">
                            Keamanan data Anda adalah prioritas. Enkripsi password, 
                            email verification, dan forgot password yang aman.
                        </p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-infinity"></i>
                        </div>
                        <h3 class="feature-title">Unlimited Everything</h3>
                        <p class="feature-text">
                            Tambahkan link sebanyak yang Anda mau. Tidak ada batasan 
                            untuk kreativitas Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">Cara Kerja</h2>
                <p class="lead text-muted">Mulai dalam 3 langkah mudah</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; background: var(--primary-gradient);">
                            <span class="text-white fw-bold fs-2">1</span>
                        </div>
                        <h4 class="fw-bold mb-3">Daftar Gratis</h4>
                        <p class="text-muted">
                            Buat akun dalam hitungan detik. Hanya perlu email dan username pilihan Anda.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; background: var(--primary-gradient);">
                            <span class="text-white fw-bold fs-2">2</span>
                        </div>
                        <h4 class="fw-bold mb-3">Kustomisasi</h4>
                        <p class="text-muted">
                            Tambahkan links, pilih warna, upload foto profil, dan sesuaikan tampilan sesuai selera.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                             style="width: 80px; height: 80px; background: var(--primary-gradient);">
                            <span class="text-white fw-bold fs-2">3</span>
                        </div>
                        <h4 class="fw-bold mb-3">Share & Grow</h4>
                        <p class="text-muted">
                            Bagikan link profil Anda di bio Instagram, TikTok, atau media sosial lainnya!
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="stat-number">100%</div>
                    <div class="stat-label">Gratis Selamanya</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">∞</div>
                    <div class="stat-label">Unlimited Links</div>
                </div>
                <div class="col-md-4">
                    <div class="stat-number">12+</div>
                    <div class="stat-label">Gradient Presets</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center position-relative">
            <h2 class="display-4 fw-bold mb-4">Siap Untuk Memulai?</h2>
            <p class="lead mb-4">Bergabunglah dengan ribuan kreator yang sudah menggunakan LinkMy</p>
            <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5">
                <i class="bi bi-rocket-takeoff me-2"></i>Buat Akun Gratis
            </a>
            <p class="mt-3 mb-0"><small>Tidak perlu kartu kredit • Setup dalam 2 menit</small></p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-link-45deg"></i> LinkMy
                    </h5>
                    <p class="text-white-50">
                        Platform link-in-bio yang powerful untuk menghubungkan semua profil online Anda dalam satu tempat.
                    </p>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#features">Fitur</a></li>
                        <li class="mb-2"><a href="#how-it-works">Cara Kerja</a></li>
                        <li class="mb-2"><a href="login.php">Login</a></li>
                        <li class="mb-2"><a href="register.php">Daftar</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="fw-bold mb-3">Connect</h6>
                    <div class="d-flex gap-2">
                        <a href="https://www.instagram.com/fahmi.ilham06/" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://x.com/FahmiVoldigoad" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="https://www.facebook.com/Fahmi1lham" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://github.com/FahmiYoshikage" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-github"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/fahmi-ilham-bagaskara-65a197305/" class="btn btn-outline-light btn-sm rounded-circle">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="text-center text-white-50">
                <small>&copy; 2025 LinkMy. Made with <i class="bi bi-heart-fill text-danger"></i> for all creators everywhere.</small>
            </div>
        </div>
    </footer>

    <script src="/assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
