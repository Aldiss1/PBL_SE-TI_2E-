<?php
// --- 1. SETUP & KONEKSI ---
$page_title = "Kelola Profil Kami - Admin";

require_once __DIR__ . '/../../config/koneksi.php'; 
require_once __DIR__ . '/../../fungsi/pesan_kilat.php'; 
require_once __DIR__ . '/../auth/cek_login.php'; 

if (!isset($base_path)) {
    $base_path = '../../';
}

$pesan = '';

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

// --- 3. LOGIKA CRUD TENTANG KAMI ---
$tentang_kami_data = null;
$tentang_kami_features = [
    'rekayasa' => null,
    'publikasi' => null,
    'kemitraan' => null,
    'pembinaan' => null
];

// Ambil data Tentang Kami
$query_tentang_kami = "SELECT page_key, title, content FROM konten_page WHERE page_key = 'tentang_kami'";
$result_tentang_kami = pg_query($koneksi, $query_tentang_kami);
if ($result_tentang_kami && pg_num_rows($result_tentang_kami) > 0) {
    $tentang_kami_data = pg_fetch_assoc($result_tentang_kami);
}

// Ambil data 4 kartu feature
$feature_keys = ['tentang_kami_feature_rekayasa', 'tentang_kami_feature_publikasi', 'tentang_kami_feature_kemitraan', 'tentang_kami_feature_pembinaan'];
foreach ($feature_keys as $key) {
    $query_feature = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_feature = pg_query_params($koneksi, $query_feature, array($key));
    if ($result_feature && pg_num_rows($result_feature) > 0) {
        $feature_data = pg_fetch_assoc($result_feature);
        // Extract feature type from key
        if (strpos($key, 'rekayasa') !== false) {
            $tentang_kami_features['rekayasa'] = $feature_data;
        } elseif (strpos($key, 'publikasi') !== false) {
            $tentang_kami_features['publikasi'] = $feature_data;
        } elseif (strpos($key, 'kemitraan') !== false) {
            $tentang_kami_features['kemitraan'] = $feature_data;
        } elseif (strpos($key, 'pembinaan') !== false) {
            $tentang_kami_features['pembinaan'] = $feature_data;
        }
    }
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

// Handle: Update Feature (POST harus dijalankan sebelum GET)
if (isset($_POST['update_feature'])) {
    $feature_key = $_POST['feature_key'] ?? '';
    $feature_title = trim($_POST['feature_title'] ?? '');
    $feature_content = trim($_POST['feature_content'] ?? '');
    if (empty($feature_key) || empty($feature_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Data tidak valid! Judul dan deskripsi harus diisi.</div>";
    } else {
        // Cek apakah sudah ada
        $cek_feature = "SELECT COUNT(*) FROM konten_page WHERE page_key = $1";
        $result_cek = pg_query_params($koneksi, $cek_feature, array($feature_key));
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            // Update
            $update_feature = "UPDATE konten_page SET title = $1, content = $2, last_updated = NOW() WHERE page_key = $3";
            $result_update = pg_query_params($koneksi, $update_feature, array($feature_title, $feature_content, $feature_key));
            if ($result_update) {
                $pesan = "<div class='alert alert-success'>✅ Kartu feature berhasil diperbarui!</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update kartu feature: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            // Insert
            $insert_feature = "INSERT INTO konten_page (page_key, title, content) VALUES ($1, $2, $3)";
            $result_insert = pg_query_params($koneksi, $insert_feature, array($feature_key, $feature_title, $feature_content));
            if ($result_insert) {
                $pesan = "<div class='alert alert-success'>✅ Kartu feature berhasil ditambahkan!</div>";
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert kartu feature: " . pg_last_error($koneksi) . "</div>";
            }
        }
        
        // Refresh data features setelah update/insert
        if ((isset($result_update) && $result_update) || (isset($result_insert) && $result_insert)) {
            // Refresh data features
            foreach ($feature_keys as $key) {
                $query_feature = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
                $result_feature = pg_query_params($koneksi, $query_feature, array($key));
                if ($result_feature && pg_num_rows($result_feature) > 0) {
                    $feature_data = pg_fetch_assoc($result_feature);
                    if (strpos($key, 'rekayasa') !== false) {
                        $tentang_kami_features['rekayasa'] = $feature_data;
                    } elseif (strpos($key, 'publikasi') !== false) {
                        $tentang_kami_features['publikasi'] = $feature_data;
                    } elseif (strpos($key, 'kemitraan') !== false) {
                        $tentang_kami_features['kemitraan'] = $feature_data;
                    } elseif (strpos($key, 'pembinaan') !== false) {
                        $tentang_kami_features['pembinaan'] = $feature_data;
                    }
                }
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

// --- 4. LOGIKA CRUD NAVBAR LOGO ---
$navbar_logo_data = null;

// Ambil data Navbar Logo
$query_navbar_logo = "SELECT page_key, title, content FROM konten_page WHERE page_key = 'navbar_logo'";
$result_navbar_logo = pg_query($koneksi, $query_navbar_logo);
if ($result_navbar_logo && pg_num_rows($result_navbar_logo) > 0) {
    $navbar_logo_data = pg_fetch_assoc($result_navbar_logo);
}

// --- 5. LOGIKA CRUD FOOTER LINKS ---
$footer_data = [
    'deskripsi' => null,
    'copyright' => null,
    'social_links' => [],
    'tautan_cepat' => [],
    'layanan' => [],
    'informasi' => []
];

// Ambil data footer
$footer_keys = ['footer_deskripsi', 'footer_copyright', 'footer_social_links', 'footer_tautan_cepat', 'footer_layanan', 'footer_informasi'];
foreach ($footer_keys as $key) {
    $query_footer = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_footer = pg_query_params($koneksi, $query_footer, array($key));
    if ($result_footer && pg_num_rows($result_footer) > 0) {
        $footer_row = pg_fetch_assoc($result_footer);
        $content = $footer_row['content'];
        
        if ($key === 'footer_deskripsi') {
            $footer_data['deskripsi'] = $content;
        } elseif ($key === 'footer_copyright') {
            $footer_data['copyright'] = $content;
        } elseif ($key === 'footer_social_links') {
            $footer_data['social_links'] = json_decode($content, true) ?: [];
        } elseif ($key === 'footer_tautan_cepat') {
            $footer_data['tautan_cepat'] = json_decode($content, true) ?: [];
        } elseif ($key === 'footer_layanan') {
            $footer_data['layanan'] = json_decode($content, true) ?: [];
        } elseif ($key === 'footer_informasi') {
            $footer_data['informasi'] = json_decode($content, true) ?: [];
        }
    }
}

// Handle: Update Footer Deskripsi
if (isset($_POST['update_footer_deskripsi'])) {
    $deskripsi = trim($_POST['footer_deskripsi'] ?? '');
    if (empty($deskripsi)) {
        $pesan = "<div class='alert alert-danger'>❌ Deskripsi footer tidak boleh kosong!</div>";
    } else {
        $cek = "SELECT COUNT(*) FROM konten_page WHERE page_key = 'footer_deskripsi'";
        $result_cek = pg_query($koneksi, $cek);
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            $update = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = 'footer_deskripsi'";
            $result = pg_query_params($koneksi, $update, array($deskripsi));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Deskripsi footer berhasil diperbarui!</div>";
                $footer_data['deskripsi'] = $deskripsi;
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            $insert = "INSERT INTO konten_page (page_key, title, content) VALUES ('footer_deskripsi', 'Footer Deskripsi', $1)";
            $result = pg_query_params($koneksi, $insert, array($deskripsi));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Deskripsi footer berhasil ditambahkan!</div>";
                $footer_data['deskripsi'] = $deskripsi;
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// Handle: Update Footer Copyright
if (isset($_POST['update_footer_copyright'])) {
    $copyright = trim($_POST['footer_copyright'] ?? '');
    if (empty($copyright)) {
        $pesan = "<div class='alert alert-danger'>❌ Copyright tidak boleh kosong!</div>";
    } else {
        $cek = "SELECT COUNT(*) FROM konten_page WHERE page_key = 'footer_copyright'";
        $result_cek = pg_query($koneksi, $cek);
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            $update = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = 'footer_copyright'";
            $result = pg_query_params($koneksi, $update, array($copyright));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Copyright berhasil diperbarui!</div>";
                $footer_data['copyright'] = $copyright;
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            $insert = "INSERT INTO konten_page (page_key, title, content) VALUES ('footer_copyright', 'Footer Copyright', $1)";
            $result = pg_query_params($koneksi, $insert, array($copyright));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Copyright berhasil ditambahkan!</div>";
                $footer_data['copyright'] = $copyright;
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// Handle: Update Footer Links (Social, Tautan Cepat, Layanan, Informasi)
if (isset($_POST['update_footer_links'])) {
    $link_type = $_POST['link_type'] ?? '';
    $links_json = $_POST['links_json'] ?? '[]';
    
    if (empty($link_type)) {
        $pesan = "<div class='alert alert-danger'>❌ Tipe link tidak valid!</div>";
    } else {
        $page_key = 'footer_' . $link_type;
        $title_map = [
            'social_links' => 'Footer Social Links',
            'tautan_cepat' => 'Footer Tautan Cepat',
            'layanan' => 'Footer Layanan',
            'informasi' => 'Footer Informasi'
        ];
        $title = $title_map[$link_type] ?? 'Footer Links';
        
        // Validasi JSON
        $links_array = json_decode($links_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $pesan = "<div class='alert alert-danger'>❌ Format JSON tidak valid!</div>";
        } else {
            $cek = "SELECT COUNT(*) FROM konten_page WHERE page_key = $1";
            $result_cek = pg_query_params($koneksi, $cek, array($page_key));
            $count = 0;
            if ($result_cek) {
                $row_cek = pg_fetch_row($result_cek);
                $count = $row_cek[0];
            }
            
            if ($count > 0) {
                $update = "UPDATE konten_page SET content = $1, last_updated = NOW() WHERE page_key = $2";
                $result = pg_query_params($koneksi, $update, array($links_json, $page_key));
                if ($result) {
                    $pesan = "<div class='alert alert-success'>✅ Link footer berhasil diperbarui!</div>";
                    $footer_data[$link_type] = $links_array;
                } else {
                    $pesan = "<div class='alert alert-danger'>❌ Gagal update: " . pg_last_error($koneksi) . "</div>";
                }
            } else {
                $insert = "INSERT INTO konten_page (page_key, title, content) VALUES ($1, $2, $3)";
                $result = pg_query_params($koneksi, $insert, array($page_key, $title, $links_json));
                if ($result) {
                    $pesan = "<div class='alert alert-success'>✅ Link footer berhasil ditambahkan!</div>";
                    $footer_data[$link_type] = $links_array;
                } else {
                    $pesan = "<div class='alert alert-danger'>❌ Gagal insert: " . pg_last_error($koneksi) . "</div>";
                }
            }
        }
    }
}

// --- 5.5. LOGIKA CRUD FOCUS & SCOPE ---
$focus_scope_data = [
    'qa' => null,
    'hci' => null,
    'ml' => null
];

// Ambil data Focus & Scope
$focus_keys = ['focus_qa', 'focus_hci', 'focus_ml'];
foreach ($focus_keys as $key) {
    $query_focus = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
    $result_focus = pg_query_params($koneksi, $query_focus, array($key));
    if ($result_focus && pg_num_rows($result_focus) > 0) {
        $focus_row = pg_fetch_assoc($result_focus);
        if ($key === 'focus_qa') {
            $focus_scope_data['qa'] = $focus_row;
        } elseif ($key === 'focus_hci') {
            $focus_scope_data['hci'] = $focus_row;
        } elseif ($key === 'focus_ml') {
            $focus_scope_data['ml'] = $focus_row;
        }
    }
}

// Handle: Update Focus & Scope
if (isset($_POST['update_focus_scope'])) {
    $focus_key = $_POST['focus_key'] ?? '';
    $focus_title = trim($_POST['focus_title'] ?? '');
    $focus_content = trim($_POST['focus_content'] ?? '');
    $focus_icon = trim($_POST['focus_icon'] ?? '');
    
    if (empty($focus_key) || empty($focus_title) || empty($focus_content)) {
        $pesan = "<div class='alert alert-danger'>❌ Data tidak valid! Semua field harus diisi.</div>";
    } else {
        // Simpan icon dalam format JSON bersama dengan title dan content
        $focus_data = [
            'title' => $focus_title,
            'content' => $focus_content,
            'icon' => $focus_icon
        ];
        $focus_json = json_encode($focus_data);
        
        $cek = "SELECT COUNT(*) FROM konten_page WHERE page_key = $1";
        $result_cek = pg_query_params($koneksi, $cek, array($focus_key));
        $count = 0;
        if ($result_cek) {
            $row_cek = pg_fetch_row($result_cek);
            $count = $row_cek[0];
        }
        
        if ($count > 0) {
            $title_map = [
                'focus_qa' => 'Quality Assurance (QA)',
                'focus_hci' => 'Human-Computer Interaction (HCI)',
                'focus_ml' => 'Machine Learning untuk Software Engineering'
            ];
            $title = $title_map[$focus_key] ?? 'Focus & Scope';
            $update = "UPDATE konten_page SET title = $1, content = $2, last_updated = NOW() WHERE page_key = $3";
            $result = pg_query_params($koneksi, $update, array($title, $focus_json, $focus_key));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Focus & Scope berhasil diperbarui!</div>";
                // Refresh data
                $query_refresh = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
                $result_refresh = pg_query_params($koneksi, $query_refresh, array($focus_key));
                if ($result_refresh && pg_num_rows($result_refresh) > 0) {
                    $refresh_row = pg_fetch_assoc($result_refresh);
                    if ($focus_key === 'focus_qa') {
                        $focus_scope_data['qa'] = $refresh_row;
                    } elseif ($focus_key === 'focus_hci') {
                        $focus_scope_data['hci'] = $refresh_row;
                    } elseif ($focus_key === 'focus_ml') {
                        $focus_scope_data['ml'] = $refresh_row;
                    }
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal update: " . pg_last_error($koneksi) . "</div>";
            }
        } else {
            $title_map = [
                'focus_qa' => 'Quality Assurance (QA)',
                'focus_hci' => 'Human-Computer Interaction (HCI)',
                'focus_ml' => 'Machine Learning untuk Software Engineering'
            ];
            $title = $title_map[$focus_key] ?? 'Focus & Scope';
            $insert = "INSERT INTO konten_page (page_key, title, content) VALUES ($1, $2, $3)";
            $result = pg_query_params($koneksi, $insert, array($focus_key, $title, $focus_json));
            if ($result) {
                $pesan = "<div class='alert alert-success'>✅ Focus & Scope berhasil ditambahkan!</div>";
                // Refresh data
                $query_refresh = "SELECT page_key, title, content FROM konten_page WHERE page_key = $1";
                $result_refresh = pg_query_params($koneksi, $query_refresh, array($focus_key));
                if ($result_refresh && pg_num_rows($result_refresh) > 0) {
                    $refresh_row = pg_fetch_assoc($result_refresh);
                    if ($focus_key === 'focus_qa') {
                        $focus_scope_data['qa'] = $refresh_row;
                    } elseif ($focus_key === 'focus_hci') {
                        $focus_scope_data['hci'] = $refresh_row;
                    } elseif ($focus_key === 'focus_ml') {
                        $focus_scope_data['ml'] = $refresh_row;
                    }
                }
            } else {
                $pesan = "<div class='alert alert-danger'>❌ Gagal insert: " . pg_last_error($koneksi) . "</div>";
            }
        }
    }
}

// --- 6. TAMPILAN (VIEW) ---
include __DIR__ . '/../../template/admin_header.php';
?>

<!-- Sidebar -->
<?php include __DIR__ . '/../../template/admin_sidebar.php'; ?>

<style>
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
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalVisiMisiLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-bullseye" style="font-size: 22px;"></i> Kelola Visi & Misi
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalVisiMisi()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc;">
                    <?php echo $pesan; ?>

                    <!-- VISI -->
                    <div class="mb-4 pb-4" style="border-bottom: 2px solid #e9ecef; padding-bottom: 25px;">
                        <h5 style="color: #2F80ED; margin-bottom: 20px; font-weight: 600; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="lni lni-bullseye" style="font-size: 20px;"></i> Visi
                        </h5>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="visi_content" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Deskripsi Visi:</label>
                                <textarea name="visi_content" id="visi_content" class="form-control" rows="4" required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;" onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"><?php echo $visi_data ? htmlspecialchars($visi_data['content']) : ''; ?></textarea>
                                <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Masukkan deskripsi visi laboratorium</small>
                            </div>
                            <button type="submit" name="update_visi" class="btn btn-primary" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                <i class="lni lni-save"></i> Simpan Visi
                            </button>
                        </form>
                    </div>

                    <!-- MISI -->
                    <div>
                        <h5 style="color: #27AE60; margin-bottom: 20px; font-weight: 600; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="lni lni-target" style="font-size: 20px;"></i> Misi
                        </h5>
                        
                        <?php if ($misi_edit): ?>
                            <!-- Form Edit Misi -->
                            <div class="alert alert-info mb-3" style="background: #d1ecf1; border-left: 4px solid #0c5460; color: #0c5460; padding: 15px; border-radius: 8px;">
                                <strong><i class="lni lni-pencil"></i> Edit Misi:</strong> Mengedit item misi dengan key <code style="background: #fff; padding: 2px 6px; border-radius: 3px; color: #0c5460;"><?php echo htmlspecialchars($misi_edit['page_key']); ?></code>
                            </div>
                            <form method="POST" action="" style="background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef;">
                                <input type="hidden" name="misi_key" value="<?php echo htmlspecialchars($misi_edit['page_key']); ?>">
                                <div class="form-group mb-3">
                                    <label for="misi_content_edit" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Konten Misi:</label>
                                    <textarea name="misi_content" id="misi_content_edit" class="form-control" rows="3" required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;" onfocus="this.style.borderColor='#27AE60'; this.style.boxShadow='0 0 0 3px rgba(39, 174, 96, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"><?php echo htmlspecialchars($misi_edit['content']); ?></textarea>
                                </div>
                                <div style="margin-top: 15px;">
                                    <button type="submit" name="update_misi" class="btn btn-success" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border: none; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s; margin-right: 10px;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.3)';">
                                        <i class="lni lni-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="index.php" class="btn btn-secondary" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; text-decoration: none; display: inline-block; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                                        <i class="lni lni-close"></i> Batal
                                    </a>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Form Tambah Misi -->
                            <form method="POST" action="" class="mb-4" style="background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e9ecef; margin-bottom: 20px;">
                                <div class="form-group mb-3">
                                    <label for="misi_content" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Tambah Misi Baru:</label>
                                    <textarea name="misi_content" id="misi_content" class="form-control" rows="3" placeholder="Masukkan deskripsi misi baru..." required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;" onfocus="this.style.borderColor='#27AE60'; this.style.boxShadow='0 0 0 3px rgba(39, 174, 96, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"></textarea>
                                </div>
                                <button type="submit" name="tambah_misi" class="btn btn-success" style="padding: 10px 20px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border: none; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.3)';">
                                    <i class="lni lni-plus"></i> Tambah Misi
                                </button>
                            </form>

                            <!-- Daftar Misi -->
                            <?php if (!empty($misi_items)): ?>
                                <div class="table-wrapper" style="max-height: 400px; overflow-y: auto; border-radius: 8px; overflow: hidden; border: 1px solid #e9ecef;">
                                    <table class="custom-table" style="width: 100%; border-collapse: collapse; margin: 0;">
                                        <thead>
                                            <tr style="background: #f8f9fa; border-bottom: 2px solid #27AE60;">
                                                <th style="padding: 15px; text-align: center; width: 60px; font-weight: 700; color: #2c3e50 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">No</th>
                                                <th style="padding: 15px; text-align: left; font-weight: 700; color: #2c3e50 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Konten Misi</th>
                                                <th style="padding: 15px; text-align: center; width: 150px; font-weight: 700; color: #2c3e50 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($misi_items as $index => $item): ?>
                                            <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" 
                                                onmouseover="this.style.backgroundColor='#f8f9fa'" 
                                                onmouseout="this.style.backgroundColor='white'">
                                                <td style="padding: 18px; text-align: center; font-weight: 600; color: #6c757d;">
                                                    <span style="display: inline-block; width: 30px; height: 30px; line-height: 30px; background: #27AE60; color: white; border-radius: 50%; font-size: 14px;">
                                                        <?php echo $index + 1; ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 18px; color: #495057; line-height: 1.6; font-size: 14px;">
                                                    <?php echo htmlspecialchars($item['content']); ?>
                                                </td>
                                                <td style="padding: 18px; text-align: center;">
                                                    <div style="display: flex; flex-direction: column; gap: 8px; align-items: center;">
                                                        <a href="index.php?edit_misi=<?php echo urlencode($item['page_key']); ?>" 
                                                           class="btn btn-sm btn-info" 
                                                           style="padding: 8px 15px; border-radius: 6px; font-size: 13px; transition: all 0.3s; background: #2980b9; border: none; color: white; text-decoration: none; width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;"
                                                           onmouseover="this.style.background='#1f6aa5'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';"
                                                           onmouseout="this.style.background='#2980b9'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                                            <i class="lni lni-pencil"></i> Edit
                                                        </a>
                                                        <a href="index.php?hapus_misi=<?php echo urlencode($item['page_key']); ?>" 
                                                           class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Yakin ingin menghapus misi ini?');"
                                                           style="padding: 8px 15px; border-radius: 6px; font-size: 13px; transition: all 0.3s; background: #e74c3c; border: none; color: white; text-decoration: none; width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;"
                                                           onmouseover="this.style.background='#c0392b'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';"
                                                           onmouseout="this.style.background='#e74c3c'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                                            <i class="lni lni-trash"></i> Hapus
                                                        </a>
                                                    </div>
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
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 25px; background: #ffffff;">
                    <button type="button" class="btn btn-secondary" onclick="closeModalVisiMisi()" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                        <i class="lni lni-close"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: KELOLA VISI-MISI ========================= -->

    <!-- ========================= MODAL: KELOLA TENTANG KAMI ========================= -->
    <div class="modal fade" id="modalTentangKami" tabindex="-1" aria-labelledby="modalTentangKamiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalTentangKamiLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-information" style="font-size: 22px;"></i> Kelola Tentang Kami
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalTentangKami()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc;">
                    <?php echo $pesan; ?>

                    <!-- Form Judul dan Deskripsi Utama -->
                    <div class="mb-4 pb-4" style="border-bottom: 2px solid #e9ecef; padding-bottom: 25px;">
                        <h5 style="color: #2F80ED; margin-bottom: 20px; font-weight: 600; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                            <i class="lni lni-information" style="font-size: 20px;"></i> Judul & Deskripsi Utama
                        </h5>
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="tentang_kami_title" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Judul:</label>
                                <input type="text" name="tentang_kami_title" id="tentang_kami_title" class="form-control" 
                                       value="<?php echo $tentang_kami_data ? htmlspecialchars($tentang_kami_data['title']) : 'Tentang Kami'; ?>" 
                                       placeholder="Tentang Kami"
                                       style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;"
                                       onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                       onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                                <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Judul untuk konten tentang kami</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="tentang_kami_content" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Konten Tentang Kami:</label>
                                <textarea name="tentang_kami_content" id="tentang_kami_content" class="form-control" rows="5" required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s; resize: vertical;" onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"><?php echo $tentang_kami_data ? htmlspecialchars($tentang_kami_data['content']) : ''; ?></textarea>
                                <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Masukkan deskripsi lengkap tentang laboratorium</small>
                            </div>
                            <button type="submit" name="update_tentang_kami" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                <i class="lni lni-save"></i> Simpan Judul & Deskripsi
                            </button>
                        </form>
                    </div>

                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 25px; background: #ffffff;">
                    <button type="button" class="btn btn-secondary" onclick="closeModalTentangKami()" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                        <i class="lni lni-close"></i> Tutup
                    </button>
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

    <!-- ========================= SECTION: KELOLA 4 KARTU FEATURE ========================= -->
    <div class="card mb-4" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: none;">
        <div class="d-flex justify-content-between align-items-center mb-3" style="padding-bottom: 15px; border-bottom: 2px solid #f1f1f1;">
            <h3 class="card-title-profile mb-0" style="color: #2c3e50;">
                <i class="lni lni-grid-alt" style="color: #667eea; margin-right: 8px;"></i> Kelola 4 Kartu Feature
            </h3>
        </div>
        <p class="text-muted" style="margin-bottom: 0;">Kelola 4 kartu feature yang ditampilkan di halaman Tentang Kami.</p>
        
        <!-- Tabel 4 Kartu Feature -->
        <div class="table-wrapper" style="margin-top: 20px;">
                <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                            <th style="padding: 15px; text-align: center; width: 60px; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">No</th>
                            <th style="padding: 15px; text-align: left; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Judul</th>
                            <th style="padding: 15px; text-align: left; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Deskripsi</th>
                            <th style="padding: 15px; text-align: center; width: 120px; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $feature_list = [
                            ['key' => 'tentang_kami_feature_rekayasa', 'name' => 'Rekayasa Perangkat Lunak', 'icon' => 'lni-code-alt', 'color' => '#2F80ED', 'data' => $tentang_kami_features['rekayasa']],
                            ['key' => 'tentang_kami_feature_publikasi', 'name' => 'Publikasi Ilmiah', 'icon' => 'lni-book', 'color' => '#27AE60', 'data' => $tentang_kami_features['publikasi']],
                            ['key' => 'tentang_kami_feature_kemitraan', 'name' => 'Kemitraan Industri', 'icon' => 'lni-handshake', 'color' => '#E67E22', 'data' => $tentang_kami_features['kemitraan']],
                            ['key' => 'tentang_kami_feature_pembinaan', 'name' => 'Pembinaan Kompetensi', 'icon' => 'lni-graduation', 'color' => '#9B59B6', 'data' => $tentang_kami_features['pembinaan']]
                        ];
                        foreach ($feature_list as $index => $feature): 
                            $feature_data = isset($feature['data']) && is_array($feature['data']) ? $feature['data'] : null;
                            $has_data = $feature_data && !empty($feature_data['content']);
                            $title = ($feature_data && isset($feature_data['title'])) ? htmlspecialchars($feature_data['title']) : $feature['name'];
                            $content = ($feature_data && isset($feature_data['content'])) ? htmlspecialchars($feature_data['content']) : '';
                            $content_display = $has_data ? (strlen($content) > 80 ? substr($content, 0, 80) . '...' : $content) : '<span style="color: #95a5a6; font-style: italic;">Belum diatur</span>';
                        ?>
                        <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" 
                            onmouseover="this.style.backgroundColor='#f8f9fa'" 
                            onmouseout="this.style.backgroundColor='white'">
                            <td style="padding: 18px; text-align: center; font-weight: 600; color: #6c757d;">
                                <span style="display: inline-block; width: 30px; height: 30px; line-height: 30px; background: <?php echo $feature['color']; ?>; color: white; border-radius: 50%; font-size: 14px;">
                                    <?php echo $index + 1; ?>
                                </span>
                            </td>
                            <td style="padding: 18px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="lni <?php echo $feature['icon']; ?>" style="color: <?php echo $feature['color']; ?>; font-size: 20px;"></i>
                                    <strong style="color: #2c3e50; font-size: 15px;"><?php echo $title; ?></strong>
                                </div>
                            </td>
                            <td style="padding: 18px; color: #495057; line-height: 1.6;">
                                <?php echo $content_display; ?>
                            </td>
                            <td style="padding: 18px; text-align: center;">
                                <button type="button" 
                                        class="btn btn-sm btn-info" 
                                        onclick="openModalEditFeature('<?php echo htmlspecialchars($feature['key'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($title, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($content, ENT_QUOTES); ?>')"
                                        style="padding: 8px 15px; border-radius: 6px; font-size: 13px; transition: all 0.3s; background: #2980b9; border: none; color: white; cursor: pointer;"
                                        onmouseover="this.style.background='#1f6aa5'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';"
                                        onmouseout="this.style.background='#2980b9'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                    <i class="lni lni-pencil"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
    </div>
    <!-- ========================= END SECTION: KELOLA 4 KARTU FEATURE ========================= -->

    <!-- ========================= MODAL: EDIT FEATURE ========================= -->
    <div class="modal fade" id="modalEditFeature" tabindex="-1" aria-labelledby="modalEditFeatureLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalEditFeatureLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-pencil" style="font-size: 22px;"></i> Edit Kartu Feature
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalEditFeature()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc;">
                    <?php echo $pesan; ?>
                    <form method="POST" action="" id="formEditFeature">
                        <input type="hidden" name="feature_key" id="modal_feature_key" value="">
                        <div class="form-group mb-3">
                            <label for="modal_feature_title" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Judul:</label>
                            <input type="text" name="feature_title" id="modal_feature_title" class="form-control" 
                                   placeholder="Masukkan judul kartu" required
                                   style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;"
                                   onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                   onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                            <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Judul yang akan ditampilkan di kartu feature</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="modal_feature_content" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Deskripsi:</label>
                            <textarea name="feature_content" id="modal_feature_content" class="form-control" rows="4" 
                                      placeholder="Masukkan deskripsi kartu" required
                                      style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s; resize: vertical;"
                                      onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                      onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"></textarea>
                            <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Deskripsi yang akan ditampilkan di kartu feature</small>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 0 0 0; margin-top: 25px;">
                            <button type="button" class="btn btn-secondary" onclick="closeModalEditFeature()" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; transition: all 0.3s; margin-right: 10px;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                                <i class="lni lni-close"></i> Batal
                            </button>
                            <button type="submit" name="update_feature" class="btn btn-success" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border: none; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.3)';">
                                <i class="lni lni-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: EDIT FEATURE ========================= -->

    <!-- ========================= SECTION: KELOLA FOCUS & SCOPE ========================= -->
    <div class="card mb-4" style="box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: none;">
        <div class="d-flex justify-content-between align-items-center mb-3" style="padding-bottom: 15px; border-bottom: 2px solid #f1f1f1;">
            <h3 class="card-title-profile mb-0" style="color: #2c3e50;">
                <i class="lni lni-target" style="color: #667eea; margin-right: 8px;"></i> Kelola Focus & Scope
            </h3>
        </div>
        <p class="text-muted" style="margin-bottom: 20px;">Kelola 3 kartu focus & scope yang ditampilkan di landing page.</p>
        
        <!-- Tabel Focus & Scope -->
        <div class="table-wrapper" style="margin-top: 20px;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #667eea;">
                        <th style="padding: 15px; text-align: center; width: 60px; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">No</th>
                        <th style="padding: 15px; text-align: left; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Icon</th>
                        <th style="padding: 15px; text-align: left; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Judul</th>
                        <th style="padding: 15px; text-align: left; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Deskripsi</th>
                        <th style="padding: 15px; text-align: center; width: 120px; font-weight: 700; color: #000000 !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $focus_items = [
                        ['key' => 'focus_qa', 'icon' => 'lni-vector', 'title' => 'Quality Assurance (QA)', 'desc' => 'Pengujian dan jaminan kualitas perangkat lunak menggunakan metodologi terkini'],
                        ['key' => 'focus_hci', 'icon' => 'lni-layers', 'title' => 'Human-Computer Interaction (HCI)', 'desc' => 'Desain interaksi dan pengalaman pengguna untuk aplikasi yang user-friendly'],
                        ['key' => 'focus_ml', 'icon' => 'lni-code-alt', 'title' => 'Machine Learning untuk Software Engineering', 'desc' => 'Penerapan ML dalam optimasi proses pengembangan perangkat lunak']
                    ];
                    foreach ($focus_items as $index => $item): 
                        $focus_data = null;
                        if ($item['key'] === 'focus_qa' && $focus_scope_data['qa']) {
                            $focus_data = $focus_scope_data['qa'];
                        } elseif ($item['key'] === 'focus_hci' && $focus_scope_data['hci']) {
                            $focus_data = $focus_scope_data['hci'];
                        } elseif ($item['key'] === 'focus_ml' && $focus_scope_data['ml']) {
                            $focus_data = $focus_scope_data['ml'];
                        }
                        
                        // Parse JSON content jika ada
                        $focus_title = $item['title'];
                        $focus_desc = $item['desc'];
                        $focus_icon = $item['icon'];
                        if ($focus_data && !empty($focus_data['content'])) {
                            $parsed = json_decode($focus_data['content'], true);
                            if ($parsed) {
                                $focus_title = $parsed['title'] ?? $item['title'];
                                $focus_desc = $parsed['content'] ?? $item['desc'];
                                $focus_icon = $parsed['icon'] ?? $item['icon'];
                            } else {
                                // Jika bukan JSON, gunakan content langsung sebagai deskripsi
                                $focus_desc = $focus_data['content'];
                                $focus_title = $focus_data['title'] ?? $item['title'];
                            }
                        }
                    ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background-color 0.2s;" 
                        onmouseover="this.style.backgroundColor='#f8f9fa'" 
                        onmouseout="this.style.backgroundColor='white'">
                        <td style="padding: 18px; text-align: center; font-weight: 600; color: #6c757d;">
                            <span style="display: inline-block; width: 30px; height: 30px; line-height: 30px; background: #667eea; color: white; border-radius: 50%; font-size: 14px;">
                                <?php echo $index + 1; ?>
                            </span>
                        </td>
                        <td style="padding: 18px; text-align: center;">
                            <i class="lni <?php echo htmlspecialchars($focus_icon); ?>" style="font-size: 28px; color: #667eea;"></i>
                        </td>
                        <td style="padding: 18px; color: #495057; line-height: 1.6; font-size: 14px; font-weight: 600;">
                            <?php echo htmlspecialchars($focus_title); ?>
                        </td>
                        <td style="padding: 18px; color: #495057; line-height: 1.6; font-size: 14px;">
                            <?php echo htmlspecialchars(strlen($focus_desc) > 80 ? substr($focus_desc, 0, 80) . '...' : $focus_desc); ?>
                        </td>
                        <td style="padding: 18px; text-align: center;">
                            <button type="button" class="btn btn-sm btn-info" 
                                    onclick="openModalEditFocus('<?php echo $item['key']; ?>', '<?php echo htmlspecialchars(addslashes($focus_title)); ?>', '<?php echo htmlspecialchars(addslashes($focus_desc)); ?>', '<?php echo htmlspecialchars($focus_icon); ?>')"
                                    style="padding: 8px 15px; border-radius: 6px; font-size: 13px; transition: all 0.3s; background: #2980b9; border: none; color: white; width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;"
                                    onmouseover="this.style.background='#1f6aa5'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';"
                                    onmouseout="this.style.background='#2980b9'; this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                <i class="lni lni-pencil"></i> Edit
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- ========================= END SECTION: KELOLA FOCUS & SCOPE ========================= -->

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

    <!-- ========================= SECTION: KELOLA NAVBAR LOGO ========================= -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title-profile mb-0">
                <i class="lni lni-image"></i> Kelola Logo Navbar Landing Page
            </h3>
            <button type="button" class="btn btn-secondary" onclick="openModalNavbarLogo(event)" style="cursor: pointer; z-index: 1; position: relative;">
                <i class="lni lni-image"></i> Ganti Logo Navbar
            </button>
        </div>
        <p class="text-muted">Kelola logo yang ditampilkan di navbar landing page. Format yang didukung: JPG, PNG, GIF, WEBP, SVG (Maksimal 2MB).</p>
        
        <!-- Preview Logo Saat Ini -->
        <div class="mt-3">
            <label class="form-label"><strong>Logo Saat Ini:</strong></label>
            <div class="navbar-logo-preview" style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-top: 10px;">
                <?php 
                $current_logo = $navbar_logo_data ? $navbar_logo_data['content'] : '../../assets/img/logo/logo.png';
                $logo_path = strpos($current_logo, 'assets/') === 0 ? '../../' . $current_logo : $current_logo;
                ?>
                <img id="navbar-logo-preview-img" 
                     src="<?php echo htmlspecialchars($logo_path); ?>" 
                     alt="Navbar Logo" 
                     style="max-height: 80px; max-width: 200px; object-fit: contain; display: block;"
                     onerror="this.src='../../assets/img/logo/logo.png';">
                <small class="text-muted d-block mt-2">
                    Path: <?php echo htmlspecialchars($navbar_logo_data ? $navbar_logo_data['content'] : 'assets/img/logo/logo.png (default)'); ?>
                </small>
            </div>
        </div>
    </div>
    <!-- ========================= END SECTION: KELOLA NAVBAR LOGO ========================= -->

    <!-- ========================= MODAL: KELOLA NAVBAR LOGO ========================= -->
    <div class="modal fade" id="modalNavbarLogo" tabindex="-1" aria-labelledby="modalNavbarLogoLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg" style="z-index: 1055;">
            <div class="modal-content" style="z-index: 1055; border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalNavbarLogoLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-image" style="font-size: 22px;"></i> Ganti Logo Navbar Landing Page
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalNavbarLogo()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc;">
                    <div id="navbar-logo-message"></div>
                    
                    <form id="form-upload-navbar-logo" enctype="multipart/form-data">
                        <!-- File Input dengan styling menarik -->
                        <div class="form-group mb-4">
                            <label for="navbar_logo_file" class="form-label" style="font-weight: 600; color: #2c3e50; margin-bottom: 12px; display: block; font-size: 14px;">
                                <i class="lni lni-folder" style="margin-right: 8px; color: #2F80ED;"></i> Pilih Logo Baru
                            </label>
                            <div style="position: relative;">
                                <input type="file" 
                                       name="file" 
                                       id="navbar_logo_file" 
                                       class="form-control" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/svg+xml"
                                       required
                                       style="padding: 12px; border: 2px dashed #2F80ED; border-radius: 8px; background: white; cursor: pointer; transition: all 0.3s;"
                                       onmouseover="this.style.borderColor='#1E5FCC'; this.style.background='#f0f7ff';"
                                       onmouseout="this.style.borderColor='#2F80ED'; this.style.background='white';">
                            </div>
                            <small class="text-muted" style="display: block; margin-top: 8px; font-size: 12px; color: #6c757d;">
                                <i class="lni lni-info-circle" style="margin-right: 4px;"></i>
                                Format yang didukung: JPG, PNG, GIF, WEBP, SVG | Maksimal ukuran: 2MB
                            </small>
                        </div>
                        
                        <!-- Preview Area dengan styling menarik -->
                        <div class="form-group mb-4">
                            <label class="form-label" style="font-weight: 600; color: #2c3e50; margin-bottom: 12px; display: block; font-size: 14px;">
                                <i class="lni lni-eye" style="margin-right: 8px; color: #2F80ED;"></i> Preview Logo
                            </label>
                            <div id="navbar-logo-upload-preview" 
                                 style="padding: 30px; 
                                        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%); 
                                        border: 2px dashed #dee2e6; 
                                        border-radius: 12px; 
                                        text-align: center; 
                                        min-height: 200px; 
                                        display: flex; 
                                        align-items: center; 
                                        justify-content: center;
                                        transition: all 0.3s;
                                        position: relative;
                                        overflow: hidden;">
                                <div style="text-align: center;">
                                    <i class="lni lni-image" style="font-size: 48px; color: #dee2e6; margin-bottom: 10px; display: block;"></i>
                                    <p class="text-muted mb-0" style="color: #95a5a6; font-size: 14px;">Pilih file untuk melihat preview logo</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tombol dengan styling menarik -->
                        <div class="d-flex gap-2" style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                            <button type="submit" 
                                    class="btn btn-primary" 
                                    id="btn-upload-navbar-logo"
                                    style="flex: 1; 
                                           padding: 12px 24px; 
                                           border-radius: 8px; 
                                           font-weight: 600; 
                                           background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); 
                                           border: none; 
                                           box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3);
                                           transition: all 0.3s;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                <i class="lni lni-upload"></i> Upload Logo
                            </button>
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    onclick="closeModalNavbarLogo()" 
                                    style="cursor: pointer; 
                                           padding: 12px 24px; 
                                           border-radius: 8px; 
                                           font-weight: 600; 
                                           background: #6c757d; 
                                           border: none;
                                           transition: all 0.3s;"
                                    onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';"
                                    onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                                <i class="lni lni-close"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: KELOLA NAVBAR LOGO ========================= -->
    
    <!-- ========================= MODAL: KELOLA FOOTER LINKS ========================= -->
    <div class="modal fade" id="modalFooterLinks" tabindex="-1" aria-labelledby="modalFooterLinksLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalFooterLinksLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-link" style="font-size: 22px;"></i> Kelola Footer Links
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalFooterLinks()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc; max-height: 70vh; overflow-y: auto;">
                    <?php echo $pesan; ?>
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4" id="footerTabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="deskripsi-tab" data-bs-toggle="tab" data-bs-target="#deskripsi" type="button" role="tab" aria-controls="deskripsi" aria-selected="true" style="color: #2c3e50; font-weight: 600; padding: 12px 20px; border: none; background: transparent; cursor: pointer; border-bottom: 3px solid #2F80ED; margin-bottom: -2px;">
                                <i class="lni lni-text-format"></i> Deskripsi & Copyright
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="social-tab" data-bs-toggle="tab" data-bs-target="#social" type="button" role="tab" aria-controls="social" aria-selected="false" style="color: #6c757d; font-weight: 600; padding: 12px 20px; border: none; background: transparent; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.color='#2F80ED';" onmouseout="this.style.color='#6c757d';">
                                <i class="lni lni-share"></i> Social Media
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tautan-tab" data-bs-toggle="tab" data-bs-target="#tautan" type="button" role="tab" aria-controls="tautan" aria-selected="false" style="color: #6c757d; font-weight: 600; padding: 12px 20px; border: none; background: transparent; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.color='#2F80ED';" onmouseout="this.style.color='#6c757d';">
                                <i class="lni lni-link"></i> Tautan Cepat
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="layanan-tab" data-bs-toggle="tab" data-bs-target="#layanan" type="button" role="tab" aria-controls="layanan" aria-selected="false" style="color: #6c757d; font-weight: 600; padding: 12px 20px; border: none; background: transparent; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.color='#2F80ED';" onmouseout="this.style.color='#6c757d';">
                                <i class="lni lni-briefcase"></i> Layanan
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="informasi-tab" data-bs-toggle="tab" data-bs-target="#informasi" type="button" role="tab" aria-controls="informasi" aria-selected="false" style="color: #6c757d; font-weight: 600; padding: 12px 20px; border: none; background: transparent; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.color='#2F80ED';" onmouseout="this.style.color='#6c757d';">
                                <i class="lni lni-information"></i> Informasi
                            </button>
                        </li>
                    </ul>
                    
                    <style>
                    .nav-tabs .nav-link {
                        border: none !important;
                        border-radius: 0 !important;
                    }
                    .nav-tabs .nav-link.active {
                        color: #2F80ED !important;
                        background: transparent !important;
                        border-bottom: 3px solid #2F80ED !important;
                        margin-bottom: -2px !important;
                    }
                    .nav-tabs .nav-link:not(.active) {
                        color: #6c757d !important;
                    }
                    .nav-tabs .nav-link:not(.active):hover {
                        color: #2F80ED !important;
                        background: transparent !important;
                    }
                    </style>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="footerTabsContent">
                        <!-- Tab Deskripsi & Copyright -->
                        <div class="tab-pane fade show active" id="deskripsi" role="tabpanel">
                            <form method="POST" action="">
                                <div class="mb-4">
                                    <label for="footer_deskripsi" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">
                                        <i class="lni lni-text-format" style="margin-right: 8px; color: #2F80ED;"></i> Deskripsi Footer
                                    </label>
                                    <textarea name="footer_deskripsi" id="footer_deskripsi" class="form-control" rows="4" required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;" onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"><?php echo htmlspecialchars($footer_data['deskripsi'] ?? 'Laboratorium Software Engineering Politeknik Negeri Malang yang berfokus pada pengembangan perangkat lunak berkualitas tinggi, penelitian, dan pembinaan kompetensi mahasiswa di bidang rekayasa perangkat lunak.'); ?></textarea>
                                    <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Deskripsi yang ditampilkan di bagian atas footer</small>
                                </div>
                                <div class="mb-3">
                                    <label for="footer_copyright" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">
                                        <i class="lni lni-copyright" style="margin-right: 8px; color: #2F80ED;"></i> Copyright Text
                                    </label>
                                    <input type="text" name="footer_copyright" id="footer_copyright" class="form-control" value="<?php echo htmlspecialchars($footer_data['copyright'] ?? '© ' . date('Y') . ' Laboratorium Software Engineering - Jurusan Teknologi Informasi, Politeknik Negeri Malang. All rights reserved.'); ?>" required style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;" onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';" onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                                    <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Gunakan {YEAR} untuk menampilkan tahun saat ini</small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" name="update_footer_deskripsi" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                        <i class="lni lni-save"></i> Simpan Deskripsi
                                    </button>
                                    <button type="submit" name="update_footer_copyright" class="btn btn-success" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border: none; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.3)';">
                                        <i class="lni lni-save"></i> Simpan Copyright
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Tab Social Media -->
                        <div class="tab-pane fade" id="social" role="tabpanel">
                            <div id="socialLinksContainer">
                                <?php 
                                $social_links = $footer_data['social_links'] ?: [
                                    ['icon' => 'lni-facebook-filled', 'url' => '#0', 'label' => 'Facebook'],
                                    ['icon' => 'lni-twitter-filled', 'url' => '#0', 'label' => 'Twitter'],
                                    ['icon' => 'lni-instagram-filled', 'url' => '#0', 'label' => 'Instagram'],
                                    ['icon' => 'lni-linkedin-original', 'url' => '#0', 'label' => 'LinkedIn']
                                ];
                                foreach ($social_links as $index => $link): 
                                ?>
                                <div class="mb-3 p-3" style="background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Icon Class:</label>
                                            <input type="text" class="form-control social-icon" value="<?php echo htmlspecialchars($link['icon'] ?? ''); ?>" placeholder="lni-facebook-filled" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                        <div class="col-md-4">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                                            <input type="text" class="form-control social-url" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="https://..." style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                        <div class="col-md-4">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                                            <input type="text" class="form-control social-label" value="<?php echo htmlspecialchars($link['label'] ?? ''); ?>" placeholder="Facebook" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addSocialLink()">
                                <i class="lni lni-plus"></i> Tambah Link
                            </button>
                            <form method="POST" action="" id="formSocialLinks">
                                <input type="hidden" name="link_type" value="social_links">
                                <input type="hidden" name="links_json" id="socialLinksJson">
                                <button type="submit" name="update_footer_links" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                    <i class="lni lni-save"></i> Simpan Social Links
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Tautan Cepat -->
                        <div class="tab-pane fade" id="tautan" role="tabpanel">
                            <div id="tautanLinksContainer">
                                <?php 
                                $tautan_links = $footer_data['tautan_cepat'] ?: [
                                    ['label' => 'Beranda', 'url' => '/index.php#home'],
                                    ['label' => 'Tentang Kami', 'url' => '/pages/profil/tentang-kami.php'],
                                    ['label' => 'Personil', 'url' => '/pages/profil/personil.php']
                                ];
                                foreach ($tautan_links as $index => $link): 
                                ?>
                                <div class="mb-3 p-3 link-item" style="background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                                            <input type="text" class="form-control link-label" value="<?php echo htmlspecialchars($link['label'] ?? ''); ?>" placeholder="Beranda" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                                            <input type="text" class="form-control link-url" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="/index.php#home" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
                                        <i class="lni lni-trash"></i> Hapus
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addTautanLink()">
                                <i class="lni lni-plus"></i> Tambah Link
                            </button>
                            <form method="POST" action="" id="formTautanLinks">
                                <input type="hidden" name="link_type" value="tautan_cepat">
                                <input type="hidden" name="links_json" id="tautanLinksJson">
                                <button type="submit" name="update_footer_links" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                    <i class="lni lni-save"></i> Simpan Tautan Cepat
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Layanan -->
                        <div class="tab-pane fade" id="layanan" role="tabpanel">
                            <div id="layananLinksContainer">
                                <?php 
                                $layanan_links = $footer_data['layanan'] ?: [
                                    ['label' => 'Visi & Misi', 'url' => '/pages/profil/visi-misi.php'],
                                    ['label' => 'Roadmap', 'url' => '/pages/profil/roadmap.php'],
                                    ['label' => 'Blog', 'url' => '/pages/blog/blog-list.php'],
                                    ['label' => 'Dosen Pengampu', 'url' => '/index.php#galeri-dosen']
                                ];
                                foreach ($layanan_links as $index => $link): 
                                ?>
                                <div class="mb-3 p-3 link-item" style="background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                                            <input type="text" class="form-control link-label" value="<?php echo htmlspecialchars($link['label'] ?? ''); ?>" placeholder="Visi & Misi" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                                            <input type="text" class="form-control link-url" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="/pages/profil/visi-misi.php" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
                                        <i class="lni lni-trash"></i> Hapus
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addLayananLink()">
                                <i class="lni lni-plus"></i> Tambah Link
                            </button>
                            <form method="POST" action="" id="formLayananLinks">
                                <input type="hidden" name="link_type" value="layanan">
                                <input type="hidden" name="links_json" id="layananLinksJson">
                                <button type="submit" name="update_footer_links" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                    <i class="lni lni-save"></i> Simpan Layanan
                                </button>
                            </form>
                        </div>
                        
                        <!-- Tab Informasi -->
                        <div class="tab-pane fade" id="informasi" role="tabpanel">
                            <div id="informasiLinksContainer">
                                <?php 
                                $informasi_links = $footer_data['informasi'] ?: [
                                    ['label' => 'Pendaftaran', 'url' => '/pages/pendaftaran/daftar.php'],
                                    ['label' => 'Anggota Lab', 'url' => '/pages/profil/personil.php'],
                                    ['label' => 'Admin', 'url' => '/module/auth/login.php'],
                                    ['label' => 'Kontak', 'url' => '/index.php#contact']
                                ];
                                foreach ($informasi_links as $index => $link): 
                                ?>
                                <div class="mb-3 p-3 link-item" style="background: white; border-radius: 8px; border: 1px solid #e9ecef;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                                            <input type="text" class="form-control link-label" value="<?php echo htmlspecialchars($link['label'] ?? ''); ?>" placeholder="Pendaftaran" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                        <div class="col-md-6">
                                            <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                                            <input type="text" class="form-control link-url" value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>" placeholder="/pages/pendaftaran/daftar.php" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
                                        <i class="lni lni-trash"></i> Hapus
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" onclick="addInformasiLink()">
                                <i class="lni lni-plus"></i> Tambah Link
                            </button>
                            <form method="POST" action="" id="formInformasiLinks">
                                <input type="hidden" name="link_type" value="informasi">
                                <input type="hidden" name="links_json" id="informasiLinksJson">
                                <button type="submit" name="update_footer_links" class="btn btn-primary" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); border: none; box-shadow: 0 4px 15px rgba(47, 128, 237, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(47, 128, 237, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(47, 128, 237, 0.3)';">
                                    <i class="lni lni-save"></i> Simpan Informasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 25px; background: #ffffff;">
                    <button type="button" class="btn btn-secondary" onclick="closeModalFooterLinks()" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; transition: all 0.3s;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                        <i class="lni lni-close"></i> Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: KELOLA FOOTER LINKS ========================= -->
    
    <!-- ========================= MODAL: EDIT FOCUS & SCOPE ========================= -->
    <div class="modal fade" id="modalEditFocus" tabindex="-1" aria-labelledby="modalEditFocusLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%); color: white; padding: 20px 25px; border-bottom: none;">
                    <h5 class="modal-title" id="modalEditFocusLabel" style="color: white; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 10px;">
                        <i class="lni lni-target" style="font-size: 22px;"></i> Edit Focus & Scope
                    </h5>
                    <button type="button" class="btn-close btn-close-white" onclick="closeModalEditFocus()" aria-label="Close" style="cursor: pointer; opacity: 0.9; filter: brightness(0) invert(1);" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.9'"></button>
                </div>
                <div class="modal-body" style="padding: 30px; background: #fafbfc;">
                    <?php echo $pesan; ?>
                    <form method="POST" action="" id="formEditFocus">
                        <input type="hidden" name="focus_key" id="modal_focus_key" value="">
                        <div class="form-group mb-3">
                            <label for="modal_focus_icon" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Icon Class:</label>
                            <input type="text" name="focus_icon" id="modal_focus_icon" class="form-control" 
                                   placeholder="lni-vector" required
                                   style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;"
                                   onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                   onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                            <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Contoh: lni-vector, lni-layers, lni-code-alt (tanpa prefix "lni ")</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="modal_focus_title" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Judul:</label>
                            <input type="text" name="focus_title" id="modal_focus_title" class="form-control" 
                                   placeholder="Masukkan judul focus" required
                                   style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s;"
                                   onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                   onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';">
                            <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Judul yang akan ditampilkan di kartu focus</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="modal_focus_content" style="font-weight: 600; color: #2c3e50; margin-bottom: 10px; display: block; font-size: 14px;">Deskripsi:</label>
                            <textarea name="focus_content" id="modal_focus_content" class="form-control" rows="4" 
                                      placeholder="Masukkan deskripsi focus" required
                                      style="border: 1px solid #ced4da; border-radius: 8px; padding: 12px; font-size: 14px; transition: all 0.3s; resize: vertical;"
                                      onfocus="this.style.borderColor='#2F80ED'; this.style.boxShadow='0 0 0 3px rgba(47, 128, 237, 0.1)';"
                                      onblur="this.style.borderColor='#ced4da'; this.style.boxShadow='none';"></textarea>
                            <small class="text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Deskripsi yang akan ditampilkan di kartu focus</small>
                        </div>
                        <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 0 0 0; margin-top: 25px;">
                            <button type="button" class="btn btn-secondary" onclick="closeModalEditFocus()" style="cursor: pointer; padding: 10px 24px; border-radius: 8px; font-weight: 600; background: #6c757d; border: none; transition: all 0.3s; margin-right: 10px;" onmouseover="this.style.background='#5a6268'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='#6c757d'; this.style.transform='translateY(0)';">
                                <i class="lni lni-close"></i> Batal
                            </button>
                            <button type="submit" name="update_focus_scope" class="btn btn-success" style="padding: 10px 24px; border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #27AE60 0%, #229954 100%); border: none; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(39, 174, 96, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(39, 174, 96, 0.3)';">
                                <i class="lni lni-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- ========================= END MODAL: EDIT FOCUS & SCOPE ========================= -->
    
    <!-- ========================= SECTION: KELOLA FOOTER LINKS ========================= -->
    <div class="card mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="card-title-profile mb-0">
                <i class="lni lni-link"></i> Kelola Footer Links
            </h3>
            <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#modalFooterLinks" onclick="openModalFooterLinks(event)">
                <i class="lni lni-pencil"></i> Kelola Footer Links
            </button>
        </div>
        <p class="text-muted">Kelola deskripsi, copyright, social media links, dan link-link di footer website.</p>
    </div>
    <!-- ========================= END SECTION: KELOLA FOOTER LINKS ========================= -->
    
</div>
</div>
</main>


<script>
// ========================= NAVBAR LOGO UPLOAD HANDLER =========================
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan tombol bisa diklik
    const btnGantiLogo = document.querySelector('button[onclick*="openModalNavbarLogo"]');
    if (btnGantiLogo) {
        btnGantiLogo.style.pointerEvents = 'auto';
        btnGantiLogo.style.cursor = 'pointer';
        // Tambahkan event listener alternatif jika onclick tidak bekerja
        btnGantiLogo.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof openModalNavbarLogo === 'function') {
                openModalNavbarLogo(e);
            }
        });
    }
    
    const formUpload = document.getElementById('form-upload-navbar-logo');
    const fileInput = document.getElementById('navbar_logo_file');
    const previewDiv = document.getElementById('navbar-logo-upload-preview');
    const messageDiv = document.getElementById('navbar-logo-message');
    const btnUpload = document.getElementById('btn-upload-navbar-logo');
    
    // Preview image saat file dipilih
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validasi ukuran
                if (file.size > 2 * 1024 * 1024) {
                    messageDiv.innerHTML = '<div class="alert alert-danger">Ukuran file terlalu besar. Maksimal 2MB.</div>';
                    previewDiv.innerHTML = '<p class="text-muted mb-0">Pilih file untuk melihat preview</p>';
                    return;
                }
                
                // Validasi tipe
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    messageDiv.innerHTML = '<div class="alert alert-danger">Format file tidak didukung. Hanya JPG, PNG, GIF, WEBP, SVG.</div>';
                    previewDiv.innerHTML = '<p class="text-muted mb-0">Pilih file untuk melihat preview</p>';
                    return;
                }
                
                messageDiv.innerHTML = '';
                
                // Tampilkan preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewDiv.innerHTML = '<img src="' + e.target.result + '" style="max-height: 150px; max-width: 100%; object-fit: contain;">';
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.innerHTML = '<p class="text-muted mb-0">Pilih file untuk melihat preview</p>';
            }
        });
    }
    
    // Handle form submit
    if (formUpload) {
        formUpload.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const file = fileInput.files[0];
            if (!file) {
                messageDiv.innerHTML = '<div class="alert alert-danger">Silakan pilih file terlebih dahulu.</div>';
                return;
            }
            
            // Validasi ukuran
            if (file.size > 2 * 1024 * 1024) {
                messageDiv.innerHTML = '<div class="alert alert-danger">Ukuran file terlalu besar. Maksimal 2MB.</div>';
                return;
            }
            
            // Disable button
            btnUpload.disabled = true;
            btnUpload.innerHTML = '<i class="lni lni-spinner lni-spin"></i> Uploading...';
            messageDiv.innerHTML = '<div class="alert alert-info">Mengupload logo...</div>';
            
            // Upload via FormData
            const formData = new FormData();
            formData.append('file', file);
            
            // Gunakan path absolut relatif dari current location
            const uploadUrl = window.location.pathname.includes('/module/profil/') 
                ? 'upload_gambar_navbar.php' 
                : 'module/profil/upload_gambar_navbar.php';
            
            console.log('Uploading to:', uploadUrl);
            
            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Cek jika response bukan JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Response bukan JSON:', text);
                        throw new Error('Server mengembalikan response yang tidak valid: ' + text.substring(0, 200));
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    messageDiv.innerHTML = '<div class="alert alert-success">✅ ' + data.message + '</div>';
                    
                    // Update preview logo saat ini
                    const previewImg = document.getElementById('navbar-logo-preview-img');
                    if (previewImg) {
                        // Tambahkan timestamp untuk cache busting
                        previewImg.src = '../../' + data.url + '?t=' + new Date().getTime();
                    }
                    
                    // Reset form setelah 2 detik
                    setTimeout(function() {
                        formUpload.reset();
                        previewDiv.innerHTML = '<p class="text-muted mb-0">Pilih file untuk melihat preview</p>';
                        messageDiv.innerHTML = '';
                        
                        // Reload halaman setelah 1 detik untuk update data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }, 2000);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-danger">❌ ' + (data.message || 'Gagal upload logo.') + '</div>';
                    btnUpload.disabled = false;
                    btnUpload.innerHTML = '<i class="lni lni-upload"></i> Upload Logo';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                let errorMsg = '❌ Terjadi kesalahan saat upload. ';
                if (error.message) {
                    errorMsg += error.message;
                } else {
                    errorMsg += 'Silakan coba lagi atau periksa console browser untuk detail.';
                }
                messageDiv.innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
                btnUpload.disabled = false;
                btnUpload.innerHTML = '<i class="lni lni-upload"></i> Upload Logo';
            });
        });
    }
});

