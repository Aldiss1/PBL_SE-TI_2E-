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

// Ambil statistik dari database
$stats = [
    'anggota' => 0,
    'pendaftar' => 0,
    'pendaftar_pending' => 0,
    'jabatan' => 0
];

// Hitung total anggota
$query_anggota = "SELECT COUNT(*) as total FROM anggota";
$result_anggota = pg_query($koneksi, $query_anggota);
if ($result_anggota) {
    $row = pg_fetch_assoc($result_anggota);
    $stats['anggota'] = (int)$row['total'];
}

// Hitung total pendaftar
$query_pendaftar = "SELECT COUNT(*) as total FROM pendaftaran";
$result_pendaftar = pg_query($koneksi, $query_pendaftar);
if ($result_pendaftar) {
    $row = pg_fetch_assoc($result_pendaftar);
    $stats['pendaftar'] = (int)$row['total'];
    
    // Hitung pendaftar pending
    $query_pending = "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'pending'";
    $result_pending = pg_query($koneksi, $query_pending);
    if ($result_pending) {
        $row = pg_fetch_assoc($result_pending);
        $stats['pendaftar_pending'] = (int)$row['total'];
    }
}

// Hitung total jabatan
$query_jabatan = "SELECT COUNT(*) as total FROM jabatan";
$result_jabatan = pg_query($koneksi, $query_jabatan);
if ($result_jabatan) {
    $row = pg_fetch_assoc($result_jabatan);
    $stats['jabatan'] = (int)$row['total'];
}

// Ambil pendaftar terbaru (5 terakhir)
$recent_pendaftar = [];
$query_recent = "SELECT nama, nim, tanggal_daftar, status FROM pendaftaran ORDER BY tanggal_daftar DESC LIMIT 5";
$result_recent = pg_query($koneksi, $query_recent);
if ($result_recent && pg_num_rows($result_recent) > 0) {
    while ($row = pg_fetch_assoc($result_recent)) {
        $recent_pendaftar[] = $row;
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
<section class="pt-50 pb-50" style="padding-top: 30px !important; background: transparent;">
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
                        <?php foreach ($recent_pendaftar as $pendaftar): ?>
                            <div class="activity-item">
                                <div class="activity-name">
                                    <?php echo htmlspecialchars($pendaftar['nama']); ?>
                                    <?php if ($pendaftar['status'] === 'pending'): ?>
                                        <span class="badge badge-warning ml-2" style="background: #F2994A; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px;">Pending</span>
                                    <?php endif; ?>
                                </div>
                                <div class="activity-meta">
                                    <i class="lni lni-id-card mr-1"></i> NIM: <?php echo htmlspecialchars($pendaftar['nim']); ?> | 
                                    <i class="lni lni-calendar mr-1"></i> <?php echo date('d/m/Y', strtotime($pendaftar['tanggal_daftar'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-20">
                            <a href="../../module/pendaftar/index.php" class="button button-sm radius-10">Lihat Semua Pendaftar</a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                            <p class="mt-3 text-muted">Belum ada data pendaftar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-xl-4 col-lg-4 mb-30">
                <div class="recent-activity wow fadeInUp" data-wow-delay=".7s">
                    <h4><i class="lni lni-bolt mr-2"></i> Quick Actions</h4>
                    <div class="activity-item" style="border-left-color: #27AE60;">
                        <div class="activity-name">
                            <i class="lni lni-plus-circle mr-2"></i> Tambah Anggota Baru
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/anggota/index.php" style="color: #27AE60; text-decoration: none;">Klik untuk menambah →</a>
                        </div>
                    </div>
                    <div class="activity-item" style="border-left-color: #F2994A;">
                        <div class="activity-name">
                            <i class="lni lni-eye mr-2"></i> Review Pendaftar
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/pendaftar/index.php" style="color: #F2994A; text-decoration: none;"><?php echo $stats['pendaftar_pending']; ?> pending →</a>
                        </div>
                    </div>
                    <div class="activity-item" style="border-left-color: #9B59B6;">
                        <div class="activity-name">
                            <i class="lni lni-briefcase mr-2"></i> Kelola Jabatan
                        </div>
                        <div class="activity-meta">
                            <a href="../../module/jabatan/index.php" style="color: #9B59B6; text-decoration: none;">Klik untuk mengelola →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart Section -->
<section class="pt-50 pb-50" style="background: transparent; padding-top: 20px !important;">
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
// Chart.js Configuration - Tunggu semua resource dimuat
(function() {
    // Validasi data chart dari PHP
    const chartData = <?php echo $chart_json; ?> || { labels: [], data: [] };
    
    // Validasi struktur data
    if (!chartData.labels || !chartData.data) {
        console.error('Invalid chart data structure');
        return;
    }
    
    let initAttempts = 0;
    const maxAttempts = 50;
    
    function initChart() {
        initAttempts++;
        
        // Cek apakah Chart.js sudah dimuat
        if (typeof Chart === 'undefined') {
            if (initAttempts < maxAttempts) {
                setTimeout(initChart, 100);
            } else {
                console.error('Chart.js failed to load after maximum attempts');
            }
            return;
        }
        
        // Cek apakah canvas sudah ada
        const canvas = document.getElementById('pendaftaranChart');
        if (!canvas) {
            if (initAttempts < maxAttempts) {
                setTimeout(initChart, 100);
            } else {
                console.error('Chart canvas not found after maximum attempts');
            }
            return;
        }
        
        // Pastikan chart belum diinisialisasi
        if (canvas.chartInstance) {
            return;
        }
        
        try {
            const ctx = canvas.getContext('2d');
            
            // Destroy chart jika sudah ada
            if (canvas.chartInstance) {
                canvas.chartInstance.destroy();
            }
            
            // Buat chart baru
            canvas.chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: 'Jumlah Pendaftar',
                        data: chartData.data || [],
                        borderColor: '#2F80ED',
                        backgroundColor: 'rgba(47, 128, 237, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2F80ED',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#1E5FCC',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    aspectRatio: 2.5,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 14,
                                    weight: '500'
                                },
                                color: '#2c3e50'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            borderColor: '#2F80ED',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return 'Pendaftar: ' + context.parsed.y + ' siswa';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6c757d'
                            },
                            grid: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
            
            console.log('Chart initialized successfully with data:', chartData);
        } catch (error) {
            console.error('Error initializing chart:', error);
            console.error('Chart data:', chartData);
        }
    }
    
    // Inisialisasi chart setelah semua resource dimuat
    function startInit() {
        // Tunggu sedikit untuk memastikan semua script sudah dimuat
        setTimeout(function() {
            initChart();
        }, 200);
    }
    
    // Cek apakah document sudah ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startInit);
    } else {
        // Document sudah ready, tunggu window load
        if (document.readyState === 'complete') {
            startInit();
        } else {
            window.addEventListener('load', startInit);
        }
    }
})();
</script>


