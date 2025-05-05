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

// Process subject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_subject') {
        $subjectName = trim($_POST['subject_name']);
        $teacherId = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
        
        if (!empty($subjectName)) {
            $subjectData = [
                'subject_name' => $subjectName,
                'teacher_id' => $teacherId
            ];
            
            $subjectId = createSubject($subjectData);
            
            if ($subjectId) {
                // If classes were selected, assign subject to them
                if (isset($_POST['classes']) && is_array($_POST['classes'])) {
                    foreach ($_POST['classes'] as $classId) {
                        assignSubjectToClass($classId, $subjectId);
                    }
                }
                
                setAlert('Mata pelajaran berhasil dibuat.', 'success');
            } else {
                setAlert('Gagal membuat mata pelajaran.', 'danger');
            }
        } else {
            setAlert('Nama mata pelajaran tidak boleh kosong.', 'danger');
        }
    } elseif ($_POST['action'] === 'update_subject') {
        $subjectId = $_POST['subject_id'];
        $subjectName = trim($_POST['subject_name']);
        $teacherId = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
        
        if (!empty($subjectName)) {
            $subjectData = [
                'subject_name' => $subjectName,
                'teacher_id' => $teacherId
            ];
            
            if (updateSubject($subjectId, $subjectData)) {
                // Get current classes for this subject
                $currentClasses = getClassesBySubject($subjectId);
                $currentClassIds = array_map(function($class) {
                    return $class['id'];
                }, $currentClasses);
                
                // Get new classes selected
                $newClassIds = isset($_POST['classes']) && is_array($_POST['classes']) ? $_POST['classes'] : [];
                
                // Remove subject from classes that are no longer selected
                foreach ($currentClassIds as $classId) {
                    if (!in_array($classId, $newClassIds)) {
                        removeSubjectFromClass($classId, $subjectId);
                    }
                }
                
                // Add subject to newly selected classes
                foreach ($newClassIds as $classId) {
                    if (!in_array($classId, $currentClassIds)) {
                        assignSubjectToClass($classId, $subjectId);
                    }
                }
                
                setAlert('Mata pelajaran berhasil diperbarui.', 'success');
            } else {
                setAlert('Gagal memperbarui mata pelajaran.', 'danger');
            }
        } else {
            setAlert('Nama mata pelajaran tidak boleh kosong.', 'danger');
        }
    } elseif ($_POST['action'] === 'delete_subject') {
        $subjectId = $_POST['subject_id'];
        
        // Delete subject-class relationships first
        executeQuery("DELETE FROM class_subject WHERE subject_id = ?", [$subjectId]);
        
        // Delete subject
        if (delete('subjects', 'id = ?', [$subjectId])) {
            setAlert('Mata pelajaran berhasil dihapus.', 'success');
        } else {
            setAlert('Gagal menghapus mata pelajaran.', 'danger');
        }
    }
    
    // Redirect to refresh the page and avoid resubmission
    redirect(BASE_URL . '/admin/manage_subjects.php');
}

