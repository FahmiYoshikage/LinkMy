<?php
/**
 * Debug Profile - Shows detailed errors
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Debug Profile Page</h2>";
echo "<pre>";

try {
    echo "1. Loading db.php...\n";
    require_once 'config/db.php';
    echo "✓ Database connected\n\n";

    // Get slug
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : 'fahmi';
    echo "2. Testing slug: $slug\n\n";

    // Test basic user query
    echo "3. Testing v_public_page_data view...\n";
    $query = "SELECT * FROM v_public_page_data WHERE page_slug = ?";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        die("ERROR preparing statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        die("ERROR getting result: " . mysqli_error($conn));
    }
    
    $user_data = mysqli_fetch_assoc($result);
    
    if (!$user_data) {
        die("ERROR: No user found with slug '$slug'");
    }
    
    echo "✓ User found: " . $user_data['username'] . " (ID: " . $user_data['user_id'] . ")\n";
    echo "✓ Boxed Layout: " . ($user_data['boxed_layout'] ?? 'NULL') . "\n\n";
    
    mysqli_stmt_close($stmt);
    
    // Test links query
    echo "4. Testing links query...\n";
    $user_id = $user_data['user_id'];
    
    $links_query = "SELECT l.id as link_id, l.title, l.url, l.icon, l.category_id, l.display_order,
                    c.name as category_name, c.icon as category_icon, 
                    c.color as category_color, c.is_expanded as category_expanded
                    FROM links l
                    LEFT JOIN categories c ON l.category_id = c.id
                    WHERE l.user_id = ? AND l.is_visible = 1
                    ORDER BY l.display_order ASC";
    
    $stmt_links = mysqli_prepare($conn, $links_query);
    
    if (!$stmt_links) {
        die("ERROR preparing links statement: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt_links, 'i', $user_id);
    mysqli_stmt_execute($stmt_links);
    $links_result = mysqli_stmt_get_result($stmt_links);
    
    if (!$links_result) {
        die("ERROR getting links result: " . mysqli_error($conn));
    }
    
    $link_count = mysqli_num_rows($links_result);
    echo "✓ Found $link_count link(s)\n\n";
    
    if ($link_count > 0) {
        echo "Links:\n";
        while ($link = mysqli_fetch_assoc($links_result)) {
            echo "  - {$link['title']} ({$link['url']})\n";
        }
    }
    
    mysqli_stmt_close($stmt_links);
    
    echo "\n5. All tests passed! ✓\n";
    echo "\nNow testing actual profile.php include...\n";
    echo "===========================================\n\n";
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";

// Now try to include actual profile.php
echo "<hr><h3>Loading actual profile.php:</h3>";
echo "<iframe src='profile.php?slug=" . htmlspecialchars($_GET['slug'] ?? 'fahmi') . "' style='width:100%; height:600px; border:1px solid #ccc;'></iframe>";
?>
