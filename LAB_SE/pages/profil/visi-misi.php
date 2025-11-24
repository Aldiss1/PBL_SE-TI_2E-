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

// Ambil data yang dibutuhkan (Asumsi Page Key Anda adalah 'visi_kami' dan 'misi_kami')
$visi_data = ambil_konten($koneksi, 'visi_kami');
$misi_data = ambil_konten($koneksi, 'misi_kami');

// Tutup koneksi setelah selesai mengambil data (Opsional: bisa juga ditutup di akhir file)
// pg_close($koneksi); 
?>

<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/visi-misi.css" />

<section class="visi-misi-hero">
  <div class="container">
    <h2 class="wow fadeInUp" data-wow-delay=".2s">Visi &amp; Misi</h2>
    <p class="wow fadeInUp" data-wow-delay=".3s">Arah dan komitmen Laboratorium Software Engineering</p>
  </div>
</section>

<section class="visi-misi-content">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        
                <div class="vision-card wow fadeInUp" data-wow-delay=".2s">
          <div class="card-header-custom">
            <div class="card-icon">
              <i class="lni lni-bullseye"></i>
            </div>
                        <h3 class="card-title-custom"><?php echo empty($visi_data['title']) ? 'Visi' : htmlspecialchars($visi_data['title']); ?></h3>
          </div>
          <p class="vision-text">
            <?php 
                // Mengganti teks statis dengan konten dari database
                // nl2br() digunakan agar baris baru/enter dari admin panel tampil sebagai <br> di HTML
                echo nl2br(htmlspecialchars($visi_data['content'])); 
            ?>
          </p>
        </div>

                <div class="mission-card wow fadeInUp" data-wow-delay=".4s">
          <div class="card-header-custom">
            <div class="card-icon">
              <i class="lni lni-target"></i>
            </div>
                        <h3 class="card-title-custom"><?php echo empty($misi_data['title']) ? 'Misi' : htmlspecialchars($misi_data['title']); ?></h3>
          </div>
          
                      <p class="mission-text">
            <?php 
                // Mengganti teks statis dengan konten dari database
                echo nl2br(htmlspecialchars($misi_data['content'])); 
            ?>
          </p>
            
                    </div>

      </div>
    </div>
  </div>
</section>

<?php 
// Tutup koneksi database di akhir file (opsional tapi disarankan)
if (isset($koneksi) && $koneksi) {
    pg_close($koneksi);
}
?>
<?php include __DIR__ . '/../../template/footer.php'; ?>