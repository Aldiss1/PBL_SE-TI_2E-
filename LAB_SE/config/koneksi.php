<?php
// Include helper untuk cek extension
require_once __DIR__ . '/check_pgsql.php';

// Koneksi ke database PostgreSQL
$host = "localhost";
$port = "5432";
$username = "postgres";
$password = "123456";
$database = "lab_se";

// Membuat connection string
$connection_string = "host=$host port=$port dbname=$database user=$username password=$password";

// Membuat koneksi dengan error handling
$koneksi = @pg_connect($connection_string);

// Cek koneksi
if (!$koneksi) {
   
    // Tampilkan error detail untuk debugging (bisa diubah di production)
    $error_msg = "Koneksi ke database gagal!\n\n";
    $error_msg .= "Error: " . $error . "\n\n";
    $error_msg .= "Periksa:\n";
    $error_msg .= "1. Apakah PostgreSQL service sudah berjalan?\n";
    $error_msg .= "2. Apakah database '$database' sudah dibuat?\n";
    $error_msg .= "3. Apakah username dan password benar?\n";
    $error_msg .= "4. Apakah port $port sudah benar?\n\n";
    $error_msg .= "Konfigurasi saat ini:\n";
    $error_msg .= "- Host: $host\n";
    $error_msg .= "- Port: $port\n";
    $error_msg .= "- Database: $database\n";
    $error_msg .= "- Username: $username\n";
    
    die("<pre style='font-family: monospace; padding: 20px; background: #f5f5f5; border: 2px solid #dc3545; border-radius: 5px; margin: 20px;'>" . htmlspecialchars($error_msg) . "</pre>");
}

// Set client encoding ke UTF-8
if (!pg_set_client_encoding($koneksi, "UTF8")) {
    // Jika gagal set encoding, tetap lanjutkan (bukan fatal error)
    error_log("Warning: Gagal set client encoding ke UTF8");
}
?>

