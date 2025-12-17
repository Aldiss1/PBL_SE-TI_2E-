<?php
$page_title = "Kelola Publikasi - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'publikasi';
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
        'active' => false
    ],
    'publikasi' => [
        'title' => 'Kelola Publikasi',
        'icon' => 'lni lni-book',
        'link' => '../../module/publikasi/index.php',
        'active' => true
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Proses hapus
if (isset($_GET['hapus']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Menggunakan stored procedure sp_delete_publikasi
    $query = "SELECT sp_delete_publikasi($1)";
    $result = pg_query_params($koneksi, $query, array($id));
    if ($result) {
        $row = pg_fetch_row($result);
        $deleted = $row[0] ?? false;
        if ($deleted) {
            // Refresh materialized views setelah delete publikasi
            if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
                require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
                if (function_exists('refresh_views_complete')) {
                    @refresh_views_complete();
                }
            }
            pesan_kilat('Publikasi berhasil dihapus!', 'success');
        } else {
            pesan_kilat('Gagal menghapus publikasi! Data tidak ditemukan.', 'error');
        }
    } else {
        pesan_kilat('Gagal menghapus publikasi! Error: ' . pg_last_error($koneksi), 'error');
    }
    header('Location: index.php');
    exit;
}

// Hitung statistik
$total_publikasi_query = "SELECT COUNT(*) as total FROM publikasi";
$total_publikasi_result = pg_query($koneksi, $total_publikasi_query);
$total_publikasi = 0;
if ($total_publikasi_result && pg_num_rows($total_publikasi_result) > 0) {
    $total_publikasi_row = pg_fetch_assoc($total_publikasi_result);
    $total_publikasi = (int)$total_publikasi_row['total'];
}

$publikasi_journal_query = "SELECT COUNT(*) as total FROM publikasi WHERE tipe = 'journal'";
$publikasi_journal_result = pg_query($koneksi, $publikasi_journal_query);
$publikasi_journal = 0;
if ($publikasi_journal_result && pg_num_rows($publikasi_journal_result) > 0) {
    $publikasi_journal_row = pg_fetch_assoc($publikasi_journal_result);
    $publikasi_journal = (int)$publikasi_journal_row['total'];
}

$publikasi_conference = $total_publikasi - $publikasi_journal;

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Ambil data publikasi
$publikasi = [];
$query = "SELECT * FROM publikasi";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $query .= " WHERE judul ILIKE '%$search_escaped%'";
}
$query .= " ORDER BY tahun DESC, id DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $publikasi[] = $row;
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Publikasi</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data publikasi laboratorium</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <!-- Stat Cards -->
        <div class="row mb-30">
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-card-icon stat-card-icon-primary">
                        <i class="lni lni-book"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $total_publikasi; ?></h3>
                        <p class="stat-card-label">Total Publikasi</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-card-icon stat-card-icon-info">
                        <i class="lni lni-files"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $publikasi_journal; ?></h3>
                        <p class="stat-card-label">Journal</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".4s">
                    <div class="stat-card-icon stat-card-icon-success">
                        <i class="lni lni-presentation"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $publikasi_conference; ?></h3>
                        <p class="stat-card-label">Conference</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-20">
            <div class="col-lg-12">
                <div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-search"></i>
                    <input type="text" id="searchPublikasi" class="jabatan-search-input" placeholder="Cari publikasi berdasarkan judul..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <i class="lni lni-plus me-2"></i> Tambah Publikasi
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <?php if (count($publikasi) > 0): ?>
                    <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>NO</th>
                                    <th>Judul</th>
                                    <th>Tipe</th>
                                    <th>Tahun</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($publikasi as $p): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <strong style="color: #2c3e50; font-size: 15px; line-height: 1.6;"><?php echo htmlspecialchars($p['judul']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($p['tipe'] == 'journal'): ?>
                                            <span class="status-badge status-badge-info">Journal</span>
                                        <?php else: ?>
                                            <span class="status-badge status-badge-success">Conference</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: #2c3e50; font-size: 15px;"><?php echo htmlspecialchars($p['tahun']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="edit.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="lni lni-pencil me-1"></i> Edit
                                            </a>
                                            <a href="index.php?hapus=1&id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus publikasi ini?');">
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
                        <i class="lni lni-book empty-state-icon"></i>
                        <h3 class="empty-state-title">Belum Ada Publikasi</h3>
                        <p class="empty-state-message">Mulai dengan menambahkan publikasi pertama ke dalam sistem.</p>
                        <a href="tambah.php" class="empty-state-action">
                            <i class="lni lni-plus"></i> Tambah Publikasi Pertama
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
document.getElementById('searchPublikasi')?.addEventListener('keypress', function(e) {
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

