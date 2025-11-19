<?php
session_start();

// Hapus semua session
$_SESSION = array();

// Hapus session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy session
session_destroy();

// Redirect ke login (menggunakan path absolut dari root)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$redirect_url = $base_url . $script_path . '/login.php';

header('Location: ' . $redirect_url);
exit;
?>

