/**
 * AnggotaConfirmModal - Modal konfirmasi khusus untuk anggota
 */
class AnggotaConfirmModal {
    constructor() {
        this.overlay = null;
        this.modal = null;
        this.confirmCallback = null;
        this.init();
    }

    init() {
        // Create modal if it doesn't exist
        if (!document.getElementById('anggotaConfirmModal')) {
            this.overlay = document.createElement('div');
            this.overlay.id = 'anggotaConfirmModal';
            this.overlay.className = 'confirm-modal-overlay';
            this.overlay.innerHTML = `
                <div class="confirm-modal">
                    <div class="confirm-modal-header">
                        <h3 class="confirm-modal-title">
                            <i class="lni lni-warning confirm-modal-icon"></i>
                            <span id="confirmModalTitle">Konfirmasi</span>
                        </h3>
                        <button class="confirm-modal-close" onclick="window.anggotaConfirmModal.close()">&times;</button>
                    </div>
                    <div class="confirm-modal-body">
                        <p class="confirm-modal-message" id="confirmModalMessage">Apakah Anda yakin?</p>
                    </div>
                    <div class="confirm-modal-footer">
                        <button class="confirm-modal-btn confirm-modal-btn-cancel" onclick="window.anggotaConfirmModal.close()">
                            <i class="lni lni-close"></i> Batal
                        </button>
                        <button class="confirm-modal-btn confirm-modal-btn-confirm" id="confirmModalConfirmBtn">
                            <i class="lni lni-checkmark"></i> Ya, Lanjutkan
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(this.overlay);
            
            // Setup close on overlay click
            this.overlay.addEventListener('click', (e) => {
                if (e.target === this.overlay) {
                    this.close();
                }
            });
            
            // Setup confirm button
            const confirmBtn = document.getElementById('confirmModalConfirmBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', () => {
                    if (this.confirmCallback) {
                        this.confirmCallback();
                    }
                    this.close();
                });
            }
        } else {
            this.overlay = document.getElementById('anggotaConfirmModal');
        }
    }

    show(title, message, onConfirm) {
        const titleEl = document.getElementById('confirmModalTitle');
        const messageEl = document.getElementById('confirmModalMessage');
        
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;
        
        this.confirmCallback = onConfirm;
        this.overlay.classList.add('active');
    }

    close() {
        this.overlay.classList.remove('active');
        this.confirmCallback = null;
    }

    confirmDelete(nama, onConfirm) {
        this.show(
            'Hapus Anggota',
            `Apakah Anda yakin ingin menghapus anggota "${nama}"? Tindakan ini tidak dapat dibatalkan.`,
            onConfirm
        );
    }
}

// Initialize global confirm modal instance
if (typeof window !== 'undefined') {
    window.anggotaConfirmModal = new AnggotaConfirmModal();
}


