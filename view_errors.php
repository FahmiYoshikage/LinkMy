<?php
/**
 * Check PHP Error Logs from Docker Container
 */

header('Content-Type: text/plain');

echo "=== PHP ERROR LOG VIEWER ===\n\n";

// Try different log locations
$log_locations = [
    '/var/log/apache2/error.log',
    '/var/log/php_errors.log',
    '/var/log/php-fpm/error.log',
    'php://stderr',
    ini_get('error_log')
];

echo "PHP Error Log Setting: " . ini_get('error_log') . "\n";
echo "Display Errors: " . ini_get('display_errors') . "\n";
echo "Error Reporting: " . ini_get('error_reporting') . "\n\n";

echo "=== CHECKING LOG LOCATIONS ===\n\n";

foreach ($log_locations as $log) {
    if (empty($log) || $log == 'php://stderr') continue;
    
    echo "Checking: $log\n";
    
    if (file_exists($log)) {
        echo "  STATUS: EXISTS\n";
        if (is_readable($log)) {
            echo "  READABLE: YES\n";
            $size = filesize($log);
            echo "  SIZE: " . number_format($size) . " bytes\n";
            
            if ($size > 0) {
                echo "\n  LAST 50 LINES:\n";
                echo "  " . str_repeat("-", 70) . "\n";
                $lines = file($log);
                $last_lines = array_slice($lines, -50);
                foreach ($last_lines as $line) {
                    echo "  " . $line;
                }
                echo "  " . str_repeat("-", 70) . "\n";
            } else {
                echo "  FILE IS EMPTY\n";
            }
        } else {
            echo "  READABLE: NO (Permission denied)\n";
        }
    } else {
        echo "  STATUS: NOT FOUND\n";
    }
    echo "\n";
}

echo "\n=== RECENT PHP ERRORS (if any) ===\n\n";

// Try to get last error
$last_error = error_get_last();
if ($last_error) {
    echo "Type: " . $last_error['type'] . "\n";
    echo "Message: " . $last_error['message'] . "\n";
    echo "File: " . $last_error['file'] . "\n";
    echo "Line: " . $last_error['line'] . "\n";
} else {
    echo "No recent PHP errors recorded.\n";
}

echo "\n=== SYSTEM INFO ===\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server API: " . php_sapi_name() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

echo "\n=== TO VIEW DOCKER LOGS ===\n\n";
echo "Run on VPS:\n";
echo "  docker logs linkmy_web --tail 100\n";
echo "  docker logs linkmy_web --follow\n";
echo "  docker exec linkmy_web tail -f /var/log/apache2/error.log\n";
?>
