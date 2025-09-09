<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Cek apakah ID kelas diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID kelas tidak diberikan']);
    exit;
}

$kelas_id = (int)$_GET['id'];

try {
    // Query untuk mengambil data kelas berdasarkan ID
    $sql = "SELECT k.*, g.nama_lengkap as wali_kelas_nama 
            FROM kelas k 
            LEFT JOIN guru g ON k.wali_kelas_id = g.id 
            WHERE k.id = ?";
    $result = executeQuery($sql, [$kelas_id]);
    
    if ($result) {
        $kelas = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($kelas) {
            echo json_encode([
                'success' => true,
                'data' => $kelas
            ]);
        } else {
            echo json_encode(['error' => 'Data kelas tidak ditemukan']);
        }
    } else {
        echo json_encode(['error' => 'Gagal mengambil data kelas']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>