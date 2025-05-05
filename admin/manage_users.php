<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';

// Set required role
$requiredRole = ROLE_ADMIN;

// Include authentication check
require_once '../includes/auth_check.php';

// Process user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $fullName = trim($_POST['full_name']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $classId = !empty($_POST['class_id']) ? $_POST['class_id'] : null;
        
        // Validate input
        $errors = [];
        
        if (empty($username)) {
            $errors[] = 'Username tidak boleh kosong';
        } elseif (valueExists('users', 'username', $username)) {
            $errors[] = 'Username sudah digunakan';
        }
        
        if (empty($email)) {
            $errors[] = 'Email tidak boleh kosong';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif (valueExists('users', 'email', $email)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if (empty($role)) {
            $errors[] = 'Role harus dipilih';
        }
        
        if ($role === ROLE_STUDENT && empty($classId)) {
            $errors[] = 'Murid harus memiliki kelas';
        }
        
        // If no errors, create user
        if (empty($errors)) {
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $fullName,
                'role' => $role,
                'class_id' => $classId
            ];
            
            $userId = registerUser($userData);
            
            if ($userId) {
                setAlert('Pengguna berhasil dibuat.', 'success');
            } else {
                setAlert('Gagal membuat pengguna.', 'danger');
            }
        } else {
            setAlert('Error: ' . implode(', ', $errors), 'danger');
        }
    } elseif ($_POST['action'] === 'update_user') {
        $userId = $_POST['user_id'];
        $fullName = trim($_POST['full_name']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $classId = !empty($_POST['class_id']) ? $_POST['class_id'] : null;
        // Validate input
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email tidak boleh kosong';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } elseif (valueExists('users', 'email', $email, $userId)) {
            $errors[] = 'Email sudah digunakan';
        }
        
        if (empty($role)) {
            $errors[] = 'Role harus dipilih';
        }
        
        if ($role === ROLE_STUDENT && empty($classId)) {
            $errors[] = 'Murid harus memiliki kelas';
        }
        
        // If no errors, update user
        if (empty($errors)) {
            $userData = [
                'full_name' => $fullName,
                'email' => $email,
                'role' => $role,
                'class_id' => $classId
            ];
            
            if (update('users', $userData, 'id = ?', [$userId])) {
                setAlert('Pengguna berhasil diperbarui.', 'success');
            } else {
                setAlert('Gagal memperbarui pengguna.', 'danger');
            }
        } else {
            setAlert('Error: ' . implode(', ', $errors), 'danger');
        }
    } elseif ($_POST['action'] === 'reset_password') {
        $userId = $_POST['user_id'];
        $password = $_POST['password'];
        
        if (strlen($password) < 6) {
            setAlert('Password minimal 6 karakter', 'danger');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            if (update('users', ['password' => $hashedPassword], 'id = ?', [$userId])) {
                setAlert('Password berhasil direset.', 'success');
            } else {
                setAlert('Gagal mereset password.', 'danger');
            }
        }
    } elseif ($_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'];
        
        if (delete('users', 'id = ?', [$userId])) {
            setAlert('Pengguna berhasil dihapus.', 'success');
        } else {
            setAlert('Gagal menghapus pengguna.', 'danger');
        }
    }
    
    // Redirect to refresh the page and avoid resubmission
    redirect(BASE_URL . '/admin/manage_users.php' . (isset($_GET['role']) ? '?role=' . $_GET['role'] : ''));
}

// Get filter parameters
$roleFilter = getParam('role', 'all');
$searchQuery = getParam('search', '');
$page = getParam('page', 1);
$limit = 15;
$offset = ($page - 1) * $limit;

// Build query conditions
$conditions = [];
$params = [];
$types = '';

