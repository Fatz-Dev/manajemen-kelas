<?php
// Include authentication functions
require_once __DIR__ . '/../functions/auth_functions.php';

// Get current user
$currentUser = getCurrentUser();
$currentUri = get_active_uri();
?>

<nav class="bg-blue-600 text-white shadow-lg">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-3">
            <!-- Logo and brand name -->
            <div class="flex items-center space-x-2">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center space-x-2">
                    <i class="fas fa-graduation-cap text-xl"></i>
                    <span class="font-bold text-lg"><?php echo APP_NAME; ?></span>
                </a>
            </div>
            
            <!-- Main navigation -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="<?php echo BASE_URL; ?>" class="py-2 px-3 rounded hover:bg-blue-700 <?php echo $currentUri === '' || $currentUri === 'index.php' ? 'bg-blue-700' : ''; ?>">
                    Beranda
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (hasRole(ROLE_ADMIN)): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="py-2 px-3 rounded hover:bg-blue-700 <?php echo strpos($currentUri, 'admin/') === 0 ? 'bg-blue-700' : ''; ?>">
                            Dashboard Admin
                        </a>
                    <?php elseif (hasRole(ROLE_TEACHER)): ?>
                        <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="py-2 px-3 rounded hover:bg-blue-700 <?php echo strpos($currentUri, 'teacher/') === 0 ? 'bg-blue-700' : ''; ?>">
                            Dashboard Guru
                        </a>
                    <?php elseif (hasRole(ROLE_STUDENT)): ?>
                        <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="py-2 px-3 rounded hover:bg-blue-700 <?php echo strpos($currentUri, 'student/') === 0 ? 'bg-blue-700' : ''; ?>">
                            Dashboard Siswa
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- User menu -->
            <div class="flex items-center space-x-3">
                <?php if (isLoggedIn()): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-1 py-2 px-3 rounded hover:bg-blue-700 focus:outline-none">
                            <span><?php echo escape($currentUser['full_name']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-1 w-48 bg-white rounded shadow-lg py-2 z-20 hidden group-hover:block">
                            <div class="px-4 py-2 border-b">
                                <div class="text-sm font-medium text-gray-800"><?php echo escape($currentUser['full_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo getRoleDisplayName($currentUser['role']); ?></div>
                            </div>
                            
                            <?php if (hasRole(ROLE_ADMIN)): ?>
                                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt w-5 inline-block"></i> Dashboard
                                </a>
                            <?php elseif (hasRole(ROLE_TEACHER)): ?>
                                <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt w-5 inline-block"></i> Dashboard
                                </a>
                            <?php elseif (hasRole(ROLE_STUDENT)): ?>
                                <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt w-5 inline-block"></i> Dashboard
                                </a>
                            <?php endif; ?>
                            
                            <a href="#" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-user-circle w-5 inline-block"></i> Profil
                            </a>
                            
                            <a href="<?php echo BASE_URL; ?>/logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt w-5 inline-block"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>/login.php" class="py-2 px-4 bg-white text-blue-600 rounded shadow hover:bg-gray-100">
                        Login
                    </a>
                    <a href="<?php echo BASE_URL; ?>/register.php" class="py-2 px-4 bg-blue-700 text-white rounded shadow hover:bg-blue-800 hidden md:inline-block">
                        Daftar
                    </a>
                <?php endif; ?>
                
                <!-- Mobile menu button -->
                <button class="md:hidden focus:outline-none">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu (hidden by default) -->
    <div class="md:hidden hidden bg-blue-700 pb-3" id="mobile-menu">
        <div class="container mx-auto px-4 space-y-1">
            <a href="<?php echo BASE_URL; ?>" class="block py-2 px-3 rounded hover:bg-blue-600">
                Beranda
            </a>
            
            <?php if (isLoggedIn()): ?>
                <?php if (hasRole(ROLE_ADMIN)): ?>
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="block py-2 px-3 rounded hover:bg-blue-600">
                        Dashboard Admin
                    </a>
                <?php elseif (hasRole(ROLE_TEACHER)): ?>
                    <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="block py-2 px-3 rounded hover:bg-blue-600">
                        Dashboard Guru
                    </a>
                <?php elseif (hasRole(ROLE_STUDENT)): ?>
                    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="block py-2 px-3 rounded hover:bg-blue-600">
                        Dashboard Siswa
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/register.php" class="block py-2 px-3 rounded hover:bg-blue-600">
                    Daftar
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function() {
        const menuButton = document.querySelector('button.md\\:hidden');
        const mobileMenu = document.getElementById('mobile-menu');
        
        menuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    });
</script>
