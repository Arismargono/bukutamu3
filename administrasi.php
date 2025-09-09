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

// Ambil statistik untuk dashboard administrasi
$stats = [];

// Total siswa
$sql_siswa = "SELECT COUNT(*) as total FROM siswa WHERE status = 'Aktif'";
$result_siswa = executeQuery($sql_siswa, []);
if ($result_siswa) {
    $row = $result_siswa->fetchArray(SQLITE3_ASSOC);
    $stats['total_siswa'] = $row['total'];
}

// Total guru
$sql_guru = "SELECT COUNT(*) as total FROM guru WHERE status = 'Aktif'";
$result_guru = executeQuery($sql_guru, []);
if ($result_guru) {
    $row = $result_guru->fetchArray(SQLITE3_ASSOC);
    $stats['total_guru'] = $row['total'];
}

// Total kelas
$sql_kelas = "SELECT COUNT(*) as total FROM kelas WHERE status = 'Aktif'";
$result_kelas = executeQuery($sql_kelas, []);
if ($result_kelas) {
    $row = $result_kelas->fetchArray(SQLITE3_ASSOC);
    $stats['total_kelas'] = $row['total'];
}

// Total mata pelajaran
$sql_mapel = "SELECT COUNT(*) as total FROM mata_pelajaran WHERE status = 'Aktif'";
$result_mapel = executeQuery($sql_mapel, []);
if ($result_mapel) {
    $row = $result_mapel->fetchArray(SQLITE3_ASSOC);
    $stats['total_mata_pelajaran'] = $row['total'];
}

// Siswa per kelas
$sql_siswa_kelas = "SELECT k.nama_kelas, COUNT(s.id) as jumlah_siswa 
                   FROM kelas k 
                   LEFT JOIN siswa s ON k.id = s.kelas_id AND s.status = 'Aktif' 
                   WHERE k.status = 'Aktif' 
                   GROUP BY k.id, k.nama_kelas 
                   ORDER BY k.tingkat ASC, k.nama_kelas ASC";
$result_siswa_kelas = executeQuery($sql_siswa_kelas, []);
$siswa_per_kelas = [];
if ($result_siswa_kelas) {
    while ($row = $result_siswa_kelas->fetchArray(SQLITE3_ASSOC)) {
        $siswa_per_kelas[] = $row;
    }
}

// Aktivitas terbaru (contoh: siswa yang baru ditambahkan)
$sql_aktivitas = "SELECT 'Siswa Baru' as jenis, s.nama_lengkap as nama, s.created_at as waktu, k.nama_kelas as detail
                  FROM siswa s 
                  JOIN kelas k ON s.kelas_id = k.id 
                  WHERE s.created_at >= datetime('now', '-7 days') 
                  UNION ALL
                  SELECT 'Guru Baru' as jenis, g.nama_lengkap as nama, g.created_at as waktu, g.mata_pelajaran as detail
                  FROM guru g 
                  WHERE g.created_at >= datetime('now', '-7 days') 
                  ORDER BY waktu DESC 
                  LIMIT 10";
