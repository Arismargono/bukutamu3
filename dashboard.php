<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    // Jika belum login, redirect ke halaman login
    header("Location: login.php");
    exit;
}

// Ambil data user dari session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nama_lengkap = $_SESSION['nama_lengkap'];
$role = $_SESSION['role'];

// Ambil data statistik dari database
// Jumlah siswa
$sql_siswa = "SELECT COUNT(*) as total FROM siswa WHERE status = 'Aktif'";
$result_siswa = $conn->query($sql_siswa);
$jumlah_siswa = ($result_siswa && $result_siswa->num_rows > 0) ? $result_siswa->fetch_assoc()['total'] : 0;

// Jumlah guru
$sql_guru = "SELECT COUNT(*) as total FROM guru WHERE status = 'Aktif'";
$result_guru = $conn->query($sql_guru);
$jumlah_guru = ($result_guru && $result_guru->num_rows > 0) ? $result_guru->fetch_assoc()['total'] : 0;

// Jumlah kelas
$sql_kelas = "SELECT COUNT(*) as total FROM kelas";
$result_kelas = $conn->query($sql_kelas);
$jumlah_kelas = ($result_kelas && $result_kelas->num_rows > 0) ? $result_kelas->fetch_assoc()['total'] : 0;

// Jumlah mata pelajaran
$sql_mapel = "SELECT COUNT(*) as total FROM mata_pelajaran";
$result_mapel = $conn->query($sql_mapel);
$jumlah_mapel = ($result_mapel && $result_mapel->num_rows > 0) ? $result_mapel->fetch_assoc()['total'] : 0;

