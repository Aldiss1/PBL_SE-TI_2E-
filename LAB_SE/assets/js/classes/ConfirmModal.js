/**
 * ConfirmModal - Kelas reusable untuk modal konfirmasi (delete, dll)
 */
class ConfirmModal {
    constructor(config = {}) {
        this.overlayId = config.overlayId || 'confirmDeleteOverlay';
        this.confirmButtonId = config.confirmButtonId || 'btnConfirmDelete';
        this.cancelButtonId = config.cancelButtonId || 'btnCancelDelete';
        this.deleteButtonSelector = config.deleteButtonSelector || '.btn-delete';
        this.pendingAction = null;
        this.overlay = null;
        this.btnConfirm = null;
        this.btnCancel = null;
        this.onConfirm = config.onConfirm || null;
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
        this.overlay = document.getElementById(this.overlayId);
        this.btnConfirm = document.getElementById(this.confirmButtonId);
        this.btnCancel = document.getElementById(this.cancelButtonId);
        
        if (!this.overlay || !this.btnConfirm || !this.btnCancel) {
            console.warn('ConfirmModal: Required elements not found');
            return;
        }
        
        // Setup event listeners
        if (this.btnCancel) {
            this.btnCancel.addEventListener('click', () => this.close());
        }
        
        if (this.btnConfirm) {
            this.btnConfirm.addEventListener('click', () => this.handleConfirm());
        }
        
        if (this.overlay) {
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) {
                    this.close();
                }
            });
        }
        
        // Setup delete button listeners
        document.querySelectorAll(this.deleteButtonSelector).forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const url = btn.getAttribute('data-delete-url') || btn.getAttribute('href');
                if (url) {
                    this.open(url);
                }
            });
        });
    }

    open(action) {
        this.pendingAction = action;
        if (this.overlay) {
            this.overlay.style.display = 'flex';
        }
    }

    close() {
        this.pendingAction = null;
        if (this.overlay) {
            this.overlay.style.display = 'none';
        }
    }

    handleConfirm() {
        if (this.pendingAction) {
            if (this.onConfirm) {
                this.onConfirm(this.pendingAction);
            } else {
                // Default behavior: redirect to URL
                if (typeof this.pendingAction === 'string') {
                    window.location.href = this.pendingAction;
                }
            }
        }
        this.close();
    }
}

