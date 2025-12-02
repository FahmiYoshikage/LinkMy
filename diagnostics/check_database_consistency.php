<?php
// Database consistency checker and fixer
require_once '../config/auth_check.php';
require_once '../config/db.php';

echo "<style>body{font-family:Arial;padding:20px;} .error{background:#f8d7da;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #dc3545;} .warning{background:#fff3cd;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #ffc107;} .success{background:#d4edda;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #28a745;} .info{background:#d1ecf1;padding:15px;border-radius:5px;margin:10px 0;border-left:4px solid #0dcaf0;} table{border-collapse:collapse;width:100%;margin:20px 0;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#667eea;color:white;} .btn{display:inline-block;padding:10px 20px;background:#667eea;color:white;text-decoration:none;border-radius:5px;margin:10px 5px 0 0;border:none;cursor:pointer;} .btn-danger{background:#dc3545;} .btn-success{background:#28a745;}</style>";

echo "<h1>🔍 Database Consistency Checker</h1>";
echo "<p><strong>Current User:</strong> {$current_user_id} ({$_SESSION['username']})</p>";
echo "<hr>";

// STEP 1: Check for inconsistencies
echo "<h2>Step 1: Checking for Issues</h2>";

$issues = [];

// Issue 1: Check if user has multiple primary profiles
$check1 = mysqli_query($conn, "
    SELECT user_id, COUNT(*) as primary_count 
    FROM profiles 
    WHERE is_primary = 1 
    GROUP BY user_id 
    HAVING primary_count > 1
");

echo "<h3>Issue 1: Multiple Primary Profiles per User</h3>";
if (mysqli_num_rows($check1) > 0) {
    echo "<div class='error'><strong>❌ FOUND ISSUES!</strong></div>";
    echo "<table><tr><th>User ID</th><th>Primary Profile Count</th></tr>";
    while ($row = mysqli_fetch_assoc($check1)) {
        echo "<tr><td>{$row['user_id']}</td><td><strong>{$row['primary_count']}</strong></td></tr>";
        $issues[] = "user_{$row['user_id']}_multiple_primary";
    }
    echo "</table>";
} else {
    echo "<div class='success'>✅ No multiple primary profiles found</div>";
}

// Issue 2: Check if user has NO primary profile
$check2 = mysqli_query($conn, "
    SELECT DISTINCT p.user_id, u.username
    FROM profiles p
    LEFT JOIN users u ON p.user_id = u.user_id
    WHERE p.user_id NOT IN (
        SELECT user_id FROM profiles WHERE is_primary = 1
    )
    GROUP BY p.user_id, u.username
");

echo "<h3>Issue 2: Users Without Primary Profile</h3>";
if (mysqli_num_rows($check2) > 0) {
    echo "<div class='error'><strong>❌ FOUND ISSUES!</strong></div>";
    echo "<table><tr><th>User ID</th><th>Username</th></tr>";
    while ($row = mysqli_fetch_assoc($check2)) {
        echo "<tr><td>{$row['user_id']}</td><td>{$row['username']}</td></tr>";
        $issues[] = "user_{$row['user_id']}_no_primary";
    }
    echo "</table>";
} else {
    echo "<div class='success'>✅ All users have a primary profile</div>";
}

// Issue 3: Check is_primary vs users.page_slug mismatch
$check3 = mysqli_query($conn, "
    SELECT u.user_id, u.username, u.page_slug, 
           p.profile_id, p.slug, p.is_primary
    FROM users u
    LEFT JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1
    WHERE u.page_slug != p.slug OR p.slug IS NULL
");

echo "<h3>Issue 3: Primary Profile vs users.page_slug Mismatch</h3>";
if (mysqli_num_rows($check3) > 0) {
    echo "<div class='error'><strong>❌ FOUND MISMATCHES!</strong></div>";
    echo "<table><tr><th>User ID</th><th>Username</th><th>users.page_slug</th><th>Primary Profile Slug</th></tr>";
    while ($row = mysqli_fetch_assoc($check3)) {
        echo "<tr><td>{$row['user_id']}</td><td>{$row['username']}</td><td><code>{$row['page_slug']}</code></td><td><code>" . ($row['slug'] ?? 'NULL') . "</code></td></tr>";
        $issues[] = "user_{$row['user_id']}_slug_mismatch";
    }
    echo "</table>";
} else {
    echo "<div class='success'>✅ All primary profiles match users.page_slug</div>";
}

// Issue 4: Check current user's specific data
echo "<h3>Issue 4: Current User ({$current_user_id}) Profile Details</h3>";
$user_check = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM links WHERE profile_id = p.profile_id) as link_count,
           (SELECT COALESCE(SUM(click_count), 0) FROM links WHERE profile_id = p.profile_id) as total_clicks
    FROM profiles p
    WHERE p.user_id = {$current_user_id}
    ORDER BY p.is_primary DESC, p.created_at ASC
");

echo "<table>";
echo "<tr><th>Profile ID</th><th>Slug</th><th>Name</th><th>is_primary</th><th>Links</th><th>Clicks</th><th>Status</th></tr>";
$primary_count = 0;
while ($row = mysqli_fetch_assoc($user_check)) {
    $status_class = $row['is_primary'] ? 'success' : 'warning';
    $status_text = $row['is_primary'] ? '✅ PRIMARY' : '⚠️ SECONDARY';
    echo "<tr style='background:" . ($row['is_primary'] ? '#d4edda' : '#fff3cd') . "'>";
    echo "<td><strong>{$row['profile_id']}</strong></td>";
    echo "<td><code>{$row['slug']}</code></td>";
    echo "<td>{$row['profile_name']}</td>";
    echo "<td><strong>{$row['is_primary']}</strong></td>";
    echo "<td><strong>{$row['link_count']}</strong></td>";
    echo "<td><strong>{$row['total_clicks']}</strong></td>";
    echo "<td>{$status_text}</td>";
    echo "</tr>";
    if ($row['is_primary']) $primary_count++;
}
echo "</table>";

if ($primary_count == 0) {
    echo "<div class='error'><strong>❌ CRITICAL:</strong> You have NO primary profile!</div>";
    $issues[] = "current_user_no_primary";
} elseif ($primary_count > 1) {
    echo "<div class='error'><strong>❌ CRITICAL:</strong> You have {$primary_count} primary profiles (should be only 1)!</div>";
    $issues[] = "current_user_multiple_primary";
} else {
    echo "<div class='success'>✅ You have exactly 1 primary profile</div>";
}

// STEP 2: Show summary and offer fix
echo "<hr>";
echo "<h2>Step 2: Summary & Action</h2>";

if (empty($issues)) {
    echo "<div class='success'>";
    echo "<h3>🎉 All Good!</h3>";
    echo "<p>No database inconsistencies found. Everything is properly configured.</p>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Found " . count($issues) . " issue(s)</h3>";
    echo "<p>Database inconsistencies detected. Click the button below to automatically fix them.</p>";
    echo "</div>";
    
    echo "<form method='POST'>";
    echo "<button type='submit' name='auto_fix' class='btn btn-danger'>🔧 Auto-Fix All Issues</button>";
    echo "<a href='settings.php' class='btn'>← Back to Settings</a>";
    echo "</form>";
}

// STEP 3: Auto-fix if requested
if (isset($_POST['auto_fix'])) {
    echo "<hr>";
    echo "<h2>Step 3: Auto-Fixing Issues</h2>";
    
    $fixed = [];
    
    // Fix 1: Users with multiple primary profiles - keep the oldest one
    $multi_primary = mysqli_query($conn, "
        SELECT user_id, COUNT(*) as cnt 
        FROM profiles 
        WHERE is_primary = 1 
        GROUP BY user_id 
        HAVING cnt > 1
    ");
    
    while ($user = mysqli_fetch_assoc($multi_primary)) {
        $uid = $user['user_id'];
        
        // Get the oldest primary profile
        $oldest = mysqli_query($conn, "
            SELECT profile_id FROM profiles 
            WHERE user_id = {$uid} AND is_primary = 1 
            ORDER BY created_at ASC LIMIT 1
        ");
        $keep = mysqli_fetch_assoc($oldest)['profile_id'];
        
        // Set all others to non-primary
        mysqli_query($conn, "
            UPDATE profiles 
            SET is_primary = 0 
            WHERE user_id = {$uid} AND is_primary = 1 AND profile_id != {$keep}
        ");
        
        $fixed[] = "Fixed user {$uid}: kept profile {$keep} as primary";
        echo "<div class='success'>✅ Fixed user {$uid}: kept profile {$keep} as primary</div>";
    }
    
    // Fix 2: Users without primary profile - set the oldest one as primary
    $no_primary = mysqli_query($conn, "
        SELECT DISTINCT user_id 
        FROM profiles 
        WHERE user_id NOT IN (SELECT user_id FROM profiles WHERE is_primary = 1)
    ");
    
    while ($user = mysqli_fetch_assoc($no_primary)) {
        $uid = $user['user_id'];
        
        // Get the oldest profile
        $oldest = mysqli_query($conn, "
            SELECT profile_id FROM profiles 
            WHERE user_id = {$uid} 
            ORDER BY created_at ASC LIMIT 1
        ");
        $make_primary = mysqli_fetch_assoc($oldest)['profile_id'];
        
        // Set as primary
        mysqli_query($conn, "UPDATE profiles SET is_primary = 1 WHERE profile_id = {$make_primary}");
        
        $fixed[] = "Fixed user {$uid}: set profile {$make_primary} as primary";
        echo "<div class='success'>✅ Fixed user {$uid}: set profile {$make_primary} as primary</div>";
    }
    
    // Fix 3: Sync users.page_slug with primary profile slug
    mysqli_query($conn, "
        UPDATE users u
        JOIN profiles p ON u.user_id = p.user_id AND p.is_primary = 1
        SET u.page_slug = p.slug, u.active_profile_id = p.profile_id
        WHERE u.page_slug != p.slug OR u.active_profile_id != p.profile_id
    ");
    
    if (mysqli_affected_rows($conn) > 0) {
        $fixed[] = "Synced users.page_slug with primary profile slugs";
        echo "<div class='success'>✅ Synced users.page_slug with primary profile slugs (" . mysqli_affected_rows($conn) . " users)</div>";
    }
    
    echo "<hr>";
    if (!empty($fixed)) {
        echo "<div class='success'>";
        echo "<h3>✅ Fixed " . count($fixed) . " issue(s)</h3>";
        echo "<ul>";
        foreach ($fixed as $fix) {
            echo "<li>{$fix}</li>";
        }
        echo "</ul>";
        echo "<p><strong>Please refresh settings.php and profiles.php to see the changes.</strong></p>";
        echo "</div>";
        
        echo "<a href='settings.php' class='btn btn-success'>✅ Go to Settings</a>";
        echo "<a href='profiles.php' class='btn btn-success'>✅ Go to Profiles</a>";
        echo "<a href='?reload=1' class='btn'>🔄 Re-check Database</a>";
    } else {
        echo "<div class='info'>ℹ️ No fixes needed</div>";
    }
}
?>
