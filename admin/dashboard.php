<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';
require_once '../functions/assignment_functions.php';

// Set required role
$requiredRole = ROLE_ADMIN;

// Include authentication check
require_once '../includes/auth_check.php';

// Get counts for dashboard
$totalStudents = countUsersByRole(ROLE_STUDENT);
$totalTeachers = countUsersByRole(ROLE_TEACHER);
$totalClasses = countAllClasses();
$totalSubjects = countAllSubjects();

// Get recent users
$recentUsers = fetchAll(
    "SELECT id, username, full_name, email, role, created_at 
    FROM users 
    ORDER BY created_at DESC 
    LIMIT 5"
);

// Get recent assignments
$recentAssignments = fetchAll(
    "SELECT a.id, a.title, a.due_date, a.status, s.subject_name, c.class_name, u.full_name as teacher_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    JOIN classes c ON a.class_id = c.id
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
    LIMIT 5"
);

// Page details
$pageTitle = "Dashboard Admin";
$pageHeader = "Dashboard Admin";
$pageDescription = "Selamat datang di panel kontrol administrator";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Total Students Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Murid</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalStudents; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-user-graduate text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Teachers Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Guru</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalTeachers; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-chalkboard-teacher text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Classes Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Kelas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalClasses; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-school text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Total Subjects Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-purple-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Mata Pelajaran</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalSubjects; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <i class="fas fa-book text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users & Assignments -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Users -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Pengguna Terbaru</h3>
            <a href="<?php echo BASE_URL; ?>/admin/manage_users.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($recentUsers)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pengguna</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentUsers as $user): ?>
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
                                        <?php echo formatDate($user['created_at']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Recent Assignments -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Tugas Terbaru</h3>
        </div>
        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($recentAssignments)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data tugas</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentAssignments as $assignment): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo escape($assignment['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo escape($assignment['subject_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo escape($assignment['class_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($assignment['due_date']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                            <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- System Status -->
<div class="mt-8 bg-white rounded-lg shadow-md">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Status Sistem</h3>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Versi PHP</h4>
                <p class="text-lg font-semibold text-gray-800"><?php echo PHP_VERSION; ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Database</h4>
                <p class="text-lg font-semibold text-gray-800">MySQL <?php echo mysqli_get_server_info($conn); ?></p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="text-sm font-medium text-gray-500 mb-2">Versi Aplikasi</h4>
                <p class="text-lg font-semibold text-gray-800"><?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
