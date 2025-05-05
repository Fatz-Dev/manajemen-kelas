<?php
/**
 * Database helper functions using PDO for PostgreSQL
 * This file contains all database functions using PDO
 */

/**
 * Execute a query and return results as array
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types (not used in PDO but kept for compatibility)
 * @return array Query results
 */
function fetchAll($sql, $params = [], $types = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error and return empty array
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

/**
 * Execute a query and return single row as array
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types (not used in PDO but kept for compatibility)
 * @return array|null Single row or null if no results
 */
function fetchRow($sql, $params = [], $types = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    } catch (PDOException $e) {
        // Log error and return null
        error_log("Database error: " . $e->getMessage());
        return null;
    }
}

/**
 * Execute a query (INSERT, UPDATE, DELETE) and return success/failure
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types (not used in PDO but kept for compatibility)
 * @return bool Success or failure
 */
function executeQuery($sql, $params = [], $types = '') {
    global $conn;
    
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        // Log error and return false
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Insert data into a table and return the last inserted ID
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|false Last inserted ID or false on failure
 */
function insert($table, $data) {
    global $conn;
    
    // Build column names and placeholders
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    
    // Build query
    $sql = "INSERT INTO " . $table . " (" . implode(',', $columns) . ") "
         . "VALUES (" . implode(',', $placeholders) . ") RETURNING id";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_values($data));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['id']) ? $result['id'] : false;
    } catch (PDOException $e) {
        // Log error and return false
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update data in a table
 * @param string $table Table name
 * @param array $data Associative array of column => value to update
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success or failure
 */
function update($table, $data, $where, $params = []) {
    global $conn;
    
    // Build SET clause
    $set = [];
    $values = [];
    
    foreach ($data as $column => $value) {
        $set[] = "$column = ?";
        $values[] = $value;
    }
    
    // Combine values with where params
    $values = array_merge($values, $params);
    
    // Build query
    $sql = "UPDATE " . $table . " SET " . implode(', ', $set) . " WHERE " . $where;
    
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($values);
    } catch (PDOException $e) {
        // Log error and return false
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete data from a table
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success or failure
 */
function delete($table, $where, $params = []) {
    global $conn;
    
    // Build query
    $sql = "DELETE FROM " . $table . " WHERE " . $where;
    
    try {
        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        // Log error and return false
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

/**
 * Count rows in a table
 * @param string $table Table name
 * @param string $where WHERE clause (optional)
 * @param array $params Parameters for WHERE clause (optional)
 * @return int Number of rows
 */
function countRows($table, $where = '1=1', $params = []) {
    global $conn;
    
    // Build query
    $sql = "SELECT COUNT(*) as count FROM " . $table . " WHERE " . $where;
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($result['count']) ? (int)$result['count'] : 0;
    } catch (PDOException $e) {
        // Log error and return 0
        error_log("Database error: " . $e->getMessage());
        return 0;
    }
}
