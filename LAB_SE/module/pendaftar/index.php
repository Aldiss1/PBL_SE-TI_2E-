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

// Ambil statistik pendaftar
$stats = [
	'total' => 0,
	'pending' => 0,
	'accepted' => 0,
	'rejected' => 0
];

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
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
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
				<div class="stat-card blue wow fadeInUp" data-wow-delay=".2s" style="text-align: center;">
					<div class="stat-icon">
						<i class="lni lni-graduation"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['total']); ?></div>
					<div class="stat-label">Total Pendaftar</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card orange wow fadeInUp" data-wow-delay=".3s" style="text-align: center;">
					<div class="stat-icon">
						<i class="lni lni-hourglass"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
					<div class="stat-label">Pending</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card green wow fadeInUp" data-wow-delay=".4s" style="text-align: center;">
					<div class="stat-icon">
						<i class="lni lni-checkmark-circle"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['accepted']); ?></div>
					<div class="stat-label">Diterima</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card purple wow fadeInUp" data-wow-delay=".5s" style="text-align: center;">
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
				<div class="wow fadeInUp" data-wow-delay=".2s" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
					<div style="display: flex; align-items: center; flex-wrap: wrap; gap: 12px;">
						<span style="font-weight: 600; color: #2c3e50; font-size: 14px; margin-right: 5px;">
							<i class="lni lni-filter" style="margin-right: 5px;"></i>Filter Status:
						</span>
						<a href="?status=all" class="button button-sm radius-10 <?php echo $status_filter === 'all' ? '' : 'border-button'; ?>" style="margin-right: 8px; <?php echo $status_filter === 'all' ? 'background: #2F80ED; color: white; border: none;' : ''; ?>">
							<i class="lni lni-list"></i> Semua
						</a>
						<a href="?status=pending" class="button button-sm radius-10 <?php echo $status_filter === 'pending' ? '' : 'border-button'; ?>" style="margin-right: 8px; <?php echo $status_filter === 'pending' ? 'background: #F2994A; color: white; border: none;' : ''; ?>">
							<i class="lni lni-hourglass"></i> Pending
						</a>
						<a href="?status=accepted" class="button button-sm radius-10 <?php echo $status_filter === 'accepted' ? '' : 'border-button'; ?>" style="margin-right: 8px; <?php echo $status_filter === 'accepted' ? 'background: #27AE60; color: white; border: none;' : ''; ?>">
							<i class="lni lni-checkmark-circle"></i> Diterima
						</a>
						<a href="?status=rejected" class="button button-sm radius-10 <?php echo $status_filter === 'rejected' ? '' : 'border-button'; ?>" style="<?php echo $status_filter === 'rejected' ? 'background: #e74c3c; color: white; border: none;' : ''; ?>">
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
					<table class="table table-striped table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
						<thead style="background: #2c3e50; color: white;">
							<tr>
								<th style="padding: 15px;">NO</th>
								<th style="padding: 15px;">Nama Lengkap</th>
								<th style="padding: 15px;">Email</th>
								<th style="padding: 15px;">NIM</th>
								<th style="padding: 15px;">Minat</th>
								<th style="padding: 15px;">Status</th>
								<th style="padding: 15px;">Tanggal Daftar</th>
								<th style="padding: 15px; text-align: center;">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php if (count($pendaftar) > 0): ?>
								<?php 
								$no = 1;
								foreach ($pendaftar as $p): 
								?>
								<tr>
									<td style="padding: 15px;"><?php echo $no++; ?></td>
									<td style="padding: 15px;">
										<div style="display:flex; align-items:center; gap:10px;">
											<?php if (!empty($p['foto'])): ?>
												<img src="<?php echo htmlspecialchars($p['foto']); ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:1px solid #eee;">
											<?php else: ?>
												<?php $initial = strtoupper(substr($p['nama'], 0, 1)); ?>
												<div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; background: linear-gradient(135deg, #2F80ED 0%, #6C63FF 100%);">
													<?php echo $initial; ?>
												</div>
											<?php endif; ?>
											<strong><?php echo htmlspecialchars($p['nama']); ?></strong>
										</div>
									</td>
									<td style="padding: 15px;">
										<?php if (isset($p['email']) && !empty($p['email'])): ?>
											<a href="mailto:<?php echo htmlspecialchars($p['email']); ?>" style="color: #2F80ED; text-decoration: none;">
												<i class="lni lni-envelope" style="margin-right: 5px;"></i><?php echo htmlspecialchars($p['email']); ?>
											</a>
										<?php else: ?>
											<span style="color: #95a5a6;">-</span>
										<?php endif; ?>
									</td>
									<td style="padding: 15px;">
										<span style="background: #e3f2fd; color: #1976d2; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 12px;">
											<?php echo htmlspecialchars($p['nim']); ?>
										</span>
									</td>
									<td style="padding: 15px;">
										<?php if (!empty($p['minat'])): ?>
											<span style="color: #495057; font-size: 13px;" title="<?php echo htmlspecialchars($p['minat']); ?>">
												<?php echo htmlspecialchars(substr($p['minat'], 0, 50)); ?>
												<?php echo strlen($p['minat']) > 50 ? '...' : ''; ?>
											</span>
										<?php else: ?>
											<span style="color: #95a5a6;">-</span>
										<?php endif; ?>
									</td>
									<td style="padding: 15px;">
										<?php
										$status = $p['status'] ?? 'pending';
										$status_badge = '';
										switch ($status) {
											case 'accepted':
												$status_badge = '<span style="background: #27AE60; color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Diterima</span>';
												break;
											case 'rejected':
												$status_badge = '<span style="background: #e74c3c; color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Ditolak</span>';
												break;
											case 'pending':
											default:
												$status_badge = '<span style="background: #F2994A; color: white; padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">Pending</span>';
												break;
										}
										echo $status_badge;
										?>
									</td>
									<td style="padding: 15px;">
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
									<td style="padding: 15px; text-align: center;">
										<div style="display: flex; align-items: center; justify-content: center; gap: 5px; flex-wrap: wrap;">
											<button type="button" class="button button-sm radius-10 border-button" style="padding: 5px 12px; margin-right: 5px;" onclick="viewDetail(<?php echo $p['id']; ?>)" title="Lihat Detail">
												<i class="lni lni-eye"></i> Detail
											</button>
											<?php if ($status === 'pending'): ?>
											<button type="button" class="button button-sm radius-10" style="background: #27AE60; color: white; padding: 5px 12px; margin-right: 5px; border: none;" onclick="updateStatus(<?php echo $p['id']; ?>, 'accepted')" title="Terima Pendaftar">
												<i class="lni lni-checkmark-circle"></i> Terima
											</button>
											<button type="button" class="button button-sm radius-10" style="background: #e74c3c; color: white; padding: 5px 12px; border: none;" onclick="updateStatus(<?php echo $p['id']; ?>, 'rejected')" title="Tolak Pendaftar">
												<i class="lni lni-close"></i> Tolak
											</button>
											<?php else: ?>
											<?php
											// Cek apakah user sudah ada dengan username = NIM
											$nimTmp = pg_escape_string($koneksi, $p['nim']);
											$existsRes = pg_query($koneksi, "SELECT id, username FROM users WHERE username = '$nimTmp' LIMIT 1");
											$hasUser = $existsRes && pg_num_rows($existsRes) > 0;
											
											if ($hasUser): ?>
												<span style="background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;" title="Akun sudah dibuat (Username: <?php echo htmlspecialchars($p['nim']); ?>)">
													<i class="lni lni-checkmark-circle"></i> Akun Aktif
												</span>
											<?php else: ?>
												<?php if ($status === 'accepted'): ?>
												<button type="button" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 12px; border: none;" onclick="showCreateAccountModal(<?php echo (int)$p['id']; ?>, '<?php echo htmlspecialchars($p['nim'], ENT_QUOTES); ?>')" title="Buat akun login dengan username dan password = NIM">
													<i class="lni lni-user"></i> Buat Akun
												</button>
												<?php else: ?>
												<span style="color: #95a5a6; font-size: 12px;">-</span>
												<?php endif; ?>
											<?php endif; ?>
											<?php endif; ?>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="8" class="text-center" style="padding: 40px;">
										<i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
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

<!-- Confirm Modal for Create Account -->
<div id="createAccountOverlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px;">
	<div style="background: #fff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;">
		<div style="padding: 16px 20px; border-bottom: 1px solid #f1f1f1; display: flex; align-items: center; gap: 10px;">
			<i class="lni lni-user" style="font-size: 20px; color: #2F80ED;"></i>
			<h4 style="margin: 0; color: #2c3e50; font-weight: 600; font-size: 16px;">localhost says</h4>
		</div>
		<div style="padding: 20px;">
			<p style="margin: 0 0 16px 0; color: #2c3e50; font-size: 14px; font-weight: 500;">
				Buat akun login untuk mahasiswa ini?
			</p>
			<div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
				<div style="margin-bottom: 8px;">
					<span style="color: #6c757d; font-size: 13px;">Username: </span>
					<span id="createAccountUsername" style="color: #2c3e50; font-weight: 600; font-size: 14px;">-</span>
				</div>
				<div>
					<span style="color: #6c757d; font-size: 13px;">Password: </span>
					<span id="createAccountPassword" style="color: #2c3e50; font-weight: 600; font-size: 14px;">-</span>
				</div>
			</div>
			<p style="margin: 0; color: #6c757d; font-size: 13px; line-height: 1.5;">
				Mahasiswa bisa login ke halaman mahasiswa dengan NIM sebagai username dan password.
			</p>
		</div>
		<div style="padding: 12px 20px; display: flex; justify-content: flex-end; gap: 8px; background: #fafafa;">
			<button id="btnCancelCreateAccount" class="button radius-10" style="background: #6c757d; color: #fff; padding: 8px 16px; font-size: 14px; border: none;">Cancel</button>
			<button id="btnConfirmCreateAccount" class="button radius-10" style="background: #9b59b6; color: #fff; padding: 8px 16px; font-size: 14px; border: none;">OK</button>
		</div>
	</div>
</div>

<style>
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
.status-pending { background: #fff3cd; color: #856404; }
.status-accepted { background: #d4edda; color: #155724; }
.status-rejected { background: #f8d7da; color: #721c24; }

/* Table Styling Improvements */
.table-striped tbody tr:nth-of-type(odd) { background-color: rgba(0,0,0,0.02); }
.table-striped tbody tr:hover { background-color: rgba(47, 128, 237, 0.05); transition: background-color 0.2s ease; }
.table thead th { font-weight: 600; text-transform: uppercase; font-size: 12px; letter-spacing: 0.5px; }

/* Button Hover Effects */
.button:hover { transform: translateY(-1px); transition: all 0.2s ease; }
.button:active { transform: translateY(0); }

/* Filter Card Styling */
.filter-card-wrapper { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }

/* Responsive Table */
@media (max-width: 768px) {
	.table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
	.table thead th, .table tbody td { white-space: nowrap; }
}
</style>

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

// Create Account Modal
(function() {
	let pendingAccount = { id: null, nim: null };
	const overlay = document.getElementById('createAccountOverlay');
	const usernameEl = document.getElementById('createAccountUsername');
	const passwordEl = document.getElementById('createAccountPassword');
	const btnConfirm = document.getElementById('btnConfirmCreateAccount');
	const btnCancel = document.getElementById('btnCancelCreateAccount');

	window.showCreateAccountModal = function(id, nim) {
		pendingAccount.id = id;
		pendingAccount.nim = nim;
		usernameEl.textContent = nim;
		passwordEl.textContent = nim;
		overlay.style.display = 'flex';
	};

	function closeModal() {
		pendingAccount.id = null;
		pendingAccount.nim = null;
		overlay.style.display = 'none';
	}

	btnCancel.addEventListener('click', function() { closeModal(); });
	btnConfirm.addEventListener('click', function() {
		if (!pendingAccount.id) return;
		
		// Submit form
		var form = document.createElement('form');
		form.method = 'POST';
		form.action = '';
		
		var actionInput = document.createElement('input');
		actionInput.type = 'hidden';
		actionInput.name = 'action';
		actionInput.value = 'create_account';
		form.appendChild(actionInput);
		
		var idInput = document.createElement('input');
		idInput.type = 'hidden';
		idInput.name = 'id';
		idInput.value = pendingAccount.id;
		form.appendChild(idInput);
		
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
		if (e.target === overlay) { closeModal(); }
	});
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
