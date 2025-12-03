<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth_check.php';

$user_id = $_SESSION['user_id'] ?? 1;

echo "<h2>Query Test Tool</h2>";
echo "<p>Testing profile stats query for user_id: {$user_id}</p>";
echo "<hr>";

// Test Query 1: With LEFT JOIN and GROUP BY
echo "<h3>Test 1: LEFT JOIN with GROUP BY</h3>";
$query1 = "SELECT 
            p.id, 
            p.user_id, 
            p.slug, 
            p.name, 
            p.display_order, 
            p.is_active, 
            p.created_at,
            COUNT(l.id) as link_count,
            COALESCE(SUM(l.clicks), 0) as total_clicks
            FROM profiles p 
            LEFT JOIN links l ON l.profile_id = p.id
            WHERE p.user_id = {$user_id}
            GROUP BY p.id, p.user_id, p.slug, p.name, p.display_order, p.is_active, p.created_at
            ORDER BY p.display_order ASC";

echo "<pre>Query: {$query1}</pre>";
$result1 = mysqli_query($conn, $query1);

if (!$result1) {
    echo "<p style='color: red;'>ERROR: " . mysqli_error($conn) . "</p>";
} else {
    echo "<p style='color: green;'>✅ Query executed successfully!</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<thead><tr><th>id</th><th>slug</th><th>name</th><th>link_count</th><th>total_clicks</th><th>created_at</th><th>is_active</th></tr></thead>";
    echo "<tbody>";
    
    while ($row = mysqli_fetch_assoc($result1)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['slug']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['link_count']}</td>";
        echo "<td>{$row['total_clicks']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['is_active']}</td>";
        echo "</tr>";
        
        echo "<tr><td colspan='7'><pre style='background: #f0f0f0; padding: 10px;'>";
        print_r($row);
        echo "</pre></td></tr>";
    }
    
    echo "</tbody></table>";
}

// Test Query 2: With subqueries (original method)
echo "<hr>";
echo "<h3>Test 2: Subqueries method</h3>";
$query2 = "SELECT p.id, p.user_id, p.slug, p.name, p.display_order, p.is_active, p.created_at,
            (SELECT COUNT(*) FROM links WHERE profile_id = p.id) as link_count,
            (SELECT COALESCE(SUM(clicks), 0) FROM links WHERE profile_id = p.id) as total_clicks
            FROM profiles p 
            WHERE p.user_id = {$user_id}
            ORDER BY p.display_order ASC";

echo "<pre>Query: {$query2}</pre>";
$result2 = mysqli_query($conn, $query2);

if (!$result2) {
    echo "<p style='color: red;'>ERROR: " . mysqli_error($conn) . "</p>";
} else {
    echo "<p style='color: green;'>✅ Query executed successfully!</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<thead><tr><th>id</th><th>slug</th><th>name</th><th>link_count</th><th>total_clicks</th><th>created_at</th><th>is_active</th></tr></thead>";
    echo "<tbody>";
    
    while ($row = mysqli_fetch_assoc($result2)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['slug']}</td>";
        echo "<td>{$row['name']}</td>";
        echo "<td>{$row['link_count']}</td>";
        echo "<td>{$row['total_clicks']}</td>";
        echo "<td>{$row['created_at']}</td>";
        echo "<td>{$row['is_active']}</td>";
        echo "</tr>";
        
        echo "<tr><td colspan='7'><pre style='background: #f0f0f0; padding: 10px;'>";
        print_r($row);
        echo "</pre></td></tr>";
    }
    
    echo "</tbody></table>";
}

// Check MySQL SQL Mode
echo "<hr>";
echo "<h3>MySQL Configuration Check</h3>";
$sql_mode_query = "SELECT @@sql_mode";
$sql_mode_result = mysqli_query($conn, $sql_mode_query);
if ($sql_mode_result) {
    $sql_mode = mysqli_fetch_assoc($sql_mode_result);
    echo "<pre><strong>SQL Mode:</strong> {$sql_mode['@@sql_mode']}</pre>";
    
    if (strpos($sql_mode['@@sql_mode'], 'ONLY_FULL_GROUP_BY') !== false) {
        echo "<p style='color: orange;'>⚠️ ONLY_FULL_GROUP_BY is enabled. This might affect GROUP BY queries.</p>";
    }
}

echo "<hr>";
echo "<p><a href='../admin/settings.php'>← Back to Settings</a></p>";
?>
