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
$kategori_filter = isset($_GET['kategori']) ? sanitize_input($_GET['kategori']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Query untuk mengambil data mata pelajaran dengan filter
$sql = "SELECT * FROM mata_pelajaran WHERE 1=1";
$params = [];

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql .= " AND (nama_mata_pelajaran LIKE ? OR kode_mata_pelajaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tambahkan filter kategori jika ada
if (!empty($kategori_filter)) {
    $sql .= " AND kategori = ?";
    $params[] = $kategori_filter;
}

// Tambahkan filter status jika ada
if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

// Tambahkan pengurutan
$sql .= " ORDER BY nama_mata_pelajaran ASC";

// Eksekusi query
$result = executeQuery($sql, $params);

// Konversi hasil query ke array
$mata_pelajaran_list = [];
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $mata_pelajaran_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Mata Pelajaran - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
                <a href="mata_pelajaran.php" class="nav-link active">
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
                <h4 class="mb-0">Manajemen Mata Pelajaran</h4>
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
                <form method="GET" action="mata_pelajaran.php">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Cari nama atau kode mata pelajaran..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori">
                                <option value="">Semua Kategori</option>
                                <option value="Wajib" <?php echo ($kategori_filter == 'Wajib') ? 'selected' : ''; ?>>Wajib</option>
                                <option value="Peminatan" <?php echo ($kategori_filter == 'Peminatan') ? 'selected' : ''; ?>>Peminatan</option>
                                <option value="Lintas Minat" <?php echo ($kategori_filter == 'Lintas Minat') ? 'selected' : ''; ?>>Lintas Minat</option>
                                <option value="Muatan Lokal" <?php echo ($kategori_filter == 'Muatan Lokal') ? 'selected' : ''; ?>>Muatan Lokal</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua Status</option>
                                <option value="Aktif" <?php echo ($status_filter == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                                <option value="Tidak Aktif" <?php echo ($status_filter == 'Tidak Aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="mata_pelajaran.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMataPelajaranModal">
                                <i class="bi bi-plus"></i> Tambah Mata Pelajaran
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Mata Pelajaran -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Mata Pelajaran
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Mata Pelajaran</th>
                                <th>Kategori</th>
                                <th>SKS</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($mata_pelajaran_list) > 0): ?>
                                <?php $no = 1; foreach ($mata_pelajaran_list as $mapel): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($mapel['kode_mata_pelajaran']); ?></td>
                                    <td><?php echo htmlspecialchars($mapel['nama_mata_pelajaran']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($mapel['kategori']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($mapel['sks']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($mapel['status'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($mapel['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editMataPelajaran(<?php echo $mapel['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteMataPelajaran(<?php echo $mapel['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data mata pelajaran ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Mata Pelajaran -->
    <div class="modal fade" id="addMataPelajaranModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="mata_pelajaran_process.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kode_mata_pelajaran" class="form-label">Kode Mata Pelajaran</label>
                                <input type="text" class="form-control" id="kode_mata_pelajaran" name="kode_mata_pelajaran" 
                                       placeholder="Contoh: MTK001" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nama_mata_pelajaran" class="form-label">Nama Mata Pelajaran</label>
                                <input type="text" class="form-control" id="nama_mata_pelajaran" name="nama_mata_pelajaran" 
                                       placeholder="Contoh: Matematika" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kategori" class="form-label">Kategori</label>
                                <select class="form-select" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <option value="Wajib">Wajib</option>
                                    <option value="Peminatan">Peminatan</option>
                                    <option value="Lintas Minat">Lintas Minat</option>
                                    <option value="Muatan Lokal">Muatan Lokal</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="sks" class="form-label">SKS</label>
                                <input type="number" class="form-control" id="sks" name="sks" min="1" max="10" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                          placeholder="Deskripsi mata pelajaran (opsional)"></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Pilih Status</option>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
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

    <!-- Modal Edit Mata Pelajaran -->
    <div class="modal fade" id="editMataPelajaranModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mata Pelajaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="mata_pelajaran_process.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="mata_pelajaran_id" id="edit_mata_pelajaran_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_kode_mata_pelajaran" class="form-label">Kode Mata Pelajaran</label>
                                <input type="text" class="form-control" id="edit_kode_mata_pelajaran" name="kode_mata_pelajaran" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nama_mata_pelajaran" class="form-label">Nama Mata Pelajaran</label>
                                <input type="text" class="form-control" id="edit_nama_mata_pelajaran" name="nama_mata_pelajaran" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_guru_pengampu_id" class="form-label">Guru Pengampu</label>
                                <select class="form-select" id="edit_guru_pengampu_id" name="guru_pengampu_id">
                                    <option value="">Pilih Guru Pengampu</option>
                                    <?php
                                    $guru_result = executeQuery("SELECT id, nama_lengkap FROM guru WHERE status = 'Aktif' ORDER BY nama_lengkap");
                                    if ($guru_result) {
                                        while ($guru = $guru_result->fetchArray(SQLITE3_ASSOC)) {
                                            echo "<option value='{$guru['id']}'>{$guru['nama_lengkap']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_kelas_id" class="form-label">Kelas</label>
                                <select class="form-select" id="edit_kelas_id" name="kelas_id">
                                    <option value="">Pilih Kelas</option>
                                    <?php
                                    $kelas_result = executeQuery("SELECT id, nama_kelas FROM kelas WHERE status = 'Aktif' ORDER BY nama_kelas");
                                    if ($kelas_result) {
                                        while ($kelas = $kelas_result->fetchArray(SQLITE3_ASSOC)) {
                                            echo "<option value='{$kelas['id']}'>{$kelas['nama_kelas']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="edit_semester" class="form-label">Semester</label>
                                <select class="form-select" id="edit_semester" name="semester" required>
                                    <option value="">Pilih Semester</option>
                                    <option value="1">Semester 1</option>
                                    <option value="2">Semester 2</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" id="edit_tahun_ajaran" name="tahun_ajaran" placeholder="2023/2024" required>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_kkm" class="form-label">KKM</label>
                                <input type="number" class="form-control" id="edit_kkm" name="kkm" min="0" max="100" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" 
                                          placeholder="Deskripsi mata pelajaran (opsional)"></textarea>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Tidak Aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Mata Pelajaran -->
    <div class="modal fade" id="deleteMataPelajaranModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="mata_pelajaran_process.php">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="mata_pelajaran_id" id="delete_mata_pelajaran_id">
                        
                        <p>Apakah Anda yakin ingin menghapus mata pelajaran berikut?</p>
                        <div id="delete_mata_pelajaran_info" class="alert alert-warning"></div>
                        <p class="text-danger"><small><i class="bi bi-exclamation-triangle"></i> Tindakan ini tidak dapat dibatalkan!</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editMataPelajaran(id) {
            // Ambil data mata pelajaran via AJAX
            fetch('get_mata_pelajaran.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi form edit dengan data mata pelajaran
                        document.getElementById('edit_mata_pelajaran_id').value = data.data.id;
                        document.getElementById('edit_kode_mata_pelajaran').value = data.data.kode_mata_pelajaran;
                        document.getElementById('edit_nama_mata_pelajaran').value = data.data.nama_mata_pelajaran;
                        document.getElementById('edit_guru_pengampu_id').value = data.data.guru_pengampu_id || '';
                        document.getElementById('edit_kelas_id').value = data.data.kelas_id || '';
                        document.getElementById('edit_semester').value = data.data.semester;
                        document.getElementById('edit_tahun_ajaran').value = data.data.tahun_ajaran;
                        document.getElementById('edit_kkm').value = data.data.kkm;
                        document.getElementById('edit_deskripsi').value = data.data.deskripsi || '';
                        document.getElementById('edit_status').value = data.data.status;
                        
                        // Tampilkan modal edit
                        var editModal = new bootstrap.Modal(document.getElementById('editMataPelajaranModal'));
                        editModal.show();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data mata pelajaran');
                });
        }
        
        function deleteMataPelajaran(id) {
            // Ambil data mata pelajaran untuk konfirmasi
            fetch('get_mata_pelajaran.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Isi data untuk konfirmasi hapus
                        const deleteIdElement = document.getElementById('delete_mata_pelajaran_id');
                        const deleteInfoElement = document.getElementById('delete_mata_pelajaran_info');
                        
                        if (deleteIdElement) {
                            deleteIdElement.value = data.data.id;
                        }
                        
                        if (deleteInfoElement) {
                            deleteInfoElement.innerHTML = 
                                '<strong>Kode:</strong> ' + data.data.kode_mata_pelajaran + '<br>' +
                                '<strong>Nama:</strong> ' + data.data.nama_mata_pelajaran + '<br>' +
                                '<strong>Semester:</strong> ' + data.data.semester + '<br>' +
                                '<strong>Tahun Ajaran:</strong> ' + data.data.tahun_ajaran;
                        }
                        
                        // Tampilkan modal konfirmasi hapus
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteMataPelajaranModal'));
                        deleteModal.show();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data mata pelajaran');
                });
        }
    </script>
</body>
</html>