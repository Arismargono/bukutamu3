<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit;
}

// Verifikasi token CSRF untuk keamanan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!verify_csrf_token($_POST['csrf_token'])) {
        // Token CSRF tidak valid
        $_SESSION['error'] = "Token keamanan tidak valid. Silakan coba lagi.";
        header("Location: guru.php");
        exit;
    }

    // Ambil action dari form
    $action = sanitize_input($_POST['action']);

    // Proses berdasarkan action
    switch ($action) {
        case 'add':
            // Proses tambah guru
            addGuru();
            break;
        case 'edit':
            // Proses edit guru
            editGuru();
            break;
        case 'delete':
            // Proses hapus guru
            deleteGuru();
            break;
        default:
            // Action tidak valid
            $_SESSION['error'] = "Action tidak valid.";
            header("Location: guru.php");
            exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman guru
    header("Location: guru.php");
    exit;
}

/**
 * Fungsi untuk menambah data guru baru
 */
function addGuru() {
    global $conn;
    
    // Ambil data dari form
    $nip = sanitize_input($_POST['nip']);
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $jenis_kelamin = sanitize_input($_POST['jenis_kelamin']);
    $tempat_lahir = sanitize_input($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir']);
    $alamat = sanitize_input($_POST['alamat']);
    $telepon = sanitize_input($_POST['telepon']);
    $email = sanitize_input($_POST['email']);
    $bidang_studi = sanitize_input($_POST['bidang_studi']);
    $status = sanitize_input($_POST['status']);
    $status_wali_kelas = sanitize_input($_POST['status_wali_kelas']);
    
    // Validasi data
    if (empty($nip) || empty($nama_lengkap) || empty($jenis_kelamin) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($status) || empty($status_wali_kelas)) {
        $_SESSION['error'] = "Semua field wajib diisi kecuali telepon, email, dan bidang studi.";
        header("Location: guru.php");
        exit;
    }
    
    // Cek apakah NIP sudah ada
    $sql_check = "SELECT * FROM guru WHERE nip = ?";
    $result_check = executeQuery($sql_check, [$nip]);
    
    $exists = false;
    if ($result_check) {
        while ($row = $result_check->fetchArray(SQLITE3_ASSOC)) {
            $exists = true;
            break;
        }
    }
    
    if ($exists) {
        $_SESSION['error'] = "NIP sudah terdaftar.";
        header("Location: guru.php");
        exit;
    }
    
    // Query untuk insert data
    $sql = "INSERT INTO guru (nip, nama_lengkap, jenis_kelamin, tempat_lahir, 
            tanggal_lahir, alamat, telepon, email, bidang_studi, status, status_wali_kelas, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'))";
    
    $params = [$nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, 
               $tanggal_lahir, $alamat, $telepon, $email, $bidang_studi, $status, $status_wali_kelas];
    
    if (executeQuery($sql, $params)) {
        // Berhasil tambah data
        $_SESSION['success'] = "Data guru berhasil ditambahkan.";
    } else {
        // Gagal tambah data
        $_SESSION['error'] = "Gagal menambahkan data guru.";
    }
    
    header("Location: guru.php");
    exit;
}

/**
 * Fungsi untuk mengedit data guru
 */
function editGuru() {
    global $conn;
    
    // Ambil data dari form
    $id = sanitize_input($_POST['id']);
    $nip = sanitize_input($_POST['nip']);
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $jenis_kelamin = sanitize_input($_POST['jenis_kelamin']);
    $tempat_lahir = sanitize_input($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir']);
    $alamat = sanitize_input($_POST['alamat']);
    $telepon = sanitize_input($_POST['telepon']);
    $email = sanitize_input($_POST['email']);
    $bidang_studi = sanitize_input($_POST['bidang_studi']);
    $status = sanitize_input($_POST['status']);
    $status_wali_kelas = sanitize_input($_POST['status_wali_kelas']);
    
    // Validasi data
    if (empty($id) || empty($nip) || empty($nama_lengkap) || empty($jenis_kelamin) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($status) || empty($status_wali_kelas)) {
        $_SESSION['error'] = "Semua field wajib diisi kecuali telepon, email, dan bidang studi.";
        header("Location: guru.php");
        exit;
    }
    
    // Cek apakah NIP sudah ada (kecuali untuk guru yang sedang diedit)
    $sql_check = "SELECT * FROM guru WHERE nip = ? AND id != ?";
    $result_check = executeQuery($sql_check, [$nip, $id]);
    
    $exists = false;
    if ($result_check) {
        while ($row = $result_check->fetchArray(SQLITE3_ASSOC)) {
            $exists = true;
            break;
        }
    }
    
    if ($exists) {
        $_SESSION['error'] = "NIP sudah terdaftar.";
        header("Location: guru.php");
        exit;
    }
    
    // Query untuk update data
    $sql = "UPDATE guru SET nip = ?, nama_lengkap = ?, jenis_kelamin = ?, 
            tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, 
            telepon = ?, email = ?, bidang_studi = ?, status = ?, status_wali_kelas = ?, updated_at = datetime('now', 'localtime') 
            WHERE id = ?";
    
    $params = [$nip, $nama_lengkap, $jenis_kelamin, $tempat_lahir, 
               $tanggal_lahir, $alamat, $telepon, $email, $bidang_studi, $status, $status_wali_kelas, $id];
    
    if (executeQuery($sql, $params)) {
        // Berhasil update data
        $_SESSION['success'] = "Data guru berhasil diperbarui.";
    } else {
        // Gagal update data
        $_SESSION['error'] = "Gagal memperbarui data guru.";
    }
    
    header("Location: guru.php");
    exit;
}

/**
 * Fungsi untuk menghapus data guru
 */
function deleteGuru() {
    global $conn;
    
    // Ambil ID guru yang akan dihapus
    $id = sanitize_input($_POST['id']);
    
    // Validasi ID
    if (empty($id)) {
        $_SESSION['error'] = "ID guru tidak valid.";
        header("Location: guru.php");
        exit;
    }
    
    // Query untuk hapus data
    $sql = "DELETE FROM guru WHERE id = ?";
    
    if (executeQuery($sql, [$id])) {
        // Berhasil hapus data
        $_SESSION['success'] = "Data guru berhasil dihapus.";
    } else {
        // Gagal hapus data
        $_SESSION['error'] = "Gagal menghapus data guru.";
    }
    
    header("Location: guru.php");
    exit;
}
?>