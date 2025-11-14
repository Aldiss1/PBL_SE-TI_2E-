<?php
require_once __DIR__ . '/config/koneksi.php';
?>
<?php include __DIR__ . '/template/header.php'; ?>

<link rel="stylesheet" href="assets/css/roadmap.css" />

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

