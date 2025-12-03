<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';

// Load materialized views functions jika tersedia
if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
}

// Inisialisasi variabel
$pendaftar = [];
$total_records = 0;
$total_pages = 1;
$total_pendaftar = 0;
$current_page = 1;
$per_page = 12;
$offset = 0;
$search_query = '';

// Pastikan koneksi database tersedia
if (!$koneksi) {
    // Jika tidak ada koneksi, tetap tampilkan halaman dengan data kosong
    $search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';
    $per_page = 12;
    $current_page = 1;
    $offset = 0;
} else {
    // Filter berdasarkan status
    $status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

    // Pencarian
    $search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

    // Pagination
    $per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 12;
    if (!in_array($per_page, [12, 24, 36, 48])) {
        $per_page = 12;
    }
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($current_page - 1) * $per_page;

    // Build WHERE conditions
    $where_conditions = [];

    // Tampilkan semua pendaftar (tidak filter status)
    // Jika ingin hanya yang diterima, uncomment baris di bawah:
    // $where_conditions[] = "p.status = 'accepted'";

    // Pencarian (nama, NIM, email)
    if (!empty($search_query)) {
        $search_escaped = pg_escape_string($koneksi, $search_query);
        $where_conditions[] = "(p.nama ILIKE '%$search_escaped%' OR p.nim ILIKE '%$search_escaped%' OR p.email ILIKE '%$search_escaped%')";
    }

    // Build query
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // OPTIMASI: Gunakan materialized view untuk total count jika tidak ada filter
    // Materialized view lebih cepat untuk COUNT tanpa filter
    if (empty($where_conditions) && function_exists('get_stats_complete')) {
        $stats_mv = get_stats_complete();
        if ($stats_mv && isset($stats_mv['total_pendaftar'])) {
            $total_records = (int)$stats_mv['total_pendaftar'];
        }
    }
    
    // Jika materialized view tidak tersedia atau ada filter, query langsung
    if ($total_records === 0) {
        try {
            $count_query = "SELECT COUNT(*) as total FROM pendaftaran p $where_clause";
            $count_result = @pg_query($koneksi, $count_query);
            if ($count_result && !pg_last_error($koneksi)) {
                $count_row = pg_fetch_assoc($count_result);
                if ($count_row) {
                    $total_records = (int)$count_row['total'];
                }
            }
        } catch (Exception $e) {
            $total_records = 0;
        }
    }

    // Query untuk data dengan pagination - dengan error handling
    // Catatan: Materialized view mv_pendaftar_terbaru_complete hanya 20 data dan tidak ada foto/minat
    // Jadi tetap query langsung untuk mendapatkan semua kolom yang dibutuhkan
    try {
        $query = "SELECT p.* FROM pendaftaran p $where_clause ORDER BY p.tanggal_daftar DESC LIMIT $per_page OFFSET $offset";
        $result = @pg_query($koneksi, $query);
        if ($result && !pg_last_error($koneksi) && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $pendaftar[] = $row;
            }
        }
    } catch (Exception $e) {
        $pendaftar = [];
    }

    // Hitung total pages untuk pagination
    $total_pages = $total_records > 0 ? (int)ceil($total_records / $per_page) : 1;
}

// Pastikan semua variabel pagination adalah integer
$current_page = (int)$current_page;
$total_pages = (int)$total_pages;
$per_page = (int)$per_page;
$offset = (int)$offset;

// Ambil total pendaftar - gunakan materialized view jika tersedia (lebih cepat!)
if ($koneksi) {
    // Coba ambil dari materialized view dulu
    if (function_exists('get_stats_complete')) {
        $stats_mv = get_stats_complete();
        if ($stats_mv && isset($stats_mv['total_pendaftar'])) {
            $total_pendaftar = (int)$stats_mv['total_pendaftar'];
        }
    }
    
    // Fallback: Query langsung jika materialized view tidak tersedia atau gagal
    if ($total_pendaftar === 0) {
        try {
            $query_total = "SELECT COUNT(*) as total FROM pendaftaran";
            $result_total = @pg_query($koneksi, $query_total);
            if ($result_total && !pg_last_error($koneksi)) {
                $row_total = pg_fetch_assoc($result_total);
                if ($row_total) {
                    $total_pendaftar = (int)$row_total['total'];
                }
            }
        } catch (Exception $e) {
            $total_pendaftar = 0;
        }
    }
}
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<script>
// FORCE HIDE PRELOADER - JANGAN TUNGGU APAPUN
(function() {
    'use strict';
    function forceHide() {
        var preloader = document.querySelector('.preloader');
        if (preloader) {
            preloader.style.cssText = 'display: none !important; opacity: 0 !important; visibility: hidden !important; z-index: -1 !important;';
            try {
                preloader.remove();
            } catch(e) {
                if (preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
            }
        }
    }
    forceHide();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceHide);
    }
    setTimeout(forceHide, 10);
    setTimeout(forceHide, 50);
    setTimeout(forceHide, 100);
    window.addEventListener('load', forceHide);
})();
</script>

