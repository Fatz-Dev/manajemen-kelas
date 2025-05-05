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

// Check if assignment ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setAlert('ID tugas tidak valid.', 'danger');
    redirect(BASE_URL . '/student/assignments.php');
}

$assignmentId = (int)$_GET['id'];

// Get assignment details
$assignment = getAssignmentById($assignmentId);
if (!$assignment) {
    setAlert('Tugas tidak ditemukan.', 'danger');
    redirect(BASE_URL . '/student/assignments.php');
}

// Check if student is in the assignment's class
if ($user['class_id'] != $assignment['class_id']) {
    setAlert('Anda tidak memiliki akses ke tugas ini.', 'danger');
    redirect(BASE_URL . '/student/assignments.php');
}

// Get subject information
$subject = getSubjectById($assignment['subject_id']);

// Get teacher information
$teacher = getUserById($assignment['created_by']);

// Get submission information if exists
$submission = getSubmissionByAssignmentAndStudent($assignmentId, $studentId);

// Calculate status
$isOverdue = isOverdue($assignment['due_date']);
$hasSubmission = !empty($submission);
$isGraded = $hasSubmission && isset($submission['grade']);

// Set page title and description
$pageTitle = $assignment['title'];
$pageHeader = $assignment['title'];
$pageDescription = "Detail tugas dari mata pelajaran " . ($subject ? $subject['subject_name'] : 'Tidak diketahui');
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Tugas
    </a>
</div>

