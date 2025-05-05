# Petunjuk Instalasi Sistem Manajemen Kelas di XAMPP

Ikuti langkah-langkah berikut untuk menginstal dan menjalankan Sistem Manajemen Kelas di lingkungan XAMPP dengan MySQL:

## 1. Persiapan XAMPP

1. Pastikan XAMPP sudah terpasang di komputer Anda. Jika belum, unduh dari [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html) dan ikuti petunjuk instalasinya.
2. Jalankan XAMPP Control Panel dan aktifkan modul Apache dan MySQL.

## 2. Persiapan Database

1. Buka phpMyAdmin melalui browser dengan mengakses http://localhost/phpmyadmin/
2. Buat database baru dengan nama `manajemen_kelas_db`
3. Pilih database `manajemen_kelas_db` yang baru dibuat
4. Pilih tab 'Import' di bagian atas
5. Klik tombol 'Choose File' dan pilih file `manajemen_kelas_db.sql` dari aplikasi ini
6. Klik tombol 'Go' atau 'Import' di bagian bawah halaman
7. Tunggu hingga proses import selesai

## 3. Pemasangan Aplikasi

1. Salin seluruh folder aplikasi ini ke dalam folder `htdocs` di dalam instalasi XAMPP Anda
   - Biasanya terletak di `C:\xampp\htdocs\` (Windows) atau `/Applications/XAMPP/htdocs/` (macOS)
   - Buat folder baru dengan nama `manajemen_kelas` di dalam folder `htdocs`
   - Salin semua file aplikasi ke dalam folder tersebut
2. Konfigurasi database sudah diatur untuk XAMPP di file `config.php`
   - File ini menggunakan username `root` tanpa password (default XAMPP)
   - Jika Anda mengubah password MySQL di XAMPP, edit file `config.php` dan ubah nilai `DB_PASSWORD`

## 4. Akses Aplikasi

1. Buka browser dan akses aplikasi melalui URL: http://localhost/manajemen_kelas
2. Login dengan salah satu kredensial berikut:
   - Admin: username `admin`, password `admin123`
   - Guru: username `guru1`, password `admin123`
   - Siswa: username `siswa1`, password `admin123`

## 5. Konfigurasi Tambahan (Opsional)

- Jika Anda ingin mengubah URL base aplikasi (misal jika Anda memasang di folder berbeda), edit file `config.php` dan ubah nilai konstanta `BASE_URL`
- Sesuaikan baris berikut: `define('BASE_URL', 'http://localhost/manajemen_kelas');`

## 6. Troubleshooting

### Masalah Database
- Pastikan modul MySQL sudah berjalan di XAMPP Control Panel
- Periksa apakah username dan password database di `config.php` sudah benar
- Pastikan database `manajemen_kelas_db` sudah dibuat dan berisi tabel-tabel yang dibutuhkan

### Masalah Akses Aplikasi
- Pastikan Apache berjalan di XAMPP Control Panel
- Periksa apakah path aplikasi di folder htdocs sudah benar
- Periksa error log di `C:\xampp\apache\logs\error.log` (Windows) atau `/Applications/XAMPP/logs/error_log` (macOS)

### Masalah Login
- Pastikan database berisi data pengguna (admin, guru, siswa)
- Coba gunakan kredensial default yang tertera di atas

---

Jika Anda masih mengalami masalah setelah mengikuti petunjuk di atas, silakan hubungi administrator sistem.