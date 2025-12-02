<?php
/**
 * Check Links Table Structure
 */

require_once 'config/db.php';

header('Content-Type: text/plain');

echo "=== LINKS TABLE STRUCTURE ===\n\n";

// Show table structure
$result = mysqli_query($conn, "DESCRIBE links");

if ($result) {
    echo "Column Name          | Type                | Null | Key | Default | Extra\n";
    echo str_repeat("-", 80) . "\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        printf("%-20s | %-18s | %-4s | %-3s | %-7s | %s\n",
            $row['Field'],
            $row['Type'],
            $row['Null'],
            $row['Key'],
            $row['Default'] ?? 'NULL',
            $row['Extra']
        );
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

echo "\n\n=== SAMPLE DATA ===\n\n";

// Get sample data
$result = mysqli_query($conn, "SELECT * FROM links LIMIT 3");

if ($result) {
    $first_row = true;
    while ($row = mysqli_fetch_assoc($result)) {
        if ($first_row) {
            echo "Columns: " . implode(", ", array_keys($row)) . "\n\n";
            $first_row = false;
        }
        print_r($row);
        echo "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
