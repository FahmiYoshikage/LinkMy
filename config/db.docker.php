<?php
/**
 * Database Configuration for Docker Environment
 * LinkMy v2.1
 * 
 * This file is used when running LinkMy in Docker containers.
 * The database service is accessed via the Docker network.
 */

// Database connection parameters
define('DB_HOST', 'linkmy-db');          // Docker service name
define('DB_USER', 'linkmy_user');        // MySQL user from docker-compose
define('DB_PASS', 'linkmy_pass');        // MySQL password from docker-compose
define('DB_NAME', 'linkmy_db');          // Database name

// Connection settings
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', 'utf8mb4_unicode_ci');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, DB_CHARSET);

// Optional: Set timezone
mysqli_query($conn, "SET time_zone = '+00:00'");
?>
