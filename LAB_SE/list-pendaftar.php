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
					<h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s" style="color: #2c3e50; font-size: 28px; font-weight: 700;">
						<i class="lni lni-list" style="color: #2F80ED; margin-right: 10px;"></i>
						Kelola Pendaftar
					</h2>
					<p class="wow fadeInUp" data-wow-delay=".4s" style="color: #6c757d; font-size: 14px; margin: 0;">
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
									<th>Email</th>
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
													<?php if (!empty($p['foto'])): ?>
														<img src="<?php echo htmlspecialchars($p['foto']); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
													<?php else: ?>
														<?php echo strtoupper(substr($p['nama'], 0, 1)); ?>
													<?php endif; ?>
												</div>
												<div class="user-details">
													<strong><?php echo htmlspecialchars($p['nama']); ?></strong>
												</div>
											</div>
										</td>
										<td>
											<?php if (isset($p['email']) && !empty($p['email'])): ?>
												<a href="mailto:<?php echo htmlspecialchars($p['email']); ?>"><?php echo htmlspecialchars($p['email']); ?></a>
											<?php else: ?>
												<span class="text-muted">-</span>
											<?php endif; ?>
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

<!-- Confirm Modal for Status Update -->
<div id="confirmStatusOverlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px;">
	<div style="background: #fff; border-radius: 12px; max-width: 460px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
		<div style="padding: 16px 20px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; gap: 10px;">
			<i id="confirmStatusIcon" class="lni lni-hourglass" style="font-size: 20px; color: #2F80ED;"></i>
			<h4 id="confirmStatusTitle" style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 16px;">Konfirmasi</h4>
		</div>
		<div id="confirmStatusText" style="padding: 16px 20px; color: #6c757d; font-size: 14px;">
			Apakah Anda yakin?
		</div>
		<div style="padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px; background: #fafafa;">
			<button id="btnCancelStatus" class="button radius-10 border-button" style="padding: 8px 14px; font-size: 14px;">Batal</button>
			<button id="btnConfirmStatus" class="button radius-10" style="background: #2F80ED; color: #fff; padding: 8px 14px; font-size: 14px;">Konfirmasi</button>
		</div>
	</div>
</div>

