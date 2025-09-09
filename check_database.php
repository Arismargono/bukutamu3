<?php
/**
 * Check Database Connection
 * File ini digunakan untuk memeriksa koneksi database dan mengimpor struktur database jika diperlukan
 */

// Sertakan file koneksi database
require_once 'db_connect.php';

// Fungsi untuk menampilkan pesan status
function showStatus($message, $type = 'info') {
    $alertClass = 'alert-info';
    
    if ($type == 'success') {
        $alertClass = 'alert-success';
    } elseif ($type == 'error') {
        $alertClass = 'alert-danger';
    } elseif ($type == 'warning') {
        $alertClass = 'alert-warning';
    }
    
    echo "<div class='alert $alertClass'>$message</div>";
}

// Fungsi untuk memeriksa tabel dalam database
function checkTables() {
    global $conn;
    
    $requiredTables = ['siswa', 'guru', 'kelas', 'mata_pelajaran', 'jadwal_pelajaran', 'nilai', 'absensi', 'users'];
    $missingTables = [];
    
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        
        if ($result->num_rows == 0) {
            $missingTables[] = $table;
        }
    }
    
    return $missingTables;
}

// Fungsi untuk mengimpor struktur database
function importDatabase() {
    global $conn;
    
    $sqlFile = __DIR__ . '/database.sql';
    
    if (!file_exists($sqlFile)) {
        // Jika file tidak ada, gunakan fungsi createBasicDatabaseStructure
        if (function_exists('createBasicDatabaseStructure')) {
            $result = createBasicDatabaseStructure();
            if ($result) {
                return [true, "Struktur database dasar berhasil dibuat"];
            } else {
                return [false, "Gagal membuat struktur database dasar"];
            }
        } else {
            return [false, "File SQL tidak ditemukan dan fungsi createBasicDatabaseStructure tidak tersedia"];
        }
    }
    
    try {
        // Baca file SQL
        $sql = file_get_contents($sqlFile);
        
        if (!$sql) {
            return [false, "Gagal membaca file SQL"];
        }
        
        // Pisahkan query berdasarkan titik koma
        $queries = explode(';', $sql);
        
        // Jalankan setiap query
        foreach ($queries as $query) {
            $query = trim($query);
            
            if (!empty($query)) {
                if (!$conn->query($query)) {
                    return [false, "Gagal menjalankan query: " . $conn->error];
                }
            }
        }
        
        return [true, "Struktur database berhasil diimpor"];
        
    } catch (Exception $e) {
        return [false, "Error: " . $e->getMessage()];
    }
}

// Catatan: Fungsi createBasicDatabaseStructure() sudah dideklarasikan di db_connect.php

// Mulai output HTML
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Koneksi Database - SMA Negeri 6 Surakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0d6efd;
            margin-bottom: 20px;
        }
        .status-box {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .btn-action {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Cek Koneksi Database</h1>
        <h4>Sistem Informasi SMA Negeri 6 Surakarta</h4>
        
        <div class="status-box">
            <h5>Status Koneksi Database:</h5>
            <?php
            // Cek koneksi database
            if ($conn) {
                showStatus("Koneksi ke database berhasil!", 'success');
                
                // Cek tabel yang diperlukan
                $missingTables = checkTables();
                
                if (empty($missingTables)) {
                    showStatus("Semua tabel database telah tersedia.", 'success');
                } else {
                    showStatus("Beberapa tabel tidak ditemukan: " . implode(", ", $missingTables), 'warning');
                    
                    // Jika ada form submission untuk mengimpor database
                    if (isset($_POST['import_database'])) {
                        list($success, $message) = importDatabase();
                        
                        if ($success) {
                            showStatus($message, 'success');
                            // Cek lagi tabel setelah impor
                            $missingTables = checkTables();
                            
                            if (empty($missingTables)) {
                                showStatus("Semua tabel database telah tersedia setelah impor.", 'success');
                            } else {
                                showStatus("Masih ada tabel yang tidak ditemukan setelah impor: " . implode(", ", $missingTables), 'error');
                            }
                        } else {
                            showStatus($message, 'error');
                        }
                    } else {
                        // Tampilkan form untuk mengimpor database
                        echo '<form method="post" action="">';
                        echo '<input type="hidden" name="import_database" value="1">';
                        echo '<button type="submit" class="btn btn-primary btn-action">Impor Struktur Database</button>';
                        echo '</form>';
                    }
                }
            } else {
                showStatus("Koneksi ke database gagal!", 'error');
            }
            ?>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Kembali ke Beranda</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>