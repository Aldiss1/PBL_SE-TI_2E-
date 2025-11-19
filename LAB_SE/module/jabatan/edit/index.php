<?php
require_once __DIR__ . '/../../../config/koneksi.php';
require_once __DIR__ . '/../../../config/anti_injection.php';
require_once __DIR__ . '/../../../fungsi/pesan_kilat.php';
require_once __DIR__ . '/../../../cek_login.php';

$id = isset($_GET['id']) ? clean_input($_GET['id']) : '';

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
	$nama_jabatan = clean_input($_POST['nama_jabatan']);
	
	require_once __DIR__ . '/../../../fungsi/edit.php';
	if (edit_data('jabatan', ['nama_jabatan' => $nama_jabatan], $id)) {
		pesan_kilat('Data jabatan berhasil diupdate', 'success');
		header('Location: ../index.php');
		exit;
	} else {
		pesan_kilat('Gagal mengupdate data', 'error');
	}
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
		'link' => '../../../list-pendaftar.php',
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
</head>
<body>
	<!-- Sidebar -->
	<?php include __DIR__ . '/../../../template/admin_sidebar.php'; ?>

	<!-- Main Content -->
	<main class="admin-main-content">
	<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
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
				<div class="col-lg-12">
					<div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
						<form method="POST">
							<div class="mb-3">
								<label for="nama_jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama Jabatan</label>
								<input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" value="<?php echo htmlspecialchars($data['nama_jabatan']); ?>" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
							</div>
							<button type="submit" class="button radius-10" style="background: #2F80ED; color: white; padding: 12px 30px;">
								<i class="lni lni-save"></i> Update
							</button>
							<a href="../index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
								<i class="lni lni-arrow-left"></i> Batal
							</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	</main>

	<script src="<?php echo $base_path; ?>assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/count-up.min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/wow.min.js"></script>
	<script src="<?php echo $base_path; ?>assets/js/main.js"></script>
</body>
</html>

