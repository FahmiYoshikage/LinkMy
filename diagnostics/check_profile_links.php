<?php
/**
 * Diagnostic Tool: Check Profile Links Data
 * Memeriksa apakah data links terhubung dengan benar ke profiles
 */

require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/db.php';

// Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$user_id = $_SESSION['user_id'];

echo "<h2>Profile & Links Diagnostic Tool</h2>";
echo "<hr>";

// Get user profiles
$profiles_query = "SELECT id, slug, name, is_active, created_at FROM profiles WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $profiles_query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$profiles_result = mysqli_stmt_get_result($stmt);

echo "<h3>Your Profiles:</h3>";
if (mysqli_num_rows($profiles_result) == 0) {
    echo "<p style='color: red;'>No profiles found!</p>";
} else {
    while ($profile = mysqli_fetch_assoc($profiles_result)) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px;'>";
        echo "<h4>Profile: {$profile['slug']} (ID: {$profile['id']})</h4>";
        echo "<p><strong>Name:</strong> {$profile['name']}<br>";
        echo "<strong>Active:</strong> " . ($profile['is_active'] ? 'Yes' : 'No') . "<br>";
        echo "<strong>Created:</strong> {$profile['created_at']}</p>";
        
        // Check links for this profile
        $links_query = "SELECT id, title, url, clicks, is_active, position FROM links WHERE profile_id = ? ORDER BY position ASC";
        $links_stmt = mysqli_prepare($conn, $links_query);
        mysqli_stmt_bind_param($links_stmt, 'i', $profile['id']);
        mysqli_stmt_execute($links_stmt);
        $links_result = mysqli_stmt_get_result($links_stmt);
        
        $link_count = mysqli_num_rows($links_result);
        $total_clicks = 0;
        
        echo "<h5>Links ({$link_count}):</h5>";
        
        if ($link_count == 0) {
            echo "<p style='color: orange;'>No links found for this profile.</p>";
            echo "<p><em>Solution: Add links via <a href='../admin/profiles.php'>Admin > Profiles</a></em></p>";
        } else {
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
            echo "<thead><tr><th>ID</th><th>Title</th><th>URL</th><th>Clicks</th><th>Active</th><th>Position</th></tr></thead>";
            echo "<tbody>";
            
            while ($link = mysqli_fetch_assoc($links_result)) {
                $total_clicks += $link['clicks'];
                echo "<tr>";
                echo "<td>{$link['id']}</td>";
                echo "<td>{$link['title']}</td>";
                echo "<td>" . substr($link['url'], 0, 50) . "...</td>";
                echo "<td>{$link['clicks']}</td>";
                echo "<td>" . ($link['is_active'] ? 'Yes' : 'No') . "</td>";
                echo "<td>{$link['position']}</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table>";
            echo "<p><strong>Total Clicks:</strong> {$total_clicks}</p>";
        }
        
        // Verify stats query
        $stats_query = "SELECT 
                        (SELECT COUNT(*) FROM links WHERE profile_id = ?) as link_count,
                        (SELECT COALESCE(SUM(clicks), 0) FROM links WHERE profile_id = ?) as total_clicks";
        $stats_stmt = mysqli_prepare($conn, $stats_query);
        mysqli_stmt_bind_param($stats_stmt, 'ii', $profile['id'], $profile['id']);
        mysqli_stmt_execute($stats_stmt);
        $stats_result = mysqli_stmt_get_result($stats_stmt);
        $stats = mysqli_fetch_assoc($stats_result);
        
        echo "<hr>";
        echo "<h5>Stats Verification (from subquery):</h5>";
        echo "<p><strong>Link Count:</strong> {$stats['link_count']}<br>";
        echo "<strong>Total Clicks:</strong> {$stats['total_clicks']}</p>";
        
        if ($link_count != $stats['link_count'] || $total_clicks != $stats['total_clicks']) {
            echo "<p style='color: red; font-weight: bold;'>⚠️ MISMATCH DETECTED!</p>";
        } else {
            echo "<p style='color: green; font-weight: bold;'>✅ Stats match correctly!</p>";
        }
        
        echo "</div>";
    }
}

// Check for orphaned links (links without valid profile_id)
echo "<hr>";
echo "<h3>Orphaned Links Check:</h3>";
$orphaned_query = "SELECT l.* FROM links l 
                   LEFT JOIN profiles p ON l.profile_id = p.id 
                   WHERE p.id IS NULL";
$orphaned_result = mysqli_query($conn, $orphaned_query);

if (mysqli_num_rows($orphaned_result) > 0) {
    echo "<p style='color: red;'>Found orphaned links (links without valid profile):</p>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<thead><tr><th>Link ID</th><th>Profile ID</th><th>Title</th><th>URL</th></tr></thead>";
    echo "<tbody>";
    
    while ($orphan = mysqli_fetch_assoc($orphaned_result)) {
        echo "<tr>";
        echo "<td>{$orphan['id']}</td>";
        echo "<td>{$orphan['profile_id']}</td>";
        echo "<td>{$orphan['title']}</td>";
        echo "<td>" . substr($orphan['url'], 0, 50) . "...</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p style='color: green;'>✅ No orphaned links found.</p>";
}

echo "<hr>";
echo "<p><a href='../admin/settings.php'>← Back to Settings</a></p>";
?>
