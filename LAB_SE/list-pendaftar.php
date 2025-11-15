<?php
$page_title = "Kelola Pendaftar - Admin";
require_once __DIR__ . '/config/koneksi.php';
require_once __DIR__ . '/cek_login.php';
require_once __DIR__ . '/config/anti_injection.php';
require_once __DIR__ . '/fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'pendaftar';
$base_path = '';
$menu_items = [
    'dashboard.php' => [
        'title' => 'Dashboard',
        'icon' => 'lni lni-home',
        'link' => 'module/admin/dashboard.php',
        'active' => false
    ],
    'anggota' => [
        'title' => 'Kelola Anggota',
        'icon' => 'lni lni-users',
        'link' => 'module/anggota/index.php',
        'active' => false
    ],
    'jabatan' => [
        'title' => 'Kelola Jabatan',
        'icon' => 'lni lni-briefcase',
        'link' => 'module/jabatan/index.php',
        'active' => false
    ],
    'pendaftar' => [
        'title' => 'Kelola Pendaftar',
        'icon' => 'lni lni-list',
        'link' => 'list-pendaftar.php',
        'active' => true
    ],
    'blog' => [
        'title' => 'Kelola Blog',
        'icon' => 'lni lni-text-format',
        'link' => 'module/blog/index.php',
        'active' => false
    ]
];

// Set base_path untuk sidebar
if (!isset($base_path)) {
    $base_path = '';
}

// Ambil statistik pendaftar
$stats = [
    'total' => 0,
    'pending' => 0,
    'accepted' => 0,
    'rejected' => 0
];

// Cek apakah tabel pendaftaran ada
$table_check = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'pendaftaran'
)";
$check_table = pg_query($koneksi, $table_check);
$has_table = false;
if ($check_table) {
    $row_check = pg_fetch_assoc($check_table);
    $has_table = ($row_check['exists'] === 't' || $row_check['exists'] === true || $row_check['exists'] === 'true');
}

if ($has_table) {
    // Hitung total pendaftar
    $query_total = "SELECT COUNT(*) as total FROM pendaftaran";
    $result_total = pg_query($koneksi, $query_total);
    if ($result_total) {
        $row_total = pg_fetch_assoc($result_total);
        $stats['total'] = (int)$row_total['total'];
    }
    
    // Hitung pending
    $query_pending = "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'pending'";
    $result_pending = pg_query($koneksi, $query_pending);
    if ($result_pending) {
        $row_pending = pg_fetch_assoc($result_pending);
        $stats['pending'] = (int)$row_pending['total'];
    }
    
    // Hitung accepted
    $query_accepted = "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'accepted'";
    $result_accepted = pg_query($koneksi, $query_accepted);
    if ($result_accepted) {
        $row_accepted = pg_fetch_assoc($result_accepted);
        $stats['accepted'] = (int)$row_accepted['total'];
    }
    
    // Hitung rejected
    $query_rejected = "SELECT COUNT(*) as total FROM pendaftaran WHERE status = 'rejected'";
    $result_rejected = pg_query($koneksi, $query_rejected);
    if ($result_rejected) {
        $row_rejected = pg_fetch_assoc($result_rejected);
        $stats['rejected'] = (int)$row_rejected['total'];
    }
}

// Filter berdasarkan status (ambil dulu sebelum proses update)
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

// Proses update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $new_status = isset($_POST['status']) ? clean_input($_POST['status']) : '';
    $current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
    
    if ($id > 0 && in_array($new_status, ['accepted', 'rejected', 'pending'])) {
        $id_escaped = pg_escape_string($koneksi, $id);
        $status_escaped = pg_escape_string($koneksi, $new_status);
        
        $update_query = "UPDATE pendaftaran SET status = '$status_escaped', updated_at = NOW()";
        if ($new_status === 'accepted' || $new_status === 'rejected') {
            $update_query .= ", tanggal_review = NOW()";
        }
        $update_query .= " WHERE id = $id_escaped";
        
        $update_result = pg_query($koneksi, $update_query);
        if ($update_result) {
            $status_text = $new_status === 'accepted' ? 'diterima' : ($new_status === 'rejected' ? 'ditolak' : 'diubah');
            pesan_kilat('Status pendaftar berhasil ' . $status_text . '!', 'success');
        } else {
            pesan_kilat('Gagal mengupdate status. Error: ' . pg_last_error($koneksi), 'error');
        }
        
        $redirect_url = 'list-pendaftar.php';
        if ($current_filter !== 'all') {
            $redirect_url .= '?status=' . $current_filter;
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

// Query pendaftar
$pendaftar = [];
if ($has_table) {
    if ($status_filter === 'all') {
        $query = "SELECT * FROM pendaftaran ORDER BY tanggal_daftar DESC";
    } else {
        $status_filter_escaped = pg_escape_string($koneksi, $status_filter);
        $query = "SELECT * FROM pendaftaran WHERE status = '$status_filter_escaped' ORDER BY tanggal_daftar DESC";
    }
    
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $pendaftar[] = $row;
        }
    }
}

