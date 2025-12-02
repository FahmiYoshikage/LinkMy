<?php
// Direct database check - NO AUTH
$conn = mysqli_connect('localhost', 'root', '', 'linkmy_db');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>🔍 Direct Database Investigation</h2>";
echo "<style>table{border-collapse:collapse;width:100%;margin:20px 0} th,td{border:1px solid #ddd;padding:8px;text-align:left} th{background:#667eea;color:white} tr:nth-child(even){background:#f2f2f2}</style>";

// Find user_id = 6 (fahmi)
echo "<h3>1. User Profile (user_id = 6)</h3>";
$q1 = mysqli_query($conn, "SELECT * FROM profiles WHERE user_id = 6");
echo "<table><tr><th>profile_id</th><th>user_id</th><th>slug</th><th>profile_name</th><th>is_primary</th><th>created_at</th></tr>";
$profile_ids = [];
while ($row = mysqli_fetch_assoc($q1)) {
    echo "<tr>";
    echo "<td><strong>{$row['profile_id']}</strong></td>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td><strong>{$row['slug']}</strong></td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td>" . ($row['is_primary'] ? '✅' : '❌') . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
    $profile_ids[] = $row['profile_id'];
}
echo "</table>";

// Check links for these profiles
echo "<h3>2. Links for User 6's Profiles</h3>";
if (!empty($profile_ids)) {
    $ids = implode(',', $profile_ids);
    $q2 = mysqli_query($conn, "SELECT link_id, profile_id, user_id, title, click_count FROM links WHERE profile_id IN ($ids) ORDER BY profile_id");
    echo "<table><tr><th>link_id</th><th>profile_id</th><th>user_id</th><th>title</th><th>click_count</th></tr>";
    
    $stats = [];
    while ($row = mysqli_fetch_assoc($q2)) {
        echo "<tr>";
        echo "<td>{$row['link_id']}</td>";
        echo "<td><strong>{$row['profile_id']}</strong></td>";
        echo "<td>{$row['user_id']}</td>";
        echo "<td>{$row['title']}</td>";
        echo "<td>{$row['click_count']}</td>";
        echo "</tr>";
        
        if (!isset($stats[$row['profile_id']])) {
            $stats[$row['profile_id']] = ['count' => 0, 'clicks' => 0];
        }
        $stats[$row['profile_id']]['count']++;
        $stats[$row['profile_id']]['clicks'] += $row['click_count'];
    }
    echo "</table>";
    
    echo "<h3>3. Calculated Stats per Profile</h3>";
    echo "<table><tr><th>profile_id</th><th>Link Count</th><th>Total Clicks</th></tr>";
    foreach ($profile_ids as $pid) {
        $count = $stats[$pid]['count'] ?? 0;
        $clicks = $stats[$pid]['clicks'] ?? 0;
        echo "<tr><td><strong>{$pid}</strong></td><td>{$count}</td><td>{$clicks}</td></tr>";
    }
    echo "</table>";
}

// Test the exact query used in code
echo "<h3>4. Test Exact Query (Subquery Method)</h3>";
$q3 = mysqli_query($conn, "
    SELECT p.profile_id, p.slug, p.profile_name, p.is_primary, p.created_at,
    (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
    (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
    FROM profiles p
    WHERE p.user_id = 6
    ORDER BY p.is_primary DESC, p.created_at ASC
");

echo "<table><tr><th>profile_id</th><th>slug</th><th>profile_name</th><th>is_primary</th><th>link_count</th><th>total_clicks</th><th>created_at</th></tr>";
while ($row = mysqli_fetch_assoc($q3)) {
    echo "<tr>";
    echo "<td><strong>{$row['profile_id']}</strong></td>";
    echo "<td>{$row['slug']}</td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td>" . ($row['is_primary'] ? '✅' : '❌') . "</td>";
    echo "<td style='background:yellow;'><strong>{$row['link_count']}</strong></td>";
    echo "<td style='background:yellow;'><strong>{$row['total_clicks']}</strong></td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check if there's data mismatch in user_id
echo "<h3>5. Check user_id Mismatch in Links</h3>";
$q4 = mysqli_query($conn, "
    SELECT l.link_id, l.user_id as link_user_id, l.profile_id, p.user_id as profile_user_id, l.title
    FROM links l
    LEFT JOIN profiles p ON l.profile_id = p.profile_id
    WHERE p.user_id = 6
    LIMIT 10
");
echo "<table><tr><th>link_id</th><th>link.user_id</th><th>profile_id</th><th>profile.user_id</th><th>Match?</th><th>title</th></tr>";
while ($row = mysqli_fetch_assoc($q4)) {
    $match = $row['link_user_id'] == $row['profile_user_id'] ? '✅' : '❌ MISMATCH!';
    echo "<tr>";
    echo "<td>{$row['link_id']}</td>";
    echo "<td>{$row['link_user_id']}</td>";
    echo "<td>{$row['profile_id']}</td>";
    echo "<td>{$row['profile_user_id']}</td>";
    echo "<td>{$match}</td>";
    echo "<td>{$row['title']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr><p><strong>KEY FINDINGS:</strong></p>";
echo "<ul>";
echo "<li>If Test 4 shows correct numbers → Query is working, issue is in PHP code</li>";
echo "<li>If Test 4 shows 0s → Database has no links for these profiles</li>";
echo "<li>If Test 5 shows MISMATCH → Links have wrong user_id or profile_id</li>";
echo "</ul>";

mysqli_close($conn);
?>
