<?php
// Template Sidebar untuk Admin
// Pastikan variabel $current_page dan $menu_items sudah didefinisikan sebelum include file ini

// Set base_path jika belum ada (default untuk module)
if (!isset($base_path)) {
    $base_path = '../../';
}

// Set active menu berdasarkan current page
if (!isset($menu_items)) {
    $menu_items = [];
}

// Update active status berdasarkan current page jika belum diset
// Jika sudah ada active status, gunakan yang sudah ada
foreach ($menu_items as $key => $item) {
    if (!isset($menu_items[$key]['active'])) {
        $menu_items[$key]['active'] = false;
    }
    
    // Override dengan current_page jika ada
    if (isset($current_page)) {
        if ($key === 'dashboard.php' && $current_page === 'dashboard') {
            $menu_items[$key]['active'] = true;
        } elseif ($key !== 'dashboard.php' && $current_page === $key) {
            $menu_items[$key]['active'] = true;
        } elseif ($key !== 'dashboard.php' && strpos($current_page, $key) !== false) {
            $menu_items[$key]['active'] = true;
        }
    }
}
?>

<!-- Sidebar Toggle Button (Mobile) -->
<button class="sidebar-toggle" id="sidebarToggle" onclick="if(typeof toggleSidebar === 'function') { toggleSidebar(); } else if(window.adminSidebar) { window.adminSidebar.toggle(); }" style="z-index: 1000000 !important; position: fixed !important; pointer-events: auto !important; cursor: pointer !important;">
    <i class="lni lni-menu"></i>
</button>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <h4>Admin Panel</h4>
        <p><i class="lni lni-user"></i> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?></p>
    </div>
    <ul class="sidebar-menu">
        <?php if (isset($menu_items['dashboard.php'])): ?>
        <li>
            <a href="<?php echo $menu_items['dashboard.php']['link']; ?>" class="<?php echo $menu_items['dashboard.php']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['dashboard.php']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['dashboard.php']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['anggota'])): ?>
        <li>
            <a href="<?php echo $menu_items['anggota']['link']; ?>" class="<?php echo $menu_items['anggota']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['anggota']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['anggota']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['jabatan'])): ?>
        <li>
            <a href="<?php echo $menu_items['jabatan']['link']; ?>" class="<?php echo $menu_items['jabatan']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['jabatan']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['jabatan']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['user'])): ?>
        <li>
            <a href="<?php echo $menu_items['user']['link']; ?>" class="<?php echo $menu_items['user']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['user']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['user']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['pendaftar'])): ?>
        <li>
            <a href="<?php echo $menu_items['pendaftar']['link']; ?>" class="<?php echo $menu_items['pendaftar']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['pendaftar']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['pendaftar']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['blog'])): ?>
        <li>
            <a href="<?php echo $menu_items['blog']['link']; ?>" class="<?php echo $menu_items['blog']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['blog']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['blog']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
        <?php if (isset($menu_items['profil'])): ?>
        <li>
          <a href="<?php echo $menu_items['profil']['link']; ?>" class="<?php echo $menu_items['profil']['active'] ? 'active' : ''; ?>">
            <i class="<?php echo $menu_items['profil']['icon']; ?>"></i>
              <span class="menu-text"><?php echo $menu_items['profil']['title']; ?></span>
          </a>
        </li>
        <?php endif; ?>
        
        <?php if (isset($menu_items['roadmap'])): ?>
        <li>
            <a href="<?php echo $menu_items['roadmap']['link']; ?>" class="<?php echo $menu_items['roadmap']['active'] ? 'active' : ''; ?>">
                <i class="<?php echo $menu_items['roadmap']['icon']; ?>"></i>
                <span class="menu-text"><?php echo $menu_items['roadmap']['title']; ?></span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    <div class="sidebar-divider"></div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo isset($base_path) ? $base_path : '../../'; ?>index.php" target="_blank">
                <i class="lni lni-world"></i>
                <span class="menu-text">Lihat Website</span>
            </a>
        </li>
        <li>
            <a href="<?php echo isset($base_path) ? $base_path : '../../'; ?>module/auth/logout.php" style="color: #e74c3c;">
                <i class="lni lni-exit"></i>
                <span class="menu-text">Logout</span>
            </a>
        </li>
    </ul>
</aside>

