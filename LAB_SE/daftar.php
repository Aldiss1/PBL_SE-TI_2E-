<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/anti_injection.php';
require_once __DIR__ . '/fungsi/pesan_kilat.php';

session_start();

// Proses pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = clean_input($_POST['name']);
    $nim = clean_input($_POST['nim']);
    $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
    $jurusan = clean_input($_POST['jurusan']);

    // Validasi sederhana sisi server
    if ($nama === '' || $nim === '' || $jurusan === '' || $email === '') {
        pesan_kilat('Semua field wajib diisi.', 'error');
    } else {
        // Validasi format email sederhana
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            pesan_kilat('Format email tidak valid.', 'error');
            header('Location: daftar.php');
            exit;
        }

        // Handle upload foto (opsional)
        $foto_path = '';
        if (isset($_FILES['foto']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $allowed_ext = ['jpg','jpeg','png','webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $original_name = $_FILES['foto']['name'];
            $tmp_path = $_FILES['foto']['tmp_name'];
            $size = (int)$_FILES['foto']['size'];

            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) {
                pesan_kilat('Format foto tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.', 'error');
                header('Location: daftar.php');
                exit;
            }
            if ($size > $max_size) {
                pesan_kilat('Ukuran foto maksimal 2MB.', 'error');
                header('Location: daftar.php');
                exit;
            }

            $uploadDir = __DIR__ . '/uploads/pendaftar/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(pathinfo($original_name, PATHINFO_FILENAME)));
            $newName = $safeBase . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            $destPath = $uploadDir . $newName;
            if (@move_uploaded_file($tmp_path, $destPath)) {
                // Path relatif untuk disimpan di DB
                $foto_path = 'uploads/pendaftar/' . $newName;
            }
        }
    
        require_once __DIR__ . '/fungsi/tambah.php';
        if (tambah_data('pendaftaran', ['nama' => $nama, 'nim' => $nim, 'email' => $email, 'jurusan' => $jurusan, 'foto' => $foto_path, 'tanggal_daftar' => date('Y-m-d H:i:s')])) {
            pesan_kilat('Pendaftaran berhasil dikirim!', 'success');
            header('Location: daftar.php');
            exit;
        } else {
            pesan_kilat('Gagal mengirim pendaftaran. Silakan coba lagi.', 'error');
        }
    }
}
?>
<?php include __DIR__ . '/template/header.php'; ?>

<link rel="stylesheet" href="assets/css/daftar.css" />

<section id="daftar" class="contact-section contact-style-6 pt-150 pb-100" style="margin-top: 80px;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="section-title text-center mb-60">
                <h3 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Formulir Pendaftaran</h3>
                <p class="wow fadeInUp" data-wow-delay=".4s">Isi data di bawah ini untuk mendaftar sebagai anggota Laboratorium Software Engineering.</p>
            </div>

            <?php tampilkan_pesan(); ?>

            <div class="contact-form-wrapper wow fadeInUp" data-wow-delay=".6s" style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <form method="POST" action="" enctype="multipart/form-data">
                  <div class="row">
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="name" style="color: #22243e; font-weight: 600; margin-bottom: 8px; display: block;">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="form-input" placeholder="Masukkan Nama Lengkap Anda" required style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s;">
                        <i class="lni lni-user"></i>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="email" style="color: #22243e; font-weight: 600; margin-bottom: 8px; display: block;">Email</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="nama@email.com" required style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s;">
                        <i class="lni lni-envelope"></i>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="nim" style="color: #22243e; font-weight: 600; margin-bottom: 8px; display: block;">NIM</label>
                        <input type="text" id="nim" name="nim" class="form-input" placeholder="Masukkan NIM Anda" required style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s;">
                        <i class="lni lni-graduation"></i> 
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="jurusan" style="color: #22243e; font-weight: 600; margin-bottom: 8px; display: block;">Jurusan</label>
                        <input type="text" id="jurusan" name="jurusan" class="form-input" placeholder="cth: JTI, Teknik Elektro, dll." required style="width: 100%; padding: 12px 15px 12px 45px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1rem; transition: all 0.3s;">
                        <i class="lni lni-apartment"></i>
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="single-input">
                        <label for="foto" style="color: #22243e; font-weight: 600; margin-bottom: 8px; display: block;">Foto (opsional)</label>
                        <input type="file" id="foto" name="foto" class="form-input" accept=".jpg,.jpeg,.png,.webp" style="width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 0.95rem; background: #fff;">
                      </div>
                    </div>
                    <div class="col-md-12">
                      <div class="form-button text-center" style="margin-top: 30px;">
                        <button type="submit" class="button radius-10" style="background: #2F80ED; color: #fff; border: none; padding: 12px 38px; font-size: 1.09rem; font-weight: 600; box-shadow: 0 2px 8px rgba(47,128,237,.07); transition: all 0.3s;">Kirim Pendaftaran</button>
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

