<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/anti_injection.php';
require_once __DIR__ . '/fungsi/pesan_kilat.php';

session_start();

// Proses pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['name']);
    $nim = clean_input($_POST['nim']);
    $jurusan = clean_input($_POST['jurusan']);
    
    require_once __DIR__ . '/fungsi/tambah.php';
    if (tambah_data('pendaftaran', ['nama' => $nama, 'nim' => $nim, 'jurusan' => $jurusan, 'tanggal_daftar' => date('Y-m-d H:i:s')])) {
        pesan_kilat('Pendaftaran berhasil dikirim!', 'success');
        header('Location: daftar.php');
        exit;
    } else {
        pesan_kilat('Gagal mengirim pendaftaran. Silakan coba lagi.', 'error');
    }
}
?>
<?php include __DIR__ . '/template/header.php'; ?>

<section id="daftar" class="contact-section contact-style-6 pt-150 pb-100" style="margin-top: 80px;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="section-title text-center mb-60">
                <h2 class="mb-25 wow fadeInUp" data-wow-delay=".2s">Formulir Pendaftaran</h2>
                <p class="wow fadeInUp" data-wow-delay=".4s">Isi data di bawah ini untuk mendaftar sebagai anggota Laboratorium Software Engineering.</p>
            </div>

            <?php tampilkan_pesan(); ?>

            <div class="contact-form-wrapper wow fadeInUp" data-wow-delay=".6s">
                <form method="POST" action="">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan Nama Lengkap Anda" required>
                        <i class="lni lni-user"></i>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="nim">NIM</label>
                        <input type="text" id="nim" name="nim" class="form-input" placeholder="Masukkan NIM Anda" required>
                        <i class="lni lni-graduation"></i> 
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="jurusan">Jurusan</label>
                        <input type="text" id="jurusan" name="jurusan" class="form-input" placeholder="cth: JTI, Teknik Elektro, dll." required>
                        <i class="lni lni-apartment"></i>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-button text-center">
                        <button type="submit" class="button radius-10">Kirim Pendaftaran</button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>

        </div>
      </div>
    </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

