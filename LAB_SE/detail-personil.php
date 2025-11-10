<?php
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/config/anti_injection.php';

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$personil = null;

// Cek apakah tabel anggota ada
$table_check = "SELECT EXISTS (
  SELECT FROM information_schema.tables 
  WHERE table_schema = 'public' 
  AND table_name = 'anggota'
)";
$table_exists = pg_query($koneksi, $table_check);
$has_table = false;
if ($table_exists) {
  $row_check = pg_fetch_assoc($table_exists);
  $has_table = $row_check['exists'] === 't' || $row_check['exists'] === true;
}

if ($id && $has_table) {
    $query = "SELECT * FROM anggota WHERE id = '$id'";
    $result = pg_query($koneksi, $query);
    if (!$result) {
        error_log("Error query detail personil: " . pg_last_error($koneksi));
    } elseif (pg_num_rows($result) > 0) {
        $personil = pg_fetch_assoc($result);
    }
}

if (!$personil) {
    header('Location: personil.php');
    exit;
}
?>
<?php include __DIR__ . '/template/header.php'; ?>

<style>
  .profile-details h3 {
    color: #323450;
    font-weight: 600;
    margin-bottom: 10px;
  }
  .profile-details .role {
    font-size: 1.25rem;
    font-weight: 500;
    color: #2F80ED;
    margin-bottom: 25px;
  }
  .profile-details .contact-info {
    list-style: none;
    padding: 0;
    margin: 25px 0;
  }
  .profile-details .contact-info li {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
    font-size: 1rem;
  }
  .profile-details .contact-info li i {
    font-size: 1.2rem;
    color: #2F80ED;
    margin-right: 15px;
    width: 20px;
    text-align: center;
  }
  .profile-details .section-heading {
    margin-top: 40px;
    margin-bottom: 20px;
    font-weight: 600;
    color: #323450;
  }
</style>

<section class="pt-150 pb-50" style="background: #f3f3f3; margin-top: 80px;">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="section-title text-center mb-30">
            <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Detail Personil</h2>
            <p class="wow fadeInUp" data-wow-delay=".4s">Informasi lengkap tentang anggota laboratorium</p>
          </div>
        </div>
      </div>
    </div>
</section>

<section class="pt-100 pb-70" style="background: #ffffff;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="profile-details wow fadeInUp" data-wow-delay=".2s">
                    <div class="text-center mb-40">
                        <img src="<?php echo isset($personil['foto']) && $personil['foto'] ? htmlspecialchars($personil['foto']) : 'https://via.placeholder.com/300'; ?>" 
                             alt="<?php echo htmlspecialchars($personil['nama']); ?>" 
                             style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; margin-bottom: 20px;">
                        <h3><?php echo htmlspecialchars($personil['nama']); ?></h3>
                        <p class="role"><?php echo htmlspecialchars($personil['jabatan'] ?? 'Anggota'); ?></p>
                    </div>
                    
                    <div class="section-heading">Informasi Kontak</div>
                    <ul class="contact-info">
                        <?php if (!empty($personil['email'])): ?>
                        <li>
                            <i class="lni lni-envelope"></i>
                            <span><?php echo htmlspecialchars($personil['email']); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($personil['telepon'])): ?>
                        <li>
                            <i class="lni lni-phone"></i>
                            <span><?php echo htmlspecialchars($personil['telepon']); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <?php if (!empty($personil['deskripsi'])): ?>
                    <div class="section-heading">Tentang</div>
                    <p><?php echo nl2br(htmlspecialchars($personil['deskripsi'])); ?></p>
                    <?php endif; ?>
                    
                    <div class="text-center mt-40">
                        <a href="personil.php" class="button radius-10 border-button">← Kembali ke Daftar Personil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/template/footer.php'; ?>

