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

// Get assignments for this student's class
if (!empty($user['class_id'])) {
    $classId = $user['class_id'];
    $class = getClassById($classId);
    
    // Check if filtering by subject
    $subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : null;
    
    // Check if filtering by status
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $validStatuses = ['all', 'pending', 'submitted', 'graded', 'overdue'];
    if (!in_array($status, $validStatuses)) {
        $status = 'all';
    }
    
    // Get assignments
    if ($subjectId) {
        $assignments = getAssignmentsBySubject($subjectId);
        $subjectInfo = getSubjectById($subjectId);
        $pageSubtitle = 'Mata Pelajaran: ' . ($subjectInfo ? $subjectInfo['subject_name'] : 'Tidak Ditemukan');
    } else {
        $assignments = getAssignmentsByClass($classId);
        $pageSubtitle = 'Kelas: ' . $class['class_name'];
    }
    
    // Filter assignments by status
    if ($status !== 'all') {
        $filteredAssignments = [];
        
        foreach ($assignments as $assignment) {
            $submission = getSubmissionByAssignmentAndStudent($assignment['id'], $studentId);
            $isOverdue = isOverdue($assignment['due_date']);
            
            if ($status === 'pending' && !$submission && !$isOverdue) {
                $filteredAssignments[] = $assignment;
            } else if ($status === 'submitted' && $submission && !isset($submission['grade'])) {
                $filteredAssignments[] = $assignment;
            } else if ($status === 'graded' && $submission && isset($submission['grade'])) {
                $filteredAssignments[] = $assignment;
            } else if ($status === 'overdue' && !$submission && $isOverdue) {
                $filteredAssignments[] = $assignment;
            }
        }
        
        $assignments = $filteredAssignments;
    }
    
    // Get subjects for this class for the filter
    $subjects = getSubjectsByClass($classId);
} else {
    $assignments = [];
    $subjects = [];
    $class = null;
    $pageSubtitle = 'Anda belum terdaftar di kelas manapun';
}

// Set page title and description
$pageTitle = "Daftar Tugas";
$pageHeader = "Daftar Tugas";
$pageDescription = "Lihat dan kelola tugas-tugas Anda";
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
        <h2 class="text-xl font-bold text-white"><?php echo $pageHeader; ?></h2>
        <p class="text-blue-100"><?php echo $pageSubtitle; ?></p>
    </div>
    
    <div class="p-6">
        <!-- Filters -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Subject Filter -->
                <div>
                    <label for="subject-filter" class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                    <select id="subject-filter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="applyFilter('subject')">
                        <option value="">Semua Mata Pelajaran</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo (isset($subjectId) && $subjectId == $subject['id']) ? 'selected' : ''; ?>>
                                <?php echo escape($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status-filter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="applyFilter('status')">
                        <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Menunggu Pengumpulan</option>
                        <option value="submitted" <?php echo $status === 'submitted' ? 'selected' : ''; ?>>Sudah Dikumpulkan</option>
                        <option value="graded" <?php echo $status === 'graded' ? 'selected' : ''; ?>>Sudah Dinilai</option>
                        <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Melewati Tenggat</option>
                    </select>
                </div>
            </div>
            
            <div>
                <span class="text-sm text-gray-500"><?php echo count($assignments); ?> tugas ditemukan</span>
            </div>
        </div>
        
        <!-- Assignments List -->
        <?php if (empty($assignments)): ?>
            <div class="bg-gray-50 rounded-lg p-8 text-center">
                <div class="flex justify-center mb-4">
                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-tasks text-gray-400 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-gray-500 text-lg mb-2">Tidak ada tugas ditemukan</h3>
                <p class="text-gray-400 text-sm mb-4">Tidak ada tugas yang sesuai dengan filter yang dipilih.</p>
                <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="text-blue-600 hover:text-blue-800">
                    Lihat semua tugas
                </a>
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
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-lg font-medium text-blue-600 hover:text-blue-800">
                                                <?php echo escape($assignment['title']); ?>
                                            </a>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                                <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <span class="mr-2">Mata Pelajaran: <?php echo escape($assignment['subject_name']); ?></span>
                                            <span class="mx-2">•</span>
                                            <span>Guru: <?php echo escape($assignment['teacher_name']); ?></span>
                                        </div>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-600 line-clamp-2">
                                                <?php echo escape(substr($assignment['description'], 0, 150)); ?>
                                                <?php echo (strlen($assignment['description']) > 150) ? '...' : ''; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <div>
                                            <i class="far fa-calendar mr-1"></i> Dibuat: <?php echo formatDate($assignment['created_at']); ?>
                                        </div>
                                        <div>
                                            <i class="far fa-clock mr-1"></i> Tenggat: <?php echo formatDate($assignment['due_date']); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <?php if ($submission): ?>
                                            <div class="flex items-center">
                                                <?php if (isset($submission['grade'])): ?>
                                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs mr-2">
                                                        Nilai: <?php echo $submission['grade']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs mr-2">
                                                        Sudah Mengumpulkan
                                                    </span>
                                                <?php endif; ?>
                                                <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-blue-600 hover:text-blue-800 text-xs">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <?php if ($isOverdue): ?>
                                                <div class="flex items-center">
                                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs mr-2">
                                                        Melewati Tenggat
                                                    </span>
                                                    <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-blue-600 hover:text-blue-800 text-xs">
                                                        <i class="fas fa-eye mr-1"></i> Lihat
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>/student/submit_assignment.php?id=<?php echo $assignment['id']; ?>" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    <i class="fas fa-upload mr-1"></i> Kumpulkan
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function applyFilter(type) {
    const subjectFilter = document.getElementById('subject-filter');
    const statusFilter = document.getElementById('status-filter');
    
    let url = '<?php echo BASE_URL; ?>/student/assignments.php?';
    
    // Add subject filter if selected
    if (subjectFilter.value) {
        url += 'subject_id=' + subjectFilter.value + '&';
    }
    
    // Add status filter if not 'all'
    if (statusFilter.value !== 'all') {
        url += 'status=' + statusFilter.value;
    } else if (url.endsWith('&')) {
        // Remove trailing & if no status filter
        url = url.slice(0, -1);
    }
    
    window.location.href = url;
}
</script>

<?php include '../includes/footer.php'; ?>