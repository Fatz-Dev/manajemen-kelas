<?php
/**
 * Configuration file for Manajemen Kelas application
 * Contains database credentials and global settings
 */

// Database Configuration - Using SQLite for Replit environment
define('DB_PATH', __DIR__ . '/database.sqlite');

// Attempt to connect to SQLite database
try {
    // Create a new SQLite database or open existing one
    $conn = new SQLite3(DB_PATH);
    
    // Enable exceptions for SQLite errors
    $conn->enableExceptions(true);
    
    // Create a mysqli compatibility wrapper
    class SQLite3_Compat {
        private $sqlite;
        public $insert_id;
        public $affected_rows;
        
        public function __construct($sqlite) {
            $this->sqlite = $sqlite;
        }
        
        public function query($sql) {
            // Convert MySQL-specific syntax to SQLite
            $sql = $this->convertToSQLite($sql);
            
            try {
                $result = $this->sqlite->query($sql);
                
                if ($result === false) {
                    error_log("Error executing query: " . $this->sqlite->lastErrorMsg());
                    return false;
                }
                
                // For INSERT queries, get the last insert ID
                if (stripos($sql, 'INSERT') === 0) {
                    $this->insert_id = $this->sqlite->lastInsertRowID();
                }
                
                // For UPDATE/DELETE queries, get affected rows
                if (stripos($sql, 'UPDATE') === 0 || stripos($sql, 'DELETE') === 0) {
                    $this->affected_rows = $this->sqlite->changes();
                }
                
                return new SQLite3_Result($result);
            } catch (Exception $e) {
                error_log("Query error: " . $e->getMessage());
                return false;
            }
        }
        
        public function prepare($sql) {
            // Convert MySQL-specific syntax to SQLite
            $sql = $this->convertToSQLite($sql);
            
            try {
                $stmt = $this->sqlite->prepare($sql);
                if ($stmt === false) {
                    error_log("Error preparing statement: " . $this->sqlite->lastErrorMsg());
                    return false;
                }
                return new SQLite3_Statement($stmt);
            } catch (Exception $e) {
                error_log("Prepare error: " . $e->getMessage());
                return false;
            }
        }
        
        public function real_escape_string($string) {
            return SQLite3::escapeString($string);
        }
        
        public function select_db($dbname) {
            // SQLite doesn't need to select database
            return true;
        }
        
        private function convertToSQLite($sql) {
            // Replace AUTO_INCREMENT with AUTOINCREMENT
            $sql = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $sql);
            
            // Replace ENGINE=InnoDB with empty string
            $sql = preg_replace('/ENGINE=InnoDB.*?;/', ';', $sql);
            
            // Replace character set declarations
            $sql = preg_replace('/DEFAULT CHARSET=([^\s;]+)/', '', $sql);
            
            // Replace UNSIGNED (not supported in SQLite)
            $sql = str_replace('UNSIGNED', '', $sql);
            
            // Replace ON UPDATE CURRENT_TIMESTAMP (not supported in SQLite)
            $sql = str_replace('ON UPDATE CURRENT_TIMESTAMP', '', $sql);
            
            // ENUM types are not supported in SQLite, convert to TEXT
            $sql = preg_replace('/ENUM\([^\)]+\)/', 'TEXT', $sql);
            
            return $sql;
        }
    }
    
    class SQLite3_Result {
        private $result;
        public $num_rows;
        
        public function __construct($result) {
            $this->result = $result;
            
            // Count rows for SELECT queries
            $count = 0;
            if ($this->result) {
                $current_position = $this->result->reset();
                while ($this->result->fetchArray()) {
                    $count++;
                }
                $this->result->reset();
                $this->num_rows = $count;
            } else {
                $this->num_rows = 0;
            }
        }
        
        public function fetch_assoc() {
            return $this->result ? $this->result->fetchArray(SQLITE3_ASSOC) : false;
        }
        
        public function fetch_array($mode = SQLITE3_BOTH) {
            return $this->result ? $this->result->fetchArray($mode) : false;
        }
        
        public function free() {
            // SQLite3Result doesn't need explicit freeing
            return true;
        }
    }
    
    class SQLite3_Statement {
        private $stmt;
        private $params = [];
        private $result;
        public $affected_rows;
        
        public function __construct($stmt) {
            $this->stmt = $stmt;
        }
        
        public function bind_param($types, ...$params) {
            for ($i = 0; $i < strlen($types); $i++) {
                $param_index = $i + 1; // SQLite parameter indices are 1-based
                $type = $types[$i];
                $value = $params[$i];
                
                switch ($type) {
                    case 'i': // integer
                        $this->stmt->bindValue($param_index, (int)$value, SQLITE3_INTEGER);
                        break;
                    case 'd': // double
                        $this->stmt->bindValue($param_index, (float)$value, SQLITE3_FLOAT);
                        break;
                    case 'b': // blob
                        $this->stmt->bindValue($param_index, $value, SQLITE3_BLOB);
                        break;
                    case 's': // string
                    default:
                        $this->stmt->bindValue($param_index, $value, SQLITE3_TEXT);
                        break;
                }
            }
            
            return true;
        }
        
        public function execute() {
            try {
                $this->result = $this->stmt->execute();
                
                if ($this->result === false) {
                    return false;
                }
                
                global $conn;
                $this->affected_rows = $conn->changes();
                return true;
            } catch (Exception $e) {
                error_log("Execute error: " . $e->getMessage());
                return false;
            }
        }
        
        public function get_result() {
            return new SQLite3_Result($this->result);
        }
        
        public function close() {
            // Close statement
            return $this->stmt->close();
        }
    }
    
    // Create compatibility wrapper
    $conn = new SQLite3_Compat($conn);
    
} catch (Exception $e) {
    die("ERROR: Could not connect to SQLite database. " . $e->getMessage());
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
