<?php
$page_title = "History Log Pendaftar - Admin";
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../auth/cek_login.php';
require_once __DIR__ . '/../../config/anti_injection.php';
require_once __DIR__ . '/../../fungsi/pesan_kilat.php';
require_once __DIR__ . '/../../fungsi/pendaftar_log.php';

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

// ============================================
// FILTER & PENCARIAN
// ============================================
$filter_tanggal_dari = isset($_GET['tanggal_dari']) ? clean_input($_GET['tanggal_dari']) : '';
$filter_tanggal_sampai = isset($_GET['tanggal_sampai']) ? clean_input($_GET['tanggal_sampai']) : '';
$filter_action_type = isset($_GET['action_type']) ? clean_input($_GET['action_type']) : 'all';
$filter_username = isset($_GET['username']) ? trim(clean_input($_GET['username'])) : '';
$filter_pendaftar = isset($_GET['pendaftar']) ? trim(clean_input($_GET['pendaftar'])) : '';
$search_query = isset($_GET['search']) ? trim(clean_input($_GET['search'])) : '';

// ============================================
// PAGINATION
// ============================================
$per_page = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
if (!in_array($per_page, [25, 50, 100, 200])) {
    $per_page = 50;
}
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($current_page - 1) * $per_page;

// ============================================
// QUERY LOG AKTIVITAS
// ============================================
$logs = [];
$total_records = 0;

// Build WHERE conditions
$where_conditions = [];

// Filter tanggal
if (!empty($filter_tanggal_dari)) {
    $tanggal_dari_escaped = pg_escape_string($koneksi, $filter_tanggal_dari);
    $where_conditions[] = "DATE(l.created_at) >= '$tanggal_dari_escaped'";
}
if (!empty($filter_tanggal_sampai)) {
    $tanggal_sampai_escaped = pg_escape_string($koneksi, $filter_tanggal_sampai);
    $where_conditions[] = "DATE(l.created_at) <= '$tanggal_sampai_escaped'";
}

// Filter action type
if ($filter_action_type !== 'all') {
    $action_type_escaped = pg_escape_string($koneksi, $filter_action_type);
    $where_conditions[] = "l.action_type = '$action_type_escaped'";
}

// Filter username
if (!empty($filter_username)) {
    $username_escaped = pg_escape_string($koneksi, $filter_username);
    $where_conditions[] = "l.username ILIKE '%$username_escaped%'";
}

// Filter pendaftar (nama atau NIM)
if (!empty($filter_pendaftar)) {
    $pendaftar_escaped = pg_escape_string($koneksi, $filter_pendaftar);
    $where_conditions[] = "(p.nama ILIKE '%$pendaftar_escaped%' OR p.nim ILIKE '%$pendaftar_escaped%')";
}

