<?php
// Database Configuration for MySQL/XAMPP
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'manajemen_kelas_db');

// Attempt to connect to MySQL database
try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage());
}

// Application Settings
define('APP_NAME', 'Manajemen Kelas');
define('BASE_URL', 'http://localhost/manajemen_kelas');
define('APP_VERSION', '1.0.0');

// Session Configuration
session_start();

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');


// Error reporting settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>