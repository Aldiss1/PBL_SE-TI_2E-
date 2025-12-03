<?php
// --- 1. SETUP & KONEKSI ---
$page_title = "Kelola Profil Kami - Admin";

require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../fungsi/pesan_kilat.php'; 
require_once __DIR__ . '/../auth/cek_login.php'; 

if (!isset($base_path)) {
    $base_path = '../../';
}

// --- DEFINISI VARIABEL KOSONG ---
$pesan = '';
// --- END DEFINISI ---

// --- 2. KONFIGURASI MENU SIDEBAR ---
// Catatan: Sidebar di View tampaknya ditujukan untuk Mahasiswa.
// Namun, menu items ini adalah untuk navigasi Admin. Kita biarkan saja karena
// logika navigasi utama Anda ada di sini.

$current_page = 'profil'; 

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
        'icon' => 'lni lni-cog', 
        'link' => '../../module/profil/index.php',
        'active' => true
    ],
    'roadmap' => [
        'title' => 'Kelola Roadmap',
        'icon' => 'lni lni-map-marker',
        'link' => '../../module/profil/kelola_roadmap.php',
        'active' => false
    ]
];

// --- 3. LOGIKA CRUD TENTANG KAMI ---
$tentang_kami_data = null;

// Ambil data Tentang Kami
$query_tentang_kami = "SELECT page_key, title, content FROM konten_page WHERE page_key = 'tentang_kami'";
$result_tentang_kami = pg_query($koneksi, $query_tentang_kami);
if ($result_tentang_kami && pg_num_rows($result_tentang_kami) > 0) {
    $tentang_kami_data = pg_fetch_assoc($result_tentang_kami);
}