<style>
/* Pendaftar Card Styling */
.pendaftar-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 30px 25px;
    box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    height: 100%;
    display: flex;
    flex-direction: column;
    border: 1px solid #f0f0f0;
    position: relative;
    overflow: hidden;
}

.pendaftar-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #2d5aa0 0%, #4a90e2 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.pendaftar-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 30px rgba(45, 90, 160, 0.15);
    border-color: #2d5aa0;
}

.pendaftar-card:hover::before {
    transform: scaleX(1);
}

.pendaftar-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 20px;
    border: 4px solid #e8f0f8;
    box-shadow: 0 4px 15px rgba(45, 90, 160, 0.1);
    transition: all 0.3s ease;
}

.pendaftar-card:hover .pendaftar-avatar {
    border-color: #2d5aa0;
    box-shadow: 0 6px 20px rgba(45, 90, 160, 0.2);
    transform: scale(1.05);
}

.pendaftar-initial {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2d5aa0 0%, #4a90e2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 700;
    margin: 0 auto 20px;
    border: 4px solid #e8f0f8;
    box-shadow: 0 4px 15px rgba(45, 90, 160, 0.1);
    transition: all 0.3s ease;
}

.pendaftar-card:hover .pendaftar-initial {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 6px 20px rgba(45, 90, 160, 0.2);
}

.pendaftar-name {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 12px;
    text-align: center;
    line-height: 1.4;
}

.pendaftar-nim {
    background: linear-gradient(135deg, #f0f4f8 0%, #e8f0f8 100%);
    color: #2d5aa0;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
    margin: 0 auto 15px;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
    border: 1px solid #d0e0f0;
}

.pendaftar-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
}

.pendaftar-info-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14px;
    color: #555;
    line-height: 1.6;
}

.pendaftar-info-item i {
    color: #2d5aa0;
    width: 20px;
    margin-top: 2px;
    flex-shrink: 0;
}

