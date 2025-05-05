# Aplikasi Manajemen Kelas

Aplikasi Manajemen Kelas adalah aplikasi berbasis web yang memudahkan pengelolaan kelas, tugas, dan penilaian siswa dalam satu platform terintegrasi.

## Fitur Utama

- **Multi-role System**: Admin, Guru, dan Murid memiliki hak akses dan fitur yang berbeda
- **Manajemen Kelas**: Pengelolaan kelas dan penugasan guru
- **Manajemen Mata Pelajaran**: Mengelola mata pelajaran dan mengaitkannya dengan kelas
- **Manajemen Tugas**: Membuat, mengumpulkan, dan menilai tugas
- **Sistem Penilaian**: Penilaian tugas dan laporan hasil belajar
- **Manajemen Pengguna**: Membuat, mengedit, dan mengelola pengguna

## Teknologi yang Digunakan

- PHP 8.2.12 (Native tanpa framework)
- MySQL / phpMyAdmin 5.2.1
- Tailwind CSS (untuk styling)
- Font Awesome (untuk ikon)

## Persyaratan Sistem

- Web server (Apache/Nginx) dengan PHP 8.2.12 atau lebih tinggi
- MySQL Server 5.7 atau lebih tinggi
- Browser modern (Chrome, Firefox, Safari, Edge)

## Instalasi

1. **Clone atau download repository ini ke direktori web server Anda**

2. **Konfigurasi database**
   - Buat database MySQL baru atau gunakan database yang sudah ada
   - Import file `database.sql` ke database Anda
   - Edit file `config.php` dan sesuaikan kredensial database:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'username_anda');
     define('DB_PASSWORD', 'password_anda');
     define('DB_NAME', 'manajemen_kelas_db');
     ```

3. **Konfigurasi aplikasi**
   - Edit file `config.php` dan sesuaikan BASE_URL dengan URL aplikasi Anda:
     ```php
     define('BASE_URL', 'http://localhost/manajemen_kelas');
     ```

4. **Konfigurasi direktori upload**
   - Pastikan direktori `uploads/` dan subdirektorinya (`assignments/` dan `submissions/`) memiliki izin tulis (writeable)
   ```
   chmod -R 755 uploads/
   