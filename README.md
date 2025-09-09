# Sistem Informasi SMA Negeri 6 Surakarta

## Deskripsi
Sistem Informasi SMA Negeri 6 Surakarta adalah aplikasi web manajemen sekolah yang dirancang khusus untuk memenuhi kebutuhan administrasi dan pengelolaan data di SMA Negeri 6 Surakarta. Sistem ini menyediakan antarmuka yang user-friendly dengan fokus pada pengguna administrator untuk mengelola berbagai aspek administrasi sekolah.

## Fitur Utama
- **Manajemen Siswa**: Pengelolaan data siswa, absensi, dan informasi akademik
- **Manajemen Guru**: Pengelolaan data guru, jadwal mengajar, dan evaluasi kinerja
- **Manajemen Kelas**: Pengelolaan kelas, jadwal pelajaran, dan distribusi siswa
- **Manajemen Mata Pelajaran**: Pengelolaan kurikulum, silabus, dan materi pembelajaran
- **Manajemen Nilai**: Pengelolaan penilaian, rapor, dan analisis hasil belajar
- **Administrasi Sekolah**: Pengelolaan administrasi, keuangan, dan inventaris sekolah

## Teknologi yang Digunakan
- PHP (Backend)
- MySQL (Database)
- HTML, CSS, JavaScript (Frontend)
- Bootstrap 5 (Framework CSS)
- Bootstrap Icons (Icon Library)

## Struktur Proyek
```
├── index.php           # Halaman utama/landing page
├── login.php           # Halaman login administrator
├── dashboard.php       # Dashboard administrator
├── siswa.php           # Halaman manajemen siswa
├── config.php          # Konfigurasi database dan fungsi utilitas
└── README.md           # Dokumentasi proyek
```

## Instalasi dan Pengaturan

### Prasyarat
- Web server (Apache/Nginx)
- PHP 8.0 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi

### Langkah Instalasi
1. Clone atau download repositori ini ke direktori web server Anda
2. Buat database MySQL baru dengan nama `sman6_db`
3. Import struktur database dari file SQL (akan ditambahkan nanti)
4. Sesuaikan pengaturan koneksi database di file `config.php`
5. Akses aplikasi melalui browser web

### Pengaturan Database
Untuk mengatur koneksi database, edit file `config.php` dan sesuaikan parameter berikut:
```php
$db_host = 'localhost'; // Host database
$db_user = 'root';      // Username database
$db_pass = '';          // Password database
$db_name = 'sman6_db';  // Nama database
```

## Penggunaan
1. Akses halaman utama melalui `index.php`
2. Login sebagai administrator melalui `login.php`
3. Setelah login berhasil, Anda akan diarahkan ke dashboard administrator
4. Gunakan menu navigasi di sidebar untuk mengakses berbagai fitur pengelolaan

## Pengembangan Selanjutnya
Berikut adalah beberapa fitur yang direncanakan untuk pengembangan selanjutnya:

1. **Modul Guru**
   - Halaman manajemen guru
   - Pengelolaan jadwal mengajar
   - Evaluasi kinerja guru

2. **Modul Kelas**
   - Pengelolaan ruang kelas
   - Pengelolaan jadwal pelajaran
   - Distribusi siswa per kelas

3. **Modul Mata Pelajaran**
   - Pengelolaan kurikulum
   - Pengelolaan silabus
   - Pengelolaan materi pembelajaran

4. **Modul Nilai**
   - Input nilai siswa
   - Pembuatan rapor
   - Analisis hasil belajar

5. **Modul Administrasi**
   - Pengelolaan keuangan sekolah
   - Pengelolaan inventaris
   - Pengelolaan surat-menyurat

## Keamanan
Sistem ini telah dilengkapi dengan beberapa fitur keamanan dasar:
- Sanitasi input untuk mencegah SQL Injection
- Proteksi CSRF untuk keamanan form
- Enkripsi password (akan diimplementasikan)

## Kontak
Untuk informasi lebih lanjut, silakan hubungi administrator SMA Negeri 6 Surakarta.

---

&copy; 2025 SMA Negeri 6 Surakarta. Hak Cipta Dilindungi.