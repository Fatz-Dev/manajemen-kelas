
<?php
// Include configuration file and functions
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions/helpers.php';
require_once __DIR__ . '/../functions/auth_functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : '';
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        redirect(BASE_URL . '/login.php?error=invalid');
        exit;
    }
    
    // Attempt authentication
    $user = authenticate($username, $password);
    
    if ($user && $user['role'] === $role) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_full_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        if ($user['role'] === 'student' && isset($user['class_id'])) {
            $_SESSION['user_class_id'] = $user['class_id'];
        }
        
        // Remember me functionality
        if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
            $expiry = time() + (30 * 24 * 60 * 60); // 30 days
            setcookie('remember_user', $user['id'], $expiry, '/');
        }
        
        // Redirect to appropriate dashboard
        if ($user['role'] === ROLE_ADMIN) {
            redirect(BASE_URL . '/admin/dashboard.php');
        } elseif ($user['role'] === ROLE_TEACHER) {
            redirect(BASE_URL . '/teacher/dashboard.php');
        } elseif ($user['role'] === ROLE_STUDENT) {
            redirect(BASE_URL . '/student/dashboard.php');
        } else {
            redirect(BASE_URL);
        }
    } else {
        // Authentication failed
        redirect(BASE_URL . '/login.php?error=invalid');
    }
} else {
    // Not a POST request
    redirect(BASE_URL . '/login.php');
}
