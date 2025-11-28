/**
 * LoadingState - Kelas untuk mengelola loading states
 */
class LoadingState {
    constructor() {
        this.overlay = null;
        this.init();
    }

    init() {
        // Create loading overlay if it doesn't exist
        if (!document.getElementById('loadingOverlay')) {
            this.overlay = document.createElement('div');
            this.overlay.id = 'loadingOverlay';
            this.overlay.className = 'loading-overlay';
            this.overlay.innerHTML = `
                <div style="text-align: center;">
                    <div class="loading-spinner"></div>
                    <div class="loading-text">Memproses...</div>
                </div>
            `;
            document.body.appendChild(this.overlay);
        } else {
            this.overlay = document.getElementById('loadingOverlay');
        }
    }

    show(text = 'Memproses...') {
        if (this.overlay) {
            const loadingText = this.overlay.querySelector('.loading-text');
            if (loadingText) {
                loadingText.textContent = text;
            }
            this.overlay.classList.add('active');
        }
    }

    hide() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
        }
    }

    setButtonLoading(button, loading = true) {
        if (!button) return;
        
        if (loading) {
            button.classList.add('button-loading');
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span style="opacity: 0;">' + button.innerHTML + '</span>';
        } else {
            button.classList.remove('button-loading');
            button.disabled = false;
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
                delete button.dataset.originalText;
            }
        }
    }

    setTableLoading(tableContainer, loading = true) {
        if (!tableContainer) return;
        
        let loadingElement = tableContainer.querySelector('.table-loading');
        
        if (loading) {
            if (!loadingElement) {
                loadingElement = document.createElement('div');
                loadingElement.className = 'table-loading';
                loadingElement.innerHTML = '<div class="loading-spinner"></div>';
                tableContainer.style.position = 'relative';
                tableContainer.appendChild(loadingElement);
            }
            loadingElement.classList.add('active');
        } else {
            if (loadingElement) {
                loadingElement.classList.remove('active');
            }
        }
    }
}

// Initialize global loading instance
if (typeof window !== 'undefined') {
    window.loading = new LoadingState();
}


