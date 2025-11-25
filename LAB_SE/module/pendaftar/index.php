<?php
$page_title = "Kelola Pendaftar - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'pendaftar';
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
        'active' => true
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

// Ambil statistik pendaftar dari materialized view (lebih cepat!)
// Fallback ke query biasa jika materialized view belum dibuat
$stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];

if (file_exists(__DIR__ . '/../../fungsi/materialized_views_simple.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_simple.php';
    $stats_mv = get_pendaftar_per_status();
    if ($stats_mv) {
        $stats = $stats_mv;
        $stats['total'] = $stats['pending'] + $stats['accepted'] + $stats['rejected'];
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if ($stats['total'] === 0 && $koneksi) {
    $query_stats = "SELECT status, COUNT(*) as total FROM pendaftaran GROUP BY status";
    $result_stats = pg_query($koneksi, $query_stats);
    if ($result_stats) {
        while ($row = pg_fetch_assoc($result_stats)) {
            $stats[$row['status']] = (int)$row['total'];
            $stats['total'] += (int)$row['total'];
        }
    }
}

// Filter berdasarkan status (ambil dulu sebelum proses update)
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

// Proses update status
// PENTING: Proses ini HANYA mengubah status pendaftaran, TIDAK membuat akun user secara otomatis
// Akun user hanya dibuat ketika admin mengklik tombol "Buat Akun" secara manual
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$new_status = isset($_POST['status']) ? clean_input($_POST['status']) : '';
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
	
	if ($id > 0 && in_array($new_status, ['accepted', 'rejected', 'pending'])) {
		$id_escaped = pg_escape_string($koneksi, $id);
		$status_escaped = pg_escape_string($koneksi, $new_status);
		
		// Hanya update status, TIDAK membuat akun user
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
		
		$redirect_url = 'index.php';
		if ($current_filter !== 'all') {
			$redirect_url .= '?status=' . $current_filter;
		}
		header('Location: ' . $redirect_url);
		exit;
	}
}

// Proses pembuatan akun mahasiswa dari pendaftar accepted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_account') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';

	if ($id > 0) {
		// Ambil data pendaftar
		$id_escaped = pg_escape_string($koneksi, $id);
		$q = "SELECT * FROM pendaftaran WHERE id = $id_escaped LIMIT 1";
		$res = pg_query($koneksi, $q);
		if ($res && pg_num_rows($res) === 1) {
			$pd = pg_fetch_assoc($res);
			$nim = trim($pd['nim'] ?? '');
			$nama = trim($pd['nama'] ?? '');
			$email = trim($pd['email'] ?? '');
			$status = $pd['status'] ?? 'pending';

			if ($status !== 'accepted') {
				pesan_kilat('Akun hanya bisa dibuat untuk pendaftar yang sudah diterima.', 'error');
			} elseif ($nim === '' || $nama === '') {
				pesan_kilat('Data pendaftar tidak lengkap (NIM/Nama).', 'error');
			} else {
				// Cek apakah user sudah ada (username = NIM)
				$nim_escaped = pg_escape_string($koneksi, $nim);
				$check_user = pg_query($koneksi, "SELECT id FROM users WHERE username = '$nim_escaped' LIMIT 1");
				if ($check_user && pg_num_rows($check_user) > 0) {
					pesan_kilat('Akun sudah ada untuk NIM tersebut.', 'error');
				} else {
					// Buat akun: username = NIM, password default = MD5(NIM), role = mahasiswa
					$insert = pg_query($koneksi, "
						INSERT INTO users (username, password, role)
						VALUES ('$nim_escaped', MD5('$nim_escaped'), 'mahasiswa')
					");
					if ($insert) {
						pesan_kilat('Akun mahasiswa berhasil dibuat. Username & password awal: NIM.', 'success');
					} else {
						pesan_kilat('Gagal membuat akun: ' . pg_last_error($koneksi), 'error');
					}
				}
			}
		} else {
			pesan_kilat('Data pendaftar tidak ditemukan.', 'error');
		}

		$redirect_url = 'index.php';
		if ($current_filter !== 'all') {
			$redirect_url .= '?status=' . $current_filter;
		}
		header('Location: ' . $redirect_url);
		exit;
	}
}

// Query pendaftar
$pendaftar = [];
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
					<h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Pendaftar</h2>
					<p class="wow fadeInUp" data-wow-delay=".4s">Daftar mahasiswa yang telah mendaftar ke Laboratorium Software Engineering</p>
				</div>
			</div>
		</div>

		<?php tampilkan_pesan(); ?>

		<!-- Statistics Cards -->
		<div class="row mb-50">
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card blue wow fadeInUp" data-wow-delay=".2s">
					<div class="stat-icon">
						<i class="lni lni-graduation"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['total']); ?></div>
					<div class="stat-label">Total Pendaftar</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card orange wow fadeInUp" data-wow-delay=".3s">
					<div class="stat-icon">
						<i class="lni lni-hourglass"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
					<div class="stat-label">Pending</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card green wow fadeInUp" data-wow-delay=".4s">
					<div class="stat-icon">
						<i class="lni lni-checkmark-circle"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['accepted']); ?></div>
					<div class="stat-label">Diterima</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card purple wow fadeInUp" data-wow-delay=".5s">
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
				<div class="wow fadeInUp filter-card-wrapper" data-wow-delay=".2s">
					<div class="filter-buttons">
						<span class="filter-label">
							<i class="lni lni-filter"></i>Filter Status:
						</span>
						<a href="?status=all" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'all' ? 'filter-button-active' : 'border-button'; ?>">
							<i class="lni lni-list"></i> Semua
						</a>
						<a href="?status=pending" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'pending' ? 'filter-button-pending' : 'border-button'; ?>">
							<i class="lni lni-hourglass"></i> Pending
						</a>
						<a href="?status=accepted" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'accepted' ? 'filter-button-accepted' : 'border-button'; ?>">
							<i class="lni lni-checkmark-circle"></i> Diterima
						</a>
						<a href="?status=rejected" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'rejected' ? 'filter-button-rejected' : 'border-button'; ?>">
							<i class="lni lni-close"></i> Ditolak
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Table Pendaftar -->
		<div class="row">
			<div class="col-lg-12">
				<div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
					<table class="table table-striped table-hover admin-table">
						<thead>
							<tr>
								<th>NO</th>
								<th>Nama Lengkap</th>
								<th>Email</th>
								<th>NIM</th>
								<th>Minat</th>
								<th>Status</th>
								<th>Tanggal Daftar</th>
								<th class="text-center">Aksi</th>
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
										<div class="flex-center">
											<?php if (!empty($p['foto'])): ?>
												<img src="<?php echo htmlspecialchars($p['foto']); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>" class="avatar-circle">
											<?php else: ?>
												<?php $initial = strtoupper(substr($p['nama'], 0, 1)); ?>
												<div class="initial-circle">
													<?php echo $initial; ?>
												</div>
											<?php endif; ?>
											<strong><?php echo htmlspecialchars($p['nama']); ?></strong>
										</div>
									</td>
									<td>
										<?php if (isset($p['email']) && !empty($p['email'])): ?>
											<a href="mailto:<?php echo htmlspecialchars($p['email']); ?>" class="email-link">
												<i class="lni lni-envelope"></i><?php echo htmlspecialchars($p['email']); ?>
											</a>
										<?php else: ?>
											<span class="text-muted-small">-</span>
										<?php endif; ?>
									</td>
									<td>
										<span class="nim-badge">
											<?php echo htmlspecialchars($p['nim']); ?>
										</span>
									</td>
									<td>
										<?php if (!empty($p['minat'])): ?>
											<span class="text-color-primary text-size-13" title="<?php echo htmlspecialchars($p['minat']); ?>">
												<?php echo htmlspecialchars(substr($p['minat'], 0, 50)); ?>
												<?php echo strlen($p['minat']) > 50 ? '...' : ''; ?>
											</span>
										<?php else: ?>
											<span class="text-muted-small">-</span>
										<?php endif; ?>
									</td>
									<td>
										<?php
										$status = $p['status'] ?? 'pending';
										$status_badge = '';
										switch ($status) {
											case 'accepted':
												$status_badge = '<span class="status-badge status-badge-accepted">Diterima</span>';
												break;
											case 'rejected':
												$status_badge = '<span class="status-badge status-badge-rejected">Ditolak</span>';
												break;
											case 'pending':
											default:
												$status_badge = '<span class="status-badge status-badge-pending">Pending</span>';
												break;
										}
										echo $status_badge;
										?>
									</td>
									<td>
										<?php 
										if (!empty($p['tanggal_daftar'])) {
											try {
												// Konversi ke timezone WIB (Asia/Jakarta)
												$date = new DateTime($p['tanggal_daftar'], new DateTimeZone('UTC'));
												$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
												echo $date->format('d/m/Y H:i') . ' WIB';
											} catch (Exception $e) {
												// Fallback jika DateTime gagal
												$tanggal = strtotime($p['tanggal_daftar']);
												if ($tanggal !== false) {
													// Tambah 7 jam untuk konversi UTC ke WIB
													$tanggal_wib = $tanggal + (7 * 3600);
													echo date('d/m/Y H:i', $tanggal_wib) . ' WIB';
												} else {
													echo htmlspecialchars($p['tanggal_daftar']);
												}
											}
										} else {
											echo '-';
										}
										?>
									</td>
									<td class="text-center">
										<div class="flex-center-wrap">
											<button type="button" class="button button-sm radius-10 border-button btn-preview" onclick="viewDetail(<?php echo $p['id']; ?>)" title="Lihat Detail">
												<i class="lni lni-eye"></i> Detail
											</button>
											<?php if ($status === 'pending'): ?>
											<button type="button" class="button button-sm radius-10 btn-accept" onclick="updateStatus(<?php echo $p['id']; ?>, 'accepted')" title="Terima Pendaftar">
												<i class="lni lni-checkmark-circle"></i> Terima
											</button>
											<button type="button" class="button button-sm radius-10 btn-reject" onclick="updateStatus(<?php echo $p['id']; ?>, 'rejected')" title="Tolak Pendaftar">
												<i class="lni lni-close"></i> Tolak
											</button>
											<?php else: ?>
											<?php
											// Cek apakah user sudah ada dengan username = NIM
											$nimTmp = pg_escape_string($koneksi, $p['nim']);
											$existsRes = pg_query($koneksi, "SELECT id, username FROM users WHERE username = '$nimTmp' LIMIT 1");
											$hasUser = $existsRes && pg_num_rows($existsRes) > 0;
											
											if ($hasUser): ?>
												<span class="account-active-badge" title="Akun sudah dibuat (Username: <?php echo htmlspecialchars($p['nim']); ?>)">
													<i class="lni lni-checkmark-circle"></i> Akun Aktif
												</span>
											<?php else: ?>
												<?php if ($status === 'accepted'): ?>
												<button type="button" class="button button-sm radius-10 btn-create-account" onclick="showCreateAccountModal(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars($p['nim'], ENT_QUOTES); ?>')" title="Buat akun login dengan username dan password = NIM">
													<i class="lni lni-user"></i> Buat Akun
												</button>
												<?php else: ?>
												<span class="text-muted-small">-</span>
												<?php endif; ?>
											<?php endif; ?>
											<?php endif; ?>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-center admin-table-empty">
										<i class="lni lni-inbox admin-table-empty-icon"></i>
										<p class="mt-3 text-muted">
											<?php if ($status_filter !== 'all'): ?>
												Tidak ada pendaftar dengan status "<?php echo htmlspecialchars(ucfirst($status_filter)); ?>".
											<?php else: ?>
												Belum ada data pendaftar. Mahasiswa yang mendaftar akan muncul di sini.
											<?php endif; ?>
										</p>
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

