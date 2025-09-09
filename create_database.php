<?php
// Script untuk membuat database sman6_db
$servername = "localhost";
$username = "root";
$password = "";
$database = "sman6_db";

// Buat koneksi
$conn = new mysqli($servername, $username, $password);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Buat database
$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($sql) === TRUE) {
    echo "Database $database berhasil dibuat atau sudah ada<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Pilih database
if (!$conn->select_db($database)) {
    echo "Gagal memilih database: " . $conn->error . "<br>";
    $conn->close();
    exit;
}

// Baca file SQL
$sqlFile = __DIR__ . '/database.sql';
if (file_exists($sqlFile)) {
    $sql = file_get_contents($sqlFile);
    
    // Hapus CREATE DATABASE dan USE statements karena kita sudah terhubung
    $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
    $sql = preg_replace('/USE.*?;/i', '', $sql);
    
    // Pisahkan query berdasarkan titik koma
    $queries = explode(';', $sql);
    
    // Jalankan setiap query
    foreach ($queries as $query) {
        $query = trim($query);
        
        // Skip empty queries or comments
        if (empty($query) || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
            continue;
        }
        
        // Skip SQLite format indicator if present
        if (strpos($query, 'SQLite format') !== false) {
            continue;
        }
        
        if ($conn->query($query) === TRUE) {
            // Query berhasil
        } else {
            echo "Error executing query: " . $conn->error . "<br>";
            echo "Query: " . htmlspecialchars($query) . "<br><br>";
        }
    }
    
    echo "Struktur database berhasil diimpor dari database.sql<br>";
} else {
    echo "File database.sql tidak ditemukan<br>";
}

// Pastikan user admin ada dengan password yang benar
$hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
$checkUser = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($checkUser && $checkUser->num_rows > 0) {
    // Update user admin jika sudah ada
    $update = "UPDATE users SET password = '$hashedPassword' WHERE username = 'admin'";
    if ($conn->query($update)) {
        echo "Password admin berhasil diupdate<br>";
    } else {
        echo "Gagal mengupdate password admin: " . $conn->error . "<br>";
    }
} else {
    // Buat user admin jika belum ada
    // Perhatikan bahwa database.sql menggunakan 'role' bukan 'level'
    $insert = "INSERT IGNORE INTO users (username, password, nama_lengkap, email, role) 
               VALUES ('admin', '$hashedPassword', 'Administrator', 'admin@sman6-ska.sch.id', 'admin')";
    if ($conn->query($insert)) {
        echo "User admin berhasil dibuat<br>";
    } else {
        echo "Gagal membuat user admin: " . $conn->error . "<br>";
    }
}

$conn->close();
echo "Proses selesai. Anda dapat menutup halaman ini dan mencoba mengakses aplikasi.";
?>