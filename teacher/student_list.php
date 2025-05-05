<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';

// Set required role
$requiredRole = ROLE_TEACHER;

// Include authentication check
require_once '../includes/auth_check.php';

// Get current user
$user = getCurrentUser();
$teacherId = $user['id'];

// Check if class ID is provided
if (!isset($_GET['class_id']) || empty($_GET['class_id'])) {
    setAlert('ID kelas tidak valid.', 'danger');
    redirect(BASE_URL . '/teacher/my_classes.php');
}

$classId = (int)$_GET['class_id'];

// Check if the teacher has access to this class
if (!teacherTeachesClass($teacherId, $classId)) {
    setAlert('Anda tidak memiliki akses ke kelas ini.', 'danger');
    redirect(BASE_URL . '/teacher/my_classes.php');
}

// Get class details
$class = getClassById($classId);
if (!$class) {
    setAlert('Kelas tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/teacher/my_classes.php');
}

// Get students in the class
$students = getStudentsByClass($classId);

// Page details
$pageTitle = "Daftar Siswa: " . $class['class_name'];
$pageHeader = "Daftar Siswa";
$pageDescription = "Daftar siswa kelas " . $class['class_name'];
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/teacher/class_details.php?id=<?php echo $classId; ?>" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Kelas
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-blue-600 text-white px-6 py-4">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-semibold">Daftar Siswa: <?php echo escape($class['class_name']); ?></h2>
            <span class="bg-blue-800 text-white text-sm py-1 px-3 rounded-full"><?php echo count($students); ?> Siswa</span>
        </div>
    </div>
    
    <div class="p-6">
        <?php if (empty($students)): ?>
            <div class="text-center py-8">
                <p class="text-gray-500">Belum ada siswa di kelas ini.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terdaftar</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($students as $i => $student): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $i + 1; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?php echo escape($student['full_name']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($student['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($student['email']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($student['created_at']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo BASE_URL; ?>/teacher/student_details.php?id=<?php echo $student['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/teacher/student_grades.php?id=<?php echo $student['id']; ?>" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-chart-line"></i> Nilai
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
