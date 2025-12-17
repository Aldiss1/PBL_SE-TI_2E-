<?php
// module/tentang/index.php

// --- 1. SETUP & KONEKSI ---
require_once __DIR__ . '/../../config/koneksi.php'; 

// --- DATABASE RETRIEVAL LOGIC ---

// Fungsi untuk mengambil konten berdasarkan Page Key (sama seperti di file Visi Misi)
function ambil_konten($koneksi, $key) {
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

// Ambil data konten 'Tentang Kami'
$tentang_kami_data = ambil_konten($koneksi, 'tentang_kami');

// Ambil data 4 kartu feature
$tentang_kami_features = [
    'rekayasa' => ambil_konten($koneksi, 'tentang_kami_feature_rekayasa'),
    'publikasi' => ambil_konten($koneksi, 'tentang_kami_feature_publikasi'),
    'kemitraan' => ambil_konten($koneksi, 'tentang_kami_feature_kemitraan'),
    'pembinaan' => ambil_konten($koneksi, 'tentang_kami_feature_pembinaan')
];

?>

<?php 
// Deteksi halaman tentang-kami untuk inject CSS khusus di head
$is_tentang_kami_page = true;
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<section class="pt-150 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-30">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tentang <span>Kami</span></h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Mengenal Laboratorium Software Engineering lebih dekat</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="tentang-kami-content" class="pt-100 pb-70">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        
        <div class="about-card wow fadeInUp" data-wow-delay=".2s">
          <div class="about-header">
            <div class="about-icon">
              <i class="lni lni-information"></i>
            </div>
            <h3 class="about-title">
                <?php 
                // Menggunakan Judul dari database, fallback ke 'Tentang Kami' jika kosong
                echo empty($tentang_kami_data['title']) ? 'Tentang Kami' : htmlspecialchars($tentang_kami_data['title']); 
                ?>
            </h3>
          </div>
          
          <p class="about-text">
            <?php 
                // Teks Utama Ditarik dari Database
                echo nl2br(htmlspecialchars($tentang_kami_data['content'])); 
            ?>
          </p>
          
          <div class="about-features">
            <div class="feature-item wow fadeInUp" data-wow-delay=".3s">
              <div class="feature-icon">
                <i class="lni lni-code-alt"></i>
              </div>
              <div class="feature-content">
                <h5><?php echo $tentang_kami_features['rekayasa']['title'] ?? 'Rekayasa Perangkat Lunak'; ?></h5>
                <p><?php echo $tentang_kami_features['rekayasa']['content'] ?? 'Fokus pada pengembangan perangkat lunak berkualitas tinggi dengan metodologi terkini'; ?></p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".4s">
              <div class="feature-icon">
                <i class="lni lni-book"></i>
              </div>
              <div class="feature-content">
                <h5><?php echo $tentang_kami_features['publikasi']['title'] ?? 'Publikasi Ilmiah'; ?></h5>
                <p><?php echo $tentang_kami_features['publikasi']['content'] ?? 'Mendorong penelitian dan publikasi hasil riset di bidang software engineering'; ?></p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".5s">
              <div class="feature-icon">
                <i class="lni lni-handshake"></i>
              </div>
              <div class="feature-content">
                <h5><?php echo $tentang_kami_features['kemitraan']['title'] ?? 'Kemitraan Industri'; ?></h5>
                <p><?php echo $tentang_kami_features['kemitraan']['content'] ?? 'Membangun kerjasama dengan industri untuk implementasi teknologi nyata'; ?></p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".6s">
              <div class="feature-icon">
                <i class="lni lni-graduation"></i>
              </div>
              <div class="feature-content">
                <h5><?php echo $tentang_kami_features['pembinaan']['title'] ?? 'Pembinaan Kompetensi'; ?></h5>
                <p><?php echo $tentang_kami_features['pembinaan']['content'] ?? 'Membina mahasiswa melalui proyek nyata dan pengembangan skill praktis'; ?></p>
              </div>
            </div>
          </div>
        </div>

      </div>
      
    </div>
  </div>
</section>




<?php include __DIR__ . '/../../template/footer.php'; ?>