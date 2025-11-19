<?php
require_once __DIR__ . '/../../config/koneksi.php';
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
        
        <!-- Visi Card -->
        <div class="vision-card wow fadeInUp" data-wow-delay=".2s">
          <div class="card-header-custom">
            <div class="card-icon">
              <i class="lni lni-bullseye"></i>
            </div>
            <h3 class="card-title-custom">Visi</h3>
          </div>
          <p class="vision-text">
            Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.
          </p>
        </div>

        <!-- Misi Card -->
        <div class="mission-card wow fadeInUp" data-wow-delay=".4s">
          <div class="card-header-custom">
            <div class="card-icon">
              <i class="lni lni-target"></i>
            </div>
            <h3 class="card-title-custom">Misi</h3>
          </div>
          <ul class="mission-list">
            <li class="mission-item">
              <div class="mission-number">1</div>
              <p class="mission-text">
                Melakukan penelitian berkualitas di bidang perangkat lunak yang memberikan kontribusi nyata bagi industri dan akademisi.
              </p>
            </li>
            <li class="mission-item">
              <div class="mission-number">2</div>
              <p class="mission-text">
                Mendorong kolaborasi akademik dan industri untuk menghasilkan inovasi teknologi yang applicable.
              </p>
            </li>
            <li class="mission-item">
              <div class="mission-number">3</div>
              <p class="mission-text">
                Membina mahasiswa melalui proyek penelitian yang berkualitas dan membentuk karakter peneliti yang handal.
              </p>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>

