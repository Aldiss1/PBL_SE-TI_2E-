<?php
// FILE: kelola_roadmap.php
// Kelola Roadmap - Admin (Bootstrap 5, PostgreSQL)
// Pastikan sudah include koneksi & cek_login sesuai struktur project

$page_title = "Kelola Roadmap - Admin"; 

require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../fungsi/pesan_kilat.php'; 
require_once __DIR__ . '/../auth/cek_login.php'; 

/** @var PgSql\Connection $koneksi */

if (!isset($koneksi) || !$koneksi) {
    die("Koneksi database gagal dimuat. Cek file koneksi.php Anda.");
}

if (!isset($base_path)) {
    $base_path = '../../';
}

// --------------------------------------------------
// Variabel awal
// --------------------------------------------------
$data_edit = null;
$pesan = '';

// --- Sidebar/menu (tidak wajib diubah) ---
$current_page = 'roadmap';
$menu_items = [
    'dashboard.php' => ['title'=>'Dashboard','icon'=>'lni lni-home','link'=>'../../module/admin/dashboard.php','active'=>false],
    'anggota' => ['title'=>'Kelola Anggota','icon'=>'lni lni-users','link'=>'../../module/anggota/index.php','active'=>false],
    'jabatan' => ['title'=>'Kelola Jabatan','icon'=>'lni lni-briefcase','link'=>'../../module/jabatan/index.php','active'=>false],
    'pendaftar' => ['title'=>'Kelola Pendaftar','icon'=>'lni lni-list','link'=>'../../module/pendaftar/index.php','active'=>false],
    'blog' => ['title'=>'Kelola Blog','icon'=>'lni lni-text-format','link'=>'../../module/blog/index.php','active'=>false],
    'profil' => ['title'=>'Kelola Profil','icon'=>'lni lni-cog','link'=>'../../module/profil/index.php','active'=>false],
    'roadmap' => ['title'=>'Kelola Roadmap','icon'=>'lni lni-map-marker','link'=>'../../module/profil/kelola_roadmap.php','active'=>true],
];

// --------------------------------------------------
// 2. CEK DAN BUAT TABEL JIKA BELUM ADA
// --------------------------------------------------

// Cek tabel roadmap_periode
$check_periode = pg_query($koneksi, "
    SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'roadmap_periode'
    )
");