// Pencarian umum
if (!empty($search_query)) {
    $search_escaped = pg_escape_string($koneksi, $search_query);
    $where_conditions[] = "(l.description ILIKE '%$search_escaped%' OR p.nama ILIKE '%$search_escaped%' OR p.nim ILIKE '%$search_escaped%' OR l.username ILIKE '%$search_escaped%')";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query untuk total records
$count_query = "
    SELECT COUNT(*) as total 
    FROM log_aktivitas_pendaftar l
    LEFT JOIN pendaftaran p ON l.pendaftaran_id = p.id
    $where_clause
";

$count_result = pg_query($koneksi, $count_query);
if ($count_result) {
    $count_row = pg_fetch_assoc($count_result);
    $total_records = (int)$count_row['total'];
}

// Query untuk data dengan pagination
$query = "
    SELECT 
        l.*,
        p.nama as pendaftar_nama,
        p.nim as pendaftar_nim,
        p.email as pendaftar_email,
        p.status as pendaftar_status
    FROM log_aktivitas_pendaftar l
    LEFT JOIN pendaftaran p ON l.pendaftaran_id = p.id
    $where_clause
    ORDER BY l.created_at DESC
    LIMIT $per_page OFFSET $offset
";

$result = pg_query($koneksi, $query);
if ($result && pg_num_rows($result) > 0) {
    while ($row = pg_fetch_assoc($result)) {
        // Format tanggal
        $tanggal_formatted = '-';
        if (!empty($row['created_at'])) {
            try {
                $date = new DateTime($row['created_at'], new DateTimeZone('UTC'));
                $date->setTimezone(new DateTimeZone('Asia/Jakarta'));
                $tanggal_formatted = $date->format('d/m/Y H:i:s') . ' WIB';
            } catch (Exception $e) {
                $tanggal = strtotime($row['created_at']);
                if ($tanggal !== false) {
                    $tanggal_wib = $tanggal + (7 * 3600);
                    $tanggal_formatted = date('d/m/Y H:i:s', $tanggal_wib) . ' WIB';
                }
            }
        }
        $row['tanggal_formatted'] = $tanggal_formatted;
        
        // Parse JSON data
        if (!empty($row['old_data'])) {
            $row['old_data_parsed'] = json_decode($row['old_data'], true);
        }
        if (!empty($row['new_data'])) {
            $row['new_data_parsed'] = json_decode($row['new_data'], true);
        }
        
        // Get action type label
        $action_labels = [
            'status_change' => 'Perubahan Status',
            'account_create' => 'Pembuatan Akun',
            'account_deactivate' => 'Nonaktifkan Akun',
            'account_reset_password' => 'Reset Password',
            'update' => 'Update Data',
            'delete' => 'Hapus Pendaftar'
        ];
        $row['action_label'] = $action_labels[$row['action_type']] ?? ucfirst(str_replace('_', ' ', $row['action_type']));
        
        $logs[] = $row;
    }
}

// Hitung total pages
$total_pages = $total_records > 0 ? ceil($total_records / $per_page) : 1;

// Get action types untuk dropdown filter
$action_types_query = "SELECT DISTINCT action_type FROM log_aktivitas_pendaftar ORDER BY action_type";
$action_types_result = pg_query($koneksi, $action_types_query);
$action_types = [];
if ($action_types_result && pg_num_rows($action_types_result) > 0) {
    while ($row = pg_fetch_assoc($action_types_result)) {
        $action_types[] = $row['action_type'];
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
                    <h2 class="mb-15 wow fadeInUp" data-wow-delay=".2s">
                        <i class="lni lni-history"></i> History Log Aktivitas Pendaftar
                    </h2>
                    <p class="wow fadeInUp" data-wow-delay=".4s">
                        Riwayat lengkap semua aktivitas yang dilakukan pada data pendaftar
                    </p>
                </div>
            </div>
        </div>

        <?php tampilkan_pesan(); ?>

        <!-- Filter Section -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="wow fadeInUp filter-advanced-card" data-wow-delay=".3s" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <form method="GET" action="history_log.php" id="filterForm">
                        <div class="row">
                            <!-- Pencarian -->
                            <div class="col-md-3 mb-3">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
                                    <i class="lni lni-search"></i> Pencarian
                                </label>
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Cari di deskripsi, nama, NIM..." style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
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
                            
                            <!-- Filter Action Type -->
                            <div class="col-md-2 mb-3">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
                                    <i class="lni lni-tag"></i> Jenis Aksi
                                </label>
                                <select name="action_type" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                                    <option value="all" <?php echo $filter_action_type === 'all' ? 'selected' : ''; ?>>Semua</option>
                                    <?php foreach ($action_types as $at): ?>
                                        <?php
                                        $action_labels = [
                                            'status_change' => 'Perubahan Status',
                                            'account_create' => 'Pembuatan Akun',
                                            'account_deactivate' => 'Nonaktifkan Akun',
                                            'account_reset_password' => 'Reset Password',
                                            'update' => 'Update Data',
                                            'delete' => 'Hapus Pendaftar'
                                        ];
                                        $label = $action_labels[$at] ?? ucfirst(str_replace('_', ' ', $at));
                                        ?>
                                        <option value="<?php echo htmlspecialchars($at); ?>" <?php echo $filter_action_type === $at ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Filter Username -->
                            <div class="col-md-2 mb-3">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600; color: #2c3e50; font-size: 13px;">
                                    <i class="lni lni-user"></i> Admin/User
                                </label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($filter_username); ?>" placeholder="Username..." style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                            </div>
                            
                            <!-- Tombol -->
                            <div class="col-md-1 mb-3" style="display: flex; align-items: flex-end;">
                                <button type="submit" class="button radius-10 btn-edit" style="width: 100%; padding: 10px;">
                                    <i class="lni lni-search"></i> Cari
                                </button>
                            </div>
                        </div>
                        
                        <?php if (!empty($search_query) || !empty($filter_tanggal_dari) || !empty($filter_tanggal_sampai) || $filter_action_type !== 'all' || !empty($filter_username) || !empty($filter_pendaftar)): ?>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <a href="history_log.php" class="button button-sm radius-10 border-button">
                                    <i class="lni lni-close"></i> Reset Filter
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-30">
            <div class="col-lg-12">
                <div class="wow fadeInUp" data-wow-delay=".2s" style="background: white; padding: 15px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
                        <span style="color: #666; font-size: 14px;">
                            <i class="lni lni-list"></i> Total Log: <strong><?php echo number_format($total_records); ?></strong>
                        </span>
                        <span style="color: #666; font-size: 14px;">
                            <i class="lni lni-calendar"></i> Menampilkan: <strong><?php echo $offset + 1; ?></strong> - <strong><?php echo min($offset + $per_page, $total_records); ?></strong>
                        </span>
                        <a href="index.php" class="button button-sm radius-10 border-button">
                            <i class="lni lni-arrow-left"></i> Kembali ke Pendaftar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Timeline -->
        <div class="row">
            <div class="col-lg-12">
                <div class="wow fadeInUp" data-wow-delay=".3s" style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <?php if (count($logs) > 0): ?>
                        <div class="log-timeline">
                            <?php foreach ($logs as $log): ?>
                                <div class="log-item" data-action-type="<?php echo htmlspecialchars($log['action_type']); ?>">
                                    <div class="log-item-header">
                                        <div class="log-icon-wrapper">
                                            <?php
                                            $icon_class = 'lni-hourglass';
                                            $icon_color = '#ffc107';
                                            switch ($log['action_type']) {
                                                case 'status_change':
                                                    $icon_class = 'lni-sync';
                                                    $icon_color = '#2F80ED';
                                                    break;
                                                case 'account_create':
                                                    $icon_class = 'lni-user-plus';
                                                    $icon_color = '#28a745';
                                                    break;
                                                case 'account_deactivate':
                                                    $icon_class = 'lni-ban';
                                                    $icon_color = '#dc3545';
                                                    break;
                                                case 'account_reset_password':
                                                    $icon_class = 'lni-key';
                                                    $icon_color = '#ffc107';
                                                    break;
                                                case 'update':
                                                    $icon_class = 'lni-pencil';
                                                    $icon_color = '#17a2b8';
                                                    break;
                                                case 'delete':
                                                    $icon_class = 'lni-trash';
                                                    $icon_color = '#dc3545';
                                                    break;
                                            }
                                            ?>
                                            <div class="log-icon" style="background: <?php echo $icon_color; ?>20; color: <?php echo $icon_color; ?>;">
                                                <i class="lni <?php echo $icon_class; ?>"></i>
                                            </div>
                                        </div>
                                        <div class="log-content">
                                            <div class="log-title">
                                                <strong><?php echo htmlspecialchars($log['action_label']); ?></strong>
                                                <?php if ($log['pendaftar_nama']): ?>
                                                    <span class="log-pendaftar">
                                                        - <a href="index.php?search=<?php echo urlencode($log['pendaftar_nim']); ?>" style="color: #2F80ED; text-decoration: none;">
                                                            <?php echo htmlspecialchars($log['pendaftar_nama']); ?> (<?php echo htmlspecialchars($log['pendaftar_nim']); ?>)
                                                        </a>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="log-description">
                                                <?php echo htmlspecialchars($log['description'] ?? '-'); ?>
                                            </div>
                                            <div class="log-meta">
                                                <span class="log-time">
                                                    <i class="lni lni-calendar"></i> <?php echo htmlspecialchars($log['tanggal_formatted']); ?>
                                                </span>
                                                <?php if ($log['username']): ?>
                                                    <span class="log-user">
                                                        <i class="lni lni-user"></i> <?php echo htmlspecialchars($log['username']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Detail Changes (jika ada old_data atau new_data) -->
                                            <?php if (!empty($log['old_data_parsed']) || !empty($log['new_data_parsed'])): ?>
                                                <div class="log-details" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 6px; border-left: 3px solid #2F80ED;">
                                                    <button type="button" class="log-details-toggle" onclick="toggleLogDetails(<?php echo $log['id']; ?>)" style="background: none; border: none; color: #2F80ED; cursor: pointer; font-size: 13px; padding: 0;">
                                                        <i class="lni lni-chevron-down" id="icon-<?php echo $log['id']; ?>"></i> Lihat Detail Perubahan
                                                    </button>
                                                    <div class="log-details-content" id="details-<?php echo $log['id']; ?>" style="display: none; margin-top: 10px;">
                                                        <?php if (!empty($log['old_data_parsed'])): ?>
                                                            <div style="margin-bottom: 10px;">
                                                                <strong style="color: #dc3545; font-size: 12px;">Data Lama:</strong>
                                                                <pre style="background: #fff; padding: 8px; border-radius: 4px; font-size: 11px; margin-top: 5px; overflow-x: auto;"><?php echo htmlspecialchars(json_encode($log['old_data_parsed'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($log['new_data_parsed'])): ?>
                                                            <div>
                                                                <strong style="color: #28a745; font-size: 12px;">Data Baru:</strong>
                                                                <pre style="background: #fff; padding: 8px; border-radius: 4px; font-size: 11px; margin-top: 5px; overflow-x: auto;"><?php echo htmlspecialchars(json_encode($log['new_data_parsed'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center" style="padding: 40px;">
                            <i class="lni lni-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <p style="color: #999; font-size: 16px;">
                                <?php if (!empty($search_query) || !empty($filter_tanggal_dari) || !empty($filter_tanggal_sampai) || $filter_action_type !== 'all' || !empty($filter_username)): ?>
                                    Tidak ada log yang sesuai dengan filter yang dipilih.
                                <?php else: ?>
                                    Belum ada log aktivitas.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_records > 0): ?>
        <div class="row mt-30">
            <div class="col-lg-6">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="color: #666; font-size: 14px;">
                        Menampilkan <strong><?php echo $offset + 1; ?></strong> - <strong><?php echo min($offset + $per_page, $total_records); ?></strong> dari <strong><?php echo number_format($total_records); ?></strong> log
                    </span>
                    <select name="per_page" onchange="changePerPage(this.value)" style="padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        <option value="25" <?php echo $per_page == 25 ? 'selected' : ''; ?>>25 per halaman</option>
                        <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50 per halaman</option>
                        <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100 per halaman</option>
                        <option value="200" <?php echo $per_page == 200 ? 'selected' : ''; ?>>200 per halaman</option>
                    </select>
                </div>
            </div>
            <div class="col-lg-6">
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 10px;">
                    <?php
                    // Build query string untuk pagination
                    $query_params = [];
                    if (!empty($search_query)) $query_params[] = 'search=' . urlencode($search_query);
                    if (!empty($filter_tanggal_dari)) $query_params[] = 'tanggal_dari=' . urlencode($filter_tanggal_dari);
                    if (!empty($filter_tanggal_sampai)) $query_params[] = 'tanggal_sampai=' . urlencode($filter_tanggal_sampai);
                    if ($filter_action_type !== 'all') $query_params[] = 'action_type=' . urlencode($filter_action_type);
                    if (!empty($filter_username)) $query_params[] = 'username=' . urlencode($filter_username);
                    if (!empty($filter_pendaftar)) $query_params[] = 'pendaftar=' . urlencode($filter_pendaftar);
                    if ($per_page != 50) $query_params[] = 'per_page=' . $per_page;
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

<style>
/* Log Timeline Styles */
.log-timeline {
    position: relative;
    padding-left: 40px;
}

.log-timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.log-item {
    position: relative;
    margin-bottom: 25px;
    padding-left: 20px;
}

.log-item:last-child {
    margin-bottom: 0;
}

.log-item-header {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.log-icon-wrapper {
    position: absolute;
    left: -25px;
    top: 0;
    z-index: 1;
}

.log-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.log-content {
    flex: 1;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #2F80ED;
}

.log-title {
    font-size: 15px;
    color: #2c3e50;
    margin-bottom: 8px;
}

.log-pendaftar {
    color: #666;
    font-weight: normal;
}

.log-description {
    font-size: 14px;
    color: #555;
    margin-bottom: 10px;
    line-height: 1.5;
}

.log-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    font-size: 12px;
    color: #999;
}

.log-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.log-details-toggle {
    display: flex;
    align-items: center;
    gap: 5px;
}

.log-details-toggle:hover {
    text-decoration: underline;
}

.log-details-content pre {
    max-height: 300px;
    overflow-y: auto;
}

/* Responsive */
@media (max-width: 768px) {
    .log-timeline {
        padding-left: 30px;
    }
    
    .log-icon {
        width: 30px;
        height: 30px;
        font-size: 14px;
    }
    
    .log-content {
        padding: 12px;
    }
}
</style>

<script>
function toggleLogDetails(logId) {
    const details = document.getElementById('details-' + logId);
    const icon = document.getElementById('icon-' + logId);
    
    if (details.style.display === 'none') {
        details.style.display = 'block';
        icon.classList.remove('lni-chevron-down');
        icon.classList.add('lni-chevron-up');
    } else {
        details.style.display = 'none';
        icon.classList.remove('lni-chevron-up');
        icon.classList.add('lni-chevron-down');
    }
}

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset ke halaman 1
    window.location.href = url.toString();
}
</script>

<?php include __DIR__ . '/../../template/admin_footer.php'; ?>

