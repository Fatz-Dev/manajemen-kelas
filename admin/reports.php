<?php
// Include configuration and functions
require_once '../config.php';
require_once '../functions/helpers.php';
require_once '../functions/auth_functions.php';
require_once '../functions/class_functions.php';
require_once '../functions/assignment_functions.php';

// Set required role
$requiredRole = ROLE_ADMIN;

// Include authentication check
require_once '../includes/auth_check.php';

// Get filter parameters
$classFilter = getParam('class_id', 'all');
$reportType = getParam('report_type', 'grades');

// Get all classes for filter dropdown
$allClasses = getAllClasses(100, 0);

// Initialize report data
$reportData = [];
$reportTitle = '';
$reportDescription = '';

// Generate report based on type and filters
if ($reportType === 'grades') {
    $reportTitle = 'Laporan Nilai Siswa';
    
    if ($classFilter !== 'all') {
        // Get class details
        $classDetails = getClassById($classFilter);
        
        if ($classDetails) {
            $reportDescription = 'Daftar nilai rata-rata siswa untuk kelas ' . escape($classDetails['class_name']);
            $reportData = getStudentGradesByClass($classFilter);
        } else {
            setAlert('Kelas tidak ditemukan.', 'danger');
            redirect(BASE_URL . '/admin/reports.php');
        }
    } else {
        // Get overall average grades by class
        $reportDescription = 'Daftar nilai rata-rata siswa untuk semua kelas';
        
        $sql = "SELECT c.id as class_id, c.class_name, 
                       COUNT(DISTINCT u.id) as student_count,
                       AVG(s.grade) as average_grade,
                       MIN(s.grade) as min_grade,
                       MAX(s.grade) as max_grade,
                       COUNT(DISTINCT a.id) as assignment_count
                FROM classes c
                LEFT JOIN users u ON c.id = u.class_id AND u.role = 'student'
                LEFT JOIN submissions s ON u.id = s.student_id
                LEFT JOIN assignments a ON s.assignment_id = a.id AND a.class_id = c.id
                GROUP BY c.id
                ORDER BY c.class_name";
                
        $reportData = fetchAll($sql);
    }
} elseif ($reportType === 'submissions') {
    $reportTitle = 'Laporan Pengumpulan Tugas';
    
    if ($classFilter !== 'all') {
        // Get class details
        $classDetails = getClassById($classFilter);
        
        if ($classDetails) {
            $reportDescription = 'Daftar pengumpulan tugas untuk kelas ' . escape($classDetails['class_name']);
            
            $sql = "SELECT a.id as assignment_id, a.title, a.due_date, a.status,
                           s.subject_name, 
                           COUNT(sub.id) as submitted_count,
                           (SELECT COUNT(*) FROM users WHERE class_id = ? AND role = 'student') as total_students,
                           AVG(sub.grade) as average_grade
                    FROM assignments a
                    JOIN subjects s ON a.subject_id = s.id
                    LEFT JOIN submissions sub ON a.id = sub.assignment_id
                    WHERE a.class_id = ?
                    GROUP BY a.id
                    ORDER BY a.due_date DESC";
                    
            $reportData = fetchAll($sql, [$classFilter, $classFilter], 'ii');
        } else {
            setAlert('Kelas tidak ditemukan.', 'danger');
            redirect(BASE_URL . '/admin/reports.php');
        }
    } else {
        // Get overall submission stats
        $reportDescription = 'Daftar pengumpulan tugas untuk semua kelas';
        
        $sql = "SELECT c.id as class_id, c.class_name,
                       COUNT(DISTINCT a.id) as assignment_count,
                       COUNT(sub.id) as submission_count,
                       AVG(CASE WHEN sub.submitted_at <= a.due_date THEN 1 ELSE 0 END) as on_time_rate,
                       AVG(sub.grade) as average_grade
                FROM classes c
                LEFT JOIN assignments a ON c.id = a.class_id
                LEFT JOIN submissions sub ON a.id = sub.assignment_id
                GROUP BY c.id
                ORDER BY c.class_name";
                
        $reportData = fetchAll($sql);
    }
} elseif ($reportType === 'teachers') {
    $reportTitle = 'Laporan Aktivitas Guru';
    $reportDescription = 'Daftar aktivitas dan beban mengajar guru';
    
    $sql = "SELECT u.id, u.full_name, u.email, u.last_login,
                   COUNT(DISTINCT s.id) as subject_count,
                   COUNT(DISTINCT cs.class_id) as class_count,
                   COUNT(DISTINCT a.id) as assignment_count,
                   COUNT(DISTINCT sub.id) as graded_submission_count
            FROM users u
            LEFT JOIN subjects s ON u.id = s.teacher_id
            LEFT JOIN class_subject cs ON s.id = cs.subject_id
            LEFT JOIN assignments a ON u.id = a.created_by
            LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.grade IS NOT NULL
            WHERE u.role = 'teacher'
            GROUP BY u.id
            ORDER BY u.full_name";
            
    $reportData = fetchAll($sql);
} elseif ($reportType === 'activity') {
    $reportTitle = 'Laporan Aktivitas Sistem';
    $reportDescription = 'Statistik aktivitas penggunaan sistem';
    
    // Get general stats
    $stats = [
        'total_users' => countRows('users'),
        'total_students' => countUsersByRole(ROLE_STUDENT),
        'total_teachers' => countUsersByRole(ROLE_TEACHER),
        'total_admins' => countUsersByRole(ROLE_ADMIN),
        'total_classes' => countAllClasses(),
        'total_subjects' => countAllSubjects(),
        'total_assignments' => countRows('assignments'),
        'total_submissions' => countRows('submissions'),
        'graded_submissions' => countRows('submissions', 'grade IS NOT NULL'),
        'pending_submissions' => countRows('submissions', 'grade IS NULL'),
        'active_assignments' => countRows('assignments', "status = 'published' AND due_date >= NOW()"),
        'past_due_assignments' => countRows('assignments', "status = 'published' AND due_date < NOW()")
    ];
    
    // Monthly activity for the last 6 months
    $monthlyActivity = [];
    for ($i = 0; $i < 6; $i++) {
        $startDate = date('Y-m-d 00:00:00', strtotime("-$i month"));
        $endDate = date('Y-m-d 23:59:59', strtotime("-$i month +1 month -1 day"));
        
        $month = date('M Y', strtotime($startDate));
        
        $monthlyActivity[$month] = [
            'new_users' => countRows('users', 'created_at BETWEEN ? AND ?', [$startDate, $endDate]),
            'new_assignments' => countRows('assignments', 'created_at BETWEEN ? AND ?', [$startDate, $endDate]),
            'new_submissions' => countRows('submissions', 'submitted_at BETWEEN ? AND ?', [$startDate, $endDate])
        ];
    }
    
    $reportData = [
        'stats' => $stats,
        'monthly_activity' => array_reverse($monthlyActivity, true)
    ];
}

