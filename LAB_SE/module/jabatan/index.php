<?php
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Hitung total jabatan
$count_query = "SELECT COUNT(*) as total FROM jabatan";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $count_query = "SELECT COUNT(*) as total FROM jabatan WHERE nama_jabatan ILIKE '%$search_escaped%'";
}
$count_result = pg_query($koneksi, $count_query);
$total_jabatan = 0;
if ($count_result && pg_num_rows($count_result) > 0) {
    $count_row = pg_fetch_assoc($count_result);
    $total_jabatan = (int)$count_row['total'];
}

// Hitung jabatan yang sedang digunakan oleh anggota
$jabatan_terpakai_query = "SELECT COUNT(DISTINCT jabatan) as total FROM anggota WHERE jabatan IS NOT NULL AND jabatan != ''";
$jabatan_terpakai_result = pg_query($koneksi, $jabatan_terpakai_query);
$jabatan_terpakai = 0;
if ($jabatan_terpakai_result && pg_num_rows($jabatan_terpakai_result) > 0) {
    $jabatan_terpakai_row = pg_fetch_assoc($jabatan_terpakai_result);
    $jabatan_terpakai = (int)$jabatan_terpakai_row['total'];
}

// Hitung jabatan yang belum digunakan (kosong)
$jabatan_kosong = $total_jabatan - $jabatan_terpakai;
if ($jabatan_kosong < 0) $jabatan_kosong = 0;

// Ambil detail jabatan dengan jumlah anggota per jabatan
$jabatan_detail_query = "
    SELECT 
        j.id,
        j.nama_jabatan,
        j.created_at,
        COUNT(a.id) as jumlah_anggota
    FROM jabatan j
    LEFT JOIN anggota a ON a.jabatan = j.nama_jabatan AND a.jabatan IS NOT NULL AND a.jabatan != ''
";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $jabatan_detail_query .= " WHERE j.nama_jabatan ILIKE '%$search_escaped%'";
}
$jabatan_detail_query .= "
    GROUP BY j.id, j.nama_jabatan, j.created_at
    ORDER BY j.id DESC
";
$jabatan_detail_result = pg_query($koneksi, $jabatan_detail_query);
$jabatan_detail = [];
if ($jabatan_detail_result && pg_num_rows($jabatan_detail_result) > 0) {
    while ($row = pg_fetch_assoc($jabatan_detail_result)) {
        $jabatan_detail[] = $row;
    }
}

// Ambil data jabatan untuk JavaScript (dengan filter search)
$query = "SELECT * FROM jabatan";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $query .= " WHERE nama_jabatan ILIKE '%$search_escaped%'";
}
$query .= " ORDER BY id DESC";
$result = pg_query($koneksi, $query);

// Simpan data untuk JavaScript
$jabatan_data = [];
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $jabatan_data[] = [
            'id' => (int)$row['id'],
            'nama_jabatan' => $row['nama_jabatan'] ?? ''
        ];
    }
}

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

		<!-- Stat Cards -->
		<div class="row mb-30">
			<div class="col-lg-4 col-md-6 mb-20">
				<div class="stat-card wow fadeInUp" data-wow-delay=".2s">
					<div class="stat-card-icon stat-card-icon-primary">
						<i class="lni lni-briefcase"></i>
					</div>
					<div class="stat-card-content">
						<h3 class="stat-card-number"><?php echo $total_jabatan; ?></h3>
						<p class="stat-card-label">Total Jabatan</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-6 mb-20">
				<div class="stat-card wow fadeInUp" data-wow-delay=".3s">
					<div class="stat-card-icon stat-card-icon-success">
						<i class="lni lni-users"></i>
					</div>
					<div class="stat-card-content">
						<h3 class="stat-card-number"><?php echo $jabatan_terpakai; ?></h3>
						<p class="stat-card-label">Jabatan Terpakai</p>
					</div>
				</div>
			</div>
			<div class="col-lg-4 col-md-6 mb-20">
				<div class="stat-card wow fadeInUp" data-wow-delay=".4s">
					<div class="stat-card-icon stat-card-icon-info">
						<i class="lni lni-empty-file"></i>
					</div>
					<div class="stat-card-content">
						<h3 class="stat-card-number"><?php echo $jabatan_kosong; ?></h3>
						<p class="stat-card-label">Jabatan Kosong</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Search Bar -->
		<div class="row mb-20">
			<div class="col-lg-12">
				<div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
					<i class="lni lni-search"></i>
					<input type="text" id="searchJabatan" class="jabatan-search-input" placeholder="Cari jabatan..." value="<?php echo htmlspecialchars($search_query); ?>">
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
				<a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".3s">
					<i class="lni lni-plus"></i> Tambah Jabatan
				</a>
			</div>
		</div>

		<!-- Info Box -->
		<?php if ($total_jabatan > 0): ?>
		<div class="row mb-30">
			<div class="col-lg-12">
				<div class="info-box wow fadeInUp" data-wow-delay=".3s">
					<div class="info-box-icon">
						<i class="lni lni-information"></i>
					</div>
					<div class="info-box-content">
						<h4 class="info-box-title">Tips Mengelola Jabatan</h4>
						<p class="info-box-text">
							Jabatan yang sudah digunakan oleh anggota tidak dapat dihapus. Pastikan untuk menghapus atau mengubah jabatan anggota terlebih dahulu sebelum menghapus jabatan.
							<?php if ($jabatan_kosong > 0): ?>
								<br><strong>Anda memiliki <?php echo $jabatan_kosong; ?> jabatan yang belum digunakan.</strong>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="col-lg-12">
				<?php if ($result && pg_num_rows($result) > 0): ?>
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
								<?php 
								$no = 1; 
								foreach ($jabatan_detail as $jabatan): 
									$jumlah_anggota = (int)$jabatan['jumlah_anggota'];
									$is_terpakai = $jumlah_anggota > 0;
								?>
								<tr>
									<td><?php echo $no++; ?></td>
									<td>
										<div style="display: flex; align-items: center; gap: 12px;">
											<div class="jabatan-icon-badge">
												<i class="lni lni-briefcase"></i>
											</div>
											<div style="flex: 1;">
												<strong><?php echo htmlspecialchars($jabatan['nama_jabatan']); ?></strong>
												<?php if ($is_terpakai): ?>
													<div class="jabatan-usage-badge">
														<i class="lni lni-users"></i> Digunakan oleh <?php echo $jumlah_anggota; ?> anggota
													</div>
												<?php else: ?>
													<div class="jabatan-usage-badge jabatan-usage-empty">
														<i class="lni lni-empty-file"></i> Belum digunakan
													</div>
												<?php endif; ?>
											</div>
										</div>
									</td>
									<td style="text-align: center;">
										<div class="btn-group" role="group">
											<a href="edit/index.php?id=<?php echo $jabatan['id']; ?>" class="btn btn-primary btn-sm">
												<i class="lni lni-pencil me-1"></i> Edit
											</a>
											<button type="button" class="btn btn-danger btn-sm" 
												onclick="confirmDeleteJabatan(<?php echo (int)$jabatan['id']; ?>, '<?php echo htmlspecialchars(addslashes($jabatan['nama_jabatan'])); ?>', <?php echo $is_terpakai ? 'true' : 'false'; ?>, <?php echo $jumlah_anggota; ?>)" 
												<?php echo $is_terpakai ? 'disabled title="Jabatan sedang digunakan, tidak dapat dihapus"' : ''; ?>>
												<i class="lni lni-trash me-1"></i> Hapus
											</button>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php else: ?>
					<!-- Empty State -->
					<div class="empty-state wow fadeInUp" data-wow-delay=".3s">
						<i class="lni lni-briefcase empty-state-icon"></i>
						<h3 class="empty-state-title">Belum Ada Jabatan</h3>
						<p class="empty-state-message">Mulai dengan menambahkan jabatan pertama ke dalam sistem untuk mengorganisir posisi di laboratorium.</p>
						<a href="tambah.php" class="empty-state-action">
							<i class="lni lni-plus"></i> Tambah Jabatan Pertama
						</a>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<script>
