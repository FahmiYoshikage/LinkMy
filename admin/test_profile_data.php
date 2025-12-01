<?php
// Simple test without auth to check raw data
require_once '../config/db.php';

// Test for user_id = 6 (fahmi based on the HTML)
$test_user_id = 6;

echo "<h2>Raw Database Test for user_id = {$test_user_id}</h2>";

// Check profiles
echo "<h3>1. Profiles Table:</h3>";
$q1 = "SELECT * FROM profiles WHERE user_id = {$test_user_id}";
$r1 = mysqli_query($conn, $q1);
echo "<pre>";
while ($row = mysqli_fetch_assoc($r1)) {
    print_r($row);
}
echo "</pre>";

// Check links
echo "<h3>2. Links Table:</h3>";
$q2 = "SELECT link_id, profile_id, title, url, click_count FROM links WHERE user_id = {$test_user_id}";
$r2 = mysqli_query($conn, $q2);
echo "<pre>";
while ($row = mysqli_fetch_assoc($r2)) {
    print_r($row);
}
echo "</pre>";

// Test the new subquery method
echo "<h3>3. New Query Method (Subquery):</h3>";
$q3 = "SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.created_at,
       (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
       (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
       FROM profiles p
       WHERE p.user_id = {$test_user_id}
       ORDER BY p.is_primary DESC, p.created_at ASC";
$r3 = mysqli_query($conn, $q3);
echo "<pre>";
while ($row = mysqli_fetch_assoc($r3)) {
    echo "Profile: {$row['slug']}\n";
    echo "Link Count: {$row['link_count']}\n";
    echo "Total Clicks: {$row['total_clicks']}\n";
    echo "Created At: {$row['created_at']}\n";
    echo "---\n";
    print_r($row);
}
echo "</pre>";

// Test date formatting
echo "<h3>4. Date Formatting Test:</h3>";
$test_date = '2025-11-18 10:30:00';
echo "Original: {$test_date}<br>";
echo "Formatted: " . date('d M Y', strtotime($test_date)) . "<br>";
echo "strtotime result: " . strtotime($test_date) . "<br>";

// Test empty/null date
$empty_date = null;
echo "<br>Null date strtotime: " . strtotime($empty_date) . "<br>";
echo "Null date formatted: " . date('d M Y', strtotime($empty_date)) . "<br>";
?>
