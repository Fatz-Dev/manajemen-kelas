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

// Check if class ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setAlert('ID kelas tidak valid.', 'danger');
    redirect(BASE_URL . '/teacher/my_classes.php');
}

$classId = (int)$_GET['id'];

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

// Get homeroom teacher
if ($class['homeroom_teacher_id']) {
    $homeroomTeacher = getUserById($class['homeroom_teacher_id']);
}

// Get students in the class
$students = getStudentsByClass($classId);
$totalStudents = count($students);

// Get all subjects for this class, then filter by teacher
$allSubjects = getSubjectsByClass($classId);
$subjects = [];
foreach ($allSubjects as $subject) {
    if ($subject['teacher_id'] == $teacherId) {
        $subjects[] = $subject;
    }
}

// Get recent assignments for this class
$assignments = getRecentAssignmentsByClass($classId, 'published', 5);

// Page details
$pageTitle = "Detail Kelas: " . $class['class_name'];
$pageHeader = "Detail Kelas";
$pageDescription = "Informasi detail tentang kelas " . $class['class_name'];
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Kelas
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-blue-600 text-white px-6 py-4">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center">
            <div>
                <h2 class="text-2xl font-bold"><?php echo escape($class['class_name']); ?></h2>
                <p class="text-blue-100"><?php echo $totalStudents; ?> murid</p>
            </div>
            <div class="mt-4 md:mt-0 flex flex-wrap gap-2">
                <a href="<?php echo BASE_URL; ?>/teacher/student_list.php?class_id=<?php echo $class['id']; ?>" class="bg-white text-blue-600 px-4 py-2 rounded shadow hover:bg-blue-50">
                    <i class="fas fa-users mr-2"></i> Daftar Siswa
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?class_id=<?php echo $class['id']; ?>" class="bg-white text-blue-600 px-4 py-2 rounded shadow hover:bg-blue-50">
                    <i class="fas fa-tasks mr-2"></i> Kelola Tugas
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/grade_book.php?class_id=<?php echo $class['id']; ?>" class="bg-white text-blue-600 px-4 py-2 rounded shadow hover:bg-blue-50">
                    <i class="fas fa-book-open mr-2"></i> Buku Nilai
                </a>
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Kelas</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="divide-y divide-gray-200">
                        <div class="flex py-2">
                            <dt class="w-1/3 text-sm font-medium text-gray-500">Nama Kelas</dt>
                            <dd class="w-2/3 text-sm text-gray-900"><?php echo escape($class['class_name']); ?></dd>
                        </div>
                        <div class="flex py-2">
                            <dt class="w-1/3 text-sm font-medium text-gray-500">Wali Kelas</dt>
                            <dd class="w-2/3 text-sm text-gray-900">
                                <?php 
                                if (isset($homeroomTeacher)) {
                                    echo escape($homeroomTeacher['full_name']);
                                    if ($homeroomTeacher['id'] == $teacherId) {
                                        echo ' <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Anda</span>';
                                    }
                                } else {
                                    echo '-';
                                }
                                ?>
                            </dd>
                        </div>
                        <div class="flex py-2">
                            <dt class="w-1/3 text-sm font-medium text-gray-500">Jumlah Siswa</dt>
                            <dd class="w-2/3 text-sm text-gray-900"><?php echo $totalStudents; ?> murid</dd>
                        </div>
                        <div class="flex py-2">
                            <dt class="w-1/3 text-sm font-medium text-gray-500">Mata Pelajaran Anda</dt>
                            <dd class="w-2/3 text-sm text-gray-900">
                                <?php if (empty($subjects)): ?>
                                    -
                                <?php else: ?>
                                    <div class="flex flex-wrap gap-1">
                                        <?php foreach ($subjects as $subject): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                                <?php echo escape($subject['subject_name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Tugas Terbaru</h3>
                    <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?class_id=<?php echo $class['id']; ?>" class="text-sm text-blue-600 hover:text-blue-800">Lihat Semua</a>
                </div>
                
                <?php if (empty($assignments)): ?>
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-500">Belum ada tugas untuk kelas ini.</p>
                        <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php?class_id=<?php echo $class['id']; ?>" class="mt-2 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fas fa-plus mr-2"></i> Buat Tugas Baru
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($assignments as $assignment): ?>
                                <li class="py-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <a href="<?php echo BASE_URL; ?>/teacher/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">
                                                <?php echo escape($assignment['title']); ?>
                                            </a>
                                            <div class="text-xs text-gray-500"><?php echo escape($assignment['subject_name']); ?></div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                <span class="mr-2">Tenggat: <?php echo formatDate($assignment['due_date']); ?></span>
                                                <?php 
                                                $submissionCount = countSubmissionsByAssignment($assignment['id']);
                                                echo "<span>$submissionCount pengumpulan</span>";
                                                ?>
                                            </div>
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                            <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="mt-3 text-center">
                            <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php?class_id=<?php echo $class['id']; ?>" class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                <i class="fas fa-plus mr-1"></i> Buat Tugas Baru
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Siswa Kelas</h3>
            
            <?php if (empty($students)): ?>
                <div class="bg-gray-50 rounded-lg p-6 text-center">
                    <p class="text-gray-500">Belum ada siswa di kelas ini.</p>
                </div>
            <?php else: ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php foreach (array_slice($students, 0, 9) as $student): ?>
                            <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-lg bg-white hover:shadow-md">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo escape($student['full_name']); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo escape($student['username']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($students) > 9): ?>
                        <div class="mt-4 text-center">
                            <a href="<?php echo BASE_URL; ?>/teacher/student_list.php?class_id=<?php echo $class['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                Lihat semua <?php echo $totalStudents; ?> siswa
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
