<?php
// Script untuk memeriksa user admin
require_once 'db_connect.php';

if ($conn) {
    echo "<h2>Memeriksa User Admin</h2>";
    
    // Periksa apakah tabel users ada
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>Tabel 'users' ditemukan</p>";
        
        // Periksa user admin
        $result = $conn->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p style='color: green;'>User admin ditemukan:</p>";
            echo "<ul>";
            echo "<li>ID: " . $user['id'] . "</li>";
            echo "<li>Username: " . $user['username'] . "</li>";
            echo "<li>Nama Lengkap: " . $user['nama_lengkap'] . "</li>";
            echo "<li>Level: " . $user['level'] . "</li>";
            echo "<li>Status: " . $user['status'] . "</li>";
            echo "</ul>";
            
            // Periksa password
            $hashedPassword = $user['password'];
            if (password_verify('admin123', $hashedPassword)) {
                echo "<p style='color: green; font-weight: bold;'>Password benar!</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>Password salah!</p>";
                echo "<p>Hash di database: " . $hashedPassword . "</p>";
            }
        } else {
            echo "<p style='color: red;'>User admin tidak ditemukan</p>";
            
            // Coba cari user lain
            $result = $conn->query("SELECT * FROM users");
            if ($result && $result->num_rows > 0) {
                echo "<p>User lain yang ditemukan:</p>";
                echo "<ul>";
                while ($user = $result->fetch_assoc()) {
                    echo "<li>" . $user['username'] . " (" . $user['nama_lengkap'] . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Tidak ada user dalam database</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>Tabel 'users' tidak ditemukan</p>";
    }
} else {
    echo "<h2 style='color: red;'>Koneksi database gagal!</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

echo "<br><a href='index.php'>Kembali ke Beranda</a>";
?>