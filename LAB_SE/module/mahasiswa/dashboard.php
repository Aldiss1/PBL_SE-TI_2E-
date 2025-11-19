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
  // 3 pengumuman terbaru
  $rp = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
  if ($rp && pg_num_rows($rp) > 0) {
    while ($row = pg_fetch_assoc($rp)) { $recent_posts[] = $row; }
  }
  // 3 event terbaru (berdasarkan category)
  $ev = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE AND lower(category) = 'event' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
  if ($ev && pg_num_rows($ev) > 0) {
    while ($row = pg_fetch_assoc($ev)) { $recent_events[] = $row; }
  }
  // 3 resource/materi terbaru (berdasarkan category)
  $rs = pg_query($koneksi, "SELECT title, slug, published_at FROM blog WHERE is_published = TRUE AND lower(category) IN ('materi','resource','resources') ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
  if ($rs && pg_num_rows($rs) > 0) {
    while ($row = pg_fetch_assoc($rs)) { $recent_resources[] = $row; }
  }
  // Banner pengumuman paling baru
  $ba = pg_query($koneksi, "SELECT title, slug FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 1");
  if ($ba && pg_num_rows($ba) === 1) {
    $latest_announcement = pg_fetch_assoc($ba);
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
              <i class="lni lni-home" style="color:#2F80ED; margin-right:10px;"></i>
              Dashboard Mahasiswa
            </h2>
            <p class="wow fadeInUp" data-wow-delay=".4s" style="color:#6c757d; font-size:14px; margin:0;">
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
              <a class="button button-sm radius-10" href="<?php echo $base_path; ?>module/mahasiswa/profile.php"><i class="lni lni-user" style="margin-right:6px;"></i> Profil</a>
              <a class="button button-sm radius-10 border-button" href="<?php echo $base_path; ?>module/mahasiswa/announcements.php"><i class="lni lni-bullhorn" style="margin-right:6px;"></i> Pengumuman</a>
              <a class="button button-sm radius-10 border-button" href="<?php echo $base_path; ?>module/mahasiswa/status.php"><i class="lni lni-clipboard" style="margin-right:6px;"></i> Status</a>
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
          <div class="stat-card stat-card-blue wow fadeInUp" data-wow-delay=".2s">
            <div class="stat-icon"><i class="lni lni-checkmark-circle"></i></div>
            <div class="stat-number">Akses</div>
            <div class="stat-label">Masuk ke portal mahasiswa</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card stat-card-orange wow fadeInUp" data-wow-delay=".3s">
            <div class="stat-icon"><i class="lni lni-user"></i></div>
            <div class="stat-number">Profil</div>
            <div class="stat-label">Perbarui data dan password</div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="stat-card stat-card-green wow fadeInUp" data-wow-delay=".4s">
            <div class="stat-icon"><i class="lni lni-bullhorn"></i></div>
            <div class="stat-number">Info</div>
            <div class="stat-label">Pengumuman dan kegiatan</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-12">
          <div class="filter-card wow fadeInUp" data-wow-delay=".2s" style="margin-bottom: 20px;">
            <div class="filter-header" style="margin-bottom:0;">
              <i class="lni lni-information"></i>
              <span>Informasi</span>
            </div>
            <div style="padding-top:12px; color:#2c3e50;">
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
            $badge = '<span class="detail-status-badge status-pending" style="padding:6px 12px; border-radius:16px; font-size:12px; display:inline-flex; align-items:center; gap:6px;"><i class=\"lni lni-hourglass\"></i> Pending</span>';
            if ($my_status) {
              $s = $my_status['status'] ?? 'pending';
              if ($s === 'accepted') { $badge = '<span class="detail-status-badge status-accepted" style="padding:6px 12px; border-radius:16px; font-size:12px; display:inline-flex; align-items:center; gap:6px;"><i class="lni lni-checkmark-circle"></i> Diterima</span>'; $status_text = 'Selamat! Anda telah diterima.'; }
              else if ($s === 'rejected') { $badge = '<span class="detail-status-badge status-rejected" style="padding:6px 12px; border-radius:16px; font-size:12px; display:inline-flex; align-items:center; gap:6px;"><i class="lni lni-close"></i> Ditolak</span>'; $status_text = 'Maaf, pendaftaran Anda belum diterima.'; }
              else { $status_text = 'Pendaftaran Anda masih dalam proses review.'; }
            }
            ?>
            <div style="display:flex; align-items:center; gap:12px;">
              <?php echo $badge; ?>
              <div style="color:#2c3e50;"><?php echo $status_text; ?></div>
            </div>
            <div style="margin-top:12px;">
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
                <div style="padding:10px 0; border-bottom:1px solid #f1f1f1;">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($rp['slug'] ?? ''); ?>" style="text-decoration:none; color:#2c3e50; font-weight:700;">
                    <?php echo htmlspecialchars($rp['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($rp['published_at'])): ?>
                    <div style="font-size:12px; color:#6c757d;"><?php echo date('d M Y', strtotime($rp['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <div style="margin-top:10px;">
                <a href="<?php echo $base_path; ?>module/mahasiswa/announcements.php" class="button button-sm radius-10">Lihat Semua</a>
              </div>
            <?php else: ?>
              <div class="text-muted" style="font-size:14px;">Belum ada pengumuman.</div>
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
                <div style="padding:10px 0; border-bottom:1px solid #f1f1f1;">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($ev['slug'] ?? ''); ?>" style="text-decoration:none; color:#2c3e50; font-weight:700;">
                    <?php echo htmlspecialchars($ev['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($ev['published_at'])): ?>
                    <div style="font-size:12px; color:#6c757d;"><?php echo date('d M Y', strtotime($ev['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted" style="font-size:14px;">Belum ada event.</div>
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
                <div style="padding:10px 0; border-bottom:1px solid #f1f1f1;">
                  <a href="<?php echo $base_path; ?>module/mahasiswa/announcement_detail.php?slug=<?php echo urlencode($rs['slug'] ?? ''); ?>" style="text-decoration:none; color:#2c3e50; font-weight:700;">
                    <?php echo htmlspecialchars($rs['title'] ?? ''); ?>
                  </a>
                  <?php if (!empty($rs['published_at'])): ?>
                    <div style="font-size:12px; color:#6c757d;"><?php echo date('d M Y', strtotime($rs['published_at'])); ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-muted" style="font-size:14px;">Belum ada materi/resources.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </section>
</main>

<style>
/* Stat Cards */
.stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 22px; color: white; box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: all 0.2s ease; height: 100%; text-align: center; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.14) 0%, transparent 70%); animation: pulse 3s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.05); opacity: 0.8; } }
.stat-card:hover { transform: translateY(-4px); box-shadow: 0 14px 28px rgba(0,0,0,0.12); }
.stat-card-blue { background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); }
.stat-card-orange { background: linear-gradient(135deg, #F2994A 0%, #E67E22 100%); }
.stat-card-green { background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%); }
.stat-card .stat-icon { font-size: 32px; margin-bottom: 10px; opacity: 0.95; position: relative; z-index: 1; }
.stat-card .stat-number { font-size: 20px; font-weight: 700; margin-bottom: 4px; position: relative; z-index: 1; text-shadow: 0 1px 6px rgba(0,0,0,0.2); }
.stat-card .stat-label { font-size: 12px; opacity: 0.95; font-weight: 600; position: relative; z-index: 1; text-transform: uppercase; letter-spacing: 1px; }

/* Info Card */
.filter-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
.filter-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 2px solid #f8f9fa; color: #2c3e50; font-weight: 700; }
.filter-header i { font-size: 18px; color: #2F80ED; }

/* Hero Greeting */
.hero-greeting { display: flex; justify-content: space-between; align-items: center; gap: 16px; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border-radius: 14px; padding: 18px 20px; color: #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
.hero-left { display: flex; align-items: center; gap: 14px; }
.hero-avatar { width: 56px; height: 56px; border-radius: 50%; overflow: hidden; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; }
.hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
.hero-avatar-fallback { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #2F80ED; background: #fff; }
.hero-text .hero-title { font-size: 18px; font-weight: 800; line-height: 1.2; }
.hero-text .hero-subtitle { font-size: 13px; opacity: 0.95; }
.hero-actions .button { margin-left: 8px; }
.hero-actions .border-button { background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.3); }
.hero-actions .border-button:hover { background: rgba(255,255,255,0.25); }

/* Top Banner */
.top-banner { background: #fff7e6; border: 1px solid #ffe8bf; border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); }
.top-banner .top-banner-text { display: flex; align-items: center; gap: 10px; color: #a35d00; font-weight: 600; }
.top-banner .top-banner-text a { color: #a35d00; text-decoration: underline; }
.top-banner .top-banner-text i { color: #ff9900; }
.top-banner .top-banner-close { background: transparent; border: none; color: #a35d00; font-size: 20px; line-height: 1; cursor: pointer; }

/* Responsive tweaks */
@media (max-width: 991px) {
  .stat-card { padding: 18px; }
  .stat-card .stat-icon { font-size: 28px; }
  .stat-card .stat-number { font-size: 18px; }
  .hero-greeting { flex-direction: column; align-items: flex-start; }
  .hero-actions .button { margin-left: 0; margin-right: 8px; margin-top: 8px; }
}
</style>

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

