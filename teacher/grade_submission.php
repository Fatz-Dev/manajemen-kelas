<?php
require_once '../config.php';
require_once '../functions/auth_functions.php';
require_once '../functions/assignment_functions.php';
require_once '../functions/helpers.php';
require_once '../functions/class_functions.php';

// Ensure user is logged in and is a teacher
if (!isLoggedIn()) {
    setAlert('Silakan login untuk mengakses halaman ini.', 'danger');
    redirect(BASE_URL . '/login.php');
}

// Get current user
$user = getCurrentUser();
if ($user['role'] !== ROLE_TEACHER) {
    setAlert('Halaman ini hanya dapat diakses oleh guru.', 'danger');
    redirect(BASE_URL . '/');
}

$teacherId = $_SESSION['user_id'];
$submissionId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$submissionId) {
    setAlert('ID pengumpulan tugas tidak valid', 'danger');
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get submission details
$submission = getSubmissionById($submissionId);

if (!$submission) {
    setAlert('Pengumpulan tugas tidak ditemukan', 'danger');
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get assignment details
$assignment = getAssignmentById($submission['assignment_id']);

// Verify teacher has access to this assignment
if (!$assignment || $assignment['created_by'] != $teacherId) {
    setAlert('Anda tidak memiliki akses ke tugas ini', 'danger');
    redirect(BASE_URL . '/teacher/assignments.php');
}

// Get student details
$student = getUserById($submission['student_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade = $_POST['grade'] ?? null;
    $feedback = $_POST['feedback'] ?? '';
    
    // Validate grade
    if ($grade === null || !is_numeric($grade) || $grade < 0 || $grade > 100) {
        setAlert('Nilai harus berupa angka antara 0 dan 100', 'danger');
    } else {
        // Update submission with grade and feedback
        $updateData = [
            'grade' => $grade,
            'feedback' => $feedback,
            'graded_at' => date('Y-m-d H:i:s')
        ];
        
        if (updateSubmission($submissionId, $updateData)) {
            setAlert('Penilaian berhasil disimpan', 'success');
            redirect(BASE_URL . '/teacher/view_submissions.php?id=' . $assignment['id']);
        } else {
            setAlert('Gagal menyimpan penilaian', 'danger');
        }
    }
}

// Page title
$pageTitle = 'Nilai Tugas: ' . $assignment['title'];

// Include header
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
        <a href="<?php echo BASE_URL; ?>/teacher/view_submissions.php?id=<?php echo $assignment['id']; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Pengumpulan
        </a>
    </div>

    <!-- Student Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Informasi Siswa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Nama Siswa:</p>
                <p class="font-medium"><?php echo $student['full_name']; ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Kelas:</p>
                <p class="font-medium"><?php echo getClassNameById($student['class_id']); ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Username:</p>
                <p class="font-medium"><?php echo $student['username']; ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Waktu Pengumpulan:</p>
                <p class="font-medium"><?php echo formatDateTime($submission['submitted_at']); ?></p>
            </div>
        </div>
    </div>

    <!-- Submission Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Assignment details -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Detail Tugas</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Judul:</p>
                    <p class="font-medium"><?php echo $assignment['title']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Mata Pelajaran:</p>
                    <p class="font-medium"><?php echo $assignment['subject_name']; ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Tenggat Waktu:</p>
                    <p class="font-medium"><?php echo formatDate($assignment['due_date']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Status Pengumpulan:</p>
                    <?php if (isOverdue($assignment['due_date']) && strtotime($submission['submitted_at']) > strtotime($assignment['due_date'])): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Terlambat</span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Tepat Waktu</span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Deskripsi Tugas:</p>
                    <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm">
                        <?php echo nl2br(htmlspecialchars($assignment['description'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submission content -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Konten Pengumpulan</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-600">Jawaban Siswa:</p>
                    <div class="mt-1 p-3 bg-gray-50 rounded-md text-sm">
                        <?php echo nl2br(htmlspecialchars($submission['content'])); ?>
                    </div>
                </div>
                
                <?php if (!empty($submission['file_path'])): ?>
                <div>
                    <p class="text-sm text-gray-600">File Lampiran:</p>
                    <div class="mt-1">
                        <a href="<?php echo BASE_URL . '/' . $submission['file_path']; ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <i class="fas fa-download mr-1"></i> Download File
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Grading Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Form Penilaian</h2>
        
        <form action="" method="POST">
            <div class="space-y-4">
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700">Nilai (0-100)</label>
                    <input type="number" name="grade" id="grade" min="0" max="100" value="<?php echo isset($submission['grade']) ? $submission['grade'] : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" required>
                </div>
                
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                    <textarea name="feedback" id="feedback" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?php echo isset($submission['feedback']) ? $submission['feedback'] : ''; ?></textarea>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i> Simpan Penilaian
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>