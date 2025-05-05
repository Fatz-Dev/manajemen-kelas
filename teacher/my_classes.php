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

// Get classes where the teacher teaches
$teacherId = $user['id'];
$classes = getClassesByTeacher($teacherId);

// Get class where teacher is homeroom teacher
if (!empty($user['class_id'])) {
    $homeroomClass = getClassById($user['class_id']);
}

// Page details
$pageTitle = "Kelas Saya";
$pageHeader = "Kelas Saya";
$pageDescription = "Kelola kelas dan lihat informasi murid";
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="flex flex-col space-y-6">
    <!-- Homeroom Class Card (if applicable) -->
    <?php if (isset($homeroomClass)): ?>
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Kelas Wali</h2>
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">Wali Kelas</span>
        </div>
        
        <div class="mt-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between p-4 bg-blue-50 rounded-lg">
                <div>
                    <h3 class="text-lg font-medium text-blue-800"><?php echo escape($homeroomClass['class_name']); ?></h3>
                    <p class="text-sm text-gray-600 mt-1"><?php echo countStudentsByClass($homeroomClass['id']); ?> murid</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-2">
                    <a href="<?php echo BASE_URL; ?>/teacher/class_details.php?id=<?php echo $homeroomClass['id']; ?>" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                        <i class="fas fa-eye mr-2"></i> Detail
                    </a>
                    <a href="<?php echo BASE_URL; ?>/teacher/student_list.php?class_id=<?php echo $homeroomClass['id']; ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center">
                        <i class="fas fa-user-graduate mr-2"></i> Daftar Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Teaching Classes -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Kelas yang Diajar</h2>
        
        <?php if (empty($classes)): ?>
            <div class="py-4 text-center text-gray-500">
                <p>Anda belum mengajar kelas apapun.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($classes as $class): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">                        
                        <h3 class="text-lg font-medium text-gray-800"><?php echo escape($class['class_name']); ?></h3>
                        
                        <div class="mt-2 flex items-center">
                            <span class="text-sm text-gray-600 mr-2">Mata Pelajaran:</span>
                            <?php 
                            // Get subjects from the class, filter by teacher
                            $allSubjectsByClass = getSubjectsByClass($class['id']);
                            $subjectsByClass = [];
                            foreach ($allSubjectsByClass as $subject) {
                                if ($subject['teacher_id'] == $teacherId) {
                                    $subjectsByClass[] = $subject;
                                }
                            }
                            
                            foreach ($subjectsByClass as $idx => $subject): 
                                echo '<span class="px-2 py-1 text-xs rounded-full ' . 
                                     ($idx % 2 == 0 ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') . 
                                     ' mr-1">' . escape($subject['subject_name']) . '</span>';
                            endforeach; 
                            ?>
                        </div>
                        
                        <div class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-user-graduate mr-1"></i> <?php echo countStudentsByClass($class['id']); ?> murid
                        </div>
                        
                        <div class="mt-4 flex space-x-2">
                            <a href="<?php echo BASE_URL; ?>/teacher/class_details.php?id=<?php echo $class['id']; ?>" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?class_id=<?php echo $class['id']; ?>" class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                <i class="fas fa-tasks mr-1"></i> Tugas
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/student_list.php?class_id=<?php echo $class['id']; ?>" class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                <i class="fas fa-users mr-1"></i> Siswa
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
