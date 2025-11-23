<?php
$page_title = "Pengumuman";
$base_path = '../../';
$current_page = 'announcements';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../template/mahasiswa_header.php';

// Ambil 10 pengumuman terbaru
$posts = [];
if ($koneksi) {
  $q = "SELECT id, title, slug, excerpt, published_at FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 10";
  $res = pg_query($koneksi, $q);
  if ($res && pg_num_rows($res) > 0) {
    while ($row = pg_fetch_assoc($res)) { $posts[] = $row; }
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
              Pengumuman
            </h2>
            <p class="wow fadeInUp" data-wow-delay=".4s" style="color:#6c757d; font-size:14px; margin:0;">
              Informasi terbaru dari Laboratorium Software Engineering
            </p>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <?php if (count($posts) > 0): ?>
              <?php foreach ($posts as $p): ?>
                <div style="padding:16px 0; border-bottom:1px solid #f1f1f1;">
                  <h5 style="margin:0 0 6px 0; font-weight:700; color:#2c3e50;">
                    <a href="announcement_detail.php?slug=<?php echo urlencode($p['slug'] ?? (string)$p['id']); ?>" style="text-decoration:none; color:#2c3e50;">
                      <?php echo htmlspecialchars($p['title'] ?? ''); ?>
                    </a>
                  </h5>
                  <div style="font-size:12px; color:#6c757d; margin-bottom:6px;">
                    <?php if (!empty($p['published_at'])): ?>
                      <?php echo date('d M Y', strtotime($p['published_at'])); ?>
                    <?php endif; ?>
                  </div>
                  <div style="color:#34495e; font-size:14px;">
                    <?php echo htmlspecialchars($p['excerpt'] ?? ''); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                <p class="mt-3 text-muted">Belum ada pengumuman.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>





