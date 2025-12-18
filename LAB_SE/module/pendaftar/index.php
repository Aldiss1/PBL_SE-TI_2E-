<?php
$page_title = "Kelola Pendaftar - Admin";
// Pastikan LABSE_BASE_PATH terdefinisi
if (!defined('LABSE_BASE_PATH')) {
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    $appRoot = realpath(__DIR__ . '/../..');
    
    if ($documentRoot) {
        $documentRoot = str_replace('\\', '/', $documentRoot);
    }
    if ($appRoot) {
        $appRoot = str_replace('\\', '/', $appRoot);
    }
    
    $basePath = '';
    if ($documentRoot && $appRoot && strpos($appRoot, $documentRoot) === 0) {
        $basePath = substr($appRoot, strlen($documentRoot));
        $basePath = '/' . trim($basePath, '/');
        if ($basePath === '/') {
            $basePath = '';
        }
    }
    
    define('LABSE_BASE_PATH', $basePath);
}
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';

// Set active menu
$current_page = 'pendaftar';
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
        'active' => true
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
        'active' => false
    ]
];

// Ambil statistik pendaftar dari materialized view (lebih cepat!)
// Fallback ke query biasa jika materialized view belum dibuat
$stats = ['total' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0];

if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
    require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
    $stats_mv = get_pendaftar_per_status();
    if ($stats_mv) {
        $stats = $stats_mv;
        $stats['total'] = $stats['pending'] + $stats['accepted'] + $stats['rejected'];
    }
}

// Fallback: Query langsung jika materialized view tidak tersedia
if ($stats['total'] === 0 && $koneksi) {
    $query_stats = "SELECT status, COUNT(*) as total FROM pendaftaran GROUP BY status";
    $result_stats = pg_query($koneksi, $query_stats);
    if ($result_stats) {
        while ($row = pg_fetch_assoc($result_stats)) {
            $stats[$row['status']] = (int)$row['total'];
            $stats['total'] += (int)$row['total'];
        }
    }
}

// ============================================
// HANDLER: BULK ACTIONS
// ============================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_action') {
	$bulk_action = isset($_POST['bulk_action_type']) ? clean_input($_POST['bulk_action_type']) : '';
	$selected_ids = isset($_POST['selected_ids']) ? $_POST['selected_ids'] : [];
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
	
	if (!empty($selected_ids) && in_array($bulk_action, ['accept', 'reject', 'delete'])) {
		$success_count = 0;
		$error_count = 0;
		$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
		$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';
		
		foreach ($selected_ids as $id) {
			$id = (int)$id;
			if ($id <= 0) continue;
			
			$id_escaped = pg_escape_string($koneksi, $id);
			
			if ($bulk_action === 'accept') {
				// Ambil status lama
				$q_old = pg_query($koneksi, "SELECT status FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
				$old_status = 'pending';
				if ($q_old && pg_num_rows($q_old) > 0) {
					$old_data = pg_fetch_assoc($q_old);
					$old_status = $old_data['status'] ?? 'pending';
				}
				
				$update = pg_query($koneksi, "UPDATE pendaftaran SET status = 'accepted', tanggal_review = NOW(), updated_at = NOW() WHERE id = $id_escaped");
				if ($update) {
					// Simpan riwayat
					pg_query($koneksi, "INSERT INTO riwayat_pendaftaran (pendaftaran_id, status_lama, status_baru, changed_by, catatan) VALUES ($id_escaped, '$old_status', 'accepted', $user_id, 'Bulk action: Terima pendaftar')");
					$success_count++;
				} else {
					$error_count++;
				}
			} elseif ($bulk_action === 'reject') {
				// Ambil status lama
				$q_old = pg_query($koneksi, "SELECT status FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
				$old_status = 'pending';
				if ($q_old && pg_num_rows($q_old) > 0) {
					$old_data = pg_fetch_assoc($q_old);
					$old_status = $old_data['status'] ?? 'pending';
				}
				
				$update = pg_query($koneksi, "UPDATE pendaftaran SET status = 'rejected', tanggal_review = NOW(), updated_at = NOW() WHERE id = $id_escaped");
				if ($update) {
					// Simpan riwayat
					pg_query($koneksi, "INSERT INTO riwayat_pendaftaran (pendaftaran_id, status_lama, status_baru, changed_by, catatan) VALUES ($id_escaped, '$old_status', 'rejected', $user_id, 'Bulk action: Tolak pendaftar')");
					$success_count++;
				} else {
					$error_count++;
				}
			} elseif ($bulk_action === 'delete') {
				$delete = pg_query($koneksi, "DELETE FROM pendaftaran WHERE id = $id_escaped");
				if ($delete) {
					$success_count++;
				} else {
					$error_count++;
				}
			}
		}
		
		$action_text = $bulk_action === 'accept' ? 'diterima' : ($bulk_action === 'reject' ? 'ditolak' : 'dihapus');
		if ($success_count > 0) {
			// Refresh materialized views setelah bulk action
			if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
				require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
				if (function_exists('refresh_views_complete')) {
					@refresh_views_complete();
				}
			}
			pesan_kilat("$success_count pendaftar berhasil $action_text!", 'success');
		}
		if ($error_count > 0) {
			pesan_kilat("$error_count pendaftar gagal diproses.", 'error');
		}
		
		$redirect_url = build_redirect_url($current_filter, $search_query, $filter_tanggal_dari, $filter_tanggal_sampai, $per_page);
		header('Location: ' . $redirect_url);
		exit;
	}
}

// ============================================
// HANDLER: EXPORT DATA
// ============================================
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
	// Export akan dihandle di bagian bawah file
	$export_mode = true;
} else {
	$export_mode = false;
}

