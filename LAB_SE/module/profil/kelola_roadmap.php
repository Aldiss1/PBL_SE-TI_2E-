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

include __DIR__ . '/../../template/admin_header.php';

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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Roadmap</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Atur periode (tahun) dan daftar tugas di setiap fase pengembangan laboratorium.</p>
                </div>
            </div>
        </div>

        <?php if ($pesan): ?>
            <div class="alert alert-warning wow fadeInUp" data-wow-delay=".3s"><?php echo htmlspecialchars($pesan); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-12">
                <?php if ($periode_aktif): ?>
                    <div class="card wow fadeInUp" data-wow-delay=".3s" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none;">
                        <div class="card-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; border-radius: 15px 15px 0 0; padding: 20px 30px;">
                            <h4 class="mb-0" style="color: white; font-weight: 600;">
                                <i class="lni lni-calendar mr-2"></i>
                                Kelola Tugas: <?php echo htmlspecialchars($periode_aktif['tahun_periode']) . ' - ' . htmlspecialchars($periode_aktif['judul_fase']); ?>
                            </h4>
                        </div>
                        <div class="card-body" style="padding: 30px;">
                            <div class="mb-3">
                                <a href="kelola_roadmap.php" class="button button-sm radius-10" style="margin-right: 10px;">
                                    <i class="lni lni-arrow-left"></i> Kembali ke Periode
                                </a>

                                <!-- Tambah tugas (modal trigger) -->
                                <button type="button" class="button button-sm radius-10" style="background: #27AE60; border-color: #27AE60;"
                                    data-bs-toggle="modal" data-bs-target="#modalTambahTugas">
                                    <i class="lni lni-plus"></i> Tambah Tugas
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover admin-table">
                                    <thead>
                                        <tr>
                                            <th style="width:50px">Urutan</th>
                                            <th>Deskripsi Tugas</th>
                                            <th style="width:200px; text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($tugas_list)): foreach ($tugas_list as $tugas): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($tugas['urutan']); ?></td>
                                            <td><?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?></td>
                                            <td style="text-align: center;">
                                                <div class="btn-group" role="group">
                                                    <!-- Edit trigger: data attributes diisi -->
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        data-bs-toggle="modal" data-bs-target="#modalEditTugas"
                                                        data-id="<?php echo $tugas['id']; ?>"
                                                        data-deskripsi="<?php echo htmlspecialchars($tugas['deskripsi_tugas']); ?>"
                                                        data-urutan="<?php echo htmlspecialchars($tugas['urutan']); ?>">
                                                        <i class="lni lni-pencil me-1"></i> Edit
                                                    </button>

                                                    <a href="?hapus_tugas=<?php echo $tugas['id']; ?>&kelola_tugas=<?php echo $id_periode_aktif; ?>"
                                                       class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus tugas ini?')">
                                                        <i class="lni lni-trash me-1"></i> Hapus
                                                    </a>
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
                    <div class="card wow fadeInUp" data-wow-delay=".3s" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.08); border: none;">
                        <div class="card-header" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; border-radius: 15px 15px 0 0; padding: 20px 30px;">
                            <h4 class="mb-0" style="color: white; font-weight: 600;">
                                <i class="lni lni-list mr-2"></i>
                                Daftar Periode Roadmap
                            </h4>
                        </div>
                        <div class="card-body" style="padding: 30px;">
                            <div class="mb-3">
                                <button type="button" class="button button-sm radius-10" data-bs-toggle="modal" data-bs-target="#modalTambahPeriode">
                                    <i class="lni lni-plus"></i> Tambah Periode
                                </button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover admin-table">
                                    <thead>
                                        <tr>
                                            <th style="width:50px">Urutan</th>
                                            <th>Tahun & Periode</th>
                                            <th>Judul Fase</th>
                                            <th style="width:300px; text-align: center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($periode_list)): foreach ($periode_list as $periode): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($periode['urutan']); ?></td>
                                                <td><?php echo htmlspecialchars($periode['tahun_periode']); ?></td>
                                                <td><?php echo htmlspecialchars($periode['judul_fase']); ?></td>
                                                <td style="text-align: center;">
                                                    <div class="btn-group" role="group">
                                                        <a href="?kelola_tugas=<?php echo $periode['id']; ?>" class="btn btn-info btn-sm text-white">
                                                            <i class="lni lni-cog me-1"></i> Kelola Tugas
                                                        </a>
                                                        <a href="?edit_periode=<?php echo $periode['id']; ?>" class="btn btn-warning btn-sm">
                                                            <i class="lni lni-pencil me-1"></i> Edit
                                                        </a>
                                                        <a href="?hapus_periode=<?php echo $periode['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus periode dan semua tugas di dalamnya?')">
                                                            <i class="lni lni-trash me-1"></i> Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    <div class="empty-state" style="padding: 40px 20px;">
                                                        <i class="lni lni-calendar empty-state-icon" style="font-size: 48px; color: #6c757d; margin-bottom: 15px;"></i>
                                                        <h3 class="empty-state-title" style="color: #2c3e50; margin-bottom: 10px;">Roadmap belum memiliki periode</h3>
                                                        <p class="empty-state-message" style="color: #6c757d; margin-bottom: 20px;">Silakan tambah periode baru untuk memulai.</p>
                                                    </div>
                                                </td>
                                            </tr>
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
</section>
</main>

