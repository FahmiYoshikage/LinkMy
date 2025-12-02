<?php
require_once 'config/db.php';

echo "<h2>Verifikasi Kolom is_active</h2>";

// Check if is_active column exists in profiles table
$result = mysqli_query($conn, "DESCRIBE profiles");
echo "<h3>Struktur tabel profiles:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "<td>{$row['Extra']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check current is_active values
$result = mysqli_query($conn, "SELECT id, slug, name, is_active FROM profiles ORDER BY id");
echo "<h3>Data is_active di profiles:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Slug</th><th>Name</th><th>is_active</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['slug']}</td>";
    echo "<td>" . ($row['name'] ?? 'NULL') . "</td>";
    echo "<td>" . ($row['is_active'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// If is_active doesn't exist, show SQL to add it
$column_exists = false;
$result = mysqli_query($conn, "SHOW COLUMNS FROM profiles LIKE 'is_active'");
if (mysqli_num_rows($result) > 0) {
    $column_exists = true;
    echo "<p style='color: green;'>✅ Kolom is_active SUDAH ADA di tabel profiles!</p>";
} else {
    echo "<p style='color: red;'>❌ Kolom is_active TIDAK ADA di tabel profiles!</p>";
    echo "<h3>SQL untuk menambahkan kolom:</h3>";
    echo "<pre>ALTER TABLE profiles ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER display_order;</pre>";
    echo "<form method='post'>";
    echo "<button type='submit' name='add_column' style='padding: 10px 20px; background: #28a745; color: white; border: none; cursor: pointer;'>Tambahkan Kolom is_active</button>";
    echo "</form>";
    
    if (isset($_POST['add_column'])) {
        $sql = "ALTER TABLE profiles ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER display_order";
        if (mysqli_query($conn, $sql)) {
            echo "<p style='color: green;'>✅ Kolom is_active berhasil ditambahkan!</p>";
            echo "<script>window.location.reload();</script>";
        } else {
            echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>
