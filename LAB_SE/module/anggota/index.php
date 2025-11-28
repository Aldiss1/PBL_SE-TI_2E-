<?php
$page_title = "Kelola Anggota - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
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

include __DIR__ . '/../../template/admin_header.php';

// Hitung statistik anggota
$total_anggota_query = "SELECT COUNT(*) as total FROM anggota";
$total_anggota_result = pg_query($koneksi, $total_anggota_query);
$total_anggota = 0;
if ($total_anggota_result && pg_num_rows($total_anggota_result) > 0) {
    $total_anggota_row = pg_fetch_assoc($total_anggota_result);
    $total_anggota = (int)$total_anggota_row['total'];
}

// Hitung anggota dengan jabatan
$anggota_berjabatan_query = "SELECT COUNT(*) as total FROM anggota WHERE jabatan IS NOT NULL AND jabatan != ''";
$anggota_berjabatan_result = pg_query($koneksi, $anggota_berjabatan_query);
$anggota_berjabatan = 0;
if ($anggota_berjabatan_result && pg_num_rows($anggota_berjabatan_result) > 0) {
    $anggota_berjabatan_row = pg_fetch_assoc($anggota_berjabatan_result);
    $anggota_berjabatan = (int)$anggota_berjabatan_row['total'];
}

// Hitung anggota tanpa jabatan
$anggota_tanpa_jabatan = $total_anggota - $anggota_berjabatan;
if ($anggota_tanpa_jabatan < 0) $anggota_tanpa_jabatan = 0;