<div class="flex flex-col md:flex-row gap-6">
    <!-- Main Content -->
    <div class="md:w-2/3">
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white"><?php echo escape($assignment['title']); ?></h2>
                <div class="flex items-center text-blue-100">
                    <span class="mr-3"><?php echo escape($subject['subject_name'] ?? 'Tidak ada mata pelajaran'); ?></span>
                    <span class="mx-2">•</span>
                    <span class="ml-3">Dibuat oleh: <?php echo escape($teacher['full_name'] ?? 'Tidak diketahui'); ?></span>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <div>
                            <i class="far fa-calendar mr-1"></i> Dibuat: <?php echo formatDate($assignment['created_at']); ?>
                        </div>
                        <div>
                            <i class="far fa-clock mr-1"></i> Tenggat: <?php echo formatDate($assignment['due_date']); ?>
                        </div>
                    </div>
                    
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                        <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                    </span>
                </div>
                
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Deskripsi Tugas</h3>
                    <div class="prose prose-blue max-w-none mb-6">
                        <?php echo nl2br(escape($assignment['description'])); ?>
                    </div>
                </div>
                
                <?php if (!empty($assignment['attachment_path'])): ?>
                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Lampiran</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo basename($assignment['attachment_path']); ?>
                                    </p>
                                    <div class="mt-1 flex items-center">
                                        <a href="<?php echo BASE_URL . '/' . $assignment['attachment_path']; ?>" class="text-xs text-blue-600 hover:text-blue-800" target="_blank">
                                            <i class="fas fa-download mr-1"></i> Unduh Lampiran
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Submission Section -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 <?php echo $hasSubmission ? 'bg-green-600' : 'bg-yellow-600'; ?>">
                <h2 class="text-lg font-bold text-white">
                    <?php echo $hasSubmission ? 'Tugas Anda' : 'Kumpulkan Tugas'; ?>
                </h2>
                <p class="text-sm <?php echo $hasSubmission ? 'text-green-100' : 'text-yellow-100'; ?>">
                    <?php 
                    if ($hasSubmission) {
                        echo 'Dikumpulkan pada: ' . formatDate($submission['submitted_at']);
                    } else {
                        echo $isOverdue ? 'Tenggat waktu telah berakhir, namun Anda masih dapat mengumpulkan.' : 'Kumpulkan tugas Anda sebelum tenggat waktu.';
                    }
                    ?>
                </p>
            </div>
            
            <div class="p-6">
                <?php if ($hasSubmission): ?>
                    <!-- Submission details -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Jawaban Anda</h3>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="prose prose-blue max-w-none mb-4">
                                <?php echo nl2br(escape($submission['content'])); ?>
                            </div>
                            
                            <?php if (!empty($submission['file_path'])): ?>
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo basename($submission['file_path']); ?>
                                            </p>
                                            <div class="mt-1 flex items-center">
                                                <a href="<?php echo BASE_URL . '/' . $submission['file_path']; ?>" class="text-xs text-blue-600 hover:text-blue-800" target="_blank">
                                                    <i class="fas fa-download mr-1"></i> Unduh File
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($isGraded): ?>
                        <!-- Grade information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nilai</h3>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-500">Nilai Anda</p>
                                        <p class="text-3xl font-bold text-green-700"><?php echo $submission['grade']; ?></p>
                                    </div>
                                    <?php if (!empty($submission['feedback'])): ?>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Dinilai pada</p>
                                            <p class="text-sm font-medium text-gray-900"><?php echo formatDate($submission['graded_at']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($submission['feedback'])): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-900 mb-1">Umpan Balik Guru:</p>
                                        <p class="text-sm text-gray-700"><?php echo nl2br(escape($submission['feedback'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700">Tugas Anda telah dikumpulkan dan menunggu penilaian dari guru.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="flex justify-between">
                        <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignmentId; ?>&edit=1" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-edit mr-2"></i> Edit Jawaban
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Submit form button -->
                    <div class="text-center">
                        <?php if ($isOverdue): ?>
                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Tenggat waktu pengumpulan telah berakhir, namun Anda masih dapat mengumpulkan tugas ini.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignmentId; ?>" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-upload mr-2"></i> Kumpulkan Tugas Anda
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="md:w-1/3">
        <div class="bg-white rounded-lg shadow-md overflow-hidden sticky top-4">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Tugas</h3>
            </div>
            
            <div class="p-6">
                <dl class="divide-y divide-gray-200">
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm text-right">
                            <?php if ($hasSubmission): ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                    <?php echo $isGraded ? 'Sudah Dinilai' : 'Sudah Dikumpulkan'; ?>
                                </span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $isOverdue ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                    <?php echo $isOverdue ? 'Melewati Tenggat' : 'Belum Dikumpulkan'; ?>
                                </span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Mata Pelajaran</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo escape($subject['subject_name'] ?? 'Tidak ada mata pelajaran'); ?></dd>
                    </div>
                    
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Guru</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo escape($teacher['full_name'] ?? 'Tidak diketahui'); ?></dd>
                    </div>
                    
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Tanggal Dibuat</dt>
                        <dd class="text-sm text-gray-900"><?php echo formatDate($assignment['created_at']); ?></dd>
                    </div>
                    
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Tenggat Waktu</dt>
                        <dd class="text-sm <?php echo $isOverdue ? 'text-red-600 font-bold' : 'text-gray-900'; ?>">
                            <?php echo formatDate($assignment['due_date']); ?>
                        </dd>
                    </div>
                    
                    <?php if ($hasSubmission): ?>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Waktu Pengumpulan</dt>
                        <dd class="text-sm text-gray-900"><?php echo formatDate($submission['submitted_at']); ?></dd>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($isGraded): ?>
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Nilai</dt>
                        <dd class="text-sm font-bold text-green-600"><?php echo $submission['grade']; ?></dd>
                    </div>
                    
                    <div class="py-3 flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Tanggal Penilaian</dt>
                        <dd class="text-sm text-gray-900"><?php echo formatDate($submission['graded_at']); ?></dd>
                    </div>
                    <?php endif; ?>
                </dl>
                
                <?php if (!$hasSubmission): ?>
                <div class="mt-6">
                    <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignmentId; ?>" class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-upload mr-2"></i> Kumpulkan Tugas
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>