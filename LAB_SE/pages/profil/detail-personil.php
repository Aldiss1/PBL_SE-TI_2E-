<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';
$personil = null;

if ($id) {
    $query = "SELECT * FROM anggota WHERE id = '$id'";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        $personil = pg_fetch_assoc($result);
    }
}

if (!$personil) {
    header('Location: personil.php');
    exit;
}
?>
<?php include __DIR__ . '/../../template/header.php'; ?>

<link rel="stylesheet" href="../../assets/css/detail-personil.css" />

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

<?php include __DIR__ . '/../../template/footer.php'; ?>

