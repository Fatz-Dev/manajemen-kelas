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

// Check if editing existing assignment
$isEditing = false;
$assignmentId = getParam('id', 0);
$assignment = null;

if ($assignmentId > 0) {
    $assignment = getAssignmentById($assignmentId);
    
    // Verify that this assignment belongs to the current teacher
    if (!$assignment || $assignment['created_by'] != $teacherId) {
        setAlert('Anda tidak memiliki akses untuk mengedit tugas ini.', 'danger');
        redirect(BASE_URL . '/teacher/assignments.php');
    }
    
    $isEditing = true;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $classId = $_POST['class_id'];
    $subjectId = $_POST['subject_id'];
    $dueDate = $_POST['due_date'];
    $dueTime = $_POST['due_time'];
    $status = $_POST['status'];
    
    // Combine date and time
    $dueDatetime = $dueDate . ' ' . $dueTime . ':00';
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Judul tugas harus diisi';
    }
    
    if (empty($description)) {
        $errors[] = 'Deskripsi tugas harus diisi';
    }
    
    if (empty($classId)) {
        $errors[] = 'Kelas harus dipilih';
    } else {
        // Verify teacher has access to this class
        if (!teacherTeachesClass($teacherId, $classId)) {
            $errors[] = 'Anda tidak memiliki akses untuk membuat tugas untuk kelas ini';
        }
    }
    
    if (empty($subjectId)) {
        $errors[] = 'Mata pelajaran harus dipilih';
    } else {
        // Verify subject belongs to this teacher
        $subject = getSubjectById($subjectId);
        if (!$subject || $subject['teacher_id'] != $teacherId) {
            $errors[] = 'Anda tidak dapat membuat tugas untuk mata pelajaran ini';
        }
    }
    
    if (empty($dueDate) || empty($dueTime)) {
        $errors[] = 'Tanggal dan waktu tenggat harus diisi';
    } else {
        // Verify due date is in the future
        $dueTimestamp = strtotime($dueDatetime);
        $currentTimestamp = time();
        
        if ($dueTimestamp < $currentTimestamp && $status === 'published') {
            $errors[] = 'Tanggal tenggat tidak boleh di masa lalu untuk tugas yang dipublikasikan';
        }
    }
    
    // Process if no errors
    if (empty($errors)) {
        $assignmentData = [
            'title' => $title,
            'description' => $description,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'due_date' => $dueDatetime,
            'status' => $status
        ];
        
        // Upload file if provided
        if (!empty($_FILES['file_upload']['name'])) {
            $uploadDir = '../uploads/assignments/';
            $allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
            
            $fileInfo = uploadFile($_FILES['file_upload'], $uploadDir, $allowedExtensions);
            
            if ($fileInfo) {
                $assignmentData['file_path'] = $fileInfo['filepath'];
            } else {
                $errors[] = 'Gagal mengunggah file. Pastikan tipe file diizinkan (PDF, DOC, DOCX, TXT, JPG, JPEG, PNG) dan ukuran tidak lebih dari 5MB.';
            }
        }
        
        if (empty($errors)) {
            if ($isEditing) {
                // Don't override the created_by field when updating
                if (updateAssignment($assignmentId, $assignmentData)) {
                    setAlert('Tugas berhasil diperbarui.', 'success');
                    redirect(BASE_URL . '/teacher/assignments.php');
                } else {
                    $errors[] = 'Gagal memperbarui tugas';
                }
            } else {
                // Add created_by field for new assignments
                $assignmentData['created_by'] = $teacherId;
                
                $newAssignmentId = createAssignment($assignmentData);
                
                if ($newAssignmentId) {
                    setAlert('Tugas berhasil dibuat.', 'success');
                    redirect(BASE_URL . '/teacher/assignments.php');
                } else {
                    $errors[] = 'Gagal membuat tugas';
                }
            }
        }
    }
    
    // Display errors if any
    if (!empty($errors)) {
        setAlert('Error: ' . implode(', ', $errors), 'danger');
    }
}

// Get classes taught by this teacher
$classesForTeacher = getClassesByTeacher($teacherId);

// Get subjects taught by this teacher
$subjectsForTeacher = getSubjectsByTeacher($teacherId);

// Default values for form
$formData = [
    'title' => '',
    'description' => '',
    'class_id' => getParam('class_id', ''),
    'subject_id' => '',
    'due_date' => date('Y-m-d', strtotime('+1 week')),
    'due_time' => '23:59',
    'status' => 'draft'
];

// If editing, populate form with assignment data
if ($isEditing && $assignment) {
    $dueDateParts = explode(' ', $assignment['due_date']);
    $formData = [
        'title' => $assignment['title'],
        'description' => $assignment['description'],
        'class_id' => $assignment['class_id'],
        'subject_id' => $assignment['subject_id'],
        'due_date' => isset($dueDateParts[0]) ? $dueDateParts[0] : date('Y-m-d'),
        'due_time' => isset($dueDateParts[1]) ? substr($dueDateParts[1], 0, 5) : '23:59',
        'status' => $assignment['status'],
        'file_path' => $assignment['file_path'] ?? null
    ];
}

