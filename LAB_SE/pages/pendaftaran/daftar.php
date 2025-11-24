<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

session_start();

// Proses pendaftaran
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Pastikan koneksi database tersedia
        if (!isset($koneksi) || !$koneksi) {
            throw new Exception('Koneksi database tidak tersedia. Silakan hubungi administrator.');
        }

        $nama = clean_input($_POST['name'] ?? '');
        $nim = clean_input($_POST['nim'] ?? '');
        $email = isset($_POST['email']) ? clean_input($_POST['email']) : '';

        // Validasi input
        if (empty($nama)) {
            throw new Exception('Nama lengkap wajib diisi.');
        }
        if (empty($nim)) {
            throw new Exception('NIM wajib diisi.');
        }
        if (empty($email)) {
            throw new Exception('Email wajib diisi.');
        }

        // Validasi format email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid.');
        }

        // Handle upload foto (opsional)
        $foto_path = null;
        if (isset($_FILES['foto']) && is_uploaded_file($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed_ext = ['jpg','jpeg','png','webp'];
            $max_size = 2 * 1024 * 1024; // 2MB
            $original_name = $_FILES['foto']['name'];
            $tmp_path = $_FILES['foto']['tmp_name'];
            $size = (int)$_FILES['foto']['size'];
            $error_code = $_FILES['foto']['error'];

            if ($error_code !== UPLOAD_ERR_OK) {
                throw new Exception('Error upload foto. Kode error: ' . $error_code);
            }

            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext)) {
                throw new Exception('Format foto tidak didukung. Gunakan JPG, JPEG, PNG, atau WEBP.');
            }
            if ($size > $max_size) {
                throw new Exception('Ukuran foto maksimal 2MB.');
            }

            $uploadDir = __DIR__ . '/../../uploads/pendaftar/';
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0775, true)) {
                    throw new Exception('Gagal membuat folder upload.');
                }
            }
            
            $safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(pathinfo($original_name, PATHINFO_FILENAME)));
            $newName = $safeBase . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            $destPath = $uploadDir . $newName;
            
            if (!@move_uploaded_file($tmp_path, $destPath)) {
                throw new Exception('Gagal mengupload foto. Silakan coba lagi.');
            }
            
            $foto_path = 'uploads/pendaftar/' . $newName;
        }

        // Escape input untuk keamanan
        $nama_escaped = pg_escape_string($koneksi, $nama);
        $nim_escaped = pg_escape_string($koneksi, $nim);
        $email_escaped = pg_escape_string($koneksi, $email);
        
        // Cek apakah kolom jurusan ada di tabel (untuk kompatibilitas)
        $check_jurusan = pg_query($koneksi, "
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
            AND table_name = 'pendaftaran' 
            AND column_name = 'jurusan'
        ");
        $has_jurusan_column = $check_jurusan && pg_num_rows($check_jurusan) > 0;
        
        // Build query
        $columns = "nama, nim, email";
        $values = "'$nama_escaped', '$nim_escaped', '$email_escaped'";
        
        // Jika kolom jurusan masih ada, tambahkan dengan nilai NULL
        if ($has_jurusan_column) {
            $columns .= ", jurusan";
            $values .= ", NULL";
        }
        
        // Tambahkan foto jika ada
        if ($foto_path !== null) {
            $foto_escaped = pg_escape_string($koneksi, $foto_path);
            $columns .= ", foto";
            $values .= ", '$foto_escaped'";
        }
        
        $query = "INSERT INTO pendaftaran ($columns) VALUES ($values)";
        
        // Execute query
        $result = pg_query($koneksi, $query);
        
        if (!$result) {
            $error_detail = pg_last_error($koneksi);
            error_log("Database error: " . $error_detail . " | Query: " . $query);
            
            // Jika error karena NOT NULL constraint pada jurusan, coba perbaiki otomatis
            if (strpos($error_detail, 'null value in column "jurusan"') !== false) {
                // Coba hapus NOT NULL constraint
                $fix_query = "ALTER TABLE pendaftaran ALTER COLUMN jurusan DROP NOT NULL";
                $fix_result = pg_query($koneksi, $fix_query);
                
                if ($fix_result) {
                    // Coba insert lagi
                    $result = pg_query($koneksi, $query);
                    if ($result) {
                        pesan_kilat('Pendaftaran berhasil dikirim! (Database telah diperbaiki otomatis)', 'success');
                        header('Location: daftar.php');
                        exit;
                    }
                }
            }
            
            throw new Exception('Gagal menyimpan data ke database. ' . ($error_detail ? 'Error: ' . $error_detail : ''));
        }
        
        // Success
        pesan_kilat('Pendaftaran berhasil dikirim!', 'success');
        header('Location: daftar.php');
        exit;
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        pesan_kilat($e->getMessage(), 'error');
    }
}
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/daftar.css" />

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

 

<?php include __DIR__ . '/../../template/footer.php'; ?>

