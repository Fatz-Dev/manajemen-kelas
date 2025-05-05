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

// Get page parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Check if specific class is requested
$classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;

// Get assignments
if ($classId) {
    // Check if teacher has access to this class
    if (!teacherTeachesClass($teacherId, $classId)) {
        setAlert('Anda tidak memiliki akses ke kelas ini.', 'danger');
        redirect(BASE_URL . '/teacher/my_classes.php');
    }
    
    // Get class info
    $class = getClassById($classId);
    
    // Get assignments for specific class
    $assignments = getAssignmentsByClass($classId, $status, $limit, $offset);
    $totalAssignments = countAssignmentsByClass($classId, $status);
    
    $pageTitle = "Tugas Kelas: " . $class['class_name'];
    $pageHeader = "Tugas Kelas";
    $pageDescription = "Kelola tugas untuk kelas " . $class['class_name'];
} else {
    // Get all assignments by teacher
    $assignments = getAssignmentsByTeacher($teacherId, $status, $limit, $offset);
    $totalAssignments = countAssignmentsByTeacher($teacherId, $status);
    
    $pageTitle = "Semua Tugas";
    $pageHeader = "Semua Tugas";
    $pageDescription = "Kelola semua tugas yang Anda buat";
}

// Calculate total pages
$totalPages = ceil($totalAssignments / $limit);

// Get ungraded submissions count
$ungradedCount = getUngradedSubmissionsCount($teacherId);

// Set sidebar flag
$showSidebar = true;

// Include header
include '../includes/header.php';
?><!-- Filter and Action Buttons -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
    <div>
        <?php if ($classId): ?>
            <a href="<?php echo BASE_URL; ?>/teacher/class_details.php?id=<?php echo $classId; ?>" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Kelas
            </a>
        <?php endif; ?>
        
        <h2 class="text-2xl font-bold text-gray-800"><?php echo $pageHeader; ?></h2>
        <p class="text-gray-600"><?php echo $pageDescription; ?></p>
    </div>
    
    <div class="flex flex-wrap gap-2">
        <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php<?php echo $classId ? "?class_id=" . $classId : ""; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded inline-flex items-center">
            <i class="fas fa-plus mr-2"></i> Buat Tugas Baru
        </a>
        
        <?php if ($ungradedCount > 0): ?>
            <a href="<?php echo BASE_URL; ?>/teacher/submissions.php?ungraded=1" class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-clipboard-check mr-2"></i> <?php echo $ungradedCount; ?> Tugas Belum Dinilai
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col md:flex-row justify-between gap-4">
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php<?php echo $classId ? "?class_id=" . $classId : ""; ?>" class="px-3 py-1 rounded <?php echo !$status ? "bg-blue-600 text-white" : "bg-gray-200 text-gray-700 hover:bg-gray-300"; ?>">
                Semua
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?>status=published" class="px-3 py-1 rounded <?php echo $status === "published" ? "bg-blue-600 text-white" : "bg-gray-200 text-gray-700 hover:bg-gray-300"; ?>">
                Aktif
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?>status=draft" class="px-3 py-1 rounded <?php echo $status === "draft" ? "bg-blue-600 text-white" : "bg-gray-200 text-gray-700 hover:bg-gray-300"; ?>">
                Draft
            </a>
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?>status=completed" class="px-3 py-1 rounded <?php echo $status === "completed" ? "bg-blue-600 text-white" : "bg-gray-200 text-gray-700 hover:bg-gray-300"; ?>">
                Selesai
            </a>
        </div>
        
        <?php if (!$classId): ?>
            <div class="flex items-center">
                <label for="class-filter" class="mr-2 text-sm font-medium text-gray-700">Kelas:</label>
                <select id="class-filter" class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">Semua Kelas</option>
                    <?php 
                    $classes = getClassesByTeacher($teacherId);
                    foreach ($classes as $c): 
                    ?>
                    <option value="<?php echo $c["id"]; ?>"><?php echo escape($c["class_name"]); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Assignments List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <?php if (empty($assignments)): ?>
        <div class="p-8 text-center">
            <div class="text-gray-500 mb-4">
                <i class="fas fa-tasks text-5xl mb-4"></i>
                <p>Belum ada tugas <?php echo $status ? "dengan status " . $status : ""; ?>.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php<?php echo $classId ? "?class_id=" . $classId : ""; ?>" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Buat Tugas Baru
            </a>
        </div>
    <?php else: ?>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <?php if (!$classId): ?>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    <?php endif; ?>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengumpulan</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($assignments as $assignment): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo escape($assignment["title"]); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                Dibuat: <?php echo formatDate($assignment["created_at"]); ?>
                            </div>
                        </td>
                        
                        <?php if (!$classId): ?>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo escape($assignment["class_name"]); ?>
                                </span>
                            </td>
                        <?php endif; ?>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">
                                <?php echo escape($assignment["subject_name"]); ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm <?php echo strtotime($assignment["due_date"]) < time() ? "text-red-600" : "text-gray-900"; ?>">
                                <?php echo formatDate($assignment["due_date"]); ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment["status"], $assignment["due_date"]); ?>">
                                <?php echo getAssignmentStatusText($assignment["status"], $assignment["due_date"]); ?>
                            </span>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php 
                            $submissionCount = countSubmissionsByAssignment($assignment["id"]);
                            echo $submissionCount;
                            ?>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo BASE_URL; ?>/teacher/view_assignment.php?id=<?php echo $assignment["id"]; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/edit_assignment.php?id=<?php echo $assignment["id"]; ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="<?php echo BASE_URL; ?>/teacher/view_submissions.php?id=<?php echo $assignment["id"]; ?>" class="text-green-600 hover:text-green-900">
                                <i class="fas fa-clipboard-check"></i> Nilai
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="p-4 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Menampilkan <?php echo ($offset + 1); ?>-<?php echo min($offset + $limit, $totalAssignments); ?> dari <?php echo $totalAssignments; ?> tugas
                    </div>
                    <div class="flex space-x-1">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?><?php echo $status ? "status=" . $status . "&" : ""; ?>page=<?php echo ($page - 1); ?>" class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                &laquo; Prev
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?><?php echo $status ? "status=" . $status . "&" : ""; ?>page=<?php echo $i; ?>" class="px-3 py-1 rounded border <?php echo $i === $page ? "bg-blue-600 text-white border-blue-600" : "border-gray-300 bg-white text-gray-700 hover:bg-gray-50"; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php?<?php echo $classId ? "class_id=" . $classId . "&" : ""; ?><?php echo $status ? "status=" . $status . "&" : ""; ?>page=<?php echo ($page + 1); ?>" class="px-3 py-1 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50">
                                Next &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    // Class filter change handler
    document.addEventListener("DOMContentLoaded", function() {
        const classFilter = document.getElementById("class-filter");
        if (classFilter) {
            classFilter.addEventListener("change", function() {
                const classId = this.value;
                if (classId) {
                    window.location.href = "<?php echo BASE_URL; ?>/teacher/assignments.php?class_id=" + classId<?php echo $status ? " + \"&status=" . $status . "\"" : ""; ?>;
                } else {
                    window.location.href = "<?php echo BASE_URL; ?>/teacher/assignments.php<?php echo $status ? "?status=" . $status : ""; ?>";
                }
            });
        }
    });
</script>

<?php include "../includes/footer.php"; ?>
