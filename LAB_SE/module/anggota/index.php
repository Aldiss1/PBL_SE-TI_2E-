<?php
$page_title = "Kelola Anggota - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
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

// Ambil data anggota
$anggota = [];
$query = "SELECT * FROM anggota ORDER BY id DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $anggota[] = $row;
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
                    <table class="table table-striped table-hover admin-table">
                        <thead></thead>
                            <tr>
                                <th>NO</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($anggota) > 0): ?>
                                <?php $no = 1; foreach ($anggota as $a): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="flex-center">
                                            <?php 
                                            $foto_raw = $a['foto'] ?? '';
                                            $is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                                            $foto_src = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
                                            ?>
                                            <?php if (!empty($foto_src)): ?>
                                                <img src="<?php echo htmlspecialchars($foto_src); ?>" alt="<?php echo htmlspecialchars($a['nama']); ?>" class="avatar-circle">
                                            <?php else: ?>
                                                <?php $initial = strtoupper(substr($a['nama'], 0, 1)); ?>
                                                <div class="initial-circle">
                                                    <?php echo $initial; ?>
                                                </div>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($a['nama']); ?></strong>
                                        </div>
                                    </td>
                                    <td>&nbsp;<?php echo htmlspecialchars($a['jabatan'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <button type="button" class="button button-sm radius-10 border-button btn-preview" onclick="openPreviewAnggota(<?php echo (int)$a['id']; ?>)">
                                            <i class="lni lni-eye"></i> Preview
                                        </button>
                                        <a href="edit.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10 btn-edit">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10 btn-delete" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center admin-table-empty">
                                        <i class="lni lni-inbox admin-table-empty-icon"></i>
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

<?php
// Data untuk modal preview
$anggota_map = [];
foreach ($anggota as $row) {
	$foto_raw = $row['foto'] ?? '';
	$is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
	$foto_resolved = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
	$anggota_map[(string)$row['id']] = [
		'id' => (int)$row['id'],
		'nama' => $row['nama'] ?? '',
		'jabatan' => $row['jabatan'] ?? '',
		'foto' => $row['foto'] ?? '',
		'foto_resolved' => $foto_resolved,
		'instagram' => $row['instagram'] ?? '',
		'linkedin' => $row['linkedin'] ?? ''
	];
}
?>

<script>
window.anggotaData = <?php echo json_encode($anggota_map); ?>;
</script>
<script src="../../assets/js/classes/AnggotaPreviewModal.js"></script>
<script>
// Initialize AnggotaPreviewModal
if (typeof AnggotaPreviewModal !== 'undefined' && window.anggotaData) {
    window.anggotaPreviewModal = new AnggotaPreviewModal(window.anggotaData);
}
</script>

<!-- Modal Preview Anggota -->
<div id="anggotaPreviewOverlay" onclick="if(event.target===this){closePreviewAnggota()}">
	<div class="anggota-modal">
		<div class="anggota-header">
			<h4>Preview Anggota</h4>
			<button class="anggota-close" onclick="closePreviewAnggota()">&times;</button>
		</div>
		<div class="anggota-body">
			<div class="row">
				<img id="apFoto" class="anggota-photo" src="https://via.placeholder.com/300" alt="Foto">
				<div>
					<h5 id="apNama" class="anggota-name">-</h5>
					<div id="apJabatan" class="anggota-role">-</div>
					<div id="apSosmed" class="anggota-social"></div>
				</div>
			</div>
		</div>
		<div class="anggota-footer">
			<button class="button radius-10 border-button" onclick="closePreviewAnggota()">Tutup</button>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


