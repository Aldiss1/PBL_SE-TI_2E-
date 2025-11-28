<?php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../config/anti_injection.php';
require_once __DIR__ . '/../../../fungsi/pesan_kilat.php';
require_once __DIR__ . '/../../auth/cek_login.php';

$id = clean_input($_GET['id'] ?? '');

// Ambil data jabatan berdasarkan ID
$query = "SELECT * FROM jabatan WHERE id = '$id'";
$result = pg_query($koneksi, $query);
$data = $result ? pg_fetch_assoc($result) : null;

if (!$data) {
	header('Location: ../index.php');
	exit;
}

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$nama_jabatan = trim(clean_input($_POST['nama_jabatan'] ?? ''));
	
	if (empty($nama_jabatan)) {
		pesan_kilat('Nama jabatan wajib diisi.', 'error');
	} else {
		if (strlen($nama_jabatan) < 3) {
			pesan_kilat('Nama jabatan minimal 3 karakter.', 'error');
	} else {
		$id_escaped = pg_escape_string($koneksi, $id);
		$nama_jabatan_escaped = pg_escape_string($koneksi, $nama_jabatan);
		
			// Cek duplikat (kecuali untuk ID yang sama)
			$dup_query = "SELECT 1 FROM jabatan WHERE LOWER(nama_jabatan) = LOWER('$nama_jabatan_escaped') AND id != $id_escaped LIMIT 1";
			$dup_result = pg_query($koneksi, $dup_query);
			if ($dup_result && pg_num_rows($dup_result) > 0) {
				pesan_kilat('Jabatan dengan nama tersebut sudah ada.', 'error');
			} else {
		$query = "UPDATE jabatan SET nama_jabatan = '$nama_jabatan_escaped', updated_at = NOW() WHERE id = $id_escaped";
		$result = pg_query($koneksi, $query);
		
		if ($result) {
			pesan_kilat('Data jabatan berhasil diupdate', 'success');
			header('Location: ../index.php');
			exit;
		} else {
			pesan_kilat('Gagal mengupdate data. Error: ' . pg_last_error($koneksi), 'error');
		}
	}
		}
	}
}

// Cek apakah jabatan sedang digunakan
$jabatan_terpakai_query = "SELECT COUNT(*) as jumlah FROM anggota WHERE jabatan = '" . pg_escape_string($koneksi, $data['nama_jabatan']) . "'";
$jabatan_terpakai_result = pg_query($koneksi, $jabatan_terpakai_query);
$jumlah_anggota = 0;
if ($jabatan_terpakai_result && pg_num_rows($jabatan_terpakai_result) > 0) {
	$jabatan_terpakai_row = pg_fetch_assoc($jabatan_terpakai_result);
	$jumlah_anggota = (int)$jabatan_terpakai_row['jumlah'];
}

// Page meta and menu
$page_title = "Edit Jabatan - Admin";
$current_page = 'jabatan';
$menu_items = [
	'dashboard.php' => [
		'title' => 'Dashboard',
		'icon' => 'lni lni-home',
		'link' => '../../../module/admin/dashboard.php',
		'active' => false
	],
	'anggota' => [
		'title' => 'Kelola Anggota',
		'icon' => 'lni lni-users',
		'link' => '../../../module/anggota/index.php',
		'active' => false
	],
	'jabatan' => [
		'title' => 'Kelola Jabatan',
		'icon' => 'lni lni-briefcase',
		'link' => '../../../module/jabatan/index.php',
		'active' => true
	],
	'user' => [
		'title' => 'Kelola User',
		'icon' => 'lni lni-user',
		'link' => '../../../module/user/index.php',
		'active' => false
	],
	'pendaftar' => [
		'title' => 'Kelola Pendaftar',
		'icon' => 'lni lni-list',
		'link' => '../../../module/pendaftar/index.php',
		'active' => false
	],
	'blog' => [
		'title' => 'Kelola Blog',
		'icon' => 'lni lni-text-format',
		'link' => '../../../module/blog/index.php',
		'active' => false
	],
	'profil' => [ 
        'title' => 'Kelola Profil', 
        'icon'  => 'lni lni-cog', 
        'link'  => '../../module/profil/index.php',
        'active' => false
    ]
];

