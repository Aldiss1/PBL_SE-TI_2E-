<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Ambil data jabatan
$query = "SELECT * FROM jabatan ORDER BY id DESC";
$result = pg_query($koneksi, $query);

// Set judul halaman dan menu aktif
$page_title = "Kelola Jabatan - Admin";
$current_page = 'jabatan';
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
		'active' => true
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
					<h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Jabatan</h2>
					<p class="wow fadeInUp" data-wow-delay=".4s">Kelola daftar jabatan/posisi di laboratorium</p>
				</div>
			</div>
		</div>

		<?php tampilkan_pesan(); ?>

		<div class="row mb-30">
			<div class="col-lg-12">
				<a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
					<i class="lni lni-plus"></i> Tambah Jabatan
				</a>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-12">
				<div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
					<table class="table table-striped table-hover admin-table">
						<thead>
							<tr>
								<th>NO</th>
								<th>Nama Jabatan</th>
								<th style="text-align: center;">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php if ($result && pg_num_rows($result) > 0): ?>
								<?php $no = 1; while ($row = pg_fetch_assoc($result)): ?>
								<tr>
									<td><?php echo $no++; ?></td>
									<td><strong><?php echo htmlspecialchars($row['nama_jabatan']); ?></strong></td>
									<td style="text-align: center;">
										<a href="edit/index.php?id=<?php echo $row['id']; ?>" class="button button-sm radius-10 btn-edit">
											<i class="lni lni-pencil"></i> Edit
										</a>
										<a href="hapus.php?id=<?php echo $row['id']; ?>" class="button button-sm radius-10 btn-delete" data-delete-url="hapus.php?id=<?php echo $row['id']; ?>">
											<i class="lni lni-trash"></i> Hapus
										</a>
									</td>
								</tr>
								<?php endwhile; ?>
							<?php else: ?>
								<tr>
									<td colspan="3" class="text-center admin-table-empty">
										<i class="lni lni-inbox admin-table-empty-icon"></i>
										<p class="mt-3 text-muted">Belum ada data jabatan.</p>
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

<!-- Custom Confirm Modal -->
<div id="confirmDeleteOverlay">
	<div class="confirm-modal">
		<div class="confirm-modal-header">
			<i class="lni lni-trash"></i>
			<h4>Hapus Jabatan?</h4>
		</div>
		<div class="confirm-modal-body">
			Apakah Anda yakin ingin menghapus jabatan ini? Tindakan ini tidak dapat dibatalkan.
		</div>
		<div class="confirm-modal-footer">
			<button id="btnCancelDelete" class="button radius-10 border-button">Batal</button>
			<button id="btnConfirmDelete" class="button radius-10 btn-delete">Hapus</button>
		</div>
	</div>
</div>

<script src="../../assets/js/classes/ConfirmModal.js"></script>
<script>
// Initialize ConfirmModal for jabatan deletion
if (typeof ConfirmModal !== 'undefined') {
    window.jabatanConfirmModal = new ConfirmModal({
        overlayId: 'confirmDeleteOverlay',
        confirmButtonId: 'btnConfirmDelete',
        cancelButtonId: 'btnCancelDelete',
        deleteButtonSelector: '.btn-delete'
    });
}
</script>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

<?php

