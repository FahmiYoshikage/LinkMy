<?php
// Quick test to see actual data
require_once '../config/auth_check.php';
require_once '../config/db.php';

echo "<h2>Profile Data Test for User ID: {$current_user_id}</h2>";
echo "<p><a href='settings.php'>← Back to Settings</a> | <a href='profiles.php'>Go to Profiles</a></p>";
echo "<hr>";

// Test 1: Get all profiles for this user
echo "<h3>Test 1: All Profiles (Basic SELECT)</h3>";
$query1 = "SELECT * FROM profiles WHERE user_id = ?";
$stmt1 = mysqli_prepare($conn, $query1);
mysqli_stmt_bind_param($stmt1, 'i', $current_user_id);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);

echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin-bottom:20px;'>";
echo "<tr><th>ID</th><th>Slug</th><th>Name</th><th>Primary</th><th>Created At</th></tr>";
while ($row = mysqli_fetch_assoc($result1)) {
    echo "<tr>";
    echo "<td>{$row['profile_id']}</td>";
    echo "<td><strong>{$row['slug']}</strong></td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td>" . ($row['is_primary'] ? '✅ YES' : 'No') . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

// Test 2: Get all links for this user
echo "<h3>Test 2: All Links</h3>";
$query2 = "SELECT link_id, profile_id, title, url, click_count FROM links WHERE user_id = ? ORDER BY profile_id";
$stmt2 = mysqli_prepare($conn, $query2);
mysqli_stmt_bind_param($stmt2, 'i', $current_user_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);

$total_links = 0;
$total_clicks = 0;
echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin-bottom:20px;'>";
echo "<tr><th>Link ID</th><th>Profile ID</th><th>Title</th><th>Clicks</th></tr>";
while ($row = mysqli_fetch_assoc($result2)) {
    echo "<tr>";
    echo "<td>{$row['link_id']}</td>";
    echo "<td>{$row['profile_id']}</td>";
    echo "<td>{$row['title']}</td>";
    echo "<td>{$row['click_count']}</td>";
    echo "</tr>";
    $total_links++;
    $total_clicks += $row['click_count'];
}
echo "</table>";
echo "<p><strong>Total Links: {$total_links} | Total Clicks: {$total_clicks}</strong></p>";

// Test 3: Test the NEW subquery method
echo "<h3>Test 3: NEW Query Method (With Subquery - SHOULD BE CORRECT)</h3>";
$query3 = "SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.is_active, p.created_at,
           (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
           (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
           FROM profiles p
           WHERE p.user_id = ?
           ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt3 = mysqli_prepare($conn, $query3);
if (!$stmt3) {
    echo "<div style='color:red;'><strong>ERROR:</strong> " . mysqli_error($conn) . "</div>";
} else {
    mysqli_stmt_bind_param($stmt3, 'i', $current_user_id);
    mysqli_stmt_execute($stmt3);
    $result3 = mysqli_stmt_get_result($stmt3);
    
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin-bottom:20px;'>";
    echo "<tr><th>Slug</th><th>Name</th><th>Primary</th><th>Link Count</th><th>Total Clicks</th><th>Created At</th></tr>";
    while ($row = mysqli_fetch_assoc($result3)) {
        echo "<tr>";
        echo "<td><strong>{$row['slug']}</strong></td>";
        echo "<td>{$row['profile_name']}</td>";
        echo "<td>" . ($row['is_primary'] ? '✅' : '') . "</td>";
        echo "<td style='text-align:center;'><strong>{$row['link_count']}</strong></td>";
        echo "<td style='text-align:center;'><strong>{$row['total_clicks']}</strong></td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test 4: Test OLD method (GROUP BY) for comparison
echo "<h3>Test 4: OLD Query Method (GROUP BY - MAY FAIL)</h3>";
$query4 = "SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.is_active, p.created_at,
           COUNT(DISTINCT l.link_id) as link_count,
           COALESCE(SUM(l.click_count), 0) as total_clicks
           FROM profiles p
           LEFT JOIN links l ON p.profile_id = l.profile_id
           WHERE p.user_id = ?
           GROUP BY p.profile_id, p.slug, p.profile_name, p.is_primary, p.is_active, p.created_at
           ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt4 = mysqli_prepare($conn, $query4);
if (!$stmt4) {
    echo "<div style='color:red;'><strong>ERROR:</strong> " . mysqli_error($conn) . "</div>";
} else {
    mysqli_stmt_bind_param($stmt4, 'i', $current_user_id);
    mysqli_stmt_execute($stmt4);
    $result4 = mysqli_stmt_get_result($stmt4);
    
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse; margin-bottom:20px;'>";
    echo "<tr><th>Slug</th><th>Name</th><th>Primary</th><th>Link Count</th><th>Total Clicks</th><th>Created At</th></tr>";
    while ($row = mysqli_fetch_assoc($result4)) {
        echo "<tr>";
        echo "<td><strong>{$row['slug']}</strong></td>";
        echo "<td>{$row['profile_name']}</td>";
        echo "<td>" . ($row['is_primary'] ? '✅' : '') . "</td>";
        echo "<td style='text-align:center;'><strong>{$row['link_count']}</strong></td>";
        echo "<td style='text-align:center;'><strong>{$row['total_clicks']}</strong></td>";
        echo "<td>{$row['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<hr>";
echo "<h3>Conclusion:</h3>";
echo "<p>✅ If Test 3 (NEW method) shows correct data → The fix is working!</p>";
echo "<p>❌ If both Test 3 and Test 4 show 0s → Check if links.profile_id matches profiles.profile_id</p>";
echo "<p>🔄 After confirming Test 3 works, <strong>clear your browser cache</strong> and reload settings.php</p>";
?>
