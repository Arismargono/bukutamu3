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
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Query untuk mengambil data siswa dengan filter
$sql = "SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE 1=1";
$params = [];

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql .= " AND (s.nama_lengkap LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Tambahkan filter kelas jika ada
if (!empty($kelas_filter)) {
    $sql .= " AND s.kelas_id = ?";
    $params[] = $kelas_filter;
}

// Tambahkan filter status jika ada
if (!empty($status_filter)) {
    $sql .= " AND s.status = ?";
    $params[] = $status_filter;
}

// Tambahkan pengurutan
$sql .= " ORDER BY s.nama_lengkap ASC";

// Eksekusi query
$result = executeQuery($sql, $params);
$siswa_list = [];
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $siswa_list[] = $row;
    }
}

// Ambil data kelas aktif untuk dropdown filter dan modal tambah
$sql_kelas = "SELECT id, nama_kelas FROM kelas WHERE status = 'Aktif' ORDER BY nama_kelas ASC";
$result_kelas = executeQuery($sql_kelas);
$kelas_list = [];
if ($result_kelas) {
    while ($row = $result_kelas->fetchArray(SQLITE3_ASSOC)) {
        $kelas_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siswa - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .search-container {
            max-width: 300px;
        }
        .filter-container {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
                <a href="siswa.php" class="nav-link active">
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

        <!-- Content -->
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Manajemen Siswa</h2>
                    <p class="text-muted">Kelola data siswa SMA Negeri 6 Surakarta</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group me-2" role="group">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importSiswaModal">
                            <i class="bi bi-upload me-2"></i>Import CSV
                        </button>
                        <a href="siswa_export.php" class="btn btn-info">
                            <i class="bi bi-download me-2"></i>Export Excel
                        </a>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSiswaModal">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Siswa
                    </button>
                </div>
            </div>

            <!-- Filter and Search -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="filter-container">
                        <form action="" method="get" class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group search-container">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control border-0 bg-light" name="search" placeholder="Cari siswa..." value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="kelas">
                                    <option value="">Semua Kelas</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo ($kelas_filter == $kelas['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Semua Status</option>
                                    <option value="Aktif" <?php echo ($status_filter == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="Tidak Aktif" <?php echo ($status_filter == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-table me-2"></i> Daftar Siswa
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if (!empty($siswa_list)) {
                                    $no = 1;
                                    foreach ($siswa_list as $row) {
                                ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nisn']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_kelas'] ?? 'Belum ditentukan'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($row['status'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info btn-action" data-bs-toggle="modal" data-bs-target="#viewSiswaModal" data-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning btn-action" data-bs-toggle="modal" data-bs-target="#editSiswaModal" data-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#deleteSiswaModal" data-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    }
                                } else {
                                ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data siswa</td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tampilkan pesan sukses atau error -->
    <?php if(isset($_SESSION['success'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div class="toast show bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Sukses</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
        <div class="toast show bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Siswa Modal -->
    <div class="modal fade" id="addSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Tambah Siswa Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addSiswaForm" action="siswa_process.php" method="post">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nis" class="form-label">NIS</label>
                                <input type="text" class="form-control" id="nis" name="nis" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nisn" class="form-label">NISN</label>
                                <input type="text" class="form-control" id="nisn" name="nisn" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="kelas_id" class="form-label">Kelas</label>
                                <select class="form-select" id="kelas_id" name="kelas_id" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>">
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="addSiswaForm" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Siswa Modal -->
    <div class="modal fade" id="viewSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Detail Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewSiswaContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Memuat data...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Siswa Modal -->
    <div class="modal fade" id="editSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">Edit Data Siswa</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editSiswaForm" action="siswa_process.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nis" class="form-label">NIS</label>
                                <input type="text" class="form-control" id="edit_nis" name="nis" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nisn" class="form-label">NISN</label>
                                <input type="text" class="form-control" id="edit_nisn" name="nisn" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap" required>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                <select class="form-select" id="edit_jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_kelas_id" class="form-label">Kelas</label>
                                <select class="form-select" id="edit_kelas_id" name="kelas_id" required>
                                    <option value="">Pilih Kelas</option>
                                    <?php foreach ($kelas_list as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>">
                                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="edit_tempat_lahir" name="tempat_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="edit_tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="edit_telepon" name="telepon">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="editSiswaForm" class="btn btn-warning">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Siswa Modal -->
    <div class="modal fade" id="deleteSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Loading...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="deleteSiswaForm" class="btn btn-danger">Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Siswa Modal -->
    <div class="modal fade" id="importSiswaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Import Data Siswa dari CSV</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importSiswaForm" action="siswa_import.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Pilih File CSV</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".csv" required>
                            <div class="form-text">Format yang didukung: .csv (maksimal 5MB)</div>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle me-2"></i>Format File CSV:</h6>
                            <p class="mb-2">File CSV harus memiliki kolom dengan urutan sebagai berikut:</p>
                            <ol class="mb-0">
                                <li>NIS</li>
                                <li>NISN</li>
                                <li>Nama Lengkap</li>
                                <li>Jenis Kelamin (Laki-laki/Perempuan)</li>
                                <li>Kelas ID</li>
                                <li>Tempat Lahir</li>
                                <li>Tanggal Lahir (YYYY-MM-DD)</li>
                                <li>Alamat</li>
                                <li>Telepon (opsional)</li>
                                <li>Email (opsional)</li>
                                <li>Status (Aktif/Tidak Aktif)</li>
                            </ol>
                        </div>
                        
                        <div class="mb-3">
                            <div class="btn-group" role="group">
                                <a href="template_siswa.csv" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-2"></i>Download Template CSV
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="importSiswaForm" class="btn btn-success">
                        <i class="bi bi-upload me-2"></i>Import Data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-hide toast after 5 seconds
        setTimeout(function() {
            const toasts = document.querySelectorAll('.toast.show');
            toasts.forEach(toast => {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.hide();
            });
        }, 5000);
        
        // Handle view siswa modal
         const viewSiswaModal = document.getElementById('viewSiswaModal');
         if (viewSiswaModal) {
             viewSiswaModal.addEventListener('show.bs.modal', function(event) {
                 const button = event.relatedTarget;
                 const siswaId = button.getAttribute('data-id');
                 const modalContent = document.getElementById('viewSiswaContent');
                 
                 // Pastikan elemen modalContent ada
                 if (!modalContent) {
                     console.error('Element viewSiswaContent not found');
                     return;
                 }
                 
                 // Tampilkan loading
                 modalContent.innerHTML = `
                     <div class="text-center">
                         <div class="spinner-border text-primary" role="status">
                             <span class="visually-hidden">Loading...</span>
                         </div>
                         <p>Memuat data...</p>
                     </div>
                 `;
                 
                 // Fetch data siswa dengan AJAX
                 fetch(`get_siswa.php?id=${siswaId}`)
                     .then(response => response.json())
                     .then(data => {
                         if (data.error) {
                             modalContent.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                             return;
                         }
                         
                         const siswa = data.data;
                         
                         // Tampilkan data siswa dalam format yang lebih terstruktur
                         modalContent.innerHTML = `
                             <div class="row">
                                 <div class="col-md-4 text-center mb-4">
                                     <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150' viewBox='0 0 150 150'%3E%3Ccircle cx='75' cy='75' r='75' fill='%23e9ecef'/%3E%3Cpath d='M75 37.5c10.3 0 18.75 8.45 18.75 18.75s-8.45 18.75-18.75 18.75-18.75-8.45-18.75-18.75S64.7 37.5 75 37.5zm0 46.875c15.6 0 28.125 6.375 28.125 14.063v14.062H46.875v-14.062c0-7.688 12.525-14.063 28.125-14.063z' fill='%236c757d'/%3E%3C/svg%3E" class="img-fluid rounded-circle mb-3" alt="${siswa.nama_lengkap}">
                                     <h5>${siswa.nama_lengkap}</h5>
                                     <span class="badge ${siswa.status === 'Aktif' ? 'bg-success' : 'bg-danger'}">${siswa.status}</span>
                                 </div>
                                 <div class="col-md-8">
                                     <table class="table table-striped">
                                         <tr>
                                             <th width="30%">NIS</th>
                                             <td width="5%">:</td>
                                             <td>${siswa.nis}</td>
                                         </tr>
                                         <tr>
                                             <th>NISN</th>
                                             <td>:</td>
                                             <td>${siswa.nisn}</td>
                                         </tr>
                                         <tr>
                                             <th>Kelas</th>
                                             <td>:</td>
                                             <td>${siswa.nama_kelas || 'Belum ditentukan'}</td>
                                         </tr>
                                         <tr>
                                             <th>Jenis Kelamin</th>
                                             <td>:</td>
                                             <td>${siswa.jenis_kelamin}</td>
                                         </tr>
                                         <tr>
                                             <th>Tempat, Tgl Lahir</th>
                                             <td>:</td>
                                             <td>${siswa.tempat_lahir}, ${siswa.tanggal_lahir_formatted}</td>
                                         </tr>
                                         <tr>
                                             <th>Alamat</th>
                                             <td>:</td>
                                             <td>${siswa.alamat}</td>
                                         </tr>
                                         <tr>
                                             <th>Telepon</th>
                                             <td>:</td>
                                             <td>${siswa.telepon || '-'}</td>
                                         </tr>
                                         <tr>
                                             <th>Email</th>
                                             <td>:</td>
                                             <td>${siswa.email || '-'}</td>
                                         </tr>
                                     </table>
                                 </div>
                             </div>
                         `;
                     })
                     .catch(error => {
                         console.error('Error:', error);
                         modalContent.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat mengambil data. Silakan coba lagi.</div>`;
                     });
             });
         }
        
        // Handle edit siswa modal
         const editSiswaModal = document.getElementById('editSiswaModal');
         if (editSiswaModal) {
             editSiswaModal.addEventListener('show.bs.modal', function(event) {
                 const button = event.relatedTarget;
                 const siswaId = button.getAttribute('data-id');
                 document.getElementById('edit_id').value = siswaId;
                 
                 // Tampilkan loading pada tombol submit
                 const submitBtn = document.querySelector('button[form="editSiswaForm"][type="submit"]');
                 if (!submitBtn) {
                     console.error('Edit submit button not found');
                     return;
                 }
                 const originalBtnText = submitBtn.innerHTML;
                 submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                 submitBtn.disabled = true;
                 
                 // Fetch data siswa dengan AJAX
                 fetch(`get_siswa.php?id=${siswaId}`)
                     .then(response => response.json())
                     .then(data => {
                         if (data.error) {
                             alert(data.error);
                             return;
                         }
                         
                         const siswa = data.data;
                         
                         // Isi form dengan data siswa
                         document.getElementById('edit_nis').value = siswa.nis;
                         document.getElementById('edit_nisn').value = siswa.nisn;
                         document.getElementById('edit_nama_lengkap').value = siswa.nama_lengkap;
                         document.getElementById('edit_jenis_kelamin').value = siswa.jenis_kelamin;
                         document.getElementById('edit_kelas_id').value = siswa.kelas_id;
                         document.getElementById('edit_tempat_lahir').value = siswa.tempat_lahir;
                         document.getElementById('edit_tanggal_lahir').value = siswa.tanggal_lahir;
                         document.getElementById('edit_alamat').value = siswa.alamat;
                         document.getElementById('edit_telepon').value = siswa.telepon || '';
                         document.getElementById('edit_email').value = siswa.email || '';
                         document.getElementById('edit_status').value = siswa.status;
                         
                         // Kembalikan tombol submit ke keadaan semula
                         if (submitBtn) {
                             submitBtn.innerHTML = originalBtnText;
                             submitBtn.disabled = false;
                         }
                     })
                     .catch(error => {
                         console.error('Error:', error);
                         alert('Terjadi kesalahan saat mengambil data. Silakan coba lagi.');
                         
                         // Kembalikan tombol submit ke keadaan semula
                         if (submitBtn) {
                             submitBtn.innerHTML = originalBtnText;
                             submitBtn.disabled = false;
                         }
                     });
             });
         }
        
        // Handle edit siswa form submission
        const editSiswaForm = document.getElementById('editSiswaForm');
        if (editSiswaForm) {
            editSiswaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = document.querySelector('button[form="editSiswaForm"][type="submit"]');
                if (!submitBtn) {
                    console.error('Edit submit button not found');
                    return;
                }
                
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                submitBtn.disabled = true;
                
                const formData = new FormData(this);
                
                fetch('siswa_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tutup modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editSiswaModal'));
                        modal.hide();
                        
                        // Reload halaman untuk menampilkan data terbaru
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data. Silakan coba lagi.');
                })
                .finally(() => {
                    // Kembalikan tombol submit ke keadaan semula
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                });
            });
        }
        
        // Handle delete siswa modal
         const deleteSiswaModal = document.getElementById('deleteSiswaModal');
         if (deleteSiswaModal) {
             deleteSiswaModal.addEventListener('show.bs.modal', function(event) {
                 const button = event.relatedTarget;
                 const siswaId = button.getAttribute('data-id');
                 
                 // Tampilkan loading pada modal body
                 const modalBody = deleteSiswaModal.querySelector('.modal-body');
                 if (!modalBody) {
                     console.error('Modal body not found');
                     return;
                 }
                 modalBody.innerHTML = '<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>';
                 
                 // Fetch data siswa dengan AJAX untuk konfirmasi
                 fetch(`get_siswa.php?id=${siswaId}`)
                     .then(response => response.json())
                     .then(data => {
                         if (data.error) {
                             modalBody.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                             return;
                         }
                         
                         const siswa = data.data;
                         modalBody.innerHTML = `
                             <form id="deleteSiswaForm" action="siswa_process.php" method="post">
                                 <input type="hidden" name="action" value="delete">
                                 <input type="hidden" name="id" value="${siswa.id}">
                                 <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                 
                                 <p>Apakah Anda yakin ingin menghapus data siswa <strong>${siswa.nama_lengkap}</strong> (NIS: ${siswa.nis})?</p>
                                 <p class="text-danger"><strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan.</p>
                             </form>
                         `;
                     })
                     .catch(error => {
                         console.error('Error:', error);
                         modalBody.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat mengambil data. Silakan coba lagi.</div>`;
                     });
             });
         }
        
        // Handle delete siswa form submission
        document.addEventListener('submit', function(e) {
            if (e.target && e.target.id === 'deleteSiswaForm') {
                e.preventDefault();
                
                const submitBtn = document.querySelector('#deleteSiswaModal button[type="submit"]');
                if (!submitBtn) {
                    console.error('Delete submit button not found');
                    return;
                }
                
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghapus...';
                submitBtn.disabled = true;
                
                const formData = new FormData(e.target);
                
                fetch('siswa_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tutup modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteSiswaModal'));
                        modal.hide();
                        
                        // Reload halaman untuk menampilkan data terbaru
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus data. Silakan coba lagi.');
                })
                .finally(() => {
                    // Kembalikan tombol submit ke keadaan semula
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                });
            }
        });
        
        // Handle add siswa form submission
        const addSiswaForm = document.getElementById('addSiswaForm');
        if (addSiswaForm) {
            addSiswaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = document.querySelector('button[form="addSiswaForm"][type="submit"]');
                if (!submitBtn) {
                    console.error('Submit button not found');
                    return;
                }
                const originalBtnText = submitBtn.innerHTML;
                
                // Tampilkan loading pada tombol submit
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
                submitBtn.disabled = true;
                
                // Kirim data dengan fetch
                const formData = new FormData(this);
                // Action sudah ada di form hidden input, tidak perlu ditambahkan lagi
                
                fetch('siswa_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Tutup modal dan reload halaman
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addSiswaModal'));
                        modal.hide();
                        location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat menyimpan data.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan data.');
                })
                .finally(() => {
                    // Kembalikan tombol submit ke keadaan semula
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnText;
                        submitBtn.disabled = false;
                    }
                });
            });
        }
    });
    </script>
</body>
</html>