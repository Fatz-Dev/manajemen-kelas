<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';

// Set required role
$requiredRole = ROLE_ADMIN;

// Include authentication check
require_once '../includes/auth_check.php';

// Process class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_class') {
        $className = trim($_POST['class_name']);
        $teacherId = !empty($_POST['homeroom_teacher_id']) ? $_POST['homeroom_teacher_id'] : null;
        
        if (!empty($className)) {
            $classData = [
                'class_name' => $className,
                'homeroom_teacher_id' => $teacherId
            ];
            
            $classId = createClass($classData);
            
            if ($classId) {
                setAlert('Kelas berhasil dibuat.', 'success');
            } else {
                setAlert('Gagal membuat kelas.', 'danger');
            }
        } else {
            setAlert('Nama kelas tidak boleh kosong.', 'danger');
        }
    } elseif ($_POST['action'] === 'update_class') {
        $classId = $_POST['class_id'];
        $className = trim($_POST['class_name']);
        $teacherId = !empty($_POST['homeroom_teacher_id']) ? $_POST['homeroom_teacher_id'] : null;
        
        if (!empty($className)) {
            $classData = [
                'class_name' => $className,
                'homeroom_teacher_id' => $teacherId
            ];
            
            if (updateClass($classId, $classData)) {
                setAlert('Kelas berhasil diperbarui.', 'success');
            } else {
                setAlert('Gagal memperbarui kelas.', 'danger');
            }
        } else {
            setAlert('Nama kelas tidak boleh kosong.', 'danger');
        }
    } elseif ($_POST['action'] === 'delete_class') {
        $classId = $_POST['class_id'];
        
        // Check if class has students
        $studentCount = countStudentsByClass($classId);
        
        if ($studentCount > 0) {
            setAlert('Kelas tidak dapat dihapus karena masih memiliki siswa.', 'danger');
        } else {
            // Delete class-subject relationships first
            executeQuery("DELETE FROM class_subject WHERE class_id = ?", [$classId]);
            
            // Delete class
            if (delete('classes', 'id = ?', [$classId])) {
                setAlert('Kelas berhasil dihapus.', 'success');
            } else {
                setAlert('Gagal menghapus kelas.', 'danger');
            }
        }
    }
    
    // Redirect to refresh the page and avoid resubmission
    redirect(BASE_URL . '/admin/manage_classes.php');
}

// Get all classes
$page = getParam('page', 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$classes = getAllClasses($limit, $offset);
$totalClasses = countAllClasses();

// Get all available teachers
$teachers = getAvailableTeachers();

// Setup pagination
$paginationData = getPaginationData(
    $totalClasses,
    $limit,
    $page,
    BASE_URL . '/admin/manage_classes.php?page=:page'
);

// Page details
$pageTitle = "Manajemen Kelas";
$pageHeader = "Manajemen Kelas";
$pageDescription = "Kelola kelas dalam sistem";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Daftar Kelas</h2>
        <p class="text-sm text-gray-500">Total: <?php echo $totalClasses; ?> kelas</p>
    </div>
    <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" data-modal="create-class-modal">
        <i class="fas fa-plus mr-2"></i> Tambah Kelas
    </button>
</div>

<!-- Class List Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Siswa</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data kelas</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $class['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($class['class_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?php echo $class['homeroom_teacher_name'] ? escape($class['homeroom_teacher_name']) : '<span class="text-yellow-600">Belum ditetapkan</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $class['student_count']; ?> siswa
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" onclick="editClass(<?php echo $class['id']; ?>, '<?php echo escape($class['class_name']); ?>', <?php echo $class['homeroom_teacher_id'] ? $class['homeroom_teacher_id'] : 'null'; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteClass(<?php echo $class['id']; ?>, '<?php echo escape($class['class_name']); ?>')">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalClasses > $limit): ?>
        <div class="px-6 py-4 border-t">
            <?php echo generatePagination($paginationData); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Class Modal -->
<div id="create-class-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_classes.php" method="post">
                <input type="hidden" name="action" value="create_class">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Tambah Kelas Baru
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="class_name" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                                    <input type="text" name="class_name" id="class_name" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="homeroom_teacher_id" class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                                    <select name="homeroom_teacher_id" id="homeroom_teacher_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Pilih Wali Kelas --</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo escape($teacher['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Class Modal -->
<div id="edit-class-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_classes.php" method="post">
                <input type="hidden" name="action" value="update_class">
                <input type="hidden" name="class_id" id="edit_class_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Kelas
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="edit_class_name" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                                    <input type="text" name="class_name" id="edit_class_name" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="edit_homeroom_teacher_id" class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                                    <select name="homeroom_teacher_id" id="edit_homeroom_teacher_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Pilih Wali Kelas --</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo escape($teacher['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Simpan Perubahan
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Class Modal -->
<div id="delete-class-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_classes.php" method="post">
                <input type="hidden" name="action" value="delete_class">
                <input type="hidden" name="class_id" id="delete_class_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Kelas
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menghapus kelas <span id="delete_class_name" class="font-medium"></span>? 
                                    Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait kelas ini.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" data-close-modal>
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function editClass(id, name, teacherId) {
        document.getElementById('edit_class_id').value = id;
        document.getElementById('edit_class_name').value = name;
        
        const teacherSelect = document.getElementById('edit_homeroom_teacher_id');
        if (teacherId) {
            teacherSelect.value = teacherId;
        } else {
            teacherSelect.value = '';
        }
        
        // Show modal
        const modal = document.getElementById('edit-class-modal');
        modal.classList.remove('hidden');
    }
    
    function deleteClass(id, name) {
        document.getElementById('delete_class_id').value = id;
        document.getElementById('delete_class_name').textContent = name;
        
        // Show modal
        const modal = document.getElementById('delete-class-modal');
        modal.classList.remove('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>