// ============================================
// HANDLER: GET RIWAYAT (AJAX)
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'get_riwayat' && isset($_GET['id'])) {
	header('Content-Type: application/json');
	$id = (int)$_GET['id'];
	$id_escaped = pg_escape_string($koneksi, $id);
	
	// Ambil data pendaftar
	$q_pendaftar = pg_query($koneksi, "SELECT * FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
	$pendaftar_data = null;
	if ($q_pendaftar && pg_num_rows($q_pendaftar) > 0) {
		$pendaftar_data = pg_fetch_assoc($q_pendaftar);
	}
	
	// Ambil riwayat dari riwayat_pendaftaran
	$q_riwayat = pg_query($koneksi, "
		SELECT r.*, u.username as changed_by_username
		FROM riwayat_pendaftaran r
		LEFT JOIN users u ON r.changed_by = u.id
		WHERE r.pendaftaran_id = $id_escaped
		ORDER BY r.changed_at DESC
	");
	
	$riwayat = [];
	if ($q_riwayat && pg_num_rows($q_riwayat) > 0) {
		while ($row = pg_fetch_assoc($q_riwayat)) {
			// Format tanggal
			$tanggal_formatted = '-';
			if (!empty($row['changed_at'])) {
				try {
					$date = new DateTime($row['changed_at'], new DateTimeZone('UTC'));
					$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
					$tanggal_formatted = $date->format('d/m/Y H:i') . ' WIB';
				} catch (Exception $e) {
					$tanggal = strtotime($row['changed_at']);
					if ($tanggal !== false) {
						$tanggal_wib = $tanggal + (7 * 3600);
						$tanggal_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
					}
				}
			}
			$row['tanggal'] = $tanggal_formatted;
			$riwayat[] = $row;
		}
	}
	
	// Ambil juga log aktivitas
	$q_logs = pg_query($koneksi, "
		SELECT * FROM log_aktivitas_pendaftar
		WHERE pendaftaran_id = $id_escaped
		ORDER BY created_at DESC
		LIMIT 20
	");
	
	if ($q_logs && pg_num_rows($q_logs) > 0) {
		while ($row = pg_fetch_assoc($q_logs)) {
			// Format tanggal
			$tanggal_formatted = '-';
			if (!empty($row['created_at'])) {
				try {
					$date = new DateTime($row['created_at'], new DateTimeZone('UTC'));
					$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
					$tanggal_formatted = $date->format('d/m/Y H:i') . ' WIB';
				} catch (Exception $e) {
					$tanggal = strtotime($row['created_at']);
					if ($tanggal !== false) {
						$tanggal_wib = $tanggal + (7 * 3600);
						$tanggal_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
					}
				}
			}
			$row['tanggal'] = $tanggal_formatted;
			$row['status_baru'] = $row['action_type'];
			$row['status_lama'] = '';
			$riwayat[] = $row;
		}
	}
	
	// Sort by date descending
	usort($riwayat, function($a, $b) {
		$dateA = strtotime($a['changed_at'] ?? $a['created_at'] ?? '');
		$dateB = strtotime($b['changed_at'] ?? $b['created_at'] ?? '');
		return $dateB - $dateA;
	});
	
	echo json_encode([
		'success' => true,
		'pendaftar' => $pendaftar_data ?: ['nama' => '-', 'nim' => '-'],
		'riwayat' => $riwayat
	]);
	exit;
}

// Helper function untuk build redirect URL dengan semua filter
function build_redirect_url($status_filter, $search_query = '', $filter_tanggal_dari = '', $filter_tanggal_sampai = '', $per_page = 25) {
	$params = [];
	if ($status_filter !== 'all') $params[] = 'status=' . urlencode($status_filter);
	if (!empty($search_query)) $params[] = 'search=' . urlencode($search_query);
	if (!empty($filter_tanggal_dari)) $params[] = 'tanggal_dari=' . urlencode($filter_tanggal_dari);
	if (!empty($filter_tanggal_sampai)) $params[] = 'tanggal_sampai=' . urlencode($filter_tanggal_sampai);
	if ($per_page != 25) $params[] = 'per_page=' . $per_page;
	
	return 'index.php' . (!empty($params) ? '?' . implode('&', $params) : '');
}

// Filter berdasarkan status (ambil dulu sebelum proses update)
$status_filter = isset($_GET['status']) ? clean_input($_GET['status']) : 'all';

// ============================================
// PENCARIAN DAN FILTER
// ============================================
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? clean_input($_GET['tanggal_dari']) : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? clean_input($_GET['tanggal_sampai']) : '';

// ============================================
// PAGINATION
// ============================================
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 25;
if (!in_array($per_page, [10, 25, 50, 100])) {
	$per_page = 25;
}
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// Proses update status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$new_status = isset($_POST['status']) ? clean_input($_POST['status']) : '';
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
	
	if ($id > 0 && in_array($new_status, ['accepted', 'rejected', 'pending'])) {
		$id_escaped = pg_escape_string($koneksi, $id);
		$status_escaped = pg_escape_string($koneksi, $new_status);
		
		// Ambil status lama untuk riwayat
		$q_old = pg_query($koneksi, "SELECT status FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
		$old_status = 'pending';
		if ($q_old && pg_num_rows($q_old) > 0) {
			$old_data = pg_fetch_assoc($q_old);
			$old_status = $old_data['status'] ?? 'pending';
		}
		
		// Ambil user yang melakukan perubahan
		$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
		$catatan = isset($_POST['catatan']) ? clean_input($_POST['catatan']) : '';
		$catatan_escaped = pg_escape_string($koneksi, $catatan);
		
		// Hanya update status, TIDAK membuat akun user
		$update_query = "UPDATE pendaftaran SET status = '$status_escaped', updated_at = NOW()";
		if ($new_status === 'accepted' || $new_status === 'rejected') {
			$update_query .= ", tanggal_review = NOW()";
		}
		$update_query .= " WHERE id = $id_escaped";
		
		$update_result = pg_query($koneksi, $update_query);
		if ($update_result) {
			// Simpan riwayat perubahan status
			if ($old_status !== $new_status) {
				pg_query($koneksi, "INSERT INTO riwayat_pendaftaran (pendaftaran_id, status_lama, status_baru, changed_by, catatan) VALUES ($id_escaped, '$old_status', '$status_escaped', $user_id, '$catatan_escaped')");
			}
			
			$status_text = $new_status === 'accepted' ? 'diterima' : ($new_status === 'rejected' ? 'ditolak' : 'diubah');
			pesan_kilat('Status pendaftar berhasil ' . $status_text . '!', 'success');
		} else {
			pesan_kilat('Gagal mengupdate status. Error: ' . pg_last_error($koneksi), 'error');
		}
		
		$redirect_url = build_redirect_url($current_filter, $search_query ?? '', $filter_tanggal_dari ?? '', $filter_tanggal_sampai ?? '', $per_page ?? 25);
		header('Location: ' . $redirect_url);
		exit;
	}
}


// Proses update data pendaftar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_pendaftar') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
	
	if ($id > 0) {
		$id_escaped = pg_escape_string($koneksi, $id);
		$nama = isset($_POST['nama']) ? clean_input($_POST['nama']) : '';
		$nim = isset($_POST['nim']) ? clean_input($_POST['nim']) : '';
		$email = isset($_POST['email']) ? clean_input($_POST['email']) : '';
		$status = isset($_POST['status']) ? clean_input($_POST['status']) : 'pending';
		$catatan_admin = isset($_POST['catatan_admin']) ? clean_input($_POST['catatan_admin']) : '';
		
		// Handle upload foto (opsional - replace jika ada)
		$foto_path = '';
		if (isset($_FILES['foto']) && is_uploaded_file($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
			$allowed_ext = ['jpg','jpeg','png','webp'];
			$max_size = 2 * 1024 * 1024; // 2MB
			$original_name = $_FILES['foto']['name'];
			$tmp_path = $_FILES['foto']['tmp_name'];
			$size = (int)$_FILES['foto']['size'];
			$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
			
			if (in_array($ext, $allowed_ext) && $size <= $max_size) {
				$uploadDir = __DIR__ . '/../../uploads/pendaftar/';
				if (!is_dir($uploadDir)) {
					@mkdir($uploadDir, 0775, true);
				}
				$safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(pathinfo($original_name, PATHINFO_FILENAME)));
				$newName = $safeBase . '_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
				$destPath = $uploadDir . $newName;
				if (@move_uploaded_file($tmp_path, $destPath)) {
					$foto_path = 'uploads/pendaftar/' . $newName;
				}
			} else {
				pesan_kilat('Foto tidak valid (format harus JPG/PNG/WEBP, maks 2MB).', 'error');
			}
		} elseif (isset($_POST['foto_old']) && !empty($_POST['foto_old'])) {
			// Jika tidak ada file baru, gunakan foto lama
			$foto_path = clean_input($_POST['foto_old']);
		}
		
		if ($nama === '' || $nim === '') {
			pesan_kilat('Nama dan NIM wajib diisi.', 'error');
		} else {
			$nama_escaped = pg_escape_string($koneksi, $nama);
			$nim_escaped = pg_escape_string($koneksi, $nim);
			$email_escaped = pg_escape_string($koneksi, $email);
			$foto_escaped = pg_escape_string($koneksi, $foto_path);
			$status_escaped = pg_escape_string($koneksi, $status);
			$catatan_admin_escaped = pg_escape_string($koneksi, $catatan_admin);
			
			$update_query = "UPDATE pendaftaran SET 
				nama = '$nama_escaped',
				nim = '$nim_escaped',
				email = '$email_escaped',
				foto = '$foto_escaped',
				status = '$status_escaped',
				catatan_admin = '$catatan_admin_escaped',
				updated_at = NOW()";
			
			if ($status === 'accepted' || $status === 'rejected') {
				$update_query .= ", tanggal_review = NOW()";
			}
			
			$update_query .= " WHERE id = $id_escaped";
			
			// Ambil data lama untuk log
			$q_old = pg_query($koneksi, "SELECT * FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
			$old_data = null;
			if ($q_old && pg_num_rows($q_old) > 0) {
				$old_data = pg_fetch_assoc($q_old);
			}
			
			$update_result = pg_query($koneksi, $update_query);
			if ($update_result) {
				// Ambil data baru
				$q_new = pg_query($koneksi, "SELECT * FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
				$new_data = null;
				if ($q_new && pg_num_rows($q_new) > 0) {
					$new_data = pg_fetch_assoc($q_new);
				}
				
				// Simpan riwayat jika status berubah
				if ($old_data && $old_data['status'] !== $status) {
					$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
					pg_query($koneksi, "INSERT INTO riwayat_pendaftaran (pendaftaran_id, status_lama, status_baru, changed_by, catatan) VALUES ($id_escaped, '{$old_data['status']}', '$status_escaped', $user_id, 'Update data: Status diubah')");
				}
				
				// Refresh materialized views setelah update pendaftaran
				if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
					require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
					if (function_exists('refresh_views_complete')) {
						@refresh_views_complete();
					}
				}
				
				pesan_kilat('Data pendaftar berhasil diupdate!', 'success');
			} else {
				pesan_kilat('Gagal mengupdate data. Error: ' . pg_last_error($koneksi), 'error');
			}
		}
		
		$redirect_url = build_redirect_url($current_filter, $search_query ?? '', $filter_tanggal_dari ?? '', $filter_tanggal_sampai ?? '', $per_page ?? 25);
		header('Location: ' . $redirect_url);
		exit;
	}
}



// Proses hapus pendaftar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_pendaftar') {
	$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
	$current_filter = isset($_POST['current_filter']) ? clean_input($_POST['current_filter']) : 'all';
	
	if ($id > 0) {
		$id_escaped = pg_escape_string($koneksi, $id);
		// Ambil data sebelum hapus untuk log
		$q_before = pg_query($koneksi, "SELECT * FROM pendaftaran WHERE id = $id_escaped LIMIT 1");
		$before_data = null;
		if ($q_before && pg_num_rows($q_before) > 0) {
			$before_data = pg_fetch_assoc($q_before);
		}
		
		$delete = pg_query($koneksi, "DELETE FROM pendaftaran WHERE id = $id_escaped");
		if ($delete) {
			// Refresh materialized views setelah delete pendaftaran
			if (file_exists(__DIR__ . '/../../fungsi/materialized_views_complete.php')) {
				require_once __DIR__ . '/../../fungsi/materialized_views_complete.php';
				if (function_exists('refresh_views_complete')) {
					@refresh_views_complete();
				}
			}
			pesan_kilat('Pendaftar berhasil dihapus.', 'success');
		} else {
			pesan_kilat('Gagal menghapus pendaftar: ' . pg_last_error($koneksi), 'error');
		}
		$redirect_url = build_redirect_url($current_filter, $search_query ?? '', $filter_tanggal_dari ?? '', $filter_tanggal_sampai ?? '', $per_page ?? 25);
		header('Location: ' . $redirect_url);
		exit;
	}
}

// ============================================
// QUERY PENDAFTAR DENGAN PENCARIAN, FILTER, DAN PAGINATION
// ============================================
$pendaftar = [];
$total_records = 0;

// Build WHERE conditions
$where_conditions = [];

// Filter status
if ($status_filter !== 'all') {
	$status_filter_escaped = pg_escape_string($koneksi, $status_filter);
	$where_conditions[] = "p.status = '$status_filter_escaped'";
}

// Pencarian (nama, NIM, email)
if (!empty($search_query)) {
	$search_escaped = pg_escape_string($koneksi, $search_query);
	$where_conditions[] = "(p.nama ILIKE '%$search_escaped%' OR p.nim ILIKE '%$search_escaped%' OR p.email ILIKE '%$search_escaped%')";
}

// Filter tanggal
if (!empty($filter_tanggal_dari)) {
	$tanggal_dari_escaped = pg_escape_string($koneksi, $filter_tanggal_dari);
	$where_conditions[] = "DATE(p.tanggal_daftar) >= '$tanggal_dari_escaped'";
}
if (!empty($filter_tanggal_sampai)) {
	$tanggal_sampai_escaped = pg_escape_string($koneksi, $filter_tanggal_sampai);
	$where_conditions[] = "DATE(p.tanggal_daftar) <= '$tanggal_sampai_escaped'";
}


// Build query
$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query untuk total records (untuk pagination)
$count_query = "SELECT COUNT(*) as total FROM pendaftaran p $where_clause";

$count_result = pg_query($koneksi, $count_query);
if ($count_result) {
	$count_row = pg_fetch_assoc($count_result);
	$total_records = (int)$count_row['total'];
}

// Query untuk data dengan pagination
$query = "SELECT p.* FROM pendaftaran p $where_clause ORDER BY p.tanggal_daftar DESC LIMIT $per_page OFFSET $offset";

$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
	while ($row = pg_fetch_assoc($result)) {
		$pendaftar[] = $row;
	}
}

// Hitung total pages untuk pagination
$total_pages = $total_records > 0 ? ceil($total_records / $per_page) : 1;

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
					<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
						<div>
							<h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">Kelola Pendaftar</h2>
							<p class="wow fadeInUp" data-wow-delay=".4s">Daftar mahasiswa yang telah mendaftar ke Laboratorium Software Engineering</p>
						</div>
						<div class="wow fadeInUp" data-wow-delay=".3s">
							<a href="history_log.php" class="button radius-10" style="background: #6c757d; color: white; border: none; padding: 10px 20px;">
								<i class="lni lni-history"></i> History Log
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php tampilkan_pesan(); ?>

		<!-- Statistics Cards -->
		<div class="row mb-50">
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card blue wow fadeInUp" data-wow-delay=".2s">
					<div class="stat-icon">
						<i class="lni lni-graduation"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['total']); ?></div>
					<div class="stat-label">Total Pendaftar</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card orange wow fadeInUp" data-wow-delay=".3s">
					<div class="stat-icon">
						<i class="lni lni-hourglass"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['pending']); ?></div>
					<div class="stat-label">Pending</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card green wow fadeInUp" data-wow-delay=".4s">
					<div class="stat-icon">
						<i class="lni lni-checkmark-circle"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['accepted']); ?></div>
					<div class="stat-label">Diterima</div>
				</div>
			</div>
			<div class="col-xl-3 col-lg-6 col-md-6 mb-30">
				<div class="stat-card purple wow fadeInUp" data-wow-delay=".5s">
					<div class="stat-icon">
						<i class="lni lni-close"></i>
					</div>
					<div class="stat-number"><?php echo number_format($stats['rejected']); ?></div>
					<div class="stat-label">Ditolak</div>
				</div>
			</div>
		</div>

		<!-- Filter Status -->
		<div class="row mb-30">
			<div class="col-lg-12">
				<div class="wow fadeInUp filter-card-wrapper" data-wow-delay=".2s">
					<div class="filter-buttons">
						<span class="filter-label">
							<i class="lni lni-filter"></i>Filter Status:
						</span>
						<a href="?status=all<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_tanggal_dari) ? '&tanggal_dari=' . urlencode($filter_tanggal_dari) : ''; ?><?php echo !empty($filter_tanggal_sampai) ? '&tanggal_sampai=' . urlencode($filter_tanggal_sampai) : ''; ?>" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'all' ? 'filter-button-active' : 'border-button'; ?>">
							<i class="lni lni-list"></i> Semua
						</a>
						<a href="?status=pending<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_tanggal_dari) ? '&tanggal_dari=' . urlencode($filter_tanggal_dari) : ''; ?><?php echo !empty($filter_tanggal_sampai) ? '&tanggal_sampai=' . urlencode($filter_tanggal_sampai) : ''; ?>" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'pending' ? 'filter-button-pending' : 'border-button'; ?>">
							<i class="lni lni-hourglass"></i> Pending
						</a>
						<a href="?status=accepted<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_tanggal_dari) ? '&tanggal_dari=' . urlencode($filter_tanggal_dari) : ''; ?><?php echo !empty($filter_tanggal_sampai) ? '&tanggal_sampai=' . urlencode($filter_tanggal_sampai) : ''; ?>" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'accepted' ? 'filter-button-accepted' : 'border-button'; ?>">
							<i class="lni lni-checkmark-circle"></i> Diterima
						</a>
						<a href="?status=rejected<?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?><?php echo !empty($filter_tanggal_dari) ? '&tanggal_dari=' . urlencode($filter_tanggal_dari) : ''; ?><?php echo !empty($filter_tanggal_sampai) ? '&tanggal_sampai=' . urlencode($filter_tanggal_sampai) : ''; ?>" class="button button-sm radius-10 filter-button <?php echo $status_filter === 'rejected' ? 'filter-button-rejected' : 'border-button'; ?>">
							<i class="lni lni-close"></i> Ditolak
						</a>
					</div>
				</div>
			</div>
		</div>

		<!-- Pencarian dan Filter Lanjutan -->
		<div class="row mb-30">
			<div class="col-lg-12">
				<div class="wow fadeInUp filter-advanced-card" data-wow-delay=".3s" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
					<form method="GET" action="index.php" id="filterForm">
						<input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
						
						<div class="row">
							<!-- Pencarian -->
							<div class="col-md-3 mb-3">
								<label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
									<i class="lni lni-search"></i> Pencarian
								</label>
								<input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Nama, NIM, atau Email..." style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
							</div>
							
							<!-- Filter Tanggal Dari -->
							<div class="col-md-2 mb-3">
								<label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
									<i class="lni lni-calendar"></i> Tanggal Dari
								</label>
								<input type="date" name="tanggal_dari" value="<?php echo htmlspecialchars($filter_tanggal_dari); ?>" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
							</div>
							
							<!-- Filter Tanggal Sampai -->
							<div class="col-md-2 mb-3">
								<label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
									<i class="lni lni-calendar"></i> Tanggal Sampai
								</label>
								<input type="date" name="tanggal_sampai" value="<?php echo htmlspecialchars($filter_tanggal_sampai); ?>" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
							</div>
							
							<!-- Tombol -->
							<div class="col-md-1 mb-3" style="display: flex; align-items: flex-end;">
								<button type="submit" class="button radius-10 btn-edit" style="width: 100%; padding: 10px;">
									<i class="lni lni-search"></i> Cari
								</button>
							</div>
						</div>
						
						<?php if (!empty($search_query) || !empty($filter_tanggal_dari) || !empty($filter_tanggal_sampai)): ?>
						<div class="row mt-2">
							<div class="col-md-12">
								<a href="?status=<?php echo htmlspecialchars($status_filter); ?>" class="button button-sm radius-10 border-button">
									<i class="lni lni-close"></i> Reset Filter
								</a>
							</div>
						</div>
						<?php endif; ?>
					</form>
				</div>
			</div>
		</div>

		<!-- Bulk Actions Bar -->
		<?php if (count($pendaftar) > 0): ?>
		<div class="row mb-20" id="bulkActionsBar" style="display: none;">
			<div class="col-lg-12">
				<div class="wow fadeInUp bulk-actions-bar" data-wow-delay=".3s" style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
					<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
						<span style="font-weight: 600; color: #2c3e50;">
							<i class="lni lni-checkmark-circle"></i> <span id="selectedCount">0</span> dipilih
						</span>
						<button type="button" onclick="bulkAction('accept')" class="button button-sm radius-10" style="background: #28a745; color: white; border: none;">
							<i class="lni lni-checkmark-circle"></i> Terima
						</button>
						<button type="button" onclick="bulkAction('reject')" class="button button-sm radius-10" style="background: #dc3545; color: white; border: none;">
							<i class="lni lni-close"></i> Tolak
						</button>
						<button type="button" onclick="bulkAction('delete')" class="button button-sm radius-10 border-button" style="color: #dc3545;">
							<i class="lni lni-trash"></i> Hapus
						</button>
						<button type="button" onclick="exportSelected()" class="button button-sm radius-10 border-button">
							<i class="lni lni-download"></i> Export
						</button>
						<button type="button" onclick="clearSelection()" class="button button-sm radius-10 border-button">
							<i class="lni lni-close"></i> Batal
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Table Pendaftar -->
		<div class="row">
			<div class="col-lg-12">
				<div class="table-responsive wow fadeInUp" data-wow-delay=".3s" style="overflow-x: auto; overflow-y: visible;">
					<table class="table table-striped table-hover admin-table" style="position: relative;">
						<thead>
							<tr>
								<th style="width: 40px;">
									<input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" title="Pilih Semua">
								</th>
								<th>NO</th>
								<th>Nama Lengkap</th>
								<th>Email</th>
								<th>NIM</th>
								<th>Status</th>
								<th>Tanggal Daftar</th>
								<th class="text-center">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php if (count($pendaftar) > 0): ?>
								<?php 
								$no = 1;
								foreach ($pendaftar as $p): 
								?>
								<tr>
									<td>
										<input type="checkbox" class="row-checkbox" value="<?php echo $p['id']; ?>" onchange="updateBulkActionsBar()">
									</td>
									<td><?php echo $no++; ?></td>
									<td>
										<div class="flex-center">
											<?php 
											$foto_raw = $p['foto'] ?? '';
											$foto_src = '';
											if (!empty($foto_raw)) {
												$is_abs = preg_match('/^https?:\/\//', $foto_raw) || (strlen($foto_raw) > 0 && $foto_raw[0] === '/');
												if ($is_abs) {
													$foto_src = $foto_raw;
												} else {
													// Path relatif dari module/pendaftar/ ke root
													$base_path = defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : '';
													if (!empty($base_path)) {
														$foto_src = rtrim($base_path, '/') . '/' . ltrim($foto_raw, '/');
													} else {
														// Fallback: path relatif dari module/pendaftar/
														$foto_src = '../../' . ltrim($foto_raw, '/');
													}
												}
											}
											?>
											<?php if (!empty($foto_src)): ?>
												<img src="<?php echo htmlspecialchars($foto_src); ?>" 
												     alt="<?php echo htmlspecialchars($p['nama']); ?>" 
												     class="avatar-circle"
												     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
												<?php $initial = strtoupper(substr($p['nama'], 0, 1)); ?>
												<div class="initial-circle" style="display: none;">
													<?php echo $initial; ?>
												</div>
											<?php else: ?>
												<?php $initial = strtoupper(substr($p['nama'], 0, 1)); ?>
												<div class="initial-circle">
													<?php echo $initial; ?>
												</div>
											<?php endif; ?>
											<strong><?php echo htmlspecialchars($p['nama']); ?></strong>
										</div>
									</td>
									<td>
										<?php if (isset($p['email']) && !empty($p['email'])): ?>
											<a href="mailto:<?php echo htmlspecialchars($p['email']); ?>" class="email-link">
												<i class="lni lni-envelope"></i><?php echo htmlspecialchars($p['email']); ?>
											</a>
										<?php else: ?>
											<span class="text-muted-small">-</span>
										<?php endif; ?>
									</td>
									<td>
										<span class="nim-badge">
											<?php echo htmlspecialchars($p['nim']); ?>
										</span>
									</td>
									<td>
										<?php
										$status = $p['status'] ?? 'pending';
										$status_badge = '';
										switch ($status) {
											case 'accepted':
												$status_badge = '<span class="status-badge status-badge-accepted">Diterima</span>';
												break;
											case 'rejected':
												$status_badge = '<span class="status-badge status-badge-rejected">Ditolak</span>';
												break;
											case 'pending':
											default:
												$status_badge = '<span class="status-badge status-badge-pending">Pending</span>';
												break;
										}
										echo $status_badge;
										?>
									</td>
									<td>
										<?php 
										if (!empty($p['tanggal_daftar'])) {
											try {
												// Konversi ke timezone WIB (Asia/Jakarta)
												$date = new DateTime($p['tanggal_daftar'], new DateTimeZone('UTC'));
												$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
												echo $date->format('d/m/Y H:i') . ' WIB';
											} catch (Exception $e) {
												// Fallback jika DateTime gagal
												$tanggal = strtotime($p['tanggal_daftar']);
												if ($tanggal !== false) {
													// Tambah 7 jam untuk konversi UTC ke WIB
													$tanggal_wib = $tanggal + (7 * 3600);
													echo date('d/m/Y H:i', $tanggal_wib) . ' WIB';
												} else {
													echo htmlspecialchars($p['tanggal_daftar']);
												}
											}
										} else {
											echo '-';
										}
										?>
									</td>
									<td class="text-center">
										<div class="dropdown-actions">
											<button type="button" class="button button-sm radius-10 border-button dropdown-toggle" onclick="toggleDropdown(<?php echo $p['id']; ?>)" title="Menu Aksi">
												<i class="lni lni-menu"></i> Aksi
											</button>
											<div class="dropdown-menu" id="dropdown-<?php echo $p['id']; ?>">
												<a href="javascript:void(0)" onclick="viewDetail(<?php echo $p['id']; ?>)" class="dropdown-item">
													<i class="lni lni-eye"></i> Lihat Detail
												</a>
												<a href="javascript:void(0)" onclick="viewRiwayat(<?php echo $p['id']; ?>)" class="dropdown-item">
													<i class="lni lni-history"></i> Riwayat
												</a>
												<?php if ($status === 'pending'): ?>
													<a href="javascript:void(0)" onclick="updateStatus(<?php echo $p['id']; ?>, 'accepted')" class="dropdown-item text-success">
														<i class="lni lni-checkmark-circle"></i> Terima Pendaftar
													</a>
													<a href="javascript:void(0)" onclick="updateStatus(<?php echo $p['id']; ?>, 'rejected')" class="dropdown-item text-danger">
														<i class="lni lni-close"></i> Tolak Pendaftar
													</a>
												<?php else: ?>
													<a href="javascript:void(0)" onclick="showEditModal(<?php echo $p['id']; ?>)" class="dropdown-item">
														<i class="lni lni-pencil"></i> Update Data
													</a>
												<?php endif; ?>
												<div class="dropdown-divider"></div>
												<a href="javascript:void(0)" onclick="deletePendaftar(<?php echo $p['id']; ?>)" class="dropdown-item text-danger">
													<i class="lni lni-trash"></i> Hapus Pendaftar
												</a>
											</div>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="9" class="text-center admin-table-empty">
										<i class="lni lni-inbox admin-table-empty-icon"></i>
										<p class="mt-3 text-muted">
											<?php if ($status_filter !== 'all'): ?>
												Tidak ada pendaftar dengan status "<?php echo htmlspecialchars(ucfirst($status_filter)); ?>".
											<?php else: ?>
												Belum ada data pendaftar. Mahasiswa yang mendaftar akan muncul di sini.
											<?php endif; ?>
										</p>
									</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- Pagination dan Info -->
		<?php if ($total_records > 0): ?>
		<div class="row mt-30">
			<div class="col-lg-6">
				<div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
					<span style="color: #666; font-size: 14px;">
						Menampilkan <strong><?php echo $offset + 1; ?></strong> - <strong><?php echo min($offset + $per_page, $total_records); ?></strong> dari <strong><?php echo number_format($total_records); ?></strong> pendaftar
					</span>
					<select name="per_page" onchange="changePerPage(this.value)" style="padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
						<option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10 per halaman</option>
						<option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25 per halaman</option>
						<option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50 per halaman</option>
						<option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100 per halaman</option>
					</select>
				</div>
			</div>
			<div class="col-lg-6">
				<div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
					<?php
					// Build query string untuk pagination
					$query_params = [];
					if ($status_filter !== 'all') $query_params[] = 'status=' . urlencode($status_filter);
					if (!empty($search_query)) $query_params[] = 'search=' . urlencode($search_query);
					if (!empty($filter_tanggal_dari)) $query_params[] = 'tanggal_dari=' . urlencode($filter_tanggal_dari);
					if (!empty($filter_tanggal_sampai)) $query_params[] = 'tanggal_sampai=' . urlencode($filter_tanggal_sampai);
					if ($per_page != 25) $query_params[] = 'per_page=' . $per_page;
					$query_string = !empty($query_params) ? '?' . implode('&', $query_params) : '';
					$page_separator = !empty($query_string) ? '&' : '?';
					?>
					
					<!-- Previous Button -->
					<?php if ($current_page > 1): ?>
						<a href="<?php echo $query_string . $page_separator; ?>page=<?php echo ($current_page - 1); ?>" class="button button-sm radius-10 border-button">
							<i class="lni lni-chevron-left"></i> Sebelumnya
						</a>
					<?php else: ?>
						<button type="button" class="button button-sm radius-10 border-button" disabled>
							<i class="lni lni-chevron-left"></i> Sebelumnya
						</button>
					<?php endif; ?>
					
					<!-- Page Numbers -->
					<?php
					$start_page = max(1, $current_page - 2);
					$end_page = min($total_pages, $current_page + 2);
					
					if ($start_page > 1): ?>
						<a href="<?php echo $query_string . $page_separator; ?>page=1" class="button button-sm radius-10 border-button">1</a>
						<?php if ($start_page > 2): ?>
							<span style="padding: 8px 12px;">...</span>
						<?php endif; ?>
					<?php endif; ?>
					
					<?php for ($i = $start_page; $i <= $end_page; $i++): ?>
						<?php if ($i == $current_page): ?>
							<button type="button" class="button button-sm radius-10 btn-edit" style="min-width: 40px;">
								<?php echo $i; ?>
							</button>
						<?php else: ?>
							<a href="<?php echo $query_string . $page_separator; ?>page=<?php echo $i; ?>" class="button button-sm radius-10 border-button" style="min-width: 40px;">
								<?php echo $i; ?>
							</a>
						<?php endif; ?>
					<?php endfor; ?>
					
					<?php if ($end_page < $total_pages): ?>
						<?php if ($end_page < $total_pages - 1): ?>
							<span style="padding: 8px 12px;">...</span>
						<?php endif; ?>
						<a href="<?php echo $query_string . $page_separator; ?>page=<?php echo $total_pages; ?>" class="button button-sm radius-10 border-button">
							<?php echo $total_pages; ?>
						</a>
					<?php endif; ?>
					
					<!-- Next Button -->
					<?php if ($current_page < $total_pages): ?>
						<a href="<?php echo $query_string . $page_separator; ?>page=<?php echo ($current_page + 1); ?>" class="button button-sm radius-10 border-button">
							Selanjutnya <i class="lni lni-chevron-right"></i>
						</a>
					<?php else: ?>
						<button type="button" class="button button-sm radius-10 border-button" disabled>
							Selanjutnya <i class="lni lni-chevron-right"></i>
						</button>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
