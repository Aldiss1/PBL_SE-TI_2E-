<?php
// Aktifkan error reporting untuk debugging (nonaktifkan di production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Jangan tampilkan error langsung, kirim via JSON

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/koneksi.php';

// Cek login manual (tidak menggunakan cek_login.php karena bisa redirect)
if (!isset($_SESSION['login']) || empty($_SESSION['login'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu.']);
    exit;
}

// Set header JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid Request Method.']);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File tidak ditemukan. Pastikan form menggunakan enctype="multipart/form-data".']);
    exit;
}

$file = $_FILES['file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit;
}

// Lokasi folder upload logo navbar
$upload_dir_root = __DIR__ . '/../../assets/img/logo/';
$base_url_path = 'assets/img/logo/';

// Pastikan folder ada
if (!is_dir($upload_dir_root)) {
    mkdir($upload_dir_root, 0777, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format tidak diizinkan. Hanya JPG, PNG, GIF, WEBP, SVG.']);
    exit;
}

// Validasi ukuran file (max 2MB)
$max_size = 2 * 1024 * 1024; // 2MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB.']);
    exit;
}

// Nama file: navbar_logo dengan timestamp untuk cache busting
$nama_file_baru = 'navbar_logo_' . time() . '.' . $ext;

$server_file = $upload_dir_root . $nama_file_baru;
$url_file = $base_url_path . $nama_file_baru;

if (move_uploaded_file($file['tmp_name'], $server_file)) {
    // Simpan path ke database
    $logo_path = $url_file;
    
    // Cek apakah sudah ada setting navbar_logo
    $cek_query = "SELECT COUNT(*) FROM konten_page WHERE page_key = 'navbar_logo'";
    $result_cek = pg_query($koneksi, $cek_query);
    $count = 0;
    if ($result_cek) {
        $row_cek = pg_fetch_row($result_cek);
        $count = (int)$row_cek[0];
    }
    
    $db_success = false;
    if ($count > 0) {
        // Update
        $update_query = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = 'navbar_logo'";
        $result_update = pg_query_params($koneksi, $update_query, array($logo_path));
        $db_success = $result_update !== false;
    } else {
        // Insert
        $insert_query = "INSERT INTO konten_page (page_key, title, content) VALUES ('navbar_logo', 'Navbar Logo', $1)";
        $result_insert = pg_query_params($koneksi, $insert_query, array($logo_path));
        $db_success = $result_insert !== false;
    }
    
    if ($db_success) {
        echo json_encode([
            'success' => true, 
            'message' => 'Logo navbar berhasil diupload dan disimpan!',
            'url' => $url_file
        ]);
        exit;
    } else {
        // File sudah terupload tapi gagal simpan ke database
        @unlink($server_file); // Hapus file yang sudah terupload
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Gagal menyimpan ke database: ' . pg_last_error($koneksi)
        ]);
        exit;
    }
}

// Jika sampai sini berarti move_uploaded_file gagal
$error_msg = 'Gagal memindahkan file.';
if (!is_writable($upload_dir_root)) {
    $error_msg = 'Folder upload tidak dapat ditulis. Periksa permission folder.';
} elseif (!file_exists($upload_dir_root)) {
    $error_msg = 'Folder upload tidak ditemukan.';
}

http_response_code(500);
echo json_encode([
    'success' => false, 
    'message' => $error_msg,
    'debug' => [
        'upload_dir' => $upload_dir_root,
        'is_writable' => is_writable($upload_dir_root),
        'file_exists' => file_exists($upload_dir_root)
    ]
]);
exit;



