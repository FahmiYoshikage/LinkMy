<!DOCTYPE html>
<html>
<head>
    <title>LinkMy Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        h2 { border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #667eea; color: white; }
    </style>
</head>
<body>
    <h1>🔍 LinkMy Diagnostic Tool</h1>

<?php
require_once 'config/db.php';

// 1. Check Database Connection
echo "<div class='section'>";
echo "<h2>1. Database Connection</h2>";
if ($conn) {
    echo "<p class='success'>✅ Connected to database</p>";
    echo "<p>Database: " . mysqli_get_host_info($conn) . "</p>";
} else {
    echo "<p class='error'>❌ Cannot connect to database</p>";
    exit;
}
echo "</div>";

// 2. Check link_categories table structure
echo "<div class='section'>";
echo "<h2>2. link_categories Table Structure</h2>";
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'link_categories'");
if ($check_table && mysqli_num_rows($check_table) > 0) {
    echo "<p class='success'>✅ Table exists</p>";
    
    $columns = mysqli_query($conn, "SHOW COLUMNS FROM link_categories");
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while ($col = mysqli_fetch_assoc($columns)) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check for correct column names
    $has_display_order = mysqli_query($conn, "SHOW COLUMNS FROM link_categories LIKE 'display_order'");
    $has_is_expanded = mysqli_query($conn, "SHOW COLUMNS FROM link_categories LIKE 'is_expanded'");
    
    if (mysqli_num_rows($has_display_order) > 0) {
        echo "<p class='success'>✅ Column 'display_order' exists</p>";
    } else {
        echo "<p class='error'>❌ Column 'display_order' MISSING (should rename from 'order_index')</p>";
    }
    
    if (mysqli_num_rows($has_is_expanded) > 0) {
        echo "<p class='success'>✅ Column 'is_expanded' exists</p>";
    } else {
        echo "<p class='error'>❌ Column 'is_expanded' MISSING (should rename from 'is_active')</p>";
    }
} else {
    echo "<p class='error'>❌ Table 'link_categories' does not exist</p>";
}
echo "</div>";

// 3. Check appearance table structure
echo "<div class='section'>";
echo "<h2>3. appearance Table - New Columns</h2>";
$app_cols = mysqli_query($conn, "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'appearance' AND COLUMN_NAME IN ('container_style', 'enable_categories')");

if (mysqli_num_rows($app_cols) > 0) {
    echo "<table>";
    echo "<tr><th>Column</th><th>Type</th><th>Default</th></tr>";
    while ($col = mysqli_fetch_assoc($app_cols)) {
        echo "<tr>";
        echo "<td>{$col['COLUMN_NAME']}</td>";
        echo "<td>{$col['DATA_TYPE']}</td>";
        echo "<td>{$col['COLUMN_DEFAULT']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ Columns 'container_style' and 'enable_categories' NOT FOUND in appearance table</p>";
}
echo "</div>";

// 4. Check views
echo "<div class='section'>";
echo "<h2>4. Database Views</h2>";
$views = mysqli_query($conn, "SHOW FULL TABLES WHERE TABLE_TYPE = 'VIEW'");
echo "<ul>";
while ($view = mysqli_fetch_array($views)) {
    echo "<li class='success'>✅ {$view[0]}</li>";
}
echo "</ul>";

// Check if v_public_page_data has new columns
$check_view = mysqli_query($conn, "SELECT container_style, enable_categories FROM v_public_page_data LIMIT 1");
if ($check_view) {
    echo "<p class='success'>✅ View 'v_public_page_data' has container_style and enable_categories columns</p>";
} else {
    echo "<p class='error'>❌ View 'v_public_page_data' MISSING new columns. Error: " . mysqli_error($conn) . "</p>";
}
echo "</div>";

// 5. Check user's current settings
echo "<div class='section'>";
echo "<h2>5. User Settings Check (user_id=12)</h2>";
$settings = mysqli_query($conn, "SELECT container_style, enable_categories FROM appearance WHERE user_id = 12");
if ($settings && mysqli_num_rows($settings) > 0) {
    $s = mysqli_fetch_assoc($settings);
    echo "<table>";
    echo "<tr><th>Setting</th><th>Value</th></tr>";
    echo "<tr><td>container_style</td><td><strong>{$s['container_style']}</strong></td></tr>";
    echo "<tr><td>enable_categories</td><td><strong>{$s['enable_categories']}</strong></td></tr>";
    echo "</table>";
    
    if ($s['container_style'] === 'boxed') {
        echo "<p class='success'>✅ Boxed layout is ENABLED</p>";
    } else {
        echo "<p class='warning'>⚠️ Boxed layout is DISABLED (current: {$s['container_style']})</p>";
    }
} else {
    echo "<p class='error'>❌ Cannot fetch user settings</p>";
}
echo "</div>";

// 6. Session check
echo "<div class='section'>";
echo "<h2>6. Session Configuration</h2>";
echo "<table>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>session.cookie_lifetime</td><td>" . ini_get('session.cookie_lifetime') . "</td></tr>";
echo "<tr><td>session.gc_maxlifetime</td><td>" . ini_get('session.gc_maxlifetime') . "</td></tr>";
echo "<tr><td>session.save_path</td><td>" . ini_get('session.save_path') . "</td></tr>";
echo "</table>";
echo "</div>";

?>

    <div class='section'>
        <h2>🔧 Recommended Actions</h2>
        <ol>
            <li>If columns missing: Run <code>database_fix_structure.sql</code> in phpMyAdmin</li>
            <li>If boxed layout disabled: Go to Appearance → Advanced → Select "Boxed"</li>
            <li>If session expires: Check docker volume mounts for /tmp or /var/lib/php/sessions</li>
        </ol>
    </div>
</body>
</html>
