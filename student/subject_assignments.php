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

// Check if subject ID is provided
if (!isset($_GET['subject_id']) || empty($_GET['subject_id'])) {
    setAlert('ID mata pelajaran tidak valid.', 'danger');
    redirect(BASE_URL . '/student/dashboard.php');
}

$subjectId = (int)$_GET['subject_id'];

// Get subject details
$subject = getSubjectById($subjectId);
if (!$subject) {
    setAlert('Mata pelajaran tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/student/dashboard.php');
}

// Check if student's class has this subject
if (empty($user['class_id'])) {
    setAlert('Anda belum terdaftar di kelas manapun.', 'danger');
    redirect(BASE_URL . '/student/dashboard.php');
}

$classId = $user['class_id'];
$classSubjects = getSubjectsByClass($classId);
$hasSubject = false;

foreach ($classSubjects as $classSubject) {
    if ($classSubject['id'] == $subjectId) {
        $hasSubject = true;
        break;
    }
}

if (!$hasSubject) {
    setAlert('Kelas Anda tidak memiliki mata pelajaran ini.', 'danger');
    redirect(BASE_URL . '/student/dashboard.php');
}

// Get teacher information if available
if (isset($subject['teacher_id'])) {
    $teacher = getUserById($subject['teacher_id']);
}

// Get assignments for this subject
$assignments = getAssignmentsBySubject($subjectId);

// Set page title and description
$pageTitle = $subject['subject_name'];
$pageHeader = $subject['subject_name'];
$pageDescription = "Tugas untuk mata pelajaran " . $subject['subject_name'];
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-blue-600 px-6 py-4">
        <h2 class="text-xl font-bold text-white"><?php echo escape($subject['subject_name']); ?></h2>
        <p class="text-blue-100">
            <?php if (isset($teacher)): ?>
                Guru: <?php echo escape($teacher['full_name']); ?>
            <?php else: ?>
                Belum ada guru yang ditugaskan
            <?php endif; ?>
        </p>
    </div>
    
    <div class="p-6">
        <!-- Subject Description if available -->
        <?php if (!empty($subject['description'])): ?>
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Deskripsi Mata Pelajaran</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-gray-600"><?php echo nl2br(escape($subject['description'])); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Assignments -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Tugas</h3>
                
                <div>
                    <span class="text-sm text-gray-500"><?php echo count($assignments); ?> tugas</span>
                </div>
            </div>
            
            <?php if (empty($assignments)): ?>
                <div class="bg-gray-50 rounded-lg p-8 text-center">
                    <div class="flex justify-center mb-4">
                        <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                        </div>
                    </div>
                    <h3 class="text-gray-500 text-lg mb-2">Belum ada tugas</h3>
                    <p class="text-gray-400 text-sm">Guru belum menambahkan tugas untuk mata pelajaran ini.</p>
                </div>
            <?php else: ?>
                <div class="overflow-hidden bg-white shadow sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($assignments as $assignment): ?>
                            <?php 
                            $submission = getSubmissionByAssignmentAndStudent($assignment['id'], $studentId);
                            $isOverdue = isOverdue($assignment['due_date']);
                            ?>
                            <li>
                                <div class="p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                <?php echo escape($assignment['title']); ?>
                                            </a>
                                            <div class="mt-1 flex items-center text-sm text-gray-500">
                                                <span>
                                                    <i class="far fa-calendar mr-1"></i> Dibuat: <?php echo formatDate($assignment['created_at']); ?>
                                                </span>
                                                <span class="mx-2">•</span>
                                                <span>
                                                    <i class="far fa-clock mr-1"></i> Tenggat: <?php echo formatDate($assignment['due_date']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($submission): ?>
                                                <?php if (isset($submission['grade'])): ?>
                                                    <span class="flex items-center px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium">
                                                        <i class="fas fa-check-circle mr-1"></i> Nilai: <?php echo $submission['grade']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="flex items-center px-2 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-medium">
                                                        <i class="fas fa-paper-plane mr-1"></i> Sudah Dikumpulkan
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if ($isOverdue): ?>
                                                    <span class="flex items-center px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-medium">
                                                        <i class="fas fa-exclamation-circle mr-1"></i> Melewati Tenggat
                                                    </span>
                                                <?php else: ?>
                                                    <span class="flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium">
                                                        <i class="fas fa-hourglass-half mr-1"></i> Menunggu Pengumpulan
                                                    </span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-600 line-clamp-2">
                                            <?php echo escape(substr($assignment['description'], 0, 150)); ?>
                                            <?php echo (strlen($assignment['description']) > 150) ? '...' : ''; ?>
                                        </p>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <?php if ($submission): ?>
                                            <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-sm text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye mr-1"></i> Lihat Pengumpulan
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                                <i class="fas fa-upload mr-1"></i> Kumpulkan
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>