<!-- Confirm Modal for Status Update -->
<div id="confirmStatusOverlay">
	<div class="confirm-status-modal">
		<div class="confirm-status-header">
			<i id="confirmStatusIcon" class="lni lni-hourglass"></i>
			<h4 id="confirmStatusTitle">Konfirmasi</h4>
		</div>
		<div id="confirmStatusText" class="confirm-status-body">
			Apakah Anda yakin?
		</div>
		<div class="confirm-status-footer">
			<button id="btnCancelStatus" class="button radius-10 border-button">Batal</button>
			<button id="btnConfirmStatus" class="button radius-10 btn-edit">Konfirmasi</button>
		</div>
	</div>
</div>

<!-- Confirm Modal for Create Account -->
<div id="createAccountOverlay">
	<div class="create-account-modal">
		<div class="create-account-header">
			<i class="lni lni-user"></i>
			<h4>localhost says</h4>
		</div>
		<div class="create-account-body">
			<p>Buat akun login untuk mahasiswa ini?</p>
			<div class="create-account-info">
				<div class="create-account-info-item">
					<span class="create-account-info-label">Username: </span>
					<span id="createAccountUsername" class="create-account-info-value">-</span>
				</div>
				<div class="create-account-info-item">
					<span class="create-account-info-label">Password: </span>
					<span id="createAccountPassword" class="create-account-info-value">-</span>
				</div>
			</div>
			<p>Mahasiswa bisa login ke halaman mahasiswa dengan NIM sebagai username dan password.</p>
		</div>
		<div class="create-account-footer">
			<button id="btnCancelCreateAccount" class="button radius-10 btn-cancel-account">Cancel</button>
			<button id="btnConfirmCreateAccount" class="button radius-10 btn-confirm-account">OK</button>
		</div>
	</div>
