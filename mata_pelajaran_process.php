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

// Verifikasi CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Token keamanan tidak valid.';
    header("Location: mata_pelajaran.php");
    exit;
}

// Ambil aksi yang akan dilakukan
$action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';

// Proses berdasarkan aksi
switch ($action) {
    case 'add':
        addMataPelajaran();
        break;
    case 'edit':
        editMataPelajaran();
        break;
    case 'delete':
        deleteMataPelajaran();
        break;
    default:
        $_SESSION['error'] = 'Aksi tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
}

// Fungsi untuk menambah data mata pelajaran
function addMataPelajaran() {
    // Ambil data dari form
    $kode_mata_pelajaran = sanitize_input($_POST['kode_mata_pelajaran']);
    $nama_mata_pelajaran = sanitize_input($_POST['nama_mata_pelajaran']);
    $kategori = sanitize_input($_POST['kategori']);
    $sks = (int)$_POST['sks'];
    $deskripsi = sanitize_input($_POST['deskripsi']);
    $status = sanitize_input($_POST['status']);
    
    // Validasi input
    if (empty($kode_mata_pelajaran) || empty($nama_mata_pelajaran) || empty($kategori) || empty($sks) || empty($status)) {
        $_SESSION['error'] = 'Semua field wajib diisi kecuali deskripsi.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi kategori
    if (!in_array($kategori, ['Wajib', 'Peminatan', 'Lintas Minat', 'Muatan Lokal'])) {
        $_SESSION['error'] = 'Kategori tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi status
    if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
        $_SESSION['error'] = 'Status tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi SKS
    if ($sks < 1 || $sks > 10) {
        $_SESSION['error'] = 'SKS harus antara 1-10.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah kode mata pelajaran sudah ada
    $sql_check = "SELECT id FROM mata_pelajaran WHERE kode_mata_pelajaran = ?";
    $result_check = executeQuery($sql_check, [$kode_mata_pelajaran]);
    
    if ($result_check && $result_check->fetchArray()) {
        $_SESSION['error'] = 'Kode mata pelajaran sudah ada.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah nama mata pelajaran sudah ada
    $sql_check_nama = "SELECT id FROM mata_pelajaran WHERE nama_mata_pelajaran = ?";
    $result_check_nama = executeQuery($sql_check_nama, [$nama_mata_pelajaran]);
    
    if ($result_check_nama && $result_check_nama->fetchArray()) {
        $_SESSION['error'] = 'Nama mata pelajaran sudah ada.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Insert data mata pelajaran baru
    $sql = "INSERT INTO mata_pelajaran (kode_mata_pelajaran, nama_mata_pelajaran, kategori, sks, deskripsi, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, datetime('now', 'localtime'), datetime('now', 'localtime'))";
    
    $params = [$kode_mata_pelajaran, $nama_mata_pelajaran, $kategori, $sks, $deskripsi, $status];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data mata pelajaran berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data mata pelajaran.';
    }
    
    header("Location: mata_pelajaran.php");
    exit;
}

// Fungsi untuk mengedit data mata pelajaran
function editMataPelajaran() {
    // Ambil data dari form
    $id = (int)$_POST['id'];
    $kode_mata_pelajaran = sanitize_input($_POST['kode_mata_pelajaran']);
    $nama_mata_pelajaran = sanitize_input($_POST['nama_mata_pelajaran']);
    $kategori = sanitize_input($_POST['kategori']);
    $sks = (int)$_POST['sks'];
    $deskripsi = sanitize_input($_POST['deskripsi']);
    $status = sanitize_input($_POST['status']);
    
    // Validasi input
    if (empty($id) || empty($kode_mata_pelajaran) || empty($nama_mata_pelajaran) || empty($kategori) || empty($sks) || empty($status)) {
        $_SESSION['error'] = 'Semua field wajib diisi kecuali deskripsi.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi kategori
    if (!in_array($kategori, ['Wajib', 'Peminatan', 'Lintas Minat', 'Muatan Lokal'])) {
        $_SESSION['error'] = 'Kategori tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi status
    if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
        $_SESSION['error'] = 'Status tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Validasi SKS
    if ($sks < 1 || $sks > 10) {
        $_SESSION['error'] = 'SKS harus antara 1-10.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah mata pelajaran dengan ID tersebut ada
    $sql_check = "SELECT id FROM mata_pelajaran WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data mata pelajaran tidak ditemukan.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah kode mata pelajaran sudah ada (kecuali untuk ID yang sedang diedit)
    $sql_check_kode = "SELECT id FROM mata_pelajaran WHERE kode_mata_pelajaran = ? AND id != ?";
    $result_check_kode = executeQuery($sql_check_kode, [$kode_mata_pelajaran, $id]);
    
    if ($result_check_kode && $result_check_kode->fetchArray()) {
        $_SESSION['error'] = 'Kode mata pelajaran sudah ada.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah nama mata pelajaran sudah ada (kecuali untuk ID yang sedang diedit)
    $sql_check_nama = "SELECT id FROM mata_pelajaran WHERE nama_mata_pelajaran = ? AND id != ?";
    $result_check_nama = executeQuery($sql_check_nama, [$nama_mata_pelajaran, $id]);
    
    if ($result_check_nama && $result_check_nama->fetchArray()) {
        $_SESSION['error'] = 'Nama mata pelajaran sudah ada.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Update data mata pelajaran
    $sql = "UPDATE mata_pelajaran SET kode_mata_pelajaran = ?, nama_mata_pelajaran = ?, kategori = ?, sks = ?, deskripsi = ?, status = ?, updated_at = datetime('now', 'localtime') WHERE id = ?";
    
    $params = [$kode_mata_pelajaran, $nama_mata_pelajaran, $kategori, $sks, $deskripsi, $status, $id];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data mata pelajaran berhasil diperbarui.';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui data mata pelajaran.';
    }
    
    header("Location: mata_pelajaran.php");
    exit;
}

// Fungsi untuk menghapus data mata pelajaran
function deleteMataPelajaran() {
    // Ambil ID mata pelajaran yang akan dihapus
    $id = (int)$_POST['id'];
    
    // Validasi input
    if (empty($id)) {
        $_SESSION['error'] = 'ID mata pelajaran tidak valid.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah mata pelajaran dengan ID tersebut ada
    $sql_check = "SELECT id FROM mata_pelajaran WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data mata pelajaran tidak ditemukan.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah ada jadwal pelajaran yang menggunakan mata pelajaran ini
    $sql_check_jadwal = "SELECT id FROM jadwal_pelajaran WHERE mata_pelajaran_id = ?";
    $result_check_jadwal = executeQuery($sql_check_jadwal, [$id]);
    
    if ($result_check_jadwal && $result_check_jadwal->fetchArray()) {
        $_SESSION['error'] = 'Tidak dapat menghapus mata pelajaran karena masih ada jadwal pelajaran yang menggunakan mata pelajaran ini.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Cek apakah ada nilai yang menggunakan mata pelajaran ini
    $sql_check_nilai = "SELECT id FROM nilai WHERE mata_pelajaran_id = ?";
    $result_check_nilai = executeQuery($sql_check_nilai, [$id]);
    
    if ($result_check_nilai && $result_check_nilai->fetchArray()) {
        $_SESSION['error'] = 'Tidak dapat menghapus mata pelajaran karena masih ada data nilai yang menggunakan mata pelajaran ini.';
        header("Location: mata_pelajaran.php");
        exit;
    }
    
    // Hapus data mata pelajaran
    $sql = "DELETE FROM mata_pelajaran WHERE id = ?";
    $result = executeQuery($sql, [$id]);
    
    if ($result) {
        $_SESSION['success'] = 'Data mata pelajaran berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus data mata pelajaran.';
    }
    
    header("Location: mata_pelajaran.php");
    exit;
}
?>