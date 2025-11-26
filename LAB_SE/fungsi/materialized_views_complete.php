<?php
/**
 * ============================================
 * FUNGSI MATERIALIZED VIEWS UNTUK PEMULA
 * ============================================
 * 
 * File ini berisi fungsi-fungsi untuk mengambil data dari materialized views
 * Materialized views = Data yang sudah dihitung dan disimpan di database (lebih cepat!)
 * 
 * CARA PAKAI:
 * 1. Include file ini: require_once 'fungsi/materialized_views_complete.php';
 * 2. Panggil fungsi yang diinginkan: $stats = get_stats_complete();
 * 3. Gunakan hasilnya: echo $stats['total_anggota'];
 * 
 * PENTING: Materialized views harus dibuat dulu di database!
 * Jalankan: materialized_views_complete.sql di database
 */

require_once __DIR__ . '/../config/koneksi.php';

/**
 * Cek apakah materialized view sudah dibuat di database
 * 
 * Fungsi ini mengecek apakah materialized view sudah ada
 * Jika belum ada, fungsi lain akan return false atau array kosong
 * 
 * @param string $view_name Nama materialized view (contoh: 'mv_stats_complete')
 * @return bool true jika ada, false jika tidak ada
 */
function materialized_view_exists($view_name) {
    global $koneksi;
    if (!$koneksi) return false;
    
    $query = "SELECT 1 FROM pg_matviews WHERE matviewname = '$view_name' LIMIT 1";
    $result = @pg_query($koneksi, $query);
    return $result && pg_num_rows($result) > 0;
}

/**
 * Ambil semua statistik dashboard (anggota, pendaftar, blog, dll)
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_stats_complete();
 * if ($stats) {
 *     echo "Total Anggota: " . $stats['total_anggota'];
 *     echo "Total Pendaftar: " . $stats['total_pendaftar'];
 *     echo "Pending: " . $stats['pendaftar_pending'];
 * }
 * 
 * @return array|false Array berisi statistik, atau false jika materialized view belum dibuat
 */
function get_stats_complete() {
    global $koneksi;
    if (!$koneksi) return false;
    
    // Cek apakah materialized view sudah dibuat
    if (!materialized_view_exists('mv_stats_complete')) {
        return false;
    }
    
    // Ambil data dari materialized view
    $query = "SELECT * FROM mv_stats_complete LIMIT 1";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        return pg_fetch_assoc($result);
    }
    return false;
}

/**
 * Ambil pendaftar terbaru (urutkan dari yang terbaru)
 * 
 * CONTOH PENGGUNAAN:
 * $pendaftar = get_pendaftar_terbaru_complete(5); // Ambil 5 pendaftar terbaru
 * foreach ($pendaftar as $p) {
 *     echo $p['nama'] . " - " . $p['status'] . "<br>";
 * }
 * 
 * @param int $limit Berapa banyak data yang diambil (default: 20)
 * @return array Array berisi data pendaftar terbaru
 */
function get_pendaftar_terbaru_complete($limit = 20) {
    global $koneksi;
    $pendaftar = [];
    
    if (!$koneksi) return $pendaftar;
    
    if (!materialized_view_exists('mv_pendaftar_terbaru_complete')) {
        return $pendaftar;
    }
    
    $query = "SELECT * FROM mv_pendaftar_terbaru_complete LIMIT " . (int)$limit;
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $pendaftar[] = $row;
        }
    }
    return $pendaftar;
}

/**
 * Ambil blog yang sudah dipublish (ditampilkan ke user)
 * 
 * CONTOH PENGGUNAAN:
 * // Ambil 10 blog terbaru
 * $blogs = get_blog_published_complete(10);
 * 
 * // Ambil 5 blog kategori 'event'
 * $events = get_blog_published_complete(5, 'event');
 * 
 * // Ambil 5 blog paling populer (urutkan berdasarkan views)
 * $popular = get_blog_published_complete(5, null, 'views');
 * 
 * @param int $limit Berapa banyak blog yang diambil (default: 10)
 * @param string|null $category Filter berdasarkan kategori, contoh: 'event', 'news' (opsional)
 * @param string $order_by Urutkan berdasarkan: 'published_at' (terbaru) atau 'views' (paling populer)
 * @return array Array berisi data blog
 */
