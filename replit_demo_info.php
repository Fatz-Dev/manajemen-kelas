<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Kelas - Informasi</title>
    <!-- Tailwind CSS via CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6 bg-blue-600 text-white">
                <h1 class="text-3xl font-bold">Sistem Manajemen Kelas</h1>
                <p class="mt-2">Informasi Instalasi dan Penggunaan</p>
            </div>
            
            <div class="p-6">
                <div class="mb-6 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                    <h2 class="font-bold text-lg">⚠️ Perhatian</h2>
                    <p>Aplikasi ini didesain untuk berjalan di lingkungan <strong>XAMPP dengan MySQL</strong> dan bukan di Replit. Beberapa fitur mungkin tidak berfungsi dengan baik di Replit.</p>
                </div>
                
                <h2 class="text-2xl font-bold mb-4">Petunjuk Instalasi di XAMPP</h2>
                
                <ol class="list-decimal pl-6 space-y-3">
                    <li>
                        <strong>Persiapan XAMPP:</strong>
                        <ul class="list-disc pl-6 mt-1">
                            <li>Pastikan XAMPP sudah terpasang di komputer Anda</li>
                            <li>Jalankan XAMPP Control Panel</li>
                            <li>Aktifkan modul Apache dan MySQL</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Persiapan Database:</strong>
                        <ul class="list-disc pl-6 mt-1">
                            <li>Buka phpMyAdmin melalui <a href="http://localhost/phpmyadmin/" class="text-blue-600 hover:underline" target="_blank">http://localhost/phpmyadmin/</a></li>
                            <li>Buat database baru dengan nama <code class="bg-gray-200 px-2 py-1 rounded">manajemen_kelas_db</code></li>
                            <li>Impor file <code class="bg-gray-200 px-2 py-1 rounded">manajemen_kelas_db.sql</code> ke database</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Pemasangan Aplikasi:</strong>
                        <ul class="list-disc pl-6 mt-1">
                            <li>Salin seluruh folder aplikasi ke <code class="bg-gray-200 px-2 py-1 rounded">C:\xampp\htdocs\manajemen_kelas</code></li>
                            <li>Pastikan konfigurasi database di <code class="bg-gray-200 px-2 py-1 rounded">config.php</code> sudah benar</li>
                        </ul>
                    </li>
                    <li>
                        <strong>Akses Aplikasi:</strong>
                        <ul class="list-disc pl-6 mt-1">
                            <li>Buka browser dan akses <a href="http://localhost/manajemen_kelas" class="text-blue-600 hover:underline" target="_blank">http://localhost/manajemen_kelas</a></li>
                        </ul>
                    </li>
                </ol>
                
                <h3 class="text-xl font-bold mt-6 mb-3">Kredensial Default</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-300">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">Peran</th>
                                <th class="py-2 px-4 border-b">Username</th>
                                <th class="py-2 px-4 border-b">Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-2 px-4 border-b">Admin</td>
                                <td class="py-2 px-4 border-b">admin</td>
                                <td class="py-2 px-4 border-b">admin123</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border-b">Guru</td>
                                <td class="py-2 px-4 border-b">guru1 - guru4</td>
                                <td class="py-2 px-4 border-b">admin123</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-4 border-b">Siswa</td>
                                <td class="py-2 px-4 border-b">siswa1 - siswa6</td>
                                <td class="py-2 px-4 border-b">admin123</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-8 p-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700">
                    <h3 class="font-bold">File Penting</h3>
                    <ul class="list-disc pl-6 mt-2">
                        <li><strong>database.sql</strong> - Skema database MySQL</li>
                        <li><strong>manajemen_kelas_db.sql</strong> - Data awal untuk MySQL</li>
                        <li><strong>XAMPP_SETUP.md</strong> - Petunjuk lengkap instalasi di XAMPP</li>
                    </ul>
                </div>
            </div>
            
            <div class="px-6 py-3 bg-gray-100 text-center">
                <p>Untuk informasi lebih lanjut, silakan baca file <code class="bg-gray-200 px-2 py-1 rounded">README_XAMPP.md</code> dan <code class="bg-gray-200 px-2 py-1 rounded">XAMPP_SETUP.md</code></p>
                <p class="mt-2">Terima kasih!</p>
            </div>
        </div>
    </div>
</body>
</html>