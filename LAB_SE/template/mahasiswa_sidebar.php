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
        <h4>Akun Mahasiswa</h4>
        <p><i class="lni lni-user"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Mahasiswa'; ?></p>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo $base_path; ?>module/mahasiswa/dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <i class="lni lni-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>module/mahasiswa/profile.php" class="<?php echo $current_page === 'profile' ? 'active' : ''; ?>">
                <i class="lni lni-user"></i>
                <span class="menu-text">Profil</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>module/mahasiswa/announcements.php" class="<?php echo $current_page === 'announcements' ? 'active' : ''; ?>">
                <i class="lni lni-bullhorn"></i>
                <span class="menu-text">Pengumuman</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $base_path; ?>module/mahasiswa/status.php" class="<?php echo $current_page === 'status' ? 'active' : ''; ?>">
                <i class="lni lni-clipboard"></i>
                <span class="menu-text">Status Pendaftaran</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-divider"></div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo $base_path; ?>module/auth/logout.php" style="color:#e74c3c;">
                <i class="lni lni-exit"></i>
                <span class="menu-text">Logout</span>
            </a>
        </li>
    </ul>
</aside>

<style>
.sidebar-toggle { position: fixed; top: 14px; left: 14px; z-index: 1101; background: #ffffff; border: 1px solid #eaecef; width: 38px; height: 38px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px rgba(0,0,0,0.06); color: #2c3e50; }
.sidebar-toggle:hover { background: #f7f9fc; }

.sidebar-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 1100; display: none; }
.sidebar-overlay.active { display: block; }

.admin-sidebar { position: fixed; top: 0; left: 0; height: 100vh; width: 260px; background: #ffffff; border-right: 1px solid #eaecef; z-index: 1102; padding: 20px 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.06); overflow-y: auto; }
.admin-sidebar .sidebar-header { margin-bottom: 16px; }
.admin-sidebar .sidebar-header h4 { margin: 0 0 6px 0; font-weight: 800; color: #2c3e50; font-size: 22px; letter-spacing: .3px; }
.admin-sidebar .sidebar-header p { margin: 0; color: #6c757d; font-size: 13px; display: flex; align-items: center; gap: 6px; }

.admin-sidebar .sidebar-menu { list-style: none; margin: 10px 0; padding: 0; }
.admin-sidebar .sidebar-menu li { margin-bottom: 6px; }
.admin-sidebar .sidebar-menu a { display: flex; align-items: center; gap: 10px; color: #2c3e50; text-decoration: none; padding: 10px 12px; border-radius: 10px; transition: background .15s ease, color .15s ease, transform .06s ease; }
.admin-sidebar .sidebar-menu a i { color: #2F80ED; font-size: 18px; }
.admin-sidebar .sidebar-menu a:hover { background: #f0f5ff; color: #2F80ED; transform: translateY(-1px); }
.admin-sidebar .sidebar-menu a.active { background: #eaf2ff; color: #2F80ED; font-weight: 700; }
.admin-sidebar .sidebar-menu a.active i { color: #2F80ED; }
.admin-sidebar .sidebar-divider { height: 1px; background: #f1f3f5; margin: 14px 0; }

.admin-main-content { margin-left: 260px; transition: margin-left .2s ease; }

@media (max-width: 991px) {
  .admin-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
  .admin-sidebar.active { transform: translateX(0); }
  .admin-main-content { margin-left: 0; }
}
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


