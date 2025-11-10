<?php
// Fungsi untuk menambah data
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/anti_injection.php';

function tambah_data($table, $data) {
    global $koneksi;
    
    $fields = array_keys($data);
    $values = array_values($data);
    
    // Membersihkan data
    foreach ($values as $key => $value) {
        $values[$key] = clean_input($value);
    }
    
    $fields_str = implode(', ', $fields);
    $values_str = "'" . implode("', '", $values) . "'";
    
    $query = "INSERT INTO $table ($fields_str) VALUES ($values_str)";
    
    $result = pg_query($koneksi, $query);
    if ($result) {
        return true;
    } else {
        error_log("Error INSERT: " . pg_last_error($koneksi) . " - Query: " . $query);
        return false;
    }
}
?>

