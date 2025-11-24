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
  <section class="pt-50 pb-50 admin-section-bg-full">
    <div class="container">
      <div class="row mb-40">
        <div class="col-lg-12">
          <div class="section-title mb-30">
            <h2 class="mb-15 wow fadeInUp section-title-h2" data-wow-delay=".2s">
              <i class="lni lni-bullhorn section-title-icon"></i>
              Pengumuman
            </h2>
            <p class="wow fadeInUp section-title-p" data-wow-delay=".4s">
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
                <div class="list-item">
                  <h5 class="text-color-primary" style="margin:0 0 6px 0; font-weight:700;">
                    <a href="announcement_detail.php?slug=<?php echo urlencode($p['slug'] ?? (string)$p['id']); ?>" class="list-item-link">
                      <?php echo htmlspecialchars($p['title'] ?? ''); ?>
                    </a>
                  </h5>
                  <div class="list-item-date mb-6">
                    <?php if (!empty($p['published_at'])): ?>
                      <?php echo date('d M Y', strtotime($p['published_at'])); ?>
                    <?php endif; ?>
                  </div>
                  <div class="text-muted-medium" style="color:#34495e;">
                    <?php echo htmlspecialchars($p['excerpt'] ?? ''); ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="lni lni-inbox admin-table-empty-icon"></i>
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