</main>

<!-- Confirm Modal for Status Update -->
<div id="confirmStatusOverlay">
	<div class="confirm-status-modal">
		<div class="confirm-status-header">
			<i id="confirmStatusIcon" class="lni lni-hourglass"></i>
			<h4 id="confirmStatusTitle">Konfirmasi</h4>
		</div>
		<div id="confirmStatusText" class="confirm-status-body">
			Apakah Anda yakin?
		</div>
		<div class="confirm-status-footer">
			<button id="btnCancelStatus" class="button radius-10 border-button">Batal</button>
			<button id="btnConfirmStatus" class="button radius-10 btn-edit">Konfirmasi</button>
		</div>
	</div>
</div>


<!-- Update Modal for Pendaftar -->
<div id="updatePendaftarOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;" onclick="if(event.target===this){ this.style.display='none'; }">
	<div class="detail-modal" style="max-width: 600px;">
		<div class="detail-header">
			<h4>
				<i class="lni lni-pencil icon-blue"></i>
				Update Data Pendaftar
			</h4>
			<button class="detail-close" title="Tutup" onclick="closeUpdatePendaftarModal();">&times;</button>
		</div>
		<div class="detail-body">
			<form id="formUpdatePendaftar" method="POST" enctype="multipart/form-data" onsubmit="return submitUpdatePendaftar(event);">
				<input type="hidden" name="action" value="update_pendaftar">
				<input type="hidden" name="id" id="updatePendaftarId">
				<input type="hidden" name="current_filter" value="<?php echo htmlspecialchars($status_filter); ?>">
				<input type="hidden" name="foto_old" id="updatePendaftarFotoOld">
				
				<div style="display: grid; gap: 16px;">
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">Nama Lengkap <span style="color: #dc3545;">*</span></label>
						<input type="text" name="nama" id="updatePendaftarNama" required style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
					</div>
					
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">NIM <span style="color: #dc3545;">*</span></label>
						<input type="text" name="nim" id="updatePendaftarNim" required style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
					</div>
					
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">Email</label>
						<input type="email" name="email" id="updatePendaftarEmail" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
					</div>
					
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">Foto</label>
						<div id="fotoPreviewContainer" style="margin-bottom: 10px; display: none;">
							<img id="fotoPreview" src="" alt="Preview" style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #e0e0e0; object-fit: cover;">
							<small style="display: block; margin-top: 5px; color: #6c757d;">Gambar saat ini</small>
						</div>
						<input type="file" name="foto" id="updatePendaftarFoto" accept="image/jpeg,image/jpg,image/png,image/webp" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box;" onchange="previewFotoUpdate(this);">
						<small style="display: block; margin-top: 5px; color: #6c757d;">Format: JPG, PNG, WEBP (Maksimal 2MB). Kosongkan jika tidak ingin mengganti.</small>
					</div>
					
					<div>
					</div>
					
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">Status</label>
						<select name="status" id="updatePendaftarStatus" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
							<option value="pending">Pending</option>
							<option value="accepted">Accepted</option>
							<option value="rejected">Rejected</option>
						</select>
					</div>
					
					<div>
						<label style="display: block; margin-bottom: 6px; color: #2c3e50; font-size: 13px; font-weight: 600;">Catatan Admin</label>
						<textarea name="catatan_admin" id="updatePendaftarCatatan" rows="3" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"></textarea>
					</div>
				</div>
			</form>
		</div>
		<div class="detail-footer">
			<button type="button" class="button radius-10 border-button" onclick="closeUpdatePendaftarModal();">Tutup</button>
			<button type="button" class="button radius-10 btn-edit" onclick="document.getElementById('formUpdatePendaftar').submit();">Simpan</button>
		</div>
	</div>
