<?php
$page_title = "Detail Pengumuman";
$base_path = '../../';
$current_page = 'announcements';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../template/mahasiswa_header.php';

$slug = isset($_GET['slug']) ? clean_input($_GET['slug']) : '';
$post = null;

if ($koneksi && $slug !== '') {
  // Cari berdasarkan slug dulu, jika tidak ada jatuh ke id numeric
  $slug_esc = pg_escape_string($koneksi, $slug);
  $q = "SELECT id, title, content, published_at FROM blog WHERE is_published = TRUE AND (slug = '$slug_esc'";
  if (ctype_digit($slug)) {
    $q .= " OR id = ".((int)$slug);
  }
  $q .= ") LIMIT 1";
  $res = pg_query($koneksi, $q);
  if ($res && pg_num_rows($res) === 1) {
    $post = pg_fetch_assoc($res);
  }
}
?>

<?php include __DIR__ . '/../../template/mahasiswa_sidebar.php'; ?>

<main class="admin-main-content">
  <section class="pt-50 pb-50" style="background: #f8f9fa; min-height: calc(100vh - 80px); padding-top: 40px !important;">
    <div class="container">
      <div class="row mb-40">
        <div class="col-lg-12">
          <div class="section-title mb-30">
            <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s" style="color:#2c3e50; font-size:28px; font-weight:700;">
              <i class="lni lni-bullhorn" style="color:#2F80ED; margin-right:10px;"></i>
              <?php echo htmlspecialchars($post['title'] ?? 'Pengumuman'); ?>
            </h2>
            <?php if ($post && !empty($post['published_at'])): ?>
            <p class="wow fadeInUp" data-wow-delay=".4s" style="color:#6c757d; font-size:12px; margin:0;">
              Dipublikasikan pada <?php echo date('d M Y', strtotime($post['published_at'])); ?>
            </p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <?php if ($post): ?>
              <div style="color:#2c3e50; font-size:15px;">
                <?php
                // Konten diasumsikan HTML aman dari admin
                echo $post['content'];
                ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="lni lni-close" style="font-size: 48px; color: #e74c3c;"></i>
                <p class="mt-3 text-muted">Pengumuman tidak ditemukan.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>