// Set base path for sidebar links in nested directory
$base_path = '../../../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($page_title); ?></title>
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap-5.0.0-alpha-2.min.css" />
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/LineIcons.2.0.css"/>
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/animate.css"/>
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/lindy-uikit.css"/>
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-dashboard.css"/>
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-modules.css"/>
</head>
<body>
	<!-- Sidebar -->
	<?php include __DIR__ . '/../../../template/admin_sidebar.php'; ?>

	<!-- Main Content -->
	<main class="admin-main-content">
	<section class="pt-50 pb-50 admin-section-bg">
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="section-title mb-50">
						<h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Edit Jabatan</h2>
						<p class="wow fadeInUp" data-wow-delay=".4s">Perbarui nama jabatan/posisi</p>
					</div>
				</div>
			</div>

			<?php tampilkan_pesan(); ?>

			<div class="row">
				<!-- Form Section -->
				<div class="col-lg-8">
					<div class="card wow fadeInUp form-card" data-wow-delay=".3s">
						<div class="card-header bg-white border-bottom">
							<h5 class="card-title mb-0">
								<i class="lni lni-pencil text-primary"></i> Form Edit Jabatan
							</h5>
						</div>
						<div class="card-body">
							<form method="POST" id="formEditJabatan">
								<div class="mb-4">
									<label for="nama_jabatan" class="form-label form-label-custom">
										<i class="lni lni-tag"></i> Nama Jabatan <span class="text-danger">*</span>
									</label>
									<input 
										type="text" 
										class="form-control form-control-lg form-input-custom" 
										id="nama_jabatan" 
										name="nama_jabatan" 
										value="<?php echo htmlspecialchars($data['nama_jabatan']); ?>" 
										placeholder="Contoh: Ketua Lab, Wakil Ketua, Dosen Pengampu" 
										required
										minlength="3"
										maxlength="100"
										autofocus>
									<div class="form-text">
										<i class="lni lni-information"></i> Masukkan nama jabatan atau posisi di laboratorium (minimal 3 karakter)
									</div>
									<div class="invalid-feedback" id="nama_jabatan_error"></div>
								</div>

								<?php if ($jumlah_anggota > 0): ?>
								<div class="alert alert-warning d-flex align-items-center" role="alert">
									<i class="lni lni-warning me-2 fs-5"></i>
									<div>
										<strong>Perhatian:</strong> Jabatan ini sedang digunakan oleh <strong><?php echo $jumlah_anggota; ?> anggota</strong>. 
										Perubahan nama jabatan akan mempengaruhi semua anggota yang menggunakan jabatan ini.
									</div>
								</div>
								<?php else: ?>
								<div class="alert alert-info d-flex align-items-center" role="alert">
									<i class="lni lni-bulb me-2 fs-5"></i>
									<div>
										<strong>Info:</strong> Jabatan ini belum digunakan oleh anggota. Anda dapat mengubah nama jabatan dengan bebas.
									</div>
								</div>
								<?php endif; ?>

								<div class="d-flex gap-2 mt-4">
									<button type="submit" class="btn btn-primary btn-lg px-4" id="btnUpdate">
										<i class="lni lni-save me-2"></i> Update Jabatan
									</button>
									<a href="../index.php" class="btn btn-outline-secondary btn-lg px-4">
										<i class="lni lni-arrow-left me-2"></i> Batal
									</a>
								</div>
							</form>
						</div>
					</div>
				</div>

				<!-- Info Section -->
				<div class="col-lg-4">
					<!-- Current Data Card -->
					<div class="card wow fadeInUp mb-3" data-wow-delay=".4s" style="border-left: 4px solid #667eea;">
						<div class="card-body">
							<h6 class="card-title text-primary mb-3">
								<i class="lni lni-file me-2"></i> Data Saat Ini
							</h6>
							<div class="mb-2">
								<small class="text-muted d-block">Nama Jabatan</small>
								<strong><?php echo htmlspecialchars($data['nama_jabatan']); ?></strong>
							</div>
							<?php if (isset($data['created_at']) && !empty($data['created_at'])): ?>
							<div class="mb-2">
								<small class="text-muted d-block">
									<i class="lni lni-calendar me-1"></i> Dibuat
								</small>
								<small><?php echo date('d M Y', strtotime($data['created_at'])); ?></small>
							</div>
							<?php endif; ?>
							<?php if (isset($data['updated_at']) && !empty($data['updated_at']) && $data['updated_at'] != $data['created_at']): ?>
							<div class="mb-2">
								<small class="text-muted d-block">
									<i class="lni lni-reload me-1"></i> Terakhir Diupdate
								</small>
								<small><?php echo date('d M Y', strtotime($data['updated_at'])); ?></small>
							</div>
							<?php endif; ?>
							<?php if ($jumlah_anggota > 0): ?>
							<div>
								<small class="text-muted d-block">Digunakan Oleh</small>
								<span class="badge bg-success"><?php echo $jumlah_anggota; ?> Anggota</span>
							</div>
							<?php else: ?>
							<div>
								<small class="text-muted d-block">Status</small>
								<span class="badge bg-secondary">Belum Digunakan</span>
							</div>
							<?php endif; ?>
						</div>
					</div>

					<!-- Quick Info Card -->
					<div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
						<div class="card-body">
							<h6 class="card-title text-success mb-3">
								<i class="lni lni-information me-2"></i> Informasi
							</h6>
							<ul class="list-unstyled mb-0">
								<li class="mb-2">
									<i class="lni lni-checkmark-circle text-success me-2"></i>
									<small>Nama jabatan harus unik</small>
								</li>
								<li class="mb-2">
									<i class="lni lni-checkmark-circle text-success me-2"></i>
									<small>Minimal 3 karakter</small>
								</li>
								<li class="mb-2">
									<i class="lni lni-checkmark-circle text-success me-2"></i>
									<small>Maksimal 100 karakter</small>
								</li>
								<li>
									<i class="lni lni-checkmark-circle text-success me-2"></i>
									<small>Perubahan akan langsung tersimpan</small>
								</li>
							</ul>
						</div>
					</div>

					<!-- Help Card -->
					<div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
						<div class="card-body">
							<h6 class="card-title text-warning mb-3">
								<i class="lni lni-question-circle me-2"></i> Tips
							</h6>
							<p class="card-text small mb-2">
								<?php if ($jumlah_anggota > 0): ?>
									Perubahan nama jabatan akan mempengaruhi semua anggota yang menggunakan jabatan ini. Pastikan perubahan sudah benar sebelum menyimpan.
								<?php else: ?>
									Jabatan ini belum digunakan, jadi Anda dapat mengubahnya dengan bebas tanpa mempengaruhi data lain.
								<?php endif; ?>
							</p>
							<a href="../index.php" class="btn btn-sm btn-outline-primary">
								<i class="lni lni-arrow-left me-1"></i> Kembali ke Daftar
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	</main>

	<!-- Load UI/UX Components -->
	<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/anggota-ui.css">
	<script src="<?php echo $base_path; ?>assets/js/classes/ToastNotification.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/classes/LoadingState.js"></script>

	<script src="<?php echo $base_path; ?>assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/count-up.min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/wow.min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/main.js"></script>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		const form = document.getElementById('formEditJabatan');
		const inputNama = document.getElementById('nama_jabatan');
		const btnUpdate = document.getElementById('btnUpdate');
		const originalValue = inputNama.value.trim();
		
		// Real-time validation
		inputNama.addEventListener('input', function() {
			const value = this.value.trim();
			const minLength = 3;
			const maxLength = 100;
			
			// Remove previous validation classes
			this.classList.remove('is-invalid', 'is-valid');
			
			if (value.length === 0) {
				// Empty, no validation yet
				return;
			} else if (value.length < minLength) {
				this.classList.add('is-invalid');
				document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan minimal ' + minLength + ' karakter';
			} else if (value.length > maxLength) {
				this.classList.add('is-invalid');
				document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan maksimal ' + maxLength + ' karakter';
			} else if (value === originalValue) {
				// Same as original, no change
				this.classList.remove('is-valid', 'is-invalid');
			} else {
				this.classList.add('is-valid');
			}
		});
		
		// Form submission
		form.addEventListener('submit', function(e) {
			const value = inputNama.value.trim();
			
			if (value.length < 3) {
				e.preventDefault();
				inputNama.classList.add('is-invalid');
				document.getElementById('nama_jabatan_error').textContent = 'Nama jabatan minimal 3 karakter';
				inputNama.focus();
				
				if (window.toast) {
					window.toast.error('Nama jabatan minimal 3 karakter', 'Validasi Gagal');
				}
				return false;
			}
			
			if (value === originalValue) {
				e.preventDefault();
				if (window.toast) {
					window.toast.info('Tidak ada perubahan yang dilakukan', 'Tidak Ada Perubahan');
				}
				return false;
			}
			
			// Show loading
			if (window.loading) {
				window.loading.show('Mengupdate jabatan...');
			}
			btnUpdate.disabled = true;
			btnUpdate.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupdate...';
		});
		
		// Show toast from PHP flash messages
		const alertMessages = document.querySelectorAll('.alert:not(.alert-info):not(.alert-warning)');
		alertMessages.forEach(function(alert) {
			const message = alert.textContent.trim();
			const type = alert.classList.contains('alert-success') ? 'success' :
						 alert.classList.contains('alert-danger') ? 'error' :
						 alert.classList.contains('alert-warning') ? 'warning' : 'info';
			
			if (window.toast && message) {
				window.toast.show(message, type);
			}
			
			// Hide original alert after showing toast
			setTimeout(() => {
				alert.style.display = 'none';
			}, 100);
		});
	});
	</script>
</body>
</html>