</div>

<!-- Confirm Modal for Delete Pendaftar -->
<div id="confirmDeletePendaftarOverlay" class="confirm-modal-overlay" onclick="if(event.target===this) closeConfirmDeletePendaftar();">
	<div class="confirm-modal-dark">
		<div class="confirm-modal-header-dark">
			<h4>localhost says</h4>
		</div>
		<div class="confirm-modal-body-dark">
			<p id="confirmDeletePendaftarText">Yakin ingin menghapus pendaftar ini? Data tidak bisa dikembalikan.</p>
		</div>
		<div class="confirm-modal-footer-dark">
			<button type="button" id="btnCancelDeletePendaftar" class="confirm-btn-cancel-dark" onclick="closeConfirmDeletePendaftar();">Cancel</button>
			<button type="button" id="btnConfirmDeletePendaftar" class="confirm-btn-ok-dark" onclick="executeDeletePendaftar();">OK</button>
		</div>
	</div>
</div>

<!-- Modal Riwayat Pendaftar -->
<div id="riwayatOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;" onclick="if(event.target===this){ this.style.display='none'; }">
	<div class="detail-modal" style="max-width: 700px; max-height: 90vh;">
		<div class="detail-header">
			<h4>
				<i class="lni lni-history icon-blue"></i>
				Riwayat Pendaftar
			</h4>
			<button class="detail-close" title="Tutup" onclick="closeRiwayatModal();">&times;</button>
		</div>
		<div class="detail-body">
			<div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">
				<div style="display: grid; grid-template-columns: auto 1fr; gap: 10px; align-items: center;">
					<strong style="color: #2c3e50;">Nama:</strong>
					<span id="riwayatNama">-</span>
					<strong style="color: #2c3e50;">NIM:</strong>
					<span id="riwayatNim">-</span>
				</div>
			</div>
			<div id="riwayatBody" style="max-height: 400px; overflow-y: auto;">
				<!-- Timeline akan diisi oleh JavaScript -->
			</div>
		</div>
		<div class="detail-footer">
			<button type="button" class="button radius-10 border-button" onclick="closeRiwayatModal();">Tutup</button>
		</div>
	</div>
