<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';
require_once '../functions/assignment_functions.php';

// Set required role
$requiredRole = ROLE_STUDENT;

// Include authentication check
require_once '../includes/auth_check.php';

// Get current user
$user = getCurrentUser();
$studentId = $user['id'];

// Get class info
if (!empty($user['class_id'])) {
    $class = getClassById($user['class_id']);
    $classId = $class['id'];
    
    // Get recent assignments for this class
    $assignments = getRecentAssignmentsByClass($classId, 'published', 5);
    
    // Get subjects for this class
    $subjects = getSubjectsByClass($classId);
} else {
    $assignments = [];
    $subjects = [];
}

// Get student performance
$performance = getStudentPerformance($studentId);

// Set sidebar flag
$showSidebar = true;

// Set page title and description
$pageTitle = "Dashboard Siswa";
$pageHeader = "Dashboard Siswa";
$pageDescription = "Selamat datang di dashboard siswa, " . $user['full_name'];

// Include header
include '../includes/header.php';
?>

<!-- Welcome and Overview -->
<div class="flex flex-col md:flex-row justify-between items-start mb-6 gap-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageHeader; ?></h2>
        <p class="text-gray-600"><?php echo $pageDescription; ?></p>
    </div>
    
    <?php if (!empty($class)): ?>
        <div class="bg-blue-50 rounded-lg p-4 flex items-center">
            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                <i class="fas fa-chalkboard text-blue-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">Kelas Anda</p>
                <p class="text-lg font-semibold text-blue-600"><?php echo escape($class['class_name']); ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-yellow-50 rounded-lg p-4 flex items-center">
            <div class="flex-shrink-0 h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">Perhatian</p>
                <p class="text-sm text-yellow-700">Anda belum terdaftar di kelas manapun.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Performance Metrics -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Statistik Belajar</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-700">Tugas</h3>
            <div class="mt-2 flex justify-between items-center">
                <p class="text-2xl font-bold text-blue-800"><?php echo isset($performance['total_assignments']) ? $performance['total_assignments'] : 0; ?></p>
                <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-tasks text-blue-600"></i>
                </div>
            </div>
            <p class="mt-2 text-xs text-blue-600">Total tugas yang ditugaskan</p>
        </div>
        
        <div class="bg-green-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-green-700">Pengumpulan</h3>
            <div class="mt-2 flex justify-between items-center">
                <p class="text-2xl font-bold text-green-800"><?php echo isset($performance['submissions_count']) ? $performance['submissions_count'] : 0; ?></p>
                <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
            <p class="mt-2 text-xs text-green-600">Tugas yang telah dikumpulkan</p>
        </div>
        
        <div class="bg-purple-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-purple-700">Telah Dinilai</h3>
            <div class="mt-2 flex justify-between items-center">
                <p class="text-2xl font-bold text-purple-800"><?php echo isset($performance['graded_count']) ? $performance['graded_count'] : 0; ?></p>
                <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-purple-600"></i>
                </div>
            </div>
            <p class="mt-2 text-xs text-purple-600">Tugas yang telah dinilai</p>
        </div>
        
        <div class="bg-yellow-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-yellow-700">Rata-rata Nilai</h3>
            <div class="mt-2 flex justify-between items-center">
                <p class="text-2xl font-bold text-yellow-800"><?php echo isset($performance['average_grade']) ? number_format($performance['average_grade'], 1) : '-'; ?></p>
                <div class="h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-yellow-600"></i>
                </div>
            </div>
            <p class="mt-2 text-xs text-yellow-600">Nilai rata-rata Anda</p>
        </div>
    </div>
    
    <?php if (isset($performance['submissions_count']) && $performance['submissions_count'] > 0): ?>
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Progress Pengumpulan</h3>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <?php 
                $totalAssignments = max(1, isset($performance['total_assignments']) ? $performance['total_assignments'] : 1);
                $submissionsCount = isset($performance['submissions_count']) ? $performance['submissions_count'] : 0;
                $progressPercentage = min(100, ($submissionsCount / $totalAssignments) * 100);
                ?>
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo $progressPercentage; ?>%"></div>
            </div>
            <p class="mt-2 text-xs text-gray-600"><?php echo $submissionsCount; ?> dari <?php echo $totalAssignments; ?> tugas telah dikumpulkan (<?php echo number_format($progressPercentage, 0); ?>%)</p>
        </div>
    <?php endif; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Assignments -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Tugas Terbaru</h2>
            <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
        </div>
        
        <?php if (empty($assignments)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <div class="flex justify-center mb-4">
                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                    </div>
                </div>
                <p class="text-gray-500">Belum ada tugas yang tersedia.</p>
                <?php if (empty($classId)): ?>
                    <p class="text-sm text-gray-400 mt-2">Anda perlu terdaftar di kelas untuk melihat tugas.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($assignments as $assignment): ?>
                    <div class="py-3 transition hover:bg-gray-50 rounded-lg px-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-base font-medium text-blue-600 hover:text-blue-800">
                                    <?php echo escape($assignment['title']); ?>
                                </a>
                                <p class="text-sm text-gray-500"><?php echo escape($assignment['subject_name']); ?></p>
                            </div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                            </span>
                        </div>
                        <div class="mt-2 flex justify-between items-center">
                            <div class="text-sm text-gray-500">
                                <i class="far fa-clock mr-1"></i> Tenggat: <?php echo formatDate($assignment['due_date']); ?>
                            </div>
                            <?php 
                            $submission = getSubmissionByAssignmentAndStudent($assignment['id'], $studentId);
                            if ($submission): 
                            ?>
                                <div class="text-xs">
                                    <?php if (isset($submission['grade'])): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">Nilai: <?php echo $submission['grade']; ?></span>
                                    <?php else: ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Sudah Mengumpulkan</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full hover:bg-yellow-200">
                                    Belum Mengumpulkan
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Subjects -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Mata Pelajaran</h2>
        
        <?php if (empty($subjects)): ?>
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <div class="flex justify-center mb-4">
                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                    </div>
                </div>
                <p class="text-gray-500">Tidak ada mata pelajaran yang tersedia.</p>
                <?php if (empty($classId)): ?>
                    <p class="text-sm text-gray-400 mt-2">Anda perlu terdaftar di kelas untuk melihat mata pelajaran.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($subjects as $subject): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-book text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900"><?php echo escape($subject['subject_name']); ?></h3>
                                <?php 
                                if (isset($subject['teacher_id'])) {
                                    $teacher = getUserById($subject['teacher_id']);
                                    if ($teacher) {
                                        echo '<p class="text-sm text-gray-500">Pengajar: ' . escape($teacher['full_name']) . '</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="<?php echo BASE_URL; ?>/student/subject_assignments.php?subject_id=<?php echo $subject['id']; ?>" class="text-sm text-blue-600 hover:text-blue-800">
                                <i class="fas fa-tasks mr-1"></i> Lihat Tugas
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>