<?php
require_once '../config.php';
require_once '../functions/auth_functions.php';
require_once '../functions/assignment_functions.php';
require_once '../functions/helpers.php';
require_once '../functions/class_functions.php';

// Ensure user is logged in and is a teacher
if (!isLoggedIn()) {
    setAlert('Silakan login untuk mengakses halaman ini.', 'danger');
    redirect(BASE_URL . '/login.php');
}

// Get current user
$user = getCurrentUser();
if ($user['role'] !== ROLE_TEACHER) {
    setAlert('Halaman ini hanya dapat diakses oleh guru.', 'danger');
    redirect(BASE_URL . '/');
}

$teacherId = $_SESSION['user_id'];
$assignmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$assignmentId) {
    setAlert('ID tugas tidak valid', 'danger');
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get assignment details
$assignment = getAssignmentById($assignmentId);

if (!$assignment || $assignment['created_by'] != $teacherId) {
    setAlert('Anda tidak memiliki akses ke tugas ini', 'danger');
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get all submissions for this assignment
$submissions = getSubmissionsByAssignment($assignmentId);

// Page title
$pageTitle = 'Pengumpulan Tugas: ' . $assignment['title'];

// Include header
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
        <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Tugas
        </a>
    </div>

    <!-- Assignment Details -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Detail Tugas</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Mata Pelajaran:</p>
                <p class="font-medium"><?php echo $assignment['subject_name']; ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Kelas:</p>
                <p class="font-medium"><?php echo getClassNameById($assignment['class_id']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Tenggat Waktu:</p>
                <p class="font-medium"><?php echo formatDate($assignment['due_date']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Status:</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                    <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                </span>
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-600">Deskripsi:</p>
            <div class="mt-2 p-4 bg-gray-50 rounded-md">
                <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
            </div>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Daftar Pengumpulan Tugas</h2>
        
        <?php if (empty($submissions)): ?>
        <div class="bg-yellow-50 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">Belum ada siswa yang mengumpulkan tugas ini.</p>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Pengumpulan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo $submission['student_name']; ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?php echo formatDateTime($submission['submitted_at']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (isOverdue($assignment['due_date']) && strtotime($submission['submitted_at']) > strtotime($assignment['due_date'])): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Terlambat</span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Tepat Waktu</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if (isset($submission['grade'])): ?>
                                <div class="text-sm font-medium text-gray-900"><?php echo $submission['grade']; ?></div>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Belum Dinilai</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/teacher/grade_submission.php?id=<?php echo $submission['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i> Lihat & Nilai
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