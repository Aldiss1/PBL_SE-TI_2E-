<?php
$page_title = "Tambah Penelitian - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'penelitian';
$menu_items = [
    'dashboard.php' => [
        'title' => 'Dashboard',
        'icon' => 'lni lni-home',
        'link' => '../../module/admin/dashboard.php',
        'active' => false
    ],
    'penelitian' => [
        'title' => 'Kelola Penelitian',
        'icon' => 'lni lni-microscope',
        'link' => '../../module/penelitian/index.php',
        'active' => true
    ],
    'publikasi' => [
        'title' => 'Kelola Publikasi',
        'icon' => 'lni lni-book',
        'link' => '../../module/publikasi/index.php',
        'active' => false
    ]
];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean_input($_POST['judul'] ?? '');
    $deskripsi = clean_input($_POST['deskripsi'] ?? '');
    $tahun = (int)($_POST['tahun'] ?? date('Y'));
    $status = clean_input($_POST['status'] ?? 'ongoing');
    $jumlah_tim = (int)($_POST['jumlah_tim'] ?? 0);
    $peneliti_utama = clean_input($_POST['peneliti_utama'] ?? '');
    $anggota_tim = clean_input($_POST['anggota_tim'] ?? '');
    $dosen_pembimbing = clean_input($_POST['dosen_pembimbing'] ?? '');
    $kategori = clean_input($_POST['kategori'] ?? '');
    $metodologi = clean_input($_POST['metodologi'] ?? '');
    $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $progress = isset($_POST['progress']) ? (int)$_POST['progress'] : 0;
    if ($progress < 0) $progress = 0;
    if ($progress > 100) $progress = 100;
    $keywords = clean_input($_POST['keywords'] ?? '');
    $link_dokumen = clean_input($_POST['link_dokumen'] ?? '');
    $output_hasil = clean_input($_POST['output_hasil'] ?? '');

    if ($judul === '') {
        pesan_kilat('Judul wajib diisi.', 'error');
    } else {
        $judul_e = pg_escape_string($koneksi, $judul);
        $deskripsi_e = pg_escape_string($koneksi, $deskripsi);
        $status_e = pg_escape_string($koneksi, $status);
        $peneliti_utama_e = pg_escape_string($koneksi, $peneliti_utama);
        $anggota_tim_e = pg_escape_string($koneksi, $anggota_tim);
        $dosen_pembimbing_e = pg_escape_string($koneksi, $dosen_pembimbing);
        $kategori_e = pg_escape_string($koneksi, $kategori);
        $metodologi_e = pg_escape_string($koneksi, $metodologi);
        $keywords_e = pg_escape_string($koneksi, $keywords);
        $link_dokumen_e = pg_escape_string($koneksi, $link_dokumen);
        $output_hasil_e = pg_escape_string($koneksi, $output_hasil);
        
        // Menggunakan stored procedure sp_insert_penelitian
        $query = "SELECT sp_insert_penelitian($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16)";
        $result = pg_query_params($koneksi, $query, array(
            $judul_e, $tahun, $deskripsi_e ?: null, $status_e, $jumlah_tim, 
            $peneliti_utama_e ?: null, $anggota_tim_e ?: null, $dosen_pembimbing_e ?: null, 
            $kategori_e ?: null, $metodologi_e ?: null, $tanggal_mulai ?: null, $tanggal_selesai ?: null, 
            $progress, $keywords_e ?: null, $link_dokumen_e ?: null, $output_hasil_e ?: null
        ));
        
        if ($result) {
            $row = pg_fetch_row($result);
            $new_id = $row[0] ?? null;
            if ($new_id) {
                pesan_kilat('Penelitian berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan penelitian!', 'error');
            }
        } else {
            pesan_kilat('Gagal menambahkan penelitian! Error: ' . pg_last_error($koneksi), 'error');
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Penelitian</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan data penelitian baru</p>
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
                            <i class="lni lni-microscope text-primary"></i> Form Tambah Penelitian
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formTambahPenelitian">
                            <div class="mb-4">
                                <label for="judul" class="form-label form-label-custom">
                                    <i class="lni lni-text-format"></i> Judul Penelitian <span class="text-danger">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="judul" 
                                    name="judul" 
                                    placeholder="Masukkan judul penelitian"
                                    required
                                    minlength="5"
                                    maxlength="500"
                                    autofocus
                                    value="<?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Judul penelitian yang jelas dan deskriptif (minimal 5 karakter)
                                </div>
                                <div class="invalid-feedback" id="judul_error"></div>
                            </div>

                            <div class="mb-4">
                                <label for="deskripsi" class="form-label form-label-custom">
                                    <i class="lni lni-file"></i> Deskripsi
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="deskripsi" 
                                    name="deskripsi" 
                                    rows="5"
                                    placeholder="Masukkan deskripsi lengkap tentang penelitian"><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Deskripsi lengkap tentang penelitian, tujuan, dan metodologi
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="tahun" class="form-label form-label-custom">
                                        <i class="lni lni-calendar"></i> Tahun <span class="text-danger">*</span>
                                    </label>
                                    <input 
                                        type="number" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="tahun" 
                                        name="tahun" 
                                        required
                                        min="2000" 
                                        max="2100" 
                                        value="<?php echo isset($_POST['tahun']) ? (int)$_POST['tahun'] : date('Y'); ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Tahun dimulainya penelitian
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <label for="status" class="form-label form-label-custom">
                                        <i class="lni lni-checkmark-circle"></i> Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg form-select-custom" id="status" name="status" required>
                                        <option value="ongoing" <?php echo (isset($_POST['status']) && $_POST['status'] == 'ongoing') ? 'selected' : ''; ?>>Sedang Berlangsung</option>
                                        <option value="completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'completed') ? 'selected' : ''; ?>>Selesai</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Status penelitian saat ini
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="peneliti_utama" class="form-label form-label-custom">
                                        <i class="lni lni-user"></i> Peneliti Utama
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="peneliti_utama" 
                                        name="peneliti_utama" 
                                        placeholder="Nama peneliti utama/ketua"
                                        maxlength="255"
                                        value="<?php echo isset($_POST['peneliti_utama']) ? htmlspecialchars($_POST['peneliti_utama']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Nama peneliti utama atau ketua penelitian
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="jumlah_tim" class="form-label form-label-custom">
                                        <i class="lni lni-users"></i> Jumlah Tim (Peneliti)
                                    </label>
                                    <input 
                                        type="number" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="jumlah_tim" 
                                        name="jumlah_tim" 
                                        min="0" 
                                        value="<?php echo isset($_POST['jumlah_tim']) ? (int)$_POST['jumlah_tim'] : 0; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Jumlah total anggota tim peneliti
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="anggota_tim" class="form-label form-label-custom">
                                    <i class="lni lni-user"></i> Anggota Tim
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="anggota_tim" 
                                    name="anggota_tim" 
                                    rows="3" 
                                    placeholder="Nama anggota tim (dipisah koma)"><?php echo isset($_POST['anggota_tim']) ? htmlspecialchars($_POST['anggota_tim']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Daftar anggota tim peneliti, dipisahkan dengan koma (contoh: Nama 1, Nama 2, Nama 3)
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="dosen_pembimbing" class="form-label form-label-custom">
                                    <i class="lni lni-graduation"></i> Dosen Pembimbing
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="dosen_pembimbing" 
                                    name="dosen_pembimbing" 
                                    placeholder="Nama dosen pembimbing (jika penelitian mahasiswa)"
                                    maxlength="255"
                                    value="<?php echo isset($_POST['dosen_pembimbing']) ? htmlspecialchars($_POST['dosen_pembimbing']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Nama dosen pembimbing (untuk penelitian mahasiswa)
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="kategori" class="form-label form-label-custom">
                                        <i class="lni lni-folder"></i> Kategori/Bidang
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="kategori" 
                                        name="kategori" 
                                        placeholder="Contoh: Software Testing, AI, Cloud Computing"
                                        maxlength="100"
                                        value="<?php echo isset($_POST['kategori']) ? htmlspecialchars($_POST['kategori']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Kategori atau bidang penelitian
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="progress" class="form-label form-label-custom">
                                        <i class="lni lni-bar-chart"></i> Progress (%)
                                    </label>
                                    <input 
                                        type="number" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="progress" 
                                        name="progress" 
                                        min="0" 
                                        max="100" 
                                        value="<?php echo isset($_POST['progress']) ? (int)$_POST['progress'] : 0; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Progress penelitian dalam persen (0-100)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="metodologi" class="form-label form-label-custom">
                                    <i class="lni lni-layers"></i> Metodologi
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="metodologi" 
                                    name="metodologi" 
                                    rows="3" 
                                    placeholder="Metode yang digunakan dalam penelitian"><?php echo isset($_POST['metodologi']) ? htmlspecialchars($_POST['metodologi']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Metode atau pendekatan yang digunakan dalam penelitian
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="tanggal_mulai" class="form-label form-label-custom">
                                        <i class="lni lni-calendar"></i> Tanggal Mulai
                                    </label>
                                    <input 
                                        type="date" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="tanggal_mulai" 
                                        name="tanggal_mulai" 
                                        value="<?php echo isset($_POST['tanggal_mulai']) ? htmlspecialchars($_POST['tanggal_mulai']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Tanggal dimulainya penelitian
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="tanggal_selesai" class="form-label form-label-custom">
                                        <i class="lni lni-calendar"></i> Tanggal Selesai
                                    </label>
                                    <input 
                                        type="date" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="tanggal_selesai" 
                                        name="tanggal_selesai" 
                                        value="<?php echo isset($_POST['tanggal_selesai']) ? htmlspecialchars($_POST['tanggal_selesai']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Tanggal selesainya penelitian (jika sudah selesai)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="keywords" class="form-label form-label-custom">
                                    <i class="lni lni-tag"></i> Keywords
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="keywords" 
                                    name="keywords" 
                                    placeholder="Kata kunci penelitian (dipisah koma)"
                                    maxlength="500"
                                    value="<?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Kata kunci penelitian, dipisahkan dengan koma (contoh: software testing, quality assurance, automation)
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="link_dokumen" class="form-label form-label-custom">
                                    <i class="lni lni-link"></i> Link/Dokumen
                                </label>
                                <input 
                                    type="url" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="link_dokumen" 
                                    name="link_dokumen" 
                                    placeholder="https://github.com/... atau link dokumentasi"
                                    maxlength="500"
                                    value="<?php echo isset($_POST['link_dokumen']) ? htmlspecialchars($_POST['link_dokumen']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Link ke repository, dokumentasi, atau file penelitian
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="output_hasil" class="form-label form-label-custom">
                                    <i class="lni lni-bulb"></i> Output/Hasil
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="output_hasil" 
                                    name="output_hasil" 
                                    rows="3" 
                                    placeholder="Output atau hasil dari penelitian (publikasi, produk, paten, dll)"><?php echo isset($_POST['output_hasil']) ? htmlspecialchars($_POST['output_hasil']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Output atau hasil yang dihasilkan dari penelitian
                                </div>
                            </div>

                            <div class="d-flex gap-3 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="lni lni-checkmark me-2"></i> Simpan
                                </button>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="lni lni-close me-2"></i> Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="col-lg-4">
                <!-- Quick Info Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".4s" style="border-left: 4px solid #667eea;">
                    <div class="card-body">
                        <h6 class="card-title text-primary mb-3">
                            <i class="lni lni-information me-2"></i> Informasi
                        </h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Judul wajib diisi (min 5 karakter)</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Tahun wajib diisi</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Status wajib dipilih</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Progress 0-100%</small>
                            </li>
                            <li>
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Dapat diedit nanti</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Examples Card -->
                <div class="card wow fadeInUp mb-3" data-wow-delay=".5s" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                        <h6 class="card-title text-success mb-3">
                            Contoh Kategori
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark">Software Testing</span>
                            <span class="badge bg-light text-dark">AI & Machine Learning</span>
                            <span class="badge bg-light text-dark">Cloud Computing</span>
                            <span class="badge bg-light text-dark">Code Analysis</span>
                            <span class="badge bg-light text-dark">Software Architecture</span>
                            <span class="badge bg-light text-dark">Semantic Web</span>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card wow fadeInUp" data-wow-delay=".6s" style="border-left: 4px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="card-title text-warning mb-3">
                            <i class="lni lni-question-circle me-2"></i> Butuh Bantuan?
                        </h6>
                        <p class="card-text small mb-2">
                            Pastikan semua informasi penelitian diisi dengan lengkap dan akurat. Data penelitian akan ditampilkan di halaman publik website laboratorium.
                        </p>
                        <a href="index.php" class="btn btn-sm btn-outline-primary">
                            <i class="lni lni-arrow-left me-1"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

