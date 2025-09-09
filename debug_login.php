<?php
// Script untuk debugging login
require_once 'db_connect.php';

if ($conn) {
    echo "<h2>Debug Login Process</h2>";
    
    // Periksa semua user dalam database
    $result = $conn->query("SELECT * FROM users");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>Ditemukan " . $result->num_rows . " user(s) dalam database:</p>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Password Hash</th><th>Nama Lengkap</th><th>Role</th><th>Active</th></tr>";
        
        while ($user = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . $user['username'] . "</td>";
            echo "<td>" . substr($user['password'], 0, 20) . "...</td>";
            echo "<td>" . $user['nama_lengkap'] . "</td>";
            echo "<td>" . $user['role'] . "</td>";
            echo "<td>" . $user['is_active'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Uji verifikasi password untuk user admin
        $result = $conn->query("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<h3>Testing Password Verification for Admin User:</h3>";
            echo "<p>Stored hash: " . $user['password'] . "</p>";
            
            // Test with correct password
            if (password_verify('admin123', $user['password'])) {
                echo "<p style='color: green; font-weight: bold;'>✓ Password 'admin123' VERIFIED correctly!</p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>✗ Password 'admin123' FAILED verification!</p>";
                
                // Test with some common variations
                $variations = ['Admin123', 'Admin', 'admin', 'password', ''];
                foreach ($variations as $variation) {
                    if (password_verify($variation, $user['password'])) {
                        echo "<p style='color: orange;'>✓ Password '$variation' verified (variation)</p>";
                    } else {
                        echo "<p style='color: gray;'>✗ Password '$variation' failed verification</p>";
                    }
                }
            }
        }
    } else {
        echo "<p style='color: red;'>Tidak ada user dalam database!</p>";
    }
} else {
    echo "<h2 style='color: red;'>Koneksi database gagal!</h2>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}

echo "<br><a href='reset_admin_user.php'>Reset Admin User</a> | <a href='login.php'>Try Login</a> | <a href='index.php'>Home</a>";
?>