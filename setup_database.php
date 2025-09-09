<?php
// Script untuk setup database lengkap
$servername = "localhost";
$username = "root";
$password = "";
$database = "sman6_db";

echo "<h2>Setup Database Sistem Informasi SMA Negeri 6 Surakarta</h2>";

// Buat koneksi
$conn = new mysqli($servername, $username, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database jika belum ada
$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($sql) === TRUE) {
    echo "<p style='color: green;'>Database '$database' berhasil dibuat atau sudah ada</p>";
} else {
    echo "<p style='color: red;'>Error membuat database: " . $conn->error . "</p>";
    $conn->close();
    exit;
}

// Pilih database
if (!$conn->select_db($database)) {
    echo "<p style='color: red;'>Gagal memilih database: " . $conn->error . "</p>";
    $conn->close();
    exit;
}

// Coba import dari database.sql dulu
$sqlFile = __DIR__ . '/database.sql';
$importSuccess = false;

if (file_exists($sqlFile)) {
    echo "<h3>Mengimpor struktur dari database.sql...</h3>";
    $sql = file_get_contents($sqlFile);
    
    // Hapus CREATE DATABASE dan USE statements karena kita sudah terhubung
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Pisahkan query berdasarkan titik koma
    $queries = explode(';', $sql);
    
    $success = 0;
    $failed = 0;
    
    // Jalankan setiap query
    foreach ($queries as $query) {
        $query = trim($query);
        
        // Skip empty queries or comments
        if (empty($query) || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
            continue;
        }
        
        // Skip SQLite format indicator if present
        if (strpos($query, 'SQLite format') !== false) {
            continue;
        }
        
        if ($conn->query($query) === TRUE) {
            $success++;
        } else {
            $failed++;
            // Hanya tampilkan error jika bukan karena tabel sudah ada
            if (strpos($conn->error, 'already exists') === false) {
                echo "<p style='color: orange;'>⚠ Warning: " . $conn->error . "</p>";
                echo "<p>Query: " . htmlspecialchars(substr($query, 0, 100)) . "...</p>";
            }
        }
    }
    
    echo "<p>Hasil import: $success berhasil, $failed gagal (bisa jadi tabel sudah ada)</p>";
    $importSuccess = true;
}

// Jika import dari database.sql gagal, buat tabel secara manual
if (!$importSuccess) {
    echo "<h3>Membuat tabel-tabel secara manual...</h3>";
    $tables = [
        "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `nama_lengkap` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `role` enum('admin','guru','siswa') NOT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `last_login` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `siswa` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nis` varchar(20) NOT NULL,
            `nisn` varchar(20) NOT NULL,
            `nama_lengkap` varchar(100) NOT NULL,
            `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
            `tempat_lahir` varchar(50) NOT NULL,
            `tanggal_lahir` date NOT NULL,
            `alamat` text NOT NULL,
            `telepon` varchar(15) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `kelas_id` int(11) DEFAULT NULL,
            `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
            `foto` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `nis` (`nis`),
            UNIQUE KEY `nisn` (`nisn`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `guru` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nip` varchar(20) NOT NULL,
            `nama_lengkap` varchar(100) NOT NULL,
            `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL,
            `tempat_lahir` varchar(50) NOT NULL,
            `tanggal_lahir` date NOT NULL,
            `alamat` text NOT NULL,
            `telepon` varchar(15) DEFAULT NULL,
            `email` varchar(100) DEFAULT NULL,
            `bidang_studi` varchar(50) NOT NULL,
            `status` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
            `foto` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `nip` (`nip`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `kelas` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nama_kelas` varchar(20) NOT NULL,
            `tingkat` enum('X','XI','XII') NOT NULL,
            `jurusan` enum('IPA','IPS','Bahasa') NOT NULL,
            `rombel` int(11) NOT NULL,
            `wali_kelas_id` int(11) DEFAULT NULL,
            `tahun_ajaran` varchar(10) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `nama_kelas` (`nama_kelas`,`tahun_ajaran`),
            KEY `wali_kelas_id` (`wali_kelas_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `mata_pelajaran` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `kode` varchar(10) NOT NULL,
            `nama` varchar(50) NOT NULL,
            `kkm` int(11) NOT NULL,
            `tingkat` enum('X','XI','XII','Semua') NOT NULL DEFAULT 'Semua',
            `jurusan` enum('IPA','IPS','Bahasa','Semua') NOT NULL DEFAULT 'Semua',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `kode` (`kode`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `jadwal_pelajaran` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `kelas_id` int(11) NOT NULL,
            `mata_pelajaran_id` int(11) NOT NULL,
            `guru_id` int(11) NOT NULL,
            `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
            `jam_mulai` time NOT NULL,
            `jam_selesai` time NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `kelas_id` (`kelas_id`),
            KEY `mata_pelajaran_id` (`mata_pelajaran_id`),
            KEY `guru_id` (`guru_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `nilai` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `siswa_id` int(11) NOT NULL,
            `mata_pelajaran_id` int(11) NOT NULL,
            `semester` enum('Ganjil','Genap') NOT NULL,
            `tahun_ajaran` varchar(10) NOT NULL,
            `nilai_tugas` float DEFAULT NULL,
            `nilai_uts` float DEFAULT NULL,
            `nilai_uas` float DEFAULT NULL,
            `nilai_akhir` float DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `siswa_mapel_semester` (`siswa_id`,`mata_pelajaran_id`,`semester`,`tahun_ajaran`),
            KEY `mata_pelajaran_id` (`mata_pelajaran_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

        "CREATE TABLE IF NOT EXISTS `absensi` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `siswa_id` int(11) NOT NULL,
            `tanggal` date NOT NULL,
            `status` enum('Hadir','Izin','Sakit','Alpa') NOT NULL,
            `keterangan` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `siswa_tanggal` (`siswa_id`,`tanggal`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    ];

    $success = 0;
    $failed = 0;

    foreach ($tables as $index => $sql) {
        if ($conn->query($sql) === TRUE) {
            $success++;
            echo "<p style='color: green;'>✓ Tabel " . ($index + 1) . " berhasil dibuat</p>";
        } else {
            $failed++;
            echo "<p style='color: red;'>✗ Gagal membuat tabel " . ($index + 1) . ": " . $conn->error . "</p>";
        }
    }

    echo "<p>Hasil: $success berhasil, $failed gagal</p>";
}

// Buat user admin default
echo "<h3>Membuat user admin...</h3>";
$hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
$adminSql = "INSERT IGNORE INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
('admin', '$hashedPassword', 'Administrator', 'admin@sman6ska.sch.id', 'admin')";

if ($conn->query($adminSql) === TRUE) {
    if ($conn->affected_rows > 0) {
        echo "<p style='color: green;'>✓ User admin berhasil dibuat</p>";
    } else {
        echo "<p style='color: orange;'>ℹ User admin sudah ada</p>";
        
        // Update password admin jika sudah ada
        $updateSql = "UPDATE `users` SET `password` = '$hashedPassword' WHERE `username` = 'admin'";
        if ($conn->query($updateSql) === TRUE) {
            echo "<p style='color: green;'>✓ Password admin berhasil diupdate</p>";
        } else {
            echo "<p style='color: red;'>✗ Gagal mengupdate password admin: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Gagal membuat user admin: " . $conn->error . "</p>";
}

$conn->close();
echo "<h3>Setup database selesai!</h3>";
echo "<p><a href='login.php'>Coba Login</a> | <a href='index.php'>Kembali ke Beranda</a></p>";
?>