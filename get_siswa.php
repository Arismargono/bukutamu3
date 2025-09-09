<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, kirim response error
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

// Ambil ID siswa
$id = sanitize_input($_GET['id']);

// Cek koneksi database
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Koneksi database gagal']);
    exit;
}

// Query untuk mengambil data siswa
$sql = "SELECT s.*, k.nama_kelas 
        FROM siswa s 
        LEFT JOIN kelas k ON s.kelas_id = k.id 
        WHERE s.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Gagal menyiapkan query']);
    exit;
}
$stmt->bindValue(1, $id, SQLITE3_INTEGER);
$result = $stmt->execute();

if ($result && ($siswa = $result->fetchArray(SQLITE3_ASSOC))) {
    // Data ditemukan
    
    // Format tanggal lahir untuk tampilan
    if (isset($siswa['tanggal_lahir'])) {
        $tanggal_lahir = new DateTime($siswa['tanggal_lahir']);
        $siswa['tanggal_lahir_formatted'] = $tanggal_lahir->format('d-m-Y');
    } else {
        $siswa['tanggal_lahir_formatted'] = '-';
    }
    
    // Kirim response sukses
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $siswa]);
} else {
    // Data tidak ditemukan
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Data siswa tidak ditemukan']);
}

// Tutup statement dan koneksi
$stmt->close();
$conn->close();