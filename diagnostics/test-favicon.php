<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Favicon Test - LinkMy</title>
    <?php require_once __DIR__ . '/partials/favicons.php'; ?>
</head>
<body style="font-family: Arial; padding: 50px; background: #f5f5f5;">
    <h1 style="color: #667eea;">🔍 Favicon Test Page</h1>
    <p>Cek browser tab untuk melihat favicon chain link.</p>
    
    <h2>Favicon Files Check:</h2>
    <ul>
        <?php
        $faviconFiles = [
            'favicon.ico' => '/assets/images/favicon.ico',
            'favicon-16x16.png' => '/assets/images/favicon-16x16.png',
            'favicon-32x32.png' => '/assets/images/favicon-32x32.png',
            'apple-touch-icon.png' => '/assets/images/apple-touch-icon.png',
            'android-chrome-192x192.png' => '/assets/images/android-chrome-192x192.png',
            'android-chrome-512x512.png' => '/assets/images/android-chrome-512x512.png',
        ];
        
        foreach ($faviconFiles as $name => $path) {
            $fullPath = __DIR__ . $path;
            $exists = file_exists($fullPath);
            $color = $exists ? 'green' : 'red';
            $status = $exists ? '✅ EXISTS' : '❌ NOT FOUND';
            $size = $exists ? ' (' . round(filesize($fullPath) / 1024, 2) . ' KB)' : '';
            echo "<li style='color: $color;'><strong>$name:</strong> $status $size<br>";
            echo "<a href='$path' target='_blank' style='color: blue;'>→ Test link: $path</a></li>";
        }
        ?>
    </ul>
    
    <h2>Favicon Preview:</h2>
    <div style="background: white; padding: 20px; display: inline-block; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <img src="/assets/images/favicon-32x32.png" alt="Favicon 32x32" style="border: 1px solid #ddd; margin: 5px;">
        <img src="/assets/images/favicon-16x16.png" alt="Favicon 16x16" style="border: 1px solid #ddd; margin: 5px;">
    </div>
    
    <h2>HTML Head Content:</h2>
    <pre style="background: #2d2d2d; color: #f8f8f2; padding: 20px; border-radius: 8px; overflow-x: auto;">
<?php
echo htmlspecialchars(file_get_contents(__DIR__ . '/partials/favicons.php'));
?>
    </pre>
    
    <hr>
    <p><a href="/landing.php" style="color: #667eea; text-decoration: none; font-weight: bold;">← Back to Landing Page</a></p>
</body>
</html>
