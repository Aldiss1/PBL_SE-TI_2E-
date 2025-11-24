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
$data_edit = null;
$semua_konten = [];
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
    ]
];

// --- 3. LOGIKA UPDATE / SIMPAN DATA (POST) ---
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_key = $_POST['page_key']; 
    $title    = $_POST['title'];
    $content  = $_POST['content']; 

    if (empty($page_key) || empty($content)) {
        $pesan = "<div class='alert alert-danger'>❌ Error: Page Key dan Konten wajib diisi!</div>";
    } else {
        // Cek data
        $query_cek = "SELECT COUNT(*) FROM konten_page WHERE page_key = $1";
        $result_cek = pg_query_params($koneksi, $query_cek, array($page_key));
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }

        if ($count > 0) {
            // Update
            $query_update = "UPDATE konten_page SET title = $1, content = $2, last_updated = NOW() WHERE page_key = $3";
            $result_update = pg_query_params($koneksi, $query_update, array($title, $content, $page_key));
            if ($result_update) {
                $pesan = "<div class='alert alert-success'>✅ Konten <b>" . htmlspecialchars($page_key) . "</b> berhasil diperbarui!</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal Update: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            // Insert
            $query_insert = "INSERT INTO konten_page (page_key, title, content) VALUES ($1, $2, $3)";
            $result_insert = pg_query_params($koneksi, $query_insert, array($page_key, $title, $content));
            if ($result_insert) {
                $pesan = "<div class='alert alert-success'>✅ Konten baru <b>" . htmlspecialchars($page_key) . "</b> berhasil ditambahkan!</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal Insert: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// --- 4. LOGIKA AMBIL DATA UNTUK EDIT (GET) ---
$data_edit = null;
if (isset($_GET['edit'])) {
    $edit_key = $_GET['edit'];
    $query_get = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_get = pg_query_params($koneksi, $query_get, array($edit_key));
    
    if ($result_get && pg_num_rows($result_get) > 0) {
        $data_edit = pg_fetch_assoc($result_get);
    }
}

// --- 5. AMBIL SEMUA DATA ---
$semua_konten = [];
$query_all = "SELECT page_key, title, content, last_updated FROM konten_page ORDER BY page_key ASC";
$result_all = pg_query($koneksi, $query_all);

if ($result_all) {
    while ($row = pg_fetch_assoc($result_all)) {
        $semua_konten[] = $row;
    }
}

// --- 6. TAMPILAN (VIEW) ---
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
    
    <?php

// Sidebar sederhana untuk Mahasiswa
if (!isset($base_path)) {
    $base_path = '../../';
}

if (!isset($current_page)) {
    $current_page = 'dashboard';
}
?>

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
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (window.innerWidth <= 991) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
}

document.getElementById('sidebarOverlay').addEventListener('click', function() {
    if (window.innerWidth <= 991) {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }
});

window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (window.innerWidth > 991) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }
});
</script>


<div class="admin-main-content"> <div class="profil-container">
    
    <div class="header-content"> <h1>Kelola Teks Profil & Halaman</h1>
        <p>Kelola konten dinamis seperti Visi, Misi, Sejarah, dan Teks Sambutan</p>
    </div>
    <?php echo $pesan; ?>

    <div class="card">
        <h3 class="card-title-profile">
            <?php echo $data_edit ? '✏️ Edit Konten: ' . htmlspecialchars($data_edit['page_key']) : '➕ Tambah Konten Baru'; ?>
        </h3>
        <hr class="card-divider"/>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="page_key">Page Key (ID Unik):</label>
                <input type="text" name="page_key" id="page_key" class="form-control" 
                        value="<?php echo $data_edit ? htmlspecialchars($data_edit['page_key']) : ''; ?>" 
                        placeholder="contoh: visi_misi, sejarah, sambutan"
                        required 
                        <?php echo $data_edit ? 'readonly class="form-control-readonly"' : ''; ?>>
                <?php if (!$data_edit): ?>
                <small class="text-muted-small">Gunakan huruf kecil tanpa spasi (gunakan underscore _).</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="title">Judul (Opsional):</label>
                <input type="text" name="title" id="title" class="form-control" 
                        value="<?php echo $data_edit ? htmlspecialchars($data_edit['title']) : ''; ?>"
                        placeholder="Judul konten jika ada">
            </div>

            <div class="form-group">
                <label for="content">Isi Konten:</label>
                <textarea name="content" id="content" class="form-control" required><?php echo $data_edit ? htmlspecialchars($data_edit['content']) : ''; ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">💾 Simpan Konten</button>
            
            <?php if ($data_edit): ?>
                <a href="index.php" class="btn btn-danger" style="margin-left: 10px;">Batal Edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title-profile">🗃️ Daftar Konten Tersimpan</h3>
        <hr class="card-divider"/>
        <div class="table-wrapper">
            <?php if (empty($semua_konten)): ?>
                <p style="text-align: center; padding: 20px;">Belum ada konten yang tersimpan.</p>
            <?php else: ?>
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th width="150">Page Key</th>
                            <th width="150">Judul</th>
                            <th>Preview Isi</th>
                            <th width="200">Terakhir Update</th>
                            <th width="150">Aksi</th> </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semua_konten as $row): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($row['page_key']); ?></code></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <?php 
                                // Preview Isi (80 karakter)
                                $preview = htmlspecialchars(substr(strip_tags($row['content']), 0, 80)); 
                                echo $preview;
                                echo strlen(strip_tags($row['content'])) > 80 ? '...' : '';
                                ?>
                            </td>
                            <td><?php echo $row['last_updated']; ?></td>
                            <td>
                                <a href="index.php?edit=<?php echo urlencode($row['page_key']); ?>" class="btn btn-sm btn-info" style="margin-right: 5px;">
                                    ✏️ Edit
                                </a>
                                <a href="delete.php?key=<?php echo urlencode($row['page_key']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus konten ini? Tindakan ini tidak bisa dibatalkan.');">
                                    🗑️ Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</div> </body>
</html>