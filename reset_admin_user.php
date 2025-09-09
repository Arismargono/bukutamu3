<?php
// Script untuk membuat atau mereset user admin
require_once 'db_connect.php';

if ($conn) {
    echo "<h2>Membuat/Mereset User Admin</h2>";
    
    // Periksa apakah tabel users ada, jika tidak buat
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$result || $result->num_rows == 0) {
        echo "<p>Membuat tabel users...</p>";
        $createTable = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama_lengkap VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            role ENUM('admin', 'guru', 'siswa') NOT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_login DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        if ($conn->query($createTable)) {
            echo "<p style='color: green;'>Tabel users berhasil dibuat</p>";
        } else {
            echo "<p style='color: red;'>Gagal membuat tabel users: " . $conn->error . "</p>";
        }
    }
    
    // Hash password
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Cek apakah user admin sudah ada
    $result = $conn->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        // Update user admin
        $update = "UPDATE users SET 
                   password = '$hashedPassword',
                   nama_lengkap = 'Administrator',
                   role = 'admin',
                   is_active = 1
                   WHERE username = 'admin'";
        
        if ($conn->query($update)) {
            echo "<p style='color: green; font-weight: bold;'>User admin berhasil direset!</p>";
            echo "<p>Username: admin</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p style='color: red;'>Gagal mereset user admin: " . $conn->error . "</p>";
        }
    } else {
        // Hapus semua user yang mungkin ada dengan username 'admin' (untuk membersihkan data)
        $conn->query("DELETE FROM users WHERE username = 'admin'");
        
        // Buat user admin baru
        $insert = "INSERT INTO users (username, password, nama_lengkap, email, role, is_active) 
                   VALUES ('admin', '$hashedPassword', 'Administrator', 'admin@sman6-ska.sch.id', 'admin', 1)";
        
        if ($conn->query($insert)) {
            echo "<p style='color: green; font-weight: bold;'>User admin berhasil dibuat!</p>";
            echo "<p>Username: admin</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p style='color: red;'>Gagal membuat user admin: " . $conn->error . "</p>";
            
            // Coba dengan INSERT IGNORE sebagai fallback
            $insertIgnore = "INSERT IGNORE INTO users (username, password, nama_lengkap, email, role, is_active) 
                             VALUES ('admin', '$hashedPassword', 'Administrator', 'admin@sman6-ska.sch.id', 'admin', 1)";
            
            if ($conn->query($insertIgnore)) {
                echo "<p style='color: green; font-weight: bold;'>User admin berhasil dibuat dengan INSERT IGNORE!</p>";
                echo "<p>Username: admin</p>";
                echo "<p>Password: admin123</p>";
            }
        }
    }
    
    // Tampilkan semua user untuk verifikasi
    echo "<h3>Daftar User Saat Ini:</h3>";
    $result = $conn->query("SELECT * FROM users");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Active</th></tr>";
        while ($user = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . $user['nama_lengkap'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['is_active'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada user dalam database</p>";
    }
} else {
    echo "<h2 style='color: red;'>Koneksi database gagal!</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

echo "<br><a href='login.php'>Coba Login</a> | <a href='debug_login.php'>Debug Login</a> | <a href='index.php'>Kembali ke Beranda</a>";
?>