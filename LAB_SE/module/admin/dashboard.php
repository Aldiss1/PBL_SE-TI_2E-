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
    'jabatan' => 0
];
$recent_pendaftar = [];

if (file_exists(__DIR__ . '/../../fungsi/materialized_views_simple.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_simple.php';
    $stats_mv = get_stats_simple();
    if ($stats_mv) {
        $stats = [
            'anggota' => (int)($stats_mv['total_anggota'] ?? 0),
            'pendaftar' => (int)($stats_mv['total_pendaftar'] ?? 0),
            'pendaftar_pending' => (int)($stats_mv['pendaftar_pending'] ?? 0),
            'jabatan' => (int)($stats_mv['total_jabatan'] ?? 0)
        ];
        $recent_pendaftar = get_pendaftar_terbaru(5);
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if (empty($recent_pendaftar) && $koneksi) {
    // Hitung statistik (sederhanakan dengan satu query)
    $query_stats = "SELECT 
        (SELECT COUNT(*) FROM anggota) as anggota,
        (SELECT COUNT(*) FROM pendaftaran) as pendaftar,
        (SELECT COUNT(*) FROM pendaftaran WHERE status = 'pending') as pendaftar_pending,
        (SELECT COUNT(*) FROM jabatan) as jabatan";
    $result_stats = pg_query($koneksi, $query_stats);
    if ($result_stats && pg_num_rows($result_stats) > 0) {
        $row = pg_fetch_assoc($result_stats);
        $stats['anggota'] = (int)$row['anggota'];
        $stats['pendaftar'] = (int)$row['pendaftar'];
        $stats['pendaftar_pending'] = (int)$row['pendaftar_pending'];
        $stats['jabatan'] = (int)$row['jabatan'];
    }
    
    // Ambil pendaftar terbaru (5 terakhir)
    $query_recent = "SELECT nama, nim, tanggal_daftar, status FROM pendaftaran ORDER BY tanggal_daftar DESC LIMIT 5";
    $result_recent = pg_query($koneksi, $query_recent);
    if ($result_recent && pg_num_rows($result_recent) > 0) {
        while ($row = pg_fetch_assoc($result_recent)) {
            $recent_pendaftar[] = $row;
        }
    }
}

// Ambil data pendaftaran per bulan (6 bulan terakhir)
$chart_data = [
    'labels' => [],
    'data' => []
];

// Generate 6 bulan terakhir
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $month_name = date('M Y', strtotime("-$i months"));
    $months[] = [
        'period' => $date,
        'label' => $month_name
    ];
}

// Query untuk mendapatkan jumlah pendaftaran per bulan
foreach ($months as $month) {
    $chart_data['labels'][] = $month['label'];
    
    // Query dengan format date yang benar untuk PostgreSQL
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
                <div class="stat-card blue wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-icon">
                        <i class="lni lni-users"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['anggota']; ?></div>
                    <div class="stat-label">Total Anggota</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <div class="stat-card green wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-icon">
                        <i class="lni lni-graduation"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pendaftar']; ?></div>
                    <div class="stat-label">Total Pendaftar</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <div class="stat-card orange wow fadeInUp" data-wow-delay=".4s">
                    <div class="stat-icon">
                        <i class="lni lni-alarm"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['pendaftar_pending']; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 mb-30">
                <div class="stat-card purple wow fadeInUp" data-wow-delay=".5s">
                    <div class="stat-icon">
                        <i class="lni lni-briefcase"></i>
                    </div>
                    <div class="stat-number"><?php echo $stats['jabatan']; ?></div>
                    <div class="stat-label">Jabatan Tersedia</div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-xl-8 col-lg-8 mb-30">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".6s">
                    <h4><i class="lni lni-list mr-2"></i> Pendaftar Terbaru</h4>
                    <?php if (count($recent_pendaftar) > 0): ?>
                        <?php 
                        $delay = 0.7;
                        foreach ($recent_pendaftar as $index => $pendaftar): 
                            $delay += 0.1;
                        ?>
                            <div class="activity-item wow fadeInUp" data-wow-delay="<?php echo number_format($delay, 1); ?>s">
                                <div class="activity-name">
                                    <?php echo htmlspecialchars($pendaftar['nama']); ?>
                                    <?php if ($pendaftar['status'] === 'pending'): ?>
                                        <span class="status-badge status-badge-pending status-badge-small">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-meta">
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
                            <i class="lni lni-inbox admin-table-empty-icon"></i>
                            <p class="mt-3 text-muted">Belum ada data pendaftar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-4 col-lg-4 mb-30">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".7s">
                    <h4><i class="lni lni-bolt mr-2"></i> Quick Actions</h4>
                    <div class="activity-item activity-item-green">
                        <div class="activity-name">
                            <i class="lni lni-plus-circle mr-2"></i> Tambah Anggota Baru
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/anggota/index.php" class="activity-link activity-link-green">Klik untuk menambah →</a>
                        </div>
                    </div>
                    <div class="activity-item activity-item-orange">
                        <div class="activity-name">
                            <i class="lni lni-eye mr-2"></i> Review Pendaftar
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/pendaftar/index.php" class="activity-link activity-link-orange"><?php echo $stats['pendaftar_pending']; ?> pending →</a>
                        </div>
                    </div>
                    <div class="activity-item activity-item-purple">
                        <div class="activity-name">
                            <i class="lni lni-briefcase mr-2"></i> Kelola Jabatan
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/jabatan/index.php" class="activity-link activity-link-purple">Klik untuk mengelola →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            console.log('Waiting for Chart.js...');
            setTimeout(initChartManager, 100);
            return;
        }
        
        if (typeof ChartManager === 'undefined') {
            console.log('Waiting for ChartManager...');
            setTimeout(initChartManager, 100);
            return;
        }
        
        // Check if chart data is valid
        if (!chartData || !chartData.labels || !chartData.data) {
            console.warn('Chart data is invalid:', chartData);
            return;
        }
        
        // Check if canvas exists
        const canvas = document.getElementById('pendaftaranChart');
        if (!canvas) {
            console.log('Waiting for canvas element...');
            setTimeout(initChartManager, 100);
            return;
        }
        
        // Initialize ChartManager
        try {
            window.chartManager = new ChartManager('pendaftaranChart', chartData);
            console.log('ChartManager initialized successfully');
        } catch (error) {
            console.error('Error initializing ChartManager:', error);
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
            console.log('WOW.js initialized for animations');
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

