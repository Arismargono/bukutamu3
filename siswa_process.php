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
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid. Silakan coba lagi.']);
        exit;
    }

    // Ambil action dari form
    $action = sanitize_input($_POST['action']);

    // Proses berdasarkan action
    switch ($action) {
        case 'add':
            // Proses tambah siswa
            addSiswa();
            break;
        case 'edit':
            // Proses edit siswa
            editSiswa();
            break;
        case 'delete':
            // Proses hapus siswa
            deleteSiswa();
            break;
        default:
            // Action tidak valid
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Action tidak valid.']);
            exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman siswa
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Method tidak valid.']);
    exit;
}

/**
 * Fungsi untuk menambah data siswa baru
 */
function addSiswa() {
    global $conn;
    
    // Ambil data dari form
    $nis = sanitize_input($_POST['nis']);
    $nisn = sanitize_input($_POST['nisn']);
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $jenis_kelamin = sanitize_input($_POST['jenis_kelamin']);
    $kelas_id = sanitize_input($_POST['kelas_id']);
    $tempat_lahir = sanitize_input($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir']);
    $alamat = sanitize_input($_POST['alamat']);
    $telepon = sanitize_input($_POST['telepon']);
    $email = sanitize_input($_POST['email']);
    $status = sanitize_input($_POST['status']);
    
    // Validasi data
    if (empty($nis) || empty($nisn) || empty($nama_lengkap) || empty($jenis_kelamin) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($status)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi kecuali telepon dan email.']);
        exit;
    }
    
    // Cek apakah NIS atau NISN sudah ada
    $sql_check = "SELECT * FROM siswa WHERE nis = ? OR nisn = ?";
    $result_check = executeQuery($sql_check, [$nis, $nisn]);
    
    if ($result_check && $result_check->fetchArray()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'NIS atau NISN sudah terdaftar.']);
        exit;
    }
    
    // Query untuk insert data
    $sql = "INSERT INTO siswa (nis, nisn, nama_lengkap, jenis_kelamin, kelas_id, tempat_lahir, 
            tanggal_lahir, alamat, telepon, email, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))";
    
    $result = executeQuery($sql, [$nis, $nisn, $nama_lengkap, $jenis_kelamin, $kelas_id, 
                      $tempat_lahir, $tanggal_lahir, $alamat, $telepon, $email, $status]);
    
    header('Content-Type: application/json');
    if ($result) {
        // Berhasil tambah data
        $_SESSION['success'] = "Data siswa berhasil ditambahkan.";
        echo json_encode(['success' => true, 'message' => 'Data siswa berhasil ditambahkan.']);
    } else {
        // Gagal tambah data
        echo json_encode(['success' => false, 'message' => 'Gagal menambahkan data siswa. Silakan coba lagi.']);
    }
    
    exit;
}

/**
 * Fungsi untuk mengedit data siswa
 */
function editSiswa() {
    global $conn;
    
    // Ambil data dari form
    $id = sanitize_input($_POST['id']);
    $nis = sanitize_input($_POST['nis']);
    $nisn = sanitize_input($_POST['nisn']);
    $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
    $jenis_kelamin = sanitize_input($_POST['jenis_kelamin']);
    $kelas_id = sanitize_input($_POST['kelas_id']);
    $tempat_lahir = sanitize_input($_POST['tempat_lahir']);
    $tanggal_lahir = sanitize_input($_POST['tanggal_lahir']);
    $alamat = sanitize_input($_POST['alamat']);
    $telepon = sanitize_input($_POST['telepon']);
    $email = sanitize_input($_POST['email']);
    $status = sanitize_input($_POST['status']);
    
    // Validasi data
    if (empty($id) || empty($nis) || empty($nisn) || empty($nama_lengkap) || empty($jenis_kelamin) || 
        empty($tempat_lahir) || empty($tanggal_lahir) || empty($alamat) || empty($status)) {
        $_SESSION['error'] = "Semua field wajib diisi kecuali telepon dan email.";
        header("Location: siswa.php");
        exit;
    }
    
    // Cek apakah NIS atau NISN sudah ada (kecuali untuk siswa yang sedang diedit)
    $sql_check = "SELECT * FROM siswa WHERE (nis = ? OR nisn = ?) AND id != ?";
    $result_check = executeQuery($sql_check, [$nis, $nisn, $id]);
    
    if ($result_check && $result_check->fetchArray()) {
        $_SESSION['error'] = "NIS atau NISN sudah terdaftar.";
        header("Location: siswa.php");
        exit;
    }
    
    // Query untuk update data
    $sql = "UPDATE siswa SET nis = ?, nisn = ?, nama_lengkap = ?, jenis_kelamin = ?, 
            kelas_id = ?, tempat_lahir = ?, tanggal_lahir = ?, alamat = ?, 
            telepon = ?, email = ?, status = ?, updated_at = datetime('now') 
            WHERE id = ?";
    
    $result = executeQuery($sql, [$nis, $nisn, $nama_lengkap, $jenis_kelamin, $kelas_id, 
                      $tempat_lahir, $tanggal_lahir, $alamat, $telepon, $email, $status, $id]);
    
    if ($result) {
        // Berhasil update data
        $_SESSION['success'] = "Data siswa berhasil diperbarui.";
    } else {
        // Gagal update data
        $_SESSION['error'] = "Gagal memperbarui data siswa. Silakan coba lagi.";
    }
    
    header("Location: siswa.php");
    exit;
}

/**
 * Fungsi untuk menghapus data siswa
 */
function deleteSiswa() {
    global $conn;
    
    // Ambil ID siswa yang akan dihapus
    $id = sanitize_input($_POST['id']);
    
    // Validasi ID
    if (empty($id)) {
        $_SESSION['error'] = "ID siswa tidak valid.";
        header("Location: siswa.php");
        exit;
    }
    
    // Query untuk hapus data
    $sql = "DELETE FROM siswa WHERE id = ?";
    $result = executeQuery($sql, [$id]);
    
    if ($result) {
        // Berhasil hapus data
        $_SESSION['success'] = "Data siswa berhasil dihapus.";
    } else {
        // Gagal hapus data
        $_SESSION['error'] = "Gagal menghapus data siswa. Silakan coba lagi.";
    }
    
    header("Location: siswa.php");
    exit;
}