<?php
$page_title = "Status Pendaftaran";
$base_path = '../../';
$current_page = 'status';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../template/mahasiswa_header.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$pendaftaran = null;

if ($koneksi && $username !== '') {
  $nim_esc = pg_escape_string($koneksi, $username);
  $q = "SELECT * FROM pendaftaran WHERE nim = '$nim_esc' ORDER BY tanggal_daftar DESC LIMIT 1";
  $res = pg_query($koneksi, $q);
  if ($res && pg_num_rows($res) === 1) {
    $pendaftaran = pg_fetch_assoc($res);
  }
}

function status_badge($status) {
  $status = $status ?? 'pending';
  switch ($status) {
    case 'accepted': return ['status-accepted', 'Diterima', 'lni-checkmark-circle'];
    case 'rejected': return ['status-rejected', 'Ditolak', 'lni-close'];
    default: return ['status-pending', 'Pending', 'lni-hourglass'];
  }
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
              <i class="lni lni-clipboard section-title-icon"></i>
              Status Pendaftaran
            </h2>
            <p class="wow fadeInUp section-title-p" data-wow-delay=".4s">
              Lihat status pendaftaran Anda di laboratorium.
            </p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="filter-card wow fadeInUp status-card" data-wow-delay=".2s">
            <?php if ($pendaftaran): ?>
              <?php list($cls, $text, $icon) = status_badge($pendaftaran['status'] ?? 'pending'); ?>
              <div class="status-header">
                <span class="status-chip <?php echo $cls; ?>">
                  <i class="lni <?php echo $icon; ?>"></i> <?php echo $text; ?>
                </span>
                <span class="status-meta">Didaftarkan pada <?php echo date('d/m/Y H:i', strtotime($pendaftaran['tanggal_daftar'])); ?> WIB</span>
              </div>
              <div class="detail-grid status-grid">
                <div class="detail-label">Nama Lengkap</div><div class="detail-value"><strong><?php echo htmlspecialchars($pendaftaran['nama'] ?? '-'); ?></strong></div>
                <div class="detail-label">NIM</div><div class="detail-value"><strong><?php echo htmlspecialchars($pendaftaran['nim'] ?? '-'); ?></strong></div>
                <div class="detail-label">Email</div><div class="detail-value"><?php echo htmlspecialchars($pendaftaran['email'] ?? '-'); ?></div>
                <div class="detail-label">Minat</div><div class="detail-value"><?php echo htmlspecialchars($pendaftaran['minat'] ?? '-'); ?></div>
                <div class="detail-label">Tanggal Review</div><div class="detail-value"><?php echo !empty($pendaftaran['tanggal_review']) ? htmlspecialchars($pendaftaran['tanggal_review']) : '-'; ?></div>
                <div class="detail-label">Catatan Admin</div><div class="detail-value"><?php echo !empty($pendaftaran['catatan_admin']) ? htmlspecialchars($pendaftaran['catatan_admin']) : '-'; ?></div>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="lni lni-inbox admin-table-empty-icon"></i>
                <p class="mt-3 text-muted">Data pendaftaran tidak ditemukan untuk NIM Anda.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

