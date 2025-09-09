<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Set header untuk download Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Data_Siswa_" . date("Y-m-d") . ".xls"');
header('Cache-Control: max-age=0');

// Query untuk mengambil semua data siswa
$sql = "SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.nama_lengkap ASC";
$result = executeQuery($sql);

// Mulai output Excel
echo "<table border='1'>";
echo "<thead>";
echo "<tr>";
echo "<th>No</th>";
echo "<th>NIS</th>";
echo "<th>NISN</th>";
echo "<th>Nama Lengkap</th>";
echo "<th>Jenis Kelamin</th>";
echo "<th>Kelas</th>";
echo "<th>Tempat Lahir</th>";
echo "<th>Tanggal Lahir</th>";
echo "<th>Alamat</th>";
echo "<th>Telepon</th>";
echo "<th>Email</th>";
echo "<th>Status</th>";
echo "<th>Tanggal Dibuat</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

$no = 1;
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row['nis']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nisn']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_lengkap']) . "</td>";
        echo "<td>" . htmlspecialchars($row['jenis_kelamin']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nama_kelas'] ?? 'Belum ditentukan') . "</td>";
        echo "<td>" . htmlspecialchars($row['tempat_lahir']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
        echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
        echo "<td>" . htmlspecialchars($row['telepon'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
        echo "</tr>";
    }
}

echo "</tbody>";
echo "</table>";
?>