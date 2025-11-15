<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Cek apakah data ada
    $check = pg_query($koneksi, "SELECT id FROM anggota WHERE id = $id");
    if ($check && pg_num_rows($check) > 0) {
        $del = pg_query($koneksi, "DELETE FROM anggota WHERE id = $id");
        if ($del) {
            pesan_kilat('Anggota berhasil dihapus!', 'success');
        } else {
            pesan_kilat('Gagal menghapus anggota. Error: ' . pg_last_error($koneksi), 'error');
        }
    } else {
        pesan_kilat('Anggota tidak ditemukan.', 'error');
    }
} else {
    pesan_kilat('ID tidak valid.', 'error');
}

header('Location: index.php');
exit;