function get_blog_published_complete($limit = 10, $category = null, $order_by = 'published_at') {
    global $koneksi;
    $blogs = [];
    
    if (!$koneksi) return $blogs;
    
    if (!materialized_view_exists('mv_blog_published_complete')) {
        return $blogs;
    }
    
    $query = "SELECT * FROM mv_blog_published_complete";
    
    if ($category) {
        $category_escaped = pg_escape_string($koneksi, $category);
        $query .= " WHERE LOWER(category) = LOWER('$category_escaped')";
    }
    
    // Order by
    if ($order_by === 'views') {
        $query .= " ORDER BY views DESC";
    } else {
        $query .= " ORDER BY COALESCE(published_at, created_at) DESC";
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
 * Ambil jumlah pendaftar per status (pending, accepted, rejected)
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_pendaftar_per_status();
 * echo "Pending: " . $stats['pending'];
 * echo "Accepted: " . $stats['accepted'];
 * echo "Rejected: " . $stats['rejected'];
 * 
 * @return array Array dengan key: 'pending', 'accepted', 'rejected'
 */
function get_pendaftar_per_status() {
    global $koneksi;
    $stats = ['pending' => 0, 'accepted' => 0, 'rejected' => 0];
    
    if (!$koneksi) return $stats;
    
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
 * Ambil jumlah anggota per jabatan
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_anggota_per_jabatan();
 * foreach ($stats as $jabatan => $jumlah) {
 *     echo "$jabatan: $jumlah anggota<br>";
 * }
 * 
 * @return array Array dengan key = nama jabatan, value = jumlah anggota
 */
function get_anggota_per_jabatan() {
    global $koneksi;
    $stats = [];
    
    if (!$koneksi) return $stats;
    
    if (!materialized_view_exists('mv_anggota_per_jabatan')) {
        return $stats;
    }
    
    $query = "SELECT jabatan, jumlah FROM mv_anggota_per_jabatan";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[$row['jabatan']] = (int)$row['jumlah'];
        }
    }
    return $stats;
}

/**
 * Ambil jumlah blog per kategori
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_blog_per_kategori();
 * foreach ($stats as $item) {
 *     echo $item['category'] . ": " . $item['jumlah'] . " blog (" . $item['total_views'] . " views)<br>";
 * }
 * 
 * @return array Array berisi data: category, jumlah, total_views
 */
function get_blog_per_kategori() {
    global $koneksi;
    $stats = [];
    
    if (!$koneksi) return $stats;
    
    if (!materialized_view_exists('mv_blog_per_kategori')) {
        return $stats;
    }
    
    $query = "SELECT category, jumlah, total_views FROM mv_blog_per_kategori";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[] = [
                'category' => $row['category'],
                'jumlah' => (int)$row['jumlah'],
                'total_views' => (int)$row['total_views']
            ];
        }
    }
    return $stats;
}

/**
 * Ambil jumlah pendaftar per bulan (6 bulan terakhir)
 * Berguna untuk membuat chart/grafik
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_pendaftar_per_bulan();
 * foreach ($stats as $item) {
 *     echo $item['label'] . ": " . $item['jumlah'] . " pendaftar<br>";
 * }
 * 
 * @return array Array berisi data: bulan, label, jumlah
 */
function get_pendaftar_per_bulan() {
    global $koneksi;
    $stats = [];
    
    if (!$koneksi) return $stats;
    
    if (!materialized_view_exists('mv_pendaftar_per_bulan')) {
        return $stats;
    }
    
    $query = "SELECT bulan_format, bulan_label, jumlah FROM mv_pendaftar_per_bulan ORDER BY bulan DESC";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[] = [
                'bulan' => $row['bulan_format'],
                'label' => $row['bulan_label'],
                'jumlah' => (int)$row['jumlah']
            ];
        }
    }
    return $stats;
}

/**
 * Ambil blog paling populer (diurutkan berdasarkan views terbanyak)
 * 
 * CONTOH PENGGUNAAN:
 * $popular = get_blog_populer(5); // Ambil 5 blog terpopuler
 * foreach ($popular as $blog) {
 *     echo $blog['title'] . " - " . $blog['views'] . " views<br>";
 * }
 * 
 * @param int $limit Berapa banyak blog yang diambil (default: 10)
 * @return array Array berisi data blog terpopuler
 */
function get_blog_populer($limit = 10) {
    global $koneksi;
    $blogs = [];
    
    if (!$koneksi) return $blogs;
    
    if (!materialized_view_exists('mv_blog_populer')) {
        return $blogs;
    }
    
    $query = "SELECT * FROM mv_blog_populer LIMIT " . (int)$limit;
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $blogs[] = $row;
        }
    }
    return $blogs;
}

/**
 * Ambil riwayat perubahan status pendaftar terbaru
 * Menampilkan siapa yang mengubah status dan kapan
 * 
 * CONTOH PENGGUNAAN:
 * $riwayat = get_riwayat_status_terbaru(10);
 * foreach ($riwayat as $r) {
 *     echo $r['nama'] . ": " . $r['status_lama'] . " → " . $r['status_baru'] . "<br>";
 * }
 * 
 * @param int $limit Berapa banyak riwayat yang diambil (default: 20)
 * @return array Array berisi data riwayat perubahan status
 */
function get_riwayat_status_terbaru($limit = 20) {
    global $koneksi;
    $riwayat = [];
    
    if (!$koneksi) return $riwayat;
    
    if (!materialized_view_exists('mv_riwayat_status_terbaru')) {
        return $riwayat;
    }
    
    $query = "SELECT * FROM mv_riwayat_status_terbaru LIMIT " . (int)$limit;
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $riwayat[] = $row;
        }
    }
    return $riwayat;
}

