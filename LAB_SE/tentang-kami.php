<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<link rel="stylesheet" href="assets/css/tentang-kami.css" />

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
            <h3 class="about-title">Tentang Kami</h3>
          </div>
          
          <p class="about-text">
            Laboratorium Software Engineering merupakan wadah kolaborasi mahasiswa dan dosen untuk penelitian, pengembangan,
            dan penerapan rekayasa perangkat lunak. Aktivitas kami meliputi pengembangan produk, publikasi ilmiah,
            kemitraan industri, serta pembinaan kompetensi melalui proyek nyata. Kami berkomitmen menghadirkan solusi
            teknologi yang bermanfaat bagi masyarakat dan dunia pendidikan.
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
  </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

