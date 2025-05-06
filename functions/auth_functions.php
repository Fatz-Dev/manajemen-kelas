<?php
/**
 * Authentication functions for the application
 */

// Include database functions
require_once __DIR__ . '/database.php';

/**
 * Authenticate user with username and password
 * @param string $username Username
 * @param string $password Password
 * @return array|false User data or false if authentication fails
 */
function authenticate($username, $password) {
    $sql = "SELECT id, username, password, full_name, email, role, class_id, status 
            FROM users 
            WHERE username = ? AND status = 'active'";
    
    $user = fetchRow($sql, [$username]);
    
    if (!$user) {
        return false;
    }
    
    if (!password_verify($password, $user['password'])) {
        return false;
    }
    
    // Update last login timestamp (only if column exists)
    try {
        $updateSql = "UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
        executeQuery($updateSql, [$user['id']]);
    } catch (Exception $e) {
        // Column might not exist, just continue
    }
    
    // Remove password from the result
    unset($user['password']);
    
    return $user;
}

/**
 * Register a new user
 * @param array $userData User data
 * @return int|false User ID or false if registration fails
 */
function registerUser($userData) {
    // Check if username already exists
    if (valueExists('users', 'username', $userData['username'])) {
        return false;
    }
    
    // Check if email already exists
    if (valueExists('users', 'email', $userData['email'])) {
        return false;
    }
    
    // Hash password
    $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    // Insert user
    return insert('users', $userData);
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $sql = "SELECT id, username, full_name, email, role, class_id 
            FROM users 
            WHERE id = ?";
    
    return fetchRow($sql, [$_SESSION['user_id']]);
}

/**
 * Check if current user has specific role
 * @param string|array $roles Role or array of roles
 * @return bool True if has role, false otherwise
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Log out current user
 * @return void
 */
function logout() {
    // Destroy session data
    $_SESSION = [];
    
    // If session cookie is used, destroy it
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy session
    session_destroy();
}

/**
 * Generate password reset token
 * @param string $email User email
 * @return string|false Token or false if failed
 */
function generatePasswordResetToken($email) {
    $user = fetchRow("SELECT id FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        return false;
    }
    
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Store the token (This would typically be in a password_resets table)
    // For this basic implementation, we'll use a session
    $_SESSION['password_reset'] = [
        'user_id' => $user['id'],
        'token' => $token,
        'expires' => $expires
    ];
    
    return $token;
}

/**
 * Reset password using token
 * @param string $token Token
 * @param string $password New password
 * @return bool Success or failure
 */
function resetPassword($token, $password) {
    // Check if token exists and is valid
    if (!isset($_SESSION['password_reset']) || 
        $_SESSION['password_reset']['token'] !== $token ||
        strtotime($_SESSION['password_reset']['expires']) < time()) {
        return false;
    }
    
    $userId = $_SESSION['password_reset']['user_id'];
    
    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Update password
    $result = update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);
    
    // Remove token
    unset($_SESSION['password_reset']);
    
    return $result;
}

/**
 * Update user profile
 * @param int $userId User ID
 * @param array $data Profile data to update
 * @return bool Success or failure
 */
function updateProfile($userId, $data) {
    // Ensure that we're updating only allowed fields
    $allowedFields = ['full_name', 'email'];
    $updateData = array_intersect_key($data, array_flip($allowedFields));
    
    if (empty($updateData)) {
        return false;
    }
    
    return update('users', $updateData, 'id = ?', [$userId]);
}

/**
 * Change user password
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return bool Success or failure
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Get current password hash
    $user = fetchRow("SELECT password FROM users WHERE id = ?", [$userId]);
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return false;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    return update('users', ['password' => $hashedPassword], 'id = ?', [$userId]);
}

/**
 * Get user by ID
 * @param int $userId User ID
 * @return array|null User data or null if not found
 */
function getUserById($userId) {
    $sql = "SELECT id, username, full_name, email, role, class_id, created_at 
            FROM users 
            WHERE id = ?";
    
    return fetchRow($sql, [$userId]);
}

/**
 * Get users by role
 * @param string $role User role
 * @param int $limit Limit
 * @param int $offset Offset
 * @return array List of users
 */
function getUsersByRole($role, $limit = 10, $offset = 0) {
    $sql = "SELECT id, username, full_name, email, role, class_id, created_at 
            FROM users 
            WHERE role = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";
    
    return fetchAll($sql, [$role, $limit, $offset], 'sii');
}

/**
 * Count users by role
 * @param string $role User role
 * @return int Number of users
 */
function countUsersByRole($role) {
    return countRows('users', 'role = ?', [$role]);
}
?>
