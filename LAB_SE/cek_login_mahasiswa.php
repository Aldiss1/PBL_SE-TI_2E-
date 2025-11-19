<?php
session_start();

// Wajib login
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
    // Jika admin, arahkan ke dashboard admin
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header('Location: module/admin/dashboard.php');
        exit;
    }
    // Default kembali ke login
    header('Location: login.php');
    exit;
}
?>


