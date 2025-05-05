<?php
// Initialize SQLite database with schema and sample data

// Path to SQLite database
$db_path = __DIR__ . '/database.sqlite';

// Path to SQL schema file
$schema_file = __DIR__ . '/database.sqlite.sql';

// Check if database exists, if yes, delete it to start fresh
if (file_exists($db_path)) {
    unlink($db_path);
    echo "Existing database removed.<br>";
}

// Create new SQLite database
try {
    $db = new SQLite3($db_path);
    echo "New database created successfully.<br>";
    
    // Enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Read and execute SQL schema
    $sql = file_get_contents($schema_file);
    
    // Split SQL into individual statements
    $statements = explode(';', $sql);
    
    // Execute each statement
    foreach ($statements as $statement) {
        $statement = trim($statement);
        
        if (!empty($statement)) {
            if ($db->exec($statement) === false) {
                echo "Error executing statement: " . $db->lastErrorMsg() . "<br>";
            }
        }
    }
    
    echo "Database schema and sample data created successfully.<br>";
    echo "<br>You can now <a href=\"index.php\">go to the application</a>.";
    
    // Close database connection
    $db->close();
    
} catch (Exception $e) {
    echo "Error creating database: " . $e->getMessage();
}
?>