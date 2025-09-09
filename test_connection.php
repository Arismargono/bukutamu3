<?php
// Test database connection
require_once 'db_connect.php';

if ($conn) {
    echo "<h2 style='color: green;'>Koneksi database berhasil!</h2>";
    
    // Test a simple query
    $result = $conn->query("SELECT VERSION() as version");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Versi MySQL: " . $row['version'] . "</p>";
    }
    
    // Check if users table exists
    try {
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>Tabel 'users' ditemukan</p>";
            
            // Check if admin user exists
            $result = $conn->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
            if ($result && $result->num_rows > 0) {
                echo "<p style='color: green;'>User admin ditemukan</p>";
            } else {
                echo "<p style='color: orange;'>User admin tidak ditemukan</p>";
            }
        } else {
            echo "<p style='color: red;'>Tabel 'users' tidak ditemukan</p>";
            echo "<p>Anda perlu menjalankan script pembuatan database terlebih dahulu.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error saat memeriksa tabel: " . $e->getMessage() . "</p>";
        echo "<p>Anda perlu menjalankan script pembuatan database terlebih dahulu.</p>";
    }
} else {
    echo "<h2 style='color: red;'>Koneksi database gagal!</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}
?>