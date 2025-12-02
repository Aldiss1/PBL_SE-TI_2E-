<?php
// --- 1. SETUP & KONEKSI ---
require_once __DIR__ . '/../../config/koneksi.php'; 

/** @var PgSql\Connection $koneksi */

// Cek koneksi
if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal dimuat. Cek file koneksi.php Anda.");
}

// --- DATABASE RETRIEVAL LOGIC ---

/**
 * Fungsi untuk mengambil semua periode Roadmap
 * @param PgSql\Connection $koneksi Koneksi PostgreSQL
 * @return array Array berisi data periode
 */


function ambil_periode_roadmap($koneksi) {
    // Ambil semua periode, diurutkan berdasarkan kolom 'urutan'
    $query = "SELECT id, tahun_periode, judul_fase FROM roadmap_periode ORDER BY urutan ASC";
    $result = pg_query($koneksi, $query);
    
    $periode = [];
    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $periode[] = $row;
        }
    }
    return $periode;
}

/**
 * Fungsi untuk mengambil item tugas Roadmap berdasarkan ID Periode
 * @param PgSql\Connection $koneksi Koneksi PostgreSQL
 * @param int $id_periode ID dari tabel roadmap_periode
 * @return array Array berisi item tugas
 */
function ambil_item_roadmap($koneksi, $id_periode) {
    // TAMBAHKAN PENGECEKAN KONEKSI DI SINI
    if (!$koneksi) {
        return [];
    }

    // Ambil semua item tugas yang sesuai dengan id_periode
    $query = "SELECT deskripsi_tugas FROM roadmap_item WHERE id_periode = $1 ORDER BY urutan ASC";
    $result = pg_query_params($koneksi, $query, array($id_periode));
    
    $items = [];
    // Pastikan result bukan FALSE sebelum fetch
    if ($result !== false) { 
        while ($row = pg_fetch_assoc($result)) {
            $items[] = $row;
        }
    } else {
        // Log error jika query gagal
        error_log("PostgreSQL Query Error: " . pg_last_error($koneksi));
    }
    return $items;
}

// 1. Ambil semua data periode utama
$semua_periode = ambil_periode_roadmap($koneksi);

?>

<?php include __DIR__ . '/../../template/header.php'; ?>

<section class="pt-150 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-30">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s"><span>Roadmap</span></h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Perjalanan dan Rencana Pengembangan Laboratorium Software Engineering</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="roadmap-content" class="pt-100 pb-70">
    <div class="container">
    <div class="row">
        <div class="col-lg-12">
            
            <?php if (empty($semua_periode)): ?>
                <p class="text-center alert alert-warning">Roadmap belum diatur di Admin Panel.</p>
            <?php endif; ?>
            
            <?php foreach ($semua_periode as $periode): ?>
                
                <?php 
                    // Ambil item tugas untuk periode saat ini
                    $items_tugas = ambil_item_roadmap($koneksi, $periode['id']);

                    // Pisahkan tahun dan kuartal/periode dari tahun_periode
                    // Contoh: '2024 Q1 - Q4' -> $tahun='2024', $kuartal='Q1 - Q4'
                    $bagian_periode = explode(' ', $periode['tahun_periode'], 2);
                    $tahun = htmlspecialchars($bagian_periode[0]);
                    $kuartal = isset($bagian_periode[1]) ? htmlspecialchars($bagian_periode[1]) : '';
                ?>
                
                <div class="roadmap-card">
                    <div class="roadmap-header">
                        <h3><?php echo $tahun; ?></h3>
                        <div class="roadmap-year">
                            <h4><?php echo $kuartal; ?></h4>
                        </div>
                    </div>
                    <div class="roadmap-content">
                        <h5><?php echo htmlspecialchars($periode['judul_fase']); ?></h5>
                        
                        <?php if (!empty($items_tugas)): ?>
                        <ul>
                            <?php foreach ($items_tugas as $item): ?>
                                <li>
                                    <i class="lni lni-checkmark-circle"></i>
                                    <?php echo htmlspecialchars($item['deskripsi_tugas']); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                            <p>Tugas untuk fase ini belum diatur.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>