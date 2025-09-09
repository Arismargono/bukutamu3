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

// Inisialisasi variabel pencarian dan filter
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$kelas_filter = isset($_GET['kelas']) ? sanitize_input($_GET['kelas']) : '';
$mata_pelajaran_filter = isset($_GET['mata_pelajaran']) ? sanitize_input($_GET['mata_pelajaran']) : '';
$semester_filter = isset($_GET['semester']) ? sanitize_input($_GET['semester']) : '';

// Query untuk mengambil data nilai dengan filter
$sql = "SELECT n.*, s.nama_lengkap as nama_siswa, s.nisn, k.nama_kelas, mp.nama_mata_pelajaran 
        FROM nilai n 
        JOIN siswa s ON n.siswa_id = s.id 
        JOIN kelas k ON s.kelas_id = k.id 
        JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id 
        WHERE 1=1";
$params = [];

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql .= " AND (s.nama_lengkap LIKE ? OR s.nisn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tambahkan filter kelas jika ada
if (!empty($kelas_filter)) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_filter;
}

// Tambahkan filter mata pelajaran jika ada
if (!empty($mata_pelajaran_filter)) {
    $sql .= " AND n.mata_pelajaran_id = ?";
    $params[] = $mata_pelajaran_filter;
}

// Tambahkan filter semester jika ada
if (!empty($semester_filter)) {
    $sql .= " AND n.semester = ?";
    $params[] = $semester_filter;
}

// Tambahkan pengurutan
$sql .= " ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC, mp.nama_mata_pelajaran ASC";

// Eksekusi query
$result = executeQuery($sql, $params);

// Konversi hasil query ke array
$nilai_list = [];
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $nilai_list[] = $row;
    }
}

// Ambil data kelas untuk dropdown filter
$sql_kelas = "SELECT * FROM kelas WHERE status = 'Aktif' ORDER BY tingkat ASC, nama_kelas ASC";
$result_kelas = executeQuery($sql_kelas, []);
$kelas_list = [];
if ($result_kelas) {
    while ($row = $result_kelas->fetchArray(SQLITE3_ASSOC)) {
        $kelas_list[] = $row;
    }
}

// Ambil data mata pelajaran untuk dropdown filter
$sql_mapel = "SELECT * FROM mata_pelajaran WHERE status = 'Aktif' ORDER BY nama_mata_pelajaran ASC";
$result_mapel = executeQuery($sql_mapel, []);
$mata_pelajaran_list = [];
if ($result_mapel) {
    while ($row = $result_mapel->fetchArray(SQLITE3_ASSOC)) {
        $mata_pelajaran_list[] = $row;
    }
}

