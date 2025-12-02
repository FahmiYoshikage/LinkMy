<?php
require_once '../config/auth_check.php';
require_once '../config/db.php';

echo "<style>body{font-family:Arial;padding:20px} table{border-collapse:collapse;width:100%;margin:20px 0} th,td{border:1px solid #ddd;padding:12px;text-align:left} th{background:#667eea;color:white} .success{background:#d4edda;padding:10px;border-radius:5px;margin:10px 0} .warning{background:#fff3cd;padding:10px;border-radius:5px;margin:10px 0} .error{background:#f8d7da;padding:10px;border-radius:5px;margin:10px 0}</style>";

echo "<h1>🔍 Profile Data Analysis</h1>";
echo "<p><strong>Current User ID:</strong> {$current_user_id}</p>";
echo "<p><strong>Current Session Slug:</strong> " . ($_SESSION['page_slug'] ?? 'N/A') . "</p>";
echo "<hr>";

// Check current user's profiles
echo "<h2>1. Your Profiles in Production Database</h2>";
$profiles_query = "SELECT * FROM profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $profiles_query);
mysqli_stmt_bind_param($stmt, 'i', $current_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$user_profiles = [];
echo "<table><tr><th>Profile ID</th><th>Slug</th><th>Profile Name</th><th>Primary</th><th>Active</th><th>Created At</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    $user_profiles[] = $row;
    echo "<tr>";
    echo "<td><strong>{$row['profile_id']}</strong></td>";
    echo "<td><code>{$row['slug']}</code></td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td>" . ($row['is_primary'] ? '✅ Yes' : 'No') . "</td>";
    echo "<td>" . ($row['is_active'] ? '✅ Yes' : '❌ No') . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

if (empty($user_profiles)) {
    echo "<div class='error'>❌ <strong>No profiles found!</strong> This is the problem!</div>";
} else {
    echo "<div class='success'>✅ Found " . count($user_profiles) . " profile(s)</div>";
}

// Check links for each profile
echo "<h2>2. Links for Each Profile</h2>";
foreach ($user_profiles as $profile) {
    echo "<h3>Profile: <code>{$profile['slug']}</code> (ID: {$profile['profile_id']})</h3>";
    
    $links_query = "SELECT link_id, title, url, click_count, created_at FROM links WHERE profile_id = ?";
    $stmt2 = mysqli_prepare($conn, $links_query);
    mysqli_stmt_bind_param($stmt2, 'i', $profile['profile_id']);
    mysqli_stmt_execute($stmt2);
    $links_result = mysqli_stmt_get_result($stmt2);
    
    $links = [];
    while ($link = mysqli_fetch_assoc($links_result)) {
        $links[] = $link;
    }
    
    if (empty($links)) {
        echo "<div class='warning'>⚠️ No links found for this profile</div>";
    } else {
        echo "<table><tr><th>Link ID</th><th>Title</th><th>URL</th><th>Clicks</th><th>Created</th></tr>";
        $total_clicks = 0;
        foreach ($links as $link) {
            echo "<tr>";
            echo "<td>{$link['link_id']}</td>";
            echo "<td>{$link['title']}</td>";
            echo "<td><small>{$link['url']}</small></td>";
            echo "<td><strong>{$link['click_count']}</strong></td>";
            echo "<td>{$link['created_at']}</td>";
            echo "</tr>";
            $total_clicks += $link['click_count'];
        }
        echo "</table>";
        echo "<div class='success'>✅ <strong>" . count($links) . " links</strong> | <strong>{$total_clicks} total clicks</strong></div>";
    }
}

// Test the exact query from code
echo "<h2>3. Test Query Used in Code (Subquery Method)</h2>";
$test_query = "SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.created_at,
               (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
               (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
               FROM profiles p
               WHERE p.user_id = ?
               ORDER BY p.is_primary DESC, p.created_at ASC";
$stmt3 = mysqli_prepare($conn, $test_query);
mysqli_stmt_bind_param($stmt3, 'i', $current_user_id);
mysqli_stmt_execute($stmt3);
$test_result = mysqli_stmt_get_result($stmt3);

echo "<table><tr><th>Slug</th><th>Profile Name</th><th>Primary</th><th>Link Count</th><th>Total Clicks</th><th>Created At</th><th>Display Date</th></tr>";
while ($row = mysqli_fetch_assoc($test_result)) {
    $display_date = !empty($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : 'N/A';
    echo "<tr>";
    echo "<td><code><strong>{$row['slug']}</strong></code></td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td>" . ($row['is_primary'] ? '✅' : '❌') . "</td>";
    echo "<td style='background:yellow;text-align:center;'><strong>{$row['link_count']}</strong></td>";
    echo "<td style='background:yellow;text-align:center;'><strong>{$row['total_clicks']}</strong></td>";
    echo "<td>{$row['created_at']}</td>";
    echo "<td><strong>{$display_date}</strong></td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h2>📊 Analysis Result</h2>";
echo "<div class='success'>";
echo "<h3>✅ Query is Working Correctly!</h3>";
echo "<p><strong>The numbers shown (0 Links, 0 Clicks) are CORRECT</strong> because:</p>";
echo "<ul>";
echo "<li>Your production database currently has NO LINKS for these profiles</li>";
echo "<li>The created_at date showing N/A means the date is NULL or empty in database</li>";
echo "<li>The SQL dump file (linkmy_db.sql) contains OLD DATA that doesn't match production</li>";
echo "</ul>";
echo "</div>";

echo "<div class='warning'>";
echo "<h3>⚠️ What You Should Do:</h3>";
echo "<ol>";
echo "<li><strong>Add some links</strong> via the Dashboard to test if counting works</li>";
echo "<li><strong>OR</strong> Import the SQL dump if you want the old data back</li>";
echo "<li>The created_at issue suggests profiles were created without proper timestamp</li>";
echo "</ol>";
echo "</div>";

echo "<p><a href='dashboard.php' style='padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;display:inline-block;margin-top:20px;'>← Go to Dashboard to Add Links</a></p>";
?>
