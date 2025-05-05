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

// Handle assignment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $assignmentId = $_POST['assignment_id'];
        $newStatus = $_POST['status'];
        
        // Validate assignment belongs to this teacher
        $assignment = getAssignmentById($assignmentId);
        if ($assignment && $assignment['created_by'] == $teacherId) {
            updateAssignment($assignmentId, ['status' => $newStatus]);
            setAlert('Status tugas berhasil diperbarui.', 'success');
        } else {
            setAlert('Anda tidak memiliki akses untuk memperbarui tugas ini.', 'danger');
        }
    } elseif ($_POST['action'] === 'delete_assignment') {
        $assignmentId = $_POST['assignment_id'];
        
        // Validate assignment belongs to this teacher
        $assignment = getAssignmentById($assignmentId);
        if ($assignment && $assignment['created_by'] == $teacherId) {
            // First delete all submissions for this assignment
            executeQuery("DELETE FROM submissions WHERE assignment_id = ?", [$assignmentId]);
            
            // Then delete the assignment
            if (delete('assignments', 'id = ?', [$assignmentId])) {
                setAlert('Tugas berhasil dihapus.', 'success');
            } else {
                setAlert('Gagal menghapus tugas.', 'danger');
            }
        } else {
            setAlert('Anda tidak memiliki akses untuk menghapus tugas ini.', 'danger');
        }
    }
    
    // Redirect to avoid resubmission
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get filter parameters
$classFilter = getParam('class_id', 'all');
$statusFilter = getParam('status', 'all');
$searchQuery = getParam('search', '');
$page = getParam('page', 1);
$limit = 10;
$offset = ($page - 1) * $limit;

// Build query conditions
$conditions = ['a.created_by = ?'];
$params = [$teacherId];
$types = 'i';

if ($classFilter !== 'all') {
    $conditions[] = 'a.class_id = ?';
    $params[] = $classFilter;
    $types .= 'i';
}

if ($statusFilter !== 'all') {
    $conditions[] = 'a.status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $conditions[] = '(a.title LIKE ? OR a.description LIKE ? OR s.subject_name LIKE ? OR c.class_name LIKE ?)';
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ssss';
}

$whereClause = implode(' AND ', $conditions);

// Count total assignments with filters
$countSql = "SELECT COUNT(*) as count 
             FROM assignments a
             JOIN subjects s ON a.subject_id = s.id
             JOIN classes c ON a.class_id = c.id
             WHERE $whereClause";
$countResult = fetchRow($countSql, $params, $types);
$totalAssignments = $countResult ? $countResult['count'] : 0;

// Get assignments with pagination
$sql = "SELECT a.*, s.subject_name, c.class_name, 
               (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) as submission_count
        FROM assignments a
        JOIN subjects s ON a.subject_id = s.id
        JOIN classes c ON a.class_id = c.id
        WHERE $whereClause
        ORDER BY a.due_date ASC
        LIMIT ? OFFSET ?";

$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$assignments = fetchAll($sql, $params, $types);

// Get classes taught by this teacher for the filter
$teachingClasses = getClassesByTeacher($teacherId);

// Setup pagination
$paginationData = getPaginationData(
    $totalAssignments,
    $limit,
    $page,
    BASE_URL . '/teacher/assignments.php?class_id=' . $classFilter . '&status=' . $statusFilter . '&search=' . urlencode($searchQuery) . '&page=:page'
);

// Page details
$pageTitle = "Manajemen Tugas";
$pageHeader = "Manajemen Tugas";
$pageDescription = "Kelola semua tugas yang Anda buat";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-4 sm:space-y-0">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Daftar Tugas</h2>
        <p class="text-sm text-gray-500">Total: <?php echo $totalAssignments; ?> tugas</p>
    </div>
    
    <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 inline-flex items-center">
        <i class="fas fa-plus-circle mr-2"></i> Buat Tugas Baru
    </a>
</div>

