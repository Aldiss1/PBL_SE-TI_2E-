<?php
// --- 1. SETUP & KONEKSI ---
$page_title = "Kelola Roadmap - Admin"; 

// Pastikan path ke koneksi benar (naik 2 tingkat dari module/profil/)
require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../fungsi/pesan_kilat.php'; 
require_once __DIR__ . '/../auth/cek_login.php'; 

if (!isset($base_path)) {
    $base_path = '../../';
}

// --- DEFINISI VARIABEL KOSONG ---
$data_edit = null;
$pesan = '';
// --- END DEFINISI ---

// --- 2. KONFIGURASI MENU SIDEBAR ---
$current_page = 'profil'; // Tandai menu 'Kelola Profil' aktif

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
    ]
];

// --- 3. LOGIC CRUD ROADMAP PERIODE & ITEM ---

// A. LOGIC CRUD PERIODE (roadmap_periode)
// 1. Ambil data periode untuk ditampilkan
$query_periode = "SELECT id, tahun_periode, judul_fase, urutan FROM roadmap_periode ORDER BY urutan ASC";
$result_periode = pg_query($koneksi, $query_periode);
$periode_list = pg_fetch_all($result_periode);

// 2. Logic Proses Form (Tambah/Edit/Hapus Periode)
if (isset($_POST['tambah_periode'])) {
    // Logic INSERT/UPDATE akan ditambahkan di sini
}
if (isset($_GET['hapus_periode'])) {
    // Logic DELETE akan ditambahkan di sini
}

// C. LOGIC CRUD ITEM (Jika kelola_tugas aktif)
$periode_aktif = null;
$tugas_list = [];

if (isset($_GET['kelola_tugas'])) {
    $id_periode_aktif = (int)$_GET['kelola_tugas'];
    
    // Ambil nama periode aktif untuk Judul
    $query_aktif = "SELECT tahun_periode, judul_fase FROM roadmap_periode WHERE id = $1";
    $result_aktif = pg_query_params($koneksi, $query_aktif, array($id_periode_aktif));
    
    if ($result_aktif && pg_num_rows($result_aktif) > 0) {
        $periode_aktif = pg_fetch_assoc($result_aktif);

        // 3. Ambil daftar tugas saat ini
        $query_tugas = "SELECT id, deskripsi_tugas, urutan FROM roadmap_item WHERE id_periode = $1 ORDER BY urutan ASC";
        $result_tugas = pg_query_params($koneksi, $query_tugas, array($id_periode_aktif));
        $tugas_list = pg_fetch_all($result_tugas);
    }

    // Logic Tambah Tugas
    if (isset($_POST['tambah_tugas'])) {
        // Logic INSERT/UPDATE tugas akan ditambahkan di sini
    }
}


// --- 4. TAMPILAN (VIEW) ---
?>
<!DOCTYPE html>
<html class="no-js" lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title><?php echo isset($page_title) ? $page_title : 'Admin - Web Profile Laboratorium SE'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/bootstrap-5.0.0-alpha-2.min.css" />
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/LineIcons.2.0.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/animate.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/lindy-uikit.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-layout.css"/>
    <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/admin-dashboard.css"/>
</head>
<body>
    
<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="lni lni-menu"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h4>Admin Panel</h4>
        <p><i class="lni lni-user"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'admin'; ?></p>
    </div>
    <ul class="sidebar-menu">
        <?php foreach ($menu_items as $key => $item): ?>
            <li>
                <a href="<?php echo htmlspecialchars($item['link']); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                    <i class="<?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span class="menu-text"><?php echo htmlspecialchars($item['title']); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <div class="sidebar-divider"></div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo $base_path; ?>index.php" target="_blank">
                <i class="lni lni-website"></i>
                <span class="menu-text">Lihat Website</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>module/auth/logout.php" style="color:#e74c3c;">
                <i class="lni lni-exit"></i>
                <span class="menu-text">Logout</span>
            </a>
        </li>
    </ul>
</aside>
<style>
/* ------------------------------------------------ */
/* STYLE UTAMA SIDEBAR (Dark, Kotak, Ikon Putih) */
/* ------------------------------------------------ */
.admin-sidebar { 
    position: fixed; top: 0; left: 0; height: 100vh; width: 260px; 
    background: #2c3e50; 
    border-right: none; 
    z-index: 1102; padding: 20px 16px; 
    box-shadow: 2px 0 10px rgba(0,0,0,0.1); 
    overflow-y: auto; 
}
.admin-sidebar .sidebar-header { margin-bottom: 16px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.admin-sidebar .sidebar-header h4 { margin: 0 0 6px 0; font-weight: 600; color: #ffffff; font-size: 18px; text-align: center; }
.admin-sidebar .sidebar-header p { margin: 0; color: #bdc3c7; font-size: 13px; display: flex; align-items: center; justify-content: center;}