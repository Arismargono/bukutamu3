<?php
// Mulai session
session_start();

// Sertakan file koneksi database
require_once 'db_connect.php';

// Inisialisasi variabel pesan error
$error_message = '';

// Cek apakah form login telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password']; // Password tidak perlu di-sanitize karena akan di-hash
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password harus diisi.";
    } else {
        // Debug logging
        error_log("Login attempt - Username: " . $username);
        
        // Query untuk mencari user dengan username yang diberikan
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1";
        $result = executeQuery($sql, [$username]);
        
        // Debug logging
        error_log("Query executed - Result type: " . gettype($result));
        
        // Konversi hasil query ke array asosiatif
        $rows = [];
        if ($result) {
            if ($result instanceof mysqli_result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                error_log("Fetched rows: " . count($rows));
            } else if (is_object($result) && method_exists($result, 'get_result')) {
                // Handle prepared statement result
                $resultSet = $result->get_result();
                if ($resultSet) {
                    while ($row = $resultSet->fetch_assoc()) {
                        $rows[] = $row;
                    }
                }
                error_log("Fetched rows (from get_result): " . count($rows));
            } else {
                error_log("Unexpected result type: " . gettype($result));
            }
        } else {
            error_log("Query returned false");
        }
        
        if (count($rows) > 0) {
            // User ditemukan
            $user = $rows[0];
            error_log("User found: " . $user['username']);
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Password benar, buat session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role']; // Using 'role' instead of 'level'
                
                // Update last login
                $update_sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
                executeQuery($update_sql, [$user['id']]);
                
                // Redirect ke dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                // Password salah
                $error_message = "Username atau password salah.";
                error_log("Password verification failed for user: " . $username);
            }
        } else {
            // User tidak ditemukan
            $error_message = "Username atau password salah.";
            error_log("User not found: " . $username);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi SMA Negeri 6 Surakarta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            background-color: #3949ab;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background-color: #3949ab;
            border-color: #3949ab;
            width: 100%;
            padding: 10px;
        }
        .btn-primary:hover {
            background-color: #1a237e;
            border-color: #1a237e;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo h1 {
            color: #1a237e;
            font-weight: bold;
        }
        .form-control:focus {
            border-color: #3949ab;
            box-shadow: 0 0 0 0.25rem rgba(57, 73, 171, 0.25);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>SMA NEGERI 6</h1>
            <p class="lead">SURAKARTA</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Login Administrator</h4>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
        
        <div class="back-link">
            <a href="index.php">Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>