</div>

<?php
$pendaftar_map = [];
$pendaftar_map_full = [];
foreach ($pendaftar as $pp) {
	// Format tanggal_daftar ke WIB
	$tanggal_daftar_formatted = '-';
	if (!empty($pp['tanggal_daftar'])) {
		try {
			$date = new DateTime($pp['tanggal_daftar'], new DateTimeZone('UTC'));
			$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
			$tanggal_daftar_formatted = $date->format('d/m/Y H:i') . ' WIB';
		} catch (Exception $e) {
			$tanggal = strtotime($pp['tanggal_daftar']);
			if ($tanggal !== false) {
				$tanggal_wib = $tanggal + (7 * 3600);
				$tanggal_daftar_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
			} else {
				$tanggal_daftar_formatted = $pp['tanggal_daftar'];
			}
		}
	}
	
	// Format tanggal_review ke WIB
	$tanggal_review_formatted = '-';
	if (!empty($pp['tanggal_review'])) {
		try {
			$date = new DateTime($pp['tanggal_review'], new DateTimeZone('UTC'));
			$date->setTimezone(new DateTimeZone('Asia/Jakarta'));
			$tanggal_review_formatted = $date->format('d/m/Y H:i') . ' WIB';
		} catch (Exception $e) {
			$tanggal = strtotime($pp['tanggal_review']);
			if ($tanggal !== false) {
				$tanggal_wib = $tanggal + (7 * 3600);
				$tanggal_review_formatted = date('d/m/Y H:i', $tanggal_wib) . ' WIB';
			} else {
				$tanggal_review_formatted = $pp['tanggal_review'];
			}
		}
	}
	
	// Data untuk display (dengan format tanggal)
	$pendaftar_map[(string)$pp['id']] = [
		'id' => (int)$pp['id'],
		'nama' => $pp['nama'] ?? '',
		'email' => $pp['email'] ?? '',
		'nim' => $pp['nim'] ?? '',
		'foto' => $pp['foto'] ?? '',
		'status' => $pp['status'] ?? 'pending',
		'tanggal_daftar' => $tanggal_daftar_formatted,
		'tanggal_review' => $tanggal_review_formatted,
		'catatan_admin' => $pp['catatan_admin'] ?? ''
	];
	
	// Data lengkap untuk form edit (tanpa format tanggal)
	$pendaftar_map_full[(string)$pp['id']] = [
		'id' => (int)$pp['id'],
		'nama' => $pp['nama'] ?? '',
		'email' => $pp['email'] ?? '',
		'nim' => $pp['nim'] ?? '',
		'foto' => $pp['foto'] ?? '',
		'status' => $pp['status'] ?? 'pending',
		'catatan_admin' => $pp['catatan_admin'] ?? ''
	];
}
?>
<script>
window.pendaftarData = <?php echo json_encode($pendaftar_map); ?>;
window.pendaftarDataFull = <?php echo json_encode($pendaftar_map_full); ?>;
window.LABSE_BASE_PATH = <?php echo json_encode(defined('LABSE_BASE_PATH') ? LABSE_BASE_PATH : ''); ?>;
</script>
<script src="../../assets/js/classes/PendaftarModals.js"></script>
<script>
// Initialize PendaftarModals
if (typeof PendaftarModals !== 'undefined' && window.pendaftarData) {
    window.pendaftarModals = new PendaftarModals(window.pendaftarData);
}

