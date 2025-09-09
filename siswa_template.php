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
header('Content-Disposition: attachment;filename="Template_Import_Siswa.xls"');
header('Cache-Control: max-age=0');

// Ambil data kelas untuk referensi
$sql_kelas = "SELECT id, nama_kelas FROM kelas WHERE status = 'Aktif' ORDER BY nama_kelas ASC";
$result_kelas = executeQuery($sql_kelas);
$kelas_list = [];
if ($result_kelas) {
    while ($row = $result_kelas->fetchArray(SQLITE3_ASSOC)) {
        $kelas_list[] = $row;
    }
}

// Mulai output Excel
echo "<table border='1'>";
echo "<thead>";
echo "<tr style='background-color: #4CAF50; color: white; font-weight: bold;'>";
echo "<th>NIS</th>";
echo "<th>NISN</th>";
echo "<th>Nama Lengkap</th>";
echo "<th>Jenis Kelamin</th>";
echo "<th>Kelas ID</th>";
echo "<th>Tempat Lahir</th>";
echo "<th>Tanggal Lahir</th>";
echo "<th>Alamat</th>";
echo "<th>Telepon</th>";
echo "<th>Email</th>";
echo "<th>Status</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

// Contoh data
echo "<tr>";
echo "<td>2024001</td>";
echo "<td>1234567890</td>";
echo "<td>Contoh Nama Siswa</td>";
echo "<td>Laki-laki</td>";
echo "<td>1</td>";
echo "<td>Jakarta</td>";
echo "<td>2008-01-15</td>";
echo "<td>Jl. Contoh No. 123</td>";
echo "<td>081234567890</td>";
echo "<td>contoh@email.com</td>";
echo "<td>Aktif</td>";
echo "</tr>";

echo "</tbody>";
echo "</table>";

// Tambahkan sheet kedua untuk referensi kelas
echo "<br><br>";
echo "<h3>Referensi Kelas ID:</h3>";
echo "<table border='1'>";
echo "<thead>";
echo "<tr style='background-color: #2196F3; color: white; font-weight: bold;'>";
echo "<th>Kelas ID</th>";
echo "<th>Nama Kelas</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

foreach ($kelas_list as $kelas) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($kelas['id']) . "</td>";
    echo "<td>" . htmlspecialchars($kelas['nama_kelas']) . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";

echo "<br>";
echo "<h3>Catatan:</h3>";
echo "<ul>";
echo "<li>Jenis Kelamin: Laki-laki atau Perempuan</li>";
echo "<li>Tanggal Lahir: Format YYYY-MM-DD (contoh: 2008-01-15)</li>";
echo "<li>Status: Aktif atau Tidak Aktif</li>";
echo "<li>Telepon dan Email bersifat opsional</li>";
echo "<li>Gunakan Kelas ID sesuai dengan referensi di atas</li>";
echo "</ul>";
?>