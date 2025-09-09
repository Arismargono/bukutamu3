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
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Query untuk mengambil data guru dengan filter
$sql = "SELECT * FROM guru WHERE 1=1";
$params = [];

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql .= " AND (nama_lengkap LIKE ? OR nip LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Tambahkan filter status jika ada
if (!empty($status_filter)) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

// Tambahkan pengurutan
$sql .= " ORDER BY nama_lengkap ASC";

// Eksekusi query
$result = executeQuery($sql, $params);

// Konversi hasil query ke array
$guru_list = [];
if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $guru_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Guru - Sistem Informasi SMA Negeri 6 Surakarta</title>
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
                <a href="guru.php" class="nav-link active">
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
                <h4 class="mb-0">Manajemen Guru</h4>
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
                <form method="GET" action="guru.php">
                    <div class="row">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Pencarian</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Cari nama atau NIP..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
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
                            <a href="guru.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset
                            </a>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addGuruModal">
                                <i class="bi bi-plus"></i> Tambah Guru
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data Guru -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-table"></i> Data Guru
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Jenis Kelamin</th>
                                <th>Bidang Studi</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Wali Kelas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($guru_list) > 0): ?>
                                <?php $no = 1; foreach ($guru_list as $guru): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['jenis_kelamin']); ?></td>
                                    <td><?php echo htmlspecialchars($guru['bidang_studi'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($guru['telepon'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($guru['email'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($guru['status'] == 'Aktif') ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo htmlspecialchars($guru['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($guru['status_wali_kelas'] == 'Ya') ? 'bg-primary' : 'bg-secondary'; ?>">
                                            <?php echo htmlspecialchars($guru['status_wali_kelas'] ?? 'Tidak'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editGuru(<?php echo $guru['id']; ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteGuru(<?php echo $guru['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">Tidak ada data guru ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Guru -->
    <div class="modal fade" id="addGuruModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="guru_process.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="nip" name="nip" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>
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
                                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="telepon" name="telepon">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="col-md-6">
                                <label for="bidang_studi" class="form-label">Bidang Studi</label>
                                <input type="text" class="form-control" id="bidang_studi" name="bidang_studi" placeholder="Contoh: Matematika, Bahasa Indonesia">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status_wali_kelas" class="form-label">Status Wali Kelas</label>
                                <select class="form-select" id="status_wali_kelas" name="status_wali_kelas" required>
                                    <option value="">Pilih Status Wali Kelas</option>
                                    <option value="Ya">Ya (Sebagai Wali Kelas)</option>
                                    <option value="Tidak">Tidak (Bukan Wali Kelas)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
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

    <!-- Modal Edit Guru -->
    <div class="modal fade" id="editGuruModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data Guru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="guru_process.php" method="POST" id="editGuruForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nip" class="form-label">NIP</label>
                                <input type="text" class="form-control" id="edit_nip" name="nip" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap" required>
                            </div>
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
                                <label for="edit_tempat_lahir" class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" id="edit_tempat_lahir" name="tempat_lahir" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                <input type="date" class="form-control" id="edit_tanggal_lahir" name="tanggal_lahir" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control" id="edit_telepon" name="telepon">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_bidang_studi" class="form-label">Bidang Studi</label>
                                <input type="text" class="form-control" id="edit_bidang_studi" name="bidang_studi" placeholder="Contoh: Matematika, Bahasa Indonesia">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Aktif">Aktif</option>
                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_status_wali_kelas" class="form-label">Status Wali Kelas</label>
                                <select class="form-select" id="edit_status_wali_kelas" name="status_wali_kelas" required>
                                    <option value="">Pilih Status Wali Kelas</option>
                                    <option value="Ya">Ya (Sebagai Wali Kelas)</option>
                                    <option value="Tidak">Tidak (Bukan Wali Kelas)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="edit_alamat" name="alamat" rows="3" required></textarea>
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

    <!-- Modal Delete Guru -->
    <div class="modal fade" id="deleteGuruModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="guru_process.php" method="POST">
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
        function editGuru(id) {
            // Set ID guru yang akan diedit
            document.getElementById('edit_id').value = id;
            
            // Fetch data guru dengan AJAX
            fetch(`get_guru.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    const guru = data.data;
                    
                    // Isi form dengan data guru
                    document.getElementById('edit_nip').value = guru.nip;
                    document.getElementById('edit_nama_lengkap').value = guru.nama_lengkap;
                    document.getElementById('edit_jenis_kelamin').value = guru.jenis_kelamin;
                    document.getElementById('edit_tempat_lahir').value = guru.tempat_lahir;
                    document.getElementById('edit_tanggal_lahir').value = guru.tanggal_lahir;
                    document.getElementById('edit_telepon').value = guru.telepon || '';
                    document.getElementById('edit_email').value = guru.email || '';
                    document.getElementById('edit_bidang_studi').value = guru.bidang_studi || '';
                    document.getElementById('edit_status').value = guru.status;
                    document.getElementById('edit_status_wali_kelas').value = guru.status_wali_kelas || 'Tidak';
                    document.getElementById('edit_alamat').value = guru.alamat;
                    
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('editGuruModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data guru.');
                });
        }
        
        function deleteGuru(id) {
            // Set ID guru yang akan dihapus
            document.getElementById('delete_id').value = id;
            
            // Fetch data guru untuk konfirmasi
            fetch(`get_guru.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    const guru = data.data;
                    const deleteMessage = document.getElementById('delete_message');
                    if (deleteMessage) {
                        deleteMessage.innerHTML =
                            `Apakah Anda yakin ingin menghapus guru <strong>${guru.nama_lengkap}</strong> (NIP: ${guru.nip})?`;
                    }
                    
                    // Tampilkan modal
                    new bootstrap.Modal(document.getElementById('deleteGuruModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengambil data guru.');
                });
        }
    </script>
</body>
</html>