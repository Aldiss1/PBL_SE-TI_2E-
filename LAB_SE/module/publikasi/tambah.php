<?php
$page_title = "Tambah Publikasi - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'publikasi';
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
        'active' => false
    ],
    'publikasi' => [
        'title' => 'Kelola Publikasi',
        'icon' => 'lni lni-book',
        'link' => '../../module/publikasi/index.php',
        'active' => true
    ]
];

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = clean_input($_POST['judul'] ?? '');
    $tipe = clean_input($_POST['tipe'] ?? 'journal');
    $tahun = (int)($_POST['tahun'] ?? date('Y'));
    $penulis_utama = clean_input($_POST['penulis_utama'] ?? '');
    $daftar_penulis = clean_input($_POST['daftar_penulis'] ?? '');
    $nama_journal_conference = clean_input($_POST['nama_journal_conference'] ?? '');
    $volume = clean_input($_POST['volume'] ?? '');
    $issue = clean_input($_POST['issue'] ?? '');
    $halaman = clean_input($_POST['halaman'] ?? '');
    $doi = clean_input($_POST['doi'] ?? '');
    $abstract = clean_input($_POST['abstract'] ?? '');
    $keywords = clean_input($_POST['keywords'] ?? '');
    $link_url = clean_input($_POST['link_url'] ?? '');
    $status = clean_input($_POST['status'] ?? 'published');
    $scopus_indexed = isset($_POST['scopus_indexed']) ? true : false;
    $quartile = clean_input($_POST['quartile'] ?? '');
    $tanggal_publikasi = !empty($_POST['tanggal_publikasi']) ? $_POST['tanggal_publikasi'] : null;

    if ($judul === '') {
        pesan_kilat('Judul wajib diisi.', 'error');
    } else {
        $judul_e = pg_escape_string($koneksi, $judul);
        $tipe_e = pg_escape_string($koneksi, $tipe);
        $penulis_utama_e = pg_escape_string($koneksi, $penulis_utama);
        $daftar_penulis_e = pg_escape_string($koneksi, $daftar_penulis);
        $nama_journal_conference_e = pg_escape_string($koneksi, $nama_journal_conference);
        $volume_e = pg_escape_string($koneksi, $volume);
        $issue_e = pg_escape_string($koneksi, $issue);
        $halaman_e = pg_escape_string($koneksi, $halaman);
        $doi_e = pg_escape_string($koneksi, $doi);
        $abstract_e = pg_escape_string($koneksi, $abstract);
        $keywords_e = pg_escape_string($koneksi, $keywords);
        $link_url_e = pg_escape_string($koneksi, $link_url);
        $status_e = pg_escape_string($koneksi, $status);
        $quartile_e = pg_escape_string($koneksi, $quartile);
        
        // Menggunakan stored procedure sp_insert_publikasi
        $query = "SELECT sp_insert_publikasi($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, $17)";
        $result = pg_query_params($koneksi, $query, array(
            $judul_e, $tipe_e, $tahun, 
            $penulis_utama_e ?: null, $daftar_penulis_e ?: null, $nama_journal_conference_e ?: null,
            $volume_e ?: null, $issue_e ?: null, $halaman_e ?: null, $doi_e ?: null,
            $abstract_e ?: null, $keywords_e ?: null, $link_url_e ?: null, $status_e,
            $scopus_indexed, $quartile_e ?: null, $tanggal_publikasi ?: null
        ));
        
        if ($result) {
            $row = pg_fetch_row($result);
            $new_id = $row[0] ?? null;
            if ($new_id) {
                // Refresh materialized views setelah insert publikasi
                if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
                    require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
                    if (function_exists('refresh_views_complete')) {
                        @refresh_views_complete();
                    }
                }
                pesan_kilat('Publikasi berhasil ditambahkan!', 'success');
                header('Location: index.php');
                exit;
            } else {
                pesan_kilat('Gagal menambahkan publikasi!', 'error');
            }
        } else {
            pesan_kilat('Gagal menambahkan publikasi! Error: ' . pg_last_error($koneksi), 'error');
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Tambah Publikasi</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Tambahkan data publikasi baru</p>
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
                            <i class="lni lni-book text-primary"></i> Form Tambah Publikasi
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="formTambahPublikasi">
                            <div class="mb-4">
                                <label for="judul" class="form-label form-label-custom">
                                    <i class="lni lni-text-format"></i> Judul Publikasi <span class="text-danger">*</span>
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="judul" 
                                    name="judul" 
                                    rows="3"
                                    placeholder="Masukkan judul lengkap publikasi"
                                    required
                                    minlength="5"
                                    autofocus><?php echo isset($_POST['judul']) ? htmlspecialchars($_POST['judul']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Judul lengkap publikasi (minimal 5 karakter)
                                </div>
                                <div class="invalid-feedback" id="judul_error"></div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="tipe" class="form-label form-label-custom">
                                        <i class="lni lni-folder"></i> Tipe <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg form-select-custom" id="tipe" name="tipe" required>
                                        <option value="journal" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] == 'journal') ? 'selected' : ''; ?>>Journal</option>
                                        <option value="conference" <?php echo (isset($_POST['tipe']) && $_POST['tipe'] == 'conference') ? 'selected' : ''; ?>>Conference</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Tipe publikasi
                                    </div>
                                </div>

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
                                        <i class="lni lni-information"></i> Tahun publikasi
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="penulis_utama" class="form-label form-label-custom">
                                        <i class="lni lni-user"></i> Penulis Utama
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="penulis_utama" 
                                        name="penulis_utama" 
                                        placeholder="Nama penulis utama"
                                        maxlength="255"
                                        value="<?php echo isset($_POST['penulis_utama']) ? htmlspecialchars($_POST['penulis_utama']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Nama penulis utama publikasi
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="status" class="form-label form-label-custom">
                                        <i class="lni lni-checkmark-circle"></i> Status <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg form-select-custom" id="status" name="status" required>
                                        <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') ? 'selected' : 'selected'; ?>>Published</option>
                                        <option value="accepted" <?php echo (isset($_POST['status']) && $_POST['status'] == 'accepted') ? 'selected' : ''; ?>>Accepted</option>
                                        <option value="submitted" <?php echo (isset($_POST['status']) && $_POST['status'] == 'submitted') ? 'selected' : ''; ?>>Submitted</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Status publikasi
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="daftar_penulis" class="form-label form-label-custom">
                                    <i class="lni lni-user"></i> Daftar Penulis
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="daftar_penulis" 
                                    name="daftar_penulis" 
                                    rows="3" 
                                    placeholder="Nama semua penulis (dipisah koma)"><?php echo isset($_POST['daftar_penulis']) ? htmlspecialchars($_POST['daftar_penulis']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Daftar semua penulis, dipisahkan dengan koma (contoh: Nama 1, Nama 2, Nama 3)
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="nama_journal_conference" class="form-label form-label-custom">
                                    <i class="lni lni-library"></i> Nama Journal/Conference
                                </label>
                                <input 
                                    type="text" 
                                    class="form-control form-control-lg form-input-custom" 
                                    id="nama_journal_conference" 
                                    name="nama_journal_conference" 
                                    placeholder="Nama journal atau conference"
                                    maxlength="500"
                                    value="<?php echo isset($_POST['nama_journal_conference']) ? htmlspecialchars($_POST['nama_journal_conference']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Nama journal atau conference tempat publikasi
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-4">
                                    <label for="volume" class="form-label form-label-custom">
                                        <i class="lni lni-files"></i> Volume
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="volume" 
                                        name="volume" 
                                        placeholder="Volume"
                                        maxlength="50"
                                        value="<?php echo isset($_POST['volume']) ? htmlspecialchars($_POST['volume']) : ''; ?>">
                                </div>
                                <div class="col-lg-4">
                                    <label for="issue" class="form-label form-label-custom">
                                        <i class="lni lni-file"></i> Issue
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="issue" 
                                        name="issue" 
                                        placeholder="Issue/Number"
                                        maxlength="50"
                                        value="<?php echo isset($_POST['issue']) ? htmlspecialchars($_POST['issue']) : ''; ?>">
                                </div>
                                <div class="col-lg-4">
                                    <label for="halaman" class="form-label form-label-custom">
                                        <i class="lni lni-list"></i> Halaman
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="halaman" 
                                        name="halaman" 
                                        placeholder="123-145"
                                        maxlength="50"
                                        value="<?php echo isset($_POST['halaman']) ? htmlspecialchars($_POST['halaman']) : ''; ?>">
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="doi" class="form-label form-label-custom">
                                        <i class="lni lni-link"></i> DOI
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="doi" 
                                        name="doi" 
                                        placeholder="10.xxxx/xxxxx"
                                        maxlength="255"
                                        value="<?php echo isset($_POST['doi']) ? htmlspecialchars($_POST['doi']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Digital Object Identifier
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="tanggal_publikasi" class="form-label form-label-custom">
                                        <i class="lni lni-calendar"></i> Tanggal Publikasi
                                    </label>
                                    <input 
                                        type="date" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="tanggal_publikasi" 
                                        name="tanggal_publikasi" 
                                        value="<?php echo isset($_POST['tanggal_publikasi']) ? htmlspecialchars($_POST['tanggal_publikasi']) : ''; ?>">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="abstract" class="form-label form-label-custom">
                                    <i class="lni lni-file"></i> Abstract
                                </label>
                                <textarea 
                                    class="form-control form-input-custom" 
                                    id="abstract" 
                                    name="abstract" 
                                    rows="5"
                                    placeholder="Masukkan abstract publikasi"><?php echo isset($_POST['abstract']) ? htmlspecialchars($_POST['abstract']) : ''; ?></textarea>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Abstract atau ringkasan publikasi
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
                                    placeholder="Kata kunci publikasi (dipisah koma)"
                                    maxlength="500"
                                    value="<?php echo isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : ''; ?>">
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Kata kunci publikasi, dipisahkan dengan koma
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-6">
                                    <label for="link_url" class="form-label form-label-custom">
                                        <i class="lni lni-link"></i> Link/URL
                                    </label>
                                    <input 
                                        type="url" 
                                        class="form-control form-control-lg form-input-custom" 
                                        id="link_url" 
                                        name="link_url" 
                                        placeholder="https://..."
                                        maxlength="500"
                                        value="<?php echo isset($_POST['link_url']) ? htmlspecialchars($_POST['link_url']) : ''; ?>">
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Link ke publikasi online
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label for="quartile" class="form-label form-label-custom">
                                        <i class="lni lni-bar-chart"></i> Quartile
                                    </label>
                                    <select class="form-select form-select-lg form-select-custom" id="quartile" name="quartile">
                                        <option value="">Pilih Quartile</option>
                                        <option value="Q1" <?php echo (isset($_POST['quartile']) && $_POST['quartile'] == 'Q1') ? 'selected' : ''; ?>>Q1</option>
                                        <option value="Q2" <?php echo (isset($_POST['quartile']) && $_POST['quartile'] == 'Q2') ? 'selected' : ''; ?>>Q2</option>
                                        <option value="Q3" <?php echo (isset($_POST['quartile']) && $_POST['quartile'] == 'Q3') ? 'selected' : ''; ?>>Q3</option>
                                        <option value="Q4" <?php echo (isset($_POST['quartile']) && $_POST['quartile'] == 'Q4') ? 'selected' : ''; ?>>Q4</option>
                                    </select>
                                    <div class="form-text">
                                        <i class="lni lni-information"></i> Quartile jurnal (jika journal)
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="scopus_indexed" 
                                        name="scopus_indexed" 
                                        value="1"
                                        <?php echo (isset($_POST['scopus_indexed']) && $_POST['scopus_indexed']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label form-label-custom" for="scopus_indexed">
                                        <i class="lni lni-checkmark-circle"></i> Terindeks Scopus
                                    </label>
                                </div>
                                <div class="form-text">
                                    <i class="lni lni-information"></i> Centang jika publikasi terindeks di Scopus
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
                                <small>Tipe wajib dipilih</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Tahun wajib diisi</small>
                            </li>
                            <li class="mb-2">
                                <i class="lni lni-checkmark-circle text-success me-2"></i>
                                <small>Status wajib dipilih</small>
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
                            Contoh Tipe
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-light text-dark">Journal</span>
                            <span class="badge bg-light text-dark">Conference</span>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Status:</small>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <span class="badge bg-success">Published</span>
                                <span class="badge bg-info">Accepted</span>
                                <span class="badge bg-warning">Submitted</span>
                            </div>
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
                            Pastikan semua informasi publikasi diisi dengan lengkap dan akurat. Data publikasi akan ditampilkan di halaman publik website laboratorium.
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