</script>

<!-- Detail Modal -->
<div id="detailOverlay" onclick="if(event.target===this){ this.style.display='none'; }">
	<div class="detail-modal">
		<div class="detail-header">
			<h4>
				<i class="lni lni-eye icon-blue"></i>
				Detail Pendaftar
			</h4>
			<button class="detail-close" title="Tutup" onclick="document.getElementById('detailOverlay').style.display='none'">&times;</button>
		</div>
		<div class="detail-body">
			<div class="detail-grid">
				<div class="detail-label">Nama Lengkap</div>
				<div class="detail-value" id="detailNama">-</div>

				<div class="detail-label">NIM</div>
				<div class="detail-value" id="detailNim">-</div>


				<div class="detail-label">Email</div>
				<div class="detail-value"><a id="detailEmail" href="javascript:void(0)">-</a></div>

				<div class="detail-label">Foto</div>
				<div class="detail-value" id="detailFoto">-</div>

				<div class="detail-label">Status</div>
				<div class="detail-value"><span id="detailStatus" class="detail-status-badge status-pending"><i class="lni lni-hourglass"></i> Pending</span></div>

				<div class="detail-label">Tanggal Daftar</div>
				<div class="detail-value" id="detailTanggalDaftar">-</div>

				<div class="detail-label">Tanggal Review</div>
				<div class="detail-value" id="detailTanggalReview">-</div>

				<div class="detail-label">Catatan Admin</div>
				<div class="detail-value" id="detailCatatan">-</div>
			</div>
		</div>
		<div class="detail-footer">
			<button class="button radius-10 border-button" onclick="document.getElementById('detailOverlay').style.display='none'">Tutup</button>
		</div>
	</div>
