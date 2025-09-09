<?php
/**
 * Konfigurasi Database
 * File ini berisi pengaturan koneksi database untuk Sistem Informasi SMA Negeri 6 Surakarta
 */

// Pengaturan koneksi database
$db_host = 'localhost'; // Host database
$db_user = 'root';      // Username database
$db_pass = '';          // Password database
$db_name = 'sman6_db';  // Nama database

// Membuat koneksi ke database
try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    // Memeriksa koneksi
    if ($conn->connect_error) {
        throw new Exception("Koneksi database gagal: " . $conn->connect_error);
    }
    
    // Set karakter encoding
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    // Tangani error koneksi
    error_log($e->getMessage());
    // Tampilkan pesan error yang aman untuk pengguna
    echo "<div style='color:red; padding:10px; border:1px solid red; margin:10px;'>
            Terjadi kesalahan pada sistem. Silakan hubungi administrator.
          </div>";
}

/**
 * Fungsi untuk membersihkan input dari potensi serangan SQL Injection
 * @param string $data Data yang akan dibersihkan
 * @return string Data yang sudah dibersihkan
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Fungsi untuk menghasilkan token CSRF untuk keamanan form
 * @return string Token CSRF
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Fungsi untuk memverifikasi token CSRF
 * @param string $token Token yang akan diverifikasi
 * @return bool True jika token valid, false jika tidak
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}
?>