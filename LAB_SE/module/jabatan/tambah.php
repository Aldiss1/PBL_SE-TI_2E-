<?php
$page_title = "Tambah Jabatan - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Active menu
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_jabatan'] ?? '');
    $nama = clean_input($nama);

    if ($nama === '') {
        pesan_kilat('Nama jabatan wajib diisi.', 'error');
    } else {
        $nama_e = pg_escape_string($koneksi, $nama);
        // Optional: prevent duplicates (case-insensitive)
        $dup = pg_query($koneksi, "SELECT 1 FROM jabatan WHERE LOWER(nama_jabatan) = LOWER('$nama_e') LIMIT 1");
        if ($dup && pg_num_rows($dup) > 0) {
            pesan_kilat('Jabatan sudah ada.', 'error');
        } else {
            $q = pg_query($koneksi, "INSERT INTO jabatan (nama_jabatan, created_at, updated_at) VALUES ('$nama_e', NOW(), NOW())");
            if ($q) {
                pesan_kilat('Jabatan berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan jabatan. Error: ' . pg_last_error($koneksi), 'error');
            }
        }
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Jabatan</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Masukkan nama jabatan/posisi</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row">
            <div class="col-lg-12">
                <div class="card wow fadeInUp form-card" data-wow-delay=".3s">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nama_jabatan" class="form-label form-label-custom">Nama Jabatan *</label>
                            <input type="text" class="form-control form-input-custom" id="nama_jabatan" name="nama_jabatan" placeholder="Contoh: Ketua Lab" required>
                        </div>
                        <button type="submit" class="button radius-10 btn-save">
                            <i class="lni lni-save"></i> Simpan
                        </button>
                        <a href="index.php" class="button radius-10 border-button btn-cancel">
                            <i class="lni lni-arrow-left"></i> Batal
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
