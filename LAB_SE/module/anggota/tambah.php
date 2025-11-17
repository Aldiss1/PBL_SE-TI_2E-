<?php
$page_title = "Tambah Anggota - Admin";
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

// Ambil daftar jabatan untuk dropdown
$list_jabatan = [];
$q_jabatan = pg_query($koneksi, "SELECT nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC");
if ($q_jabatan && pg_num_rows($q_jabatan) > 0) {
	while ($row = pg_fetch_assoc($q_jabatan)) {
		$list_jabatan[] = $row['nama_jabatan'];
	}
}

// Proses form (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = clean_input($_POST['nama'] ?? '');
    $jabatan = clean_input($_POST['jabatan'] ?? '');

    if ($nama === '') {
        pesan_kilat('Nama wajib diisi.', 'error');
    } else {
        $nama_e = pg_escape_string($koneksi, $nama);
        $jabatan_e = pg_escape_string($koneksi, $jabatan);

        $query = "INSERT INTO anggota (nama, jabatan, created_at, updated_at)
                  VALUES ('$nama_e', NULLIF('$jabatan_e',''), NOW(), NOW())";
        $result = pg_query($koneksi, $query);
        if ($result) {
            pesan_kilat('Anggota berhasil ditambahkan!', 'success');
            header('Location: index.php');
            exit;
        } else {
            pesan_kilat('Gagal menambahkan anggota. Error: ' . pg_last_error($koneksi), 'error');
        }
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Anggota</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan data anggota/personil</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <label for="nama" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama *</label>
                                <input type="text" class="form-control" id="nama" name="nama" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                            </div>
                            <div class="col-lg-6">
                                <label for="jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Jabatan</label>
                                <select class="form-select" id="jabatan" name="jabatan" style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <option value="">Pilih Jabatan</option>
                                    <?php foreach ($list_jabatan as $j): ?>
                                    <option value="<?php echo htmlspecialchars($j); ?>"><?php echo htmlspecialchars($j); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <button type="submit" class="button radius-10" style="background: #27AE60; color: white; padding: 12px 30px;">
                                    <i class="lni lni-save"></i> Simpan
                                </button>
                                <a href="index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
                                    <i class="lni lni-arrow-left"></i> Batal
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


