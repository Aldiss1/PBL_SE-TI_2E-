<?php
$page_title = "Tambah Jabatan - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../cek_login.php';
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
        'link' => '../../list-pendaftar.php',
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
<section class="pt-50 pb-50" style="background: #f8f9fa; padding-top: 30px !important;">
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
                <div class="card wow fadeInUp" data-wow-delay=".3s" style="background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.08);">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nama_jabatan" class="form-label" style="font-weight: 600; color: #2c3e50;">Nama Jabatan *</label>
                            <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" placeholder="Contoh: Ketua Lab" required style="padding: 12px; border-radius: 5px; border: 1px solid #dee2e6;">
                        </div>
                        <button type="submit" class="button radius-10" style="background: #27AE60; color: white; padding: 12px 30px;">
                            <i class="lni lni-save"></i> Simpan
                        </button>
                        <a href="index.php" class="button radius-10 border-button" style="padding: 12px 30px; margin-left: 10px;">
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
