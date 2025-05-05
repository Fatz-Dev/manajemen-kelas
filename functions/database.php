<?php
/**
 * Database functions for the application
 * File ini menampung fungsi database utama aplikasi untuk MySQL
 */

// Include configuration file if not already included
if (!defined('DB_SERVER')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Execute SQL query with prepared statement
 * @param string $sql The SQL query
 * @param array $params Array of parameters
 * @param string $types The types of parameters (i for integer, s for string, d for double, b for blob)
 * @return mysqli_stmt|false Returns mysqli_stmt object or false on failure
 */
function executeQuery($sql, $params = [], $types = null) {
    global $conn;
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Error preparing statement: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        if ($types === null) {
            $types = str_repeat('s', count($params));
        }
        
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error executing statement: " . $stmt->error);
        $stmt->close();
        return false;
    }
    
    return $stmt;
}

/**
 * Fetch a single row from database
 * @param string $sql The SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types
 * @return array|null Returns associative array or null if not found
 */
function fetchRow($sql, $params = [], $types = null) {
    $stmt = executeQuery($sql, $params, $types);
    
    if ($stmt === false) {
        return null;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row;
}

/**
 * Fetch multiple rows from database
 * @param string $sql The SQL query
 * @param array $params Parameters for prepared statement
 * @param string $types Parameter types
 * @return array Returns array of associative arrays
 */
function fetchAll($sql, $params = [], $types = null) {
    $stmt = executeQuery($sql, $params, $types);
    
    if ($stmt === false) {
        return [];
    }
    
    $result = $stmt->get_result();
    $rows = [];
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    $stmt->close();
    
    return $rows;
}

/**
 * Insert data into database
 * @param string $table Table name
 * @param array $data Associative array of column => value
 * @return int|false The inserted ID or false on failure
 */
function insert($table, $data) {
    global $conn;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $params = array_values($data);
    
    $stmt = executeQuery($sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    $id = $conn->insert_id;
    $stmt->close();
    
    return $id;
}

/**
 * Update data in database
 * @param string $table Table name
 * @param array $data Associative array of column => value to update
 * @param string $where WHERE clause
 * @param array $whereParams Parameters for WHERE clause
 * @return bool Success or failure
 */
function update($table, $data, $where, $whereParams = []) {
    $set = [];
    foreach (array_keys($data) as $column) {
        $set[] = "$column = ?";
    }
    
    $setClause = implode(', ', $set);
    $sql = "UPDATE $table SET $setClause WHERE $where";
    
    $params = array_merge(array_values($data), $whereParams);
    
    $stmt = executeQuery($sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    $success = ($stmt->affected_rows > 0);
    $stmt->close();
    
    return $success;
}

/**
 * Delete data from database
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return bool Success or failure
 */
function delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    
    $stmt = executeQuery($sql, $params);
    
    if ($stmt === false) {
        return false;
    }
    
    $success = ($stmt->affected_rows > 0);
    $stmt->close();
    
    return $success;
}

/**
 * Count rows in database
 * @param string $table Table name
 * @param string $where WHERE clause
 * @param array $params Parameters for WHERE clause
 * @return int Number of rows
 */
function countRows($table, $where = '1', $params = []) {
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE $where";
    
    $row = fetchRow($sql, $params);
    
    return $row ? $row['count'] : 0;
}

/**
 * Check if value exists in database
 * @param string $table Table name
 * @param string $column Column name
 * @param mixed $value Value to check
 * @param int $excludeId Optional ID to exclude from check
 * @return bool True if exists, false otherwise
 */
function valueExists($table, $column, $value, $excludeId = null) {
    $sql = "SELECT 1 FROM $table WHERE $column = ?";
    $params = [$value];
    
    if ($excludeId !== null) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $result = fetchRow($sql, $params);
    
    return ($result !== null);
}

/**
 * Begin transaction
 * @return bool Success or failure
 */
function beginTransaction() {
    global $conn;
    return $conn->begin_transaction();
}

/**
 * Commit transaction
 * @return bool Success or failure
 */
function commitTransaction() {
    global $conn;
    return $conn->commit();
}

/**
 * Rollback transaction
 * @return bool Success or failure
 */
function rollbackTransaction() {
    global $conn;
    return $conn->rollback();
}

/**
 * Generate pagination links
 * @param int $totalItems Total number of items
 * @param int $itemsPerPage Items per page
 * @param int $currentPage Current page
 * @param string $urlPattern URL pattern with :page placeholder
 * @return array Pagination data
 */
function getPaginationData($totalItems, $itemsPerPage, $currentPage, $urlPattern) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    if ($currentPage < 1) {
        $currentPage = 1;
    } else if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
    }
    
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    // Generate links
    $links = [];
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $links[] = [
            'page' => $i,
            'url' => str_replace(':page', $i, $urlPattern),
            'current' => ($i == $currentPage)
        ];
    }
    
    return [
        'totalItems' => $totalItems,
        'itemsPerPage' => $itemsPerPage,
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'links' => $links,
        'prevPage' => ($currentPage > 1) ? str_replace(':page', $currentPage - 1, $urlPattern) : null,
        'nextPage' => ($currentPage < $totalPages) ? str_replace(':page', $currentPage + 1, $urlPattern) : null
    ];
}
?>
