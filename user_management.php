<?php
session_start();

// Periksa apakah user sudah login dan memiliki role admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Sertakan file koneksi database
require_once 'db_connect.php';

// Inisialisasi variabel pesan
$message = '';
$message_type = '';

// Handle form submission untuk menambah atau mengedit user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Tambah user baru
                $username = sanitize_input($_POST['username']);
                $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
                $email = sanitize_input($_POST['email']);
                $role = sanitize_input($_POST['role']);
                $password = $_POST['password'];
                
                // Validasi input
                if (empty($username) || empty($nama_lengkap) || empty($email) || empty($password)) {
                    $message = "Semua field harus diisi!";
                    $message_type = "danger";
                } else {
                    // Cek apakah username atau email sudah ada
                    $check_sql = "SELECT * FROM users WHERE username = ? OR email = ?";
                    $check_result = executeQuery($check_sql, [$username, $email]);
                    
                    if ($check_result && $check_result->num_rows > 0) {
                        $message = "Username atau email sudah digunakan!";
                        $message_type = "danger";
                    } else {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Simpan user baru
                        $insert_sql = "INSERT INTO users (username, password, nama_lengkap, email, role, is_active) VALUES (?, ?, ?, ?, ?, 1)";
                        $insert_result = executeQuery($insert_sql, [$username, $hashed_password, $nama_lengkap, $email, $role]);
                        
                        if ($insert_result) {
                            $message = "User berhasil ditambahkan!";
                            $message_type = "success";
                        } else {
                            $message = "Gagal menambahkan user!";
                            $message_type = "danger";
                        }
                    }
                }
                break;
                
            case 'edit':
                // Edit user
                $user_id = (int)$_POST['user_id'];
                $nama_lengkap = sanitize_input($_POST['nama_lengkap']);
                $email = sanitize_input($_POST['email']);
                $role = sanitize_input($_POST['role']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                // Validasi input
                if (empty($nama_lengkap) || empty($email)) {
                    $message = "Nama lengkap dan email harus diisi!";
                    $message_type = "danger";
                } else {
                    // Update user
                    $update_sql = "UPDATE users SET nama_lengkap = ?, email = ?, role = ?, is_active = ? WHERE id = ?";
                    $update_result = executeQuery($update_sql, [$nama_lengkap, $email, $role, $is_active, $user_id]);
                    
                    if ($update_result) {
                        $message = "User berhasil diupdate!";
                        $message_type = "success";
                    } else {
                        $message = "Gagal mengupdate user!";
                        $message_type = "danger";
                    }
                }
                break;
                
            case 'delete':
                // Hapus user
                $user_id = (int)$_POST['user_id'];
                
                // Jangan biarkan user menghapus dirinya sendiri
                if ($user_id == $_SESSION['user_id']) {
                    $message = "Anda tidak bisa menghapus akun Anda sendiri!";
                    $message_type = "danger";
                } else {
                    $delete_sql = "DELETE FROM users WHERE id = ?";
                    $delete_result = executeQuery($delete_sql, [$user_id]);
                    
                    if ($delete_result) {
                        $message = "User berhasil dihapus!";
                        $message_type = "success";
                    } else {
                        $message = "Gagal menghapus user!";
                        $message_type = "danger";
                    }
                }
                break;
                
            case 'change_password':
                // Ganti password
                $user_id = (int)$_POST['user_id'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                // Validasi input
                if (empty($new_password) || empty($confirm_password)) {
                    $message = "Password baru dan konfirmasi password harus diisi!";
                    $message_type = "danger";
                } elseif ($new_password !== $confirm_password) {
                    $message = "Password baru dan konfirmasi password tidak cocok!";
                    $message_type = "danger";
                } else {
                    // Hash password baru
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update password
                    $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                    $update_result = executeQuery($update_sql, [$hashed_password, $user_id]);
                    
                    if ($update_result) {
                        $message = "Password berhasil diubah!";
                        $message_type = "success";
                    } else {
                        $message = "Gagal mengubah password!";
                        $message_type = "danger";
                    }
                }
                break;
        }
    }
}

