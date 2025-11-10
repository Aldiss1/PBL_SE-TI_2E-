<?php
// Fungsi untuk mencegah SQL Injection dan XSS
function anti_injection($data) {
    // Menghapus karakter berbahaya
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // Menghapus karakter SQL injection
    $data = str_replace("'", "", $data);
    $data = str_replace('"', '', $data);
    $data = str_replace(';', '', $data);
    $data = str_replace('--', '', $data);
    $data = str_replace('/*', '', $data);
    $data = str_replace('*/', '', $data);
    $data = str_replace('xp_', '', $data);
    
    return $data;
}

// Fungsi untuk membersihkan input
function clean_input($data) {
    global $koneksi;
    if (isset($koneksi)) {
        return pg_escape_string($koneksi, anti_injection($data));
    }
    return anti_injection($data);
}
?>

