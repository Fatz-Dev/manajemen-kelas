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

// Get graded submissions for this student
$sql = "SELECT s.*, a.title as assignment_title, a.due_date, a.status as assignment_status,
               subj.subject_name, u.full_name as teacher_name
        FROM submissions s
        JOIN assignments a ON s.assignment_id = a.id
        JOIN subjects subj ON a.subject_id = subj.id
        JOIN users u ON a.created_by = u.id
        WHERE s.student_id = ? AND s.grade IS NOT NULL
        ORDER BY s.graded_at DESC";

$gradedSubmissions = fetchAll($sql, [$studentId], 'i');

// Get performance data
$performance = getStudentPerformance($studentId);

// Group grades by subjects
$subjectGrades = [];
foreach ($gradedSubmissions as $submission) {
    $subjectName = $submission['subject_name'];
    
    if (!isset($subjectGrades[$subjectName])) {
        $subjectGrades[$subjectName] = [
            'submissions' => [],
            'average' => 0,
            'count' => 0,
            'total' => 0
        ];
    }
    
    $subjectGrades[$subjectName]['submissions'][] = $submission;
    $subjectGrades[$subjectName]['total'] += $submission['grade'];
    $subjectGrades[$subjectName]['count']++;
}

// Calculate averages
foreach ($subjectGrades as $subjectName => $data) {
    if ($data['count'] > 0) {
        $subjectGrades[$subjectName]['average'] = $data['total'] / $data['count'];
    }
}

// Set page title and description
$pageTitle = "Nilai Saya";
$pageHeader = "Nilai dan Penilaian";
$pageDescription = "Lihat nilai dan penilaian tugas-tugas Anda";
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Dashboard
    </a>
</div>

<!-- Performance Overview -->
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-blue-600 px-6 py-4">
        <h2 class="text-xl font-bold text-white">Ringkasan Nilai</h2>
        <p class="text-blue-100">Rata-rata nilai keseluruhan: 
            <span class="font-bold"><?php echo number_format($performance['average_grade'], 1); ?></span>
        </p>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-blue-700">Total Tugas</h3>
                <p class="text-2xl font-bold text-blue-800 mt-2"><?php echo $performance['total_assignments']; ?></p>
                <p class="text-xs text-blue-600 mt-1">Tugas yang diberikan</p>
            </div>
            
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-green-700">Tugas Dikumpulkan</h3>
                <p class="text-2xl font-bold text-green-800 mt-2"><?php echo $performance['submissions_count']; ?></p>
                <p class="text-xs text-green-600 mt-1">Total pengumpulan</p>
            </div>
            
            <div class="bg-purple-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-purple-700">Telah Dinilai</h3>
                <p class="text-2xl font-bold text-purple-800 mt-2"><?php echo $performance['graded_count']; ?></p>
                <p class="text-xs text-purple-600 mt-1">Tugas yang telah dinilai</p>
            </div>
            
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-yellow-700">Rata-rata Nilai</h3>
                <p class="text-2xl font-bold text-yellow-800 mt-2"><?php echo number_format($performance['average_grade'], 1); ?></p>
                <p class="text-xs text-yellow-600 mt-1">Dari tugas yang dinilai</p>
            </div>
        </div>
        
        <?php if (empty($gradedSubmissions)): ?>
            <div class="text-center py-6">
                <div class="flex justify-center mb-4">
                    <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-chart-line text-gray-400 text-2xl"></i>
                    </div>
                </div>
                <h3 class="text-gray-500 text-lg mb-2">Belum Ada Nilai</h3>
                <p class="text-gray-400 text-sm">Tugas Anda belum dinilai oleh guru.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grades by Subject -->
<?php if (!empty($subjectGrades)): ?>
<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Nilai Per Mata Pelajaran</h2>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($subjectGrades as $subjectName => $data): ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition">
                    <div class="p-4 bg-blue-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900"><?php echo escape($subjectName); ?></h3>
                        <div class="flex justify-between items-center mt-1">
                            <p class="text-sm text-gray-500"><?php echo $data['count']; ?> tugas dinilai</p>
                            <p class="text-lg font-bold text-blue-600"><?php echo number_format($data['average'], 1); ?></p>
                        </div>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($data['submissions'] as $submission): ?>
                                <li class="py-2">
                                    <div class="flex justify-between">
                                        <div>
                                            <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $submission['assignment_id']; ?>" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                <?php echo escape($submission['assignment_title']); ?>
                                            </a>
                                            <p class="text-xs text-gray-500 mt-1">Dinilai: <?php echo formatDate($submission['graded_at']); ?></p>
                                        </div>
                                        <div class="text-sm font-bold <?php echo $submission['grade'] >= 70 ? 'text-green-600' : 'text-red-600'; ?>">
                                            <?php echo $submission['grade']; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Graded Assignments -->
<?php if (!empty($gradedSubmissions)): ?>
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Nilai Tugas Terakhir</h2>
    </div>
    
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Penilaian</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($gradedSubmissions as $submission): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($submission['assignment_title']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo escape($submission['teacher_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo escape($submission['subject_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo formatDate($submission['graded_at']); ?></div>
                                <div class="text-xs text-gray-500">Dikumpulkan: <?php echo formatDate($submission['submitted_at']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo $submission['grade'] >= 70 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo $submission['grade']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $submission['assignment_id']; ?>" class="text-blue-600 hover:text-blue-800">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>