// Get all subjects
$page = getParam('page', 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$subjects = getAllSubjects($limit, $offset);
$totalSubjects = countAllSubjects();

// Get all available teachers
$teachers = getAvailableTeachers();

// Get all classes for form
$allClasses = getAllClasses(100, 0);

// Setup pagination
$paginationData = getPaginationData(
    $totalSubjects,
    $limit,
    $page,
    BASE_URL . '/admin/manage_subjects.php?page=:page'
);

// Page details
$pageTitle = "Manajemen Mata Pelajaran";
$pageHeader = "Manajemen Mata Pelajaran";
$pageDescription = "Kelola mata pelajaran dalam sistem";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Daftar Mata Pelajaran</h2>
        <p class="text-sm text-gray-500">Total: <?php echo $totalSubjects; ?> mata pelajaran</p>
    </div>
    <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" data-modal="create-subject-modal">
        <i class="fas fa-plus mr-2"></i> Tambah Mata Pelajaran
    </button>
</div>

<!-- Subject List Table -->
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mata Pelajaran</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru Pengampu</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas yang Mengikuti</th>
                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($subjects)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data mata pelajaran</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $subject['id']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo escape($subject['subject_name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?php echo $subject['teacher_name'] ? escape($subject['teacher_name']) : '<span class="text-yellow-600">Belum ditetapkan</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">
                                    <?php 
                                    $classes = getClassesBySubject($subject['id']);
                                    if (!empty($classes)) {
                                        $classNames = array_map(function($class) {
                                            return escape($class['class_name']);
                                        }, $classes);
                                        echo implode(', ', $classNames);
                                    } else {
                                        echo '<span class="text-yellow-600">Belum ditetapkan</span>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button type="button" class="text-blue-600 hover:text-blue-900 mr-3" onclick="editSubject(<?php echo $subject['id']; ?>, '<?php echo escape($subject['subject_name']); ?>', <?php echo $subject['teacher_id'] ? $subject['teacher_id'] : 'null'; ?>, <?php echo htmlspecialchars(json_encode(array_column($classes, 'id'))); ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="text-red-600 hover:text-red-900" onclick="deleteSubject(<?php echo $subject['id']; ?>, '<?php echo escape($subject['subject_name']); ?>')">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalSubjects > $limit): ?>
        <div class="px-6 py-4 border-t">
            <?php echo generatePagination($paginationData); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create Subject Modal -->
<div id="create-subject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_subjects.php" method="post">
                <input type="hidden" name="action" value="create_subject">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Tambah Mata Pelajaran Baru
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="subject_name" class="block text-sm font-medium text-gray-700">Nama Mata Pelajaran</label>
                                    <input type="text" name="subject_name" id="subject_name" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="teacher_id" class="block text-sm font-medium text-gray-700">Guru Pengampu</label>
                                    <select name="teacher_id" id="teacher_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Pilih Guru Pengampu --</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo escape($teacher['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kelas yang Mengikuti</label>
                                    <div class="mt-2 max-h-48 overflow-y-auto p-2 border border-gray-300 rounded-md">
                                        <?php foreach ($allClasses as $class): ?>
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" id="class_<?php echo $class['id']; ?>" name="classes[]" value="<?php echo $class['id']; ?>"
                                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label for="class_<?php echo $class['id']; ?>" class="ml-2 text-sm text-gray-700">
                                                    <?php echo escape($class['class_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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

<!-- Edit Subject Modal -->
<div id="edit-subject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_subjects.php" method="post">
                <input type="hidden" name="action" value="update_subject">
                <input type="hidden" name="subject_id" id="edit_subject_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Edit Mata Pelajaran
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="edit_subject_name" class="block text-sm font-medium text-gray-700">Nama Mata Pelajaran</label>
                                    <input type="text" name="subject_name" id="edit_subject_name" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="edit_teacher_id" class="block text-sm font-medium text-gray-700">Guru Pengampu</label>
                                    <select name="teacher_id" id="edit_teacher_id"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Pilih Guru Pengampu --</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo escape($teacher['full_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kelas yang Mengikuti</label>
                                    <div class="mt-2 max-h-48 overflow-y-auto p-2 border border-gray-300 rounded-md">
                                        <?php foreach ($allClasses as $class): ?>
                                            <div class="flex items-center mb-2">
                                                <input type="checkbox" id="edit_class_<?php echo $class['id']; ?>" name="classes[]" value="<?php echo $class['id']; ?>"
                                                       class="edit-class-checkbox h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                                <label for="edit_class_<?php echo $class['id']; ?>" class="ml-2 text-sm text-gray-700">
                                                    <?php echo escape($class['class_name']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
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

<!-- Delete Subject Modal -->
<div id="delete-subject-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="<?php echo BASE_URL; ?>/admin/manage_subjects.php" method="post">
                <input type="hidden" name="action" value="delete_subject">
                <input type="hidden" name="subject_id" id="delete_subject_id" value="">
                
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Hapus Mata Pelajaran
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin menghapus mata pelajaran <span id="delete_subject_name" class="font-medium"></span>? 
                                    Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data terkait mata pelajaran ini.
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
    function editSubject(id, name, teacherId, classIds) {
        document.getElementById('edit_subject_id').value = id;
        document.getElementById('edit_subject_name').value = name;
        
        const teacherSelect = document.getElementById('edit_teacher_id');
        if (teacherId) {
            teacherSelect.value = teacherId;
        } else {
            teacherSelect.value = '';
        }
        
        // Reset all checkboxes
        const checkboxes = document.querySelectorAll('.edit-class-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Check selected classes
        classIds.forEach(classId => {
            const checkbox = document.getElementById('edit_class_' + classId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
        
        // Show modal
        const modal = document.getElementById('edit-subject-modal');
        modal.classList.remove('hidden');
    }
    
    function deleteSubject(id, name) {
        document.getElementById('delete_subject_id').value = id;
        document.getElementById('delete_subject_name').textContent = name;
        
        // Show modal
        const modal = document.getElementById('delete-subject-modal');
        modal.classList.remove('hidden');
    }
</script>

<?php include '../includes/footer.php'; ?>
