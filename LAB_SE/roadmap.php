<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<style>
  body { background-color: #f4f7fb; }
  .hero-section-wrapper-2 {
    background-color: #ffffff;
    padding: 60px 0;
    text-align: center;
    border-bottom: 2px solid #f0f0f0;
    margin-top: 80px;
  }
  .hero-section-wrapper-2 h2 {
    font-size: 2rem;
    color: #2F80ED;
    font-weight: 600;
    margin-bottom: 20px;
  }
  .roadmap-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    margin-bottom: 30px;
    padding: 25px;
    transition: all 0.3s ease;
  }
  .roadmap-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }
  .roadmap-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }
  .roadmap-header h3 {
    font-size: 1.6rem;
    color: #2F80ED;
    font-weight: 600;
    margin: 0;
  }
  .roadmap-year {
    margin-left: 20px;
    color: #999;
    font-size: 1rem;
    font-weight: 500;
  }
  .roadmap-content h5 {
    color: #2F80ED;
    font-size: 1.3rem;
    margin-bottom: 20px;
    font-weight: 500;
  }
  .roadmap-content ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .roadmap-content li {
    font-size: 1rem;
    color: #515e7b;
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
  }
  .roadmap-content li i {
    margin-right: 15px;
    color: #27AE60;
    font-size: 1.2rem;
  }
</style>

<section class="hero-section-wrapper-2">
    <div class="container">
        <h2>Roadmap Laboratorium SE</h2>
        <p>Perjalanan dan Rencana Pengembangan Laboratorium Software Engineering.</p>
    </div>
</section>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-12">
            <div class="roadmap-card">
                <div class="roadmap-header">
                    <h3>2024</h3>
                    <div class="roadmap-year">
                        <h4>Q1 - Q4</h4>
                    </div>
                </div>
                <div class="roadmap-content">
                    <h5>Fase Pendirian & Setup</h5>
                    <ul>
                        <li><i class="lni lni-checkmark-circle"></i>Pembentukan struktur organisasi laboratorium</li>
                        <li><i class="lni lni-checkmark-circle"></i>Pengadaan infrastruktur dan peralatan dasar</li>
                        <li><i class="lni lni-checkmark-circle"></i>Rekrutmen anggota dan asisten laboratorium</li>
                        <li><i class="lni lni-checkmark-circle"></i>Peluncuran website dan sistem informasi lab</li>
                    </ul>
                </div>
            </div>

            <div class="roadmap-card">
                <div class="roadmap-header">
                    <h3>2025</h3>
                    <div class="roadmap-year">
                        <h4>Q1 - Q4</h4>
                    </div>
                </div>
                <div class="roadmap-content">
                    <h5>Fase Pengembangan & Riset</h5>
                    <ul>
                        <li><i class="lni lni-checkmark-circle"></i>Pengembangan proyek penelitian</li>
                        <li><i class="lni lni-checkmark-circle"></i>Kerjasama dengan industri untuk implementasi teknologi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>

