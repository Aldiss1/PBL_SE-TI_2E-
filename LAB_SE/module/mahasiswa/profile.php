<?php
$page_title = "Profil Mahasiswa";
$base_path = '../../';
$current_page = 'profile';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';
require_once __DIR__ . '/../../template/mahasiswa_header.php';

// Ambil data user saat ini
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$user = null;
if ($username !== '' && $koneksi) {
  $u = pg_escape_string($koneksi, $username);
  $res = pg_query($koneksi, "SELECT username, full_name FROM users WHERE username = '$u' LIMIT 1");
  if ($res && pg_num_rows($res) === 1) {
    $user = pg_fetch_assoc($res);
  }
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $mode = isset($_POST['mode']) ? clean_input($_POST['mode']) : '';
  if ($mode === 'update_profile') {
    $full_name = isset($_POST['full_name']) ? clean_input($_POST['full_name']) : '';
    if ($full_name === '') {
      pesan_kilat('Nama lengkap tidak boleh kosong.', 'error');
    } else if ($koneksi && $username !== '') {
      $full_name_esc = pg_escape_string($koneksi, $full_name);
      $username_esc = pg_escape_string($koneksi, $username);
      $q = "UPDATE users SET full_name = '$full_name_esc', updated_at = NOW() WHERE username = '$username_esc'";
      $ok = pg_query($koneksi, $q);
      if ($ok) {
        pesan_kilat('Profil berhasil diperbarui.', 'success');
        $user['full_name'] = $full_name;
      } else {
        pesan_kilat('Gagal memperbarui profil.', 'error');
      }
    }
  } else if ($mode === 'change_password') {
    $current_password = isset($_POST['current_password']) ? clean_input($_POST['current_password']) : '';
    $new_password = isset($_POST['new_password']) ? clean_input($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? clean_input($_POST['confirm_password']) : '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
      pesan_kilat('Semua field password wajib diisi.', 'error');
    } else if ($new_password !== $confirm_password) {
      pesan_kilat('Konfirmasi password tidak cocok.', 'error');
    } else if (strlen($new_password) < 6) {
      pesan_kilat('Password baru minimal 6 karakter.', 'error');
    } else if ($koneksi && $username !== '') {
      $username_esc = pg_escape_string($koneksi, $username);
      // Verifikasi password saat ini
      $q = "SELECT id FROM users WHERE username = '$username_esc' AND password = MD5('".pg_escape_string($koneksi, $current_password)."') LIMIT 1";
      $res = pg_query($koneksi, $q);
      if ($res && pg_num_rows($res) === 1) {
        $np = pg_escape_string($koneksi, $new_password);
        $upd = pg_query($koneksi, "UPDATE users SET password = MD5('$np'), updated_at = NOW() WHERE username = '$username_esc'");
        if ($upd) {
          pesan_kilat('Password berhasil diubah.', 'success');
        } else {
          pesan_kilat('Gagal mengubah password.', 'error');
        }
      } else {
        pesan_kilat('Password saat ini salah.', 'error');
      }
    }
  }

  header('Location: profile.php');
  exit;
}
?>

<?php include __DIR__ . '/../../template/mahasiswa_sidebar.php'; ?>

<main class="admin-main-content">
  <section class="pt-50 pb-50 admin-section-bg-full">
    <div class="container">
      <div class="row mb-40">
        <div class="col-lg-12">
          <div class="section-title mb-30">
            <h2 class="mb-15 wow fadeInUp section-title-h2" data-wow-delay=".2s">
              <i class="lni lni-user section-title-icon"></i>
              Profil
            </h2>
            <p class="wow fadeInUp section-title-p" data-wow-delay=".4s">
              Kelola informasi profil dan ganti password akun Anda.
            </p>
          </div>
        </div>
      </div>

      <?php if (function_exists('tampilkan_pesan')) { tampilkan_pesan(); } ?>

      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-user"></i>
              <span>Informasi Profil</span>
            </div>
            <form method="POST">
              <input type="hidden" name="mode" value="update_profile" />
              <div class="mb-3">
                <label class="form-label form-label-custom">Username (NIM)</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled />
              </div>
              <div class="mb-3">
                <label class="form-label form-label-custom">Nama Lengkap</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Nama Lengkap" required />
              </div>
              <button type="submit" class="button radius-10">Simpan Perubahan</button>
            </form>
          </div>
        </div>

        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-lock-alt"></i>
              <span>Ganti Password</span>
            </div>
            <form method="POST">
              <input type="hidden" name="mode" value="change_password" />
              <div class="mb-3">
                <label class="form-label form-label-custom">Password Saat Ini</label>
                <input type="password" name="current_password" class="form-control" placeholder="Password saat ini" required />
              </div>
              <div class="mb-3">
                <label class="form-label form-label-custom">Password Baru</label>
                <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required />
              </div>
              <div class="mb-3">
                <label class="form-label form-label-custom">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required />
              </div>
              <button type="submit" class="button radius-10">Ubah Password</button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
*** End Patch

