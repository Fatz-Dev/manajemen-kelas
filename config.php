<?php
/**
 * Configuration file for Manajemen Kelas application
 * Configured for both PostgreSQL (Replit) and MySQL (XAMPP)
 */

// Detect environment and set appropriate database connection
if (getenv('REPL_ID') || getenv('DATABASE_URL')) {
    // We're in Replit, use PostgreSQL
    $databaseUrl = getenv('DATABASE_URL');
    
    if ($databaseUrl) {
        $dbParts = parse_url($databaseUrl);
        
        define('DB_TYPE', 'pgsql');
        define('DB_SERVER', $dbParts['host']);
        define('DB_PORT', $dbParts['port']);
        define('DB_USERNAME', $dbParts['user']);
        define('DB_PASSWORD', $dbParts['pass']);
        define('DB_NAME', ltrim($dbParts['path'], '/'));
        
        try {
            // Use PDO for PostgreSQL connection
            $dsn = "pgsql:host=" . DB_SERVER . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            $conn = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("ERROR: Could not connect to database. " . $e->getMessage());
        }
    } else {
        die("ERROR: No DATABASE_URL environment variable found.");
    }
} else {
    // We're in local XAMPP environment, use MySQL
    define('DB_TYPE', 'mysql');
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'manajemen_kelas_db');
    
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set character set
        $conn->set_charset("utf8mb4");
        
    } catch (Exception $e) {
        die("ERROR: Could not connect to database. " . $e->getMessage());
    }
}

// Application Settings
define('APP_NAME', 'Manajemen Kelas');

// Set BASE_URL based on environment
if (getenv('REPL_ID')) {
    // We're in Replit
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('REPL_SLUG') . '.' . getenv('REPL_OWNER') . '.repl.co';
    define('BASE_URL', $protocol . $domain);
} else {
    // Local XAMPP environment
    define('BASE_URL', 'http://localhost/manajemen_kelas');
}

define('APP_VERSION', '1.0.0');

// Session Configuration
session_start();

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');

// Function to get active URI path
function get_active_uri() {
    $uri = $_SERVER['REQUEST_URI'];
    $base_path = parse_url(BASE_URL, PHP_URL_PATH);
    if ($base_path && strpos($uri, $base_path) === 0) {
        $uri = substr($uri, strlen($base_path));
    }
    return ltrim($uri, '/');
}

// Error reporting settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
