<?php
$page_title = "Dashboard Mahasiswa";
$base_path = '../../';
$current_page = 'dashboard';
require_once __DIR__ . '/../../template/mahasiswa_header.php';
?>

<?php
require_once __DIR__ . '/../../config/koneksi.php';
// Ambil data pendaftaran untuk profil (nama & foto) dan status berdasarkan NIM (username)
$my_status = null;
$my_profile = null;
$recent_posts = [];
$recent_events = [];
$recent_resources = [];
$latest_announcement = null;

if ($koneksi && isset($_SESSION['username'])) {
  $nim_esc = pg_escape_string($koneksi, $_SESSION['username']);
  $res = pg_query($koneksi, "SELECT nama, foto, status, tanggal_daftar FROM pendaftaran WHERE nim = '$nim_esc' ORDER BY tanggal_daftar DESC LIMIT 1");
  if ($res && pg_num_rows($res) === 1) {
    $row = pg_fetch_assoc($res);
    $my_profile = ['nama' => $row['nama'] ?? '', 'foto' => $row['foto'] ?? ''];
    $my_status = ['status' => $row['status'] ?? 'pending', 'tanggal_daftar' => $row['tanggal_daftar'] ?? null];
  }
}

if ($koneksi) {
  // Coba gunakan materialized view, fallback ke query biasa
  if (file_exists(__DIR__ . '/../../fungsi/materialized_views_simple.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_simple.php';
    
    // 3 pengumuman terbaru dari materialized view
    $recent_posts_raw = get_blog_published_simple(3);
    if (!empty($recent_posts_raw)) {
      foreach ($recent_posts_raw as $blog) {
        $recent_posts[] = [
          'title' => $blog['title'] ?? '',
          'slug' => $blog['slug'] ?? '',
          'published_at' => $blog['published_at'] ?? $blog['created_at'] ?? null
        ];
      }
    }
    
    // 3 event terbaru dari materialized view
    $recent_events_raw = get_blog_published_simple(3, 'event');
    if (!empty($recent_events_raw)) {
      foreach ($recent_events_raw as $blog) {
        $recent_events[] = [
          'title' => $blog['title'] ?? '',
          'slug' => $blog['slug'] ?? '',
          'published_at' => $blog['published_at'] ?? $blog['created_at'] ?? null
        ];
      }
    }
    
    // 3 resource/materi terbaru dari materialized view
    $recent_resources_raw = get_blog_published_simple(10);
    if (!empty($recent_resources_raw)) {
      foreach ($recent_resources_raw as $blog) {
        $cat = strtolower($blog['category'] ?? '');
        if (in_array($cat, ['materi', 'resource', 'resources']) && count($recent_resources) < 3) {
          $recent_resources[] = [
            'title' => $blog['title'] ?? '',
            'slug' => $blog['slug'] ?? '',
            'published_at' => $blog['published_at'] ?? $blog['created_at'] ?? null
          ];
        }
      }
    }
    
    // Banner pengumuman paling baru dari materialized view
    $latest = get_blog_published_simple(1);
    if (!empty($latest)) {
      $latest_announcement = [
        'title' => $latest[0]['title'] ?? '',
        'slug' => $latest[0]['slug'] ?? ''
      ];
    }
  }
  
  // Fallback: Query langsung jika materialized view tidak tersedia
  if (empty($recent_posts)) {
    $rp = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
    if ($rp && pg_num_rows($rp) > 0) {
      while ($row = pg_fetch_assoc($rp)) { $recent_posts[] = $row; }
    }
  }
  
  if (empty($recent_events)) {
    $ev = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE AND lower(category) = 'event' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
    if ($ev && pg_num_rows($ev) > 0) {
      while ($row = pg_fetch_assoc($ev)) { $recent_events[] = $row; }
    }
  }
  
  if (empty($recent_resources)) {
    $rs = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE AND lower(category) IN ('materi','resource','resources') ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
    if ($rs && pg_num_rows($rs) > 0) {
      while ($row = pg_fetch_assoc($rs)) { $recent_resources[] = $row; }
    }
  }
  
  if (!$latest_announcement) {
    $ba = pg_query($koneksi, "SELECT title, slug FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 1");
    if ($ba && pg_num_rows($ba) === 1) {
      $latest_announcement = pg_fetch_assoc($ba);
    }
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
              <i class="lni lni-home section-title-icon"></i>
              Dashboard Mahasiswa
            </h2>
            <p class="wow fadeInUp section-title-p" data-wow-delay=".4s">
              Selamat datang, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Mahasiswa'; ?>.
            </p>
          </div>
        </div>
      </div>

      <!-- Hero Greeting -->
      <div class="row mb-4">
        <div class="col-lg-12">
          <div class="hero-greeting">
            <div class="hero-left">
              <div class="hero-avatar">
                <?php if (!empty($my_profile['foto'])): ?>
                  <img src="<?php echo htmlspecialchars($my_profile['foto']); ?>" alt="Foto" />
                <?php else: ?>
                  <div class="hero-avatar-fallback"><?php echo strtoupper(substr($my_profile['nama'] ?? (isset($_SESSION['username']) ? $_SESSION['username'] : 'M'), 0, 1)); ?></div>
                <?php endif; ?>
              </div>
              <div class="hero-text">
                <div class="hero-title">Halo, <?php echo htmlspecialchars($my_profile['nama'] ?? (isset($_SESSION['username']) ? $_SESSION['username'] : 'Mahasiswa')); ?> 👋</div>
                <div class="hero-subtitle">Selamat datang di Portal Mahasiswa Lab SE</div>
              </div>
            </div>
            <div class="hero-actions">
              <a class="button button-sm radius-10" href="<?php echo $base_path; ?>module/mahasiswa/profile.php"><i class="lni lni-user mr-6"></i> Profil</a>
              <a class="button button-sm radius-10 border-button" href="<?php echo $base_path; ?>module/mahasiswa/announcements.php"><i class="lni lni-bullhorn mr-6"></i> Pengumuman</a>
              <a class="button button-sm radius-10 border-button" href="<?php echo $base_path; ?>module/mahasiswa/status.php"><i class="lni lni-clipboard mr-6"></i> Status</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Dismissible Announcement Banner -->
      <?php if ($latest_announcement): ?>
      <div class="row mb-3" id="topBannerRow" style="display:none;">
        <div class="col-lg-12">
          <div class="top-banner" id="topBanner">
            <div class="top-banner-text">
              <i class="lni lni-bullhorn"></i>
              <span>Pengumuman: </span>
              <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($latest_announcement['slug'] ?? ''); ?>">
                <?php echo htmlspecialchars($latest_announcement['title'] ?? 'Baru'); ?>
              </a>
            </div>
            <button type="button" class="top-banner-close" id="topBannerClose">&times;</button>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="row mt-2">
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card-mh stat-card-blue wow fadeInUp" data-wow-delay=".2s">
            <div class="stat-icon"><i class="lni lni-checkmark-circle"></i></div>
            <div class="stat-number">Akses</div>
            <div class="stat-label">Masuk ke portal mahasiswa</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card-mh stat-card-orange wow fadeInUp" data-wow-delay=".3s">
            <div class="stat-icon"><i class="lni lni-user"></i></div>
            <div class="stat-number">Profil</div>
            <div class="stat-label">Perbarui data dan password</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card-mh stat-card-green wow fadeInUp" data-wow-delay=".4s">
            <div class="stat-icon"><i class="lni lni-bullhorn"></i></div>
            <div class="stat-number">Info</div>
            <div class="stat-label">Pengumuman dan kegiatan</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="filter-card wow fadeInUp mb-20" data-wow-delay=".2s">
            <div class="filter-header mb-0">
              <i class="lni lni-information"></i>
              <span>Informasi</span>
            </div>
            <div class="text-color-primary pt-12">
              - Setelah akun dibuat oleh admin, Anda bisa login menggunakan NIM sebagai username dan password awal.<br/>
              - Demi keamanan, segera ganti password Anda pada menu Profil (akan ditambahkan).<br/>
              - Pengumuman dan materi akan tersedia di menu yang akan datang.
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-clipboard"></i>
              <span>Status Pendaftaran</span>
            </div>
            <?php
            $status_text = 'Belum ada data pendaftaran';
            $badge = '<span class="detail-status-badge status-pending"><i class="lni lni-hourglass"></i> Pending</span>';
            if ($my_status) {
              $s = $my_status['status'] ?? 'pending';
              if ($s === 'accepted') { $badge = '<span class="detail-status-badge status-accepted"><i class="lni lni-checkmark-circle"></i> Diterima</span>'; $status_text = 'Selamat! Anda telah diterima.'; }
              else if ($s === 'rejected') { $badge = '<span class="detail-status-badge status-rejected"><i class="lni lni-close"></i> Ditolak</span>'; $status_text = 'Maaf, pendaftaran Anda belum diterima.'; }
              else { $status_text = 'Pendaftaran Anda masih dalam proses review.'; }
            }
            ?>
            <div class="flex-center-gap-12">
              <?php echo $badge; ?>
              <div class="text-color-primary"><?php echo $status_text; ?></div>
            </div>
            <div class="mt-12">
              <a href="<?php echo $base_path; ?>module/mahasiswa/status.php" class="button button-sm radius-10">Lihat Detail</a>
            </div>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-bullhorn"></i>
              <span>Pengumuman Terbaru</span>
            </div>
            <?php if (count($recent_posts) > 0): ?>
              <?php foreach ($recent_posts as $rp): ?>
                <div class="list-item">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($rp['slug'] ?? ''); ?>" class="list-item-link">
                    <?php echo htmlspecialchars($rp['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($rp['published_at'])): ?>
                    <div class="list-item-date"><?php echo date('d M Y', strtotime($rp['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <div class="mt-10">
                <a href="<?php echo $base_path; ?>module/mahasiswa/announcements.php" class="button button-sm radius-10">Lihat Semua</a>
              </div>
            <?php else: ?>
              <div class="text-muted-medium">Belum ada pengumuman.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-calendar"></i>
              <span>Event Terdekat</span>
            </div>
            <?php if (count($recent_events) > 0): ?>
              <?php foreach ($recent_events as $ev): ?>
                <div class="list-item">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($ev['slug'] ?? ''); ?>" class="list-item-link">
                    <?php echo htmlspecialchars($ev['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($ev['published_at'])): ?>
                    <div class="list-item-date"><?php echo date('d M Y', strtotime($ev['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted-medium">Belum ada event.</div>
            <?php endif; ?>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
            <div class="filter-header">
              <i class="lni lni-download"></i>
              <span>Materi & Resources</span>
            </div>
            <?php if (count($recent_resources) > 0): ?>
              <?php foreach ($recent_resources as $rs): ?>
                <div class="list-item">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($rs['slug'] ?? ''); ?>" class="list-item-link">
                    <?php echo htmlspecialchars($rs['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($rs['published_at'])): ?>
                    <div class="list-item-date"><?php echo date('d M Y', strtotime($rs['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted-medium">Belum ada materi/resources.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<script>
(function() {
  // Dismissible banner with sessionStorage
  const bannerRow = document.getElementById('topBannerRow');
  const banner = document.getElementById('topBanner');
  const closeBtn = document.getElementById('topBannerClose');
  if (bannerRow && banner && closeBtn) {
    if (!sessionStorage.getItem('mh_top_banner_dismissed')) {
      bannerRow.style.display = 'block';
    }
    closeBtn.addEventListener('click', function() {
      bannerRow.style.display = 'none';
      sessionStorage.setItem('mh_top_banner_dismissed', '1');
    });
  }
})();
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

