<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';
require_once '../functions/assignment_functions.php';

// Set required role
$requiredRole = ROLE_TEACHER;

// Include authentication check
require_once '../includes/auth_check.php';

// Get current user
$user = getCurrentUser();
$teacherId = $user['id'];

// Get statistics
$teachingClasses = getClassesByTeacher($teacherId);
$classesTaughtCount = count($teachingClasses);

$subjects = getSubjectsByTeacher($teacherId);
$subjectsTaughtCount = count($subjects);

// Get assignments
$totalAssignmentsCount = countAssignmentsByTeacher($teacherId);
$activeAssignmentsCount = countAssignmentsByTeacher($teacherId, 'published');
$draftAssignmentsCount = countAssignmentsByTeacher($teacherId, 'draft');
$closedAssignmentsCount = countAssignmentsByTeacher($teacherId, 'closed');

// Get ungraded submissions count
$ungradedSubmissionsCount = getUngradedSubmissionsCount($teacherId);

// Get recent assignments
$recentAssignments = getAssignmentsByTeacher($teacherId, null, 5, 0);

// Get recent submissions that need grading
$sql = "SELECT s.*, a.title as assignment_title, u.full_name as student_name, c.class_name 
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN users u ON s.student_id = u.id
        JOIN classes c ON a.class_id = c.id
        WHERE a.created_by = ? AND s.grade IS NULL
        ORDER BY s.submitted_at DESC
        LIMIT 5";

$recentSubmissions = fetchAll($sql, [$teacherId]);

// Page details
$pageTitle = "Dashboard Guru";
$pageHeader = "Dashboard Guru";
$pageDescription = "Selamat datang, " . $user['full_name'];
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Teaching Classes Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Kelas yang Diajar</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $classesTaughtCount; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-school text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php" class="text-sm text-blue-600 hover:text-blue-800">Lihat semua kelas</a>
        </div>
    </div>
    
    <!-- Subjects Taught Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Mata Pelajaran</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $subjectsTaughtCount; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-book text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php" class="text-sm text-green-600 hover:text-green-800">Lihat semua mata pelajaran</a>
        </div>
    </div>
    
    <!-- Total Assignments Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Tugas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $totalAssignmentsCount; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-tasks text-xl"></i>
            </div>
        </div>
        <div class="mt-4 text-xs text-gray-500">
            <span class="inline-block mr-2">Aktif: <?php echo $activeAssignmentsCount; ?></span>
            <span class="inline-block mr-2">Draft: <?php echo $draftAssignmentsCount; ?></span>
            <span class="inline-block">Ditutup: <?php echo $closedAssignmentsCount; ?></span>
        </div>
    </div>
    
    <!-- Submissions To Grade Card -->
    <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm text-gray-500 font-medium">Tugas Belum Dinilai</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $ungradedSubmissionsCount; ?></h3>
            </div>
            <div class="p-3 rounded-full bg-red-100 text-red-600">
                <i class="fas fa-file-alt text-xl"></i>
            </div>
        </div>
        <div class="mt-4">
            <a href="<?php echo BASE_URL; ?>/teacher/grading.php" class="text-sm text-red-600 hover:text-red-800">Nilai tugas sekarang</a>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow-md mb-8">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Aksi Cepat</h3>
    </div>
    <div class="p-6 flex flex-wrap gap-4">
        <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php" class="flex-1 min-w-[150px] bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-center">
            <i class="fas fa-plus-circle mr-2"></i> Buat Tugas Baru
        </a>
        <a href="<?php echo BASE_URL; ?>/teacher/grading.php" class="flex-1 min-w-[150px] bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-center">
            <i class="fas fa-star mr-2"></i> Nilai Tugas
        </a>
        <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="flex-1 min-w-[150px] bg-yellow-600 text-white py-3 px-4 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 text-center">
            <i class="fas fa-tasks mr-2"></i> Kelola Tugas
        </a>
        <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php" class="flex-1 min-w-[150px] bg-purple-600 text-white py-3 px-4 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 text-center">
            <i class="fas fa-users mr-2"></i> Lihat Kelas
        </a>
    </div>
</div>

