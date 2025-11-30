<?php
// Script untuk menambahkan profile "triforce" yang hilang
require_once 'config/db.php';

// Check if triforce profile exists
$check = mysqli_query($conn, "SELECT * FROM profiles WHERE slug = 'triforce' AND user_id = 12");
if (mysqli_num_rows($check) > 0) {
    echo "Profile 'triforce' sudah ada!\n";
    $profile = mysqli_fetch_assoc($check);
    print_r($profile);
} else {
    echo "Profile 'triforce' tidak ditemukan. Menambahkan...\n";
    
    // Insert triforce profile
    $query = "INSERT INTO profiles (user_id, slug, profile_name, profile_description, profile_title, bio, is_primary, is_active) 
              VALUES (12, 'triforce', 'TRIFORCE', 'Profile untuk Triforce project', 'TRIFORCE', 'Triforce project links', 0, 1)";
    
    if (mysqli_query($conn, $query)) {
        $new_profile_id = mysqli_insert_id($conn);
        echo "✅ Profile 'triforce' berhasil ditambahkan dengan profile_id: $new_profile_id\n";
        
        // Pindahkan beberapa links ke profile triforce jika diperlukan
        // Misalnya link "Kas Triforce" (link_id 19)
        $update_link = "UPDATE links SET profile_id = $new_profile_id WHERE link_id = 19 AND user_id = 12";
        if (mysqli_query($conn, $update_link)) {
            echo "✅ Link 'Kas Triforce' dipindahkan ke profile triforce\n";
        }
        
        // Create default appearance for triforce profile
        $app_query = "INSERT INTO user_appearance (user_id, profile_id) VALUES (12, $new_profile_id)";
        if (mysqli_query($conn, $app_query)) {
            echo "✅ Appearance default untuk triforce profile dibuat\n";
        }
        
    } else {
        echo "❌ Error: " . mysqli_error($conn) . "\n";
    }
}

// Show all profiles for user 12
echo "\n=== Semua profiles user 12 ===\n";
$all_profiles = mysqli_query($conn, "SELECT p.*, COUNT(l.link_id) as link_count, COALESCE(SUM(l.click_count), 0) as total_clicks 
                                      FROM profiles p 
                                      LEFT JOIN links l ON p.profile_id = l.profile_id AND l.user_id = p.user_id 
                                      WHERE p.user_id = 12 
                                      GROUP BY p.profile_id 
                                      ORDER BY p.is_primary DESC");
while ($row = mysqli_fetch_assoc($all_profiles)) {
    echo "\n";
    echo "Profile: {$row['profile_name']} (slug: {$row['slug']})\n";
    echo "  - profile_id: {$row['profile_id']}\n";
    echo "  - is_primary: {$row['is_primary']}\n";
    echo "  - Links: {$row['link_count']}\n";
    echo "  - Clicks: {$row['total_clicks']}\n";
}

mysqli_close($conn);
?>
