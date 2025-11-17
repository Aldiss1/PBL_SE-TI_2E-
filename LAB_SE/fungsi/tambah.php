<?php
// Fungsi untuk menambah data
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../config/anti_injection.php';

function tambah_data($table, $data) {
    global $koneksi;
    
    // Validasi nama tabel dan kolom sederhana (hanya huruf, angka, dan underscore)
    $is_valid_identifier = static function ($identifier) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier) === 1;
    };

    if (!$is_valid_identifier($table)) {
        error_log("Invalid table name: " . $table);
        return false;
    }

    $fields = array_keys($data);
    foreach ($fields as $field) {
        if (!$is_valid_identifier($field)) {
            error_log("Invalid field name: " . $field);
            return false;
        }
    }

    $values = array_values($data);

    // Bersihkan dan siapkan nilai parameter
    foreach ($values as $key => $value) {
        $values[$key] = clean_input($value);
    }

    $fields_str = implode(', ', $fields);

    // Buat placeholder parameter $1, $2, ...
    $placeholders = [];
    for ($i = 1; $i <= count($values); $i++) {
        $placeholders[] = '$' . $i;
    }
    $placeholders_str = implode(', ', $placeholders);

    $query = "INSERT INTO {$table} ({$fields_str}) VALUES ({$placeholders_str})";

    $result = pg_query_params($koneksi, $query, $values);
    if ($result) {
        return true;
    }

    error_log("Error INSERT: " . pg_last_error($koneksi) . " - Query: " . $query);
    return false;
}
?>

