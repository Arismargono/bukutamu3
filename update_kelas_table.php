<?php
// Script untuk menambahkan kolom kapasitas dan keterangan ke tabel kelas
require_once 'db_connect.php';

try {
    // Cek apakah kolom kapasitas sudah ada
    $check_kapasitas = executeQuery("PRAGMA table_info(kelas)", []);
    $kapasitas_exists = false;
    $keterangan_exists = false;
    
    if ($check_kapasitas) {
        while ($column = $check_kapasitas->fetchArray(SQLITE3_ASSOC)) {
            if ($column['name'] === 'kapasitas') {
                $kapasitas_exists = true;
            }
            if ($column['name'] === 'keterangan') {
                $keterangan_exists = true;
            }
        }
    }
    
    // Tambahkan kolom kapasitas jika belum ada
    if (!$kapasitas_exists) {
        $result1 = executeQuery("ALTER TABLE kelas ADD COLUMN kapasitas INTEGER DEFAULT 30", []);
        if ($result1) {
            echo "Kolom kapasitas berhasil ditambahkan.\n";
        } else {
            echo "Gagal menambahkan kolom kapasitas.\n";
        }
    } else {
        echo "Kolom kapasitas sudah ada.\n";
    }
    
    // Tambahkan kolom keterangan jika belum ada
    if (!$keterangan_exists) {
        $result2 = executeQuery("ALTER TABLE kelas ADD COLUMN keterangan TEXT", []);
        if ($result2) {
            echo "Kolom keterangan berhasil ditambahkan.\n";
        } else {
            echo "Gagal menambahkan kolom keterangan.\n";
        }
    } else {
        echo "Kolom keterangan sudah ada.\n";
    }
    
    // Tambahkan kolom status jika belum ada
    $status_exists = false;
    $check_status = executeQuery("PRAGMA table_info(kelas)", []);
    if ($check_status) {
        while ($column = $check_status->fetchArray(SQLITE3_ASSOC)) {
            if ($column['name'] === 'status') {
                $status_exists = true;
                break;
            }
        }
    }
    
    if (!$status_exists) {
        $result3 = executeQuery("ALTER TABLE kelas ADD COLUMN status TEXT DEFAULT 'Aktif'", []);
        if ($result3) {
            echo "Kolom status berhasil ditambahkan.\n";
        } else {
            echo "Gagal menambahkan kolom status.\n";
        }
    } else {
        echo "Kolom status sudah ada.\n";
    }
    
    echo "Update tabel kelas selesai.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>