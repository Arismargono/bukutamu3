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

// Cek apakah ID guru diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'ID guru tidak diberikan']);
    exit;
}

$guru_id = (int)$_GET['id'];

try {
    // Query untuk mengambil data guru berdasarkan ID
    $sql = "SELECT * FROM guru WHERE id = ?";
    $result = executeQuery($sql, [$guru_id]);
    
    if ($result) {
        $guru = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($guru) {
            // Format tanggal lahir untuk input date
            if ($guru['tanggal_lahir']) {
                $guru['tanggal_lahir'] = date('Y-m-d', strtotime($guru['tanggal_lahir']));
            }
            
            echo json_encode([
                'success' => true,
                'data' => $guru
            ]);
        } else {
            echo json_encode(['error' => 'Data guru tidak ditemukan']);
        }
    } else {
        echo json_encode(['error' => 'Gagal mengambil data guru']);
    }
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>