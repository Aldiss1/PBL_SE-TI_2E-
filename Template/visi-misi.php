<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<style>
  .visi-misi-hero {
    background-color: #ffffff;
    padding: 60px 0;
    text-align: center;
    border-bottom: 2px solid #f0f0f0;
    margin-top: 80px;
  }
  
  .visi-misi-hero h2 {
    font-size: 2rem;
    color: #2F80ED;
    font-weight: 600;
    margin-bottom: 20px;
  }
  
  .visi-misi-hero p {
    color: #585978;
    font-size: 1rem;
  }
  
  .visi-misi-content {
    padding: 60px 0;
    background: #f4f7fb;
  }
  
  .vision-card, .mission-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    padding: 30px;
    transition: all 0.3s ease;
    border-left: 4px solid #2F80ED;
  }
  
  .vision-card:hover, .mission-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }
  
  .mission-card {
    border-left-color: #27AE60;
  }
  
  .card-header-custom {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
  }
  
  .card-icon {
    width: 50px;
    height: 50px;
    background: #2F80ED;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 15px;
  }
  
  .mission-card .card-icon {
    background: #27AE60;
  }
  
  .card-title-custom {
    font-size: 1.6rem;
    font-weight: 600;
    color: #2F80ED;
    margin: 0;
  }
  
  .mission-card .card-title-custom {
    color: #27AE60;
  }
  
  .vision-text {
    font-size: 1rem;
    line-height: 1.7;
    color: #515e7b;
    margin: 0;
  }
  
  .mission-list {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  
  .mission-item {
    display: flex;
    align-items: flex-start;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    transition: all 0.3s ease;
  }
  
  .mission-item:last-child {
    border-bottom: none;
  }
  
  .mission-item:hover {
    padding-left: 10px;
  }
  
  .mission-number {
    width: 30px;
    height: 30px;
    background: #27AE60;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    margin-right: 15px;
    flex-shrink: 0;
  }
  
  .mission-text {
    font-size: 1rem;
    line-height: 1.7;
    color: #515e7b;
    flex: 1;
    margin: 0;
  }
  
  @media (max-width: 768px) {
    .visi-misi-hero h2 {
      font-size: 1.75rem;
    }
    
    .vision-card, .mission-card {
      padding: 20px;
    }
    
    .card-icon {
      width: 45px;
      height: 45px;
      font-size: 1.3rem;
    }
    
    .card-title-custom {
      font-size: 1.4rem;
    }
  }
</style>

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

<?php include __DIR__ . '/template/footer.php'; ?>

