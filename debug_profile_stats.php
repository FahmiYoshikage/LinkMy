<?php
// Debug script untuk melihat stats profile
require_once 'config/db.php';

$user_id = 12; // Fahmi

echo "=== DEBUG PROFILE STATS ===\n\n";

// Test 1: Query dengan AND l.user_id = p.user_id (query yang sekarang)
echo "1. Query DENGAN kondisi AND l.user_id = p.user_id:\n";
echo str_repeat("-", 60) . "\n";
$query1 = "SELECT p.profile_id, p.user_id, p.slug, p.profile_name, 
          COUNT(DISTINCT l.link_id) as link_count,
          COALESCE(SUM(l.click_count), 0) as total_clicks
          FROM profiles p
          LEFT JOIN links l ON p.profile_id = l.profile_id AND l.user_id = p.user_id
          WHERE p.user_id = ?
          GROUP BY p.profile_id, p.user_id, p.slug, p.profile_name
          ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt1 = mysqli_prepare($conn, $query1);
mysqli_stmt_bind_param($stmt1, 'i', $user_id);
mysqli_stmt_execute($stmt1);
$result1 = mysqli_stmt_get_result($stmt1);
while ($row = mysqli_fetch_assoc($result1)) {
    echo "Profile: {$row['slug']} (ID: {$row['profile_id']})\n";
    echo "  Links: {$row['link_count']}\n";
    echo "  Clicks: {$row['total_clicks']}\n\n";
}

// Test 2: Query TANPA kondisi AND l.user_id = p.user_id
echo "\n2. Query TANPA kondisi AND l.user_id = p.user_id:\n";
echo str_repeat("-", 60) . "\n";
$query2 = "SELECT p.profile_id, p.user_id, p.slug, p.profile_name, 
          COUNT(DISTINCT l.link_id) as link_count,
          COALESCE(SUM(l.click_count), 0) as total_clicks
          FROM profiles p
          LEFT JOIN links l ON p.profile_id = l.profile_id
          WHERE p.user_id = ?
          GROUP BY p.profile_id, p.user_id, p.slug, p.profile_name
          ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt2 = mysqli_prepare($conn, $query2);
mysqli_stmt_bind_param($stmt2, 'i', $user_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
while ($row = mysqli_fetch_assoc($result2)) {
    echo "Profile: {$row['slug']} (ID: {$row['profile_id']})\n";
    echo "  Links: {$row['link_count']}\n";
    echo "  Clicks: {$row['total_clicks']}\n\n";
}

// Test 3: Cek raw data links per profile
echo "\n3. RAW DATA - Links per profile:\n";
echo str_repeat("-", 60) . "\n";
$query3 = "SELECT profile_id, user_id, COUNT(*) as link_count, SUM(click_count) as total_clicks 
           FROM links 
           WHERE user_id = ? 
           GROUP BY profile_id, user_id";
$stmt3 = mysqli_prepare($conn, $query3);
mysqli_stmt_bind_param($stmt3, 'i', $user_id);
mysqli_stmt_execute($stmt3);
$result3 = mysqli_stmt_get_result($stmt3);
while ($row = mysqli_fetch_assoc($result3)) {
    echo "Profile ID: {$row['profile_id']} | User ID: {$row['user_id']}\n";
    echo "  Links: {$row['link_count']}\n";
    echo "  Clicks: {$row['total_clicks']}\n\n";
}

// Test 4: Cek detail beberapa links
echo "\n4. Sample links untuk user 12:\n";
echo str_repeat("-", 60) . "\n";
$query4 = "SELECT link_id, user_id, profile_id, title, click_count FROM links WHERE user_id = ? LIMIT 5";
$stmt4 = mysqli_prepare($conn, $query4);
mysqli_stmt_bind_param($stmt4, 'i', $user_id);
mysqli_stmt_execute($stmt4);
$result4 = mysqli_stmt_get_result($stmt4);
while ($row = mysqli_fetch_assoc($result4)) {
    echo "Link #{$row['link_id']}: {$row['title']}\n";
    echo "  user_id: {$row['user_id']} | profile_id: {$row['profile_id']} | clicks: {$row['click_count']}\n\n";
}

mysqli_close($conn);
?>