<!-- Recent Assignments & Submissions -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Assignments -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Tugas Terbaru</h3>
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="p-4">
            <?php if (empty($recentAssignments)): ?>
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-tasks text-4xl mb-3"></i>
                    <p>Anda belum membuat tugas apapun.</p>
                    <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php" class="mt-2 inline-block text-blue-600 hover:underline">Buat tugas pertama Anda</a>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentAssignments as $assignment): ?>
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900"><?php echo escape($assignment['title']); ?></h4>
                                    <p class="text-sm text-gray-500">Kelas: <?php echo escape($assignment['class_name']); ?> - <?php echo escape($assignment['subject_name']); ?></p>
                                </div>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                    <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                                </span>
                            </div>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">Tenggat: <?php echo formatDatetime($assignment['due_date']); ?></span>
                                <a href="<?php echo BASE_URL; ?>/teacher/submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="text-xs text-blue-600 hover:underline">Lihat pengumpulan</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Submissions Needing Grading -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Pengumpulan Belum Dinilai</h3>
            <a href="<?php echo BASE_URL; ?>/teacher/grading.php" class="text-sm text-blue-600 hover:underline">Lihat Semua</a>
        </div>
        <div class="p-4">
            <?php if (empty($recentSubmissions)): ?>
                <div class="text-center py-6 text-gray-500">
                    <i class="fas fa-check-circle text-4xl mb-3"></i>
                    <p>Tidak ada tugas yang perlu dinilai.</p>
                    <p class="text-sm mt-1">Semua pengumpulan sudah dinilai!</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentSubmissions as $submission): ?>
                        <div class="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900"><?php echo escape($submission['assignment_title']); ?></h4>
                                    <p class="text-sm text-gray-500">Siswa: <?php echo escape($submission['student_name']); ?> - <?php echo escape($submission['class_name']); ?></p>
                                </div>
                                <span class="text-xs text-gray-500">
                                    <?php echo getTimeAgo($submission['submitted_at']); ?>
                                </span>
                            </div>
                            <div class="mt-2 flex justify-between items-center">
                                <span class="text-xs text-gray-500">Dikumpulkan: <?php echo formatDatetime($submission['submitted_at']); ?></span>
                                <a href="<?php echo BASE_URL; ?>/teacher/grading.php?submission_id=<?php echo $submission['id']; ?>" class="text-xs text-blue-600 hover:underline">Nilai sekarang</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Classes Overview -->
<div class="mt-8 bg-white rounded-lg shadow-md">
    <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-800">Kelas yang Diajar</h3>
    </div>
    <div class="p-4">
        <?php if (empty($teachingClasses)): ?>
            <div class="text-center py-6 text-gray-500">
                <i class="fas fa-school text-4xl mb-3"></i>
                <p>Anda belum ditugaskan untuk mengajar kelas apapun.</p>
                <p class="text-sm mt-1">Hubungi administrator untuk mendapatkan akses ke kelas.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($teachingClasses as $class): ?>
                    <div class="border rounded-lg overflow-hidden hover-card">
                        <div class="p-4 bg-blue-50 border-b">
                            <h4 class="font-medium text-gray-900"><?php echo escape($class['class_name']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo $class['student_count']; ?> siswa</p>
                        </div>
                        <div class="p-4">
                            <?php 
                            // Get subjects taught by this teacher in this class
                            $sql = "SELECT s.* FROM subjects s
                                    JOIN class_subject cs ON s.id = cs.subject_id
                                    WHERE cs.class_id = ? AND s.teacher_id = ?";
                            $classTaughtSubjects = fetchAll($sql, [$class['id'], $teacherId], 'ii');
                            ?>
                            
                            <?php if (!empty($classTaughtSubjects)): ?>
                                <p class="text-sm text-gray-600 font-medium mb-2">Mata Pelajaran:</p>
                                <ul class="text-sm text-gray-500 ml-4 mb-4 list-disc">
                                    <?php foreach ($classTaughtSubjects as $subject): ?>
                                        <li><?php echo escape($subject['subject_name']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                            
                            <div class="mt-3 flex justify-end">
                                <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php?class_id=<?php echo $class['id']; ?>" class="text-sm text-blue-600 hover:underline mr-4">
                                    <i class="fas fa-plus-circle mr-1"></i> Buat Tugas
                                </a>
                                <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php?class_id=<?php echo $class['id']; ?>" class="text-sm text-blue-600 hover:underline">
                                    <i class="fas fa-eye mr-1"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
