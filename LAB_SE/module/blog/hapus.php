<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Cek apakah blog ada
    $query_check = "SELECT id FROM blog WHERE id = $id";
    $result_check = pg_query($koneksi, $query_check);
    
    if ($result_check && pg_num_rows($result_check) > 0) {
        // Hapus blog
        $query = "DELETE FROM blog WHERE id = $id";
        $result = pg_query($koneksi, $query);
        
        if ($result) {
            pesan_kilat('Blog berhasil dihapus!', 'success');
        } else {
            pesan_kilat('Gagal menghapus blog. Error: ' . pg_last_error($koneksi), 'error');
        }
    } else {
        pesan_kilat('Blog tidak ditemukan!', 'error');
    }
} else {
    pesan_kilat('ID blog tidak valid!', 'error');
}

header('Location: index.php');
exit;
?>

