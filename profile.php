<?php
// Include configuration and functions
require_once 'config.php';
require_once 'functions/helpers.php';
require_once 'functions/auth_functions.php';
require_once 'functions/class_functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    setAlert('Anda harus login terlebih dahulu.', 'danger');
    redirect(BASE_URL . '/login.php');
}

// Get current user data
$user = getCurrentUser();

// Get class data if user is a student or teacher
if ($user['role'] === ROLE_STUDENT || $user['role'] === ROLE_TEACHER) {
    if (!empty($user['class_id'])) {
        $class = getClassById($user['class_id']);
    }
}

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        
        // Validate input
        $errors = [];
        
        if (empty($fullName)) {
            $errors[] = 'Nama lengkap tidak boleh kosong';
        }
        
        if (empty($email)) {
            $errors[] = 'Email tidak boleh kosong';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif (valueExists('users', 'email', $email, $user['id'])) {
            $errors[] = 'Email sudah digunakan';
        }
        
        // If no errors, update profile
        if (empty($errors)) {
            $userData = [
                'full_name' => $fullName,
                'email' => $email
            ];
            
            if (updateProfile($user['id'], $userData)) {
                setAlert('Profil berhasil diperbarui.', 'success');
                // Redirect to refresh the page and show the updated info
                redirect(BASE_URL . '/profile.php');
            } else {
                setAlert('Gagal memperbarui profil.', 'danger');
            }
        } else {
            setAlert('Error: ' . implode(', ', $errors), 'danger');
        }
    } elseif ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        // Validate input
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Password saat ini tidak boleh kosong';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'Password baru tidak boleh kosong';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }
        
        // If no errors, change password
        if (empty($errors)) {
            if (changePassword($user['id'], $currentPassword, $newPassword)) {
                setAlert('Password berhasil diubah.', 'success');
                // Redirect to refresh the page
                redirect(BASE_URL . '/profile.php');
            } else {
                setAlert('Password saat ini tidak benar atau gagal mengubah password.', 'danger');
            }
        } else {
            setAlert('Error: ' . implode(', ', $errors), 'danger');
        }
    }
}

// Set page title and description
$pageTitle = "Profil Pengguna";
$pageHeader = "Profil Pengguna";
$pageDescription = "Lihat dan perbarui informasi profil Anda";
$showSidebar = true;