if ($check_periode) {
    $table_periode_exists = pg_fetch_result($check_periode, 0, 0);
    if ($table_periode_exists !== 't') {
        // Buat tabel roadmap_periode
        pg_query($koneksi, "
            CREATE TABLE IF NOT EXISTS roadmap_periode (
                id SERIAL PRIMARY KEY,
                tahun_periode VARCHAR(100) NOT NULL,
                judul_fase VARCHAR(255) NOT NULL,
                urutan INTEGER NOT NULL DEFAULT 1,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}

// Cek tabel roadmap_item
$check_item = pg_query($koneksi, "
    SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'roadmap_item'
    )
");

if ($check_item) {
    $table_item_exists = pg_fetch_result($check_item, 0, 0);
    if ($table_item_exists !== 't') {
        // Buat tabel roadmap_item
        pg_query($koneksi, "
            CREATE TABLE IF NOT EXISTS roadmap_item (
                id SERIAL PRIMARY KEY,
                id_periode INTEGER NOT NULL REFERENCES roadmap_periode(id) ON DELETE CASCADE,
                deskripsi_tugas TEXT NOT NULL,
                urutan INTEGER NOT NULL DEFAULT 1,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
}

// --------------------------------------------------
// 3. LOGIC CRUD ROADMAP PERIODE & ITEM
// --------------------------------------------------

// Ambil list periode
$query_periode = "SELECT id, tahun_periode, judul_fase, urutan FROM roadmap_periode ORDER BY urutan ASC";
$result_periode = pg_query($koneksi, $query_periode);
if ($result_periode === false) {
    // Jika query gagal, kemungkinan tabel belum ada atau ada error
    $error = pg_last_error($koneksi);
    if (strpos($error, 'does not exist') !== false) {
        // Tabel belum ada, coba buat lagi
        pg_query($koneksi, "
            CREATE TABLE IF NOT EXISTS roadmap_periode (
                id SERIAL PRIMARY KEY,
                tahun_periode VARCHAR(100) NOT NULL,
                judul_fase VARCHAR(255) NOT NULL,
                urutan INTEGER NOT NULL DEFAULT 1,
                created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )
        ");
        // Coba query lagi
        $result_periode = pg_query($koneksi, $query_periode);
    }
}
$periode_list = ($result_periode !== false) ? pg_fetch_all($result_periode) : [];

// --- HANDLING: Tambah Periode ---
if (isset($_POST['tambah_periode'])) {
    $tahun_periode = trim($_POST['tahun_periode'] ?? '');
    $judul_fase = trim($_POST['judul_fase'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 1);

    if ($tahun_periode === '' || $judul_fase === '') {
        $pesan = "<div class='alert alert-danger'>Isi tahun/periode dan judul fase.</div>";
    } else {
        // Escape input untuk keamanan
        $tahun_periode_escaped = pg_escape_string($koneksi, $tahun_periode);
        $judul_fase_escaped = pg_escape_string($koneksi, $judul_fase);
        
        $sql = "INSERT INTO roadmap_periode (tahun_periode, judul_fase, urutan) VALUES ($1, $2, $3)";
        $res = pg_query_params($koneksi, $sql, [$tahun_periode_escaped, $judul_fase_escaped, $urutan]);
        if ($res) {
            // redirect agar form tidak submit ulang saat reload
            header("Location: kelola_roadmap.php");
            exit;
        } else {
            $error_msg = pg_last_error($koneksi);
            $pesan = "<div class='alert alert-danger'>Gagal menyimpan periode: " . htmlspecialchars($error_msg) . "</div>";
        }
    }
}

// --- HANDLING: Edit Periode ---
if (isset($_POST['edit_periode'])) {
    $id_edit = (int)($_POST['id_periode'] ?? 0);
    $tahun_periode = trim($_POST['tahun_periode'] ?? '');
    $judul_fase = trim($_POST['judul_fase'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 1);

    if ($id_edit <= 0 || $tahun_periode === '' || $judul_fase === '') {
        $pesan = "Data edit tidak valid.";
    } else {
        $sql = "UPDATE roadmap_periode SET tahun_periode = $1, judul_fase = $2, urutan = $3, updated_at = NOW() WHERE id = $4";
        $res = pg_query_params($koneksi, $sql, [$tahun_periode, $judul_fase, $urutan, $id_edit]);
        if ($res) {
            header("Location: kelola_roadmap.php");
            exit;
        } else {
            $pesan = "Gagal update periode: " . pg_last_error($koneksi);
        }
    }
}

// --- HANDLING: Hapus Periode (beserta item di dalamnya) ---
if (isset($_GET['hapus_periode'])) {
    $id_hapus = (int)$_GET['hapus_periode'];
    // Hapus item tugas yang berelasi dulu (jika ingin cascade manual)
    $del_items = "DELETE FROM roadmap_item WHERE id_periode = $1";
    pg_query_params($koneksi, $del_items, [$id_hapus]);
    // Hapus periode
    $del_periode = "DELETE FROM roadmap_periode WHERE id = $1";
    $res = pg_query_params($koneksi, $del_periode, [$id_hapus]);
    header("Location: kelola_roadmap.php");
    exit;
}

// --------------------------------------------------
// C. LOGIC CRUD ITEM (Jika kelola_tugas aktif)
// --------------------------------------------------
$id_periode_aktif = null;
$periode_aktif = null;
$tugas_list = [];

if (isset($_GET['kelola_tugas'])) {
    $id_periode_aktif = (int)$_GET['kelola_tugas'];

    $query_aktif = "SELECT id, tahun_periode, judul_fase FROM roadmap_periode WHERE id = $1";
    $result_aktif = pg_query_params($koneksi, $query_aktif, [$id_periode_aktif]);

    if ($result_aktif && pg_num_rows($result_aktif) > 0) {
        $periode_aktif = pg_fetch_assoc($result_aktif);

        $query_tugas = "SELECT id, deskripsi_tugas, urutan FROM roadmap_item WHERE id_periode = $1 ORDER BY urutan ASC";
        $result_tugas = pg_query_params($koneksi, $query_tugas, [$id_periode_aktif]);
        $tugas_list = ($result_tugas !== false) ? pg_fetch_all($result_tugas) : [];
    }

    // Tambah tugas
    if (isset($_POST['tambah_tugas'])) {
        $deskripsi = trim($_POST['deskripsi_tugas'] ?? '');
        $urutan_t = (int)($_POST['urutan'] ?? 1);
        $id_per = (int)($_POST['id_periode'] ?? $id_periode_aktif);

        if ($deskripsi === '') {
            $pesan = "Deskripsi tugas tidak boleh kosong.";
        } else {
            $sql = "INSERT INTO roadmap_item (id_periode, deskripsi_tugas, urutan) VALUES ($1, $2, $3)";
            $res = pg_query_params($koneksi, $sql, [$id_per, $deskripsi, $urutan_t]);
            if ($res) {
                header("Location: kelola_roadmap.php?kelola_tugas=" . $id_per);
                exit;
            } else {
                $pesan = "Gagal menyimpan tugas: " . pg_last_error($koneksi);
            }
        }
    }

    // Edit tugas
    if (isset($_POST['edit_tugas'])) {
        $id_t = (int)($_POST['id_tugas'] ?? 0);
        $deskripsi = trim($_POST['deskripsi_tugas'] ?? '');
        $urutan_t = (int)($_POST['urutan'] ?? 1);
        $id_per = (int)($_POST['id_periode'] ?? $id_periode_aktif);

        if ($id_t <= 0 || $deskripsi === '') {
            $pesan = "Data edit tidak valid.";
        } else {
            $sql = "UPDATE roadmap_item SET deskripsi_tugas = $1, urutan = $2 WHERE id = $3";
            $res = pg_query_params($koneksi, $sql, [$deskripsi, $urutan_t, $id_t]);
            if ($res) {
                header("Location: kelola_roadmap.php?kelola_tugas=" . $id_per);
                exit;
            } else {
                $pesan = "Gagal update tugas: " . pg_last_error($koneksi);
            }
        }
    }

    // Hapus tugas
    if (isset($_GET['hapus_tugas'])) {
        $id_hapus = (int)$_GET['hapus_tugas'];
        $id_per = $id_periode_aktif;
        $del = "DELETE FROM roadmap_item WHERE id = $1";
        pg_query_params($koneksi, $del, [$id_hapus]);
        header("Location: kelola_roadmap.php?kelola_tugas=" . $id_per);
        exit;
    }
}

// --------------------------------------------------
// 4. VIEW (HTML)
// --------------------------------------------------
include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<!-- Include CSS untuk modal konfirmasi -->
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/anggota-ui.css"/>

<style>
/* Styling untuk Kelola Roadmap */
.roadmap-container {
    padding: 20px;
    padding-right: 40px;
    max-width: 100%;
    box-sizing: border-box;
    background: #f8f9fa;
    min-height: calc(100vh - 60px);
}

.header-content-roadmap {
    margin-bottom: 30px;
}

.header-content-roadmap h1 {
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 32px;
    line-height: 1.2;
}

.header-content-roadmap p {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 15px;
    line-height: 1.6;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    padding: 0;
    margin-bottom: 25px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}

.card-header {
    padding: 15px 20px;
    border-bottom: 2px solid #f0f0f0;
    font-weight: 700;
    font-size: 18px;
    border-radius: 8px 8px 0 0;
}

.card-header.bg-primary {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%) !important;
    color: white;
    border-bottom: none;
}

.card-header.bg-dark {
    background: linear-gradient(135deg, #2c3e50 0%, #1a1a1a 100%) !important;
    color: white;
    border-bottom: none;
}

.card-body {
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background-color: #f8f9fa;
    font-weight: 700;
    color: #2c3e50;
    border-bottom: 2px solid #dee2e6;
    padding: 15px 12px;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.table tbody td {
    padding: 15px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
    word-wrap: break-word;
}

/* Fix: Pastikan tombol di kolom AKSI sejajar horizontal */
.table tbody td:last-child {
    white-space: nowrap;
    width: auto;
    min-width: 250px;
}

.table tbody td:last-child > div {
    display: inline-flex !important;
    gap: 6px;
    align-items: center;
    flex-wrap: nowrap !important;
    width: 100%;
}

.table tbody td:last-child .btn {
    white-space: nowrap !important;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(0);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-success {
    background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #229954 0%, #1E8449 100%);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1E5FCC 0%, #1A4FA0 100%);
    color: white;
}

.btn-warning {
    background: linear-gradient(135deg, #F39C12 0%, #E67E22 100%);
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #E67E22 0%, #D35400 100%);
    color: white;
}

.btn-danger {
    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #C0392B 0%, #A93226 100%);
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, #3498DB 0%, #2980B9 100%);
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2980B9 0%, #21618C 100%);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #95A5A6 0%, #7F8C8D 100%);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #7F8C8D 0%, #6C7A7B 100%);
    color: white;
}

.modal {
    z-index: 1055 !important;
}

.modal-backdrop {
    z-index: 1050 !important;
    background-color: rgba(0,0,0,0.5) !important;
}

.modal-dialog {
    z-index: 1056 !important;
    position: relative;
}

.modal-content {
    position: relative;
    z-index: 1;
}

.modal-header,
.modal-body,
.modal-footer {
    position: relative;
    z-index: 1;
    pointer-events: auto !important;
}

/* FIX CRITICAL: Pastikan tombol modal bisa diklik */
.modal {
    pointer-events: none !important;
}

.modal.show,
.modal.fade.show {
    pointer-events: auto !important;
}

.modal .btn-close {
    pointer-events: auto !important;
    cursor: pointer !important;
    z-index: 99999 !important;
    position: relative !important;
    background: transparent !important;
    border: none !important;
}

.modal .modal-footer .btn {
    pointer-events: auto !important;
    cursor: pointer !important;
    z-index: 99999 !important;
    position: relative !important;
}

.modal-content {
    pointer-events: auto !important;
}

.modal-dialog {
    margin: 1.75rem auto;
    max-width: 600px;
}

.modal-content {
    border: none;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    overflow: visible !important;
    pointer-events: auto !important;
    position: relative;
    z-index: 1;
    position: relative;
    z-index: 1;
    pointer-events: auto !important;
}

.modal-header {
    background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
    color: white;
    border-radius: 0;
    padding: 20px 25px;
    border-bottom: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
}

.modal-header .modal-title {
    font-weight: 700;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.modal-header .modal-title i {
    font-size: 22px;
}

.modal-header .btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.9;
    padding: 0.5rem;
    margin: 0;
    cursor: pointer !important;
    z-index: 9999 !important;
    position: relative !important;
    pointer-events: auto !important;
    background: transparent !important;
    border: none !important;
    width: auto !important;
    height: auto !important;
    line-height: 1 !important;
    font-size: 1.5rem !important;
}

.modal-header .btn-close:hover {
    opacity: 1;
    transform: scale(1.1);
    cursor: pointer !important;
}

.modal-header .btn-close:focus {
    outline: 2px solid rgba(255,255,255,0.5);
    outline-offset: 2px;
}

.modal-body {
    padding: 30px 25px;
    background: #ffffff;
}

.modal-body .mb-3 {
    margin-bottom: 20px !important;
}

.modal-body .form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
    line-height: 1.5;
}

.modal-body .form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    box-sizing: border-box;
    background: #ffffff;
}

.modal-body .form-control:focus {
    outline: none;
    border-color: #2F80ED;
    box-shadow: 0 0 0 4px rgba(47, 128, 237, 0.1);
    background: #ffffff;
}

.modal-body .form-control::placeholder {
    color: #95a5a6;
    opacity: 0.7;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
}

.modal-footer {
    padding: 20px 25px;
    border-top: 1px solid #f0f0f0;
    border-radius: 0;
    background: #f8f9fa;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    position: relative;
    z-index: 10;
    pointer-events: auto !important;
}

.modal-footer .btn {
    min-width: 120px;
    padding: 10px 20px;
    font-weight: 600;
    cursor: pointer !important;
    z-index: 9999 !important;
    position: relative !important;
    pointer-events: auto !important;
    border: none !important;
    text-decoration: none !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.modal-footer .btn:hover {
    cursor: pointer !important;
    pointer-events: auto !important;
}

.modal-footer .btn:active {
    cursor: pointer !important;
    pointer-events: auto !important;
}

.modal-footer .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed !important;
    pointer-events: auto !important;
}

.alert {
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 20px;
    border-left: 4px solid;
}

.alert-success {
    background-color: #d4edda;
    border-color: #27AE60;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #E74C3C;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #F39C12;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #3498DB;
    color: #0c5460;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #95A5A6;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
    opacity: 0.5;
}

@media (max-width: 768px) {
    .roadmap-container {
        padding: 15px;
    }
    
    .header-content-roadmap h1 {
        font-size: 24px;
    }
    
    .card-body {
        padding: 15px;
    }
    
    .table {
        font-size: 13px;
    }
    
    .table thead th,
    .table tbody td {
        padding: 10px 8px;
        font-size: 12px;
    }
    
    .btn {
        padding: 6px 10px;
        font-size: 12px;
        gap: 4px;
    }
    
    .btn i {
        font-size: 14px;
    }
}
</style>

<!-- Main Content -->
<main class="admin-main-content">
<div class="roadmap-container">
    <div class="header-content-roadmap">
        <h1>Kelola Roadmap</h1>
        <p>Atur periode (tahun) dan daftar tugas di setiap fase pengembangan laboratorium</p>
    </div>
    
        <?php if ($pesan): ?>
            <?php echo $pesan; ?>
        <?php endif; ?>

        <div class="row" style="margin: 0;">
            <div class="col-12" style="padding: 0;">
                <?php if ($periode_aktif): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            Kelola Tugas: <?php echo htmlspecialchars($periode_aktif['tahun_periode']) . ' - ' . htmlspecialchars($periode_aktif['judul_fase']); ?>
                        </div>
                        <div class="card-body">
                            <a href="kelola_roadmap.php" class="btn btn-sm btn-secondary mb-3"><i class="lni lni-arrow-left"></i> Kembali ke Periode</a>

                            <!-- Tambah tugas (modal trigger) -->
                            <button type="button" class="btn btn-sm btn-success mb-3"
                                data-bs-toggle="modal" data-bs-target="#modalTambahTugas"
                                onclick="openModalTambahTugas(event)">
                                <i class="lni lni-plus"></i> Tambah Tugas
                            </button>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width:50px">Urutan</th>
                                            <th>Deskripsi Tugas</th>
                                            <th style="width:150px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($tugas_list)): foreach ($tugas_list as $tugas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tugas['urutan']); ?></td>
                                            <td><?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?></td>
                                            <td>
                                                <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: nowrap;">
                                                <!-- Edit trigger: data attributes diisi -->
                                                    <button type="button" class="btn btn-sm btn-warning" style="white-space: nowrap;"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditTugas"
                                                    data-id="<?php echo $tugas['id']; ?>"
                                                    data-deskripsi="<?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?>"
                                                    data-urutan="<?php echo htmlspecialchars($tugas['urutan']); ?>"
                                                    onclick="openModalEditTugas(event, this)">
                                                        <i class="lni lni-pencil"></i> Edit
                                                </button>
                                                    <button type="button" class="btn btn-sm btn-danger" style="white-space: nowrap;"
                                                            onclick="confirmDeleteTugas(<?php echo $tugas['id']; ?>, '<?php echo htmlspecialchars(addslashes($tugas['deskripsi_tugas'])); ?>', <?php echo $id_periode_aktif; ?>)">
                                                        <i class="lni lni-trash"></i> Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; else: ?>
                                        <tr><td colspan="3" class="text-center">Belum ada tugas di fase ini.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div class="card-header bg-dark text-white">Daftar Periode Roadmap</div>
                        <div class="card-body">
                            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahPeriode" onclick="openModalTambahPeriode(event)">
                                <i class="lni lni-plus"></i> Tambah Periode
                            </button>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width:50px">Urutan</th>
                                            <th>Tahun & Periode</th>
                                            <th>Judul Fase</th>
                                            <th style="width:250px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($periode_list)): foreach ($periode_list as $periode): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($periode['urutan']); ?></td>
                                                <td><?php echo htmlspecialchars($periode['tahun_periode']); ?></td>
                                                <td><?php echo htmlspecialchars($periode['judul_fase']); ?></td>
                                                <td>
                                                    <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: nowrap;">
                                                        <a href="?kelola_tugas=<?php echo $periode['id']; ?>" class="btn btn-sm btn-info text-white" style="white-space: nowrap;">
                                                            <i class="lni lni-list"></i> Kelola Tugas
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-warning" style="white-space: nowrap;"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditPeriode"
                                                        data-id="<?php echo $periode['id']; ?>"
                                                        data-tahun="<?php echo htmlspecialchars($periode['tahun_periode']); ?>"
                                                        data-judul="<?php echo htmlspecialchars($periode['judul_fase']); ?>"
                                                        data-urutan="<?php echo htmlspecialchars($periode['urutan']); ?>"
                                                        onclick="openModalEditPeriode(event, this)">
                                                            <i class="lni lni-pencil"></i> Edit
                                                    </button>
                                                        <button type="button" class="btn btn-sm btn-danger" style="white-space: nowrap;"
                                                                onclick="confirmDeletePeriode(<?php echo $periode['id']; ?>, '<?php echo htmlspecialchars(addslashes($periode['tahun_periode'] . ' - ' . $periode['judul_fase'])); ?>')">
                                                            <i class="lni lni-trash"></i> Hapus
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="4" class="text-center">Roadmap belum memiliki periode. Silakan tambah periode baru.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Tambah Periode -->
<div class="modal fade" id="modalTambahPeriode" tabindex="-1" aria-labelledby="modalTambahPeriodeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="kelola_roadmap.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahPeriodeLabel">
            <i class="lni lni-plus"></i> Tambah Periode Baru
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="tahun_periode" class="form-label">
              <i class="lni lni-calendar"></i> Tahun & Periode
            </label>
            <input type="text" class="form-control" id="tahun_periode" name="tahun_periode" 
                   placeholder="Contoh: 2024 Q1 - Q4" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Masukkan tahun dan periode dalam format: Tahun Q1 - Q4
            </small>
          </div>
          <div class="mb-3">
            <label for="judul_fase" class="form-label">
              <i class="lni lni-text-format"></i> Judul Fase
            </label>
            <input type="text" class="form-control" id="judul_fase" name="judul_fase" 
                   placeholder="Masukkan judul fase" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Judul untuk fase pengembangan ini
            </small>
          </div>
          <div class="mb-3">
            <label for="urutan" class="form-label">
              <i class="lni lni-sort-amount-asc"></i> Urutan Tampil
            </label>
            <?php
            $urutan_periode_baru = 1;
            if (!empty($periode_list)) {
                $last_periode = end($periode_list);
                $urutan_periode_baru = $last_periode['urutan'] + 1;
            }
            ?>
            <input type="number" class="form-control" id="urutan" name="urutan" value="<?php echo $urutan_periode_baru; ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="lni lni-close"></i> Batal
          </button>
          <button type="submit" name="tambah_periode" class="btn btn-primary">
            <i class="lni lni-save"></i> Simpan Periode
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Edit Periode -->
<div class="modal fade" id="modalEditPeriode" tabindex="-1" aria-labelledby="modalEditPeriodeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="kelola_roadmap.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditPeriodeLabel">
            <i class="lni lni-pencil"></i> Edit Periode
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_id_periode" name="id_periode" value="">
          <div class="mb-3">
            <label for="edit_tahun_periode" class="form-label">
              <i class="lni lni-calendar"></i> Tahun & Periode
            </label>
            <input type="text" class="form-control" id="edit_tahun_periode" name="tahun_periode" 
                   placeholder="Contoh: 2024 Q1 - Q4" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Masukkan tahun dan periode dalam format: Tahun Q1 - Q4
            </small>
          </div>
          <div class="mb-3">
            <label for="edit_judul_fase" class="form-label">
              <i class="lni lni-text-format"></i> Judul Fase
            </label>
            <input type="text" class="form-control" id="edit_judul_fase" name="judul_fase" 
                   placeholder="Masukkan judul fase" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Judul untuk fase pengembangan ini
            </small>
          </div>
          <div class="mb-3">
            <label for="edit_urutan_periode" class="form-label">
              <i class="lni lni-sort-amount-asc"></i> Urutan Tampil
            </label>
            <input type="number" class="form-control" id="edit_urutan_periode" name="urutan" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Urutan tampil periode ini dalam daftar
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="lni lni-close"></i> Batal
          </button>
          <button type="submit" name="edit_periode" class="btn btn-primary">
            <i class="lni lni-save"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Tambah Tugas -->
<div class="modal fade" id="modalTambahTugas" tabindex="-1" aria-labelledby="modalTambahTugasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahTugasLabel">
            <i class="lni lni-plus"></i> Tambah Tugas Baru
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="deskripsi_tugas_tambah" class="form-label">
              <i class="lni lni-text-format"></i> Deskripsi Tugas
            </label>
            <textarea class="form-control" id="deskripsi_tugas_tambah" name="deskripsi_tugas" rows="4" 
                      placeholder="Masukkan deskripsi tugas yang akan dikerjakan" required></textarea>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Deskripsi lengkap tentang tugas yang akan ditambahkan
            </small>
          </div>
          <div class="mb-3">
            <label for="urutan_tugas_tambah" class="form-label">
              <i class="lni lni-sort-amount-asc"></i> Urutan Tampil Tugas
            </label>
            <?php
            $urutan_tugas_baru = 1;
            if (!empty($tugas_list)) {
                $last_tugas = end($tugas_list);
                $urutan_tugas_baru = $last_tugas['urutan'] + 1;
            }
            ?>
            <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">
            <input type="number" class="form-control" id="urutan_tugas_tambah" name="urutan" value="<?php echo $urutan_tugas_baru; ?>" required>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Urutan tampil tugas ini dalam daftar
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="lni lni-close"></i> Batal
          </button>
          <button type="submit" name="tambah_tugas" class="btn btn-primary">
            <i class="lni lni-save"></i> Simpan Tugas
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Edit Tugas -->
<div class="modal fade" id="modalEditTugas" tabindex="-1" aria-labelledby="modalEditTugasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formEditTugas" action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditTugasLabel">
            <i class="lni lni-pencil"></i> Edit Tugas
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_id_tugas" name="id_tugas" value="">
          <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">

          <div class="mb-3">
            <label for="edit_deskripsi_tugas" class="form-label">
              <i class="lni lni-text-format"></i> Deskripsi Tugas
            </label>
            <textarea class="form-control" id="edit_deskripsi_tugas" name="deskripsi_tugas" rows="4" 
                      placeholder="Masukkan deskripsi tugas" required></textarea>
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Deskripsi lengkap tentang tugas yang akan diubah
            </small>
          </div>
          <div class="mb-3">
            <label for="edit_urutan_tugas" class="form-label">
              <i class="lni lni-sort-amount-asc"></i> Urutan Tampil Tugas
            </label>
            <input type="number" class="form-control" id="edit_urutan_tugas" name="urutan" required value="">
            <small class="text-muted" style="display: block; margin-top: 5px; font-size: 12px;">
              Urutan tampil tugas ini dalam daftar
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="lni lni-close"></i> Batal
          </button>
          <button type="submit" name="edit_tugas" class="btn btn-primary">
            <i class="lni lni-save"></i> Simpan Perubahan
          </button>
        </div>
      </form>
  </div>
</div>

</main>

<!-- SCRIPTS: load bootstrap bundle (sudah include Popper), optional jQuery kept for other legacy code -->
<script src="<?php echo $base_path; ?>assets/js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/bootstrap.5.0.0.alpha-2-min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/classes/AnggotaConfirmModal.js" onload="console.log('AnggotaConfirmModal.js loaded');" onerror="console.error('Failed to load AnggotaConfirmModal.js');"></script>

<script>
// Function to confirm delete tugas
function confirmDeleteTugas(id, deskripsi, idPeriode) {
    console.log('confirmDeleteTugas called:', id, deskripsi, idPeriode);
    console.log('window.anggotaConfirmModal:', window.anggotaConfirmModal);
    
    if (window.anggotaConfirmModal) {
        window.anggotaConfirmModal.confirmDelete(deskripsi, function() {
            // Redirect to delete URL
            window.location.href = '?hapus_tugas=' + id + '&kelola_tugas=' + idPeriode;
        });
    } else {
        console.warn('window.anggotaConfirmModal tidak tersedia, menggunakan fallback');
        // Fallback to default confirm
        if (confirm('Yakin hapus tugas "' + deskripsi + '"?')) {
            window.location.href = '?hapus_tugas=' + id + '&kelola_tugas=' + idPeriode;
        }
    }
}

// Function to confirm delete periode
function confirmDeletePeriode(id, periodeInfo) {
    console.log('confirmDeletePeriode called:', id, periodeInfo);
    console.log('window.anggotaConfirmModal:', window.anggotaConfirmModal);
    
    if (window.anggotaConfirmModal) {
        window.anggotaConfirmModal.show(
            'Hapus Periode',
            'Yakin hapus periode "' + periodeInfo + '" dan semua tugas di dalamnya? Tindakan ini tidak dapat dibatalkan.',
            function() {
                // Redirect to delete URL
                window.location.href = '?hapus_periode=' + id;
            }
        );
    } else {
        console.warn('window.anggotaConfirmModal tidak tersedia, menggunakan fallback');
        // Fallback to default confirm
        if (confirm('Yakin hapus periode "' + periodeInfo + '" dan semua tugas di dalamnya?')) {
            window.location.href = '?hapus_periode=' + id;
        }
    }
}

// Pastikan modal sudah dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking anggotaConfirmModal...');
    if (window.anggotaConfirmModal) {
        console.log('✓ anggotaConfirmModal tersedia');
    } else {
        console.error('✗ anggotaConfirmModal TIDAK tersedia!');
        console.log('Cek apakah script AnggotaConfirmModal.js sudah di-include');
    }
});

// Fungsi untuk membuka modal tambah periode (fallback)
function openModalTambahPeriode(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalTambahPeriode');
    if (!modalEl) {
        console.error('Modal tidak ditemukan!');
        return false;
    }
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdrop');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdrop';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalTambahPeriode();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalTambahPeriode();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal
function closeModalTambahPeriode() {
    var modalEl = document.getElementById('modalTambahPeriode');
    if (modalEl) {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
    
    var backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fungsi untuk membuka modal edit periode
function openModalEditPeriode(e, button) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalEditPeriode');
    if (!modalEl) {
        console.error('Modal edit periode tidak ditemukan!');
        return false;
    }
    
    // Ambil data dari button
    var id = button.getAttribute('data-id') || '';
    var tahun = button.getAttribute('data-tahun') || '';
    var judul = button.getAttribute('data-judul') || '';
    var urutan = button.getAttribute('data-urutan') || '';
    
    // Populate form
    var form = modalEl.querySelector('form');
    if (form) {
        var inputId = form.querySelector('#edit_id_periode');
        var inputTahun = form.querySelector('#edit_tahun_periode');
        var inputJudul = form.querySelector('#edit_judul_fase');
        var inputUrutan = form.querySelector('#edit_urutan_periode');
        
        if (inputId) inputId.value = id;
        if (inputTahun) inputTahun.value = tahun;
        if (inputJudul) inputJudul.value = judul;
        if (inputUrutan) inputUrutan.value = urutan;
    }
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdrop');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdrop';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalEditPeriode();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalEditPeriode();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal edit periode
function closeModalEditPeriode() {
    var modalEl = document.getElementById('modalEditPeriode');
    if (modalEl) {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
    
    var backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fungsi untuk membuka modal edit tugas
function openModalEditTugas(e, button) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalEditTugas');
    if (!modalEl) {
        console.error('Modal edit tugas tidak ditemukan!');
        return false;
    }
    
    // Ambil data dari button
    var id = button.getAttribute('data-id') || '';
    var deskripsi = button.getAttribute('data-deskripsi') || '';
    var urutan = button.getAttribute('data-urutan') || '';
    
    // Populate form
    var form = modalEl.querySelector('#formEditTugas');
    if (form) {
        var inputId = form.querySelector('#edit_id_tugas');
        var inputDesc = form.querySelector('#edit_deskripsi_tugas');
        var inputUrut = form.querySelector('#edit_urutan_tugas');
        
        if (inputId) inputId.value = id;
        if (inputDesc) inputDesc.value = deskripsi;
        if (inputUrut) inputUrut.value = urutan;
    }
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdrop');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdrop';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalEditTugas();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalEditTugas();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal edit tugas
function closeModalEditTugas() {
    var modalEl = document.getElementById('modalEditTugas');
    if (modalEl) {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
    
    var backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fungsi untuk membuka modal tambah tugas
function openModalTambahTugas(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalTambahTugas');
    if (!modalEl) {
        console.error('Modal tambah tugas tidak ditemukan!');
        return false;
    }
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdrop');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdrop';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalTambahTugas();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalTambahTugas();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal tambah tugas
function closeModalTambahTugas() {
    var modalEl = document.getElementById('modalTambahTugas');
    if (modalEl) {
        modalEl.style.display = 'none';
        modalEl.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
    
    var backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.remove();
    }
}


// Pastikan Bootstrap dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Debug: Cek apakah Bootstrap tersedia
    if (typeof bootstrap === 'undefined') {
        console.warn('Bootstrap tidak ter-load, menggunakan fallback modal');
    } else {
        console.log('Bootstrap loaded successfully');
    }
    
    // FIX: Pastikan tombol modal bisa diklik
    function fixModalButtons() {
        // Fix tombol close
        var closeButtons = document.querySelectorAll('.modal .btn-close');
        closeButtons.forEach(function(btn) {
            btn.style.pointerEvents = 'auto';
            btn.style.cursor = 'pointer';
            btn.style.zIndex = '99999';
            btn.style.position = 'relative';
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var modal = btn.closest('.modal');
                if (modal) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                    } else {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                    }
                }
                return false;
            };
        });
        
        // Fix tombol Batal
        var cancelButtons = document.querySelectorAll('.modal-footer .btn-secondary[data-bs-dismiss="modal"]');
        cancelButtons.forEach(function(btn) {
            btn.style.pointerEvents = 'auto';
            btn.style.cursor = 'pointer';
            btn.style.zIndex = '99999';
            btn.style.position = 'relative';
            btn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                var modal = btn.closest('.modal');
                if (modal) {
                    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        var bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            document.body.classList.remove('modal-open');
                        }
                    } else {
                        modal.style.display = 'none';
                        modal.classList.remove('show');
                        document.body.classList.remove('modal-open');
                    }
                }
                return false;
            };
        });
    }
    
    // Jalankan fix saat DOM ready
    fixModalButtons();
    
    // Jalankan fix saat modal dibuka
    document.addEventListener('shown.bs.modal', function() {
        fixModalButtons();
    });
    
    // Jalankan fix setiap kali ada perubahan
    var observer = new MutationObserver(function() {
        fixModalButtons();
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

/* Toggle sidebar (keeps margin for desktop) */
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const content = document.getElementById('adminContentWrapper');

    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');

    if (window.innerWidth > 992) {
        if (sidebar.classList.contains('active')) {
            content.style.marginLeft = '0px';
        } else {
            content.style.marginLeft = '260px';
        }
    }
}

/* ensure initial margin on load */
window.addEventListener('load', function() {
    if (window.innerWidth > 992) {
        document.getElementById('adminContentWrapper').style.marginLeft = '260px';
    }
});

/* ------------------------------
   Modal Edit Periode: native Bootstrap 5 event handling
   ------------------------------ */
(function () {
    var modalEl = document.getElementById('modalEditPeriode');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;

        var id = button.getAttribute('data-id') || '';
        var tahun = button.getAttribute('data-tahun') || '';
        var judul = button.getAttribute('data-judul') || '';
        var urutan = button.getAttribute('data-urutan') || '';

        var form = modalEl.querySelector('form');
        if (!form) return;

        var inputId = form.querySelector('#edit_id_periode');
        var inputTahun = form.querySelector('#edit_tahun_periode');
        var inputJudul = form.querySelector('#edit_judul_fase');
        var inputUrutan = form.querySelector('#edit_urutan_periode');

        if (inputId) inputId.value = id;
        if (inputTahun) inputTahun.value = tahun;
        if (inputJudul) inputJudul.value = judul;
        if (inputUrutan) inputUrutan.value = urutan;
    });
})();

/* ------------------------------
   Modal Edit Tugas: native Bootstrap 5 event handling
   ------------------------------ */
(function () {
    var modalEl = document.getElementById('modalEditTugas');
    if (!modalEl) return;

    // Use Bootstrap 5 event (works with JS API)
    modalEl.addEventListener('show.bs.modal', function (event) {
        // event.relatedTarget is the trigger element (button)
        var button = event.relatedTarget;
        if (!button) return;

        // read data-* attributes (dataset might auto-convert dashes)
        var id = button.getAttribute('data-id') || '';
        var deskripsi = button.getAttribute('data-deskripsi') || '';
        var urutan = button.getAttribute('data-urutan') || '';

        // populate modal inputs
        var form = modalEl.querySelector('#formEditTugas');
        if (!form) return;

        var inputId = form.querySelector('#edit_id_tugas');
        var inputDesc = form.querySelector('#edit_deskripsi_tugas');
        var inputUrut = form.querySelector('#edit_urutan_tugas');

        if (inputId) inputId.value = id;
        if (inputDesc) inputDesc.value = deskripsi;
        if (inputUrut) inputUrut.value = urutan;
    });

    // Optional: focus textarea after shown
    modalEl.addEventListener('shown.bs.modal', function () {
        var textarea = modalEl.querySelector('#edit_deskripsi_tugas');
        if (textarea) textarea.focus();
    });
})();

</script>

<?php 
include __DIR__ . '/../../template/admin_footer.php'; 
?>

