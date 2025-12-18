<?php
// --- 1. SETUP & KONEKSI ---
// Pastikan path ke file koneksi Anda sudah benar
require_once __DIR__ . '/../../config/koneksi.php'; 

// --- DATABASE RETRIEVAL LOGIC ---
// Fungsi untuk mengambil konten berdasarkan Page Key
function ambil_konten($koneksi, $key) {
    // Cek koneksi
    if (!$koneksi) {
        return ['title' => 'Error', 'content' => 'Koneksi database gagal.'];
    }
    
    // Menggunakan pg_query_params untuk keamanan
    $query = "SELECT title, content FROM konten_page WHERE page_key = $1";
    $result = pg_query_params($koneksi, $query, array($key));
    
    if ($result && pg_num_rows($result) > 0) {
        return pg_fetch_assoc($result);
    }
    // Fallback jika data tidak ditemukan di database
    return ['title' => 'TBA', 'content' => 'Konten ' . htmlspecialchars($key) . ' belum diatur di Admin Panel.'];
}

// Ambil data Visi
$visi_data = ambil_konten($koneksi, 'visi_deskripsi');

// AMBIL SEMUA ITEM MISI (sebagai daftar/list, karena menggunakan misi_item_%)
$misi_items = [];
// Mengambil semua key yang diawali 'misi_item_' dan mengurutkannya
$query_misi = "SELECT title, content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC";
$result_misi = pg_query($koneksi, $query_misi);

if ($result_misi) {
    while ($row = pg_fetch_assoc($result_misi)) {
        $misi_items[] = $row;
    }
}
?>

<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/visi-misi.css" />

<section class="pt-150 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-30">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Visi &amp; <span>Misi</span></h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Arah dan komitmen Laboratorium Software Engineering</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="visi-misi-content" class="pt-100 pb-70">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                
                <div class="vision-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="card-header-custom">
                        <div class="card-icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border-radius: 12px; display: flex !important; align-items: center !important; justify-content: center !important; color: #ffffff !important; font-size: 24px !important; margin-right: 20px !important; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3) !important; flex-shrink: 0 !important;">
                            <i class="lni lni-rocket" style="font-size: 24px !important; color: #ffffff !important; display: inline-block !important; line-height: 1 !important; font-family: 'LineIcons' !important;" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title-custom"><?php echo empty($visi_data['title']) ? 'Visi' : htmlspecialchars($visi_data['title']); ?></h3>
                    </div>
                    <p class="vision-text">
                        <?php 
                            echo nl2br(htmlspecialchars($visi_data['content'])); 
                        ?>
                    </p>
                </div>

                <div class="mission-card wow fadeInUp" data-wow-delay=".4s">
                    <div class="card-header-custom">
                        <div class="card-icon" style="width: 60px; height: 60px; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border-radius: 12px; display: flex !important; align-items: center !important; justify-content: center !important; color: #ffffff !important; font-size: 24px !important; margin-right: 20px !important; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3) !important; flex-shrink: 0 !important;">
                            <i class="lni lni-target" style="font-size: 24px !important; color: #ffffff !important; display: inline-block !important; line-height: 1 !important; font-family: 'LineIcons' !important;" aria-hidden="true"></i>
                        </div>
                        <h3 class="card-title-custom">Misi Kami</h3>
                    </div>
                    
                    <?php if (!empty($misi_items)): ?>
                        <ul class="mission-list">
                            <?php foreach ($misi_items as $item): ?>
                                <li>
                                    <p class="mission-text">
                                        <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                                    </p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Konten misi belum diatur di Admin Panel.</p>
                    <?php endif; ?>
                </div>
                
            </div> </div> </div> </section>

<?php include __DIR__ . '/../../template/footer.php'; ?>