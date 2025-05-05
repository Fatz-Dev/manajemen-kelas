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
$pageTitle = "Registrasi";
$hideNavigation = false;
?>

<?php include 'includes/header.php'; ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100 py-8">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-blue-600 text-white text-center">
                <h2 class="text-2xl font-bold">Registrasi Akun</h2>
                <p class="text-blue-200">Buat akun baru untuk mulai menggunakan aplikasi</p>
            </div>
            
            <form action="<?php echo BASE_URL; ?>/auth/register_process.php" method="post" class="py-6 px-8" data-validate>
                <?php if (isset($_GET['error'])): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-md">
                        <?php if ($_GET['error'] === 'username'): ?>
                            Username sudah digunakan. Silahkan pilih username lain.
                        <?php elseif ($_GET['error'] === 'email'): ?>
                            Email sudah terdaftar. Silahkan gunakan email lain.
                        <?php elseif ($_GET['error'] === 'password'): ?>
                            Password tidak cukup kuat. Minimal 6 karakter.
                        <?php else: ?>
                            Terjadi kesalahan. Silahkan coba lagi.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label for="full_name" class="block text-gray-700 text-sm font-medium mb-2">Nama Lengkap</label>
                    <input type="text" id="full_name" name="full_name" placeholder="Masukkan nama lengkap" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="full_name-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="username-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" placeholder="Masukkan email" required
                           class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div id="email-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-4">
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
                    <p class="text-xs text-gray-500 mt-1">Password minimal 6 karakter</p>
                </div>
                
                <div class="mb-6">
                    <label for="password_confirm" class="block text-gray-700 text-sm font-medium mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Masukkan password kembali" required
                               class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="button" id="toggle-password-confirm" onclick="togglePasswordVisibility('password_confirm', 'toggle-password-confirm')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 focus:outline-none">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div id="password_confirm-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div class="mb-6">
                    <label for="role" class="block text-gray-700 text-sm font-medium mb-2">Daftar Sebagai</label>
                    <select id="role" name="role" required
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Role</option>
                        <option value="teacher">Guru</option>
                        <option value="student">Murid</option>
                    </select>
                    <div id="role-error" class="text-red-500 text-xs mt-1"></div>
                    <p class="text-xs text-gray-500 mt-1">Administrator hanya dapat dibuat melalui sistem</p>
                </div>
                
                <div class="mb-6">
                    <label for="class_id" class="block text-gray-700 text-sm font-medium mb-2">Kelas (Untuk Murid)</label>
                    <select id="class_id" name="class_id"
                            class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Kelas (Opsional untuk Murid)</option>
                        <?php
                        // Get all classes
                        require_once 'functions/class_functions.php';
                        $classes = getAllClasses();
                        
                        foreach ($classes as $class) {
                            echo '<option value="' . $class['id'] . '">' . escape($class['class_name']) . '</option>';
                        }
                        ?>
                    </select>
                    <div id="class_id-error" class="text-red-500 text-xs mt-1"></div>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Daftar
                    </button>
                </div>
            </form>
            
            <div class="py-4 px-8 bg-gray-50 border-t text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="<?php echo BASE_URL; ?>/login.php" class="text-blue-600 hover:underline">
                        Login disini
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    // Show/hide class selection based on role
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const classSelect = document.getElementById('class_id');
        const classGroup = classSelect.parentElement;
        
        function toggleClassSelect() {
            if (roleSelect.value === 'student') {
                classGroup.classList.remove('hidden');
                classSelect.setAttribute('required', 'required');
            } else {
                classGroup.classList.add('hidden');
                classSelect.removeAttribute('required');
            }
        }
        
        // Initial state
        toggleClassSelect();
        
        // On change
        roleSelect.addEventListener('change', toggleClassSelect);
    });
</script>

<?php include 'includes/footer.php'; ?>