.pendaftar-minat {
    background: linear-gradient(135deg, #f8f9fa 0%, #f0f4f8 100%);
    padding: 12px 15px;
    border-radius: 12px;
    font-size: 13px;
    color: #444;
    margin-top: 10px;
    border-left: 4px solid #2d5aa0;
    line-height: 1.6;
}

.pendaftar-minat strong {
    color: #2d5aa0;
    display: block;
    margin-bottom: 5px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pendaftar-date {
    text-align: center;
    font-size: 12px;
    color: #888;
    margin-top: auto;
    padding-top: 15px;
    border-top: 2px solid #f0f0f0;
    font-weight: 500;
}

.pendaftar-date i {
    margin-right: 5px;
    color: #2d5aa0;
}

/* Stats Card Styling */
.stats-card {
    background: linear-gradient(135deg, #2d5aa0 0%, #4a90e2 100%);
    color: white;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 8px 30px rgba(45, 90, 160, 0.2);
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px rgba(45, 90, 160, 0.3);
}

.stats-label {
    font-size: 16px;
    font-weight: 600;
    opacity: 0.95;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
    position: relative;
    z-index: 1;
}

.stats-number {
    font-size: 48px;
    font-weight: 800;
    margin: 0;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Search Box Styling */
.search-box {
    background: #ffffff;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    margin-bottom: 40px;
    border: 1px solid #f0f0f0;
}

.search-box input[type="text"] {
    width: 100%;
    padding: 15px 25px;
    border: 2px solid #e8e8e8;
    border-radius: 50px;
    font-size: 15px;
    transition: all 0.3s ease;
    background: #fafafa;
}

.search-box input[type="text"]:focus {
    outline: none;
    border-color: #2d5aa0;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(45, 90, 160, 0.1);
}

.search-box .button {
    padding: 15px 35px;
    font-weight: 600;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.search-box .button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(45, 90, 160, 0.2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 80px 30px;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 2px dashed #e0e0e0;
}

.empty-state-icon {
    font-size: 80px;
    color: #d0d0d0;
    margin-bottom: 25px;
    display: block;
}

.empty-state h4 {
    color: #666;
    margin-bottom: 15px;
    font-weight: 600;
}

.empty-state p {
    color: #999;
    line-height: 1.8;
}

/* Section Background */
.pendaftar-section {
    background: linear-gradient(180deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 80vh;
}

/* Responsive */
@media (max-width: 768px) {
    .pendaftar-card {
        padding: 25px 20px;
    }
    
    .pendaftar-avatar,
    .pendaftar-initial {
        width: 80px;
        height: 80px;
        font-size: 28px;
    }
    
    .stats-number {
        font-size: 36px;
    }
    
    .search-box {
        padding: 25px 20px;
    }
}
</style>

<!-- ========================= Pendaftar List Section ========================= -->
<section class="feature-section feature-style-2 pt-150 pb-100 pendaftar-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="section-title text-center mb-60">
                    <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Daftar Pendaftar</h3>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Daftar mahasiswa yang telah mendaftar ke Laboratorium Software Engineering</p>
                </div>
            </div>
        </div>

        <!-- Statistik Total Pendaftar -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="stats-card wow fadeInUp" data-wow-delay=".2s" style="max-width: 400px; margin: 0 auto;">
                    <div class="stats-label">Total Pendaftar</div>
                    <div class="stats-number"><?php echo number_format($total_pendaftar, 0, ',', '.'); ?></div>
                </div>
            </div>
        </div>

        <!-- Pencarian -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="search-box wow fadeInUp" data-wow-delay=".2s">
                    <form method="GET" action="" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 250px;">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" 
                                   placeholder="Cari nama, NIM, atau email..." 
                                   style="width: 100%; padding: 12px 20px; border: 2px solid #e0e0e0; border-radius: 50px; font-size: 14px; transition: all 0.3s;">
                            <style>
                                input[type="text"]:focus {
                                    outline: none;
                                    border-color: #2d5aa0;
                                    box-shadow: 0 0 0 3px rgba(45, 90, 160, 0.1);
                                }
                            </style>
                        </div>
                        <div>
                            <button type="submit" class="button radius-10" style="padding: 12px 30px;">
                                <i class="lni lni-search"></i> Cari
                            </button>
                            <?php if (!empty($search_query)): ?>
                            <a href="<?php echo LABSE_BASE_PATH; ?>/pages/pendaftaran/pendaftar-list.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
                                <i class="lni lni-reload"></i> Reset
                            </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Grid Pendaftar -->
        <div class="row">
            <?php if (count($pendaftar) > 0): ?>
                <?php foreach ($pendaftar as $p): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="pendaftar-card wow fadeInUp" data-wow-delay=".3s">
                        <div style="text-align: center;">
                            <?php if (!empty($p['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($p['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($p['nama']); ?>" 
                                     class="pendaftar-avatar">
                            <?php else: ?>
                                <?php $initial = strtoupper(substr($p['nama'], 0, 1)); ?>
                                <div class="pendaftar-initial">
                                    <?php echo $initial; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="pendaftar-name">
                                <?php echo htmlspecialchars($p['nama']); ?>
                            </div>
                            
                            <div style="text-align: center; margin-bottom: 15px;">
                                <span class="pendaftar-nim">
                                    <?php echo htmlspecialchars($p['nim']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="pendaftar-info">
                            <?php if (isset($p['email']) && !empty($p['email'])): ?>
                            <div class="pendaftar-info-item">
                                <i class="lni lni-envelope"></i>
                                <span style="word-break: break-word;"><?php echo htmlspecialchars($p['email']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($p['minat'])): ?>
                            <div class="pendaftar-minat">
                                <strong><i class="lni lni-bulb"></i> Minat:</strong><br>
                                <?php echo htmlspecialchars(substr($p['minat'], 0, 100)); ?>
                                <?php echo strlen($p['minat']) > 100 ? '...' : ''; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pendaftar-date">
                            <i class="lni lni-calendar"></i>
                            <?php 
                            if (!empty($p['tanggal_daftar'])) {
                                try {
                                    $date = new DateTime($p['tanggal_daftar'], new DateTimeZone('UTC'));
                                    $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
                                    echo $date->format('d M Y');
                                } catch (Exception $e) {
                                    $tanggal = strtotime($p['tanggal_daftar']);
                                    if ($tanggal !== false) {
                                        $tanggal_wib = $tanggal + (7 * 3600);
                                        echo date('d M Y', $tanggal_wib);
                                    } else {
                                        echo htmlspecialchars($p['tanggal_daftar']);
                                    }
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <div class="empty-state wow fadeInUp" data-wow-delay=".3s">
                        <i class="lni lni-inbox empty-state-icon"></i>
                        <h4 style="color: #999; margin-bottom: 10px;">
                            <?php if (!empty($search_query)): ?>
                                Tidak ada anggota yang ditemukan dengan kata kunci "<?php echo htmlspecialchars($search_query); ?>"
                            <?php else: ?>
                                Belum ada anggota yang diterima
                            <?php endif; ?>
                        </h4>
                        <p style="color: #999;">
                            <?php if (!empty($search_query)): ?>
                                Coba gunakan kata kunci lain atau <a href="<?php echo LABSE_BASE_PATH; ?>/pages/pendaftaran/pendaftar-list.php">lihat semua anggota</a>
                            <?php else: ?>
                                Mahasiswa yang diterima akan muncul di sini setelah proses seleksi selesai.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_records > 0): ?>
        <div class="row mt-30">
            <div class="col-lg-6">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; color: #666; font-size: 14px;">
                    Menampilkan <strong><?php echo $offset + 1; ?></strong> - <strong><?php echo min($offset + $per_page, $total_records); ?></strong> dari <strong><?php echo number_format($total_records); ?></strong> anggota
                </div>
            </div>
            <div class="col-lg-6">
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <?php
                    // Build query string untuk pagination
                    $query_params = [];
                    if (!empty($search_query)) $query_params[] = 'search=' . urlencode($search_query);
                    if ($per_page != 12) $query_params[] = 'per_page=' . $per_page;
                    $query_string = !empty($query_params) ? '?' . implode('&', $query_params) : '';
                    $page_separator = !empty($query_string) ? '&' : '?';
                    ?>
                    
                    <!-- Previous Button -->
                    <?php if ($current_page > 1): ?>
                        <a href="<?php echo $query_string . $page_separator; ?>page=<?php echo ($current_page - 1); ?>" class="button button-sm radius-10 border-button">
                            <i class="lni lni-chevron-left"></i> Sebelumnya
                        </a>
                    <?php else: ?>
                        <button type="button" class="button button-sm radius-10 border-button" disabled>
                            <i class="lni lni-chevron-left"></i> Sebelumnya
                        </button>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    // Pastikan semua variabel adalah integer
                    $current_page = (int)$current_page;
                    $total_pages = (int)$total_pages;
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1): ?>
                        <a href="<?php echo $query_string . $page_separator; ?>page=1" class="button button-sm radius-10 border-button">1</a>
                        <?php if ($start_page > 2): ?>
                            <span style="padding: 8px 12px; color: #999;">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <button type="button" class="button button-sm radius-10" style="min-width: 40px; background: #2d5aa0; color: white;">
                                <?php echo $i; ?>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo $query_string . $page_separator; ?>page=<?php echo $i; ?>" class="button button-sm radius-10 border-button" style="min-width: 40px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span style="padding: 8px 12px; color: #999;">...</span>
                        <?php endif; ?>
                        <a href="<?php echo $query_string . $page_separator; ?>page=<?php echo $total_pages; ?>" class="button button-sm radius-10 border-button">
                            <?php echo $total_pages; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo $query_string . $page_separator; ?>page=<?php echo ($current_page + 1); ?>" class="button button-sm radius-10 border-button">
                            Selanjutnya <i class="lni lni-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <button type="button" class="button button-sm radius-10 border-button" disabled>
                            Selanjutnya <i class="lni lni-chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Link ke Form Pendaftaran -->
        <div class="row mt-40">
            <div class="col-lg-12 text-center">
                <a href="<?php echo LABSE_BASE_PATH; ?>/pages/pendaftaran/daftar.php" class="button radius-10" style="padding: 15px 40px; font-size: 16px;">
                    <i class="lni lni-plus"></i> Daftar Menjadi Anggota
                </a>
            </div>
        </div>
    </div>
</section>
<!-- ========================= Pendaftar List Section End ========================= -->

<script>
// Force hide preloader immediately
(function() {
    function forceHidePreloader() {
        var preloader = document.querySelector('.preloader');
        if (preloader) {
            preloader.style.display = 'none';
            preloader.style.opacity = '0';
            preloader.style.visibility = 'hidden';
            preloader.remove();
        }
    }
    
    // Hide immediately
    forceHidePreloader();
    
    // Hide saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', forceHidePreloader);
    } else {
        forceHidePreloader();
    }
    
    // Multiple backups
    setTimeout(forceHidePreloader, 100);
    setTimeout(forceHidePreloader, 300);
    setTimeout(forceHidePreloader, 500);
    
    window.addEventListener('load', forceHidePreloader);
})();
</script>

<?php include __DIR__ . '/../../template/footer.php'; ?>