// Fungsi untuk membuka modal Navbar Logo
function openModalNavbarLogo(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalNavbarLogo');
    if (!modalEl) {
        console.error('Modal navbar-logo tidak ditemukan!');
        return false;
    }
    
    // Bersihkan backdrop lama jika ada
    var oldBackdrops = document.querySelectorAll('.modal-backdrop');
    oldBackdrops.forEach(function(bd) {
        bd.remove();
    });
    document.body.classList.remove('modal-open');
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Hapus instance modal lama jika ada
        var existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }
        
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl, {
            backdrop: true,
            keyboard: true
        });
        
        // Tambahkan event listener untuk cleanup saat modal ditutup
        modalEl.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            var allBackdrops = document.querySelectorAll('.modal-backdrop');
            allBackdrops.forEach(function(bd) {
                bd.remove();
            });
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, { once: true });
        
        modal.show();
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdropNavbar');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdropNavbar';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalNavbarLogo();
        });
        
        // Tutup modal saat klik tombol close
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close');
        closeBtns.forEach(function(btn) {
            btn.onclick = function() {
                closeModalNavbarLogo();
            };
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal Navbar Logo
function closeModalNavbarLogo() {
    var modalEl = document.getElementById('modalNavbarLogo');
    if (modalEl) {
        // Cek apakah Bootstrap tersedia
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
                // Tunggu animasi selesai, lalu bersihkan
                setTimeout(function() {
                    modalEl.style.display = 'none';
                    modalEl.classList.remove('show');
                    document.body.classList.remove('modal-open');
                    // Hapus semua backdrop yang mungkin tersisa
                    var allBackdrops = document.querySelectorAll('.modal-backdrop');
                    allBackdrops.forEach(function(bd) {
                        bd.remove();
                    });
                }, 300);
            } else {
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    }
    
    // Hapus semua backdrop yang mungkin ada
    var backdrop = document.getElementById('modalBackdropNavbar');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Hapus semua backdrop Bootstrap yang mungkin tersisa
    var allBackdrops = document.querySelectorAll('.modal-backdrop');
    allBackdrops.forEach(function(bd) {
        bd.remove();
    });
    
    // Pastikan body tidak dalam state modal-open
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

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
        // Cek apakah Bootstrap tersedia
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            } else {
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
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
        // Cek apakah Bootstrap tersedia
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            } else {
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    }
    
    var backdrop = document.getElementById('modalBackdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fungsi untuk membuka modal Edit Feature
function openModalEditFeature(key, title, content) {
    // Set nilai form
    document.getElementById('modal_feature_key').value = key;
    document.getElementById('modal_feature_title').value = title || '';
    document.getElementById('modal_feature_content').value = content || '';
    
    var modalEl = document.getElementById('modalEditFeature');
    if (!modalEl) {
        console.error('Modal edit-feature tidak ditemukan!');
        return false;
    }
    
    // Cek apakah Bootstrap tersedia
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        // Gunakan Bootstrap 5 API
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
        
        // Tambahkan event listener untuk tombol close setelah modal ditampilkan
        setTimeout(function() {
            var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close, .btn-secondary');
            closeBtns.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    modal.hide();
                });
            });
        }, 100);
    } else {
        // Fallback: buka modal secara manual
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
        
        // Hapus backdrop lama jika ada
        var oldBackdrop = document.getElementById('modalBackdropFeature');
        if (oldBackdrop) {
            oldBackdrop.remove();
        }
        
        // Buat backdrop baru
        var backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.id = 'modalBackdropFeature';
        backdrop.style.zIndex = '1055';
        document.body.appendChild(backdrop);
        
        // Tutup modal saat klik backdrop
        backdrop.addEventListener('click', function() {
            closeModalEditFeature();
        });
        
        // Tutup modal saat klik tombol close - dengan event listener yang lebih kuat
        var closeBtns = modalEl.querySelectorAll('[data-bs-dismiss="modal"], .btn-close, button.btn-secondary');
        closeBtns.forEach(function(btn) {
            // Hapus event listener lama jika ada
            var newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            // Tambahkan event listener baru
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                closeModalEditFeature();
            });
        });
    }
    
    return false;
}