// Ambil aktivitas terbaru
$sql_aktivitas = "SELECT 'siswa' as tipe, nama_lengkap, created_at FROM siswa ORDER BY created_at DESC LIMIT 5";
$result_aktivitas = $conn->query($sql_aktivitas);
$aktivitas_terbaru = [];
if ($result_aktivitas && $result_aktivitas->num_rows > 0) {
    while ($row = $result_aktivitas->fetch_assoc()) {
        $aktivitas_terbaru[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Informasi SMA Negeri 6 Surakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #1a237e;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #3949ab;
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
        }
        .stats-card {
            border-left: 5px solid #3949ab;
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .stats-card i {
            font-size: 2rem;
            color: #3949ab;
        }
        .stats-card .number {
            font-size: 1.8rem;
            font-weight: bold;
        }
        .logo-text {
            font-weight: bold;
            color: white;
            text-align: center;
            margin-bottom: 30px;
            padding: 0 10px;
        }
        .user-info {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .user-info img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column flex-shrink-0 p-3" style="width: 250px;">
        <div class="logo-text">
            <h5>SMA NEGERI 6</h5>
            <p class="small mb-0">SURAKARTA</p>
        </div>
        
        <div class="user-info">
            <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Ccircle cx='40' cy='40' r='40' fill='%23e9ecef'/%3E%3Cpath d='M40 20c5.5 0 10 4.5 10 10s-4.5 10-10 10-10-4.5-10-10 4.5-10 10-10zm0 25c8.3 0 15 3.4 15 7.5v7.5H25v-7.5c0-4.1 6.7-7.5 15-7.5z' fill='%236c757d'/%3E%3C/svg%3E" alt="<?php echo htmlspecialchars($nama_lengkap); ?>">
            <h6 class="mb-0"><?php echo htmlspecialchars($nama_lengkap); ?></h6>
            <p class="small text-muted"><?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="siswa.php" class="nav-link">
                    <i class="bi bi-people"></i> Manajemen Siswa
                </a>
            </li>
            <li class="nav-item">
                <a href="guru.php" class="nav-link">
                    <i class="bi bi-person-badge"></i> Manajemen Guru
                </a>
            </li>
            <li class="nav-item">
                <a href="kelas.php" class="nav-link">
                    <i class="bi bi-building"></i> Manajemen Kelas
                </a>
            </li>
            <li class="nav-item">
                <a href="mata_pelajaran.php" class="nav-link">
                    <i class="bi bi-book"></i> Manajemen Mata Pelajaran
                </a>
            </li>
            <li class="nav-item">
                <a href="nilai.php" class="nav-link">
                    <i class="bi bi-card-checklist"></i> Manajemen Nilai
                </a>
            </li>
            <li class="nav-item">
                <a href="administrasi.php" class="nav-link">
                    <i class="bi bi-file-earmark-text"></i> Administrasi Sekolah
                </a>
            </li>
            <li class="nav-item">
                <a href="user_management.php" class="nav-link">
                    <i class="bi bi-people"></i> Manajemen User
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-gear"></i> Pengaturan
                </a>
            </li>
        </ul>
        
        <hr>
        <a href="logout.php" class="nav-link text-white">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light mb-4">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-bell"></i>
                                <span class="badge bg-danger rounded-pill">3</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Notifikasi 1</a></li>
                                <li><a class="dropdown-item" href="#">Notifikasi 2</a></li>
                                <li><a class="dropdown-item" href="#">Notifikasi 3</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nama_lengkap); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Profil</a></li>
                                <li><a class="dropdown-item" href="#">Pengaturan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2>Dashboard</h2>
                    <p class="text-muted">Selamat datang, <?php echo htmlspecialchars($nama_lengkap); ?>!</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Siswa</h6>
                                <div class="number"><?php echo $jumlah_siswa; ?></div>
                            </div>
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Guru</h6>
                                <div class="number"><?php echo $jumlah_guru; ?></div>
                            </div>
                            <i class="bi bi-person-badge"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Total Kelas</h6>
                                <div class="number"><?php echo $jumlah_kelas; ?></div>
                            </div>
                            <i class="bi bi-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted">Mata Pelajaran</h6>
                                <div class="number"><?php echo $jumlah_mapel; ?></div>
                            </div>
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Widgets -->
            <div class="row">
                <!-- Recent Activities -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-activity me-2"></i> Aktivitas Terbaru
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($aktivitas_terbaru)): ?>
                                <li class="list-group-item">Belum ada aktivitas terbaru.</li>
                                <?php else: ?>
                                    <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($aktivitas['nama_lengkap']); ?></strong>
                                                <p class="mb-0 text-muted small">Ditambahkan sebagai <?php echo htmlspecialchars($aktivitas['tipe']); ?></p>
                                            </div>
                                            <span class="badge bg-primary rounded-pill">
                                                <?php echo date('d M Y', strtotime($aktivitas['created_at'])); ?>
                                            </span>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-calendar-event me-2"></i> Kalender Akademik
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> Kalender akademik akan ditampilkan di sini.
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Ujian Tengah Semester</strong>
                                            <p class="mb-0 text-muted small">Semester Ganjil 2023/2024</p>
                                        </div>
                                        <span class="badge bg-warning rounded-pill">10-15 Okt 2023</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Libur Hari Raya</strong>
                                            <p class="mb-0 text-muted small">Libur Nasional</p>
                                        </div>
                                        <span class="badge bg-danger rounded-pill">25 Des 2023</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Ujian Akhir Semester</strong>
                                            <p class="mb-0 text-muted small">Semester Ganjil 2023/2024</p>
                                        </div>
                                        <span class="badge bg-warning rounded-pill">5-10 Des 2023</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Announcements -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-megaphone me-2"></i> Pengumuman
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle me-2"></i> Pengumpulan Nilai Semester Ganjil</h5>
                                <p>Batas waktu pengumpulan nilai semester ganjil adalah tanggal 15 Desember 2023. Mohon untuk semua guru mata pelajaran untuk segera menyelesaikan penilaian dan mengunggah nilai ke sistem.</p>
                            </div>
                            <div class="alert alert-info">
                                <h5><i class="bi bi-info-circle me-2"></i> Pembaruan Sistem</h5>
                                <p>Sistem Informasi SMA Negeri 6 Surakarta telah diperbarui dengan fitur-fitur baru. Silakan eksplorasi dan laporkan jika ada kendala.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>