// Include header
include 'includes/header.php';
?>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Profile Information Card -->
    <div class="bg-white rounded-lg shadow-md p-6 flex-1">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Profil</h2>
        
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0 h-24 w-24 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-4xl text-blue-600"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
            </div>
            <div class="ml-6">
                <h3 class="text-lg font-medium text-gray-900"><?php echo escape($user['full_name']); ?></h3>
                <p class="text-sm text-gray-500"><?php echo escape($user['username']); ?></p>
                <div class="mt-1">
                    <?php 
                    $roleLabel = '';
                    $roleClass = '';
                    switch ($user['role']) {
                        case ROLE_ADMIN:
                            $roleLabel = 'Administrator';
                            $roleClass = 'bg-purple-100 text-purple-800';
                            break;
                        case ROLE_TEACHER:
                            $roleLabel = 'Guru';
                            $roleClass = 'bg-green-100 text-green-800';
                            break;
                        case ROLE_STUDENT:
                            $roleLabel = 'Murid';
                            $roleClass = 'bg-blue-100 text-blue-800';
                            break;
                    }
                    ?>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $roleClass; ?>">
                        <?php echo $roleLabel; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-200 py-4">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo escape($user['email']); ?></dd>
                </div>
                <?php if ($user['role'] === ROLE_STUDENT && isset($class)): ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo escape($class['class_name']); ?></dd>
                </div>
                <?php elseif ($user['role'] === ROLE_TEACHER): ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Wali Kelas</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php 
                        if (isset($class)) {
                            echo escape($class['class_name']);
                        } else {
                            echo '-';
                        }
                        ?>
                    </dd>
                </div>
                <?php endif; ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Terdaftar Pada</dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo formatDate($user['created_at']); ?></dd>
                </div>
            </dl>
        </div>
        
        <div class="mt-4">
            <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" data-modal="edit-profile-modal">
                <i class="fas fa-edit mr-2"></i> Edit Profil
            </button>
            <button type="button" class="ml-2 bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2" data-modal="change-password-modal">
                <i class="fas fa-key mr-2"></i> Ubah Password
            </button>
        </div>
    </div>
    
    <?php if ($user['role'] === ROLE_STUDENT): ?>
    <!-- Student Stats Card -->
    <div class="bg-white rounded-lg shadow-md p-6 flex-1">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistik Belajar</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-blue-700">Tugas</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total Tugas</p>
                        <p class="text-2xl font-bold text-blue-700">12</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 75%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">9 dari 12 tugas selesai</p>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-green-700">Nilai</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Rata-rata</p>
                        <p class="text-2xl font-bold text-green-700">85</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-green-600"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 h-2 rounded-full" style="width: 85%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Nilai tertinggi: 92</p>
                </div>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-yellow-700">Mata Pelajaran</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-yellow-700">8</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-book text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">Matematika, IPA, Bahasa Indonesia, dll.</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-purple-700">Kehadiran</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Persentase</p>
                        <p class="text-2xl font-bold text-purple-700">95%</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-check text-purple-600"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-600 h-2 rounded-full" style="width: 95%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">19 dari 20 hari hadir</p>
                </div>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-right mr-1"></i> Lihat semua tugas
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($user['role'] === ROLE_TEACHER): ?>
    <!-- Teacher Stats Card -->
    <div class="bg-white rounded-lg shadow-md p-6 flex-1">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistik Mengajar</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-blue-700">Tugas</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total Tugas</p>
                        <p class="text-2xl font-bold text-blue-700">24</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-tasks text-blue-600"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 25%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">6 tugas aktif, 18 selesai</p>
                </div>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-green-700">Kelas</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total Kelas</p>
                        <p class="text-2xl font-bold text-green-700">5</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chalkboard text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">Mengajar 5 kelas berbeda</p>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-yellow-700">Mata Pelajaran</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-yellow-700">3</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-book text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">Matematika, Fisika, Kimia</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-purple-700">Murid</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total Murid</p>
                        <p class="text-2xl font-bold text-purple-700">142</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-user-graduate text-purple-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">Dari 5 kelas berbeda</p>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-right mr-1"></i> Kelola tugas
            </a>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if ($user['role'] === ROLE_ADMIN): ?>
    <!-- Admin Stats Card -->
    <div class="bg-white rounded-lg shadow-md p-6 flex-1">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistik Sistem</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-blue-700">Pengguna</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-blue-700">156</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">8 Admin, 12 Guru, 136 Murid</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-green-700">Kelas</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-green-700">12</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-chalkboard text-green-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">12 kelas aktif</p>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-yellow-700">Mata Pelajaran</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-yellow-700">15</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-book text-yellow-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">15 mata pelajaran aktif</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-purple-700">Tugas</h3>
                <div class="flex justify-between items-center mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Total</p>
                        <p class="text-2xl font-bold text-purple-700">248</p>
                    </div>
                    <div class="h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-tasks text-purple-600"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-3">42 aktif, 206 selesai</p>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-right mr-1"></i> Kelola sistem
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/profile.php" method="post">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Profil
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                    <input type="text" name="full_name" id="full_name" required value="<?php echo escape($user['full_name']); ?>"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email" required value="<?php echo escape($user['email']); ?>"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div id="change-password-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/profile.php" method="post">
                <input type="hidden" name="action" value="change_password">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Ubah Password
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                                    <input type="password" name="current_password" id="current_password" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                                    <input type="password" name="new_password" id="new_password" required minlength="6"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter</p>
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                                    <input type="password" name="confirm_password" id="confirm_password" required minlength="6"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Ubah Password
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
