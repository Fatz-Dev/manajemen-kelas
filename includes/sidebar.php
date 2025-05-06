<?php
// Get current user role
$userRole = $_SESSION['user_role'] ?? '';
$currentUri = get_active_uri();
?>

<aside class="w-64 bg-white shadow-md fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 overflow-y-auto transition-transform duration-200 ease-in-out z-20 pt-16 mt-1 lg:mt-0" id="sidebar">
    
    <div class="px-4 py-6">
        
        <div class="border-b pb-4 mb-4">
            <div class="flex items-center mb-3">
                <div class="bg-blue-100 text-blue-600 p-2 rounded">
                    <?php if ($userRole === ROLE_ADMIN): ?>
                        <i class="fas fa-user-shield"></i>
                    <?php elseif ($userRole === ROLE_TEACHER): ?>
                        <i class="fas fa-chalkboard-teacher"></i>
                    <?php elseif ($userRole === ROLE_STUDENT): ?>
                        <i class="fas fa-user-graduate"></i>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900"><?php echo $_SESSION['user_fullname'] ?? ''; ?></p>
                    <p class="text-xs text-gray-500"><?php echo getRoleDisplayName($userRole); ?></p>
                </div>
            </div>
        </div>
        
        <!-- Admin Sidebar Menu -->
        <?php if ($userRole === ROLE_ADMIN): ?>
            <div class="space-y-1">
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'admin/dashboard.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/manage_users.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'admin/manage_users.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span>Manajemen Pengguna</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/manage_classes.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'admin/manage_classes.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-school w-5"></i>
                    <span>Manajemen Kelas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/manage_subjects.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'admin/manage_subjects.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-book w-5"></i>
                    <span>Manajemen Mata Pelajaran</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/admin/reports.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'admin/reports.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-chart-bar w-5"></i>
                    <span>Laporan</span>
                </a>
            </div>
        
        <!-- Teacher Sidebar Menu -->
        <?php elseif ($userRole === ROLE_TEACHER): ?>
            <div class="space-y-1">
                <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'teacher/dashboard.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/my_classes.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'teacher/my_classes.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-school w-5"></i>
                    <span>Kelas Saya</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/assignments.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'teacher/assignments.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-tasks w-5"></i>
                    <span>Tugas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/submissions.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'teacher/submissions.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-file-alt w-5"></i>
                    <span>Pengumpulan Tugas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/teacher/grading.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'teacher/grading.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-star w-5"></i>
                    <span>Penilaian</span>
                </a>
            </div>
            
        <!-- Student Sidebar Menu -->
        <?php elseif ($userRole === ROLE_STUDENT): ?>
            <div class="space-y-1">
                <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'student/dashboard.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-tachometer-alt w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/student/my_class.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'student/my_class.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-school w-5"></i>
                    <span>Kelas Saya</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/student/assignments.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'student/assignments.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-tasks w-5"></i>
                    <span>Tugas</span>
                </a>
                <a href="<?php echo BASE_URL; ?>/student/grades.php" class="flex items-center text-gray-700 py-2 px-3 rounded hover:bg-gray-100 <?php echo $currentUri === 'student/grades.php' ? 'bg-gray-100 font-medium' : ''; ?>">
                    <i class="fas fa-star w-5"></i>
                    <span>Nilai</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</aside>

<!-- Mobile Sidebar Backdrop -->
<div class="fixed inset-0 bg-black opacity-0 pointer-events-none transition-opacity duration-200 ease-in-out z-0" id="sidebar-backdrop"></div>

<!-- Mobile Sidebar Toggle Button -->
<button class="lg:hidden fixed bottom-4 right-4 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center focus:outline-none z-20" id="sidebar-toggle">
    <i class="fas fa-bars"></i>
</button>

<script>
    // Sidebar toggle for mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const toggle = document.getElementById('sidebar-toggle');
        
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('opacity-0');
            backdrop.classList.toggle('pointer-events-none');
        });
        
        backdrop.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('opacity-0');
            backdrop.classList.add('pointer-events-none');
        });
    });
</script>
