<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($id > 0) {
    // Cek apakah user yang akan dihapus adalah user yang sedang login
    $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    if ($id == $current_user_id) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus user yang sedang aktif.']);
            exit;
        }
        pesan_kilat('Tidak dapat menghapus user yang sedang aktif.', 'error');
    } else {
        // Cek apakah data ada
        $check = pg_query($koneksi, "SELECT id FROM users WHERE id = $id");
        if ($check && pg_num_rows($check) > 0) {
            // Hapus user
            $del = pg_query($koneksi, "DELETE FROM users WHERE id = $id");
            if ($del) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'User berhasil dihapus!']);
                    exit;
                }
                pesan_kilat('User berhasil dihapus!', 'success');
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Gagal menghapus user. Error: ' . pg_last_error($koneksi)]);
                    exit;
                }
                pesan_kilat('Gagal menghapus user. Error: ' . pg_last_error($koneksi), 'error');
            }
        } else {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'User tidak ditemukan.']);
                exit;
            }
            pesan_kilat('User tidak ditemukan.', 'error');
        }
    }
} else {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
        exit;
    }
    pesan_kilat('ID tidak valid.', 'error');
}

if (!$is_ajax) {
    header('Location: index.php');
    exit;
}

