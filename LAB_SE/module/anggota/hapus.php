<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($id > 0) {
    // Cek apakah data ada
    $check = pg_query($koneksi, "SELECT id, nama FROM anggota WHERE id = $id");
    if ($check && pg_num_rows($check) > 0) {
        $del = pg_query($koneksi, "DELETE FROM anggota WHERE id = $id");
        if ($del) {
            // Refresh materialized views setelah delete anggota
            if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
                require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
                if (function_exists('refresh_views_complete')) {
                    @refresh_views_complete();
                }
            }
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Anggota berhasil dihapus!']);
                exit;
            }
            pesan_kilat('Anggota berhasil dihapus!', 'success');
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus anggota. Error: ' . pg_last_error($koneksi)]);
                exit;
            }
            pesan_kilat('Gagal menghapus anggota. Error: ' . pg_last_error($koneksi), 'error');
        }
    } else {
        if ($is_ajax) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Anggota tidak ditemukan.']);
            exit;
        }
        pesan_kilat('Anggota tidak ditemukan.', 'error');
    }
} else {
    if ($is_ajax) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }
    pesan_kilat('ID tidak valid.', 'error');
}

if (!$is_ajax) {
    header('Location: index.php');
    exit;
}


