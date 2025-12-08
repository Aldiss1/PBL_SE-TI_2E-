<?php
$page_title = "Kelola Penelitian - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'penelitian';
$menu_items = [
    'dashboard.php' => [
        'title' => 'Dashboard',
        'icon' => 'lni lni-home',
        'link' => '../../module/admin/dashboard.php',
        'active' => false
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
    ],
    'penelitian' => [
        'title' => 'Kelola Penelitian',
        'icon' => 'lni lni-microscope',
        'link' => '../../module/penelitian/index.php',
        'active' => true
    ],
    'publikasi' => [
        'title' => 'Kelola Publikasi',
        'icon' => 'lni lni-book',
        'link' => '../../module/publikasi/index.php',
        'active' => false
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Proses hapus
if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $query = "DELETE FROM penelitian WHERE id = $1";
    $result = pg_query_params($koneksi, $query, array($id));
    if ($result) {
        pesan_kilat('Penelitian berhasil dihapus!', 'success');
    } else {
        pesan_kilat('Gagal menghapus penelitian!', 'error');
    }
    header('Location: index.php');
    exit;
}

// Hitung statistik
$total_penelitian_query = "SELECT COUNT(*) as total FROM penelitian";
$total_penelitian_result = pg_query($koneksi, $total_penelitian_query);
$total_penelitian = 0;
if ($total_penelitian_result && pg_num_rows($total_penelitian_result) > 0) {
    $total_penelitian_row = pg_fetch_assoc($total_penelitian_result);
    $total_penelitian = (int)$total_penelitian_row['total'];
}

$penelitian_ongoing_query = "SELECT COUNT(*) as total FROM penelitian WHERE status = 'ongoing'";
$penelitian_ongoing_result = pg_query($koneksi, $penelitian_ongoing_query);
$penelitian_ongoing = 0;
if ($penelitian_ongoing_result && pg_num_rows($penelitian_ongoing_result) > 0) {
    $penelitian_ongoing_row = pg_fetch_assoc($penelitian_ongoing_result);
    $penelitian_ongoing = (int)$penelitian_ongoing_row['total'];
}

$penelitian_completed = $total_penelitian - $penelitian_ongoing;

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Ambil data penelitian
$penelitian = [];
$query = "SELECT * FROM penelitian";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $query .= " WHERE judul ILIKE '%$search_escaped%' OR deskripsi ILIKE '%$search_escaped%'";
}
$query .= " ORDER BY tahun DESC, id DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $penelitian[] = $row;
    }
}
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50 admin-section-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Penelitian</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data penelitian laboratorium</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <!-- Stat Cards -->
        <div class="row mb-30">
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-card-icon stat-card-icon-primary">
                        <i class="lni lni-microscope"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $total_penelitian; ?></h3>
                        <p class="stat-card-label">Total Penelitian</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-card-icon stat-card-icon-warning">
                        <i class="lni lni-hourglass"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $penelitian_ongoing; ?></h3>
                        <p class="stat-card-label">Sedang Berlangsung</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".4s">
                    <div class="stat-card-icon stat-card-icon-success">
                        <i class="lni lni-checkmark-circle"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $penelitian_completed; ?></h3>
                        <p class="stat-card-label">Selesai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-20">
            <div class="col-lg-12">
                <div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-search"></i>
                    <input type="text" id="searchPenelitian" class="jabatan-search-input" placeholder="Cari penelitian berdasarkan judul atau deskripsi..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php if (!empty($search_query)): ?>
                        <a href="index.php" class="jabatan-search-clear" title="Hapus pencarian">
                            <i class="lni lni-close"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="btn btn-primary btn-lg wow fadeInUp" data-wow-delay=".3s">
                    <i class="lni lni-plus me-2"></i> Tambah Penelitian
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php if (count($penelitian) > 0): ?>
                    <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>Judul</th>
                                    <th>Tahun</th>
                                    <th>Status</th>
                                    <th>Jumlah Tim</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($penelitian as $p): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong style="color: #2c3e50; font-size: 15px;"><?php echo htmlspecialchars($p['judul']); ?></strong>
                                        <?php if (!empty($p['deskripsi'])): ?>
                                            <br><small class="text-muted" style="font-size: 13px; line-height: 1.6;"><?php echo htmlspecialchars(substr($p['deskripsi'], 0, 100)); ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: #2c3e50; font-size: 15px;"><?php echo htmlspecialchars($p['tahun']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($p['status'] == 'ongoing'): ?>
                                            <span class="status-badge status-badge-warning">Sedang Berlangsung</span>
                                        <?php else: ?>
                                            <span class="status-badge status-badge-success">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="color: #6c757d; font-weight: 500;">
                                            <i class="lni lni-users" style="margin-right: 5px; color: #2F80ED;"></i>
                                            <?php echo htmlspecialchars($p['jumlah_tim']); ?> Peneliti
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="lni lni-pencil me-1"></i> Edit
                                            </a>
                                            <a href="index.php?hapus=1&id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus penelitian ini?');">
                                                <i class="lni lni-trash me-1"></i> Hapus
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state wow fadeInUp" data-wow-delay=".3s">
                        <i class="lni lni-microscope empty-state-icon"></i>
                        <h3 class="empty-state-title">Belum Ada Penelitian</h3>
                        <p class="empty-state-message">Mulai dengan menambahkan penelitian pertama ke dalam sistem.</p>
                        <a href="tambah.php" class="empty-state-action">
                            <i class="lni lni-plus"></i> Tambah Penelitian Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

<script>
// Search functionality
document.getElementById('searchPenelitian')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        const search = this.value.trim();
        if (search) {
            window.location.href = 'index.php?search=' + encodeURIComponent(search);
        } else {
            window.location.href = 'index.php';
        }
    }
});
</script>

