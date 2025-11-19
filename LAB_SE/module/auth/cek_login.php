<?php
session_start();

if (!function_exists('labse_build_login_path')) {
    function labse_build_login_path(): string
    {
        $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
        $appRoot = realpath(dirname(__DIR__, 2));

        if ($documentRoot) {
            $documentRoot = str_replace('\\', '/', $documentRoot);
        }
        if ($appRoot) {
            $appRoot = str_replace('\\', '/', $appRoot);
        }

        $relative = '';
        if ($documentRoot && $appRoot && strpos($appRoot, $documentRoot) === 0) {
            $relative = substr($appRoot, strlen($documentRoot));
            $relative = '/' . ltrim($relative, '/');
            if ($relative === '/') {
                $relative = '';
            }
        }

        return ($relative ?: '') . '/module/auth/login.php';
    }
}

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header('Location: ' . labse_build_login_path());
    exit;
}
?>
