<?php
// --- 1. SETUP & KONEKSI ---
$page_title = "Kelola Teks Profil & Halaman - Admin";

// Pastikan path ke koneksi benar
require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../fungsi/pesan_kilat.php'; 
require_once __DIR__ . '/../auth/cek_login.php'; 

if (!isset($base_path)) {
    $base_path = '../../';
}

// Anotasi untuk menghilangkan warning 'resource'/'PgSql\Connection' di editor
/** @var PgSql\Connection $koneksi */ 

// --- DEFINISI VARIABEL KOSONG ---
$pesan = '';
$data_edit = null;
$semua_konten = [];
// Pastikan $data_list didefinisikan sebagai array kosong jika tidak ada data yang diambil
$data_list = [];
// --- END DEFINISI ---

// --- CEK DAN BUAT TABEL konten_page JIKA BELUM ADA ---
$check_table = pg_query($koneksi, "
    SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'konten_page'
    )
");

if ($check_table) {
    $table_exists = pg_fetch_result($check_table, 0, 0);
    if ($table_exists !== 't') {
        // Buat tabel konten_page
        pg_query($koneksi, "
            CREATE TABLE IF NOT EXISTS konten_page (
                id SERIAL PRIMARY KEY,
                page_key VARCHAR(100) NOT NULL UNIQUE,
                title VARCHAR(255) NULL,
                content TEXT NOT NULL,
                last_updated TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Buat trigger untuk update last_updated
        pg_query($koneksi, "
            CREATE OR REPLACE FUNCTION update_last_updated_column()
            RETURNS TRIGGER AS \$\$
            BEGIN
                NEW.last_updated = now();
                RETURN NEW;
            END;
            \$\$ language 'plpgsql'
        ");
        
        pg_query($koneksi, "
            DROP TRIGGER IF EXISTS last_updated_trg ON konten_page;
            CREATE TRIGGER last_updated_trg
            BEFORE UPDATE ON konten_page
            FOR EACH ROW
            EXECUTE PROCEDURE update_last_updated_column()
        ");
        
        // Insert data default jika tabel baru dibuat
        pg_query($koneksi, "
            INSERT INTO konten_page (page_key, title, content) VALUES 
            ('roadmap_judul', 'Roadmap Laboratorium SE', 'Perjalanan dan Rencana Pengembangan Laboratorium Software Engineering.'),
            ('visi_deskripsi', 'Visi Kami', 'Menjadi pusat penelitian software engineering yang relevan dan berdaya guna bagi pengembangan teknologi informasi di Indonesia.'),
            ('misi_item_1', 'Misi Utama', 'Melakukan penelitian berkualitas di bidang perangkat lunak yang memberikan kontribusi nyata bagi industri dan akademisi.'),
            ('tentang_kami', 'Tentang Lab SE', 'Laboratorium Software Engineering merupakan wadah kolaborasi mahasiswa dan dosen untuk penelitian dan pengembangan.')
            ON CONFLICT (page_key) DO NOTHING
        ");
    }
}

// --- 2. KONFIGURASI MENU SIDEBAR ---
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
        // PERINGATAN: Pastikan Anda melakukan sanitasi HTML di sini untuk mencegah XSS.
        
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

// --- 4. LOGIKA HAPUS DATA (GET) ---
if (isset($_GET['delete'])) {
    $delete_key = $_GET['delete'];
    // Jangan hapus visi_deskripsi dan misi_item_% dari sini (gunakan modal Visi-Misi)
    if ($delete_key !== 'visi_deskripsi' && strpos($delete_key, 'misi_item_') !== 0) {
        $query_delete = "DELETE FROM konten_page WHERE page_key = $1";
        $result_delete = pg_query_params($koneksi, $query_delete, array($delete_key));
        if ($result_delete) {
            $pesan = "<div class='alert alert-success'>✅ Konten <b>" . htmlspecialchars($delete_key) . "</b> berhasil dihapus!</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>❌ Gagal hapus: " . pg_last_error($koneksi) . "</div>";
        }
    } else {
        $pesan = "<div class='alert alert-warning'>⚠️ Konten ini tidak bisa dihapus dari sini. Gunakan menu Kelola Visi & Misi untuk menghapus.</div>";
    }
}

// --- 5. LOGIKA AMBIL DATA UNTUK EDIT (GET) ---
$data_edit = null;
if (isset($_GET['edit'])) {
    $edit_key = $_GET['edit'];
    $query_get = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_get = pg_query_params($koneksi, $query_get, array($edit_key));
    
    if ($result_get && pg_num_rows($result_get) > 0) {
        $data_edit = pg_fetch_assoc($result_get);
    }
}

// --- 5. LOGIKA CRUD TENTANG KAMI ---
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

// --- 7. AMBIL SEMUA DATA KONTEN LAINNYA ---
$semua_konten = [];
$query_all = "SELECT page_key, title, content, last_updated FROM konten_page WHERE page_key NOT IN ('visi_deskripsi', 'tentang_kami') AND page_key NOT LIKE 'misi_item_%' ORDER BY page_key ASC";
$result_all = pg_query($koneksi, $query_all);

if ($result_all) {
    while ($row = pg_fetch_assoc($result_all)) {
        $semua_konten[] = $row;
    }
} else {
    // Jika query gagal, log error tapi jangan crash
    error_log("Error query konten_page: " . pg_last_error($koneksi));
    $semua_konten = []; // Tetap set sebagai array kosong
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
<style>
/* ------------------------------------------------ */
/* STYLE UTAMA SIDEBAR (Dark, Kotak, Ikon Putih) */
/* ------------------------------------------------ */
.admin-sidebar { 
    position: fixed; top: 0; left: 0; height: 100vh; width: 260px; 
    /* WARNA BACKGROUND GELAP SESUAI GAMBAR */
    background: #2c3e50; 
    border-right: none; 
    z-index: 1102; padding: 20px 16px; 
    box-shadow: 2px 0 10px rgba(0,0,0,0.1); /* Shadow lebih gelap dari default Anda */
    overflow-y: auto; 
}
.admin-sidebar .sidebar-header { margin-bottom: 16px; padding-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.1); }
.admin-sidebar .sidebar-header h4 { margin: 0 0 6px 0; font-weight: 600; color: #ffffff; font-size: 18px; text-align: center; }
.admin-sidebar .sidebar-header p { margin: 0; color: #bdc3c7; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px; }

.admin-sidebar .sidebar-menu { list-style: none; margin: 10px 0; padding: 0; }
.admin-sidebar .sidebar-menu li { margin-bottom: 0; /* Menghilangkan margin antar item menu */ }

/* Item Menu - Default */
.admin-sidebar .sidebar-menu a { 
    display: flex; align-items: center; gap: 10px; 
    color: #ecf0f1; /* Teks default abu-abu terang */
    text-decoration: none; 
    padding: 15px 16px; /* Padding dibuat lebih besar agar full kotak */
    border-radius: 0; /* PENTING: Menghilangkan sudut melengkung */
    transition: background .15s ease, color .15s ease, transform .06s ease; 
}
/* Ikon - Dibuat Putih Solid */
.admin-sidebar .sidebar-menu a i { 
    color: #ffffff; /* PENTING: Ikon putih solid */
    font-size: 20px; 
}

/* Hover Effect (Tidak bergeser, background transparan) */
.admin-sidebar .sidebar-menu a:hover { 
    background: rgba(255,255,255,0.1); /* Background transparan putih */
    color: #ffffff; 
    transform: none; /* PENTING: Menonaktifkan efek translateY(-1px) */
}

/* Item Menu AKTIF (Full Kotak & Biru Solid) */
.admin-sidebar .sidebar-menu a.active { 
    background: #2F80ED; /* PENTING: Warna biru solid */
    color: #ffffff; 
    font-weight: 600; 
    border-radius: 0; /* PENTING: Tetap kotak */
}
/* Ikon Item AKTIF */
.admin-sidebar .sidebar-menu a.active i { 
    color: #ffffff !important; /* Pastikan ikon aktif tetap putih */
}

.admin-sidebar .sidebar-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 15px 0; }

.sidebar-toggle { 
    position: fixed; top: 14px; left: 14px; z-index: 1101; 
    background: #2F80ED; /* Warna toggle biru */
    color: #ffffff; 
    border: none; border-radius: 5px; width: 38px; height: 38px; 
    display: inline-flex; align-items: center; justify-content: center; 
    box-shadow: 0 6px 16px rgba(0,0,0,0.2); 
}
.sidebar-toggle:hover { background: #1E5FCC; }

/* Mobile */
@media (max-width: 991px) {
    .admin-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
    .admin-sidebar.active { transform: translateX(0); }
    .admin-main-content { margin-left: 0; }
}

.admin-main-content { margin-left: 260px; transition: margin-left .2s ease; padding: 20px; }
.sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1100; display: none; }
.sidebar-overlay.active { display: block; }

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

/* Alignment untuk button di card kelola profil */
.card .d-flex.justify-content-between.align-items-center {
	align-items: center !important;
}

.card .d-flex.justify-content-between h3.card-title-profile {
	flex: 1;
	margin: 0;
	padding-right: 20px;
	line-height: 1.4;
	display: flex;
	align-items: center;
}

.card .d-flex.justify-content-between .btn,
.card .d-flex.justify-content-between a.btn {
	flex-shrink: 0;
	white-space: nowrap;
	height: 38px;
	min-height: 38px;
	max-height: 38px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 8px 16px;
	margin: 0;
	vertical-align: middle;
	line-height: 1;
}
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


    <div class="container-fluid">
    
    <div class="row align-items-center mb-4">
        <div class="col-6">
            </div>
        <div class="col-6 d-flex justify-content-end"> 
            <a href="kelola_roadmap.php" class="btn btn-secondary" style="white-space: nowrap;">
                <i class="lni lni-map-marker mr-2"></i> Kelola Roadmap
            </a>
        </div>
    </div>
    
    <div class="card p-4">
        <table class="table table-striped">
            
            <tbody>
                <?php foreach ($data_list as $d): ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['id']); ?></td>
                    <td><?php echo htmlspecialchars($d['judul_konten']); ?></td>
                    
                    <td class="text-end"> 
                        <a href="kelola_teks_umum.php?id=<?php echo $d['id']; ?>" class="btn btn-sm btn-primary">Kelola Konten</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        </div>
    
</div>

</div>
</div>

</body>
</html>



<?php 
// Tambahkan footer di akhir file
include __DIR__ . '/../../template/admin_footer.php'; 
?>