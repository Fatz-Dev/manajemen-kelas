<?php
// Include configuration file and authentication functions
require_once 'config.php';
require_once 'functions/auth_functions.php';
require_once 'functions/helpers.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Log out the user
    logout();
    
    // Set success message
    setAlert('Anda berhasil logout.', 'success');
}

// Redirect to login page
header('Location: ' . BASE_URL . '/login.php?logout=success');
exit;
?>