// Ambil data siswa untuk dropdown tambah nilai
$sql_siswa = "SELECT s.*, k.nama_kelas FROM siswa s JOIN kelas k ON s.kelas_id = k.id WHERE s.status = 'Aktif' ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC";
$result_siswa = executeQuery($sql_siswa, []);
$siswa_list = [];
if ($result_siswa) {
    while ($row = $result_siswa->fetchArray(SQLITE3_ASSOC)) {
        $siswa_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Nilai - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .badge {
            font-size: 0.8em;
        }
        .nilai-badge {
            font-size: 1em;
            padding: 0.5em 0.8em;
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
                <a href="nilai.php" class="nav-link active">
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
                <h4 class="mb-0">Manajemen Nilai</h4>
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

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Filter dan Pencarian -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-funnel"></i> Filter dan Pencarian
            </div>
            <div class="card-body">
                <form method="GET" action="nilai.php">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Cari nama siswa atau NISN..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="kelas" class="form-label">Kelas</label>
                            <select class="form-select" id="kelas" name="kelas">
                                <option value="">Semua Kelas</option>
                                <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($kelas_filter == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                            <select class="form-select" id="mata_pelajaran" name="mata_pelajaran">
                                <option value="">Semua Mata Pelajaran</option>
                                <?php foreach ($mata_pelajaran_list as $mapel): ?>
                                    <option value="<?php echo $mapel['id']; ?>" <?php echo ($mata_pelajaran_filter == $mapel['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mapel['nama_mata_pelajaran']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="semester" class="form-label">Semester</label>
                            <select class="form-select" id="semester" name="semester">
                                <option value="">Semua Semester</option>
                                <option value="1" <?php echo ($semester_filter == '1') ? 'selected' : ''; ?>>Semester 1</option>
                                <option value="2" <?php echo ($semester_filter == '2') ? 'selected' : ''; ?>>Semester 2</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="nilai.php" class="btn btn-secondary me-2">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addNilaiModal">
                                <i class="bi bi-plus"></i> Tambah Nilai
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Nilai -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Nilai
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NISN</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Semester</th>
                                <th>UH</th>
                                <th>UTS</th>
                                <th>UAS</th>
                                <th>Nilai Akhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($nilai_list) > 0): ?>
                                <?php $no = 1; foreach ($nilai_list as $nilai): ?>
                                <?php 
                                    // Hitung nilai akhir (30% UH + 35% UTS + 35% UAS)
                                    $nilai_akhir = ($nilai['nilai_uh'] * 0.3) + ($nilai['nilai_uts'] * 0.35) + ($nilai['nilai_uas'] * 0.35);
                                    $nilai_akhir = round($nilai_akhir, 2);
                                    
                                    // Tentukan grade berdasarkan nilai akhir
                                    if ($nilai_akhir >= 90) {
                                        $grade = 'A';
                                        $badge_class = 'bg-success';
                                    } elseif ($nilai_akhir >= 80) {
                                        $grade = 'B';
                                        $badge_class = 'bg-primary';
                                    } elseif ($nilai_akhir >= 70) {
                                        $grade = 'C';
                                        $badge_class = 'bg-warning';
                                    } elseif ($nilai_akhir >= 60) {
                                        $grade = 'D';
                                        $badge_class = 'bg-danger';
                                    } else {
                                        $grade = 'E';
                                        $badge_class = 'bg-dark';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nisn']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nama_siswa']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nama_kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nama_mata_pelajaran']); ?></td>
                                    <td>Semester <?php echo htmlspecialchars($nilai['semester']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nilai_uh']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nilai_uts']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nilai_uas']); ?></td>
                                    <td>
                                        <span class="badge nilai-badge <?php echo $badge_class; ?>">
                                            <?php echo $nilai_akhir; ?> (<?php echo $grade; ?>)
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editNilai(<?php echo $nilai['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteNilai(<?php echo $nilai['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data nilai ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Nilai -->
    <div class="modal fade" id="addNilaiModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="nilai_process.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="siswa_id" class="form-label">Siswa</label>
                                <select class="form-select" id="siswa_id" name="siswa_id" required>
                                    <option value="">Pilih Siswa</option>
                                    <?php foreach ($siswa_list as $siswa): ?>
                                        <option value="<?php echo $siswa['id']; ?>">
                                            <?php echo htmlspecialchars($siswa['nama_lengkap'] . ' - ' . $siswa['nama_kelas'] . ' (' . $siswa['nisn'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mata_pelajaran_id" class="form-label">Mata Pelajaran</label>
                                <select class="form-select" id="mata_pelajaran_id" name="mata_pelajaran_id" required>
                                    <option value="">Pilih Mata Pelajaran</option>
                                    <?php foreach ($mata_pelajaran_list as $mapel): ?>
                                        <option value="<?php echo $mapel['id']; ?>">
                                            <?php echo htmlspecialchars($mapel['nama_mata_pelajaran']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="semester" class="form-label">Semester</label>
                                <select class="form-select" id="semester" name="semester" required>
                                    <option value="">Pilih Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" 
                                       placeholder="2024/2025" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="nilai_uh" class="form-label">Nilai UH (30%)</label>
                                <input type="number" class="form-control" id="nilai_uh" name="nilai_uh" 
                                       min="0" max="100" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nilai_uts" class="form-label">Nilai UTS (35%)</label>
                                <input type="number" class="form-control" id="nilai_uts" name="nilai_uts" 
                                       min="0" max="100" step="0.01" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nilai_uas" class="form-label">Nilai UAS (35%)</label>
                                <input type="number" class="form-control" id="nilai_uas" name="nilai_uas" 
                                       min="0" max="100" step="0.01" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editNilai(id) {
            // Implementasi edit nilai
            alert('Fitur edit nilai akan segera tersedia');
        }
        
        function deleteNilai(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data nilai ini?')) {
                // Implementasi delete nilai
                alert('Fitur hapus nilai akan segera tersedia');
            }
        }
    </script>
</body>
</html>