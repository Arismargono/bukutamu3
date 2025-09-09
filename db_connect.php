<?php
/**
 * Koneksi Database
 * File ini menangani koneksi ke database MySQL untuk Sistem Informasi SMA Negeri 6 Surakarta
 */

// Memulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pengaturan koneksi database MySQL
$db_host = 'localhost'; // Host database
$db_user = 'root';      // Username database
$db_pass = '';          // Password database
$db_name = 'sman6_db';  // Nama database

// Fungsi untuk membuat koneksi database
function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    
    // Coba membuat koneksi
    try {
        // Buat koneksi MySQL
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        // Memeriksa koneksi
        if ($conn->connect_error) {
            throw new Exception("Koneksi database gagal: " . $conn->connect_error);
        }
        
        // Set karakter encoding
        $conn->set_charset("utf8");
        
        return $conn;
        
    } catch (Exception $e) {
        // Tangani error koneksi
        error_log($e->getMessage());
        // Jangan echo HTML untuk AJAX requests
        return false;
    }
}

// Buat koneksi database
$conn = connectDB();

// Fungsi untuk menjalankan query dengan penanganan error
function executeQuery($sql, $params = []) {
    global $conn;
    
    try {
        // Siapkan statement
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            throw new Exception("Persiapan query gagal: " . $conn->error);
        }
        
        // Bind parameter jika ada
        if (!empty($params)) {
            // Buat tipe parameter berdasarkan nilai
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= 'i'; // integer
                } elseif (is_float($param)) {
                    $types .= 'd'; // double
                } else {
                    $types .= 's'; // string
                }
            }
            
            // Bind parameter
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
        }
        
        // Eksekusi query
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            throw new Exception("Eksekusi query gagal: " . $stmt->error);
        }
        
        // Dapatkan hasil untuk SELECT queries
        if (stripos(trim($sql), 'SELECT') === 0) {
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            // Untuk INSERT, UPDATE, DELETE queries
            $stmt->close();
            return true;
        }
        
    } catch (Exception $e) {
        // Tangani error query
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk membersihkan input dari potensi serangan SQL Injection
 * @param string $data Data yang akan dibersihkan
 * @return string Data yang sudah dibersihkan
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    if ($conn) {
        $data = $conn->real_escape_string($data);
    }
    return $data;
}

/**
 * Fungsi untuk menghasilkan token CSRF untuk keamanan form
 * @return string Token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Fungsi untuk memverifikasi token CSRF
 * @param string $token Token yang akan diverifikasi
 * @return bool True jika token valid, false jika tidak
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Fungsi untuk mengimpor struktur database dari file SQL
 * @param string $sqlFile Path ke file SQL
 * @return bool True jika berhasil, false jika gagal
 */