// Fungsi untuk menutup modal Edit Feature
function closeModalEditFeature() {
    var modalEl = document.getElementById('modalEditFeature');
    if (modalEl) {
        // Cek apakah Bootstrap tersedia
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            } else {
                modalEl.style.display = 'none';
                modalEl.classList.remove('show');
                document.body.classList.remove('modal-open');
            }
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    }
    
    var backdrop = document.getElementById('modalBackdropFeature');
    if (backdrop) {
        backdrop.remove();
    }
}

// Fungsi untuk membuka modal Footer Links
function openModalFooterLinks(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    var modalEl = document.getElementById('modalFooterLinks');
    if (!modalEl) {
        console.error('Modal footer links tidak ditemukan!');
        return false;
    }
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }
        var modal = new bootstrap.Modal(modalEl, {
            backdrop: true,
            keyboard: true
        });
        
        // Inisialisasi tab navigation saat modal dibuka
        modalEl.addEventListener('shown.bs.modal', function() {
            // Pastikan tab pertama aktif
            var firstTab = document.querySelector('#footerTabs button[data-bs-target="#deskripsi"]');
            var firstPane = document.querySelector('#deskripsi');
            
            // Reset semua tab
            document.querySelectorAll('#footerTabs button').forEach(function(btn) {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
                btn.style.color = '#6c757d';
                btn.style.borderBottom = 'none';
            });
            
            document.querySelectorAll('#footerTabsContent .tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            
            // Aktifkan tab pertama
            if (firstTab) {
                firstTab.classList.add('active');
                firstTab.setAttribute('aria-selected', 'true');
                firstTab.style.color = '#2F80ED';
                firstTab.style.borderBottom = '3px solid #2F80ED';
                firstTab.style.marginBottom = '-2px';
            }
            
            if (firstPane) {
                firstPane.classList.add('show', 'active');
            }
        }, { once: true });
        
        modalEl.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            var allBackdrops = document.querySelectorAll('.modal-backdrop');
            allBackdrops.forEach(function(bd) {
                bd.remove();
            });
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, { once: true });
        modal.show();
    } else {
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
    }
    return false;
}