window.jabatanData = <?php echo json_encode($jabatan_data); ?>;
</script>

<!-- Load UI/UX Components -->
<link rel="stylesheet" href="../../assets/css/anggota-ui.css">
<script src="../../assets/js/classes/ToastNotification.js"></script>
<script src="../../assets/js/classes/LoadingState.js"></script>
<script src="../../assets/js/classes/AnggotaConfirmModal.js"></script>

<script>
// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchJabatan');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2 || query.length === 0) {
                searchTimeout = setTimeout(function() {
                    if (query.length > 0) {
                        window.location.href = 'index.php?search=' + encodeURIComponent(query);
                    } else {
                        window.location.href = 'index.php';
                    }
                }, 500);
            }
        });
        
        // Enter key to search immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length > 0) {
                    window.location.href = 'index.php?search=' + encodeURIComponent(query);
                } else {
                    window.location.href = 'index.php';
                }
            }
        });
    }
});

// Function to confirm delete with modal
function confirmDeleteJabatan(id, nama, isTerpakai, jumlahAnggota) {
    if (isTerpakai) {
        if (window.toast) {
            window.toast.warning('Jabatan ini sedang digunakan oleh ' + jumlahAnggota + ' anggota. Hapus atau ubah jabatan anggota terlebih dahulu.', 'Tidak Dapat Dihapus');
        }
        return;
    }
    
    if (window.anggotaConfirmModal) {
        window.anggotaConfirmModal.confirmDelete(nama, function() {
            // Show loading
            if (window.loading) {
                window.loading.show('Menghapus jabatan...');
            }
            
            // Delete via fetch
            fetch('hapus.php?id=' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (window.loading) {
                    window.loading.hide();
                }
                
                if (data.success) {
                    // Show success toast
                    if (window.toast) {
                        window.toast.success(data.message || 'Jabatan berhasil dihapus!', 'Berhasil');
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (window.toast) {
                        window.toast.error(data.message || 'Gagal menghapus jabatan. Silakan coba lagi.', 'Error');
                    }
                }
            })
            .catch(error => {
                if (window.loading) {
                    window.loading.hide();
                }
                if (window.toast) {
                    window.toast.error('Terjadi kesalahan. Silakan coba lagi.', 'Error');
                }
                console.error('Error:', error);
            });
        });
    } else {
        // Fallback to default confirm
        if (confirm('Yakin ingin menghapus jabatan "' + nama + '"?')) {
            window.location.href = 'hapus.php?id=' + id;
        }
    }
}

// Show toast from PHP flash messages
document.addEventListener('DOMContentLoaded', function() {
    const alertMessages = document.querySelectorAll('.alert');
    alertMessages.forEach(function(alert) {
        const message = alert.textContent.trim();
        const type = alert.classList.contains('alert-success') ? 'success' :
                     alert.classList.contains('alert-danger') ? 'error' :
                     alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        if (window.toast && message) {
            window.toast.show(message, type);
        }
        
        // Hide original alert
        setTimeout(() => {
            alert.style.display = 'none';
        }, 100);
    });
});
</script>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

<?php

