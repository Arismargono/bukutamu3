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
$tingkat_filter = isset($_GET['tingkat']) ? sanitize_input($_GET['tingkat']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Query untuk mengambil data kelas dengan filter
$sql = "SELECT k.*, g.nama_lengkap as wali_kelas_nama FROM kelas k 
        LEFT JOIN guru g ON k.wali_kelas_id = g.id WHERE 1=1";
$params = [];

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql .= " AND (k.nama_kelas LIKE ? OR k.tahun_ajaran LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tambahkan filter tingkat jika ada
if (!empty($tingkat_filter)) {
    $sql .= " AND k.tingkat = ?";
    $params[] = $tingkat_filter;
}

// Tambahkan filter status jika ada
if (!empty($status_filter)) {
    $sql .= " AND k.status = ?";
    $params[] = $status_filter;
}

// Tambahkan pengurutan
$sql .= " ORDER BY k.tingkat ASC, k.nama_kelas ASC";

// Eksekusi query
$result = executeQuery($sql, $params);

// Konversi hasil query ke array
$kelas_list = [];
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $kelas_list[] = $row;
    }
}

// Ambil data guru untuk dropdown wali kelas (hanya guru dengan status wali kelas = 'Ya')
$sql_guru = "SELECT * FROM guru WHERE status = 'Aktif' AND status_wali_kelas = 'Ya' ORDER BY nama_lengkap ASC";
$result_guru = executeQuery($sql_guru, []);
$guru_list = [];
if ($result_guru) {
    while ($row = $result_guru->fetchArray(SQLITE3_ASSOC)) {
        $guru_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kelas - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
                <a href="kelas.php" class="nav-link active">
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
                <h4 class="mb-0">Manajemen Kelas</h4>
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
                <form method="GET" action="kelas.php">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Cari nama kelas atau tahun ajaran..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="tingkat" class="form-label">Tingkat</label>
                            <select class="form-select" id="tingkat" name="tingkat">
                                <option value="">Semua Tingkat</option>
                                <option value="X" <?php echo ($tingkat_filter == 'X') ? 'selected' : ''; ?>>X</option>
                                <option value="XI" <?php echo ($tingkat_filter == 'XI') ? 'selected' : ''; ?>>XI</option>
                                <option value="XII" <?php echo ($tingkat_filter == 'XII') ? 'selected' : ''; ?>>XII</option>
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
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="kelas.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addKelasModal">
                                <i class="bi bi-plus"></i> Tambah Kelas
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Kelas -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Kelas
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Wali Kelas</th>
                                <th>Tahun Ajaran</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($kelas_list) > 0): ?>
                                <?php $no = 1; foreach ($kelas_list as $kelas): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['tingkat']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['wali_kelas_nama'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['tahun_ajaran']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($kelas['status'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($kelas['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editKelas(<?php echo $kelas['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteKelas(<?php echo $kelas['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data kelas ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Kelas -->
    <div class="modal fade" id="addKelasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="kelas_process.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_kelas" class="form-label">Nama Kelas</label>
                                <input type="text" class="form-control" id="nama_kelas" name="nama_kelas" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tingkat" class="form-label">Tingkat</label>
                                <select class="form-select" id="tingkat" name="tingkat" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="X">X</option>
                                    <option value="XI">XI</option>
                                    <option value="XII">XII</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="wali_kelas_id" class="form-label">Wali Kelas</label>
                                <select class="form-select" id="wali_kelas_id" name="wali_kelas_id">
                                    <option value="">Pilih Wali Kelas</option>
                                    <?php foreach ($guru_list as $guru): ?>
                                        <option value="<?php echo $guru['id']; ?>"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran" 
                                       placeholder="2024/2025" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="kapasitas" class="form-label">Kapasitas</label>
                                <input type="number" class="form-control" id="kapasitas" name="kapasitas" min="1" max="50" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Keterangan tambahan (opsional)"></textarea>
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

    <!-- Modal Edit Kelas -->
    <div class="modal fade" id="editKelasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="kelas_process.php" method="POST" id="editKelasForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nama_kelas" class="form-label">Nama Kelas</label>
                                <input type="text" class="form-control" id="edit_nama_kelas" name="nama_kelas" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_tingkat" class="form-label">Tingkat</label>
                                <select class="form-select" id="edit_tingkat" name="tingkat" required>
                                    <option value="">Pilih Tingkat</option>
                                    <option value="X">Kelas X</option>
                                    <option value="XI">Kelas XI</option>
                                    <option value="XII">Kelas XII</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" id="edit_tahun_ajaran" name="tahun_ajaran" placeholder="2023/2024" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_wali_kelas_id" class="form-label">Wali Kelas</label>
                                <select class="form-select" id="edit_wali_kelas_id" name="wali_kelas_id">
                                    <option value="">Pilih Wali Kelas</option>
                                    <?php foreach ($guru_list as $guru): ?>
                                        <option value="<?php echo $guru['id']; ?>"><?php echo htmlspecialchars($guru['nama_lengkap']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_kapasitas" class="form-label">Kapasitas</label>
                                <input type="number" class="form-control" id="edit_kapasitas" name="kapasitas" min="1" max="50" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_keterangan" class="form-label">Keterangan</label>
                            <textarea class="form-control" id="edit_keterangan" name="keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Kelas -->
    <div class="modal fade" id="deleteKelasModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="kelas_process.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <p id="delete_message">Loading...</p>
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
        function editKelas(id) {
            // Set ID kelas yang akan diedit
            document.getElementById('edit_id').value = id;
            
            // Fetch data kelas dengan AJAX
            fetch(`get_kelas.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    const kelas = data.data;
                    
                    // Isi form dengan data kelas
                    document.getElementById('edit_nama_kelas').value = kelas.nama_kelas;
                    document.getElementById('edit_tingkat').value = kelas.tingkat;
                    document.getElementById('edit_tahun_ajaran').value = kelas.tahun_ajaran;
                    document.getElementById('edit_wali_kelas_id').value = kelas.wali_kelas_id || '';
                    document.getElementById('edit_kapasitas').value = kelas.kapasitas;
                    document.getElementById('edit_status').value = kelas.status;
                    document.getElementById('edit_keterangan').value = kelas.keterangan || '';
                    
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('editKelasModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data kelas.');
                });
        }
        
        function deleteKelas(id) {
            // Set ID kelas yang akan dihapus
            document.getElementById('delete_id').value = id;
            
            // Fetch data kelas untuk konfirmasi
            fetch(`get_kelas.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    const kelas = data.data;
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) {
                        deleteMessage.innerHTML = 
                            `Apakah Anda yakin ingin menghapus kelas <strong>${kelas.nama_kelas}</strong> (${kelas.tahun_ajaran})?`;
                    }
                    
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('deleteKelasModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data kelas.');
                });
        }
    </script>
</body>
</html>