<style>
/* Stat Cards (smaller) */
.stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 20px; color: white; box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: all 0.2s ease; height: 100%; text-align: center; position: relative; overflow: hidden; }
.stat-card::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); animation: pulse 3s ease-in-out infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); opacity: 0.5; } 50% { transform: scale(1.05); opacity: 0.8; } }
.stat-card:hover { transform: translateY(-5px); box-shadow: 0 14px 28px rgba(0,0,0,0.12); }
.stat-card-blue { background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); }
.stat-card-orange { background: linear-gradient(135deg, #F2994A 0%, #E67E22 100%); }
.stat-card-green { background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%); }
.stat-card-red { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
.stat-card .stat-icon { font-size: 32px; margin-bottom: 10px; opacity: 0.95; position: relative; z-index: 1; }
.stat-card .stat-number { font-size: 28px; font-weight: 700; margin-bottom: 4px; position: relative; z-index: 1; text-shadow: 0 1px 6px rgba(0,0,0,0.2); }
.stat-card .stat-label { font-size: 12px; opacity: 0.95; font-weight: 600; position: relative; z-index: 1; text-transform: uppercase; letter-spacing: 1px; }

/* Filter Card */
.filter-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); border: 1px solid #e9ecef; }
.filter-header { display: flex; align-items: center; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f8f9fa; }
.filter-header i { font-size: 20px; }
.filter-header span { font-size: 16px; font-weight: 600; }
.filter-buttons { display: flex; flex-wrap: wrap; gap: 8px; }
.filter-btn { padding: 10px 16px; border-radius: 8px; font-size: 13px; }
.filter-btn .badge { padding: 3px 7px; font-size: 11px; }

/* Table Card */
.table-card { border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.table-header { padding: 18px 22px; }
.table-header h4 { font-size: 18px; }
.table-count { padding: 5px 12px; font-size: 12px; }

/* Empty state (compact) */
.empty-state { padding: 24px 16px !important; }
.empty-content { text-align: center; color: #6c757d; }
.empty-content i { display: block; font-size: 28px; color: #ced4da; margin-bottom: 8px; }
.empty-content h4 { font-size: 16px; color: #2c3e50; margin: 0 0 6px 0; font-weight: 600; }
.empty-content p { font-size: 13px; margin: 0; }
.empty-content a { color: #2F80ED; text-decoration: none; }
.empty-content a:hover { text-decoration: underline; }

/* Badges */
.nim-badge { padding: 5px 10px; border-radius: 6px; font-size: 12px; }
.jurusan-tag { padding: 5px 10px; border-radius: 6px; font-size: 12px; }
.minat-text { font-size: 12px; }

/* Status Badge */
.status-badge { padding: 6px 12px; border-radius: 16px; font-size: 11px; }
.status-badge i { font-size: 12px; }

/* Date Info */
.date-info strong { font-size: 13px; }
.date-info small { font-size: 10px; }

/* Action Buttons */
.btn-action { width: 32px; height: 32px; font-size: 14px; }

/* Responsive tweaks */
@media (max-width: 991px) { .stat-card { padding: 16px; } .stat-card .stat-icon { font-size: 28px; } .stat-card .stat-number { font-size: 24px; } .stat-card .stat-label { font-size: 11px; } }

/* Detail Modal */
#detailOverlay { display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px; }
.detail-modal { background: #fff; border-radius: 12px; max-width: 640px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden; }
.detail-header { padding: 16px 20px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.detail-header h4 { margin: 0; color: #2c3e50; font-weight: 700; font-size: 18px; display: flex; align-items: center; gap: 10px; }
.detail-close { background: transparent; border: none; font-size: 20px; cursor: pointer; color: #6c757d; }
.detail-body { padding: 18px 20px; }
.detail-grid { display: grid; grid-template-columns: 150px 1fr; gap: 10px 16px; }
.detail-label { color: #6c757d; font-size: 13px; }
.detail-value { color: #2c3e50; font-size: 14px; font-weight: 600; }
.detail-footer { padding: 12px 20px; background: #fafafa; border-top: 1px solid #f1f1f1; display: flex; justify-content: flex-end; }
.detail-status-badge { padding: 6px 12px; border-radius: 16px; font-size: 11px; display: inline-flex; align-items: center; gap: 6px; }
</style>

<?php
$pendaftar_map = [];
foreach ($pendaftar as $pp) {
	$pendaftar_map[(string)$pp['id']] = [
		'id' => (int)$pp['id'],
		'nama' => $pp['nama'] ?? '',
		'email' => $pp['email'] ?? '',
		'nim' => $pp['nim'] ?? '',
		'jurusan' => $pp['jurusan'] ?? '',
		'minat' => $pp['minat'] ?? '',
		'foto' => $pp['foto'] ?? '',
		'status' => $pp['status'] ?? 'pending',
		'tanggal_daftar' => $pp['tanggal_daftar'] ?? '',
		'tanggal_review' => $pp['tanggal_review'] ?? null,
		'catatan_admin' => $pp['catatan_admin'] ?? ''
	];
}
?>
<script>
window.pendaftarData = <?php echo json_encode($pendaftar_map); ?>;
</script>

<script>
function viewDetail(id) {
	var data = (window.pendaftarData && window.pendaftarData[id]) ? window.pendaftarData[id] : null;
	if (!data) {
		alert('Data pendaftar tidak ditemukan.');
		return;
	}

	var overlay = document.getElementById('detailOverlay');
	// Populate
	document.getElementById('detailNama').textContent = data.nama || '-';
	document.getElementById('detailNim').textContent = data.nim || '-';
	document.getElementById('detailJurusan').textContent = data.jurusan || '-';
	document.getElementById('detailMinat').textContent = data.minat ? data.minat : '-';
	document.getElementById('detailEmail').textContent = data.email || '-';
	document.getElementById('detailEmail').href = data.email ? ('mailto:' + data.email) : 'javascript:void(0)';
	document.getElementById('detailTanggalDaftar').textContent = data.tanggal_daftar || '-';
	document.getElementById('detailTanggalReview').textContent = data.tanggal_review ? data.tanggal_review : '-';
	document.getElementById('detailCatatan').textContent = data.catatan_admin ? data.catatan_admin : '-';

	// Foto
	var fotoEl = document.getElementById('detailFoto');
	if (data.foto) {
		fotoEl.innerHTML = '<img src="'+ data.foto +'" alt="Foto '+ (data.nama || "") +'" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover; border: 1px solid #eee;">';
	} else {
		fotoEl.textContent = '-';
	}

	// Status badge
	var statusBadge = document.getElementById('detailStatus');
	var statusText = 'Pending';
	var statusClass = 'status-pending';
	var statusIcon = 'lni-hourglass';
	if (data.status === 'accepted') { statusText = 'Diterima'; statusClass = 'status-accepted'; statusIcon = 'lni-checkmark-circle'; }
	else if (data.status === 'rejected') { statusText = 'Ditolak'; statusClass = 'status-rejected'; statusIcon = 'lni-close'; }
	statusBadge.className = 'detail-status-badge ' + statusClass;
	statusBadge.innerHTML = '<i class="lni ' + statusIcon + '"></i> ' + statusText;

	overlay.style.display = 'flex';
}

(function() {
	let pending = { id: null, status: null };
	const overlay = document.getElementById('confirmStatusOverlay');
	const title = document.getElementById('confirmStatusTitle');
	const text = document.getElementById('confirmStatusText');
	const icon = document.getElementById('confirmStatusIcon');
	const btnConfirm = document.getElementById('btnConfirmStatus');
	const btnCancel = document.getElementById('btnCancelStatus');

	function openConfirm(id, status) {
		pending.id = id;
		pending.status = status;
		btnConfirm.classList.remove('accept', 'reject');
		if (status === 'accepted') {
			title.textContent = 'Terima Pendaftar?';
			text.textContent = 'Apakah Anda yakin ingin menerima pendaftar ini?';
			icon.className = 'lni lni-checkmark-circle';
			icon.style.color = '#27AE60';
			btnConfirm.textContent = 'Terima';
			btnConfirm.classList.add('accept');
		} else if (status === 'rejected') {
			title.textContent = 'Tolak Pendaftar?';
			text.textContent = 'Apakah Anda yakin ingin menolak pendaftar ini?';
			icon.className = 'lni lni-close';
			icon.style.color = '#e74c3c';
			btnConfirm.textContent = 'Tolak';
			btnConfirm.classList.add('reject');
		} else {
			title.textContent = 'Konfirmasi';
			text.textContent = 'Apakah Anda yakin?';
			icon.className = 'lni lni-hourglass';
			icon.style.color = '#2F80ED';
			btnConfirm.textContent = 'Konfirmasi';
		}
		overlay.style.display = 'flex';
	}
	function closeConfirm() {
		pending.id = null;
		pending.status = null;
		overlay.style.display = 'none';
	}

	btnCancel.addEventListener('click', function() { closeConfirm(); });
	btnConfirm.addEventListener('click', function() {
		if (!pending.id || !pending.status) return;
		// submit hidden form
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
		idInput.value = pending.id;
		form.appendChild(idInput);
		
		var statusInput = document.createElement('input');
		statusInput.type = 'hidden';
		statusInput.name = 'status';
		statusInput.value = pending.status;
		form.appendChild(statusInput);
		
		var urlParams = new URLSearchParams(window.location.search);
		var currentFilter = urlParams.get('status') || 'all';
		var filterInput = document.createElement('input');
		filterInput.type = 'hidden';
		filterInput.name = 'current_filter';
		filterInput.value = currentFilter;
		form.appendChild(filterInput);
		
		document.body.appendChild(form);
		form.submit();
	});
	
	overlay.addEventListener('click', function(e) {
		if (e.target === overlay) { closeConfirm(); }
	});

	// expose function
	window.updateStatus = function(id, status) { openConfirm(id, status); }
})();
</script>

<!-- Detail Modal -->
<div id="detailOverlay" onclick="if(event.target===this){ this.style.display='none'; }">
	<div class="detail-modal">
		<div class="detail-header">
			<h4>
				<i class="lni lni-eye" style="color:#2F80ED;"></i>
				Detail Pendaftar
			</h4>
			<button class="detail-close" title="Tutup" onclick="document.getElementById('detailOverlay').style.display='none'">&times;</button>
		</div>
		<div class="detail-body">
			<div class="detail-grid">
				<div class="detail-label">Nama Lengkap</div>
				<div class="detail-value" id="detailNama">-</div>

				<div class="detail-label">NIM</div>
				<div class="detail-value" id="detailNim">-</div>

				<div class="detail-label">Jurusan</div>
				<div class="detail-value" id="detailJurusan">-</div>

				<div class="detail-label">Minat</div>
				<div class="detail-value" id="detailMinat">-</div>

				<div class="detail-label">Email</div>
				<div class="detail-value"><a id="detailEmail" href="javascript:void(0)">-</a></div>

				<div class="detail-label">Foto</div>
				<div class="detail-value" id="detailFoto">-</div>

				<div class="detail-label">Status</div>
				<div class="detail-value"><span id="detailStatus" class="detail-status-badge status-pending"><i class="lni lni-hourglass"></i> Pending</span></div>

				<div class="detail-label">Tanggal Daftar</div>
				<div class="detail-value" id="detailTanggalDaftar">-</div>

				<div class="detail-label">Tanggal Review</div>
				<div class="detail-value" id="detailTanggalReview">-</div>

				<div class="detail-label">Catatan Admin</div>
				<div class="detail-value" id="detailCatatan">-</div>
			</div>
		</div>
		<div class="detail-footer">
			<button class="button radius-10 border-button" onclick="document.getElementById('detailOverlay').style.display='none'">Tutup</button>
		</div>
	</div>
</div>

<?php include __DIR__ . '/template/admin_footer.php'; ?>
