<?php
$page_title = "Kelola Anggota - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'anggota';
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
        'active' => true
    ],
    'jabatan' => [
        'title' => 'Kelola Jabatan',
        'icon' => 'lni lni-briefcase',
        'link' => '../../module/jabatan/index.php',
        'active' => false
    ],
    'pendaftar' => [
        'title' => 'Kelola Pendaftar',
        'icon' => 'lni lni-list',
        'link' => '../../list-pendaftar.php',
        'active' => false
    ],
    'blog' => [
        'title' => 'Kelola Blog',
        'icon' => 'lni lni-text-format',
        'link' => '../../module/blog/index.php',
        'active' => false
    ]
];

include __DIR__ . '/../../template/admin_header.php';

// Cek keberadaan tabel
$table_check = "SELECT EXISTS (
    SELECT FROM information_schema.tables 
    WHERE table_schema = 'public' 
    AND table_name = 'anggota'
)";
$check = pg_query($koneksi, $table_check);
$has_table = false;
if ($check) {
    $row_check = pg_fetch_assoc($check);
    $has_table = ($row_check['exists'] === 't' || $row_check['exists'] === true);
}

// Ambil data anggota jika tabel ada
$anggota = [];
if ($has_table) {
    $query = "SELECT * FROM anggota ORDER BY id DESC";
    $result = pg_query($koneksi, $query);
    if ($result && pg_num_rows($result) > 0) {
        while ($row = pg_fetch_assoc($result)) {
            $anggota[] = $row;
        }
    }
}
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Main Content -->
<main class="admin-main-content">
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title mb-50">
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Anggota</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data anggota/personil laboratorium</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-plus"></i> Tambah Anggota
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                    <table class="table table-striped table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
                        <thead style="background: #2c3e50; color: white;">
                            <tr>
                                <th style="padding: 15px;">ID</th>
                                <th style="padding: 15px;">Nama</th>
                                <th style="padding: 15px;">Jabatan</th>
                                <th style="padding: 15px;">Foto</th>
                                <th style="padding: 15px;">Deskripsi</th>
                                <th style="padding: 15px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($has_table && count($anggota) > 0): ?>
                                <?php foreach ($anggota as $a): ?>
                                <tr>
                                    <td style="padding: 15px;"><?php echo $a['id']; ?></td>
                                    <td style="padding: 15px;"><strong><?php echo htmlspecialchars($a['nama']); ?></strong></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($a['jabatan'] ?? '-'); ?></td>
                                    <td style="padding: 15px;">
                                        <?php if (!empty($a['foto'])): ?>
                                            <img src="<?php echo htmlspecialchars($a['foto']); ?>" alt="foto" style="height: 40px; width: 40px; object-fit: cover; border-radius: 50%;">
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 15px; max-width: 320px;">
                                        <small class="text-muted"><?php echo htmlspecialchars(substr((string)$a['deskripsi'], 0, 80)); ?><?php echo strlen((string)$a['deskripsi']) > 80 ? '...' : ''; ?></small>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <a href="edit.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 15px; margin-right: 5px;">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10" style="background: #e74c3c; color: white; padding: 5px 15px;" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center" style="padding: 40px;">
                                        <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                                        <p class="mt-3 text-muted">Belum ada data anggota. <a href="tambah.php">Tambah anggota</a></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


