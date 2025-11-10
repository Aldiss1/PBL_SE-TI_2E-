<?php
// Fungsi untuk menghapus data
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/anti_injection.php';

function hapus_data($table, $id, $id_field = 'id') {
    global $koneksi;
    
    $id = clean_input($id);
    $query = "DELETE FROM $table WHERE $id_field = '$id'";
    
    $result = pg_query($koneksi, $query);
    if ($result) {
        return true;
    } else {
        error_log("Error DELETE: " . pg_last_error($koneksi) . " - Query: " . $query);
        return false;
    }
}
?>

