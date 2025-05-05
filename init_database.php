<?php
/**
 * Database initialization script for local XAMPP environment
 * This script helps to create and initialize the database in MySQL
 */

// Database connection parameters
$host = 'localhost';
$user = 'root';
$password = '';  // Default for XAMPP is empty

// Create connection
try {
    // Connect without database selection first
    $conn = new mysqli($host, $user, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Try to create database
    $sql = "CREATE DATABASE IF NOT EXISTS manajemen_kelas_db";
    if ($conn->query($sql) === TRUE) {
        echo "<p>Database created successfully or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db('manajemen_kelas_db');
    
    // Read the SQL file
    $sqlFile = file_get_contents('manajemen_kelas_db.sql');
    
    if ($sqlFile === false) {
        throw new Exception("Could not read SQL file");
    }
    
    // Split SQL file into separate queries
    $queries = explode(';', $sqlFile);
    
    // Execute each query
    $success = true;
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        
        if ($conn->query($query) === FALSE) {
            echo "<p>Error executing query: $query<br>" . $conn->error . "</p>";
            $success = false;
        }
    }
    
    if ($success) {
        echo "<p>Database initialized successfully!</p>";
        echo "<p>Default login credentials:</p>";
        echo "<ul>";
        echo "<li>Admin: username <strong>admin</strong>, password <strong>admin123</strong></li>";
        echo "<li>Teacher: username <strong>guru1</strong>, password <strong>admin123</strong></li>";
        echo "<li>Student: username <strong>siswa1</strong>, password <strong>admin123</strong></li>";
        echo "</ul>";
        echo "<p><a href='index.php'>Go to application</a></p>";
    }
    
    // Close connection
    $conn->close();
    
} catch (Exception $e) {
    die("<p>ERROR: " . $e->getMessage() . "</p>");
}
?>