if ($roleFilter !== 'all') {
    $conditions[] = 'role = ?';
    $params[] = $roleFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $conditions[] = '(username LIKE ? OR full_name LIKE ? OR email LIKE ?)';
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count total users with filters
$countSql = "SELECT COUNT(*) as count FROM users $whereClause";
$countResult = fetchRow($countSql, $params, $types);
$totalUsers = $countResult ? $countResult['count'] : 0;

// Get users with pagination
$sql = "SELECT u.*, c.class_name 
        FROM users u 
        LEFT JOIN classes c ON u.class_id = c.id 
        $whereClause 
        ORDER BY u.created_at DESC 
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$users = fetchAll($sql, $params, $types);

// Get all classes for forms
$classes = getAllClasses();

// Setup pagination
$paginationData = getPaginationData(
    $totalUsers,
    $limit,
    $page,
    BASE_URL . '/admin/manage_users.php?role=' . $roleFilter . '&search=' . urlencode($searchQuery) . '&page=:page'
);

// Page details
$pageTitle = "Manajemen Pengguna";
$pageHeader = "Manajemen Pengguna";
$pageDescription = "Kelola pengguna dalam sistem";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Daftar Pengguna</h2>
        <p class="text-sm text-gray-500">Total: <?php echo $totalUsers; ?> pengguna</p>
    </div>
    
    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" data-modal="create-user-modal">
            <i class="fas fa-user-plus mr-2"></i> Tambah Pengguna
        </button>
    </div>
</div>

<!-- Filters and Search -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form action="<?php echo BASE_URL; ?>/admin/manage_users.php" method="get" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="flex-1">
            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Filter Role</label>
            <select id="role" name="role" onchange="this.form.submit()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>Semua Role</option>
                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                <option value="teacher" <?php echo $roleFilter === 'teacher' ? 'selected' : ''; ?>>Guru</option>
                <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Murid</option>
            </select>
        </div>
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Pengguna</label>
            <div class="relative rounded-md shadow-sm">
                <input type="text" id="search" name="search" value="<?php echo escape($searchQuery); ?>" placeholder="Cari username, nama, atau email..." class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-search text-gray-400"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- User List Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>

                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            <?php if (!empty($searchQuery)): ?>
                                Tidak ada pengguna yang sesuai dengan pencarian "<?php echo escape($searchQuery); ?>"
                            <?php else: ?>
                                Belum ada data pengguna
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($user['full_name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo escape($user['email']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo escape($user['username']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                switch ($user['role']) {
                                    case ROLE_ADMIN:
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Admin</span>';
                                        break;
                                    case ROLE_TEACHER:
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Guru</span>';
                                        break;
                                    case ROLE_STUDENT:
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Murid</span>';
                                        break;
                                    default:
                                        echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Unknown</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $user['class_name'] ? escape($user['class_name']) : '-'; ?>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDate($user['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button type="button" class="text-blue-600 hover:text-blue-900" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="text-yellow-600 hover:text-yellow-900" onclick="resetPassword(<?php echo $user['id']; ?>, '<?php echo escape($user['username']); ?>')">
                                        <i class="fas fa-key"></i> Reset
                                    </button>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo escape($user['username']); ?>')">
                                            <i class="fas fa-trash-alt"></i> Hapus
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalUsers > $limit): ?>
        <div class="px-6 py-4 border-t">
            <?php echo generatePagination($paginationData); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create User Modal -->
<div id="create-user-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_users.php" method="post">
                <input type="hidden" name="action" value="create_user">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Tambah Pengguna Baru
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                        <input type="text" name="username" id="username" required
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                        <input type="text" name="full_name" id="full_name" required
                                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="email" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                    <input type="password" name="password" id="password" required minlength="6"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                                        <select name="role" id="role" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                onchange="toggleClassField()">
                                            <option value="">Pilih Role</option>
                                            <option value="admin">Administrator</option>
                                            <option value="teacher">Guru</option>
                                            <option value="student">Murid</option>
                                        </select>
                                    </div>
                                    <div id="class_field" style="display: none;">
                                        <label for="class_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                                        <select name="class_id" id="class_id"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>"><?php echo escape($class['class_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_users.php" method="post">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="edit_user_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Pengguna
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="edit_full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                    <input type="text" name="full_name" id="edit_full_name" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="email" id="edit_email" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="edit_role" class="block text-sm font-medium text-gray-700">Role</label>
                                        <select name="role" id="edit_role" required
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                onchange="toggleEditClassField()">
                                            <option value="admin">Administrator</option>
                                            <option value="teacher">Guru</option>
                                            <option value="student">Murid</option>
                                        </select>
                                    </div>
                                    <div id="edit_class_field" style="display: none;">
                                        <label for="edit_class_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                                        <select name="class_id" id="edit_class_id"
                                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>"><?php echo escape($class['class_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
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

<!-- Reset Password Modal -->
<div id="reset-password-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_users.php" method="post">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="reset_user_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-key text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Reset Password
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Anda akan mereset password untuk pengguna <span id="reset_username" class="font-medium"></span>.
                                </p>
                                <div class="mt-4">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                                    <input type="password" name="password" id="reset_password" required minlength="6"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reset Password
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div id="delete-user-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_users.php" method="post">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="delete_user_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Pengguna
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menghapus pengguna <span id="delete_username" class="font-medium"></span>? 
                                    Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait pengguna ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleClassField() {
        const roleSelect = document.getElementById('role');
        const classField = document.getElementById('class_field');
        const classSelect = document.getElementById('class_id');
        
        if (roleSelect.value === 'student') {
            classField.style.display = 'block';
            classSelect.setAttribute('required', 'required');
        } else {
            classField.style.display = 'none';
            classSelect.removeAttribute('required');
        }
    }
    
    function toggleEditClassField() {
        const roleSelect = document.getElementById('edit_role');
        const classField = document.getElementById('edit_class_field');
        const classSelect = document.getElementById('edit_class_id');
        
        if (roleSelect.value === 'student') {
            classField.style.display = 'block';
            classSelect.setAttribute('required', 'required');
        } else {
            classField.style.display = 'none';
            classSelect.removeAttribute('required');
        }
    }
    
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.id;
        document.getElementById('edit_full_name').value = user.full_name;
        document.getElementById('edit_email').value = user.email;
        document.getElementById('edit_role').value = user.role;
        
        if (user.class_id) {
            document.getElementById('edit_class_id').value = user.class_id;
        } else {
            document.getElementById('edit_class_id').value = '';
        }
        
        toggleEditClassField();
        
        // Show modal
        const modal = document.getElementById('edit-user-modal');
        modal.classList.remove('hidden');
    }
    
    function resetPassword(userId, username) {
        document.getElementById('reset_user_id').value = userId;
        document.getElementById('reset_username').textContent = username;
        
        // Show modal
        const modal = document.getElementById('reset-password-modal');
        modal.classList.remove('hidden');
    }
    
    function deleteUser(userId, username) {
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('delete_username').textContent = username;
        
        // Show modal
        const modal = document.getElementById('delete-user-modal');
        modal.classList.remove('hidden');
    }
    
    // Initialize class field visibility
    document.addEventListener('DOMContentLoaded', function() {
        toggleClassField();
    });
</script>

<?php include '../includes/footer.php'; ?>
