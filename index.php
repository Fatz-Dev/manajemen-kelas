<?php
// Tampilkan pesan khusus untuk lingkungan Replit
if (getenv('REPL_ID')) {
    echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Manajemen Kelas - XAMPP Required</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-16 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg p-8 mb-8">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Aplikasi Manajemen Kelas</h1>
            
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
                <p class="font-bold">Perhatian!</p>
                <p>Aplikasi ini dirancang khusus untuk berjalan di lingkungan XAMPP dengan MySQL di komputer lokal.</p>
            </div>
            
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold mb-4">Petunjuk Instalasi</h2>
                <ol class="list-decimal pl-6 space-y-2">
                    <li>Pasang XAMPP di komputer Anda (versi 7.4 atau lebih baru)</li>
                    <li>Salin seluruh folder aplikasi ke dalam folder <code>htdocs/manajemen_kelas</code> di instalasi XAMPP Anda</li>
                    <li>Buat database baru bernama <code>manajemen_kelas_db</code> melalui phpMyAdmin</li>
                    <li>Import file <code>manajemen_kelas_db.sql</code> ke database tersebut</li>
                    <li>Akses aplikasi melalui browser di <code>http://localhost/manajemen_kelas</code></li>
                </ol>
                
                <h2 class="text-xl font-semibold mt-6 mb-4">Kredensial Default</h2>
                <ul class="list-disc pl-6 space-y-2">
                    <li><strong>Admin:</strong> username <code>admin</code>, password <code>admin123</code></li>
                    <li><strong>Guru:</strong> username <code>guru1</code>, password <code>admin123</code></li>
                    <li><strong>Siswa:</strong> username <code>siswa1</code>, password <code>admin123</code></li>
                </ul>
                
                <p class="mt-6">Untuk informasi lebih lanjut, silakan lihat <a href="README_XAMPP.md" class="text-blue-600 hover:underline">README_XAMPP.md</a> dan <a href="XAMPP_SETUP.md" class="text-blue-600 hover:underline">XAMPP_SETUP.md</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>';
    exit;
}

// Include configuration file
require_once 'config.php';
require_once 'functions/helpers.php';
require_once 'functions/auth_functions.php';

// Page details
$pageTitle = "Beranda";
$hideNavigation = false;
?>

<?php include 'includes/header.php'; ?>

<div class="relative bg-blue-600 overflow-hidden">
    <div class="max-w-7xl mx-auto">
        <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-white sm:text-5xl md:text-6xl">
                        <span class="block">Aplikasi Manajemen</span>
                        <span class="block text-blue-200">Kelas Online</span>
                    </h1>
                    <p class="mt-3 text-base text-blue-100 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        Memudahkan pengelolaan kelas, tugas, dan penilaian siswa dalam satu platform terintegrasi.
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <?php if (!isLoggedIn()): ?>
                            <div class="rounded-md shadow">
                                <a href="<?php echo BASE_URL; ?>/login.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-100 md:py-4 md:text-lg md:px-10">
                                    Login
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="<?php echo BASE_URL; ?>/register.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-500 hover:bg-blue-700 md:py-4 md:text-lg md:px-10">
                                    Daftar Akun
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="rounded-md shadow">
                                <?php if ($_SESSION['user_role'] === ROLE_ADMIN): ?>
                                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-100 md:py-4 md:text-lg md:px-10">
                                        Dashboard Admin
                                    </a>
                                <?php elseif ($_SESSION['user_role'] === ROLE_TEACHER): ?>
                                    <a href="<?php echo BASE_URL; ?>/teacher/dashboard.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-100 md:py-4 md:text-lg md:px-10">
                                        Dashboard Guru
                                    </a>
                                <?php elseif ($_SESSION['user_role'] === ROLE_STUDENT): ?>
                                    <a href="<?php echo BASE_URL; ?>/student/dashboard.php" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-100 md:py-4 md:text-lg md:px-10">
                                        Dashboard Siswa
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div class="hidden lg:block lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
        <div class="h-56 w-full sm:h-72 md:h-96 lg:w-full lg:h-full bg-blue-700 opacity-75 flex items-center justify-center">
            <svg class="h-64 w-64 text-white opacity-30" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M10 4a6 6 0 100 12 6 6 0 000-12zm0 10a4 4 0 100-8 4 4 0 000 8z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M10 6a4 4 0 100 8 4 4 0 000-8zm0 6a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Fitur Utama</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Manajemen Kelas yang Lebih Mudah
            </p>
            <p class="mt-4 max-w-2xl text-xl text-gray-500 mx-auto">
                Kelola kelas, tugas, dan penilaian dalam satu platform yang terintegrasi.
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Multi Role</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Tiga jenis pengguna: Admin, Guru, dan Murid dengan akses yang berbeda.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <i class="fas fa-tasks text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Manajemen Tugas</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Guru dapat membuat, mengelola, dan menilai tugas dengan mudah.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <i class="fas fa-chart-bar text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Laporan Penilaian</h3>
                                <p class="mt-2 text-base text-gray-500">
                                    Lihat perkembangan siswa dengan laporan nilai yang terperinci.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- How It Works Section -->
<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Cara Kerja</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Mudah Digunakan
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">1. Daftar Akun</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Buat akun sebagai Guru atau Murid untuk memulai.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">2. Masuk Kelas</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Guru membuat kelas atau siswa bergabung ke kelas yang ada.
                    </p>
                </div>

                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">3. Mulai Aktivitas</h3>
                    <p class="mt-2 text-base text-gray-500">
                        Kelola tugas, nilai, dan laporan dengan mudah dalam platform.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
