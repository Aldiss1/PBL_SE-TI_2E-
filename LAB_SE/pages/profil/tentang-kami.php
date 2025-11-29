<?php
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

// Tutup koneksi setelah selesai mengambil data
// pg_close($koneksi); 
?>

<?php 
// Deteksi halaman tentang-kami untuk inject CSS khusus di head
$is_tentang_kami_page = true;
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<section class="about-hero">
  <div class="container">
        <h2 class="wow fadeInUp" data-wow-delay=".2s">Tentang Kami</h2>
    <p class="wow fadeInUp" data-wow-delay=".3s">Mengenal Laboratorium Software Engineering lebih dekat</p>
  </div>
</section>

<section class="about-content">
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
                <h5>Rekayasa Perangkat Lunak</h5>
                <p>Fokus pada pengembangan perangkat lunak berkualitas tinggi dengan metodologi terkini</p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".4s">
              <div class="feature-icon">
                <i class="lni lni-book"></i>
              </div>
              <div class="feature-content">
                <h5>Publikasi Ilmiah</h5>
                <p>Mendorong penelitian dan publikasi hasil riset di bidang software engineering</p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".5s">
              <div class="feature-icon">
                <i class="lni lni-handshake"></i>
              </div>
              <div class="feature-content">
                <h5>Kemitraan Industri</h5>
                <p>Membangun kerjasama dengan industri untuk implementasi teknologi nyata</p>
              </div>
            </div>
            
            <div class="feature-item wow fadeInUp" data-wow-delay=".6s">
              <div class="feature-icon">
                <i class="lni lni-graduation"></i>
              </div>
              <div class="feature-content">
                <h5>Pembinaan Kompetensi</h5>
                <p>Membina mahasiswa melalui proyek nyata dan pengembangan skill praktis</p>
              </div>
            </div>
          </div>
        

      </div>
      
    </div>
  </div>
</section>

<?php 
// Tutup koneksi database
if (isset($koneksi) && $koneksi) {
    pg_close($koneksi);
}
?>

<?php include __DIR__ . '/../../template/footer.php'; ?>