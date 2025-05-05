# Catatan Penting Aplikasi Manajemen Kelas

## Kredensial Default

### Admin
- Username: `admin`
- Password: `admin123`

## Informasi Keamanan

1. **Perubahan Password**: Segera ubah password default admin setelah login pertama kali.

2. **Pengelolaan Hak Akses**:
   - Role Admin hanya bisa dibuat oleh Admin yang sudah ada
   - Role Guru dan Murid dapat mendaftar sendiri melalui halaman registrasi

3. **Upload File**:
   - Hanya file dengan ekstensi berikut yang diizinkan: .pdf, .doc, .docx, .txt, .jpg, .jpeg, .png
   - Ukuran maksimum file upload: 5MB

4. **Backup Database**:
   - Lakukan backup database secara berkala untuk mencegah kehilangan data
   - File database.sql dapat digunakan untuk memulihkan struktur database

## Catatan Teknis

1. **Konfigurasi PHP**:
   - Membutuhkan PHP 8.2.12 atau yang lebih baru
   - Ekstensi PHP yang dibutuhkan: mysqli, gd, mbstring, session

2. **Konfigurasi MySQL**:
   - Membutuhkan MySQL 5.7 atau yang lebih baru
   - Collation yang direkomendasikan: utf8mb4_unicode_ci

3. **Perhatian Web Server**:
   - File .htaccess harus diaktifkan (mod_rewrite untuk Apache)
   - Pastikan direktori uploads/ dan subdirektorinya memiliki izin tulis (755)

4. **Penanganan Error**:
   - Error logging diaktifkan secara default di lingkungan pengembangan
   - Untuk lingkungan produksi, ubah konfigurasi error di config.php:
     ```php
     ini_set('display_errors', 0);
     ini_set('display_startup_errors', 0);
     error_reporting(0);
     ```

## Panduan Upgrade

1. Backup database dan seluruh file sebelum melakukan upgrade.
2. Ganti seluruh file kecuali:
   - config.php (konfigurasi custom)
   - uploads/ (file yang telah diunggah)
3. Jalankan script upgrade database jika diperlukan.

## Keterbatasan Sistem

1. **Sesi Login**: Timeout sesi default adalah 30 menit. Pengguna akan otomatis logout setelah tidak aktif selama waktu tersebut.

2. **Kompatibilitas Browser**: Aplikasi dioptimalkan untuk Chrome, Firefox, dan Edge versi terbaru. Browser lama mungkin mengalami masalah tampilan.

3. **Load Server**: Sistem belum dioptimalkan untuk jumlah pengguna yang sangat besar (>1000 pengguna aktif bersamaan).

4. **Ukuran Upload**: Batasan ukuran upload file adalah 5MB per file. Untuk mengubah batas ini, edit file .htaccess dan config.php.

## Fitur yang Direncanakan

1. Notifikasi email untuk tugas baru dan deadline
2. Integrasi kalender untuk jadwal kelas dan tugas
3. Fitur diskusi dan komentar pada tugas
4. Sistem kehadiran siswa
5. Dashboard analitik yang lebih detail

## Troubleshooting

1. **Masalah Login**:
   - Pastikan username dan password benar
   - Periksa apakah status pengguna aktif
   - Hapus cookie browser jika perlu

2. **Upload File Error**:
   - Pastikan direktori uploads/ memiliki izin tulis
   - Periksa batas ukuran upload di PHP (php.ini)
   - Pastikan ekstensi file diizinkan

3. **Database Error**:
   - Periksa koneksi database di config.php
   - Pastikan struktur database sudah sesuai
   - Periksa log error PHP dan MySQL

4. **Tampilan Rusak**:
   - Hapus cache browser
   - Pastikan JavaScript diaktifkan
   - Gunakan browser yang didukung

## Kontak Dukungan

Untuk bantuan teknis atau pertanyaan, silakan hubungi:
- Email: [support@contoh.com](mailto:support@contoh.com)
- Telepon: 021-12345678 (Jam kerja: 08.00 - 16.00 WIB)