// Page details
$pageTitle = $isEditing ? "Edit Tugas" : "Buat Tugas Baru";
$pageHeader = $isEditing ? "Edit Tugas" : "Buat Tugas Baru";
$pageDescription = $isEditing 
    ? "Perbarui informasi tugas"
    : "Buat tugas baru untuk siswa";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800"><?php echo $pageHeader; ?></h2>
        <p class="text-sm text-gray-500"><?php echo $pageDescription; ?></p>
    </div>
    
    <form action="<?php echo $isEditing ? BASE_URL . '/teacher/create_assignment.php?id=' . $assignmentId : BASE_URL . '/teacher/create_assignment.php'; ?>" method="post" enctype="multipart/form-data" class="p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul Tugas</label>
                    <input type="text" name="title" id="title" required value="<?php echo escape($formData['title']); ?>"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="class_id" id="class_id" required onchange="loadSubjects()"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($classesForTeacher as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $formData['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                    <select name="subject_id" id="subject_id" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        <?php foreach ($subjectsForTeacher as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php echo $formData['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                <?php echo escape($subject['subject_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Tanggal Tenggat</label>
                        <input type="date" name="due_date" id="due_date" required value="<?php echo $formData['due_date']; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="due_time" class="block text-sm font-medium text-gray-700">Waktu Tenggat</label>
                        <input type="time" name="due_time" id="due_time" required value="<?php echo $formData['due_time']; ?>"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <option value="draft" <?php echo $formData['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $formData['status'] === 'published' ? 'selected' : ''; ?>>Dipublikasikan</option>
                        <option value="closed" <?php echo $formData['status'] === 'closed' ? 'selected' : ''; ?>>Ditutup</option>
                    </select>
                </div>
                
                <div>
                    <label for="file_upload" class="block text-sm font-medium text-gray-700">Lampiran (Opsional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file_upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload file</span>
                                    <input id="file_upload" name="file_upload" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">atau drag dan drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, DOC, DOCX, TXT, JPG, JPEG, PNG hingga 5MB
                            </p>
                            <?php if (isset($formData['file_path']) && $formData['file_path']): ?>
                                <p class="text-xs text-green-600">
                                    File saat ini: <?php echo basename($formData['file_path']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi Tugas</label>
                <textarea name="description" id="description" rows="15" required
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"><?php echo escape($formData['description']); ?></textarea>
                <p class="mt-2 text-sm text-gray-500">
                    Deskripsikan tugas dengan jelas, termasuk instruksi dan kriteria penilaian.
                </p>
            </div>
        </div>
        
        <div class="flex justify-end space-x-3 pt-4 border-t">
            <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <?php echo $isEditing ? 'Perbarui Tugas' : 'Buat Tugas'; ?>
            </button>
        </div>
    </form>
</div>

<script>
    // File input preview and validation
    document.getElementById('file_upload').addEventListener('change', function(e) {
        const fileInput = e.target;
        const fileName = fileInput.files[0] ? fileInput.files[0].name : '';
        const fileSize = fileInput.files[0] ? fileInput.files[0].size : 0;
        const fileType = fileInput.files[0] ? fileInput.files[0].type : '';
        
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (fileName) {
            // Check file type
            if (!allowedTypes.includes(fileType)) {
                alert('Tipe file tidak diizinkan. Silakan pilih file PDF, DOC, DOCX, TXT, JPG, JPEG, atau PNG.');
                fileInput.value = '';
                return;
            }
            
            // Check file size
            if (fileSize > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 5MB.');
                fileInput.value = '';
                return;
            }
            
            // Show file name
            const fileNameDisplay = document.querySelector('.text-xs.text-gray-500');
            fileNameDisplay.innerHTML = `File dipilih: ${fileName} (${(fileSize / (1024 * 1024)).toFixed(2)} MB)`;
        }
    });
    
    // Filter subjects based on selected class
    function loadSubjects() {
        const classId = document.getElementById('class_id').value;
        const subjectSelect = document.getElementById('subject_id');
        const currentSubjectId = '<?php echo $formData['subject_id']; ?>';
        
        // Reset subject select
        subjectSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>';
        
        if (!classId) return;
        
        // Get subjects for this class and teacher
        const subjects = <?php echo json_encode($subjectsForTeacher); ?>;
        const teacherId = <?php echo $teacherId; ?>;
        
        // Filter subjects
        for (const subject of subjects) {
            const option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.subject_name;
            
            if (subject.id == currentSubjectId) {
                option.selected = true;
            }
            
            subjectSelect.appendChild(option);
        }
    }
    
    // Initialize subject list on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadSubjects();
    });
</script>

<?php include '../includes/footer.php'; ?>
