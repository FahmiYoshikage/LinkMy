<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>Tentang Fahmi Yoshikage - Founder LinkMy Indonesia</title>
    <meta name="title" content="Tentang Fahmi Yoshikage - Founder LinkMy Indonesia">
    <meta name="description" content="Profil Fahmi Yoshikage, founder dan developer LinkMy - platform bio link manager gratis Indonesia. Mahasiswa informatika yang passionate dalam web development dan open source.">
    <meta name="keywords" content="Fahmi Yoshikage, LinkMy Fahmi, founder LinkMy, developer Indonesia, web developer, bio link creator, Fahmi LinkMy Indonesia">
    <meta name="author" content="Fahmi Yoshikage">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
    <meta property="og:url" content="https://linkmy.iet.ovh/fahmi.php">
    <meta property="og:title" content="Tentang Fahmi Yoshikage - Founder LinkMy">
    <meta property="og:description" content="Profil founder dan developer LinkMy, platform bio link manager gratis Indonesia.">
    <meta property="og:image" content="https://linkmy.iet.ovh/assets/images/fahmi-profile.jpg">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:url" content="https://linkmy.iet.ovh/fahmi.php">
    <meta property="twitter:title" content="Fahmi Yoshikage - Founder LinkMy">
    <meta property="twitter:description" content="Developer LinkMy - Bio Link Manager Indonesia">
    <meta property="twitter:creator" content="@FahmiYoshikage">
    
    <!-- Schema.org Person Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Person",
      "name": "Fahmi Yoshikage",
      "givenName": "Fahmi",
      "familyName": "Yoshikage",
      "email": "fahmiilham029@gmail.com",
      "jobTitle": "Web Developer & Founder",
      "worksFor": {
        "@type": "Organization",
        "name": "LinkMy by Fahmi"
      },
      "url": "https://linkmy.iet.ovh/fahmi.php",
      "sameAs": [
        "https://github.com/FahmiYoshikage",
        "https://linkmy.iet.ovh"
      ],
      "knowsAbout": [
        "Web Development",
        "PHP Programming",
        "MySQL Database",
        "Bio Link Management",
        "SEO Optimization"
      ],
      "description": "Founder dan developer LinkMy, platform bio link manager gratis Indonesia. Mahasiswa informatika yang passionate dalam web development, database design, dan open source projects."
    }
    </script>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--primary-gradient);
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: white;
            font-weight: bold;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }
        
        .profile-title {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 20px;
        }
        
        .social-links {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .social-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
        }
        
        .bio-text {
            line-height: 1.8;
            color: #555;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .skill-tag {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            margin: 5px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .project-highlight {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        
        .project-highlight h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 30px;
            background: var(--primary-gradient);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 30px;
        }
        
        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 30px 20px;
            }
            
            .profile-name {
                font-size: 2rem;
            }
            
            .profile-title {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar">
                FY
            </div>
            <h1 class="profile-name">Fahmi Yoshikage</h1>
            <p class="profile-title">Founder & Developer LinkMy Indonesia</p>
            
            <div class="social-links">
                <a href="https://github.com/FahmiYoshikage" class="social-link" target="_blank" rel="noopener" title="GitHub">
                    <i class="bi bi-github"></i>
                </a>
                <a href="mailto:fahmiilham029@gmail.com" class="social-link" title="Email">
                    <i class="bi bi-envelope"></i>
                </a>
                <a href="https://linkmy.iet.ovh" class="social-link" title="LinkMy">
                    <i class="bi bi-link-45deg"></i>
                </a>
            </div>
        </div>
        
        <section>
            <h2 class="section-title"><i class="bi bi-person-circle"></i> Tentang Saya</h2>
            <p class="bio-text">
                Halo! Saya Fahmi Yoshikage, seorang mahasiswa informatika yang passionate dalam dunia web development 
                dan teknologi. Saya adalah founder dan developer dari <strong>LinkMy</strong>, platform bio link manager 
                gratis yang saya kembangkan untuk membantu content creator, UMKM, dan profesional Indonesia mengelola 
                semua link sosial media mereka dalam satu halaman yang cantik dan mudah dibagikan.
            </p>
            <p class="bio-text">
                LinkMy lahir dari kebutuhan pribadi saya untuk memiliki alternatif Linktree yang gratis, powerful, 
                dan dapat dikustomisasi sepenuhnya. Dengan fitur-fitur seperti analytics real-time dengan geolocation, 
                verified badge, drag & drop reordering, dan boxed layout yang modern, LinkMy dirancang untuk memberikan 
                pengalaman terbaik bagi pengguna Indonesia.
            </p>
        </section>
        
        <section>
            <h2 class="section-title"><i class="bi bi-code-slash"></i> Tech Stack & Skills</h2>
            <div>
                <span class="skill-tag">PHP</span>
                <span class="skill-tag">MySQL</span>
                <span class="skill-tag">JavaScript</span>
                <span class="skill-tag">Bootstrap 5</span>
                <span class="skill-tag">HTML5 & CSS3</span>
                <span class="skill-tag">AJAX</span>
                <span class="skill-tag">Docker</span>
                <span class="skill-tag">Git & GitHub</span>
                <span class="skill-tag">RESTful API</span>
                <span class="skill-tag">SEO Optimization</span>
                <span class="skill-tag">Responsive Design</span>
                <span class="skill-tag">Database Design</span>
            </div>
        </section>
        
        <section>
            <h2 class="section-title"><i class="bi bi-trophy"></i> LinkMy Project Highlights</h2>
            
            <div class="stats-container">
                <div class="stat-card">
                    <span class="stat-number">22+</span>
                    <span class="stat-label">Features</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Free Forever</span>
                </div>
                <div class="stat-card">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Availability</span>
                </div>
            </div>
            
            <div class="project-highlight">
                <h3><i class="bi bi-star-fill"></i> Key Features Developed</h3>
                <ul>
                    <li><strong>Real-time Analytics dengan Geolocation:</strong> Track clicks dengan data negara dan kota menggunakan ip-api.com</li>
                    <li><strong>Email OTP Verification:</strong> Sistem keamanan berlapis dengan PHPMailer integration</li>
                    <li><strong>Verified Badge System:</strong> Badge khusus untuk pengguna terverifikasi</li>
                    <li><strong>Boxed Layout:</strong> Modern card-based design dengan customizable appearance</li>
                    <li><strong>Category System:</strong> Organisasi link dengan kategori dan icon</li>
                    <li><strong>Drag & Drop Reordering:</strong> Interactive UI untuk mengatur urutan link</li>
                    <li><strong>Performance Optimization:</strong> Caching system untuk load time optimal</li>
                    <li><strong>SEO Optimization:</strong> Meta tags, Schema.org, dan sitemap untuk discoverability</li>
                </ul>
            </div>
            
            <div class="project-highlight">
                <h3><i class="bi bi-database-fill"></i> Technical Architecture</h3>
                <ul>
                    <li><strong>Backend:</strong> PHP 8.0+ dengan prepared statements untuk security</li>
                    <li><strong>Database:</strong> MySQL 8.0 dengan normalized table structure (5NF)</li>
                    <li><strong>Frontend:</strong> Bootstrap 5.3.8 dengan custom CSS animations</li>
                    <li><strong>Charts:</strong> Highcharts integration untuk visual analytics</li>
                    <li><strong>Deployment:</strong> Docker containers + VPS hosting dengan Apache</li>
                    <li><strong>Security:</strong> Session management, CSRF protection, input validation</li>
                </ul>
            </div>
        </section>
        
        <section>
            <h2 class="section-title"><i class="bi bi-lightbulb"></i> Philosophy & Vision</h2>
            <p class="bio-text">
                Saya percaya bahwa teknologi harus accessible untuk semua orang. Itulah mengapa LinkMy dibuat 
                100% gratis tanpa batasan fitur atau paywalls. Visi saya adalah membuat LinkMy menjadi platform 
                bio link terbaik di Indonesia yang membantu content creator, UMKM, dan profesional meningkatkan 
                online presence mereka tanpa harus mengeluarkan biaya.
            </p>
            <p class="bio-text">
                <strong>Core Values LinkMy:</strong>
            </p>
            <ul class="bio-text">
                <li><strong>Free Forever:</strong> Tidak ada biaya tersembunyi atau premium tiers</li>
                <li><strong>User-Centric:</strong> Desain dan fitur berdasarkan kebutuhan pengguna Indonesia</li>
                <li><strong>Innovation:</strong> Terus mengembangkan fitur baru berdasarkan feedback</li>
                <li><strong>Performance:</strong> Fast loading, optimized code, reliable uptime</li>
                <li><strong>Security:</strong> Data pengguna dilindungi dengan best practices</li>
            </ul>
        </section>
        
        <section>
            <h2 class="section-title"><i class="bi bi-envelope-heart"></i> Let's Connect</h2>
            <p class="bio-text">
                Tertarik untuk berkolaborasi, memberikan feedback, atau sekadar ngobrol tentang teknologi? 
                Jangan ragu untuk menghubungi saya!
            </p>
            <p class="bio-text">
                <strong>Email:</strong> <a href="mailto:fahmiilham029@gmail.com">fahmiilham029@gmail.com</a><br>
                <strong>GitHub:</strong> <a href="https://github.com/FahmiYoshikage" target="_blank">@FahmiYoshikage</a><br>
                <strong>LinkMy Repository:</strong> <a href="https://github.com/FahmiYoshikage/LinkMy" target="_blank">github.com/FahmiYoshikage/LinkMy</a>
            </p>
        </section>
        
        <div class="text-center">
            <a href="/" class="back-button">
                <i class="bi bi-house-door"></i>
                Kembali ke Homepage
            </a>
            <a href="/register.php" class="back-button" style="margin-left: 10px;">
                <i class="bi bi-person-plus"></i>
                Coba LinkMy Gratis
            </a>
        </div>
    </div>
    
    <script src="/assets/bootstrap-5.3.8-dist/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
