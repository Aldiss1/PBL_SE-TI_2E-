<?php
// Fungsi untuk mengedit data
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/anti_injection.php';

function edit_data($table, $data, $id, $id_field = 'id') {
    global $koneksi;
    
    $id = clean_input($id);
    $set_clause = array();
    
    foreach ($data as $field => $value) {
        $field = clean_input($field);
        $value = clean_input($value);
        $set_clause[] = "$field = '$value'";
    }
    
    $set_str = implode(', ', $set_clause);
    $query = "UPDATE $table SET $set_str WHERE $id_field = '$id'";
    
    $result = pg_query($koneksi, $query);
    if ($result) {
        return true;
    } else {
        error_log("Error UPDATE: " . pg_last_error($koneksi) . " - Query: " . $query);
        return false;
    }
}
?>

