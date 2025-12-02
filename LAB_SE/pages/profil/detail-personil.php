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

<!-- Section Header -->
<section class="pt-150 pb-50">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title text-center mb-30">
                    <h2 class="mb-15 wow fadeInUp gradient-text" data-wow-delay=".2s">Detail <span>Personil</span></h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Informasi lengkap tentang anggota laboratorium</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Detail Content -->
<section id="detail-personil-content" class="pt-100 pb-70">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="detail-card wow fadeInUp" data-wow-delay=".2s">
                    <!-- Profile Header -->
                    <div class="profile-header">
                        <div class="profile-image-wrapper">
                            <?php 
                            $foto_raw = $personil['foto'] ?? '';
                            $is_abs = preg_match('/^https?:\/\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                            $foto_src = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : 'https://via.placeholder.com/400');
                            ?>
                            <img src="<?php echo htmlspecialchars($foto_src); ?>" 
                                 alt="<?php echo htmlspecialchars($personil['nama']); ?>" 
                                 class="profile-image"
                                 onerror="this.src='https://via.placeholder.com/400?text=<?php echo urlencode(substr($personil['nama'], 0, 1)); ?>'">
                        </div>
                        <div class="profile-info">
                            <h3 class="profile-name"><?php echo htmlspecialchars($personil['nama']); ?></h3>
                            <p class="profile-role">
                                <i class="lni lni-briefcase"></i>
                                <?php echo htmlspecialchars($personil['jabatan'] ?? 'Anggota'); ?>
                            </p>
                            <?php if (!empty($personil['instagram']) || !empty($personil['linkedin'])): ?>
                            <div class="profile-socials">
                                <?php if (!empty($personil['instagram'])): ?>
                                <a href="<?php echo htmlspecialchars($personil['instagram']); ?>" target="_blank" rel="noopener" class="social-icon instagram">
                                    <i class="lni lni-instagram-filled"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($personil['linkedin'])): ?>
                                <a href="<?php echo htmlspecialchars($personil['linkedin']); ?>" target="_blank" rel="noopener" class="social-icon linkedin">
                                    <i class="lni lni-linkedin-original"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Content -->
                    <div class="profile-content">
                        <?php if (!empty($personil['deskripsi'])): ?>
                        <div class="info-section">
                            <div class="info-header">
                                <i class="lni lni-user"></i>
                                <h4>Tentang</h4>
                            </div>
                            <div class="info-body">
                                <p><?php echo nl2br(htmlspecialchars($personil['deskripsi'])); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($personil['email']) || !empty($personil['telepon'])): ?>
                        <div class="info-section">
                            <div class="info-header">
                                <i class="lni lni-envelope"></i>
                                <h4>Informasi Kontak</h4>
                            </div>
                            <div class="info-body">
                                <ul class="contact-list">
                                    <?php if (!empty($personil['email'])): ?>
                                    <li>
                                        <i class="lni lni-envelope"></i>
                                        <div>
                                            <span class="label">Email</span>
                                            <span class="value"><?php echo htmlspecialchars($personil['email']); ?></span>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (!empty($personil['telepon'])): ?>
                                    <li>
                                        <i class="lni lni-phone"></i>
                                        <div>
                                            <span class="label">Telepon</span>
                                            <span class="value"><?php echo htmlspecialchars($personil['telepon']); ?></span>
                                        </div>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="profile-actions">
                        <a href="personil.php" class="button radius-10 border-button">
                            <i class="lni lni-arrow-left"></i> Kembali ke Daftar Personil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../template/footer.php'; ?>

