<?php
/**
 * Fungsi untuk logging aktivitas pendaftar
 * Mencatat semua perubahan: status, akun, update, delete
 */

require_once __DIR__ . '/../config/koneksi.php';

/**
 * Log aktivitas pendaftar
 * @param int $pendaftaran_id ID pendaftaran
 * @param string $action_type Jenis aksi: 'status_change', 'account_create', 'account_deactivate', 'account_reset_password', 'update', 'delete'
 * @param string $description Deskripsi aktivitas
 * @param array $old_data Data lama (optional)
 * @param array $new_data Data baru (optional)
 * @return bool
 */
function log_pendaftar_activity($pendaftaran_id, $action_type, $description, $old_data = null, $new_data = null) {
    global $koneksi;
    
    if (!$koneksi) return false;
    
    // Ambil user_id dari session
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
    
    $pendaftaran_id_escaped = pg_escape_string($koneksi, $pendaftaran_id);
    $action_type_escaped = pg_escape_string($koneksi, $action_type);
    $description_escaped = pg_escape_string($koneksi, $description);
    $user_id_escaped = pg_escape_string($koneksi, $user_id);
    $username_escaped = pg_escape_string($koneksi, $username);
    
    $old_data_json = $old_data ? pg_escape_string($koneksi, json_encode($old_data)) : 'NULL';
    $new_data_json = $new_data ? pg_escape_string($koneksi, json_encode($new_data)) : 'NULL';
    
    // Cek apakah tabel log_aktivitas_pendaftar ada, jika tidak buat
    $check_table = pg_query($koneksi, "
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'log_aktivitas_pendaftar'
        )
    ");
    
    if ($check_table) {
        $table_exists = pg_fetch_result($check_table, 0, 0);
        if ($table_exists !== 't') {
            // Buat tabel jika belum ada
            pg_query($koneksi, "
                CREATE TABLE IF NOT EXISTS log_aktivitas_pendaftar (
                    id SERIAL PRIMARY KEY,
                    pendaftaran_id INTEGER NOT NULL,
                    action_type VARCHAR(50) NOT NULL,
                    description TEXT,
                    old_data JSONB,
                    new_data JSONB,
                    user_id INTEGER,
                    username VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Buat index
            pg_query($koneksi, "CREATE INDEX IF NOT EXISTS idx_log_pendaftaran_id ON log_aktivitas_pendaftar(pendaftaran_id)");
            pg_query($koneksi, "CREATE INDEX IF NOT EXISTS idx_log_created_at ON log_aktivitas_pendaftar(created_at)");
            pg_query($koneksi, "CREATE INDEX IF NOT EXISTS idx_log_action_type ON log_aktivitas_pendaftar(action_type)");
        }
    }
    
    // Insert log
    $query = "INSERT INTO log_aktivitas_pendaftar (pendaftaran_id, action_type, description, old_data, new_data, user_id, username) 
              VALUES ($pendaftaran_id_escaped, '$action_type_escaped', '$description_escaped', 
                      " . ($old_data_json !== 'NULL' ? "'$old_data_json'" : 'NULL') . ", 
                      " . ($new_data_json !== 'NULL' ? "'$new_data_json'" : 'NULL') . ", 
                      " . ($user_id > 0 ? $user_id_escaped : 'NULL') . ", 
                      '$username_escaped')";
    
    $result = pg_query($koneksi, $query);
    return $result !== false;
}

/**
 * Ambil log aktivitas untuk pendaftar tertentu
 * @param int $pendaftaran_id ID pendaftaran
 * @return array
 */
function get_pendaftar_logs($pendaftaran_id) {
    global $koneksi;
    
    if (!$koneksi) return [];
    
    $id_escaped = pg_escape_string($koneksi, $pendaftaran_id);
    $query = "SELECT * FROM log_aktivitas_pendaftar 
              WHERE pendaftaran_id = $id_escaped 
              ORDER BY created_at DESC";
    
    $result = pg_query($koneksi, $query);
    $logs = [];
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}
?>

