<?php
$page_title = "Admin Dashboard - Web Profile Laboratorium SE";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';

// Set active menu
$current_page = 'dashboard';
$menu_items = [
    'dashboard.php' => [
        'title' => 'Dashboard',
        'icon' => 'lni lni-home',
        'link' => '../../module/admin/dashboard.php',
        'active' => true
    ],
    'anggota' => [
        'title' => 'Kelola Anggota',
        'icon' => 'lni lni-users',
        'link' => '../../module/anggota/index.php',
        'active' => false
    ],
    'jabatan' => [
        'title' => 'Kelola Jabatan',
        'icon' => 'lni lni-briefcase',
        'link' => '../../module/jabatan/index.php',
        'active' => false
    ],
    'user' => [
        'title' => 'Kelola User',
        'icon' => 'lni lni-user',
        'link' => '../../module/user/index.php',
        'active' => false
    ],
    'pendaftar' => [
        'title' => 'Kelola Pendaftar',
        'icon' => 'lni lni-list',
        'link' => '../../module/pendaftar/index.php',
        'active' => false
    ],
    'blog' => [
        'title' => 'Kelola Blog',
        'icon' => 'lni lni-text-format',
        'link' => '../../module/blog/index.php',
        'active' => false
    ],
    'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php',
        'active' => false
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Ambil statistik dari materialized view (lebih cepat!)
// Fallback ke query biasa jika materialized view belum dibuat
$stats = [
    'anggota' => 0,
    'pendaftar' => 0,
    'pendaftar_pending' => 0,
    'jabatan' => 0,
    'blog_published' => 0,
    'blog_draft' => 0,
    'user' => 0
];
$recent_pendaftar = [];
$recent_blog = [];

if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
    $stats_mv = get_stats_complete();
    if ($stats_mv) {
        $stats = [
            'anggota' => (int)($stats_mv['total_anggota'] ?? 0),
            'pendaftar' => (int)($stats_mv['total_pendaftar'] ?? 0),
            'pendaftar_pending' => (int)($stats_mv['pendaftar_pending'] ?? 0),
            'jabatan' => (int)($stats_mv['total_jabatan'] ?? 0),
            'blog_published' => (int)($stats_mv['total_blog_published'] ?? 0),
            'blog_draft' => (int)($stats_mv['total_blog_draft'] ?? 0),
            'user' => (int)($stats_mv['total_user'] ?? 0)
        ];
        $recent_pendaftar = get_pendaftar_terbaru_complete(5);
        $recent_blog = get_blog_published_complete(3);
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if (empty($recent_pendaftar) && $koneksi) {
    // Hitung statistik (sederhanakan dengan satu query)
    $query_stats = "SELECT 
        (SELECT COUNT(*) FROM anggota) as anggota,
        (SELECT COUNT(*) FROM pendaftaran) as pendaftar,
        (SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending') as pendaftar_pending,
        (SELECT COUNT(*) FROM jabatan) as jabatan,
        (SELECT COUNT(*) FROM blog WHERE is_published = TRUE) as blog_published,
        (SELECT COUNT(*) FROM blog WHERE is_published = FALSE) as blog_draft,
        (SELECT COUNT(*) FROM users) as user";
    $result_stats = pg_query($koneksi, $query_stats);
    if ($result_stats && pg_num_rows($result_stats) > 0) {
        $row = pg_fetch_assoc($result_stats);
        $stats['anggota'] = (int)$row['anggota'];
        $stats['pendaftar'] = (int)$row['pendaftar'];
        $stats['pendaftar_pending'] = (int)$row['pendaftar_pending'];
        $stats['jabatan'] = (int)$row['jabatan'];
        $stats['blog_published'] = (int)$row['blog_published'];
        $stats['blog_draft'] = (int)$row['blog_draft'];
        $stats['user'] = (int)$row['user'];
    }
    
    // Ambil pendaftar terbaru (5 terakhir)
    $query_recent = "SELECT nama, nim, tanggal_daftar, status FROM pendaftaran ORDER BY tanggal_daftar DESC LIMIT 5";
    $result_recent = pg_query($koneksi, $query_recent);
    if ($result_recent && pg_num_rows($result_recent) > 0) {
        while ($row = pg_fetch_assoc($result_recent)) {
            $recent_pendaftar[] = $row;
        }
    }
    
    // Ambil blog terbaru (3 terakhir)
    $query_blog = "SELECT id, title, slug, published_at, created_at FROM blog WHERE is_published = TRUE ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3";
    $result_blog = pg_query($koneksi, $query_blog);
    if ($result_blog && pg_num_rows($result_blog) > 0) {
        while ($row = pg_fetch_assoc($result_blog)) {
            $recent_blog[] = $row;
        }
    }
}

// Ambil data pendaftaran per bulan dari materialized view (lebih cepat!)
$chart_data = ['labels' => [], 'data' => []];

if (function_exists('get_pendaftar_per_bulan')) {
    $pendaftar_bulan = get_pendaftar_per_bulan();
    if (!empty($pendaftar_bulan)) {
        foreach ($pendaftar_bulan as $item) {
            $chart_data['labels'][] = $item['label'];
            $chart_data['data'][] = $item['jumlah'];
        }
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if (empty($chart_data['labels']) && $koneksi) {
    // Generate 6 bulan terakhir
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $date = date('Y-m', strtotime("-$i months"));
        $month_name = date('M Y', strtotime("-$i months"));
        $months[] = ['period' => $date, 'label' => $month_name];
    }
    
    // Query untuk mendapatkan jumlah pendaftaran per bulan
    foreach ($months as $month) {
        $chart_data['labels'][] = $month['label'];
        $month_period = pg_escape_string($koneksi, $month['period']);
        $query_chart = "SELECT COUNT(*) as total 
                        FROM pendaftaran 
                        WHERE DATE_TRUNC('month', tanggal_daftar) = DATE_TRUNC('month', DATE '{$month_period}-01')";
        $result_chart = pg_query($koneksi, $query_chart);
        if ($result_chart && pg_num_rows($result_chart) > 0) {
            $row_chart = pg_fetch_assoc($result_chart);
            $chart_data['data'][] = (int)$row_chart['total'];
        } else {
            $chart_data['data'][] = 0;
        }
    }
}

// Encode JSON dengan escape untuk keamanan
$chart_json = json_encode($chart_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50 admin-section-bg section-transparent">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Admin Dashboard</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Selamat Datang, <strong><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></strong>. Kelola semua aspek laboratorium dari sini.</p>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-50">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/anggota/index.php" style="text-decoration: none; display: block;">
                    <div class="stat-card blue wow fadeInUp" data-wow-delay=".2s" style="cursor: pointer;">
                        <div class="stat-icon">
                            <i class="lni lni-users"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['anggota']; ?></div>
                        <div class="stat-label">Total Anggota</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/pendaftar/index.php" style="text-decoration: none; display: block;">
                    <div class="stat-card green wow fadeInUp" data-wow-delay=".3s" style="cursor: pointer;">
                        <div class="stat-icon">
                            <i class="lni lni-graduation"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['pendaftar']; ?></div>
                        <div class="stat-label">Total Pendaftar</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/pendaftar/index.php?status=pending" style="text-decoration: none; display: block;">
                    <div class="stat-card orange wow fadeInUp" data-wow-delay=".4s" style="cursor: pointer;">
                        <div class="stat-icon">
                            <i class="lni lni-alarm"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['pendaftar_pending']; ?></div>
                        <div class="stat-label">Pending Review</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/jabatan/index.php" style="text-decoration: none; display: block;">
                    <div class="stat-card purple wow fadeInUp" data-wow-delay=".5s" style="cursor: pointer;">
                        <div class="stat-icon">
                            <i class="lni lni-briefcase"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['jabatan']; ?></div>
                        <div class="stat-label">Jabatan Tersedia</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/blog/index.php" style="text-decoration: none; display: block;">
                    <div class="stat-card blue wow fadeInUp" data-wow-delay=".6s" style="cursor: pointer; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="stat-icon">
                            <i class="lni lni-text-format"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['blog_published']; ?></div>
                        <div class="stat-label">Blog Published</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/blog/index.php?status=draft" style="text-decoration: none; display: block;">
                    <div class="stat-card orange wow fadeInUp" data-wow-delay=".7s" style="cursor: pointer; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="stat-icon">
                            <i class="lni lni-file"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['blog_draft']; ?></div>
                        <div class="stat-label">Blog Draft</div>
                    </div>
                </a>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <a href="../../module/user/index.php" style="text-decoration: none; display: block;">
                    <div class="stat-card green wow fadeInUp" data-wow-delay=".8s" style="cursor: pointer; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                        <div class="stat-icon">
                            <i class="lni lni-user"></i>
                        </div>
                        <div class="stat-number"><?php echo $stats['user']; ?></div>
                        <div class="stat-label">Total User</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-xl-8 col-lg-8 mb-30">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".6s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
                    <h4 style="color: #2c3e50; margin-bottom: 25px; font-weight: 600;">
                        <i class="lni lni-list mr-2" style="color: #2F80ED;"></i> Pendaftar Terbaru
                    </h4>
                    <?php if (count($recent_pendaftar) > 0): ?>
                        <?php 
                        $delay = 0.7;
                        foreach ($recent_pendaftar as $index => $pendaftar): 
                            $delay += 0.1;
                        ?>
                            <div class="activity-item wow fadeInUp" data-wow-delay="<?php echo number_format($delay, 1); ?>s" style="padding: 15px; border-left: 3px solid #2F80ED; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                                <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                    <?php echo htmlspecialchars($pendaftar['nama']); ?>
                                    <?php if ($pendaftar['status'] === 'pending'): ?>
                                        <span class="status-badge status-badge-pending status-badge-small" style="background: #F2994A; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 8px;">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                                    <i class="lni lni-id-card mr-1"></i> NIM: <?php echo htmlspecialchars($pendaftar['nim']); ?> | 
                                    <i class="lni lni-calendar mr-1"></i> <?php echo date('d/m/Y', strtotime($pendaftar['tanggal_daftar'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-20 wow fadeInUp" data-wow-delay="<?php echo number_format($delay + 0.1, 1); ?>s">
                            <a href="../../module/pendaftar/index.php" class="button button-sm radius-10">Lihat Semua Pendaftar</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="lni lni-inbox admin-table-empty-icon" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                            <p class="mt-3 text-muted">Belum ada data pendaftar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-4 col-lg-4 mb-30">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".7s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <h4 style="color: #2c3e50; margin-bottom: 25px; font-weight: 600;">
                        <i class="lni lni-bolt mr-2" style="color: #F2994A;"></i> Quick Actions
                    </h4>
                    <div class="activity-item activity-item-green" style="padding: 15px; border-left: 3px solid #27AE60; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-plus-circle mr-2" style="color: #27AE60;"></i> Tambah Anggota Baru
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/anggota/index.php" class="activity-link activity-link-green" style="color: #27AE60; text-decoration: none; font-weight: 500;">Klik untuk menambah →</a>
                        </div>
                    </div>
                    <div class="activity-item activity-item-orange" style="padding: 15px; border-left: 3px solid #F2994A; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-eye mr-2" style="color: #F2994A;"></i> Review Pendaftar
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/pendaftar/index.php" class="activity-link activity-link-orange" style="color: #F2994A; text-decoration: none; font-weight: 500;"><?php echo $stats['pendaftar_pending']; ?> pending →</a>
                        </div>
                    </div>
                    <div class="activity-item activity-item-purple" style="padding: 15px; border-left: 3px solid #9B59B6; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-briefcase mr-2" style="color: #9B59B6;"></i> Kelola Jabatan
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/jabatan/index.php" class="activity-link activity-link-purple" style="color: #9B59B6; text-decoration: none; font-weight: 500;">Klik untuk mengelola →</a>
                        </div>
                    </div>
                    <div class="activity-item" style="padding: 15px; border-left: 3px solid #2F80ED; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-text-format mr-2" style="color: #2F80ED;"></i> Kelola Blog
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/blog/index.php" class="activity-link" style="color: #2F80ED; text-decoration: none; font-weight: 500;">Klik untuk mengelola →</a>
                        </div>
                    </div>
                    <div class="activity-item" style="padding: 15px; border-left: 3px solid #667eea; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-cog mr-2" style="color: #667eea;"></i> Kelola Profil & Roadmap
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/profil/index.php" class="activity-link" style="color: #667eea; text-decoration: none; font-weight: 500;">Klik untuk mengelola →</a>
                        </div>
                    </div>
                    <div class="activity-item" style="padding: 15px; border-left: 3px solid #4facfe; margin-bottom: 0; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                        <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                            <i class="lni lni-user mr-2" style="color: #4facfe;"></i> Kelola User
                        </div>
                        <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                            <a href="../../module/user/index.php" class="activity-link" style="color: #4facfe; text-decoration: none; font-weight: 500;">Klik untuk mengelola →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Blog Posts -->
        <?php if (!empty($recent_blog)): ?>
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".8s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <h4 style="color: #2c3e50; margin-bottom: 25px; font-weight: 600;">
                        <i class="lni lni-text-format mr-2" style="color: #667eea;"></i> Blog Terbaru
                    </h4>
                    <?php foreach ($recent_blog as $index => $blog): ?>
                        <div class="activity-item wow fadeInUp" data-wow-delay="<?php echo number_format(0.9 + ($index * 0.1), 1); ?>s" style="padding: 15px; border-left: 3px solid #667eea; margin-bottom: 15px; background: #f8f9fa; border-radius: 5px; transition: all 0.3s ease; cursor: pointer;" onmouseover="this.style.background='#e9ecef'; this.style.transform='translateX(5px)'" onmouseout="this.style.background='#f8f9fa'; this.style.transform='translateX(0)'">
                            <div class="activity-name" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">
                                <a href="../../module/blog/edit.php?id=<?php echo $blog['id']; ?>" style="color: #2c3e50; text-decoration: none;">
                                    <?php echo htmlspecialchars($blog['title']); ?>
                                </a>
                            </div>
                            <div class="activity-meta" style="font-size: 13px; color: #6c757d;">
                                <i class="lni lni-calendar mr-1"></i> 
                                <?php 
                                $publish_date = $blog['published_at'] ?? $blog['created_at'] ?? '';
                                if ($publish_date) {
                                    echo date('d/m/Y', strtotime($publish_date));
                                } else {
                                    echo 'Belum dipublish';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="text-center mt-20 wow fadeInUp" data-wow-delay="<?php echo number_format(0.9 + (count($recent_blog) * 0.1) + 0.1, 1); ?>s">
                        <a href="../../module/blog/index.php" class="button button-sm radius-10">Lihat Semua Blog</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Chart Section -->
<section class="pt-50 pb-50 admin-section-bg" style="background: transparent; padding-top: 20px !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-30">
                <div class="chart-container wow fadeInUp" data-wow-delay=".2s">
                    <div class="chart-header">
                        <h4><i class="lni lni-bar-chart mr-2"></i> Grafik Pendaftaran Siswa</h4>
                        <p>Statistik jumlah siswa yang mendaftar ke laboratorium dalam 6 bulan terakhir</p>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="pendaftaranChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

<script>
// Chart data dari PHP
const chartData = <?php echo $chart_json; ?> || { labels: [], data: [] };
</script>
<script src="../../assets/js/classes/ChartManager.js"></script>
<script>
// Initialize ChartManager after all scripts are loaded
(function() {
    function initChartManager() {
        // Check if both Chart.js and ChartManager are loaded
        if (typeof Chart === 'undefined') {
            setTimeout(initChartManager, 100);
            return;
        }
        
        if (typeof ChartManager === 'undefined') {
            setTimeout(initChartManager, 100);
            return;
        }
        
        // Check if chart data is valid
        if (!chartData || !chartData.labels || !chartData.data) {
            return;
        }
        
        // Check if canvas exists
        const canvas = document.getElementById('pendaftaranChart');
        if (!canvas) {
            setTimeout(initChartManager, 100);
            return;
        }
        
        // Initialize ChartManager
        try {
            window.chartManager = new ChartManager('pendaftaranChart', chartData);
        } catch (error) {
            // Silent fail
        }
    }
    
    // Start initialization after window load
    if (document.readyState === 'complete') {
        // Already loaded, start immediately
        setTimeout(initChartManager, 100);
    } else {
        // Wait for window load
        window.addEventListener('load', function() {
            setTimeout(initChartManager, 100);
        });
    }
})();

// Initialize WOW.js for animations
(function() {
    function initWOW() {
        if (typeof WOW !== 'undefined') {
            // Destroy existing WOW instance if any
            if (window.wowInstance) {
                window.wowInstance = null;
            }
            // Initialize WOW
            window.wowInstance = new WOW({
                boxClass: 'wow',
                animateClass: 'animated',
                offset: 0,
                mobile: true,
                live: true
            });
            window.wowInstance.init();
        } else {
            // Retry if WOW.js not loaded yet
            setTimeout(initWOW, 100);
        }
    }
    
    // Initialize WOW after scripts are loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initWOW, 200);
        });
    } else {
        setTimeout(initWOW, 200);
    }
    
    // Also try on window load as backup
    window.addEventListener('load', function() {
        setTimeout(initWOW, 100);
    });
})();
</script>

