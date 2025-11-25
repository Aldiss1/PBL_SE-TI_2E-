/**
 * NavbarManager - Kelas untuk mengelola navbar sticky dan smooth scroll
 */
class NavbarManager {
    constructor() {
        this.headerNavbar = null;
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initNavbar());
        } else {
            this.initNavbar();
        }
        
        // Backup init
        setTimeout(() => this.initNavbar(), 500);
        window.addEventListener('load', () => this.initNavbar());
    }

    initNavbar() {
        this.headerNavbar = document.querySelector(".hero-section-wrapper-2 .header") || 
                           document.querySelector(".header.header-2") || 
                           document.querySelector("header.header");
        
        if (!this.headerNavbar) {
            setTimeout(() => this.initNavbar(), 100);
            return;
        }
        
        this.setupScrollHandler();
        this.setupSmoothScroll();
        this.setupAvatarErrorHandling();
    }

    ensureNavbarVisible() {
        if (!this.headerNavbar) return;
        
        // Force navbar selalu fixed dan visible
        this.headerNavbar.style.setProperty('position', 'fixed', 'important');
        this.headerNavbar.style.setProperty('top', '0', 'important');
        this.headerNavbar.style.setProperty('left', '0', 'important');
        this.headerNavbar.style.setProperty('right', '0', 'important');
        this.headerNavbar.style.setProperty('width', '100%', 'important');
        this.headerNavbar.style.setProperty('z-index', '99999', 'important');
        this.headerNavbar.style.setProperty('background', '#fff', 'important');
        this.headerNavbar.style.setProperty('display', 'block', 'important');
        this.headerNavbar.style.setProperty('visibility', 'visible', 'important');
        this.headerNavbar.style.setProperty('opacity', '1', 'important');
        
        // Tambahkan class sticky
        if (!this.headerNavbar.classList.contains("sticky")) {
            this.headerNavbar.classList.add("sticky");
        }
    }

    handleScroll() {
        if (!this.headerNavbar) return;
        
        const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
        
        // Selalu pastikan navbar visible dan fixed
        this.ensureNavbarVisible();
        
        // Tambahkan shadow saat scroll
        if (scrollY > 50) {
            this.headerNavbar.style.setProperty('box-shadow', '0 2px 10px rgba(0,0,0,0.1)', 'important');
        } else {
            this.headerNavbar.style.setProperty('box-shadow', 'none', 'important');
        }
    }

    setupScrollHandler() {
        // Override window.onscroll
        const originalOnScroll = window.onscroll;
        window.onscroll = () => {
            if (originalOnScroll) {
                try {
                    originalOnScroll();
                } catch(e) {}
            }
            this.handleScroll();
        };
        
        // Juga listen dengan addEventListener sebagai backup
        window.addEventListener('scroll', () => this.handleScroll(), { passive: true });
        
        // Initial setup
        this.ensureNavbarVisible();
        this.handleScroll();
        
        // Periodic check setiap 100ms untuk memastikan navbar selalu visible
        setInterval(() => {
            this.ensureNavbarVisible();
            const scrollY = window.pageYOffset || window.scrollY || document.documentElement.scrollTop;
            if (scrollY > 50) {
                this.headerNavbar.style.setProperty('box-shadow', '0 2px 10px rgba(0,0,0,0.1)', 'important');
            }
        }, 100);
    }

    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href !== '#0') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    }

    setupAvatarErrorHandling() {
        // Handle avatar image errors - show initial if image fails to load
        document.querySelectorAll('.dosen-avatar-img img').forEach(img => {
            img.addEventListener('error', function() {
                this.style.display = 'none';
                const parent = this.parentElement;
                if (parent && parent.classList.contains('dosen-avatar-img')) {
                    parent.style.background = 'linear-gradient(135deg, #2F80ED 0%, #6C63FF 100%)';
                    // Ensure initial is visible
                    const initial = parent.getAttribute('data-initial');
                    if (initial && !parent.querySelector('::before')) {
                        parent.innerHTML = '<span style="position: relative; z-index: 1; line-height: 1;">' + initial + '</span>';
                    }
                }
            });
            
            // Check if image loaded successfully
            if (!img.complete || img.naturalHeight === 0) {
                img.dispatchEvent(new Event('error'));
            }
        });
        
        // Fix: Pastikan avatar tidak mengikuti navbar saat scroll
        window.addEventListener('scroll', () => {
            const galeriDosen = document.querySelector('#galeri-dosen');
            if (galeriDosen) {
                const avatars = galeriDosen.querySelectorAll('.dosen-avatar-border, .dosen-avatar-img');
                avatars.forEach(avatar => {
                    avatar.style.position = 'relative';
                    avatar.style.top = 'auto';
                    avatar.style.left = 'auto';
                    avatar.style.transform = 'none';
                    avatar.style.zIndex = '1';
                });
            }
        }, { passive: true });
    }
}

// Initialize NavbarManager
if (typeof window !== 'undefined') {
    window.navbarManager = new NavbarManager();
}