</div>

<style>
/* Dropdown Actions */
.dropdown-actions {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    cursor: pointer;
}

.dropdown-menu {
    display: none;
    position: fixed;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 200px;
    z-index: 9999;
    margin-top: 5px;
    padding: 5px 0;
}

/* Pastikan table cell tidak memotong dropdown */
.admin-table td {
    position: relative;
    overflow: visible !important;
}

/* Pastikan table responsive tidak memotong dropdown */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: visible !important;
}

/* Pastikan admin-table tidak memotong dropdown */
.admin-table {
    overflow: visible !important;
}

/* Pastikan container table tidak memotong */
.admin-table tbody tr {
    position: relative;
    overflow: visible;
}

.dropdown-menu.show {
    display: block;
}

.dropdown-item {
    display: block;
    padding: 10px 20px;
    color: #333;
    text-decoration: none;
    transition: background 0.2s;
    font-size: 14px;
}

.dropdown-item:hover {
    background: #f5f5f5;
}

.dropdown-item i {
    margin-right: 8px;
    width: 18px;
}

.dropdown-item.text-success {
    color: #28a745;
}

.dropdown-item.text-danger {
    color: #dc3545;
}

.dropdown-item.text-warning {
    color: #ffc107;
}

.dropdown-divider {
    height: 1px;
    background: #e0e0e0;
    margin: 5px 0;
}

/* Dark Theme Confirm Modal */
.confirm-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}

.confirm-modal-dark {
    background: #2c3e50;
    border-radius: 8px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    overflow: hidden;
    position: relative;
}

.confirm-modal-header-dark {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.confirm-modal-header-dark h4 {
    margin: 0;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
}

.confirm-modal-body-dark {
    padding: 20px;
}

.confirm-modal-body-dark p {
    margin: 0;
    color: #ecf0f1;
    font-size: 14px;
    line-height: 1.5;
}

.confirm-modal-footer-dark {
    padding: 12px 20px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.confirm-btn-ok-dark {
    background: #9b59b6;
    color: #fff;
    border: 1px solid #8e44ad;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    pointer-events: auto !important;
}

.confirm-btn-ok-dark:hover {
    background: #8e44ad;
    border-color: #7d3c98;
}

.confirm-btn-ok-dark:active {
    background: #7d3c98;
}

.confirm-btn-cancel-dark {
    background: #34495e;
    color: #fff;
    border: 1px solid #2c3e50;
    padding: 8px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    pointer-events: auto !important;
}

.confirm-btn-cancel-dark:hover {
    background: #2c3e50;
    border-color: #1a252f;
}

.confirm-btn-cancel-dark:active {
    background: #1a252f;
}

/* Riwayat Timeline Styles */
.riwayat-item {
    display: flex;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.riwayat-item:last-child {
    border-bottom: none;
}

.riwayat-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
    color: white;
}

.riwayat-icon.status-accepted {
    background: #28a745;
}

.riwayat-icon.status-rejected {
    background: #dc3545;
}

.riwayat-icon.status-pending {
    background: #ffc107;
}

.riwayat-content {
    flex: 1;
}

.riwayat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.riwayat-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.riwayat-status.status-accepted {
    background: #d4edda;
    color: #155724;
}

.riwayat-status.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.riwayat-status.status-pending {
    background: #fff3cd;
    color: #856404;
}

.riwayat-date {
    font-size: 12px;
    color: #999;
}

.riwayat-from {
    font-size: 13px;
    color: #666;
    margin-bottom: 5px;
}

.riwayat-catatan {
    font-size: 13px;
    color: #2c3e50;
    margin-top: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007bff;
}

.riwayat-admin {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
</style>

<script>
// Toggle Dropdown Menu
function toggleDropdown(id) {
    // Tutup semua dropdown lain
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.id !== 'dropdown-' + id) {
            menu.classList.remove('show');
        }
    });
    
    // Toggle dropdown yang diklik
    const dropdown = document.getElementById('dropdown-' + id);
    const button = event.target.closest('.dropdown-toggle');
    
    if (dropdown && button) {
        const isOpen = dropdown.classList.contains('show');
        
        if (!isOpen) {
            // Hitung posisi button relatif terhadap viewport
            const rect = button.getBoundingClientRect();
            
            // Gunakan fixed positioning untuk menghindari potong
            dropdown.style.position = 'fixed';
            dropdown.style.top = (rect.bottom + 5) + 'px';
            dropdown.style.left = (rect.right - 200) + 'px'; // 200px = min-width dropdown
            dropdown.style.right = 'auto';
            dropdown.style.zIndex = '9999';
            dropdown.classList.add('show');
            
            // Cek jika dropdown keluar dari viewport, sesuaikan posisi
            setTimeout(() => {
                const dropdownRect = dropdown.getBoundingClientRect();
                if (dropdownRect.right > window.innerWidth) {
                    dropdown.style.left = (rect.left) + 'px';
                }
                if (dropdownRect.bottom > window.innerHeight) {
                    dropdown.style.top = (rect.top - dropdownRect.height - 5) + 'px';
                }
            }, 0);
        } else {
            dropdown.classList.remove('show');
            dropdown.style.position = '';
            dropdown.style.top = '';
            dropdown.style.right = '';
            dropdown.style.left = '';
        }
    }
}

// Tutup dropdown saat klik di luar
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown-actions')) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
            menu.style.position = '';
            menu.style.top = '';
            menu.style.right = '';
            menu.style.left = '';
        });
    }
});

// Variable global untuk menyimpan ID yang akan dioperasikan
window.pendingDeletePendaftarId = null;

