# Aplikasi Manajemen Kelas

Aplikasi Manajemen Kelas adalah aplikasi berbasis web yang memudahkan pengelolaan kelas, tugas, dan penilaian siswa dalam satu platform terintegrasi. Dirancang khusus untuk dijalankan pada lingkungan XAMPP dengan MySQL.

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

- XAMPP dengan PHP 8.2.12 atau lebih tinggi
- MySQL Server 5.7 atau lebih tinggi (termasuk dalam paket XAMPP)
- Browser modern (Chrome, Firefox, Safari, Edge)

## Instalasi di XAMPP

1. **Download dan Install XAMPP**
   - Download XAMPP dari [situs resmi](https://www.apachefriends.org/download.html)
   - Install XAMPP mengikuti petunjuk instalasi

2. **Copy Aplikasi ke Folder XAMPP**
   - Salin semua file dan folder aplikasi ini ke direktori `htdocs/manajemen_kelas`
   - Pada Windows: `C:\xampp\htdocs\manajemen_kelas`
   - Pada macOS: `/Applications/XAMPP/htdocs/manajemen_kelas`
   - Pada Linux: `/opt/lampp/htdocs/manajemen_kelas`

3. **Buat dan Import Database**
   - Buka XAMPP Control Panel dan start service Apache dan MySQL
   - Buka phpMyAdmin di browser: `http://localhost/phpmyadmin`
   - Buat database baru bernama `manajemen_kelas_db`
   - Import file `manajemen_kelas_db.sql` ke database yang baru dibuat

4. **Konfigurasi Database (Jika Diperlukan)**
   - Secara default, aplikasi diatur untuk terhubung ke MySQL dengan:
     ```php
     define('DB_SERVER', 'localhost');
     define('DB_USERNAME', 'root');
     define('DB_PASSWORD', ''); // Kosong untuk XAMPP default
     define('DB_NAME', 'manajemen_kelas_db');
     ```
   - Jika Anda mengubah pengaturan MySQL di XAMPP, edit file `config.php` sesuai kebutuhan

5. **Akses Aplikasi**
   - Buka browser dan akses: `http://localhost/manajemen_kelas`
   - Login menggunakan salah satu kredensial default:
     - Admin: username `admin`, password `admin123`
     - Guru: username `guru1`, password `admin123`
     - Murid: username `siswa1`, password `admin123`

## Struktur Folder

```
/manajemen_kelas/
├── admin/                  # Area admin
├── assets/                 # Asset statis (CSS, JS, gambar)
├── auth/                   # Proses autentikasi
├── functions/              # File fungsi-fungsi
├── includes/               # Komponen yang digunakan bersama
├── teacher/                # Area guru
├── uploads/                # Folder upload file
│   ├── assignments/        # File tugas dari guru
│   └── submissions/        # File submission dari siswa
├── config.php              # Konfigurasi database dan aplikasi
├── index.php               # Halaman utama
├── login.php               # Halaman login
├── register.php            # Halaman pendaftaran
└── ... file lainnya
```

## Troubleshooting

- **Error Database**: Pastikan service MySQL di XAMPP sudah berjalan dan database telah diimport dengan benar
- **Error Akses**: Periksa URL akses sudah benar (`http://localhost/manajemen_kelas`)
- **Error File Permission**: Pastikan folder `uploads` dan subfolder memiliki izin tulis
- **Error Login**: Gunakan kredensial default yang tersedia di atas

## Informasi Tambahan

Untuk petunjuk lebih detail tentang instalasi dan penggunaan di XAMPP, silakan baca file `XAMPP_SETUP.md`