// Handle: Update Tentang Kami
if (isset($_POST['update_tentang_kami'])) {
    $tentang_kami_title = trim($_POST['tentang_kami_title'] ?? '');
    $tentang_kami_content = trim($_POST['tentang_kami_content'] ?? '');
    if (empty($tentang_kami_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Konten Tentang Kami tidak boleh kosong!</div>";
    } else {
        // Cek apakah sudah ada
        $cek_tentang_kami = "SELECT COUNT(*) FROM konten_page WHERE page_key = 'tentang_kami'";
        $result_cek = pg_query($koneksi, $cek_tentang_kami);
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            // Update
            $update_tentang_kami = "UPDATE konten_page SET title = $1, content = $2, last_updated = NOW() WHERE page_key = 'tentang_kami'";
            $result_update = pg_query_params($koneksi, $update_tentang_kami, array($tentang_kami_title ?: 'Tentang Kami', $tentang_kami_content));
            if ($result_update) {
                $pesan = "<div class='alert alert-success'>✅ Tentang Kami berhasil diperbarui!</div>";
                // Refresh data
                $result_tentang_kami = pg_query($koneksi, $query_tentang_kami);
                if ($result_tentang_kami && pg_num_rows($result_tentang_kami) > 0) {
                    $tentang_kami_data = pg_fetch_assoc($result_tentang_kami);
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update Tentang Kami: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            // Insert
            $insert_tentang_kami = "INSERT INTO konten_page (page_key, title, content) VALUES ('tentang_kami', $1, $2)";
            $result_insert = pg_query_params($koneksi, $insert_tentang_kami, array($tentang_kami_title ?: 'Tentang Kami', $tentang_kami_content));
            if ($result_insert) {
                $pesan = "<div class='alert alert-success'>✅ Tentang Kami berhasil ditambahkan!</div>";
                // Refresh data
                $result_tentang_kami = pg_query($koneksi, $query_tentang_kami);
                if ($result_tentang_kami && pg_num_rows($result_tentang_kami) > 0) {
                    $tentang_kami_data = pg_fetch_assoc($result_tentang_kami);
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert Tentang Kami: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// --- 6. LOGIKA CRUD VISI-MISI ---
$visi_data = null;
$misi_items = [];
$misi_edit = null;

// Ambil data Visi
$query_visi = "SELECT page_key, title, content FROM konten_page WHERE page_key = 'visi_deskripsi'";
$result_visi = pg_query($koneksi, $query_visi);
if ($result_visi && pg_num_rows($result_visi) > 0) {
    $visi_data = pg_fetch_assoc($result_visi);
}

// Ambil semua item Misi
$query_misi = "SELECT page_key, title, content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC";
$result_misi = pg_query($koneksi, $query_misi);
if ($result_misi) {
    while ($row = pg_fetch_assoc($result_misi)) {
        $misi_items[] = $row;
    }
}

// Handle: Update Visi
if (isset($_POST['update_visi'])) {
    $visi_content = trim($_POST['visi_content'] ?? '');
    if (empty($visi_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Konten Visi tidak boleh kosong!</div>";
    } else {
        // Cek apakah sudah ada
        $cek_visi = "SELECT COUNT(*) FROM konten_page WHERE page_key = 'visi_deskripsi'";
        $result_cek = pg_query($koneksi, $cek_visi);
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            // Update
            $update_visi = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = 'visi_deskripsi'";
            $result_update = pg_query_params($koneksi, $update_visi, array($visi_content));
            if ($result_update) {
                $pesan = "<div class='alert alert-success'>✅ Visi berhasil diperbarui!</div>";
                // Refresh data
                $result_visi = pg_query($koneksi, $query_visi);
                if ($result_visi && pg_num_rows($result_visi) > 0) {
                    $visi_data = pg_fetch_assoc($result_visi);
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update Visi: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            // Insert
            $insert_visi = "INSERT INTO konten_page (page_key, title, content) VALUES ('visi_deskripsi', 'Visi Kami', $1)";
            $result_insert = pg_query_params($koneksi, $insert_visi, array($visi_content));
            if ($result_insert) {
                $pesan = "<div class='alert alert-success'>✅ Visi berhasil ditambahkan!</div>";
                // Refresh data
                $result_visi = pg_query($koneksi, $query_visi);
                if ($result_visi && pg_num_rows($result_visi) > 0) {
                    $visi_data = pg_fetch_assoc($result_visi);
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert Visi: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// Handle: Tambah Misi
if (isset($_POST['tambah_misi'])) {
    $misi_content = trim($_POST['misi_content'] ?? '');
    if (empty($misi_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Konten Misi tidak boleh kosong!</div>";
    } else {
        // Cari nomor terakhir
        $max_num = 0;
        foreach ($misi_items as $item) {
            if (preg_match('/misi_item_(\d+)/', $item['page_key'], $matches)) {
                $num = (int)$matches[1];
                if ($num > $max_num) {
                    $max_num = $num;
                }
            }
        }
        $new_key = 'misi_item_' . ($max_num + 1);
        
        $insert_misi = "INSERT INTO konten_page (page_key, title, content) VALUES ($1, 'Misi', $2)";
        $result_insert = pg_query_params($koneksi, $insert_misi, array($new_key, $misi_content));
        if ($result_insert) {
            $pesan = "<div class='alert alert-success'>✅ Misi berhasil ditambahkan!</div>";
            // Refresh data
            $query_misi = "SELECT page_key, title, content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC";
            $result_misi = pg_query($koneksi, $query_misi);
            $misi_items = [];
            if ($result_misi) {
                while ($row = pg_fetch_assoc($result_misi)) {
                    $misi_items[] = $row;
                }
            }
        } else {
            $pesan = "<div class='alert alert-danger'>❌ Gagal tambah Misi: " . pg_last_error($koneksi) . "</div>";
        }
    }
}

// Handle: Edit Misi (GET)
if (isset($_GET['edit_misi'])) {
    $edit_key = $_GET['edit_misi'];
    $query_edit = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_edit = pg_query_params($koneksi, $query_edit, array($edit_key));
    if ($result_edit && pg_num_rows($result_edit) > 0) {
        $misi_edit = pg_fetch_assoc($result_edit);
    }
}

// Handle: Update Misi
if (isset($_POST['update_misi'])) {
    $misi_key = $_POST['misi_key'] ?? '';
    $misi_content = trim($_POST['misi_content'] ?? '');
    if (empty($misi_key) || empty($misi_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Data tidak valid!</div>";
    } else {
        $update_misi = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = $2";
        $result_update = pg_query_params($koneksi, $update_misi, array($misi_content, $misi_key));
        if ($result_update) {
            $pesan = "<div class='alert alert-success'>✅ Misi berhasil diperbarui!</div>";
            // Refresh data
            $query_misi = "SELECT page_key, title, content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC";
            $result_misi = pg_query($koneksi, $query_misi);
            $misi_items = [];
            if ($result_misi) {
                while ($row = pg_fetch_assoc($result_misi)) {
                    $misi_items[] = $row;
                }
            }
            $misi_edit = null; // Reset edit mode
        } else {
            $pesan = "<div class='alert alert-danger'>❌ Gagal update Misi: " . pg_last_error($koneksi) . "</div>";
        }
    }
}

// Handle: Hapus Misi
if (isset($_GET['hapus_misi'])) {
    $hapus_key = $_GET['hapus_misi'];
    $delete_misi = "DELETE FROM konten_page WHERE page_key = $1";
    $result_delete = pg_query_params($koneksi, $delete_misi, array($hapus_key));
    if ($result_delete) {
        $pesan = "<div class='alert alert-success'>✅ Misi berhasil dihapus!</div>";
        // Refresh data
        $query_misi = "SELECT page_key, title, content FROM konten_page WHERE page_key LIKE 'misi_item_%' ORDER BY page_key ASC";
        $result_misi = pg_query($koneksi, $query_misi);
        $misi_items = [];
        if ($result_misi) {
            while ($row = pg_fetch_assoc($result_misi)) {
                $misi_items[] = $row;
            }
        }
    } else {
        $pesan = "<div class='alert alert-danger'>❌ Gagal hapus Misi: " . pg_last_error($koneksi) . "</div>";
    }
}

// --- 6. TAMPILAN (VIEW) ---
include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<style>
/* ------------------------------------------------ */
/* STYLE KONTEN UTAMA (Dipertahankan) */
/* ------------------------------------------------ */
.header-content h1 { font-weight: 800; color: #2c3e50; margin-bottom: 5px; font-size: 32px; }
.header-content p { color: #6c757d; margin-bottom: 20px; font-size: 15px; }
.card-title-profile { font-size: 20px; font-weight: 700; color: #2c3e50; margin-bottom: 10px; }
.card-divider { border: 0; height: 1px; background: #f1f1f1; margin: 15px 0; }
.btn-sm { padding: 5px 10px; font-size: 14px; }
.btn-info { background-color: #2980b9; color: white; text-decoration: none; display: inline-block; }
.btn-danger { background-color: #e74c3c; color: white; text-decoration: none; display: inline-block; }
.profil-container { padding: 20px; padding-right: 40px;}
.card { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
.form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
textarea.form-control { height: 150px; resize: vertical; }
.btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background-color: #007bff; color: white; }
.table-wrapper { overflow-x: auto; }
table.custom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table.custom-table th, table.custom-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
table.custom-table th { background-color: #f8f9fa; font-weight: bold; }
table.custom-table tr:hover { background-color: #f1f1f1; }

/* ------------------------------------------------ */
/* STYLE KONTEN UTAMA (Update Tampilan Baru) */
/* ------------------------------------------------ */
.header-content h1 {
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 32px;
}
.header-content p {
    color: #6c757d;
    margin-bottom: 20px;
    font-size: 15px;
}
.card-title-profile {
    font-size: 20px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}
.card-divider {
    border: 0;
    height: 1px;
    background: #f1f1f1;
    margin: 15px 0;
}
.btn-sm { /* Kelas baru untuk tombol kecil di tabel */
    padding: 5px 10px;
    font-size: 14px;
}
.btn-info { /* Styling untuk tombol Edit */
    background-color: #2980b9; 
    color: white;
    text-decoration: none;
    display: inline-block;
}
.btn-danger { /* Styling untuk tombol Hapus */
    background-color: #e74c3c;
    color: white;
    text-decoration: none;
    display: inline-block;
}

/* Base styles yang sudah ada */
.profil-container { padding: 20px; }
.card { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
.form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
textarea.form-control { height: 150px; resize: vertical; }
.btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
.btn-primary { background-color: #007bff; color: white; }
.table-wrapper { overflow-x: auto; }
table.custom-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
table.custom-table th, table.custom-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
table.custom-table th { background-color: #f8f9fa; font-weight: bold; }
table.custom-table tr:hover { background-color: #f1f1f1; }
</style>

<!-- Main Content -->
<main class="admin-main-content">
<div class="profil-container">
    
    <div class="header-content"> <h1>Kelola Teks Profil & Halaman</h1>
        <p>Kelola konten dinamis seperti Visi, Misi, Sejarah, dan Teks Sambutan</p>
    </div>
    <?php echo $pesan; ?>

    <!-- ========================= MODAL: KELOLA VISI-MISI ========================= -->
    <div class="modal fade" id="modalVisiMisi" tabindex="-1" aria-labelledby="modalVisiMisiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalVisiMisiLabel">
                        <i class="lni lni-bullseye"></i> Kelola Visi & Misi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $pesan; ?>

                    <!-- VISI -->
                    <div class="mb-4 pb-4" style="border-bottom: 1px solid #f1f1f1;">
                        <h5 style="color: #2F80ED; margin-bottom: 15px; font-weight: 600;">
                            <i class="lni lni-bullseye"></i> Visi
                        </h5>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="visi_content">Deskripsi Visi:</label>
                                <textarea name="visi_content" id="visi_content" class="form-control" rows="4" required><?php echo $visi_data ? htmlspecialchars($visi_data['content']) : ''; ?></textarea>
                                <small class="text-muted">Masukkan deskripsi visi laboratorium</small>
                            </div>
                            <button type="submit" name="update_visi" class="btn btn-primary">
                                <i class="lni lni-save"></i> Simpan Visi
                            </button>
                        </form>
                    </div>

                    <!-- MISI -->
                    <div>
                        <h5 style="color: #27AE60; margin-bottom: 15px; font-weight: 600;">
                            <i class="lni lni-target"></i> Misi
                        </h5>
                        
                        <?php if ($misi_edit): ?>
                            <!-- Form Edit Misi -->
                            <div class="alert alert-info mb-3">
                                <strong>Edit Misi:</strong> Mengedit item misi dengan key <code><?php echo htmlspecialchars($misi_edit['page_key']); ?></code>
                            </div>
                            <form method="POST" action="">
                                <input type="hidden" name="misi_key" value="<?php echo htmlspecialchars($misi_edit['page_key']); ?>">
                                <div class="form-group">
                                    <label for="misi_content_edit">Konten Misi:</label>
                                    <textarea name="misi_content" id="misi_content_edit" class="form-control" rows="3" required><?php echo htmlspecialchars($misi_edit['content']); ?></textarea>
                                </div>
                                <button type="submit" name="update_misi" class="btn btn-success">
                                    <i class="lni lni-save"></i> Simpan Perubahan
                                </button>
                                <a href="index.php" class="btn btn-secondary">Batal</a>
                            </form>
                        <?php else: ?>
                            <!-- Form Tambah Misi -->
                            <form method="POST" action="" class="mb-4">
                                <div class="form-group">
                                    <label for="misi_content">Tambah Misi Baru:</label>
                                    <textarea name="misi_content" id="misi_content" class="form-control" rows="3" placeholder="Masukkan deskripsi misi baru..." required></textarea>
                                </div>
                                <button type="submit" name="tambah_misi" class="btn btn-success">
                                    <i class="lni lni-plus"></i> Tambah Misi
                                </button>
                            </form>

                            <!-- Daftar Misi -->
                            <?php if (!empty($misi_items)): ?>
                                <div class="table-wrapper" style="max-height: 400px; overflow-y: auto;">
                                    <table class="custom-table">
                                        <thead>
                                            <tr>
                                                <th width="50">No</th>
                                                <th>Konten Misi</th>
                                                <th width="150">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($misi_items as $index => $item): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($item['content']); ?></td>
                                                <td>
                                                    <a href="index.php?edit_misi=<?php echo urlencode($item['page_key']); ?>" class="btn btn-sm btn-info" style="margin-right: 5px;">
                                                        <i class="lni lni-pencil"></i> Edit
                                                    </a>
                                                    <a href="index.php?hapus_misi=<?php echo urlencode($item['page_key']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus misi ini?');">
                                                        <i class="lni lni-trash"></i> Hapus
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Belum ada misi yang ditambahkan. Silakan tambahkan misi pertama.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: KELOLA VISI-MISI ========================= -->

    <!-- ========================= MODAL: KELOLA TENTANG KAMI ========================= -->
    <div class="modal fade" id="modalTentangKami" tabindex="-1" aria-labelledby="modalTentangKamiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTentangKamiLabel">
                        <i class="lni lni-information"></i> Kelola Tentang Kami
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $pesan; ?>

                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="tentang_kami_title">Judul:</label>
                            <input type="text" name="tentang_kami_title" id="tentang_kami_title" class="form-control" 
                                   value="<?php echo $tentang_kami_data ? htmlspecialchars($tentang_kami_data['title']) : 'Tentang Kami'; ?>" 
                                   placeholder="Tentang Kami">
                            <small class="text-muted">Judul untuk konten tentang kami</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="tentang_kami_content">Konten Tentang Kami:</label>
                            <textarea name="tentang_kami_content" id="tentang_kami_content" class="form-control" rows="10" required><?php echo $tentang_kami_data ? htmlspecialchars($tentang_kami_data['content']) : ''; ?></textarea>
                            <small class="text-muted">Masukkan deskripsi lengkap tentang laboratorium</small>
                        </div>
                        <button type="submit" name="update_tentang_kami" class="btn btn-primary">
                            <i class="lni lni-save"></i> Simpan Tentang Kami
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: KELOLA TENTANG KAMI ========================= -->

    <!-- ========================= SECTION: KELOLA TENTANG KAMI ========================= -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title-profile mb-0">
                <i class="lni lni-information"></i> Kelola Tentang Kami
            </h3>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalTentangKami" onclick="openModalTentangKami(event)">
                <i class="lni lni-information"></i> Kelola Tentang Kami
            </button>
        </div>
        <p class="text-muted">Kelola konten tentang kami laboratorium. Klik tombol di atas untuk membuka halaman kelola tentang kami lengkap.</p>
    </div>
    <!-- ========================= END SECTION: KELOLA TENTANG KAMI ========================= -->

    <!-- ========================= SECTION: KELOLA VISI-MISI ========================= -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title-profile mb-0">
                <i class="lni lni-bullseye"></i> Kelola Visi & Misi
            </h3>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalVisiMisi" onclick="openModalVisiMisi(event)">
                <i class="lni lni-bullseye"></i> Kelola Visi & Misi
            </button>
        </div>
        <p class="text-muted">Kelola visi dan misi laboratorium. Klik tombol di atas untuk membuka halaman kelola visi-misi lengkap.</p>
            </div>
    <!-- ========================= END SECTION: KELOLA VISI-MISI ========================= -->

    <!-- ========================= SECTION: KELOLA ROADMAP ========================= -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title-profile mb-0">
                <i class="lni lni-map-marker"></i> Kelola Roadmap
            </h3>
            <a href="kelola_roadmap.php" class="btn btn-secondary">
                <i class="lni lni-map-marker"></i> Buka Halaman Roadmap
            </a>
        </div>
        <p class="text-muted">Kelola periode dan tugas roadmap laboratorium. Klik tombol di atas untuk membuka halaman kelola roadmap lengkap.</p>
    </div>
    <!-- ========================= END SECTION: KELOLA ROADMAP ========================= -->
    
</div>
</div>
</main>


<script>
// Fungsi untuk membuka modal Visi-Misi
function openModalVisiMisi(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalVisiMisi');
    if (!modalEl) {
        console.error('Modal visi-misi tidak ditemukan!');
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
            closeModalVisiMisi();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalVisiMisi();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal Visi-Misi
function closeModalVisiMisi() {
    var modalEl = document.getElementById('modalVisiMisi');
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

// Fungsi untuk membuka modal Tentang Kami
function openModalTentangKami(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalTentangKami');
    if (!modalEl) {
        console.error('Modal tentang-kami tidak ditemukan!');
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
            closeModalTentangKami();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalTentangKami();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal Tentang Kami
function closeModalTentangKami() {
    var modalEl = document.getElementById('modalTentangKami');
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
</script>

<?php 
// Tambahkan footer di akhir file
include __DIR__ . '/../../template/admin_footer.php'; 
?>