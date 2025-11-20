<?php
/**
 * LinkMy Diagnostic Tool
 * Check if boxed layout feature is properly installed
 */

require_once 'config/db.php';

// Get user ID from query parameter or auto-detect from page_slug
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
} elseif (isset($_GET['slug'])) {
    // Get user_id from slug
    $query = "SELECT user_id FROM users WHERE page_slug = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $_GET['slug']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $user_id = $row ? $row['user_id'] : null;
    
    if (!$user_id) {
        die('<div style="color: red; padding: 20px;">User not found with slug: ' . htmlspecialchars($_GET['slug']) . '</div>');
    }
} else {
    // Auto-detect: use the user with appearance data
    $query = "SELECT user_id FROM appearance LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $user_id = $row ? $row['user_id'] : 1;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkMy Diagnostic Tool</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        h2 {
            color: #569cd6;
            margin-top: 30px;
        }
        .success {
            background: #1e3a1e;
            border-left: 4px solid #4ec9b0;
            padding: 15px;
            margin: 10px 0;
        }
        .error {
            background: #3a1e1e;
            border-left: 4px solid #f44747;
            padding: 15px;
            margin: 10px 0;
        }
        .warning {
            background: #3a331e;
            border-left: 4px solid #dcdcaa;
            padding: 15px;
            margin: 10px 0;
        }
        .info {
            background: #1e2a3a;
            border-left: 4px solid #569cd6;
            padding: 15px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: #252526;
        }
        th {
            background: #2d2d30;
            padding: 12px;
            text-align: left;
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #3e3e42;
        }
        tr:hover {
            background: #2d2d30;
        }
        code {
            background: #1e1e1e;
            padding: 2px 6px;
            border-radius: 3px;
            color: #ce9178;
        }
        .status-ok { color: #4ec9b0; }
        .status-error { color: #f44747; }
        .status-warning { color: #dcdcaa; }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #3e3e42;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #4ec9b0; color: #1e1e1e; }
        .badge-error { background: #f44747; color: #fff; }
        .badge-warning { background: #dcdcaa; color: #1e1e1e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 LinkMy Diagnostic Tool</h1>
        
        <?php
        // Get username for display
        $query = "SELECT username, page_slug FROM users WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_info = mysqli_fetch_assoc($result);
        ?>
        
        <p>Checking User: <strong><?= $user_info['username'] ?? 'Unknown' ?></strong> (ID: <?= $user_id ?>, Slug: <?= $user_info['page_slug'] ?? 'N/A' ?>)</p>
        <p style="font-size: 14px; color: #858585;">
            Quick links: 
            <a href="?" style="color: #569cd6;">Auto-detect</a> | 
            <a href="?slug=<?= $user_info['page_slug'] ?? '' ?>" style="color: #569cd6;">Refresh</a>
        </p>

        <?php
        $all_ok = true;
        $issues = [];
        
        // ===== CHECK 1: Database Columns =====
        echo "<h2>1️⃣ Database Structure Check</h2>";
        
        $required_columns = [
            'boxed_layout', 'outer_bg_type', 'outer_bg_color', 
            'outer_bg_gradient_start', 'outer_bg_gradient_end',
            'container_bg_color', 'container_max_width', 
            'container_border_radius', 'container_shadow'
        ];
        
        $query = "SHOW COLUMNS FROM appearance";
        $result = mysqli_query($conn, $query);
        $existing_columns = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $existing_columns[] = $row['Field'];
        }
        
        $missing_columns = array_diff($required_columns, $existing_columns);
        
        if (empty($missing_columns)) {
            echo '<div class="success">✅ All boxed layout columns exist in <code>appearance</code> table</div>';
        } else {
            echo '<div class="error">❌ Missing columns in <code>appearance</code> table:</div>';
            echo '<pre>' . implode("\n", $missing_columns) . '</pre>';
            $all_ok = false;
            $issues[] = "Run database_add_boxed_layout.sql";
        }
        
        // ===== CHECK 2: View Columns =====
        echo "<h2>2️⃣ View Structure Check</h2>";
        
        $query = "SHOW COLUMNS FROM v_public_page_data";
        $result = mysqli_query($conn, $query);
        $view_columns = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $view_columns[] = $row['Field'];
        }
        
        $missing_in_view = array_diff($required_columns, $view_columns);
        
        if (empty($missing_in_view)) {
            echo '<div class="success">✅ All boxed layout columns exist in <code>v_public_page_data</code> view</div>';
        } else {
            echo '<div class="error">❌ Missing columns in <code>v_public_page_data</code> view:</div>';
            echo '<pre>' . implode("\n", $missing_in_view) . '</pre>';
            $all_ok = false;
            $issues[] = "Run database_update_view_boxed_layout.sql";
        }
        
        // ===== CHECK 3: User Data =====
        echo "<h2>3️⃣ User Appearance Data</h2>";
        
        $query = "SELECT * FROM appearance WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $appearance_data = mysqli_fetch_assoc($result);
        
        if ($appearance_data) {
            echo '<div class="success">✅ User data found</div>';
            echo '<table>';
            echo '<tr><th>Column</th><th>Value</th><th>Status</th></tr>';
            
            foreach ($required_columns as $col) {
                $value = $appearance_data[$col] ?? 'NULL';
                $status = isset($appearance_data[$col]) ? 
                    '<span class="status-ok">✓</span>' : 
                    '<span class="status-error">✗</span>';
                echo "<tr><td><code>{$col}</code></td><td>{$value}</td><td>{$status}</td></tr>";
            }
            echo '</table>';
            
            // Check if boxed layout is enabled
            if ($appearance_data['boxed_layout'] == 1) {
                echo '<div class="info">ℹ️ Boxed layout is <strong>ENABLED</strong></div>';
            } else {
                echo '<div class="warning">⚠️ Boxed layout is <strong>DISABLED</strong></div>';
            }
        } else {
            echo '<div class="error">❌ No appearance data found for user_id ' . $user_id . '</div>';
            $all_ok = false;
        }
        
        // ===== CHECK 4: View Data =====
        echo "<h2>4️⃣ View Data Check</h2>";
        
        $query = "SELECT * FROM v_public_page_data WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $view_data = mysqli_fetch_assoc($result);
        
        if ($view_data) {
            echo '<div class="success">✅ User found in view</div>';
            
            $boxed_columns_in_view = [];
            foreach ($required_columns as $col) {
                if (array_key_exists($col, $view_data)) {
                    $boxed_columns_in_view[$col] = $view_data[$col];
                }
            }
            
            if (count($boxed_columns_in_view) == count($required_columns)) {
                echo '<div class="success">✅ All boxed layout columns present in view data</div>';
                echo '<table>';
                echo '<tr><th>Column</th><th>Value from View</th></tr>';
                foreach ($boxed_columns_in_view as $col => $val) {
                    echo "<tr><td><code>{$col}</code></td><td>{$val}</td></tr>";
                }
                echo '</table>';
            } else {
                echo '<div class="error">❌ Some columns missing from view data</div>';
                $all_ok = false;
            }
        } else {
            echo '<div class="error">❌ User not found in v_public_page_data</div>';
            $all_ok = false;
        }
        
        // ===== CHECK 5: Links =====
        echo "<h2>5️⃣ Links Check</h2>";
        
        $query = "SELECT COUNT(*) as count FROM links WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $link_count = mysqli_fetch_assoc($result)['count'];
        
        if ($link_count > 0) {
            echo "<div class='success'>✅ Found {$link_count} link(s)</div>";
            
            $query = "SELECT id, title, url, is_visible, display_order FROM links WHERE user_id = ? ORDER BY display_order LIMIT 5";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            echo '<table>';
            echo '<tr><th>ID</th><th>Title</th><th>URL</th><th>Visible</th><th>Order</th></tr>';
            while ($link = mysqli_fetch_assoc($result)) {
                $visible_badge = $link['is_visible'] ? 
                    '<span class="badge badge-success">Visible</span>' : 
                    '<span class="badge badge-error">Hidden</span>';
                echo "<tr>";
                echo "<td>{$link['id']}</td>";
                echo "<td>{$link['title']}</td>";
                echo "<td><small>{$link['url']}</small></td>";
                echo "<td>{$visible_badge}</td>";
                echo "<td>{$link['display_order']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<div class="warning">⚠️ No links found for this user</div>';
        }
        
        // ===== SUMMARY =====
        echo "<h2>📊 Summary</h2>";
        
        if ($all_ok) {
            echo '<div class="success">';
            echo '<h3 style="color: #4ec9b0; margin-top: 0;">✅ All Systems Operational!</h3>';
            echo '<p>Boxed layout feature is properly installed and configured.</p>';
            if ($appearance_data && $appearance_data['boxed_layout'] == 1) {
                echo '<p><strong>Boxed layout is ENABLED.</strong> Profile should display with boxed mode.</p>';
            } else {
                echo '<p><strong>Boxed layout is DISABLED.</strong> Enable it in admin panel: Appearance → Boxed Layout</p>';
            }
            echo '</div>';
        } else {
            echo '<div class="error">';
            echo '<h3 style="color: #f44747; margin-top: 0;">❌ Issues Found</h3>';
            echo '<p>The following actions are required:</p>';
            echo '<ol>';
            foreach ($issues as $issue) {
                echo "<li>{$issue}</li>";
            }
            echo '</ol>';
            echo '<p><strong>On VPS, run:</strong></p>';
            echo '<pre>cd /var/www/html
mysql -u root -p linkmy_db < database_add_boxed_layout.sql
mysql -u root -p linkmy_db < database_update_view_boxed_layout.sql
sudo systemctl restart apache2 php-fpm</pre>';
            echo '</div>';
        }
        
        // ===== ADDITIONAL INFO =====
        echo "<h2>ℹ️ Additional Information</h2>";
        echo '<div class="info">';
        echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
        echo '<p><strong>MySQL Version:</strong> ' . mysqli_get_server_info($conn) . '</p>';
        echo '<p><strong>Server:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</p>';
        echo '<p><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</p>';
        echo '</div>';
        
        mysqli_close($conn);
        ?>
        
        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #3e3e42; color: #858585;">
            <p><small>LinkMy Diagnostic Tool v1.0 | Generated: <?= date('Y-m-d H:i:s') ?></small></p>
        </div>
    </div>
</body>
</html>
