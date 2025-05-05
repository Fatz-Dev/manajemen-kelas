<?php
/**
 * Authentication check
 * Checks if user is logged in and has required role
 * Redirects to login page if not
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Set alert for user
    $_SESSION['alert'] = [
        'message' => 'Silahkan login terlebih dahulu',
        'type' => 'warning'
    ];
    
    // Redirect to login page
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

// Check if user has required role
if (isset($requiredRole) && $_SESSION['user_role'] !== $requiredRole) {
    // Check if user has any of the required roles (if requiredRole is an array)
    $hasRequiredRole = false;
    
    if (is_array($requiredRole)) {
        foreach ($requiredRole as $role) {
            if ($_SESSION['user_role'] === $role) {
                $hasRequiredRole = true;
                break;
            }
        }
    }
    
    if (!$hasRequiredRole) {
        // Set alert for user
        $_SESSION['alert'] = [
            'message' => 'Anda tidak memiliki akses ke halaman ini',
            'type' => 'danger'
        ];
        
        // Redirect based on role
        switch ($_SESSION['user_role']) {
            case 'admin':
                header('Location: ' . BASE_URL . '/admin/dashboard.php');
                break;
            case 'teacher':
                header('Location: ' . BASE_URL . '/teacher/dashboard.php');
                break;
            case 'student':
                header('Location: ' . BASE_URL . '/student/dashboard.php');
                break;
            default:
                header('Location: ' . BASE_URL . '/index.php');
        }
        exit;
    }
}
?>
