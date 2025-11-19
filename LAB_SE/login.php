<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/anti_injection.php';
require_once __DIR__ . '/fungsi/pesan_kilat.php';

// Cek apakah tabel users sudah ada, jika belum redirect ke setup
if ($koneksi) {
    $check = @pg_query($koneksi, "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'users')");
    if ($check) {
        $exists = pg_fetch_result($check, 0, 0);
        if ($exists == 'f') {
            header('Location: setup.php');
            exit;
        }
    }
}

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
    <title>Login Admin</title>
    <link rel="stylesheet" href="assets/css/bootstrap-5.0.0-alpha-2.min.css" />
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <h2 class="text-center mb-4">Login Admin</h2>
                <?php tampilkan_pesan(); ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

