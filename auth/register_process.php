<?php
/**
 * Register Process for Manajemen Kelas application
 * This script handles new user registration
 */

// Include necessary files
require_once '../config.php'; // This includes $conn from config.php
require_once '../functions/auth_functions.php';
require_once '../functions/helpers.php';
require_once '../functions/database.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize_input($_POST['full_name']);
    $role = sanitize_input($_POST['role']);
    $class_id = isset($_POST['class_id']) ? (int) $_POST['class_id'] : null;
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    
    // Validation
    $errors = [];
    
    // Validate username (only alphanumeric, 4-20 characters)
    if (!preg_match('/^[a-zA-Z0-9]{4,20}$/', $username)) {
        $errors[] = "Username harus terdiri dari 4-20 karakter alfanumerik.";
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Username sudah digunakan. Silahkan pilih username lain.";
    }
    $stmt->close();
    
    // Validate password (min 8 characters, at least one letter and one number)
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/', $password)) {
        $errors[] = "Password harus minimal 8 karakter dan mengandung setidaknya 1 huruf dan 1 angka.";
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak sama.";
    }
    
    // Validate role
    if (!in_array($role, [ROLE_ADMIN, ROLE_TEACHER, ROLE_STUDENT])) {
        $errors[] = "Role tidak valid.";
    }
    
    // If student, class_id is required
    if ($role === ROLE_STUDENT && empty($class_id)) {
        $errors[] = "Kelas harus dipilih untuk akun Murid.";
    }
    
    // If there are errors, redirect back to register page with errors
    if (!empty($errors)) {
        $_SESSION['register_errors'] = $errors;
        $_SESSION['register_form_data'] = [
            'username' => $username,
            'full_name' => $full_name,
            'email' => $email,
            'role' => $role,
            'class_id' => $class_id
        ];
        redirect(BASE_URL . '/register.php');
        exit;
    }
    
    // If validation passes, hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, class_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $username, $hashed_password, $full_name, $email, $role, $class_id);
    
    if ($stmt->execute()) {
        // Registration successful
        redirect(BASE_URL . '/login.php?registered=success');
    } else {
        // Registration failed
        $_SESSION['register_errors'] = ["Terjadi kesalahan saat mendaftarkan akun. Silakan coba lagi."];
        redirect(BASE_URL . '/register.php');
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to register page
    redirect(BASE_URL . '/register.php');
}
?>