<!-- MODAL: Tambah Periode -->
<div class="modal fade" id="modalTambahPeriode" tabindex="-1" aria-labelledby="modalTambahPeriodeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
      <form action="kelola_roadmap.php" method="POST">
        <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; border-radius: 15px 15px 0 0; border: none;">
          <h5 class="modal-title" id="modalTambahPeriodeLabel" style="color: white; font-weight: 600;">
            <i class="lni lni-plus-circle mr-2"></i>Tambah Periode Baru
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 30px;">
          <div class="mb-3">
            <label for="tahun_periode" class="form-label" style="font-weight: 600; color: #2c3e50;">Tahun & Periode (Contoh: 2024 Q1 - Q4)</label>
            <input type="text" class="form-control" id="tahun_periode" name="tahun_periode" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;">
          </div>
          <div class="mb-3">
            <label for="judul_fase" class="form-label" style="font-weight: 600; color: #2c3e50;">Judul Fase</label>
            <input type="text" class="form-control" id="judul_fase" name="judul_fase" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;">
          </div>
          <div class="mb-3">
            <label for="urutan" class="form-label" style="font-weight: 600; color: #2c3e50;">Urutan Tampil</label>
            <?php
            $urutan_periode_baru = 1;
            if (!empty($periode_list)) {
                $last_periode = end($periode_list);
                $urutan_periode_baru = $last_periode['urutan'] + 1;
            }
            ?>
            <input type="number" class="form-control" id="urutan" name="urutan" value="<?php echo $urutan_periode_baru; ?>" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;">
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f1f1; padding: 20px 30px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Batal</button>
          <button type="submit" name="tambah_periode" class="button button-sm radius-10" style="background: #2F80ED; border-color: #2F80ED;">Simpan Periode</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Tambah Tugas -->
<div class="modal fade" id="modalTambahTugas" tabindex="-1" aria-labelledby="modalTambahTugasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
      <form action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header" style="background: linear-gradient(135deg, #27AE60 0%, #1E8E4F 100%); color: white; border-radius: 15px 15px 0 0; border: none;">
          <h5 class="modal-title" id="modalTambahTugasLabel" style="color: white; font-weight: 600;">
            <i class="lni lni-plus-circle mr-2"></i>Tambah Tugas Baru
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 30px;">
          <div class="mb-3">
            <label for="deskripsi_tugas_tambah" class="form-label" style="font-weight: 600; color: #2c3e50;">Deskripsi Tugas</label>
            <textarea class="form-control" id="deskripsi_tugas_tambah" name="deskripsi_tugas" rows="3" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;"></textarea>
          </div>
          <div class="mb-3">
            <label for="urutan_tugas_tambah" class="form-label" style="font-weight: 600; color: #2c3e50;">Urutan Tampil Tugas</label>
            <?php
            $urutan_tugas_baru = 1;
            if (!empty($tugas_list)) {
                $last_tugas = end($tugas_list);
                $urutan_tugas_baru = $last_tugas['urutan'] + 1;
            }
            ?>
            <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">
            <input type="number" class="form-control" id="urutan_tugas_tambah" name="urutan" value="<?php echo $urutan_tugas_baru; ?>" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;">
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f1f1; padding: 20px 30px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Batal</button>
          <button type="submit" name="tambah_tugas" class="button button-sm radius-10" style="background: #27AE60; border-color: #27AE60;">Simpan Tugas</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Edit Tugas -->
<div class="modal fade" id="modalEditTugas" tabindex="-1" aria-labelledby="modalEditTugasLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
      <form id="formEditTugas" action="kelola_roadmap.php?kelola_tugas=<?php echo $id_periode_aktif; ?>" method="POST">
        <div class="modal-header" style="background: linear-gradient(135deg, #F2994A 0%, #E67E22 100%); color: white; border-radius: 15px 15px 0 0; border: none;">
          <h5 class="modal-title" id="modalEditTugasLabel" style="color: white; font-weight: 600;">
            <i class="lni lni-pencil mr-2"></i>Edit Tugas
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 30px;">
          <input type="hidden" id="edit_id_tugas" name="id_tugas" value="">
          <input type="hidden" name="id_periode" value="<?php echo htmlspecialchars($id_periode_aktif); ?>">

          <div class="mb-3">
            <label for="edit_deskripsi_tugas" class="form-label" style="font-weight: 600; color: #2c3e50;">Deskripsi Tugas</label>
            <textarea class="form-control" id="edit_deskripsi_tugas" name="deskripsi_tugas" rows="3" required style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;"></textarea>
          </div>
          <div class="mb-3">
            <label for="edit_urutan_tugas" class="form-label" style="font-weight: 600; color: #2c3e50;">Urutan Tampil Tugas</label>
            <input type="number" class="form-control" id="edit_urutan_tugas" name="urutan" required value="" style="border-radius: 8px; border: 1px solid #dee2e6; padding: 10px 15px;">
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #f1f1f1; padding: 20px 30px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px;">Batal</button>
          <button type="submit" name="edit_tugas" class="button button-sm radius-10" style="background: #F2994A; border-color: #F2994A;">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
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

// Initialize WOW.js for animations
(function() {
    function initWOW() {
        if (typeof WOW !== 'undefined') {
            // Destroy existing WOW instance if any
            if (window.wowInstance) {
                window.wowInstance = null;
            }
            // Initialize WOW
            window.wowInstance = new WOW({
                boxClass: 'wow',
                animateClass: 'animated',
                offset: 0,
                mobile: true,
                live: true
            });
            window.wowInstance.init();
        } else {
            // Retry if WOW.js not loaded yet
            setTimeout(initWOW, 100);
        }
    }
    
    // Initialize WOW after scripts are loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initWOW, 200);
        });
    } else {
        setTimeout(initWOW, 200);
    }
    
    // Also try on window load as backup
    window.addEventListener('load', function() {
        setTimeout(initWOW, 100);
    });
})();
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

