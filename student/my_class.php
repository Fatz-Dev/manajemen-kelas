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
    
    // Get students in the class
    $students = getStudentsByClass($classId);
    $studentCount = count($students);
    
    // Get homeroom teacher
    if (!empty($class['homeroom_teacher_id'])) {
        $homeroomTeacher = getUserById($class['homeroom_teacher_id']);
    }
    
    // Get subjects for this class
    $subjects = getSubjectsByClass($classId);
} else {
    $class = null;
    $students = [];
    $subjects = [];
}

// Set page title and description
$pageTitle = $class ? "Kelas " . $class['class_name'] : "Kelas Saya";
$pageHeader = $class ? "Kelas " . $class['class_name'] : "Kelas Saya";
$pageDescription = $class ? "Informasi tentang kelas " . $class['class_name'] : "Anda belum terdaftar di kelas manapun";
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
    </a>
</div>

<?php if ($class): ?>
    <!-- Class Information -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="bg-blue-600 px-6 py-4">
            <h2 class="text-xl font-bold text-white"><?php echo escape($class['class_name']); ?></h2>
            <div class="flex items-center text-blue-100">
                <i class="fas fa-users mr-2"></i>
                <span><?php echo $studentCount; ?> siswa</span>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Informasi Kelas</h3>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dl class="divide-y divide-gray-200">
                            <div class="flex py-2">
                                <dt class="w-1/3 text-sm font-medium text-gray-500">Nama Kelas</dt>
                                <dd class="w-2/3 text-sm text-gray-900"><?php echo escape($class['class_name']); ?></dd>
                            </div>
                            <div class="flex py-2">
                                <dt class="w-1/3 text-sm font-medium text-gray-500">Wali Kelas</dt>
                                <dd class="w-2/3 text-sm text-gray-900">
                                    <?php echo isset($homeroomTeacher) ? escape($homeroomTeacher['full_name']) : '-'; ?>
                                </dd>
                            </div>
                            <div class="flex py-2">
                                <dt class="w-1/3 text-sm font-medium text-gray-500">Jumlah Siswa</dt>
                                <dd class="w-2/3 text-sm text-gray-900"><?php echo $studentCount; ?> siswa</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Mata Pelajaran</h3>
                    <?php if (empty($subjects)): ?>
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-500">Belum ada mata pelajaran untuk kelas ini.</p>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="divide-y divide-gray-200">
                                <?php foreach ($subjects as $subject): ?>
                                    <li class="py-3">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900"><?php echo escape($subject['subject_name']); ?></p>
                                                <?php 
                                                if (isset($subject['teacher_id'])) {
                                                    $teacher = getUserById($subject['teacher_id']);
                                                    if ($teacher) {
                                                        echo '<p class="text-xs text-gray-500">Guru: ' . escape($teacher['full_name']) . '</p>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                            <a href="<?php echo BASE_URL; ?>/student/subject_assignments.php?subject_id=<?php echo $subject['id']; ?>" class="text-xs text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-tasks mr-1"></i> Lihat Tugas
                                            </a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Class Members -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Daftar Siswa</h2>
        </div>
        
        <div class="p-6">
            <?php if (empty($students)): ?>
                <div class="text-center py-6">
                    <p class="text-gray-500">Belum ada siswa yang terdaftar di kelas ini.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($students as $student): ?>
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600"><?php echo strtoupper(substr($student['full_name'], 0, 1)); ?></span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo escape($student['full_name']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php if ($student['id'] == $studentId): ?>
                                            <span class="text-green-600">Anda</span>
                                        <?php else: ?>
                                            <?php echo escape($student['username']); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <!-- No Class Yet -->
    <div class="bg-white rounded-lg shadow-md p-8 text-center">
        <div class="flex justify-center mb-4">
            <div class="h-20 w-20 bg-yellow-100 rounded-full flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
            </div>
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">Anda Belum Terdaftar di Kelas Manapun</h3>
        <p class="text-gray-500 max-w-md mx-auto mb-6">
            Anda belum ditugaskan ke kelas manapun. Silakan hubungi admin untuk ditugaskan ke kelas yang sesuai.
        </p>
        <a href="<?php echo BASE_URL; ?>/profile.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i class="fas fa-user-circle mr-2"></i> Lihat Profil Anda
        </a>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>