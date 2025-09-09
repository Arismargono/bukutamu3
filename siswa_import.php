<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: siswa.php");
    exit;
}

// Verifikasi CSRF token
if (!verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = "Token keamanan tidak valid.";
    header("Location: siswa.php");
    exit;
}

// Cek apakah file diupload
if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['error'] = "File tidak berhasil diupload.";
    header("Location: siswa.php");
    exit;
}

$file = $_FILES['excel_file'];

// Validasi ukuran file (maksimal 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    $_SESSION['error'] = "Ukuran file terlalu besar. Maksimal 5MB.";
    header("Location: siswa.php");
    exit;
}

// Validasi ekstensi file
$allowed_extensions = ['csv'];
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
    $_SESSION['error'] = "Saat ini hanya format CSV yang didukung. Silakan konversi file Excel Anda ke CSV terlebih dahulu.";
    header("Location: siswa.php");
    exit;
}

// Buat direktori upload jika belum ada
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Pindahkan file ke direktori upload
$upload_path = $upload_dir . uniqid() . '_' . $file['name'];
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    $_SESSION['error'] = "Gagal menyimpan file.";
    header("Location: siswa.php");
    exit;
}

try {
    // Baca file Excel menggunakan SimpleXLSX atau metode sederhana
    $data = readExcelFile($upload_path);
    
    if (empty($data)) {
        throw new Exception("File Excel kosong atau tidak dapat dibaca.");
    }
    
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    // Skip header row (baris pertama)
    for ($i = 1; $i < count($data); $i++) {
        $row = $data[$i];
        
        // Pastikan ada minimal 11 kolom
        if (count($row) < 11) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Data tidak lengkap";
            continue;
        }
        
        $nis = trim($row[0]);
        $nisn = trim($row[1]);
        $nama_lengkap = trim($row[2]);
        $jenis_kelamin = trim($row[3]);
        $kelas_id = trim($row[4]);
        $tempat_lahir = trim($row[5]);
        $tanggal_lahir = trim($row[6]);
        $alamat = trim($row[7]);
        $telepon = trim($row[8] ?? '');
        $email = trim($row[9] ?? '');
        $status = trim($row[10]);
        
        // Validasi data wajib
        if (empty($nis) || empty($nisn) || empty($nama_lengkap) || empty($jenis_kelamin) || 
            empty($kelas_id) || empty($tempat_lahir) || empty($tanggal_lahir) || 
            empty($alamat) || empty($status)) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Data wajib tidak lengkap";
            continue;
        }
        
        // Validasi jenis kelamin
        if (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'])) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Jenis kelamin harus 'Laki-laki' atau 'Perempuan'";
            continue;
        }
        
        // Validasi status
        if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Status harus 'Aktif' atau 'Tidak Aktif'";
            continue;
        }
        
        // Validasi format tanggal
        if (!validateDate($tanggal_lahir)) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Format tanggal lahir tidak valid (gunakan YYYY-MM-DD)";
            continue;
        }
        
        // Cek apakah NIS atau NISN sudah ada
        $check_sql = "SELECT COUNT(*) as count FROM siswa WHERE nis = ? OR nisn = ?";
        $check_result = executeQuery($check_sql, [$nis, $nisn]);
        $check_row = $check_result->fetchArray(SQLITE3_ASSOC);
        
        if ($check_row['count'] > 0) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": NIS atau NISN sudah terdaftar";
            continue;
        }
        
        // Cek apakah kelas_id valid
        $kelas_check_sql = "SELECT COUNT(*) as count FROM kelas WHERE id = ?";
        $kelas_check_result = executeQuery($kelas_check_sql, [$kelas_id]);
        $kelas_check_row = $kelas_check_result->fetchArray(SQLITE3_ASSOC);
        
        if ($kelas_check_row['count'] == 0) {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Kelas ID tidak valid";
            continue;
        }
        
        // Insert data siswa
        $insert_sql = "INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, kelas_id, tempat_lahir, tanggal_lahir, alamat, telepon, email, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))";
        
        $params = [
            $nis,
            $nisn,
            $nama_lengkap,
            $jenis_kelamin,
            $kelas_id,
            $tempat_lahir,
            $tanggal_lahir,
            $alamat,
            $telepon,
            $email,
            $status
        ];
        
        $insert_result = executeQuery($insert_sql, $params);
        
        if ($insert_result) {
            $success_count++;
        } else {
            $error_count++;
            $errors[] = "Baris " . ($i + 1) . ": Gagal menyimpan data";
        }
    }
    
    // Hapus file upload
    unlink($upload_path);
    
    // Set pesan hasil
    if ($success_count > 0) {
        $_SESSION['success'] = "Berhasil mengimport $success_count data siswa.";
        if ($error_count > 0) {
            $_SESSION['warning'] = "$error_count data gagal diimport. " . implode(', ', array_slice($errors, 0, 5));
        }
    } else {
        $_SESSION['error'] = "Tidak ada data yang berhasil diimport. " . implode(', ', array_slice($errors, 0, 5));
    }
    
} catch (Exception $e) {
    // Hapus file upload jika ada error
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: siswa.php");
exit;

// Fungsi untuk membaca file Excel/CSV
function readExcelFile($file_path) {
    $data = [];
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    
    if ($file_extension === 'csv') {
        // Untuk file CSV
        $handle = fopen($file_path, 'r');
        if ($handle) {
            while (($row = fgetcsv($handle)) !== false) {
                $data[] = $row;
            }
            fclose($handle);
        }
    } else if ($file_extension === 'xls' || $file_extension === 'xlsx') {
        // Untuk sementara, hanya mendukung CSV karena library Excel tidak tersedia
        throw new Exception("Format file Excel (.xls/.xlsx) belum didukung. Silakan gunakan format CSV atau konversi file Excel Anda ke CSV terlebih dahulu.");
    }
    
    return $data;
}



// Fungsi untuk validasi format tanggal
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>