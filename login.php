<?php
// Include configuration file and functions
require_once 'config.php';
require_once 'functions/helpers.php';
require_once 'functions/auth_functions.php';

// If user is already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    
    if ($role === ROLE_ADMIN) {
        redirect(BASE_URL . '/admin/dashboard.php');
    } elseif ($role === ROLE_TEACHER) {
        redirect(BASE_URL . '/teacher/dashboard.php');
    } elseif ($role === ROLE_STUDENT) {
        redirect(BASE_URL . '/student/dashboard.php');
    } else {
        redirect(BASE_URL);
    }
}

// Page details
$pageTitle = "Login";
$hideNavigation = false;
?>

<?php include 'includes/header.php'; ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-blue-600 text-white text-center">
                <h2 class="text-2xl font-bold">Login</h2>
                <p class="text-blue-200">Silahkan masuk ke akun Anda</p>
            </div>
            
            <form action="<?php echo BASE_URL; ?>/auth/login_process.php" method="post" class="py-6 px-8" data-validate>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid'): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                        Username atau password salah. Silahkan coba lagi.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                        Anda berhasil logout.
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
                        Pendaftaran berhasil! Silahkan login.
                    </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="username-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required
                               class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" id="toggle-password" onclick="togglePasswordVisibility('password', 'toggle-password')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="password-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-6">
                    <label for="role" class="block text-gray-700 text-sm font-medium mb-2">Login Sebagai</label>
                    <select id="role" name="role" required
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Role</option>
                        <option value="admin">Administrator</option>
                        <option value="teacher">Guru</option>
                        <option value="student">Murid</option>
                    </select>
                    <div id="role-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                    </div>
                    
                    <a href="<?php echo BASE_URL; ?>/auth/password_reset.php" class="text-sm text-blue-600 hover:underline">
                        Lupa password?
                    </a>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Login
                    </button>
                </div>
            </form>
            
            <div class="py-4 px-8 bg-gray-50 border-t text-center">
                <p class="text-sm text-gray-600">
                    Belum punya akun?
                    <a href="<?php echo BASE_URL; ?>/register.php" class="text-blue-600 hover:underline">
                        Daftar disini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
