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
    header("Location: nilai.php");
    exit;
}

// Ambil aksi yang akan dilakukan
$action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';

// Proses berdasarkan aksi
switch ($action) {
    case 'add':
        addNilai();
        break;
    case 'edit':
        editNilai();
        break;
    case 'delete':
        deleteNilai();
        break;
    default:
        $_SESSION['error'] = 'Aksi tidak valid.';
        header("Location: nilai.php");
        exit;
}

// Fungsi untuk menambah data nilai
function addNilai() {
    // Ambil data dari form
    $siswa_id = (int)$_POST['siswa_id'];
    $mata_pelajaran_id = (int)$_POST['mata_pelajaran_id'];
    $semester = (int)$_POST['semester'];
    $tahun_ajaran = sanitize_input($_POST['tahun_ajaran']);
    $nilai_uh = (float)$_POST['nilai_uh'];
    $nilai_uts = (float)$_POST['nilai_uts'];
    $nilai_uas = (float)$_POST['nilai_uas'];
    
    // Validasi input
    if (empty($siswa_id) || empty($mata_pelajaran_id) || empty($semester) || empty($tahun_ajaran) || 
        $nilai_uh < 0 || $nilai_uts < 0 || $nilai_uas < 0) {
        $_SESSION['error'] = 'Semua field wajib diisi dengan benar.';
        header("Location: nilai.php");
        exit;
    }
    
    // Validasi semester
    if (!in_array($semester, [1, 2])) {
        $_SESSION['error'] = 'Semester tidak valid.';
        header("Location: nilai.php");
        exit;
    }
    
    // Validasi nilai (0-100)
    if ($nilai_uh > 100 || $nilai_uts > 100 || $nilai_uas > 100) {
        $_SESSION['error'] = 'Nilai tidak boleh lebih dari 100.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah siswa ada
    $sql_check_siswa = "SELECT id FROM siswa WHERE id = ?";
    $result_check_siswa = executeQuery($sql_check_siswa, [$siswa_id]);
    
    if (!$result_check_siswa || !$result_check_siswa->fetchArray()) {
        $_SESSION['error'] = 'Data siswa tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah mata pelajaran ada
    $sql_check_mapel = "SELECT id FROM mata_pelajaran WHERE id = ?";
    $result_check_mapel = executeQuery($sql_check_mapel, [$mata_pelajaran_id]);
    
    if (!$result_check_mapel || !$result_check_mapel->fetchArray()) {
        $_SESSION['error'] = 'Data mata pelajaran tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah nilai untuk siswa, mata pelajaran, semester, dan tahun ajaran yang sama sudah ada
    $sql_check = "SELECT id FROM nilai WHERE siswa_id = ? AND mata_pelajaran_id = ? AND semester = ? AND tahun_ajaran = ?";
    $result_check = executeQuery($sql_check, [$siswa_id, $mata_pelajaran_id, $semester, $tahun_ajaran]);
    
    if ($result_check && $result_check->fetchArray()) {
        $_SESSION['error'] = 'Nilai untuk siswa, mata pelajaran, semester, dan tahun ajaran yang sama sudah ada.';
        header("Location: nilai.php");
        exit;
    }
    
    // Insert data nilai baru
    $sql = "INSERT INTO nilai (siswa_id, mata_pelajaran_id, semester, tahun_ajaran, nilai_uh, nilai_uts, nilai_uas, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now', 'localtime'), datetime('now', 'localtime'))";
    
    $params = [$siswa_id, $mata_pelajaran_id, $semester, $tahun_ajaran, $nilai_uh, $nilai_uts, $nilai_uas];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data nilai berhasil ditambahkan.';
    } else {
        $_SESSION['error'] = 'Gagal menambahkan data nilai.';
    }
    
    header("Location: nilai.php");
    exit;
}

// Fungsi untuk mengedit data nilai
function editNilai() {
    // Ambil data dari form
    $id = (int)$_POST['id'];
    $siswa_id = (int)$_POST['siswa_id'];
    $mata_pelajaran_id = (int)$_POST['mata_pelajaran_id'];
    $semester = (int)$_POST['semester'];
    $tahun_ajaran = sanitize_input($_POST['tahun_ajaran']);
    $nilai_uh = (float)$_POST['nilai_uh'];
    $nilai_uts = (float)$_POST['nilai_uts'];
    $nilai_uas = (float)$_POST['nilai_uas'];
    
    // Validasi input
    if (empty($id) || empty($siswa_id) || empty($mata_pelajaran_id) || empty($semester) || empty($tahun_ajaran) || 
        $nilai_uh < 0 || $nilai_uts < 0 || $nilai_uas < 0) {
        $_SESSION['error'] = 'Semua field wajib diisi dengan benar.';
        header("Location: nilai.php");
        exit;
    }
    
    // Validasi semester
    if (!in_array($semester, [1, 2])) {
        $_SESSION['error'] = 'Semester tidak valid.';
        header("Location: nilai.php");
        exit;
    }
    
    // Validasi nilai (0-100)
    if ($nilai_uh > 100 || $nilai_uts > 100 || $nilai_uas > 100) {
        $_SESSION['error'] = 'Nilai tidak boleh lebih dari 100.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah nilai dengan ID tersebut ada
    $sql_check = "SELECT id FROM nilai WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data nilai tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah siswa ada
    $sql_check_siswa = "SELECT id FROM siswa WHERE id = ?";
    $result_check_siswa = executeQuery($sql_check_siswa, [$siswa_id]);
    
    if (!$result_check_siswa || !$result_check_siswa->fetchArray()) {
        $_SESSION['error'] = 'Data siswa tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah mata pelajaran ada
    $sql_check_mapel = "SELECT id FROM mata_pelajaran WHERE id = ?";
    $result_check_mapel = executeQuery($sql_check_mapel, [$mata_pelajaran_id]);
    
    if (!$result_check_mapel || !$result_check_mapel->fetchArray()) {
        $_SESSION['error'] = 'Data mata pelajaran tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah nilai untuk siswa, mata pelajaran, semester, dan tahun ajaran yang sama sudah ada (kecuali untuk ID yang sedang diedit)
    $sql_check_duplicate = "SELECT id FROM nilai WHERE siswa_id = ? AND mata_pelajaran_id = ? AND semester = ? AND tahun_ajaran = ? AND id != ?";
    $result_check_duplicate = executeQuery($sql_check_duplicate, [$siswa_id, $mata_pelajaran_id, $semester, $tahun_ajaran, $id]);
    
    if ($result_check_duplicate && $result_check_duplicate->fetchArray()) {
        $_SESSION['error'] = 'Nilai untuk siswa, mata pelajaran, semester, dan tahun ajaran yang sama sudah ada.';
        header("Location: nilai.php");
        exit;
    }
    
    // Update data nilai
    $sql = "UPDATE nilai SET siswa_id = ?, mata_pelajaran_id = ?, semester = ?, tahun_ajaran = ?, nilai_uh = ?, nilai_uts = ?, nilai_uas = ?, updated_at = datetime('now', 'localtime') WHERE id = ?";
    
    $params = [$siswa_id, $mata_pelajaran_id, $semester, $tahun_ajaran, $nilai_uh, $nilai_uts, $nilai_uas, $id];
    
    $result = executeQuery($sql, $params);
    
    if ($result) {
        $_SESSION['success'] = 'Data nilai berhasil diperbarui.';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui data nilai.';
    }
    
    header("Location: nilai.php");
    exit;
}

// Fungsi untuk menghapus data nilai
function deleteNilai() {
    // Ambil ID nilai yang akan dihapus
    $id = (int)$_POST['id'];
    
    // Validasi input
    if (empty($id)) {
        $_SESSION['error'] = 'ID nilai tidak valid.';
        header("Location: nilai.php");
        exit;
    }
    
    // Cek apakah nilai dengan ID tersebut ada
    $sql_check = "SELECT id FROM nilai WHERE id = ?";
    $result_check = executeQuery($sql_check, [$id]);
    
    if (!$result_check || !$result_check->fetchArray()) {
        $_SESSION['error'] = 'Data nilai tidak ditemukan.';
        header("Location: nilai.php");
        exit;
    }
    
    // Hapus data nilai
    $sql = "DELETE FROM nilai WHERE id = ?";
    $result = executeQuery($sql, [$id]);
    
    if ($result) {
        $_SESSION['success'] = 'Data nilai berhasil dihapus.';
    } else {
        $_SESSION['error'] = 'Gagal menghapus data nilai.';
    }
    
    header("Location: nilai.php");
    exit;
}
?>