// Pencarian
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// Ambil data anggota
$anggota = [];
$query = "SELECT * FROM anggota";
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $query .= " WHERE nama ILIKE '%$search_escaped%' OR jabatan ILIKE '%$search_escaped%'";
}
$query .= " ORDER BY id DESC";
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
<section class="pt-50 pb-50 admin-section-bg">
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

        <!-- Stat Cards -->
        <div class="row mb-30">
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".2s">
                    <div class="stat-card-icon stat-card-icon-primary">
                        <i class="lni lni-users"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $total_anggota; ?></h3>
                        <p class="stat-card-label">Total Anggota</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".3s">
                    <div class="stat-card-icon stat-card-icon-success">
                        <i class="lni lni-briefcase"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $anggota_berjabatan; ?></h3>
                        <p class="stat-card-label">Anggota Berjabatan</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-20">
                <div class="stat-card wow fadeInUp" data-wow-delay=".4s">
                    <div class="stat-card-icon stat-card-icon-info">
                        <i class="lni lni-user"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number"><?php echo $anggota_tanpa_jabatan; ?></h3>
                        <p class="stat-card-label">Tanpa Jabatan</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="row mb-20">
            <div class="col-lg-12">
                <div class="jabatan-search-box wow fadeInUp" data-wow-delay=".2s">
                    <i class="lni lni-search"></i>
                    <input type="text" id="searchAnggota" class="jabatan-search-input" placeholder="Cari anggota berdasarkan nama atau jabatan..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <?php if (!empty($search_query)): ?>
                        <a href="index.php" class="jabatan-search-clear" title="Hapus pencarian">
                            <i class="lni lni-close"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <a href="tambah.php" class="btn btn-primary btn-lg wow fadeInUp" data-wow-delay=".3s">
                    <i class="lni lni-plus me-2"></i> Tambah Anggota
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <?php if ($total_anggota > 0): ?>
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="info-box wow fadeInUp" data-wow-delay=".3s">
                    <div class="info-box-icon">
                        <i class="lni lni-information"></i>
                    </div>
                    <div class="info-box-content">
                        <h4 class="info-box-title">Tips Mengelola Anggota</h4>
                        <p class="info-box-text">
                            Pastikan setiap anggota memiliki foto dan informasi lengkap. Anggota tanpa jabatan dapat ditambahkan jabatan melalui menu edit.
                            <?php if ($anggota_tanpa_jabatan > 0): ?>
                                <br><strong>Anda memiliki <?php echo $anggota_tanpa_jabatan; ?> anggota yang belum memiliki jabatan.</strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-12">
                <?php if (count($anggota) > 0): ?>
                    <!-- Table View -->
                <div class="table-responsive wow fadeInUp" data-wow-delay=".3s">
                    <table class="table table-striped table-hover admin-table">
                            <thead>
                            <tr>
                                <th>NO</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php $no = 1; foreach ($anggota as $a): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td>
                                        <div class="flex-center">
                                            <?php 
                                            $foto_raw = $a['foto'] ?? '';
                                            $is_abs = preg_match('/^https?:\\/\\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
                                            $foto_src = $is_abs ? $foto_raw : ($foto_raw ? '../../' . $foto_raw : '');
                                            ?>
                                            <?php if (!empty($foto_src)): ?>
                                                <img src="<?php echo htmlspecialchars($foto_src); ?>" alt="<?php echo htmlspecialchars($a['nama']); ?>" class="avatar-circle">
                                            <?php else: ?>
                                                <?php $initial = strtoupper(substr($a['nama'], 0, 1)); ?>
                                                <div class="initial-circle">
                                                    <?php echo $initial; ?>
                                                </div>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($a['nama']); ?></strong>
                                        </div>
                                    </td>
                                    <td>&nbsp;<?php echo htmlspecialchars($a['jabatan'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-info btn-sm" onclick="openPreviewAnggota(<?php echo (int)$a['id']; ?>)">
                                                <i class="lni lni-eye me-1"></i> Preview
                                        </button>
                                            <a href="edit.php?id=<?php echo $a['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="lni lni-pencil me-1"></i> Edit
                                        </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteAnggota(<?php echo (int)$a['id']; ?>, '<?php echo htmlspecialchars(addslashes($a['nama'])); ?>')">
                                                <i class="lni lni-trash me-1"></i> Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                    
                    <!-- Card View -->
                    <div class="anggota-card-view"></div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state wow fadeInUp" data-wow-delay=".3s">
                        <i class="lni lni-users empty-state-icon"></i>
                        <h3 class="empty-state-title">Belum Ada Anggota</h3>
                        <p class="empty-state-message">Mulai dengan menambahkan anggota pertama ke dalam sistem.</p>
                        <a href="tambah.php" class="empty-state-action">
                            <i class="lni lni-plus"></i> Tambah Anggota Pertama
                        </a>
                    </div>
                <?php endif; ?>
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
		'deskripsi' => $row['deskripsi'] ?? '',
		'foto' => $row['foto'] ?? '',
		'foto_resolved' => $foto_resolved,
		'instagram' => $row['instagram'] ?? '',
		'linkedin' => $row['linkedin'] ?? ''
	];
}
?>

<script>
window.anggotaData = <?php echo json_encode($anggota_map); ?>;
</script>

<!-- Load UI/UX Components -->
<link rel="stylesheet" href="../../assets/css/anggota-ui.css">
<script src="../../assets/js/classes/ToastNotification.js"></script>
<script src="../../assets/js/classes/LoadingState.js"></script>
<script src="../../assets/js/classes/AnggotaConfirmModal.js"></script>
<script src="../../assets/js/classes/AnggotaViewManager.js"></script>
<script src="../../assets/js/classes/AnggotaPreviewModal.js"></script>

<script>
// Initialize AnggotaPreviewModal
if (typeof AnggotaPreviewModal !== 'undefined' && window.anggotaData) {
    window.anggotaPreviewModal = new AnggotaPreviewModal(window.anggotaData);
}

// Fallback function if class not loaded
function openPreviewAnggota(id) {
    if (window.anggotaPreviewModal) {
        window.anggotaPreviewModal.openPreview(id);
    } else if (window.openPreviewAnggota) {
        // Already defined
    } else {
        alert('Modal preview belum siap. Silakan refresh halaman.');
    }
}

// Function to confirm delete with modal
function confirmDeleteAnggota(id, nama) {
    if (window.anggotaConfirmModal) {
        window.anggotaConfirmModal.confirmDelete(nama, function() {
            // Show loading
            if (window.loading) {
                window.loading.show('Menghapus anggota...');
            }
            
            // Delete via fetch
            fetch('hapus.php?id=' + id, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (window.loading) {
                    window.loading.hide();
                }
                
                if (data.success) {
                    // Show success toast
                    if (window.toast) {
                        window.toast.success(data.message || 'Anggota berhasil dihapus!', 'Berhasil');
                    }
                    
                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    if (window.toast) {
                        window.toast.error(data.message || 'Gagal menghapus anggota. Silakan coba lagi.', 'Error');
                    }
                }
            })
            .catch(error => {
                if (window.loading) {
                    window.loading.hide();
                }
                if (window.toast) {
                    window.toast.error('Terjadi kesalahan. Silakan coba lagi.', 'Error');
                }
                console.error('Error:', error);
            });
        });
    } else {
        // Fallback to default confirm
        if (confirm('Yakin ingin menghapus anggota "' + nama + '"?')) {
            window.location.href = 'hapus.php?id=' + id;
        }
    }
}

// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchAnggota');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length >= 2 || query.length === 0) {
                searchTimeout = setTimeout(function() {
                    if (query.length > 0) {
                        window.location.href = 'index.php?search=' + encodeURIComponent(query);
                    } else {
                        window.location.href = 'index.php';
                    }
                }, 500);
            }
        });
        
        // Enter key to search immediately
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length > 0) {
                    window.location.href = 'index.php?search=' + encodeURIComponent(query);
                } else {
                    window.location.href = 'index.php';
                }
            }
        });
    }
    
    // Show toast from PHP flash messages
    const alertMessages = document.querySelectorAll('.alert');
    alertMessages.forEach(function(alert) {
        const message = alert.textContent.trim();
        const type = alert.classList.contains('alert-success') ? 'success' :
                     alert.classList.contains('alert-danger') ? 'error' :
                     alert.classList.contains('alert-warning') ? 'warning' : 'info';
        
        if (window.toast && message) {
            window.toast.show(message, type);
        }
        
        // Hide original alert
        setTimeout(() => {
            alert.style.display = 'none';
        }, 100);
    });
});
</script>

<!-- Modal Preview Anggota -->
<div id="anggotaPreviewOverlay" onclick="if(event.target===this){closePreviewAnggota()}">
	<div class="anggota-modal-bootstrap">
		<div class="anggota-modal-header">
			<h5 class="anggota-modal-title">
				<i class="lni lni-user me-2"></i> Preview Anggota
			</h5>
			<button type="button" class="anggota-modal-close" onclick="closePreviewAnggota()" aria-label="Close">
				<i class="lni lni-close"></i>
			</button>
		</div>
		<div class="anggota-modal-body">
			<div class="anggota-preview-content">
				<!-- Foto/Initial Section -->
				<div class="anggota-preview-photo-section">
					<div class="anggota-preview-photo-wrapper">
						<img id="apFoto" class="anggota-preview-photo" src="" alt="Foto">
						<div id="apInitial" class="anggota-preview-initial">?</div>
					</div>
				</div>

				<!-- Info Section -->
				<div class="anggota-preview-info">
					<h3 id="apNama" class="anggota-preview-nama">-</h3>
					
					<div class="anggota-preview-item">
						<div class="anggota-preview-label">
							<i class="lni lni-briefcase"></i> Jabatan
						</div>
						<div id="apJabatan" class="anggota-preview-value">
							<span class="badge bg-secondary">Belum ada jabatan</span>
						</div>
					</div>

					<div class="anggota-preview-item">
						<div class="anggota-preview-label">
							<i class="lni lni-text-format"></i> Deskripsi
						</div>
						<div id="apDeskripsi" class="anggota-preview-value">
							<p class="text-muted mb-0 fst-italic">Tidak ada deskripsi</p>
						</div>
					</div>

					<div class="anggota-preview-item">
						<div class="anggota-preview-label">
							<i class="lni lni-share"></i> Media Sosial
						</div>
						<div id="apSosmed" class="anggota-preview-value">
							<small class="text-muted">Tidak ada media sosial</small>
						</div>
		</div>
				</div>
			</div>
		</div>
		<div class="anggota-modal-footer">
			<button type="button" class="btn btn-secondary" onclick="closePreviewAnggota()">
				<i class="lni lni-close me-1"></i> Tutup
			</button>
		</div>
	</div>
</div>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>


