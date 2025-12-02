/**
 * AdminSidebar - Kelas untuk mengelola sidebar admin (toggle, overlay)
 */
// Cek apakah class sudah dideklarasikan untuk mencegah duplikasi
if (typeof AdminSidebar === 'undefined') {
class AdminSidebar {
    constructor() {
        this.sidebar = null;
        this.overlay = null;
        this.toggleButton = null;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupElements());
        } else {
            this.setupElements();
        }
    }

    setupElements() {
        this.sidebar = document.getElementById('adminSidebar');
        this.overlay = document.getElementById('sidebarOverlay');
        this.toggleButton = document.getElementById('sidebarToggle');
        
        if (this.toggleButton) {
            // Remove existing onclick handler and use addEventListener
            this.toggleButton.onclick = null;
            this.toggleButton.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });
            // Also add touch event for mobile
            this.toggleButton.addEventListener('touchend', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            });
        }
        
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.close());
        }
        
        // Close sidebar on window resize if desktop
        window.addEventListener('resize', () => this.handleResize());
        
        // Expose global function for backward compatibility
        if (typeof window !== 'undefined') {
            window.toggleSidebar = () => {
                this.toggle();
            };
        }
    }

    toggle() {
        if (window.innerWidth <= 991) {
            if (this.sidebar) {
                this.sidebar.classList.toggle('active');
            }
            if (this.overlay) {
                this.overlay.classList.toggle('active');
            }
        }
    }

    open() {
        if (window.innerWidth <= 991) {
            if (this.sidebar) {
                this.sidebar.classList.add('active');
            }
            if (this.overlay) {
                this.overlay.classList.add('active');
            }
        }
    }

    close() {
        if (window.innerWidth <= 991) {
            if (this.sidebar) {
                this.sidebar.classList.remove('active');
            }
            if (this.overlay) {
                this.overlay.classList.remove('active');
            }
        }
    }

    handleResize() {
        if (window.innerWidth > 991) {
            this.close();
        }
    }
}
} // End if typeof AdminSidebar === 'undefined'

// Initialize AdminSidebar (hanya jika belum ada instance)
if (typeof window !== 'undefined' && typeof AdminSidebar !== 'undefined' && !window.adminSidebar) {
    window.adminSidebar = new AdminSidebar();
}

