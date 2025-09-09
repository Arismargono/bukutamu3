<?php
// Script untuk menambahkan kolom status_wali_kelas ke tabel guru
require_once 'db_connect.php';

try {
    // Cek apakah kolom status_wali_kelas sudah ada
    $check_column = "PRAGMA table_info(guru)";
    $result = $conn->query($check_column);
    
    $column_exists = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === 'status_wali_kelas') {
            $column_exists = true;
            break;
        }
    }
    
    if (!$column_exists) {
        // Tambahkan kolom status_wali_kelas
        $sql = "ALTER TABLE guru ADD COLUMN status_wali_kelas TEXT DEFAULT 'Tidak'";
        $conn->exec($sql);
        echo "Kolom status_wali_kelas berhasil ditambahkan ke tabel guru.\n";
    } else {
        echo "Kolom status_wali_kelas sudah ada di tabel guru.\n";
    }
    
    // Cek apakah kolom bidang_studi sudah ada
    $check_bidang_studi = "PRAGMA table_info(guru)";
    $result2 = $conn->query($check_bidang_studi);
    
    $bidang_studi_exists = false;
    while ($row = $result2->fetchArray(SQLITE3_ASSOC)) {
        if ($row['name'] === 'bidang_studi') {
            $bidang_studi_exists = true;
            break;
        }
    }
    
    if (!$bidang_studi_exists) {
        // Tambahkan kolom bidang_studi
        $sql2 = "ALTER TABLE guru ADD COLUMN bidang_studi VARCHAR(50) DEFAULT ''";
        $conn->exec($sql2);
        echo "Kolom bidang_studi berhasil ditambahkan ke tabel guru.\n";
    } else {
        echo "Kolom bidang_studi sudah ada di tabel guru.\n";
    }
    
    echo "Update tabel guru selesai.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>