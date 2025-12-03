<?php
error_reporting(0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo "Invalid Request.";
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo "Upload error: " . $file['error'];
    exit;
}

// Lokasi folder upload di server
$upload_dir_root = __DIR__ . '/../../assets/img/blog/';
$base_url_path = '../../assets/img/blog/';

// Pastikan folder ada
if (!is_dir($upload_dir_root)) {
    mkdir($upload_dir_root, 0777, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo "Format tidak diizinkan.";
    exit;
}

// Validasi ukuran file (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo "Ukuran file terlalu besar. Maksimal 5MB.";
    exit;
}

$nama_file_baru = uniqid('blog_', true) . '.' . $ext;

$server_file = $upload_dir_root . $nama_file_baru;
$url_file = $base_url_path . $nama_file_baru;

if (move_uploaded_file($file['tmp_name'], $server_file)) {
    echo $url_file; 
    exit;
}

http_response_code(500);
echo "Gagal upload.";
exit;