function importSQL($sqlFile) {
    global $conn;
    
    try {
        // Baca file SQL
        $sql = file_get_contents($sqlFile);
        
        if (!$sql) {
            throw new Exception("Gagal membaca file SQL: $sqlFile");
        }
        
        // Pisahkan query berdasarkan titik koma
        $queries = explode(';', $sql);
        
        // Jalankan setiap query
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                if (!$conn->query($query)) {
                    throw new Exception("Gagal menjalankan query: " . $conn->error);
                }
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        // Tangani error impor
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk memeriksa dan mengimpor database jika diperlukan
 * @return bool True jika berhasil, false jika gagal
 */
function checkAndImportDatabase() {
    global $conn;
    
    // Cek apakah tabel users sudah ada
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    $tableExists = false;
    
    if ($result && $result->num_rows > 0) {
        $tableExists = true;
    }
    
    if (!$tableExists) {
        // Tabel belum ada, impor struktur database
        $sqlFile = __DIR__ . '/database.sql';
        
        if (!file_exists($sqlFile)) {
            // Jika file database.sql tidak ada, buat struktur tabel dasar
            return createBasicDatabaseStructure();
        }
        
        if (importSQL($sqlFile)) {
            error_log("Struktur database berhasil diimpor");
            return true;
        } else {
            error_log("Gagal mengimpor struktur database, mencoba membuat struktur dasar");
            return createBasicDatabaseStructure();
        }
    }
    
    return true;
}

/**
 * Fungsi untuk membuat struktur database dasar
 * @return bool True jika berhasil, false jika gagal
 */
function createBasicDatabaseStructure() {
    global $conn;
    
    try {
        // Buat tabel users (mengikuti struktur dari database.sql)
        $conn->query("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role ENUM('admin', 'guru', 'siswa') NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel siswa
        $conn->query("CREATE TABLE IF NOT EXISTS siswa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nis VARCHAR(20) NOT NULL UNIQUE,
            nisn VARCHAR(20) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
            tempat_lahir VARCHAR(50),
            tanggal_lahir DATE,
            alamat TEXT,
            telepon VARCHAR(15),
            email VARCHAR(100),
            kelas_id INT,
            status ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif',
            foto VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel guru
        $conn->query("CREATE TABLE IF NOT EXISTS guru (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nip VARCHAR(20) NOT NULL UNIQUE,
            nama_lengkap VARCHAR(100) NOT NULL,
            jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL,
            tempat_lahir VARCHAR(50),
            tanggal_lahir DATE,
            alamat TEXT,
            telepon VARCHAR(15),
            email VARCHAR(100),
            bidang_studi VARCHAR(50),
            status ENUM('Aktif', 'Tidak Aktif') NOT NULL DEFAULT 'Aktif',
            foto VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel kelas
        $conn->query("CREATE TABLE IF NOT EXISTS kelas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama_kelas VARCHAR(20) NOT NULL,
            tingkat ENUM('X', 'XI', 'XII') NOT NULL,
            jurusan ENUM('IPA', 'IPS', 'Bahasa') NOT NULL,
            rombel INT NOT NULL,
            wali_kelas_id INT,
            tahun_ajaran VARCHAR(10) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel mata_pelajaran
        $conn->query("CREATE TABLE IF NOT EXISTS mata_pelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kode VARCHAR(10) NOT NULL UNIQUE,
            nama VARCHAR(50) NOT NULL,
            kkm INT NOT NULL DEFAULT 75,
            tingkat ENUM('X', 'XI', 'XII', 'Semua') NOT NULL DEFAULT 'Semua',
            jurusan ENUM('IPA', 'IPS', 'Bahasa', 'Semua') NOT NULL DEFAULT 'Semua',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel jadwal_pelajaran
        $conn->query("CREATE TABLE IF NOT EXISTS jadwal_pelajaran (
            id INT AUTO_INCREMENT PRIMARY KEY,
            kelas_id INT NOT NULL,
            mata_pelajaran_id INT NOT NULL,
            guru_id INT NOT NULL,
            hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
            jam_mulai TIME NOT NULL,
            jam_selesai TIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel nilai
        $conn->query("CREATE TABLE IF NOT EXISTS nilai (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            mata_pelajaran_id INT NOT NULL,
            semester ENUM('Ganjil', 'Genap') NOT NULL,
            tahun_ajaran VARCHAR(10) NOT NULL,
            nilai_tugas FLOAT,
            nilai_uts FLOAT,
            nilai_uas FLOAT,
            nilai_akhir FLOAT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Buat tabel absensi
        $conn->query("CREATE TABLE IF NOT EXISTS absensi (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siswa_id INT NOT NULL,
            tanggal DATE NOT NULL,
            status ENUM('Hadir', 'Izin', 'Sakit', 'Alpa') NOT NULL,
            keterangan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Tambahkan user admin default
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT IGNORE INTO users (username, password, nama_lengkap, email, role) 
                    VALUES ('admin', '$hashedPassword', 'Administrator', 'admin@sman6-ska.sch.id', 'admin')");
        
        error_log("Struktur database dasar berhasil dibuat");
        return true;
        
    } catch (Exception $e) {
        error_log("Gagal membuat struktur database dasar: " . $e->getMessage());
        return false;
    }
}

// Jalankan pengecekan dan impor database jika diperlukan
if ($conn) {
    checkAndImportDatabase();
}
?>