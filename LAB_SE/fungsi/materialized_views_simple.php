<?php
/**
 * Fungsi Sederhana untuk Materialized Views
 * Cocok untuk pemula yang baru belajar
 */

require_once __DIR__ . '/../config/koneksi.php';

/**
 * Cek apakah materialized view ada di database
 * 
 * @param string $view_name Nama materialized view
 * @return bool
 */
function materialized_view_exists($view_name) {
    global $koneksi;
    if (!$koneksi) return false;
    
    $query = "SELECT 1 FROM pg_matviews WHERE matviewname = '$view_name' LIMIT 1";
    $result = @pg_query($koneksi, $query);
    return $result && pg_num_rows($result) > 0;
}

/**
 * Ambil statistik sederhana dari materialized view
 * Menggantikan query COUNT yang berulang-ulang
 * 
 * @return array|false
 */
function get_stats_simple() {
    global $koneksi;
    if (!$koneksi) return false;
    
    // Cek apakah materialized view ada
    if (!materialized_view_exists('mv_stats_simple')) {
        return false;
    }
    
    $query = "SELECT * FROM mv_stats_simple LIMIT 1";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        return pg_fetch_assoc($result);
    }
    return false;
}

/**
 * Ambil pendaftar terbaru (10 terbaru)
 * 
 * @param int $limit Jumlah data (default: 10)
 * @return array
 */
function get_pendaftar_terbaru($limit = 10) {
    global $koneksi;
    $pendaftar = [];
    
    if (!$koneksi) return $pendaftar;
    
    // Cek apakah materialized view ada
    if (!materialized_view_exists('mv_pendaftar_terbaru')) {
        return $pendaftar;
    }
    
    $query = "SELECT * FROM mv_pendaftar_terbaru LIMIT " . (int)$limit;
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $pendaftar[] = $row;
        }
    }
    return $pendaftar;
}

/**
 * Ambil blog yang sudah published
 * 
 * @param int $limit Jumlah data
 * @param string|null $category Filter kategori (opsional)
 * @return array
 */
function get_blog_published_simple($limit = 10, $category = null) {
    global $koneksi;
    $blogs = [];
    
    if (!$koneksi) return $blogs;
    
    // Cek apakah materialized view ada
    if (!materialized_view_exists('mv_blog_published_simple')) {
        return $blogs;
    }
    
    $query = "SELECT * FROM mv_blog_published_simple";
    if ($category) {
        $category_escaped = pg_escape_string($koneksi, $category);
        $query .= " WHERE LOWER(category) = LOWER('$category_escaped')";
    }
    $query .= " LIMIT " . (int)$limit;
    
    $result = @pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $blogs[] = $row;
        }
    }
    return $blogs;
}

/**
 * Ambil statistik pendaftar per status
 * 
 * @return array
 */
function get_pendaftar_per_status() {
    global $koneksi;
    $stats = ['pending' => 0, 'accepted' => 0, 'rejected' => 0];
    
    if (!$koneksi) return $stats;
    
    // Cek apakah materialized view ada
    if (!materialized_view_exists('mv_pendaftar_per_status')) {
        return $stats;
    }
    
    $query = "SELECT status, jumlah FROM mv_pendaftar_per_status";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[$row['status']] = (int)$row['jumlah'];
        }
    }
    return $stats;
}

/**
 * Refresh semua materialized views sederhana
 * 
 * @return bool
 */
function refresh_views_simple() {
    global $koneksi;
    if (!$koneksi) return false;
    
    $views = [
        'mv_stats_simple',
        'mv_pendaftar_terbaru',
        'mv_blog_published_simple',
        'mv_pendaftar_per_status'
    ];
    
    $success = true;
    foreach ($views as $view) {
        // Cek apakah view ada sebelum refresh
        if (!materialized_view_exists($view)) {
            $success = false;
            continue;
        }
        
        $query = "REFRESH MATERIALIZED VIEW $view";
        $result = @pg_query($koneksi, $query);
        if (!$result) {
            $success = false;
        }
    }
    
    return $success;
}

/**
 * Buat semua materialized views sederhana
 * Fungsi ini membaca file SQL dan menjalankannya
 * 
 * @return bool
 */
function create_materialized_views_simple() {
    global $koneksi;
    if (!$koneksi) return false;
    
    $sql_file = __DIR__ . '/../materialized_views.sql';
    if (!file_exists($sql_file)) {
        return false;
    }
    
    $sql = file_get_contents($sql_file);
    if (!$sql) {
        return false;
    }
    
    // Hapus bagian REFRESH di akhir (karena view belum ada)
    $sql = preg_replace('/REFRESH MATERIALIZED VIEW.*?;/s', '', $sql);
    
    // Eksekusi setiap statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $success = true;
    
    foreach ($statements as $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        $result = @pg_query($koneksi, $statement);
        if (!$result) {
            $success = false;
        }
    }
    
    return $success;
}

?>

