<?php
require_once __DIR__ . '/cek_login.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
        exit;
    }

    header('Location: ' . labse_build_login_path());
    exit;
}
?>

