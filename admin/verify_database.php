<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification - LinkMy v2.0</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .status-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { border-left: 4px solid #28a745; }
        .error { border-left: 4px solid #dc3545; }
        .warning { border-left: 4px solid #ffc107; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .sql-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
            border-left: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 LinkMy v2.0 - Database Verification</h1>
        <p>Verifikasi apakah database update v2.0 sudah dijalankan</p>
    </div>

    <?php
    require_once '../config/db.php';
    
    $checks = [];
    $all_good = true;
    
    // Check 1: Appearance table columns
    echo '<div class="status-box">';
    echo '<h2>1️⃣ Checking Appearance Table Columns</h2>';
    
    $required_columns = [
        'custom_bg_color' => 'VARCHAR(20)',
        'custom_button_color' => 'VARCHAR(20)',
        'custom_text_color' => 'VARCHAR(20)',
        'gradient_preset' => 'VARCHAR(50)',
        'profile_layout' => 'VARCHAR(20)',
        'show_profile_border' => 'TINYINT(1)',
        'enable_animations' => 'TINYINT(1)'
    ];
    
    $result = mysqli_query($conn, "DESCRIBE appearance");
    $existing_columns = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $existing_columns[$row['Field']] = $row['Type'];
    }
    
    echo '<table>';
    echo '<tr><th>Column Name</th><th>Expected Type</th><th>Status</th></tr>';
    
    foreach ($required_columns as $col => $type) {
        $exists = isset($existing_columns[$col]);
        $checks['appearance_' . $col] = $exists;
        if (!$exists) $all_good = false;
        
        echo '<tr>';
        echo '<td><code>' . $col . '</code></td>';
        echo '<td>' . $type . '</td>';
        echo '<td>';
        if ($exists) {
            echo '<span class="badge badge-success">✓ EXISTS</span>';
            echo ' <small>(' . $existing_columns[$col] . ')</small>';
        } else {
            echo '<span class="badge badge-danger">✗ MISSING</span>';
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // Check 2: Gradient Presets Table
    echo '<div class="status-box">';
    echo '<h2>2️⃣ Checking Gradient Presets Table</h2>';
    
    $table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'gradient_presets'");
    $checks['gradient_presets_table'] = mysqli_num_rows($table_exists) > 0;
    
    if ($checks['gradient_presets_table']) {
        echo '<span class="badge badge-success">✓ TABLE EXISTS</span><br><br>';
        
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM gradient_presets"));
        echo '<p>Total gradient presets: <strong>' . $count['count'] . '</strong></p>';
        
        if ($count['count'] > 0) {
            echo '<table>';
            echo '<tr><th>Preset Name</th><th>Colors</th></tr>';
            $presets = mysqli_query($conn, "SELECT preset_name, preview_color_1, preview_color_2 FROM gradient_presets");
            while ($preset = mysqli_fetch_assoc($presets)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($preset['preset_name']) . '</td>';
                echo '<td>';
                echo '<span style="display:inline-block;width:20px;height:20px;background:' . $preset['preview_color_1'] . ';border-radius:3px;"></span> ';
                echo '<span style="display:inline-block;width:20px;height:20px;background:' . $preset['preview_color_2'] . ';border-radius:3px;"></span>';
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<span class="badge badge-warning">⚠ TABLE EMPTY</span>';
            $all_good = false;
        }
    } else {
        echo '<span class="badge badge-danger">✗ TABLE MISSING</span>';
        $all_good = false;
    }
    echo '</div>';
    
    // Check 3: Social Icons Table
    echo '<div class="status-box">';
    echo '<h2>3️⃣ Checking Social Icons Table</h2>';
    
    $table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'social_icons'");
    $checks['social_icons_table'] = mysqli_num_rows($table_exists) > 0;
    
    if ($checks['social_icons_table']) {
        echo '<span class="badge badge-success">✓ TABLE EXISTS</span><br><br>';
        
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM social_icons"));
        echo '<p>Total social icons: <strong>' . $count['count'] . '</strong></p>';
        
        if ($count['count'] >= 19) {
            echo '<span class="badge badge-success">✓ ALL ICONS PRESENT</span>';
        } else {
            echo '<span class="badge badge-warning">⚠ EXPECTED 19, FOUND ' . $count['count'] . '</span>';
        }
    } else {
        echo '<span class="badge badge-danger">✗ TABLE MISSING</span>';
        $all_good = false;
    }
    echo '</div>';
    
    // Check 4: Link Categories Table
    echo '<div class="status-box">';
    echo '<h2>4️⃣ Checking Link Categories Table</h2>';
    
    $table_exists = mysqli_query($conn, "SHOW TABLES LIKE 'link_categories'");
    $checks['link_categories_table'] = mysqli_num_rows($table_exists) > 0;
    
    if ($checks['link_categories_table']) {
        echo '<span class="badge badge-success">✓ TABLE EXISTS</span>';
    } else {
        echo '<span class="badge badge-danger">✗ TABLE MISSING</span>';
        $all_good = false;
    }
    echo '</div>';
    
    // Check 5: Links table category_id column
    echo '<div class="status-box">';
    echo '<h2>5️⃣ Checking Links Table (category_id column)</h2>';
    
    $result = mysqli_query($conn, "DESCRIBE links");
    $has_category_id = false;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['Field'] === 'category_id') {
            $has_category_id = true;
            break;
        }
    }
    
    $checks['links_category_id'] = $has_category_id;
    if ($has_category_id) {
        echo '<span class="badge badge-success">✓ COLUMN EXISTS</span>';
    } else {
        echo '<span class="badge badge-danger">✗ COLUMN MISSING</span>';
        $all_good = false;
    }
    echo '</div>';
    
    // Final Summary
    echo '<div class="status-box ' . ($all_good ? 'success' : 'error') . '">';
    echo '<h2>📊 Final Summary</h2>';
    
    if ($all_good) {
        echo '<h3 style="color: #28a745;">✅ DATABASE IS READY!</h3>';
        echo '<p>All v2.0 database changes have been applied successfully.</p>';
        echo '<p><a href="appearance.php" style="display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;">Go to Appearance Settings</a></p>';
    } else {
        echo '<h3 style="color: #dc3545;">❌ DATABASE UPDATE REQUIRED!</h3>';
        echo '<p>Some database changes are missing. Please run the database update script.</p>';
        
        echo '<h4>How to Fix:</h4>';
        echo '<ol>';
        echo '<li>Open <strong>phpMyAdmin</strong> (http://localhost/phpmyadmin)</li>';
        echo '<li>Select database: <strong>linkmy_db</strong></li>';
        echo '<li>Click <strong>Import</strong> tab</li>';
        echo '<li>Choose file: <strong>database_update_v2.sql</strong></li>';
        echo '<li>Click <strong>Go</strong></li>';
        echo '<li>Refresh this page to verify</li>';
        echo '</ol>';
        
        echo '<h4>Or via Command Line:</h4>';
        echo '<div class="sql-code">';
        echo 'mysql -u root -p linkmy_db < database_update_v2.sql';
        echo '</div>';
        
        echo '<br><p><a href="?recheck=1" style="display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;">🔄 Recheck Database</a></p>';
    }
    echo '</div>';
    
    mysqli_close($conn);
    ?>
    
    <div style="text-align: center; margin-top: 30px; color: #666;">
        <p>LinkMy v2.0 Database Verification Tool</p>
        <p><small>Last checked: <?= date('Y-m-d H:i:s') ?></small></p>
    </div>
</body>
</html>