// Ambil daftar user dari database
$users_sql = "SELECT * FROM users ORDER BY id";
$users_result = executeQuery($users_sql);
$users = [];
if ($users_result) {
    while ($row = $users_result->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - SMA Negeri 6 Surakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #3949ab;
            color: white;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            margin: 0.2rem 0;
            border-radius: 0.375rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .content {
            margin-left: 250px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .table th {
            background-color: #3949ab;
            color: white;
        }
        .btn-primary {
            background-color: #3949ab;
            border-color: #3949ab;
        }
        .btn-primary:hover {
            background-color: #1a237e;
            border-color: #1a237e;
        }
        .status-active {
            color: green;
        }
        .status-inactive {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar d-flex flex-column p-3">
                <h4 class="mb-4">SMA Negeri 6 Surakarta</h4>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="siswa.php" class="nav-link">
                            <i class="bi bi-person-lines-fill me-2"></i>Manajemen Siswa
                        </a>
                    </li>
                    <li>
                        <a href="guru.php" class="nav-link">
                            <i class="bi bi-person-badge me-2"></i>Manajemen Guru
                        </a>
                    </li>
                    <li>
                        <a href="kelas.php" class="nav-link">
                            <i class="bi bi-building me-2"></i>Manajemen Kelas
                        </a>
                    </li>
                    <li>
                        <a href="mata_pelajaran.php" class="nav-link">
                            <i class="bi bi-book me-2"></i>Manajemen Mata Pelajaran
                        </a>
                    </li>
                    <li>
                        <a href="nilai.php" class="nav-link">
                            <i class="bi bi-clipboard-check me-2"></i>Manajemen Nilai
                        </a>
                    </li>
                    <li>
                        <a href="user_management.php" class="nav-link active">
                            <i class="bi bi-people me-2"></i>Manajemen User
                        </a>
                    </li>
                    <li>
                        <a href="logout.php" class="nav-link">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
                <div class="mt-auto">
                    <small class="text-muted">Login sebagai: <?php echo $_SESSION['nama_lengkap']; ?></small>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 content">
                <div class="container-fluid">
                    <h2 class="my-4">Manajemen User</h2>
                    
                    <!-- Alert Message -->
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Add User Button -->
                    <div class="d-flex justify-content-between mb-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah User
                        </button>
                    </div>
                    
                    <!-- Users Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Daftar User</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Nama Lengkap</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['username']; ?></td>
                                            <td><?php echo $user['nama_lengkap']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td>
                                                <?php 
                                                switch ($user['role']) {
                                                    case 'admin': echo 'Administrator'; break;
                                                    case 'guru': echo 'Guru'; break;
                                                    case 'siswa': echo 'Siswa'; break;
                                                    default: echo $user['role'];
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="status-active"><i class="bi bi-check-circle-fill me-1"></i> Aktif</span>
                                                <?php else: ?>
                                                    <span class="status-inactive"><i class="bi bi-x-circle-fill me-1"></i> Tidak Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo $user['username']; ?>"
                                                        data-nama="<?php echo $user['nama_lengkap']; ?>"
                                                        data-email="<?php echo $user['email']; ?>"
                                                        data-role="<?php echo $user['role']; ?>"
                                                        data-active="<?php echo $user['is_active']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-warning me-1"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#changePasswordModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo $user['username']; ?>">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button class="btn btn-sm btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteUserModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-username="<?php echo $user['username']; ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="guru">Guru</option>
                                <option value="siswa">Siswa</option>
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
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_role" class="form-label">Role</label>
                            <select class="form-select" id="edit_role" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="guru">Guru</option>
                                <option value="siswa">Siswa</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_is_active">Aktif</label>
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
    
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="change_password">
                    <input type="hidden" id="password_user_id" name="user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Ganti Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="password_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="password_username" name="username" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ganti Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" id="delete_user_id" name="user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Hapus User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus user <strong id="delete_username"></strong>?</p>
                        <p class="text-danger">Tindakan ini tidak dapat dibatalkan.</p>
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
        // Populate edit user modal
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            var nama = button.getAttribute('data-nama');
            var email = button.getAttribute('data-email');
            var role = button.getAttribute('data-role');
            var active = button.getAttribute('data-active');
            
            var modal = this;
            modal.querySelector('#edit_user_id').value = userId;
            modal.querySelector('#edit_username').value = username;
            modal.querySelector('#edit_nama_lengkap').value = nama;
            modal.querySelector('#edit_email').value = email;
            modal.querySelector('#edit_role').value = role;
            modal.querySelector('#edit_is_active').checked = (active == '1');
        });
        
        // Populate change password modal
        var changePasswordModal = document.getElementById('changePasswordModal');
        changePasswordModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            
            var modal = this;
            modal.querySelector('#password_user_id').value = userId;
            modal.querySelector('#password_username').value = username;
        });
        
        // Populate delete user modal
        var deleteUserModal = document.getElementById('deleteUserModal');
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-id');
            var username = button.getAttribute('data-username');
            
            var modal = this;
            modal.querySelector('#delete_user_id').value = userId;
            modal.querySelector('#delete_username').textContent = username;
        });
    </script>
</body>
</html>