$result_aktivitas = executeQuery($sql_aktivitas, []);
$aktivitas_terbaru = [];
if ($result_aktivitas) {
    while ($row = $result_aktivitas->fetchArray(SQLITE3_ASSOC)) {
        $aktivitas_terbaru[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrasi Sekolah - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
        .btn-primary {
            background-color: #3949ab;
            border-color: #3949ab;
        }
        .btn-primary:hover {
            background-color: #1a237e;
            border-color: #1a237e;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card.green {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stat-card.orange {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        .stat-card.red {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .admin-menu-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .admin-menu-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            color: #3949ab;
            text-decoration: none;
        }
        .admin-menu-item i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #3949ab;
        }
        .admin-menu-item h5 {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .admin-menu-item p {
            font-size: 0.9rem;
            color: #666;
            margin: 0;
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
            <img src="https://via.placeholder.com/80" alt="<?php echo htmlspecialchars($nama_lengkap); ?>">
            <h6 class="mb-0"><?php echo htmlspecialchars($nama_lengkap); ?></h6>
            <p class="small text-muted"><?php echo htmlspecialchars($username); ?></p>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
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
                <a href="administrasi.php" class="nav-link active">
                    <i class="bi bi-file-earmark-text"></i> Administrasi Sekolah
                </a>
            </li>
            <li class="nav-item">
                <a href="user_management.php" class="nav-link">
                    <i class="bi bi-people"></i> Manajemen User
                </a>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white mb-4">
            <div class="container-fluid">
                <h4 class="mb-0">Administrasi Sekolah</h4>
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($nama_lengkap); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Statistik Ringkas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_siswa'] ?? 0; ?></div>
                    <div class="stat-label">Total Siswa Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card green">
                    <div class="stat-number"><?php echo $stats['total_guru'] ?? 0; ?></div>
                    <div class="stat-label">Total Guru Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card orange">
                    <div class="stat-number"><?php echo $stats['total_kelas'] ?? 0; ?></div>
                    <div class="stat-label">Total Kelas Aktif</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card red">
                    <div class="stat-number"><?php echo $stats['total_mata_pelajaran'] ?? 0; ?></div>
                    <div class="stat-label">Total Mata Pelajaran</div>
                </div>
            </div>
        </div>

        <!-- Menu Administrasi -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-grid-3x3-gap"></i> Menu Administrasi
            </div>
            <div class="card-body">
                <div class="admin-menu">
                    <a href="siswa.php" class="admin-menu-item">
                        <i class="bi bi-people"></i>
                        <h5>Manajemen Siswa</h5>
                        <p>Kelola data siswa, pendaftaran, dan status siswa</p>
                    </a>
                    
                    <a href="guru.php" class="admin-menu-item">
                        <i class="bi bi-person-badge"></i>
                        <h5>Manajemen Guru</h5>
                        <p>Kelola data guru dan tenaga pendidik</p>
                    </a>
                    
                    <a href="kelas.php" class="admin-menu-item">
                        <i class="bi bi-building"></i>
                        <h5>Manajemen Kelas</h5>
                        <p>Kelola kelas, wali kelas, dan pembagian kelas</p>
                    </a>
                    
                    <a href="mata_pelajaran.php" class="admin-menu-item">
                        <i class="bi bi-book"></i>
                        <h5>Mata Pelajaran</h5>
                        <p>Kelola kurikulum dan mata pelajaran</p>
                    </a>
                    
                    <a href="nilai.php" class="admin-menu-item">
                        <i class="bi bi-card-checklist"></i>
                        <h5>Manajemen Nilai</h5>
                        <p>Input dan kelola nilai siswa</p>
                    </a>
                    
                    <a href="#" class="admin-menu-item" onclick="alert('Fitur laporan akan segera tersedia')">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                        <h5>Laporan</h5>
                        <p>Generate laporan akademik dan administrasi</p>
                    </a>
                    
                    <a href="#" class="admin-menu-item" onclick="alert('Fitur backup akan segera tersedia')">
                        <i class="bi bi-cloud-download"></i>
                        <h5>Backup Data</h5>
                        <p>Backup dan restore data sistem</p>
                    </a>
                    
                    <a href="#" class="admin-menu-item" onclick="alert('Fitur pengaturan akan segera tersedia')">
                        <i class="bi bi-gear"></i>
                        <h5>Pengaturan Sistem</h5>
                        <p>Konfigurasi sistem dan preferensi</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Data Siswa per Kelas -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-bar-chart"></i> Distribusi Siswa per Kelas
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($siswa_per_kelas) > 0): ?>
                                        <?php foreach ($siswa_per_kelas as $data): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($data['nama_kelas']); ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $data['jumlah_siswa']; ?> siswa</span>
                                            </td>
                                            <td>
                                                <?php if ($data['jumlah_siswa'] > 30): ?>
                                                    <span class="badge bg-warning">Penuh</span>
                                                <?php elseif ($data['jumlah_siswa'] > 20): ?>
                                                    <span class="badge bg-success">Normal</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Tersedia</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Tidak ada data kelas.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> Aktivitas Terbaru
                    </div>
                    <div class="card-body">
                        <?php if (count($aktivitas_terbaru) > 0): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($aktivitas_terbaru as $aktivitas): ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($aktivitas['jenis']); ?></h6>
                                        <small><?php echo date('d/m/Y', strtotime($aktivitas['waktu'])); ?></small>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($aktivitas['nama']); ?></p>
                                    <small class="text-muted"><?php echo htmlspecialchars($aktivitas['detail']); ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Tidak ada aktivitas terbaru.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>