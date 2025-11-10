<?php
$page_title = "Admin Dashboard - Web Profile Laboratorium SE";
require_once __DIR__ . '/../../cek_login.php';
include __DIR__ . '/../../template/admin_header.php';
?>

<section class="pt-150 pb-50" style="background: #f3f3f3; margin-top: 80px;">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-title mb-30">
            <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Admin Dashboard</h2>
            <p class="wow fadeInUp" data-wow-delay=".4s">Selamat Datang, <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?>. Pilih modul yang ingin Anda kelola.</p>
          </div>
        </div>
      </div>
    </div>
</section>

<section id="admin-menu" class="pt-100 pb-70" style="background: #ffffff;">
    <div class="container">
        <div class="row">

            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".2s" style="padding: 40px 30px; text-align: left;">
                    <div class="info">
                        <i class="lni lni-users" style="font-size: 40px; color: #2F80ED; margin-bottom: 20px;"></i>
                        <h5 style="font-weight: 600;">Kelola Anggota</h5>
                        <p>Tambah, edit, atau hapus data anggota laboratorium.</p>
                        <a href="../../module/anggota/index.php" class="button button-sm radius-10 mt-20">Mulai Kelola</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".4s" style="padding: 40px 30px; text-align: left;">
                    <div class="info">
                        <i class="lni lni-briefcase" style="font-size: 40px; color: #2F80ED; margin-bottom: 20px;"></i>
                        <h5 style="font-weight: 600;">Kelola Jabatan</h5>
                        <p>Tambah, edit, atau hapus data jabatan anggota.</p>
                        <a href="../../module/jabatan/index.php" class="button button-sm radius-10 mt-20">Mulai Kelola</a>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6">
                <div class="single-team wow fadeInUp" data-wow-delay=".6s" style="padding: 40px 30px; text-align: left;">
                    <div class="info">
                        <i class="lni lni-list" style="font-size: 40px; color: #2F80ED; margin-bottom: 20px;"></i>
                        <h5 style="font-weight: 600;">Kelola Pendaftar</h5>
                        <p>Lihat data mahasiswa yang mendaftar ke laboratorium.</p>
                        <a href="../../list-pendaftar.php" class="button button-sm radius-10 mt-20">Lihat Daftar</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