<!-- Filters and Search -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form action="<?php echo BASE_URL; ?>/teacher/assignments.php" method="get" class="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
        <div class="flex-1">
            <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Filter Kelas</label>
            <select id="class_id" name="class_id" onchange="this.form.submit()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="all" <?php echo $classFilter === 'all' ? 'selected' : ''; ?>>Semua Kelas</option>
                <?php foreach ($teachingClasses as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>>
                        <?php echo escape($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="flex-1">
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
            <select id="status" name="status" onchange="this.form.submit()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Dipublikasikan</option>
                <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Ditutup</option>
            </select>
        </div>
        
        <div class="flex-1">
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Tugas</label>
            <div class="relative rounded-md shadow-sm">
                <input type="text" id="search" name="search" value="<?php echo escape($searchQuery); ?>" placeholder="Cari judul tugas, deskripsi..." class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <button type="submit" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <i class="fas fa-search text-gray-400"></i>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Assignments List -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengumpulan</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($assignments)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                            <?php if (!empty($searchQuery) || $classFilter !== 'all' || $statusFilter !== 'all'): ?>
                                Tidak ada tugas yang sesuai dengan filter yang dipilih
                            <?php else: ?>
                                Belum ada tugas yang dibuat
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($assignment['title']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo formatDate($assignment['created_at']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo escape($assignment['class_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo escape($assignment['subject_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo formatDatetime($assignment['due_date']); ?>
                                <?php if (isPast($assignment['due_date']) && $assignment['status'] === 'published'): ?>
                                    <div class="text-xs text-yellow-600">Sudah lewat tenggat</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($assignment['status'], $assignment['due_date']); ?>">
                                    <?php echo getAssignmentStatusText($assignment['status'], $assignment['due_date']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $assignment['submission_count']; ?> pengumpulan
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="<?php echo BASE_URL; ?>/teacher/submissions.php?assignment_id=<?php echo $assignment['id']; ?>" class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> Lihat
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/teacher/create_assignment.php?id=<?php echo $assignment['id']; ?>" class="text-green-600 hover:text-green-900">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="text-red-600 hover:text-red-900" onclick="confirmDelete(<?php echo $assignment['id']; ?>, '<?php echo escape($assignment['title']); ?>')">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                                <?php if ($assignment['status'] === 'draft'): ?>
                                    <div class="mt-2">
                                        <button type="button" class="text-xs text-blue-600 hover:text-blue-900" onclick="updateStatus(<?php echo $assignment['id']; ?>, 'published')">
                                            <i class="fas fa-check-circle"></i> Publikasikan
                                        </button>
                                    </div>
                                <?php elseif ($assignment['status'] === 'published'): ?>
                                    <div class="mt-2">
                                        <button type="button" class="text-xs text-yellow-600 hover:text-yellow-900" onclick="updateStatus(<?php echo $assignment['id']; ?>, 'closed')">
                                            <i class="fas fa-lock"></i> Tutup
                                        </button>
                                    </div>
                                <?php elseif ($assignment['status'] === 'closed'): ?>
                                    <div class="mt-2">
                                        <button type="button" class="text-xs text-green-600 hover:text-green-900" onclick="updateStatus(<?php echo $assignment['id']; ?>, 'published')">
                                            <i class="fas fa-lock-open"></i> Buka Kembali
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalAssignments > $limit): ?>
        <div class="px-6 py-4 border-t">
            <?php echo generatePagination($paginationData); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Hidden forms for actions -->
<form id="status-form" action="<?php echo BASE_URL; ?>/teacher/assignments.php" method="post" class="hidden">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="assignment_id" id="status_assignment_id">
    <input type="hidden" name="status" id="status_value">
</form>

<form id="delete-form" action="<?php echo BASE_URL; ?>/teacher/assignments.php" method="post" class="hidden">
    <input type="hidden" name="action" value="delete_assignment">
    <input type="hidden" name="assignment_id" id="delete_assignment_id">
</form>

<script>
    function updateStatus(assignmentId, status) {
        if (status === 'closed') {
            if (!confirm('Apakah Anda yakin ingin menutup tugas ini? Siswa tidak akan dapat mengumpulkan tugas lagi.')) {
                return;
            }
        }
        
        document.getElementById('status_assignment_id').value = assignmentId;
        document.getElementById('status_value').value = status;
        document.getElementById('status-form').submit();
    }
    
    function confirmDelete(assignmentId, title) {
        if (confirm('Apakah Anda yakin ingin menghapus tugas "' + title + '"? Semua pengumpulan tugas ini juga akan dihapus.')) {
            document.getElementById('delete_assignment_id').value = assignmentId;
            document.getElementById('delete-form').submit();
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