include __DIR__ . '/template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50" style="background: #f8f9fa; min-height: calc(100vh - 80px); padding-top: 40px !important;">
    <div class="container">
        <!-- Header Section -->
        <div class="row mb-40">
            <div class="col-lg-12">
                <div class="section-title mb-30">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s" style="color: #2c3e50; font-size: 32px; font-weight: 700;">
                        <i class="lni lni-list" style="color: #2F80ED; margin-right: 10px;"></i>
                        Kelola Pendaftar
                    </h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s" style="color: #6c757d; font-size: 16px; margin: 0;">
                        Daftar mahasiswa yang telah mendaftar ke Laboratorium Software Engineering
                    </p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <!-- Statistik Cards -->
        <div class="row mb-40">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-card-blue wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-icon">
                        <i class="lni lni-users"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                    <div class="stat-label">Total Pendaftar</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-card-orange wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-icon">
                        <i class="lni lni-hourglass"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-card-green wow fadeInUp" data-wow-delay=".4s">
                    <div class="stat-icon">
                        <i class="lni lni-checkmark-circle"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['accepted']); ?></div>
                    <div class="stat-label">Diterima</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-card-red wow fadeInUp" data-wow-delay=".5s">
                    <div class="stat-icon">
                        <i class="lni lni-close"></i>
                    </div>
                    <div class="stat-number"><?php echo number_format($stats['rejected']); ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="filter-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="filter-header">
                        <i class="lni lni-filter"></i>
                        <span>Filter Status</span>
                    </div>
                    <div class="filter-buttons">
                        <a href="?status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                            <i class="lni lni-list"></i> Semua
                            <span class="badge"><?php echo $stats['total']; ?></span>
                        </a>
                        <a href="?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                            <i class="lni lni-hourglass"></i> Pending
                            <span class="badge"><?php echo $stats['pending']; ?></span>
                        </a>
                        <a href="?status=accepted" class="filter-btn <?php echo $status_filter === 'accepted' ? 'active' : ''; ?>">
                            <i class="lni lni-checkmark-circle"></i> Diterima
                            <span class="badge"><?php echo $stats['accepted']; ?></span>
                        </a>
                        <a href="?status=rejected" class="filter-btn <?php echo $status_filter === 'rejected' ? 'active' : ''; ?>">
                            <i class="lni lni-close"></i> Ditolak
                            <span class="badge"><?php echo $stats['rejected']; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Pendaftar -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-card wow fadeInUp" data-wow-delay=".3s">
                    <div class="table-header">
                        <h4>
                            <i class="lni lni-graduation"></i>
                            Daftar Pendaftar
                        </h4>
                        <span class="table-count">Total: <?php echo count($pendaftar); ?> data</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-pendaftar">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama Lengkap</th>
                                    <th>NIM</th>
                                    <th>Jurusan</th>
                                    <th>Minat</th>
                                    <th>Status</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($pendaftar) > 0): ?>
                                    <?php 
                                    $no = 1;
                                    foreach ($pendaftar as $p): 
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-avatar">
                                                    <?php echo strtoupper(substr($p['nama'], 0, 1)); ?>
                                                </div>
                                                <div class="user-details">
                                                    <strong><?php echo htmlspecialchars($p['nama']); ?></strong>
                                                    <?php if (isset($p['email']) && !empty($p['email'])): ?>
                                                    <small><?php echo htmlspecialchars($p['email']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="nim-badge"><?php echo htmlspecialchars($p['nim']); ?></span>
                                        </td>
                                        <td>
                                            <span class="jurusan-tag"><?php echo htmlspecialchars($p['jurusan']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($p['minat'])): ?>
                                                <div class="minat-text" title="<?php echo htmlspecialchars($p['minat']); ?>">
                                                    <?php echo htmlspecialchars(substr($p['minat'], 0, 50)); ?>
                                                    <?php echo strlen($p['minat']) > 50 ? '...' : ''; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $p['status'] ?? 'pending';
                                            $status_class = '';
                                            $status_text = '';
                                            $status_icon = '';
                                            switch ($status) {
                                                case 'accepted':
                                                    $status_class = 'status-accepted';
                                                    $status_text = 'Diterima';
                                                    $status_icon = 'lni-checkmark-circle';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'status-rejected';
                                                    $status_text = 'Ditolak';
                                                    $status_icon = 'lni-close';
                                                    break;
                                                case 'pending':
                                                default:
                                                    $status_class = 'status-pending';
                                                    $status_text = 'Pending';
                                                    $status_icon = 'lni-hourglass';
                                                    break;
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $status_class; ?>">
                                                <i class="lni <?php echo $status_icon; ?>"></i>
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="date-info">
                                                <strong><?php echo date('d/m/Y', strtotime($p['tanggal_daftar'])); ?></strong>
                                                <small><?php echo date('H:i', strtotime($p['tanggal_daftar'])); ?> WIB</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-action btn-view" title="Lihat Detail" onclick="viewDetail(<?php echo $p['id']; ?>)">
                                                    <i class="lni lni-eye"></i>
                                                </button>
                                                <?php if ($status === 'pending'): ?>
                                                <button class="btn-action btn-accept" title="Terima" onclick="updateStatus(<?php echo $p['id']; ?>, 'accepted')">
                                                    <i class="lni lni-checkmark-circle"></i>
                                                </button>
                                                <button class="btn-action btn-reject" title="Tolak" onclick="updateStatus(<?php echo $p['id']; ?>, 'rejected')">
                                                    <i class="lni lni-close"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="empty-state">
                                            <div class="empty-content">
                                                <i class="lni lni-inbox"></i>
                                                <h4>Tidak Ada Data</h4>
                                                <p>
                                                    <?php if (!$has_table): ?>
                                                        Tabel database belum dibuat. Silakan jalankan <a href="database.sql">database.sql</a> terlebih dahulu.
                                                    <?php elseif ($status_filter !== 'all'): ?>
                                                        Tidak ada pendaftar dengan status "<?php echo htmlspecialchars(ucfirst($status_filter)); ?>".
                                                    <?php else: ?>
                                                        Belum ada data pendaftar. Mahasiswa yang mendaftar akan muncul di sini.
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<style>
/* Stat Cards */
.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px;
    color: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 3s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.5; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.stat-card-blue {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
}

.stat-card-orange {
    background: linear-gradient(135deg, #F2994A 0%, #E67E22 100%);
}

.stat-card-green {
    background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%);
}

.stat-card-red {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.stat-card .stat-icon {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.stat-card .stat-number {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.stat-card .stat-label {
    font-size: 16px;
    opacity: 0.95;
    font-weight: 500;
    position: relative;
    z-index: 1;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Filter Card */
.filter-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.filter-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.filter-header i {
    font-size: 24px;
    color: #2F80ED;
    margin-right: 10px;
}

.filter-header span {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.filter-btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 20px;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    background: #f8f9fa;
    transition: all 0.3s ease;
    font-weight: 500;
    border: 2px solid transparent;
    position: relative;
}

.filter-btn i {
    margin-right: 8px;
    font-size: 16px;
}

.filter-btn .badge {
    margin-left: 8px;
    padding: 4px 8px;
    background: rgba(44, 62, 80, 0.1);
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.filter-btn:hover {
    background: #2F80ED;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(47, 128, 237, 0.3);
    border-color: #2F80ED;
}

.filter-btn:hover .badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.filter-btn.active {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
    color: white;
    font-weight: 600;
    border-color: #2F80ED;
    box-shadow: 0 5px 15px rgba(47, 128, 237, 0.3);
}

.filter-btn.active .badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Table Card */
.table-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    border: 1px solid #e9ecef;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 2px solid #dee2e6;
}

.table-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.table-header h4 i {
    color: #2F80ED;
    margin-right: 10px;
    font-size: 24px;
}

.table-count {
    background: #2F80ED;
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.table-pendaftar {
    margin: 0;
    width: 100%;
}

.table-pendaftar thead {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
}

.table-pendaftar thead th {
    padding: 18px 20px;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.5px;
    border: none;
}

.table-pendaftar tbody tr {
    transition: all 0.2s ease;
    border-bottom: 1px solid #f0f0f0;
}

.table-pendaftar tbody tr:hover {
    background: #f8f9fa;
    transform: scale(1.001);
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.table-pendaftar tbody td {
    padding: 20px;
    vertical-align: middle;
    border: none;
}

/* User Info */
.user-info {
    display: flex;
    align-items: center;
}

.user-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 18px;
    margin-right: 12px;
    box-shadow: 0 3px 10px rgba(47, 128, 237, 0.3);
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-details strong {
    color: #2c3e50;
    font-size: 15px;
    margin-bottom: 3px;
}

.user-details small {
    color: #6c757d;
    font-size: 12px;
}

/* Badges */
.nim-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    font-family: 'Courier New', monospace;
}

.jurusan-tag {
    background: #f3e5f5;
    color: #7b1fa2;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.minat-text {
    color: #6c757d;
    font-size: 13px;
    line-height: 1.5;
    max-width: 200px;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge i {
    margin-right: 6px;
    font-size: 14px;
}

.status-pending {
    background: linear-gradient(135deg, #F2994A 0%, #E67E22 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(242, 153, 74, 0.3);
}

.status-accepted {
    background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
}

.status-rejected {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(231, 76, 60, 0.3);
}

/* Date Info */
.date-info {
    display: flex;
    flex-direction: column;
}

.date-info strong {
    color: #2c3e50;
    font-size: 14px;
    margin-bottom: 3px;
}

.date-info small {
    color: #999;
    font-size: 11px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    width: 35px;
    height: 35px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 16px;
    color: white;
}

.btn-view {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
}

.btn-view:hover {
    background: linear-gradient(135deg, #1E5FCC 0%, #2F80ED 100%);
    transform: scale(1.1);
    box-shadow: 0 3px 10px rgba(47, 128, 237, 0.4);
}

.btn-accept {
    background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%);
}

.btn-accept:hover {
    background: linear-gradient(135deg, #1E8E4F 0%, #27AE60 100%);
    transform: scale(1.1);
    box-shadow: 0 3px 10px rgba(39, 174, 96, 0.4);
}

.btn-reject {
    background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.btn-reject:hover {
    background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
    transform: scale(1.1);
    box-shadow: 0 3px 10px rgba(231, 76, 60, 0.4);
}

/* Empty State */
.empty-state {
    padding: 60px 20px !important;
    text-align: center;
}

.empty-content i {
    font-size: 64px;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-content h4 {
    color: #6c757d;
    font-size: 20px;
    margin-bottom: 10px;
}

.empty-content p {
    color: #999;
    font-size: 14px;
    max-width: 500px;
    margin: 0 auto;
}

.empty-content a {
    color: #2F80ED;
    text-decoration: none;
    font-weight: 600;
}

.empty-content a:hover {
    text-decoration: underline;
}

/* Responsive */
@media (max-width: 991px) {
    .stat-card {
        margin-bottom: 20px;
    }
    
    .filter-buttons {
        justify-content: center;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .user-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .user-avatar {
        margin-bottom: 8px;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        overflow-x: auto;
    }
    
    .table-pendaftar {
        min-width: 800px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
function viewDetail(id) {
    // Bisa dikembangkan untuk menampilkan modal detail
    alert('Detail pendaftar ID: ' + id + '\n\nFitur detail lengkap akan segera tersedia.');
}

function updateStatus(id, status) {
    var statusText = status === 'accepted' ? 'menerima' : 'menolak';
    var confirmText = 'Yakin ingin ' + statusText + ' pendaftar ini?';
    
    if (confirm(confirmText)) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        var actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'update_status';
        form.appendChild(actionInput);
        
        var idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        form.appendChild(idInput);
        
        var statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        form.appendChild(statusInput);
        
        // Ambil current filter dari URL
        var urlParams = new URLSearchParams(window.location.search);
        var currentFilter = urlParams.get('status') || 'all';
        var filterInput = document.createElement('input');
        filterInput.type = 'hidden';
        filterInput.name = 'current_filter';
        filterInput.value = currentFilter;
        form.appendChild(filterInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include __DIR__ . '/template/admin_footer.php'; ?>
