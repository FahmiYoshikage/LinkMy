<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

echo "<h2>Debug Profile Stats</h2>";
echo "<p>Current User ID: {$current_user_id}</p>";

// Test Query 1: Basic profiles
echo "<h3>Test 1: Basic Profiles Query</h3>";
$query1 = "SELECT * FROM profiles WHERE user_id = ?";
$stmt1 = mysqli_prepare($conn, $query1);
mysqli_stmt_bind_param($stmt1, 'i', $current_user_id);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
echo "<pre>";
while ($row = mysqli_fetch_assoc($result1)) {
    print_r($row);
}
echo "</pre>";

// Test Query 2: With stats (current implementation)
echo "<h3>Test 2: Profiles with Stats (Current Query)</h3>";
$query2 = "SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.is_active, p.created_at,
           COUNT(DISTINCT l.link_id) as link_count,
           COALESCE(SUM(l.click_count), 0) as total_clicks
           FROM profiles p
           LEFT JOIN links l ON p.profile_id = l.profile_id
           WHERE p.user_id = ?
           GROUP BY p.profile_id, p.slug, p.profile_name, p.is_primary, p.is_active, p.created_at
           ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt2 = mysqli_prepare($conn, $query2);
mysqli_stmt_bind_param($stmt2, 'i', $current_user_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
echo "<pre>";
while ($row = mysqli_fetch_assoc($result2)) {
    print_r($row);
}
echo "</pre>";

// Test Query 3: Check links table
echo "<h3>Test 3: Links for User</h3>";
$query3 = "SELECT link_id, profile_id, title, click_count FROM links WHERE user_id = ?";
$stmt3 = mysqli_prepare($conn, $query3);
mysqli_stmt_bind_param($stmt3, 'i', $current_user_id);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
echo "<pre>";
while ($row = mysqli_fetch_assoc($result3)) {
    print_r($row);
}
echo "</pre>";

// Test Query 4: Alternative stats query
echo "<h3>Test 4: Alternative Stats Query (Subquery Method)</h3>";
$query4 = "SELECT p.*,
           (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
           (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
           FROM profiles p
           WHERE p.user_id = ?
           ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt4 = mysqli_prepare($conn, $query4);
mysqli_stmt_bind_param($stmt4, 'i', $current_user_id);
mysqli_stmt_execute($stmt4);
$result4 = mysqli_stmt_get_result($stmt4);
echo "<pre>";
while ($row = mysqli_fetch_assoc($result4)) {
    print_r($row);
}
echo "</pre>";

// Test MySQL version and ONLY_FULL_GROUP_BY mode
echo "<h3>Test 5: MySQL Configuration</h3>";
$version = mysqli_query($conn, "SELECT VERSION() as version");
$ver_row = mysqli_fetch_assoc($version);
echo "MySQL Version: " . $ver_row['version'] . "<br>";

$sql_mode = mysqli_query($conn, "SELECT @@sql_mode as mode");
$mode_row = mysqli_fetch_assoc($sql_mode);
echo "SQL Mode: " . $mode_row['mode'] . "<br>";

echo "<hr>";
echo "<p><a href='profiles.php'>Back to Profiles</a></p>";
?>