</div>

<?php
$pendaftar_map = [];
foreach ($pendaftar as $pp) {
	// Format tanggal_daftar ke WIB
	$tanggal_daftar_formatted = '-';
	if (!empty($pp['tanggal_daftar'])) {
		try {
			$date = new DateTime($pp['tanggal_daftar'], new DateTimeZone('UTC'));
			$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
			$tanggal_daftar_formatted = $date->format('d/m/Y H:i') . ' WIB';
		} catch (Exception $e) {
			$tanggal = strtotime($pp['tanggal_daftar']);
			if ($tanggal !== false) {
				$tanggal_wib = $tanggal + (7 * 3600);
				$tanggal_daftar_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
			} else {
				$tanggal_daftar_formatted = $pp['tanggal_daftar'];
			}
		}
	}
	
	// Format tanggal_review ke WIB
	$tanggal_review_formatted = '-';
	if (!empty($pp['tanggal_review'])) {
		try {
			$date = new DateTime($pp['tanggal_review'], new DateTimeZone('UTC'));
			$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
			$tanggal_review_formatted = $date->format('d/m/Y H:i') . ' WIB';
		} catch (Exception $e) {
			$tanggal = strtotime($pp['tanggal_review']);
			if ($tanggal !== false) {
				$tanggal_wib = $tanggal + (7 * 3600);
				$tanggal_review_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
			} else {
				$tanggal_review_formatted = $pp['tanggal_review'];
			}
		}
	}
	
	$pendaftar_map[(string)$pp['id']] = [
		'id' => (int)$pp['id'],
		'nama' => $pp['nama'] ?? '',
		'email' => $pp['email'] ?? '',
		'nim' => $pp['nim'] ?? '',
		'minat' => $pp['minat'] ?? '',
		'foto' => $pp['foto'] ?? '',
		'status' => $pp['status'] ?? 'pending',
		'tanggal_daftar' => $tanggal_daftar_formatted,
		'tanggal_review' => $tanggal_review_formatted,
		'catatan_admin' => $pp['catatan_admin'] ?? ''
	];
}
?>
<script>
window.pendaftarData = <?php echo json_encode($pendaftar_map); ?>;
</script>
<script src="../../assets/js/classes/PendaftarModals.js"></script>
<script>
// Initialize PendaftarModals
if (typeof PendaftarModals !== 'undefined' && window.pendaftarData) {
    window.pendaftarModals = new PendaftarModals(window.pendaftarData);
}
</script>

<!-- Detail Modal -->
<div id="detailOverlay" onclick="if(event.target===this){ this.style.display='none'; }">
	<div class="detail-modal">
		<div class="detail-header">
			<h4>
				<i class="lni lni-eye icon-blue"></i>
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

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