// Delete Pendaftar - Tampilkan modal
function deletePendaftar(id) {
    window.pendingDeletePendaftarId = id;
    const overlay = document.getElementById('confirmDeletePendaftarOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

// Execute Delete Pendaftar
function executeDeletePendaftar() {
    const id = window.pendingDeletePendaftarId;
    if (!id) return;
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="delete_pendaftar">
        <input type="hidden" name="id" value="${id}">
        <input type="hidden" name="current_filter" value="<?php echo htmlspecialchars($status_filter); ?>">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Close Delete Pendaftar Modal
function closeConfirmDeletePendaftar() {
    window.pendingDeletePendaftarId = null;
    const overlay = document.getElementById('confirmDeletePendaftarOverlay');
    if (overlay) overlay.style.display = 'none';
}

// Show Edit Modal
function showEditModal(id) {
    const data = window.pendaftarData && window.pendaftarData[id] ? window.pendaftarData[id] : null;
    if (!data) {
        alert('Data pendaftar tidak ditemukan.');
        return;
    }
    
    // Set data ke form
    document.getElementById('updatePendaftarId').value = data.id || id;
    document.getElementById('updatePendaftarNama').value = data.nama || '';
    document.getElementById('updatePendaftarNim').value = data.nim || '';
    document.getElementById('updatePendaftarEmail').value = data.email || '';
    
    // Ambil data lengkap dari pendaftar_map
    const pendaftarFull = window.pendaftarDataFull && window.pendaftarDataFull[id] ? window.pendaftarDataFull[id] : null;
    if (pendaftarFull) {
        // Handle foto preview
        const fotoInput = document.getElementById('updatePendaftarFoto');
        const fotoOldInput = document.getElementById('updatePendaftarFotoOld');
        const fotoPreviewContainer = document.getElementById('fotoPreviewContainer');
        const fotoPreview = document.getElementById('fotoPreview');
        
        if (pendaftarFull.foto) {
            fotoOldInput.value = pendaftarFull.foto;
            // Tampilkan preview foto lama
            let fotoSrc = pendaftarFull.foto;
            if (!fotoSrc.startsWith('http') && !fotoSrc.startsWith('/')) {
                fotoSrc = '../../' + fotoSrc;
            }
            fotoPreview.src = fotoSrc;
            fotoPreviewContainer.style.display = 'block';
        } else {
            fotoOldInput.value = '';
            fotoPreviewContainer.style.display = 'none';
        }
        fotoInput.value = ''; // Reset file input
        document.getElementById('updatePendaftarStatus').value = pendaftarFull.status || 'pending';
        document.getElementById('updatePendaftarCatatan').value = pendaftarFull.catatan_admin || '';
    } else {
        // Fallback: ambil dari data yang ada
        // Reset foto input
        document.getElementById('updatePendaftarFoto').value = '';
        document.getElementById('updatePendaftarFotoOld').value = '';
        document.getElementById('fotoPreviewContainer').style.display = 'none';
        document.getElementById('updatePendaftarStatus').value = data.status || 'pending';
        document.getElementById('updatePendaftarCatatan').value = '';
    }
    
    // Tampilkan modal
    const overlay = document.getElementById('updatePendaftarOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

// Close Update Modal
function closeUpdatePendaftarModal() {
    const overlay = document.getElementById('updatePendaftarOverlay');
    if (overlay) overlay.style.display = 'none';
}

// Preview foto saat memilih file baru
function previewFotoUpdate(input) {
    const fotoPreviewContainer = document.getElementById('fotoPreviewContainer');
    const fotoPreview = document.getElementById('fotoPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            fotoPreview.src = e.target.result;
            fotoPreviewContainer.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Submit Update Form
function submitUpdatePendaftar(event) {
    event.preventDefault();
    const form = event.target;
    form.submit();
    return false;
}

// ============================================
// BULK ACTIONS
// ============================================
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const count = checkboxes.length;
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (bulkBar && selectedCount) {
        if (count > 0) {
            bulkBar.style.display = 'block';
            selectedCount.textContent = count;
        } else {
            bulkBar.style.display = 'none';
        }
    }
    
    // Update select all checkbox
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        const allCheckboxes = document.querySelectorAll('.row-checkbox');
        selectAll.checked = allCheckboxes.length > 0 && checkboxes.length === allCheckboxes.length;
        selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = false;
    });
    const selectAll = document.getElementById('selectAll');
    if (selectAll) selectAll.checked = false;
    updateBulkActionsBar();
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu pendaftar!');
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    const actionText = action === 'accept' ? 'terima' : (action === 'reject' ? 'tolak' : 'hapus');
    const confirmText = action === 'delete' 
        ? `Yakin ingin menghapus ${selectedIds.length} pendaftar? Data tidak bisa dikembalikan.`
        : `Yakin ingin ${actionText} ${selectedIds.length} pendaftar?`;
    
    if (!confirm(confirmText)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="bulk_action">
        <input type="hidden" name="bulk_action_type" value="${action}">
        <input type="hidden" name="current_filter" value="<?php echo htmlspecialchars($status_filter); ?>">
        ${selectedIds.map(id => `<input type="hidden" name="selected_ids[]" value="${id}">`).join('')}
    `;
    document.body.appendChild(form);
    form.submit();
}

function exportSelected() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Pilih minimal satu pendaftar untuk di-export!');
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    const idsParam = selectedIds.join(',');
    window.location.href = `?export=excel&ids=${idsParam}`;
}

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset ke halaman 1
    window.location.href = url.toString();
}

// ============================================
// RIWAYAT
// ============================================
function viewRiwayat(id) {
    // Ambil riwayat dari server
    fetch(`?action=get_riwayat&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showRiwayatModal(data.riwayat, data.pendaftar);
            } else {
                alert('Gagal mengambil riwayat: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal mengambil riwayat. Silakan coba lagi.');
        });
}

function showRiwayatModal(riwayat, pendaftar) {
    const overlay = document.getElementById('riwayatOverlay');
    const body = document.getElementById('riwayatBody');
    
    if (!overlay || !body) return;
    
    // Set data pendaftar
    document.getElementById('riwayatNama').textContent = pendaftar.nama || '-';
    document.getElementById('riwayatNim').textContent = pendaftar.nim || '-';
    
    // Build timeline
    let timelineHTML = '';
    if (riwayat.length === 0) {
        timelineHTML = '<p style="text-align: center; color: #999; padding: 20px;">Belum ada riwayat perubahan</p>';
    } else {
        riwayat.forEach((r, index) => {
            const statusClass = r.status_baru === 'accepted' ? 'status-accepted' : (r.status_baru === 'rejected' ? 'status-rejected' : 'status-pending');
            const statusText = r.status_baru === 'accepted' ? 'Diterima' : (r.status_baru === 'rejected' ? 'Ditolak' : 'Pending');
            const statusIcon = r.status_baru === 'accepted' ? 'lni-checkmark-circle' : (r.status_baru === 'rejected' ? 'lni-close' : 'lni-hourglass');
            
            timelineHTML += `
                <div class="riwayat-item">
                    <div class="riwayat-icon ${statusClass}">
                        <i class="lni ${statusIcon}"></i>
                    </div>
                    <div class="riwayat-content">
                        <div class="riwayat-header">
                            <span class="riwayat-status ${statusClass}">${statusText}</span>
                            <span class="riwayat-date">${r.changed_at || r.tanggal || '-'}</span>
                        </div>
                        ${r.status_lama ? `<div class="riwayat-from">Dari: <strong>${r.status_lama}</strong></div>` : ''}
                        ${r.catatan ? `<div class="riwayat-catatan">${r.catatan}</div>` : ''}
                        ${r.changed_by_username ? `<div class="riwayat-admin">Oleh: <strong>${r.changed_by_username}</strong></div>` : ''}
                    </div>
                </div>
            `;
        });
    }
    
    body.innerHTML = timelineHTML;
    overlay.style.display = 'flex';
}

function closeRiwayatModal() {
    const overlay = document.getElementById('riwayatOverlay');
    if (overlay) overlay.style.display = 'none';
}
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>
