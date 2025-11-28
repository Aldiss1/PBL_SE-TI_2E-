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
$current_page = 'profil';
$menu_items = [
    'dashboard.php' => ['title'=>'Dashboard','icon'=>'lni lni-home','link'=>'../../module/admin/dashboard.php','active'=>false],
    'anggota' => ['title'=>'Kelola Anggota','icon'=>'lni lni-users','link'=>'../../module/anggota/index.php','active'=>false],
    'jabatan' => ['title'=>'Kelola Jabatan','icon'=>'lni lni-briefcase','link'=>'../../module/jabatan/index.php','active'=>false],
    'pendaftar' => ['title'=>'Kelola Pendaftar','icon'=>'lni lni-list','link'=>'../../module/pendaftar/index.php','active'=>false],
    'blog' => ['title'=>'Kelola Blog','icon'=>'lni lni-text-format','link'=>'../../module/blog/index.php','active'=>false],
    'profil' => ['title'=>'Kelola Profil','icon'=>'lni lni-cog','link'=>'../../module/profil/index.php','active'=>true],
];

// --------------------------------------------------
// 3. LOGIC CRUD ROADMAP PERIODE & ITEM
// --------------------------------------------------

// Ambil list periode
$query_periode = "SELECT id, tahun_periode, judul_fase, urutan FROM roadmap_periode ORDER BY urutan ASC";
$result_periode = pg_query($koneksi, $query_periode);
$periode_list = ($result_periode !== false) ? pg_fetch_all($result_periode) : [];

