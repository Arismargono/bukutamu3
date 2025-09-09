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

// Cek apakah ID mata pelajaran diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID mata pelajaran tidak diberikan']);
    exit;
}

$mata_pelajaran_id = (int)$_GET['id'];

try {
    // Query untuk mengambil data mata pelajaran berdasarkan ID
    $sql = "SELECT mp.*, g.nama_lengkap as guru_pengampu_nama, k.nama_kelas 
            FROM mata_pelajaran mp 
            LEFT JOIN guru g ON mp.guru_pengampu_id = g.id 
            LEFT JOIN kelas k ON mp.kelas_id = k.id 
            WHERE mp.id = ?";
    $result = executeQuery($sql, [$mata_pelajaran_id]);
    
    if ($result) {
        $mata_pelajaran = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($mata_pelajaran) {
            echo json_encode([
                'success' => true,
                'data' => $mata_pelajaran
            ]);
        } else {
            echo json_encode(['error' => 'Data mata pelajaran tidak ditemukan']);
        }
    } else {
        echo json_encode(['error' => 'Gagal mengambil data mata pelajaran']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>