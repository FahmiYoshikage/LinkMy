<?php
// Simple diagnostic file to check PHP errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Debug Test</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Test require files
echo "<h2>Testing Required Files:</h2>";

$files_to_test = [
    '../config/auth_check.php',
    '../config/db.php',
    'categories.php',
    'appearance.php',
    'profiles.php'
];

foreach ($files_to_test as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        echo "<p>✓ $file - EXISTS</p>";
        
        // Check for syntax errors
        $output = shell_exec("php -l " . escapeshellarg($full_path) . " 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<p style='color:green'>✓ $file - SYNTAX OK</p>";
        } else {
            echo "<p style='color:red'>✗ $file - SYNTAX ERROR:<br><pre>$output</pre></p>";
        }
    } else {
        echo "<p style='color:red'>✗ $file - NOT FOUND</p>";
    }
}

// Check if we can include auth without errors
echo "<h2>Testing Auth Include:</h2>";
try {
    ob_start();
    // Don't actually run auth, just check if file is readable
    $content = file_get_contents(__DIR__ . '/../config/auth_check.php');
    ob_end_clean();
    echo "<p style='color:green'>✓ auth_check.php is readable</p>";
} catch (Exception $e) {
    ob_end_clean();
    echo "<p style='color:red'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Memory & Settings:</h2>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "<p>Error Log: " . ini_get('error_log') . "</p>";

echo "<hr><p><strong>If you see this message, PHP is working!</strong></p>";
?>
