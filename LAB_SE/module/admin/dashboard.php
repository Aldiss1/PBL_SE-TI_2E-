<?php
$page_title = "Admin Dashboard - Web Profile Laboratorium SE";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
include __DIR__ . '/../../template/admin_header.php';

// Ambil statistik dari database
$stats = [
    'anggota' => 0,
    'pendaftar' => 0,
    'pendaftar_pending' => 0,
    'jabatan' => 0
];

// Hitung total anggota
$table_check_anggota = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'anggota'
)";
$check_anggota = pg_query($koneksi, $table_check_anggota);
if ($check_anggota) {
    $row_check = pg_fetch_assoc($check_anggota);
    if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
        $query_anggota = "SELECT COUNT(*) as total FROM anggota";
        $result_anggota = pg_query($koneksi, $query_anggota);
        if ($result_anggota) {
            $row = pg_fetch_assoc($result_anggota);
            $stats['anggota'] = (int)$row['total'];
        }
    }
}

// Hitung total pendaftar
$table_check_pendaftar = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'pendaftaran'
)";
$check_pendaftar = pg_query($koneksi, $table_check_pendaftar);
if ($check_pendaftar) {
    $row_check = pg_fetch_assoc($check_pendaftar);
    if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
        $query_pendaftar = "SELECT COUNT(*) as total FROM pendaftaran";
        $result_pendaftar = pg_query($koneksi, $query_pendaftar);
        if ($result_pendaftar) {
            $row = pg_fetch_assoc($result_pendaftar);
            $stats['pendaftar'] = (int)$row['total'];
        }
        
        // Hitung pendaftar pending
        $query_pending = "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'pending'";
        $result_pending = pg_query($koneksi, $query_pending);
        if ($result_pending) {
            $row = pg_fetch_assoc($result_pending);
            $stats['pendaftar_pending'] = (int)$row['total'];
        }
    }
}

// Hitung total jabatan
$table_check_jabatan = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'jabatan'
)";
$check_jabatan = pg_query($koneksi, $table_check_jabatan);
if ($check_jabatan) {
    $row_check = pg_fetch_assoc($check_jabatan);
    if ($row_check['exists'] === 't' || $row_check['exists'] === true) {
        $query_jabatan = "SELECT COUNT(*) as total FROM jabatan";
        $result_jabatan = pg_query($koneksi, $query_jabatan);
        if ($result_jabatan) {
            $row = pg_fetch_assoc($result_jabatan);
            $stats['jabatan'] = (int)$row['total'];
        }
    }
}

// Ambil pendaftar terbaru (5 terakhir)
$recent_pendaftar = [];
$query_recent = "SELECT nama, nim, jurusan, tanggal_daftar, status FROM pendaftaran ORDER BY tanggal_daftar DESC LIMIT 5";
$result_recent = pg_query($koneksi, $query_recent);
if ($result_recent && pg_num_rows($result_recent) > 0) {
    while ($row = pg_fetch_assoc($result_recent)) {
        $recent_pendaftar[] = $row;
    }
}
?>

<section class="pt-150 pb-50" style="background: #f8f9fa; margin-top: 80px;">
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
                                    <i class="lni lni-graduation mr-1"></i> <?php echo htmlspecialchars($pendaftar['jurusan']); ?> | 
                                    <i class="lni lni-calendar mr-1"></i> <?php echo date('d/m/Y', strtotime($pendaftar['tanggal_daftar'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-20">
                            <a href="../../list-pendaftar.php" class="button button-sm radius-10">Lihat Semua Pendaftar</a>
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
                            <a href="../../list-pendaftar.php" style="color: #F2994A; text-decoration: none;"><?php echo $stats['pendaftar_pending']; ?> pending →</a>
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

<!-- Module Management Section -->
<section id="admin-menu" class="pt-50 pb-100" style="background: #ffffff;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 mb-50">
                <div class="section-title text-center">
                    <h3 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Modul Manajemen</h3>
                    <p class="wow fadeInUp" data-wow-delay=".3s">Akses cepat ke semua fitur manajemen laboratorium</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                <div class="module-card wow fadeInUp" data-wow-delay=".4s">
                    <div class="module-icon">
                        <i class="lni lni-users"></i>
                    </div>
                    <h5>Kelola Anggota</h5>
                    <p>Tambah, edit, atau hapus data anggota laboratorium. Kelola profil dan informasi anggota.</p>
                    <a href="../../module/anggota/index.php" class="button button-sm radius-10 mt-20">Mulai Kelola</a>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                <div class="module-card wow fadeInUp" data-wow-delay=".5s">
                    <div class="module-icon">
                        <i class="lni lni-briefcase"></i>
                    </div>
                    <h5>Kelola Jabatan</h5>
                    <p>Tambah, edit, atau hapus data jabatan anggota. Atur struktur organisasi laboratorium.</p>
                    <a href="../../module/jabatan/index.php" class="button button-sm radius-10 mt-20">Mulai Kelola</a>
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6 mb-30">
                <div class="module-card wow fadeInUp" data-wow-delay=".6s">
                    <div class="module-icon">
                        <i class="lni lni-list"></i>
                    </div>
                    <h5>Kelola Pendaftar</h5>
                    <p>Lihat dan kelola data mahasiswa yang mendaftar ke laboratorium. Review dan verifikasi pendaftaran.</p>
                    <a href="../../list-pendaftar.php" class="button button-sm radius-10 mt-20">Lihat Daftar</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