// Page details
$pageTitle = "Laporan";
$pageHeader = "Laporan";
$pageDescription = "Lihat dan analisis data dalam sistem";
$showSidebar = true;
?>

<?php include '../includes/header.php'; ?>

<!-- Report Filters -->
<div class="bg-white rounded-lg shadow-md p-4 mb-6">
    <form action="<?php echo BASE_URL; ?>/admin/reports.php" method="get" class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
        <div class="flex-1">
            <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Laporan</label>
            <select id="report_type" name="report_type" onchange="this.form.submit()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="grades" <?php echo $reportType === 'grades' ? 'selected' : ''; ?>>Laporan Nilai</option>
                <option value="submissions" <?php echo $reportType === 'submissions' ? 'selected' : ''; ?>>Laporan Pengumpulan Tugas</option>
                <option value="teachers" <?php echo $reportType === 'teachers' ? 'selected' : ''; ?>>Laporan Aktivitas Guru</option>
                <option value="activity" <?php echo $reportType === 'activity' ? 'selected' : ''; ?>>Laporan Aktivitas Sistem</option>
            </select>
        </div>
        
        <?php if ($reportType !== 'teachers' && $reportType !== 'activity'): ?>
        <div class="flex-1">
            <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Filter Kelas</label>
            <select id="class_id" name="class_id" onchange="this.form.submit()" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="all" <?php echo $classFilter === 'all' ? 'selected' : ''; ?>>Semua Kelas</option>
                <?php foreach ($allClasses as $class): ?>
                    <option value="<?php echo $class['id']; ?>" <?php echo $classFilter == $class['id'] ? 'selected' : ''; ?>>
                        <?php echo escape($class['class_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="flex-1">
            <label for="export_format" class="block text-sm font-medium text-gray-700 mb-1">Ekspor</label>
            <div class="flex space-x-2">
                <button type="button" onclick="printReport()" class="flex-1 inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-print mr-2"></i> Print
                </button>
                <button type="button" class="flex-1 inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-file-csv mr-2"></i> CSV
                </button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
    <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-800"><?php echo $reportTitle; ?></h2>
        <p class="text-sm text-gray-500"><?php echo $reportDescription; ?></p>
    </div>
    
    <div class="p-6" id="report-content">
        <?php if ($reportType === 'grades'): ?>
            <?php if ($classFilter === 'all'): ?>
                <!-- Overall Class Grades Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Siswa</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Tugas</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Rata-rata</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Terendah</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Tertinggi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data nilai</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="<?php echo BASE_URL; ?>/admin/reports.php?report_type=grades&class_id=<?php echo $row['class_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <?php echo escape($row['class_name']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['student_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['assignment_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['average_grade'] ? number_format($row['average_grade'], 1) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['min_grade'] ? number_format($row['min_grade'], 1) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['max_grade'] ? number_format($row['max_grade'], 1) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Class Student Grades Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pengumpulan</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Rata-rata</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Terendah</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Tertinggi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data nilai</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo escape($row['full_name']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['submissions_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['average_grade'] ? number_format($row['average_grade'], 1) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['min_grade'] ? number_format($row['min_grade'], 1) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['max_grade'] ? number_format($row['max_grade'], 1) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        
        <?php elseif ($reportType === 'submissions'): ?>
            <?php if ($classFilter === 'all'): ?>
                <!-- Overall Class Submissions Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Tugas</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pengumpulan</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate Tepat Waktu</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pengumpulan tugas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="<?php echo BASE_URL; ?>/admin/reports.php?report_type=submissions&class_id=<?php echo $row['class_id']; ?>" class="text-blue-600 hover:text-blue-900">
                                                <?php echo escape($row['class_name']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['assignment_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['submission_count']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['on_time_rate'] !== null ? number_format($row['on_time_rate'] * 100, 1) . '%' : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['average_grade'] ? number_format($row['average_grade'], 1) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <!-- Class Assignment Submissions Report -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenggat</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengumpulan</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($reportData)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data pengumpulan tugas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($reportData as $row): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?php echo escape($row['title']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($row['subject_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($row['due_date']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getAssignmentStatusClass($row['status'], $row['due_date']); ?>">
                                                <?php echo getAssignmentStatusText($row['status'], $row['due_date']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['submitted_count']; ?> / <?php echo $row['total_students']; ?>
                                            (<?php echo $row['total_students'] > 0 ? number_format(($row['submitted_count'] / $row['total_students']) * 100, 1) : 0; ?>%)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $row['average_grade'] ? number_format($row['average_grade'], 1) : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
        <?php elseif ($reportType === 'teachers'): ?>
            <!-- Teacher Activity Report -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terakhir Login</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml Mata Pelajaran</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml Kelas</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml Tugas</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jml Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($reportData)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data aktivitas guru</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reportData as $row): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo escape($row['full_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($row['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $row['last_login'] ? formatDatetime($row['last_login']) : 'Belum Pernah'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['subject_count']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['class_count']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['assignment_count']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $row['graded_submission_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        <?php elseif ($reportType === 'activity'): ?>
            <!-- System Activity Report -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Pengguna</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $reportData['stats']['total_users']; ?></h3>
                        </div>
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <span class="inline-block mr-4">Admin: <?php echo $reportData['stats']['total_admins']; ?></span>
                        <span class="inline-block mr-4">Guru: <?php echo $reportData['stats']['total_teachers']; ?></span>
                        <span class="inline-block">Murid: <?php echo $reportData['stats']['total_students']; ?></span>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Kelas</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $reportData['stats']['total_classes']; ?></h3>
                        </div>
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-school text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <span class="inline-block">Mata Pelajaran: <?php echo $reportData['stats']['total_subjects']; ?></span>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Tugas</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $reportData['stats']['total_assignments']; ?></h3>
                        </div>
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-tasks text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <span class="inline-block mr-4">Aktif: <?php echo $reportData['stats']['active_assignments']; ?></span>
                        <span class="inline-block">Tenggat Lewat: <?php echo $reportData['stats']['past_due_assignments']; ?></span>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 font-medium">Total Pengumpulan</p>
                            <h3 class="text-3xl font-bold text-gray-800"><?php echo $reportData['stats']['total_submissions']; ?></h3>
                        </div>
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-file-alt text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <span class="inline-block mr-4">Dinilai: <?php echo $reportData['stats']['graded_submissions']; ?></span>
                        <span class="inline-block">Belum Dinilai: <?php echo $reportData['stats']['pending_submissions']; ?></span>
                    </div>
                </div>
            </div>
            
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aktivitas Bulanan</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna Baru</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas Baru</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengumpulan Baru</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($reportData['monthly_activity'] as $month => $activity): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $month; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $activity['new_users']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $activity['new_assignments']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $activity['new_submissions']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function printReport() {
        const reportContent = document.getElementById('report-content');
        const printWindow = window.open('', '_blank');
        
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>${document.title}</title>
                <link rel="stylesheet" href="https://cdn.tailwindcss.com">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                <style>
                    @media print {
                        body {
                            padding: 20px;
                            font-family: Arial, sans-serif;
                        }
                        .print-header {
                            text-align: center;
                            margin-bottom: 20px;
                            padding-bottom: 10px;
                            border-bottom: 1px solid #ccc;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        th, td {
                            padding: 8px;
                            text-align: left;
                            border-bottom: 1px solid #ddd;
                        }
                        th {
                            background-color: #f3f4f6;
                            font-weight: bold;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="print-header">
                    <h1>${document.title}</h1>
                    <p>${'<?php echo $reportTitle; ?>'} - ${new Date().toLocaleDateString()}</p>
                    <p>${'<?php echo $reportDescription; ?>'}</p>
                </div>
                ${reportContent.innerHTML}
            </body>
            </html>
        `);
        
        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
        }, 500);
    }
</script>

<?php include '../includes/footer.php'; ?>