// Fungsi untuk membuka modal Edit Focus
function openModalEditFocus(key, title, content, icon) {
    document.getElementById('modal_focus_key').value = key;
    document.getElementById('modal_focus_title').value = title;
    document.getElementById('modal_focus_content').value = content;
    document.getElementById('modal_focus_icon').value = icon;
    
    var modalEl = document.getElementById('modalEditFocus');
    if (!modalEl) {
        console.error('Modal edit focus tidak ditemukan!');
        return false;
    }
    
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var existingModal = bootstrap.Modal.getInstance(modalEl);
        if (existingModal) {
            existingModal.dispose();
        }
        var modal = new bootstrap.Modal(modalEl, {
            backdrop: true,
            keyboard: true
        });
        modalEl.addEventListener('hidden.bs.modal', function() {
            document.body.classList.remove('modal-open');
            var allBackdrops = document.querySelectorAll('.modal-backdrop');
            allBackdrops.forEach(function(bd) {
                bd.remove();
            });
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, { once: true });
        modal.show();
    } else {
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        document.body.classList.add('modal-open');
    }
    return false;
}

// Fungsi untuk menutup modal Edit Focus
function closeModalEditFocus() {
    var modalEl = document.getElementById('modalEditFocus');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal.getInstance(modalEl)) {
            bootstrap.Modal.getInstance(modalEl).hide();
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    }
    var allBackdrops = document.querySelectorAll('.modal-backdrop');
    allBackdrops.forEach(function(bd) {
        bd.remove();
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

// Fungsi untuk menutup modal Footer Links
function closeModalFooterLinks() {
    var modalEl = document.getElementById('modalFooterLinks');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal.getInstance(modalEl)) {
            bootstrap.Modal.getInstance(modalEl).hide();
        } else {
            modalEl.style.display = 'none';
            modalEl.classList.remove('show');
            document.body.classList.remove('modal-open');
        }
    }
    var allBackdrops = document.querySelectorAll('.modal-backdrop');
    allBackdrops.forEach(function(bd) {
        bd.remove();
    });
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

// Fungsi untuk menambah Social Link
function addSocialLink() {
    var container = document.getElementById('socialLinksContainer');
    var newLink = document.createElement('div');
    newLink.className = 'mb-3 p-3';
    newLink.style.cssText = 'background: white; border-radius: 8px; border: 1px solid #e9ecef;';
    newLink.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Icon Class:</label>
                <input type="text" class="form-control social-icon" placeholder="lni-facebook-filled" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
            <div class="col-md-4">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                <input type="text" class="form-control social-url" placeholder="https://..." style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
            <div class="col-md-4">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                <input type="text" class="form-control social-label" placeholder="Facebook" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
            <i class="lni lni-trash"></i> Hapus
        </button>
    `;
    container.appendChild(newLink);
}

// Fungsi untuk menambah Tautan Cepat Link
function addTautanLink() {
    var container = document.getElementById('tautanLinksContainer');
    var newLink = document.createElement('div');
    newLink.className = 'mb-3 p-3 link-item';
    newLink.style.cssText = 'background: white; border-radius: 8px; border: 1px solid #e9ecef;';
    newLink.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                <input type="text" class="form-control link-label" placeholder="Beranda" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                <input type="text" class="form-control link-url" placeholder="/index.php#home" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
            <i class="lni lni-trash"></i> Hapus
        </button>
    `;
    container.appendChild(newLink);
}

// Fungsi untuk menambah Layanan Link
function addLayananLink() {
    var container = document.getElementById('layananLinksContainer');
    var newLink = document.createElement('div');
    newLink.className = 'mb-3 p-3 link-item';
    newLink.style.cssText = 'background: white; border-radius: 8px; border: 1px solid #e9ecef;';
    newLink.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                <input type="text" class="form-control link-label" placeholder="Visi & Misi" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                <input type="text" class="form-control link-url" placeholder="/pages/profil/visi-misi.php" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
            <i class="lni lni-trash"></i> Hapus
        </button>
    `;
    container.appendChild(newLink);
}

// Fungsi untuk menambah Informasi Link
function addInformasiLink() {
    var container = document.getElementById('informasiLinksContainer');
    var newLink = document.createElement('div');
    newLink.className = 'mb-3 p-3 link-item';
    newLink.style.cssText = 'background: white; border-radius: 8px; border: 1px solid #e9ecef;';
    newLink.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">Label:</label>
                <input type="text" class="form-control link-label" placeholder="Pendaftaran" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
            <div class="col-md-6">
                <label style="font-weight: 600; color: #2c3e50; font-size: 14px;">URL:</label>
                <input type="text" class="form-control link-url" placeholder="/pages/pendaftaran/daftar.php" style="border: 1px solid #ced4da; border-radius: 8px; padding: 10px;">
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeLinkItem(this)">
            <i class="lni lni-trash"></i> Hapus
        </button>
    `;
    container.appendChild(newLink);
}

// Fungsi untuk menghapus link item
function removeLinkItem(btn) {
    if (confirm('Yakin ingin menghapus link ini?')) {
        btn.closest('.mb-3').remove();
    }
}

// Inisialisasi Bootstrap Tabs untuk Footer Links Modal
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan tab navigation berfungsi
    var tabTriggerList = [].slice.call(document.querySelectorAll('#footerTabs button[data-bs-toggle="tab"]'));
    tabTriggerList.forEach(function (tabTriggerEl) {
        tabTriggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            var target = tabTriggerEl.getAttribute('data-bs-target');
            var tabPane = document.querySelector(target);
            
            // Hapus active dari semua tab
            tabTriggerList.forEach(function(btn) {
                btn.classList.remove('active');
                btn.setAttribute('aria-selected', 'false');
                btn.style.color = '#6c757d';
                btn.style.borderBottom = 'none';
            });
            
            // Hapus active dari semua tab pane
            document.querySelectorAll('#footerTabsContent .tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
            });
            
            // Tambahkan active ke tab yang diklik
            tabTriggerEl.classList.add('active');
            tabTriggerEl.setAttribute('aria-selected', 'true');
            tabTriggerEl.style.color = '#2F80ED';
            tabTriggerEl.style.borderBottom = '3px solid #2F80ED';
            tabTriggerEl.style.marginBottom = '-2px';
            
            // Tampilkan tab pane yang sesuai
            if (tabPane) {
                tabPane.classList.add('show', 'active');
            }
        });
    });
    
    // Social Links Form
    var formSocial = document.getElementById('formSocialLinks');
    if (formSocial) {
        formSocial.addEventListener('submit', function(e) {
            var links = [];
            document.querySelectorAll('#socialLinksContainer .mb-3').forEach(function(item) {
                var icon = item.querySelector('.social-icon')?.value || '';
                var url = item.querySelector('.social-url')?.value || '';
                var label = item.querySelector('.social-label')?.value || '';
                if (icon || url || label) {
                    links.push({icon: icon, url: url, label: label});
                }
            });
            document.getElementById('socialLinksJson').value = JSON.stringify(links);
        });
    }
    
    // Tautan Cepat Form
    var formTautan = document.getElementById('formTautanLinks');
    if (formTautan) {
        formTautan.addEventListener('submit', function(e) {
            var links = [];
            document.querySelectorAll('#tautanLinksContainer .link-item').forEach(function(item) {
                var label = item.querySelector('.link-label')?.value || '';
                var url = item.querySelector('.link-url')?.value || '';
                if (label || url) {
                    links.push({label: label, url: url});
                }
            });
            document.getElementById('tautanLinksJson').value = JSON.stringify(links);
        });
    }
    
    // Layanan Form
    var formLayanan = document.getElementById('formLayananLinks');
    if (formLayanan) {
        formLayanan.addEventListener('submit', function(e) {
            var links = [];
            document.querySelectorAll('#layananLinksContainer .link-item').forEach(function(item) {
                var label = item.querySelector('.link-label')?.value || '';
                var url = item.querySelector('.link-url')?.value || '';
                if (label || url) {
                    links.push({label: label, url: url});
                }
            });
            document.getElementById('layananLinksJson').value = JSON.stringify(links);
        });
    }
    
    // Informasi Form
    var formInformasi = document.getElementById('formInformasiLinks');
    if (formInformasi) {
        formInformasi.addEventListener('submit', function(e) {
            var links = [];
            document.querySelectorAll('#informasiLinksContainer .link-item').forEach(function(item) {
                var label = item.querySelector('.link-label')?.value || '';
                var url = item.querySelector('.link-url')?.value || '';
                if (label || url) {
                    links.push({label: label, url: url});
                }
            });
            document.getElementById('informasiLinksJson').value = JSON.stringify(links);
        });
    }
});
</script>

<?php 
// Tambahkan footer di akhir file
include __DIR__ . '/../../template/admin_footer.php'; 
?>