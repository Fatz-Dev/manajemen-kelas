<?php
/**
 * Configuration file for Manajemen Kelas application
 * Contains database credentials and global settings for Replit environment
 */

// Database Configuration using PostgreSQL on Replit
$pghost = getenv('PGHOST');
$pgport = getenv('PGPORT');
$pgdatabase = getenv('PGDATABASE');
$pguser = getenv('PGUSER');
$pgpassword = getenv('PGPASSWORD');

// Define constants for compatibility with existing code
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'manajemen_kelas_db');

// Create PostgreSQL to MySQL compatibility layer
class PgSQLResult {
    private $result;
    public $num_rows;
    
    public function __construct($result) {
        $this->result = $result;
        $this->num_rows = pg_num_rows($result);
    }
    
    public function fetch_assoc() {
        return pg_fetch_assoc($this->result);
    }
    
    public function fetch_array() {
        return pg_fetch_array($this->result);
    }
    
    public function free() {
        return pg_free_result($this->result);
    }
}

class PgSQLStatement {
    private $conn;
    private $sql;
    private $params = [];
    private $types = '';
    public $affected_rows;
    public $result;
    
    public function __construct($connection, $sql) {
        $this->conn = $connection;
        $this->sql = $sql;
    }
    
    public function bind_param($types, ...$params) {
        $this->types = $types;
        $this->params = $params;
        return true;
    }
    
    public function execute() {
        // Convert params based on types
        $convertedParams = [];
        for ($i = 0; $i < strlen($this->types); $i++) {
            $type = $this->types[$i];
            $value = $this->params[$i];
            
            switch ($type) {
                case 'i': // integer
                    $convertedParams[] = (int)$value;
                    break;
                case 'd': // double
                    $convertedParams[] = (float)$value;
                    break;
                case 's': // string
                default:
                    $convertedParams[] = $value;
                    break;
            }
        }
        
        $this->result = pg_query_params($this->conn, $this->sql, $convertedParams);
        if (!$this->result) {
            error_log("Error executing statement: " . pg_last_error($this->conn));
            return false;
        }
        
        $this->affected_rows = pg_affected_rows($this->result);
        return true;
    }
    
    public function get_result() {
        return new PgSQLResult($this->result);
    }
    
    public function close() {
        // PostgreSQL statements don't need explicit closing
        return true;
    }
}

class PgSQLEmulator {
    private $conn;
    public $insert_id;
    public $affected_rows;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function query($sql) {
        // Convert MySQL syntax to PostgreSQL
        $sql = str_replace('`', '"', $sql); // Replace backticks with double quotes
        $sql = preg_replace('/AUTO_INCREMENT/', 'SERIAL', $sql);
        $sql = preg_replace('/INT\([0-9]+\)/', 'INTEGER', $sql);
        $sql = str_replace('UNSIGNED', '', $sql); // Remove UNSIGNED keyword
        $sql = preg_replace('/ENUM\s*\([^)]+\)/', 'VARCHAR(255)', $sql); // Replace ENUM with VARCHAR
        $sql = str_replace('ENGINE=InnoDB', '', $sql); // Remove ENGINE definition
        $sql = str_replace('DEFAULT CHARSET=utf8mb4', '', $sql); // Remove charset definition
        $sql = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $sql); // Remove ON UPDATE
        
        $result = pg_query($this->conn, $sql);
        if (!$result) {
            error_log("Error executing query: " . pg_last_error($this->conn));
            return false;
        }
        
        // For INSERT queries, get the last insert ID
        if (stripos($sql, 'INSERT') === 0) {
            $this->insert_id = $this->getLastInsertId($sql);
        }
        
        // For UPDATE/DELETE queries, get affected rows
        if (stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0) {
            $this->affected_rows = pg_affected_rows($result);
        }
        
        return new PgSQLResult($result);
    }
    
    private function getLastInsertId($sql) {
        // Extract table name from INSERT query
        if (preg_match('/INSERT\s+INTO\s+(\w+)/i', $sql, $matches)) {
            $table = $matches[1];
            $result = pg_query($this->conn, "SELECT CURRVAL(pg_get_serial_sequence('$table', 'id')) as last_id");
            if ($result && $row = pg_fetch_assoc($result)) {
                return $row['last_id'];
            }
        }
        return 0;
    }
    
    public function prepare($sql) {
        // Convert MySQL placeholders (?) to PostgreSQL placeholders ($1, $2, etc.)
        $count = 0;
        $sql = preg_replace_callback('/\?/', function($matches) use (&$count) {
            $count++;
            return '$' . $count;
        }, $sql);
        
        $sql = str_replace('`', '"', $sql); // Replace backticks with double quotes
        
        return new PgSQLStatement($this->conn, $sql);
    }
    
    public function real_escape_string($string) {
        return pg_escape_string($this->conn, $string);
    }
    
    public function select_db($dbname) {
        // PostgreSQL requires new connection to switch databases
        return true; // For compatibility, just return true
    }
}

// Attempt to connect to PostgreSQL database (as MySQL substitute)
try {
    $conn_string = "host=$pghost port=$pgport dbname=$pgdatabase user=$pguser password=$pgpassword";
    $conn = pg_connect($conn_string);
    
    // Check connection
    if (!$conn) {
        throw new Exception("Connection failed: " . pg_last_error());
    }
    
    // Create mysqli compatibility wrapper
    $conn = new PgSQLEmulator($conn);
    
} catch (Exception $e) {
    die("ERROR: Could not connect to database. " . $e->getMessage());
}

// Application Settings
define('APP_NAME', 'Manajemen Kelas');
// Use the Replit URL for the base URL
$replit_url = getenv('REPL_SLUG') ? 'https://' . getenv('REPL_SLUG') . '.' . getenv('REPL_OWNER') . '.repl.co' : 'http://localhost:5000';
define('BASE_URL', $replit_url);
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
    // Just return the relative path for Replit environment
    $uri = $_SERVER['REQUEST_URI'];
    return ltrim($uri, '/');
}

// Error reporting settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>