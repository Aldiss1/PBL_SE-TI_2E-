<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<style>
  .about-hero {
    background-color: #ffffff;
    padding: 60px 0;
    text-align: center;
    border-bottom: 2px solid #f0f0f0;
    margin-top: 80px;
  }
  
  .about-hero h2 {
    font-size: 2rem;
    color: #2F80ED;
    font-weight: 600;
    margin-bottom: 20px;
  }
  
  .about-hero p {
    color: #585978;
    font-size: 1rem;
  }
  
  .about-content {
    padding: 60px 0;
    background: #f4f7fb;
  }
  
  .about-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    padding: 30px;
    transition: all 0.3s ease;
    border-left: 4px solid #2F80ED;
  }
  
  .about-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }
  
  .about-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
  }
  
  .about-icon {
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
  
  .about-title {
    font-size: 1.6rem;
    font-weight: 600;
    color: #2F80ED;
    margin: 0;
  }
  
  .about-text {
    font-size: 1rem;
    line-height: 1.7;
    color: #515e7b;
    margin: 0 0 30px 0;
    text-align: justify;
  }
  
  .about-features {
    margin-top: 30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }
  
  .feature-item {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }
  
  .feature-item:hover {
    background: #e6eefd;
    border-left-color: #2F80ED;
    transform: translateX(5px);
  }
  
  .feature-icon {
    width: 40px;
    height: 40px;
    background: #2F80ED;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
    margin-right: 15px;
    flex-shrink: 0;
  }
  
  .feature-content h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #22243e;
    margin-bottom: 8px;
  }
  
  .feature-content p {
    font-size: 0.9rem;
    color: #515e7b;
    margin: 0;
    line-height: 1.6;
  }
  
  @media (max-width: 768px) {
    .about-hero h2 {
      font-size: 1.75rem;
    }
    
    .about-card {
      padding: 20px;
    }
    
    .about-icon {
      width: 45px;
      height: 45px;
      font-size: 1.3rem;
    }
    
    .about-title {
      font-size: 1.4rem;
    }
    
    .about-features {
      grid-template-columns: 1fr;
    }
  }
</style>

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