/**
 * Ambil data anggota lengkap dengan informasi jabatan
 * 
 * CONTOH PENGGUNAAN:
 * // Ambil semua anggota
 * $anggota = get_anggota_lengkap();
 * 
 * // Ambil hanya anggota dengan jabatan 'Ketua'
 * $ketua = get_anggota_lengkap(null, 'Ketua');
 * 
 * // Ambil 5 anggota terbaru
 * $terbaru = get_anggota_lengkap(5);
 * 
 * @param int|null $limit Berapa banyak anggota (null = semua)
 * @param string|null $jabatan Filter berdasarkan jabatan (opsional)
 * @return array Array berisi data anggota
 */
function get_anggota_lengkap($limit = null, $jabatan = null) {
    global $koneksi;
    $anggota = [];
    
    if (!$koneksi) return $anggota;
    
    if (!materialized_view_exists('mv_anggota_lengkap')) {
        return $anggota;
    }
    
    $query = "SELECT * FROM mv_anggota_lengkap";
    
    if ($jabatan) {
        $jabatan_escaped = pg_escape_string($koneksi, $jabatan);
        $query .= " WHERE jabatan = '$jabatan_escaped'";
    }
    
    if ($limit) {
        $query .= " LIMIT " . (int)$limit;
    }
    
    $result = @pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $anggota[] = $row;
        }
    }
    return $anggota;
}

/**
 * Ambil jumlah blog yang dipublish per bulan (6 bulan terakhir)
 * Berguna untuk membuat chart/grafik blog
 * 
 * CONTOH PENGGUNAAN:
 * $stats = get_blog_per_bulan();
 * foreach ($stats as $item) {
 *     echo $item['label'] . ": " . $item['jumlah'] . " blog<br>";
 * }
 * 
 * @return array Array berisi data: bulan, label, jumlah
 */
function get_blog_per_bulan() {
    global $koneksi;
    $stats = [];
    
    if (!$koneksi) return $stats;
    
    if (!materialized_view_exists('mv_blog_per_bulan')) {
        return $stats;
    }
    
    $query = "SELECT bulan_format, bulan_label, jumlah FROM mv_blog_per_bulan ORDER BY bulan DESC";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $stats[] = [
                'bulan' => $row['bulan_format'],
                'label' => $row['bulan_label'],
                'jumlah' => (int)$row['jumlah']
            ];
        }
    }
    return $stats;
}

/**
 * Ambil pendaftar yang masih pending (belum di-review)
 * Berguna untuk notifikasi admin bahwa ada pendaftar yang perlu direview
 * 
 * CONTOH PENGGUNAAN:
 * $pending = get_pendaftar_pending();
 * $jumlah_pending = count($pending);
 * echo "Ada $jumlah_pending pendaftar yang perlu direview";
 * 
 * @return array Array berisi data pendaftar yang masih pending
 */
function get_pendaftar_pending() {
    global $koneksi;
    $pendaftar = [];
    
    if (!$koneksi) return $pendaftar;
    
    if (!materialized_view_exists('mv_pendaftar_pending')) {
        return $pendaftar;
    }
    
    $query = "SELECT * FROM mv_pendaftar_pending";
    $result = @pg_query($koneksi, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $pendaftar[] = $row;
        }
    }
    return $pendaftar;
}

/**
 * Refresh semua materialized views (update data)
 * 
 * PENTING: Panggil fungsi ini setelah ada perubahan data (INSERT/UPDATE/DELETE)
 * Atau refresh secara berkala (setiap 15 menit) via cron job
 * 
 * CONTOH PENGGUNAAN:
 * if (refresh_views_complete()) {
 *     echo "Berhasil refresh!";
 * } else {
 *     echo "Gagal refresh!";
 * }
 * 
 * @return bool true jika berhasil, false jika gagal
 */
function refresh_views_complete() {
    global $koneksi;
    if (!$koneksi) return false;
    
    $views = [
        'mv_stats_complete',
        'mv_pendaftar_terbaru_complete',
        'mv_blog_published_complete',
        'mv_pendaftar_per_status',
        'mv_anggota_per_jabatan',
        'mv_blog_per_kategori',
        'mv_pendaftar_per_bulan',
        'mv_blog_populer',
        'mv_riwayat_status_terbaru',
        'mv_anggota_lengkap',
        'mv_blog_per_bulan',
        'mv_pendaftar_pending'
    ];
    
    $success = true;
    foreach ($views as $view) {
        // Cek apakah view ada sebelum refresh
        if (!materialized_view_exists($view)) {
            continue; // Skip jika view belum ada
        }
        
        $query = "REFRESH MATERIALIZED VIEW $view";
        $result = @pg_query($koneksi, $query);
        if (!$result) {
            $success = false;
        }
    }
    
    return $success;
}

?>

