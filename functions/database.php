<?php
/**
 * Database functions for the application
 */

// Include configuration file if not already included
if (!defined('DB_SERVER')) {
    require_once __DIR__ . '/../config.php';
}

/**
 * Execute SQL query with prepared statement
 */
function executeQuery($sql, $params = [], $types = null) {
    global $conn;

    try {
        if (!$conn->ping()) {
            $conn->close();
            require_once __DIR__ . '/../config.php';
        }

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }

        if (!empty($params)) {
            if ($types === null) {
                $types = str_repeat('s', count($params));
            }
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }

        return $stmt;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Fetch a single row from database
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
 */
function countRows($table, $where = '1', $params = []) {
    $sql = "SELECT COUNT(*) AS count FROM $table WHERE $where";

    $row = fetchRow($sql, $params);

    return $row ? $row['count'] : 0;
}

/**
 * Check if value exists in database
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
 */
function beginTransaction() {
    global $conn;
    return $conn->begin_transaction();
}

/**
 * Commit transaction
 */
function commitTransaction() {
    global $conn;
    return $conn->commit();
}

/**
 * Rollback transaction
 */
function rollbackTransaction() {
    global $conn;
    return $conn->rollback();
}

/**
 * Generate pagination data
 */
function getPaginationData($totalItems, $itemsPerPage, $currentPage, $urlPattern) {
    $totalPages = ceil($totalItems / $itemsPerPage);

    if ($currentPage < 1) {
        $currentPage = 1;
    } else if ($currentPage > $totalPages && $totalPages > 0) {
        $currentPage = $totalPages;
    }

    $offset = ($currentPage - 1) * $itemsPerPage;

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