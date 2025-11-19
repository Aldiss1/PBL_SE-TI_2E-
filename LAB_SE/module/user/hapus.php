<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Cek apakah user yang akan dihapus adalah user yang sedang login
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($id == $current_user_id) {
        pesan_kilat('Tidak dapat menghapus user yang sedang aktif.', 'error');
    } else {
        // Cek apakah data ada
        $check = pg_query($koneksi, "SELECT id, username FROM users WHERE id = $id");
        if ($check && pg_num_rows($check) > 0) {
            $user_data = pg_fetch_assoc($check);
            
            // Hapus user
            $del = pg_query($koneksi, "DELETE FROM users WHERE id = $id");
            if ($del) {
                pesan_kilat('User berhasil dihapus!', 'success');
            } else {
                pesan_kilat('Gagal menghapus user. Error: ' . pg_last_error($koneksi), 'error');
            }
        } else {
            pesan_kilat('User tidak ditemukan.', 'error');
        }
    }
} else {
    pesan_kilat('ID tidak valid.', 'error');
}

header('Location: index.php');
exit;