// --- HANDLING: Tambah Periode ---
if (isset($_POST['tambah_periode'])) {
    $tahun_periode = trim($_POST['tahun_periode'] ?? '');
    $judul_fase = trim($_POST['judul_fase'] ?? '');
    $urutan = (int)($_POST['urutan'] ?? 1);

    if ($tahun_periode === '' || $judul_fase === '') {
        $pesan = "Isi tahun/periode dan judul fase.";
    } else {
        $sql = "INSERT INTO roadmap_periode (tahun_periode, judul_fase, urutan) VALUES ($1, $2, $3)";
        $res = pg_query_params($koneksi, $sql, [$tahun_periode, $judul_fase, $urutan]);
        if ($res) {
            // redirect agar form tidak submit ulang saat reload
            header("Location: kelola_roadmap.php");
            exit;
        } else {
            $pesan = "Gagal menyimpan periode: " . pg_last_error($koneksi);
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
?><!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($page_title); ?></title>

<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap-5.0.0-alpha-2.min.css" />
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/LineIcons.2.0.css"/>
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-layout.css"/>
<link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-dashboard.css"/>
<style>
/* minimal styling untuk demo */
.admin-sidebar { position: fixed; top:0; left:0; height:100vh; width:260px; background:#2c3e50; padding:20px; color:#fff; overflow:auto; z-index:1102;}
.admin-content-wrapper { margin-left:260px; padding:30px; transition:margin-left .25s ease; }
@media (max-width:992px){ .admin-content-wrapper{ margin-left:0; } }
.card { margin-bottom:1rem; }
</style>
</head>
<body>

<button class="sidebar-toggle btn btn-light" id="sidebarToggle" onclick="toggleSidebar()"><i class="lni lni-menu"></i></button>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h4>Admin Panel</h4>
        <p><i class="lni lni-user"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'admin'; ?></p>
    </div>
    <ul class="sidebar-menu list-unstyled">
        <?php foreach ($menu_items as $item): ?>
        <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
            <a href="<?php echo htmlspecialchars($item['link']); ?>" class="text-white">
                <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i> <?php echo htmlspecialchars($item['title']); ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
    <hr style="border-color:rgba(255,255,255,0.06);">
    <ul class="list-unstyled">
        <li><a href="<?php echo $base_path; ?>index.php" target="_blank" class="text-white"><i class="lni lni-website"></i> Lihat Website</a></li>
        <li><a href="<?php echo $base_path; ?>module/auth/logout.php" class="text-danger"><i class="lni lni-exit"></i> Logout</a></li>
    </ul>
</aside>

<div class="admin-content-wrapper" id="adminContentWrapper">
    <div class="container-fluid">
        <?php if ($pesan): ?>
            <div class="alert alert-warning"><?php echo htmlspecialchars($pesan); ?></div>
        <?php endif; ?>

        <h2 class="mb-4">Kelola Roadmap</h2>
        <p>Atur periode (tahun) dan daftar tugas di setiap fase pengembangan laboratorium.</p>

        <div class="row">
            <div class="col-12">
                <?php if ($periode_aktif): ?>
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            Kelola Tugas: <?php echo htmlspecialchars($periode_aktif['tahun_periode']) . ' - ' . htmlspecialchars($periode_aktif['judul_fase']); ?>
                        </div>
                        <div class="card-body">
                            <a href="kelola_roadmap.php" class="btn btn-sm btn-secondary mb-3"><i class="lni lni-arrow-left"></i> Kembali ke Periode</a>

                            <!-- Tambah tugas (modal trigger) -->
                            <button type="button" class="btn btn-sm btn-success mb-3"
                                data-bs-toggle="modal" data-bs-target="#modalTambahTugas">
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
                                                <!-- Edit trigger: data attributes diisi -->
                                                <button type="button" class="btn btn-sm btn-warning"
                                                    data-bs-toggle="modal" data-bs-target="#modalEditTugas"
                                                    data-id="<?php echo $tugas['id']; ?>"
                                                    data-deskripsi="<?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?>"
                                                    data-urutan="<?php echo htmlspecialchars($tugas['urutan']); ?>">
                                                    Edit
                                                </button>

                                                <a href="?hapus_tugas=<?php echo $tugas['id']; ?>&kelola_tugas=<?php echo $id_periode_aktif; ?>"
                                                   class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus tugas ini?')">Hapus</a>
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
                            <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalTambahPeriode">
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
                                                    <a href="?kelola_tugas=<?php echo $periode['id']; ?>" class="btn btn-sm btn-info text-white">Kelola Tugas</a>
                                                    <a href="?edit_periode=<?php echo $periode['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                    <a href="?hapus_periode=<?php echo $periode['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus periode dan semua tugas di dalamnya?')">Hapus</a>
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
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="kelola_roadmap.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahPeriodeLabel">Tambah Periode Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="tahun_periode" class="form-label">Tahun & Periode (Contoh: 2024 Q1 - Q4)</label>
            <input type="text" class="form-control" id="tahun_periode" name="tahun_periode" required>
          </div>
          <div class="mb-3">
            <label for="judul_fase" class="form-label">Judul Fase</label>
            <input type="text" class="form-control" id="judul_fase" name="judul_fase" required>
          </div>
          <div class="mb-3">
            <label for="urutan" class="form-label">Urutan Tampil</label>
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah_periode" class="btn btn-primary">Simpan Periode</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Tambah Tugas -->
<div class="modal fade" id="modalTambahTugas" tabindex="-1" aria-labelledby="modalTambahTugasLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahTugasLabel">Tambah Tugas Baru</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="deskripsi_tugas_tambah" class="form-label">Deskripsi Tugas</label>
            <textarea class="form-control" id="deskripsi_tugas_tambah" name="deskripsi_tugas" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="urutan_tugas_tambah" class="form-label">Urutan Tampil Tugas</label>
            <?php
            $urutan_tugas_baru = 1;
            if (!empty($tugas_list)) {
                $last_tugas = end($tugas_list);
                $urutan_tugas_baru = $last_tugas['urutan'] + 1;
            }
            ?>
            <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">
            <input type="number" class="form-control" id="urutan_tugas_tambah" name="urutan" value="<?php echo $urutan_tugas_baru; ?>" required>
          </div>
        </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="tambah_tugas" class="btn btn-primary">Simpan Tugas</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Edit Tugas -->
<div class="modal fade" id="modalEditTugas" tabindex="-1" aria-labelledby="modalEditTugasLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formEditTugas" action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditTugasLabel">Edit Tugas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_id_tugas" name="id_tugas" value="">
          <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">

          <div class="mb-3">
            <label for="edit_deskripsi_tugas" class="form-label">Deskripsi Tugas</label>
            <textarea class="form-control" id="edit_deskripsi_tugas" name="deskripsi_tugas" rows="3" required></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_urutan_tugas" class="form-label">Urutan Tampil Tugas</label>
            <input type="number" class="form-control" id="edit_urutan_tugas" name="urutan" required value="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" name="edit_tugas" class="btn btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- SCRIPTS: load bootstrap bundle (sudah include Popper), optional jQuery kept for other legacy code -->
<script src="<?php echo $base_path; ?>assets/js/jquery-3.5.1.min.js"></script>
<script src="<?php echo $base_path; ?>assets/js/bootstrap.bundle-5.0.0-alpha-2.min.js"></script>

<script>
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
   Modal Edit: native Bootstrap 5 event handling
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

/* Debug helper: show console log if jQuery missing (not fatal) */
// jQuery check removed - Bootstrap 5 does not require jQuery
</script>

<?php
// Optional: close DB connection (not strictly necessary in PHP but okay)
if (isset($koneksi) && $koneksi) {
    // pg_close($koneksi); // Uncomment only if you want to close explicitly
}
?>
</body>
</html>

