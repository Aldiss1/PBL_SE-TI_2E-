<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/anti_injection.php';
require_once __DIR__ . '/fungsi/pesan_kilat.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['login'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa') {
        header('Location: module/mahasiswa/dashboard.php');
    } else {
        header('Location: module/admin/dashboard.php');
    }
    exit;
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean_input($_POST['username']);
    $password = clean_input($_POST['password']);
    
    if (!$koneksi) {
        pesan_kilat('Koneksi database gagal!', 'error');
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' AND password = MD5('$password')";
        $result = pg_query($koneksi, $query);
        
        if (!$result) {
            $error = pg_last_error($koneksi);
            if (strpos($error, 'does not exist') !== false) {
                header('Location: setup.php');
                exit;
            }
            pesan_kilat('Terjadi kesalahan sistem.', 'error');
        } elseif (pg_num_rows($result) > 0) {
            $user = pg_fetch_assoc($result);
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = isset($user['id']) ? (int)$user['id'] : 0;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = isset($user['role']) ? $user['role'] : 'admin';
            if ($_SESSION['role'] === 'mahasiswa') {
                header('Location: module/mahasiswa/dashboard.php');
            } else {
                header('Location: module/admin/dashboard.php');
            }
            exit;
        } else {
            pesan_kilat('Username atau password salah', 'error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="assets/css/Linelcons.2.0.css" /> 
    
    <link rel="stylesheet" href="assets/css/login.css" />
    
</head>
<body class="login-page-wrapper">
    <div class="row justify-content-center w-100"> 
        
        <div class="login-card"> 
            
            <div class="text-center">
                 <h2 class="login-title">Login</h2>
                 <p class="login-subtitle">Akses ke Sistem Laboratorium</p>
            </div>
            
            <?php tampilkan_pesan(); ?>
            
            <form method="POST">
                
                <div class="mb-4 input-icon-group">
                    <label for="username" class="form-label visually-hidden">Nama Pengguna</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Nama Pengguna" required>
                </div>
                
                <div class="mb-4 input-icon-group">
                    <label for="password" class="form-label visually-hidden">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Kata Sandi" required>
                </div>
                
                <button type="submit" class="btn btn-main w-100">LOGIN</button>
                
                <div class="back-link-container">
                    <a href="index.php">← Kembali ke Website Utama</a>
                </div>
                
            </form>
        </div>
    </div>
    <script src="js/bootstrap.5.0.0.alpha-2-min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var closeButtons = document.querySelectorAll('.alert .btn-close, .alert .close, .alert button[data-dismiss="alert"]');
            
            closeButtons.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var alert = this.closest('.alert');
                    if (alert) {
                        alert.style.display = 'none';
                        alert.remove();
                    }
                });
            });
        });
    </script>

</body>
</html>