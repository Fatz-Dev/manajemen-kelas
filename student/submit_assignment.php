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

// Check if past deadline
$isOverdue = isOverdue($assignment['due_date']);

// Get existing submission if any
$submission = getSubmissionByAssignmentAndStudent($assignmentId, $studentId);
$isEditing = isset($_GET['edit']) && $submission;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $filePath = null;
    $errorMessage = null;
    
    // Check if content is provided
    if (empty($content)) {
        $errorMessage = 'Jawaban tidak boleh kosong.';
    }
    
    // Handle file upload if any
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['size'] > 0) {
        $uploadDir = '../uploads/submissions/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = $_FILES['submission_file']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = 'submission_' . $studentId . '_' . $assignmentId . '_' . time() . '.' . $fileExtension;
        $targetFilePath = $uploadDir . $newFileName;
        
        // Check file size (max 5MB)
        if ($_FILES['submission_file']['size'] > 5 * 1024 * 1024) {
            $errorMessage = 'Ukuran file terlalu besar. Maksimal 5MB.';
        } 
        // Check file extension
        else if (!in_array(strtolower($fileExtension), ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png'])) {
            $errorMessage = 'Format file tidak didukung. Format yang didukung: PDF, DOC, DOCX, TXT, ZIP, RAR, JPG, JPEG, PNG.';
        }
        // Try to upload the file
        else if (move_uploaded_file($_FILES['submission_file']['tmp_name'], $targetFilePath)) {
            $filePath = $targetFilePath;
        } else {
            $errorMessage = 'Gagal mengunggah file.';
        }
    }
    
    // Process submission if no errors
    if (!$errorMessage) {
        // Prepare submission data
        $submissionData = [
            'assignment_id' => $assignmentId,
            'student_id' => $studentId,
            'content' => $content,
            'submitted_at' => date('Y-m-d H:i:s')
        ];
        
        if ($filePath) {
            $submissionData['file_path'] = str_replace('../', '', $filePath);
        }
        
        // Save or update submission
        if ($isEditing) {
            // If editing, update existing submission
            if (updateSubmission($submission['id'], $submissionData)) {
                setAlert('Tugas berhasil diperbarui.', 'success');
                redirect(BASE_URL . '/student/view_assignment.php?id=' . $assignmentId);
            } else {
                setAlert('Gagal memperbarui tugas.', 'danger');
            }
        } else {
            // If new submission, create new record
            if (createSubmission($submissionData)) {
                setAlert('Tugas berhasil dikumpulkan.', 'success');
                redirect(BASE_URL . '/student/view_assignment.php?id=' . $assignmentId);
            } else {
                setAlert('Gagal mengumpulkan tugas.', 'danger');
            }
        }
    } else {
        setAlert($errorMessage, 'danger');
    }
}

// Get subject information
$subject = getSubjectById($assignment['subject_id']);

// Set page title and description
$pageTitle = $isEditing ? "Edit Pengumpulan Tugas" : "Kumpulkan Tugas";
$pageHeader = $isEditing ? "Edit Pengumpulan Tugas" : "Kumpulkan Tugas";
$pageDescription = $assignment['title'];
$showSidebar = true;

// Include header
include '../includes/header.php';
?>

<div class="mb-6">
    <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignmentId; ?>" class="text-blue-600 hover:text-blue-800">
        <i class="fas fa-arrow-left mr-2"></i> Kembali ke Detail Tugas
    </a>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="bg-blue-600 px-6 py-4">
        <h2 class="text-xl font-bold text-white"><?php echo $pageHeader; ?></h2>
        <p class="text-blue-100"><?php echo escape($assignment['title']); ?></p>
    </div>
    
    <div class="p-6">
        <!-- Assignment Overview -->
        <div class="mb-6 bg-gray-50 p-4 rounded-lg">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <p class="text-sm text-gray-500">Mata Pelajaran: <?php echo escape($subject['subject_name'] ?? 'Tidak ada mata pelajaran'); ?></p>
                    <p class="text-sm text-gray-500">Tenggat: <?php echo formatDate($assignment['due_date']); ?></p>
                </div>
                
                <?php if ($isOverdue): ?>
                    <div class="px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm">
                        <i class="fas fa-exclamation-circle mr-1"></i> Melewati Tenggat
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Submission Form -->
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Jawaban Anda</label>
                <textarea id="content" name="content" rows="8" required class="block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300"><?php echo isset($submission) ? escape($submission['content']) : ''; ?></textarea>
                <p class="mt-1 text-xs text-gray-500">Tuliskan jawaban atau keterangan tugas Anda.</p>
            </div>
            
            <div class="mb-6">
                <label for="submission_file" class="block text-sm font-medium text-gray-700 mb-1">Lampiran File (Opsional)</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                    <div class="space-y-1 text-center">
                        <i class="fas fa-file-upload text-gray-400 text-3xl mb-2"></i>
                        <div class="flex text-sm text-gray-600">
                            <label for="submission_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Unggah file</span>
                                <input id="submission_file" name="submission_file" type="file" class="sr-only">
                            </label>
                            <p class="pl-1">atau seret dan taruh disini</p>
                        </div>
                        <p class="text-xs text-gray-500">
                            PDF, Word, TXT, ZIP, RAR, JPG, JPEG, PNG (Maks. 5MB)
                        </p>
                    </div>
                </div>
                <?php if ($isEditing && !empty($submission['file_path'])): ?>
                    <div class="mt-2 text-sm">
                        <span class="text-gray-500">File saat ini:</span>
                        <a href="<?php echo BASE_URL . '/' . $submission['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <?php echo basename($submission['file_path']); ?>
                        </a>
                        <p class="text-xs text-gray-500 mt-1">Mengunggah file baru akan menggantikan file yang lama.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="border-t border-gray-200 pt-4">
                <div class="flex justify-between">
                    <a href="<?php echo BASE_URL; ?>/student/view_assignment.php?id=<?php echo $assignmentId; ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i> <?php echo $isEditing ? 'Perbarui Tugas' : 'Kumpulkan Tugas'; ?>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Show selected file name
document.getElementById('submission_file').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name;
    if (fileName) {
        const fileInfoElement = document.createElement('p');
        fileInfoElement.className = 'mt-2 text-sm text-gray-500';
        fileInfoElement.innerHTML = `File terpilih: <span class="font-semibold">${fileName}</span>`;
        
        // Remove any previous file info
        const previousFileInfo = this.parentElement.parentElement.parentElement.parentElement.querySelector('.mt-2.text-sm');
        if (previousFileInfo && previousFileInfo.innerHTML.includes('File terpilih:')) {
            previousFileInfo.remove();
        }
        
        this.parentElement.parentElement.parentElement.parentElement.appendChild(fileInfoElement);
    }
});
</script>

<?php include '../includes/footer.php'; ?>