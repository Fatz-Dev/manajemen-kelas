<?php
/**
 * Configuration file for Manajemen Kelas application
 * Contains database credentials and global settings
 * This is a hybrid configuration for both MySQL (XAMPP) and PostgreSQL (Replit)
 */

// Check if running on Replit (PostgreSQL)
if (getenv('DATABASE_URL')) {
    // PostgreSQL database connection (for Replit)
    $databaseUrl = parse_url(getenv('DATABASE_URL'));
    
    try {
        // Create PDO connection for PostgreSQL
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s', 
            $databaseUrl['host'], 
            isset($databaseUrl['port']) ? $databaseUrl['port'] : 5432, 
            ltrim($databaseUrl['path'], '/'), 
            $databaseUrl['user'], 
            $databaseUrl['pass']
        );
        
        // Create connection
        $conn = new PDO($dsn);
        
        // Set PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Set PostgreSQL specific settings
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
        // Flag for PDO usage with PostgreSQL
        define('USE_PDO', true);
        define('DB_TYPE', 'pgsql');
        
    } catch (PDOException $e) {
        die("ERROR: Could not connect to PostgreSQL database. " . $e->getMessage());
    }
    
    // Override BASE_URL for Replit
    define('BASE_URL', isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'https://' : 'http://' . $_SERVER['HTTP_HOST']);
    
} else {
    // MySQL database connection (for XAMPP)
    define('DB_SERVER', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'manajemen_kelas_db');
    define('DB_TYPE', 'mysql');
    define('USE_PDO', false);
    
    // Attempt to connect to MySQL database
    try {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set character set
        $conn->set_charset("utf8mb4");
        
    } catch (Exception $e) {
        die("ERROR: Could not connect to MySQL database. " . $e->getMessage());
    }
    
    // Base URL for local XAMPP
    define('BASE_URL', 'http://localhost/manajemen_kelas');
}

// Application Settings
define('APP_NAME', 'Manajemen Kelas');
// BASE_URL is already defined above based on environment
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
