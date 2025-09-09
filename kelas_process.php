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
    header("Location: kelas.php");
    exit;
}

// Ambil aksi yang akan dilakukan
$action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';

// Proses berdasarkan aksi
switch ($action) {
    case 'add':
        addKelas();
        break;
    case 'edit':
        editKelas();
        break;
    case 'delete':
        deleteKelas();
        break;
    default:
        $_SESSION['error'] = 'Aksi tidak valid.';
        header("Location: kelas.php");
        exit;
}

// Fungsi untuk menambah data kelas
function addKelas() {
    // Ambil data dari form
    $nama_kelas = sanitize_input($_POST['nama_kelas']);
    $tingkat = sanitize_input($_POST['tingkat']);
    $wali_kelas_id = !empty($_POST['wali_kelas_id']) ? (int)$_POST['wali_kelas_id'] : null;
    $tahun_ajaran = sanitize_input($_POST['tahun_ajaran']);
    $kapasitas = (int)$_POST['kapasitas'];
    $status = sanitize_input($_POST['status']);
    $keterangan = sanitize_input($_POST['keterangan']);
    
    // Validasi input
    if (empty($nama_kelas) || empty($tingkat) || empty($tahun_ajaran) || empty($kapasitas) || empty($status)) {
        $_SESSION['error'] = 'Semua field wajib diisi kecuali wali kelas dan keterangan.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi kapasitas
    if ($kapasitas < 1 || $kapasitas > 50) {
        $_SESSION['error'] = 'Kapasitas harus antara 1-50 siswa.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi tingkat
    if (!in_array($tingkat, ['X', 'XI', 'XII'])) {
        $_SESSION['error'] = 'Tingkat tidak valid.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi status
    if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
        $_SESSION['error'] = 'Status tidak valid.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah nama kelas sudah ada untuk tingkat dan tahun ajaran yang sama
    $sql_check = "SELECT id FROM kelas WHERE nama_kelas = ? AND tingkat = ? AND tahun_ajaran = ?";
    $result_check = executeQuery($sql_check, [$nama_kelas, $tingkat, $tahun_ajaran]);
    
    if ($result_check && $result_check->fetchArray()) {
        $_SESSION['error'] = 'Nama kelas sudah ada untuk tingkat dan tahun ajaran yang sama.';
        header("Location: kelas.php");
        exit;
    }
    
    // Jika wali kelas dipilih, cek apakah guru tersebut sudah menjadi wali kelas lain yang aktif
    if ($wali_kelas_id) {
        $sql_check_wali = "SELECT id FROM kelas WHERE wali_kelas_id = ? AND status = 'Aktif' AND tahun_ajaran = ?";
        $result_check_wali = executeQuery($sql_check_wali, [$wali_kelas_id, $tahun_ajaran]);
        
        if ($result_check_wali && $result_check_wali->fetchArray()) {
            $_SESSION['error'] = 'Guru tersebut sudah menjadi wali kelas lain yang aktif pada tahun ajaran yang sama.';
            header("Location: kelas.php");
            exit;
        }
    }
    
    // Insert data kelas baru
    $sql = "INSERT INTO kelas (nama_kelas, tingkat, wali_kelas_id, tahun_ajaran, kapasitas, status, keterangan, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'), datetime('now', 'localtime'))";
    
    $params = [$nama_kelas, $tingkat, $wali_kelas_id, $tahun_ajaran, $kapasitas, $status, $keterangan];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data kelas berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data kelas.';
    }
    
    header("Location: kelas.php");
    exit;
}

// Fungsi untuk mengedit data kelas
function editKelas() {
    // Ambil data dari form
    $id = (int)$_POST['id'];
    $nama_kelas = sanitize_input($_POST['nama_kelas']);
    $tingkat = sanitize_input($_POST['tingkat']);
    $wali_kelas_id = !empty($_POST['wali_kelas_id']) ? (int)$_POST['wali_kelas_id'] : null;
    $tahun_ajaran = sanitize_input($_POST['tahun_ajaran']);
    $kapasitas = (int)$_POST['kapasitas'];
    $status = sanitize_input($_POST['status']);
    $keterangan = sanitize_input($_POST['keterangan']);
    
    // Validasi input
    if (empty($id) || empty($nama_kelas) || empty($tingkat) || empty($tahun_ajaran) || empty($kapasitas) || empty($status)) {
        $_SESSION['error'] = 'Semua field wajib diisi kecuali wali kelas dan keterangan.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi kapasitas
    if ($kapasitas < 1 || $kapasitas > 50) {
        $_SESSION['error'] = 'Kapasitas harus antara 1-50 siswa.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi tingkat
    if (!in_array($tingkat, ['X', 'XI', 'XII'])) {
        $_SESSION['error'] = 'Tingkat tidak valid.';
        header("Location: kelas.php");
        exit;
    }
    
    // Validasi status
    if (!in_array($status, ['Aktif', 'Tidak Aktif'])) {
        $_SESSION['error'] = 'Status tidak valid.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah kelas dengan ID tersebut ada
    $sql_check = "SELECT id FROM kelas WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data kelas tidak ditemukan.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah nama kelas sudah ada untuk tingkat dan tahun ajaran yang sama (kecuali untuk ID yang sedang diedit)
    $sql_check_duplicate = "SELECT id FROM kelas WHERE nama_kelas = ? AND tingkat = ? AND tahun_ajaran = ? AND id != ?";
    $result_check_duplicate = executeQuery($sql_check_duplicate, [$nama_kelas, $tingkat, $tahun_ajaran, $id]);
    
    if ($result_check_duplicate && $result_check_duplicate->fetchArray()) {
        $_SESSION['error'] = 'Nama kelas sudah ada untuk tingkat dan tahun ajaran yang sama.';
        header("Location: kelas.php");
        exit;
    }
    
    // Jika wali kelas dipilih, cek apakah guru tersebut sudah menjadi wali kelas lain yang aktif
    if ($wali_kelas_id) {
        $sql_check_wali = "SELECT id FROM kelas WHERE wali_kelas_id = ? AND status = 'Aktif' AND tahun_ajaran = ? AND id != ?";
        $result_check_wali = executeQuery($sql_check_wali, [$wali_kelas_id, $tahun_ajaran, $id]);
        
        if ($result_check_wali && $result_check_wali->fetchArray()) {
            $_SESSION['error'] = 'Guru tersebut sudah menjadi wali kelas lain yang aktif pada tahun ajaran yang sama.';
            header("Location: kelas.php");
            exit;
        }
    }
    
    // Update data kelas
    $sql = "UPDATE kelas SET nama_kelas = ?, tingkat = ?, wali_kelas_id = ?, tahun_ajaran = ?, kapasitas = ?, status = ?, keterangan = ?, updated_at = datetime('now', 'localtime') WHERE id = ?";
    
    $params = [$nama_kelas, $tingkat, $wali_kelas_id, $tahun_ajaran, $kapasitas, $status, $keterangan, $id];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data kelas berhasil diperbarui.';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui data kelas.';
    }
    
    header("Location: kelas.php");
    exit;
}

// Fungsi untuk menghapus data kelas
function deleteKelas() {
    // Ambil ID kelas yang akan dihapus
    $id = (int)$_POST['id'];
    
    // Validasi input
    if (empty($id)) {
        $_SESSION['error'] = 'ID kelas tidak valid.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah kelas dengan ID tersebut ada
    $sql_check = "SELECT id FROM kelas WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data kelas tidak ditemukan.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah ada siswa yang terdaftar di kelas ini
    $sql_check_siswa = "SELECT id FROM siswa WHERE kelas_id = ?";
    $result_check_siswa = executeQuery($sql_check_siswa, [$id]);
    
    if ($result_check_siswa && $result_check_siswa->fetchArray()) {
        $_SESSION['error'] = 'Tidak dapat menghapus kelas karena masih ada siswa yang terdaftar di kelas ini.';
        header("Location: kelas.php");
        exit;
    }
    
    // Cek apakah ada jadwal pelajaran yang menggunakan kelas ini
    $sql_check_jadwal = "SELECT id FROM jadwal_pelajaran WHERE kelas_id = ?";
    $result_check_jadwal = executeQuery($sql_check_jadwal, [$id]);
    
    if ($result_check_jadwal && $result_check_jadwal->fetchArray()) {
        $_SESSION['error'] = 'Tidak dapat menghapus kelas karena masih ada jadwal pelajaran yang menggunakan kelas ini.';
        header("Location: kelas.php");
        exit;
    }
    
    // Hapus data kelas
    $sql = "DELETE FROM kelas WHERE id = ?";
    $result = executeQuery($sql, [$id]);
    
    if ($result) {
        $_SESSION['success'] = 'Data kelas berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus data kelas.';
    }
    
    header("Location: kelas.php");
    exit;
}
?>