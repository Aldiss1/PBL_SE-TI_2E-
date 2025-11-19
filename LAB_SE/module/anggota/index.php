<?php
$page_title = "Kelola Anggota - Admin";
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

include __DIR__ . '/../../template/admin_header.php';

// Ambil data anggota
$anggota = [];
$query = "SELECT * FROM anggota ORDER BY id DESC";
$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        $anggota[] = $row;
    }
}
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Anggota</h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">Kelola data anggota/personil laboratorium</p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="button button-sm radius-10 wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-plus"></i> Tambah Anggota
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                    <table class="table table-striped table-hover" style="background: white; border-radius: 10px; overflow: hidden;">
                        <thead style="background: #2c3e50; color: white;">
                            <tr>
                                <th style="padding: 15px;">NO</th>
                                <th style="padding: 15px;">Nama</th>
                                <th style="padding: 15px;">Jabatan</th>
                                <th style="padding: 15px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($anggota) > 0): ?>
                                <?php $no = 1; foreach ($anggota as $a): ?>
                                <tr>
                                    <td style="padding: 15px;"><?php echo $no++; ?></td>
                                    <td style="padding: 15px;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php 
                                            $foto_raw = $a['foto'] ?? '';
                                            $is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                                            $foto_src = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
                                            ?>
                                            <?php if (!empty($foto_src)): ?>
                                                <img src="<?php echo htmlspecialchars($foto_src); ?>" alt="<?php echo htmlspecialchars($a['nama']); ?>" style="width:32px; height:32px; border-radius:50%; object-fit:cover; border:1px solid #eee;">
                                            <?php else: ?>
                                                <?php $initial = strtoupper(substr($a['nama'], 0, 1)); ?>
                                                <div style="width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; background: linear-gradient(135deg, #2F80ED 0%, #6C63FF 100%);">
                                                    <?php echo $initial; ?>
                                                </div>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($a['nama']); ?></strong>
                                        </div>
                                    </td>
                                    <td style="padding: 15px;">&nbsp;<?php echo htmlspecialchars($a['jabatan'] ?? '-'); ?></td>
                                    <td style="padding: 15px; text-align: center;">
                                        <button type="button" class="button button-sm radius-10 border-button" style="padding: 5px 15px; margin-right: 5px;" onclick="openPreviewAnggota(<?php echo (int)$a['id']; ?>)">
                                            <i class="lni lni-eye"></i> Preview
                                        </button>
                                        <a href="edit.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10" style="background: #2F80ED; color: white; padding: 5px 15px; margin-right: 5px;">
                                            <i class="lni lni-pencil"></i> Edit
                                        </a>
                                        <a href="hapus.php?id=<?php echo $a['id']; ?>" class="button button-sm radius-10" style="background: #e74c3c; color: white; padding: 5px 15px;" onclick="return confirm('Yakin ingin menghapus anggota ini?')">
                                            <i class="lni lni-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center" style="padding: 40px;">
                                        <i class="lni lni-inbox" style="font-size: 48px; color: #dee2e6;"></i>
                                        <p class="mt-3 text-muted">Belum ada data anggota. <a href="tambah.php">Tambah anggota</a></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

<?php
// Data untuk modal preview
$anggota_map = [];
foreach ($anggota as $row) {
	$foto_raw = $row['foto'] ?? '';
	$is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
	$foto_resolved = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
	$anggota_map[(string)$row['id']] = [
		'id' => (int)$row['id'],
		'nama' => $row['nama'] ?? '',
		'jabatan' => $row['jabatan'] ?? '',
		'foto' => $row['foto'] ?? '',
		'foto_resolved' => $foto_resolved,
		'instagram' => $row['instagram'] ?? '',
		'linkedin' => $row['linkedin'] ?? ''
	];
}
?>

<style>
#anggotaPreviewOverlay{display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1100; align-items:center; justify-content:center; padding:20px;}
.anggota-modal{background:#fff; border-radius:12px; max-width:600px; width:100%; box-shadow:0 10px 30px rgba(0,0,0,.2); overflow:hidden;}
.anggota-header{padding:14px 18px; border-bottom:1px solid #f1f1f1; display:flex; align-items:center; justify-content:space-between;}
.anggota-header h4{margin:0; font-size:16px; color:#2c3e50; font-weight:700;}
.anggota-close{background:transparent; border:none; font-size:20px; color:#6c757d; cursor:pointer;}
.anggota-body{padding:18px;}
.anggota-body .row{display:flex; gap:16px;}
.anggota-photo{width:120px; height:120px; border-radius:10px; object-fit:cover; border:1px solid #eee; background:#fafafa;}
.anggota-name{margin:0 0 6px 0; font-size:18px; font-weight:800; color:#2c3e50;}
.anggota-role{margin:0 0 12px 0; color:#6c757d; font-weight:700;}
.anggota-social a{display:inline-flex; align-items:center; gap:6px; margin-right:10px; color:#2F80ED; font-size:14px; text-decoration:none;}
.anggota-footer{padding:12px 18px; border-top:1px solid #f1f1f1; background:#fafafa; text-align:right;}
</style>

<script>
window.anggotaData = <?php echo json_encode($anggota_map); ?>;
function openPreviewAnggota(id){
	var data = (window.anggotaData && window.anggotaData[id]) ? window.anggotaData[id] : null;
	if(!data){ alert('Data tidak ditemukan'); return; }
	document.getElementById('apNama').textContent = data.nama || '-';
	document.getElementById('apJabatan').textContent = data.jabatan || '-';
	var img = document.getElementById('apFoto');
	img.src = data.foto_resolved && data.foto_resolved.length ? data.foto_resolved : 'https://via.placeholder.com/300';
	img.alt = data.nama || 'Foto';
	var soc = document.getElementById('apSosmed');
	soc.innerHTML = '';
	if (data.instagram) {
		var a = document.createElement('a'); a.href = data.instagram; a.target='_blank'; a.rel='noopener';
		a.innerHTML = '<i class="lni lni-instagram-filled"></i> Instagram';
		soc.appendChild(a);
	}
	if (data.linkedin) {
		var a2 = document.createElement('a'); a2.href = data.linkedin; a2.target='_blank'; a2.rel='noopener';
		a2.innerHTML = '<i class="lni lni-linkedin-original"></i> LinkedIn';
		soc.appendChild(a2);
	}
	document.getElementById('anggotaPreviewOverlay').style.display='flex';
}
function closePreviewAnggota(){ document.getElementById('anggotaPreviewOverlay').style.display='none'; }
</script>

<!-- Modal Preview Anggota -->
<div id="anggotaPreviewOverlay" onclick="if(event.target===this){closePreviewAnggota()}">
	<div class="anggota-modal">
		<div class="anggota-header">
			<h4>Preview Anggota</h4>
			<button class="anggota-close" onclick="closePreviewAnggota()">&times;</button>
		</div>
		<div class="anggota-body">
			<div class="row">
				<img id="apFoto" class="anggota-photo" src="https://via.placeholder.com/300" alt="Foto">
				<div>
					<h5 id="apNama" class="anggota-name">-</h5>
					<div id="apJabatan" class="anggota-role">-</div>
					<div id="apSosmed" class="anggota-social"></div>
				</div>
			</div>
		</div>
		<div class="anggota-footer">
			<button class="button radius-10 border-button" onclick="closePreviewAnggota()">Tutup</button>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


