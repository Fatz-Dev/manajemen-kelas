# Aplikasi Manajemen Kelas

## Tentang Aplikasi

Aplikasi Manajemen Kelas adalah sistem berbasis web yang dirancang untuk mempermudah pengelolaan kelas di lingkungan sekolah. Aplikasi ini dikembangkan menggunakan PHP Native dan Tailwind CSS, dan dirancang untuk berjalan di lingkungan XAMPP dengan MySQL/phpMyAdmin.

## Fitur Utama

1. **Manajemen Pengguna (Admin)**
   - Mengelola akun guru dan siswa
   - Mengatur kelas dan mata pelajaran

2. **Manajemen Kelas (Guru)**
   - Membuat dan mengelola tugas untuk kelas tertentu
   - Memeriksa dan memberi nilai tugas siswa
   - Memberikan pengumuman ke kelas

3. **Aktivitas Siswa**
   - Melihat daftar tugas
   - Mengumpulkan tugas
   - Melihat nilai dan umpan balik

## Teknologi yang Digunakan

- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **Backend**: PHP Native (tanpa framework)
- **Database**: MySQL
- **Server**: Apache (XAMPP)

## Cara Penggunaan

Lihat petunjuk instalasi dan penggunaan di file [XAMPP_SETUP.md](XAMPP_SETUP.md).

## Kredensi Default

### Admin
- Username: admin
- Password: admin123

### Guru
- Username: guru1, guru2, guru3, guru4
- Password: admin123

### Siswa
- Username: siswa1, siswa2, siswa3, siswa4, siswa5, siswa6
- Password: admin123

## File Penting

- **manajemen_kelas_db.sql**: File SQL untuk mengimpor struktur dan data awal database
- **config.php**: Konfigurasi database untuk lingkungan XAMPP

## Kebutuhan Sistem

- XAMPP versi 7.4 atau lebih baru
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Browser web modern (Chrome, Firefox, Edge)

## Catatan Penting

Aplikasi ini dirancang untuk pembelajaran dan dapat dimodifikasi sesuai kebutuhan. File konfigurasi database sudah disesuaikan untuk lingkungan XAMPP standar dengan username root tanpa password.