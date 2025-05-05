<?php
// Include configuration file and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirect(BASE_URL . '/auth/password_reset.php?error=invalid');
        exit;
    }
    
    // Generate token
    $token = generatePasswordResetToken($email);
    
    if ($token) {
        // In a real application, you would send an email with the reset link
        // For this demo, we'll just redirect to the reset form
        // Normally, you would use PHPMailer or similar to send an actual email
        
        // Log the token for debugging (in production, you would not do this)
        error_log("Password reset token for $email: $token");
        
        // Redirect with success message
        redirect(BASE_URL . '/auth/password_reset.php?status=sent');
    } else {
        // No user found with this email, but for security reasons, don't disclose this
        // Just show the same message as if the email was sent
        redirect(BASE_URL . '/auth/password_reset.php?status=sent');
    }
} else {
    // Not a POST request
    redirect(BASE_URL . '/auth/password_reset.php');
}
