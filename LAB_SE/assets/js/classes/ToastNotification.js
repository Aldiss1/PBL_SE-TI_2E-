/**
 * ToastNotification - Kelas untuk menampilkan toast notifications
 */
class ToastNotification {
    constructor() {
        this.container = null;
        this.toasts = [];
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        if (!document.getElementById('toastContainer')) {
            this.container = document.createElement('div');
            this.container.id = 'toastContainer';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('toastContainer');
        }
    }

    show(message, type = 'info', title = null, duration = 4000) {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const icon = this.getIcon(type);
        const toastTitle = title || this.getTitle(type);
        
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${toastTitle}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        this.container.appendChild(toast);
        this.toasts.push(toast);
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }
        
        return toast;
    }

    success(message, title = null, duration = 4000) {
        return this.show(message, 'success', title, duration);
    }

    error(message, title = null, duration = 5000) {
        return this.show(message, 'error', title, duration);
    }

    warning(message, title = null, duration = 4000) {
        return this.show(message, 'warning', title, duration);
    }

    info(message, title = null, duration = 4000) {
        return this.show(message, 'info', title, duration);
    }

    remove(toast) {
        if (toast && toast.parentElement) {
            toast.classList.add('fade-out');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
                const index = this.toasts.indexOf(toast);
                if (index > -1) {
                    this.toasts.splice(index, 1);
                }
            }, 300);
        }
    }

    getIcon(type) {
        const icons = {
            success: '<i class="lni lni-checkmark-circle"></i>',
            error: '<i class="lni lni-close"></i>',
            warning: '<i class="lni lni-warning"></i>',
            info: '<i class="lni lni-information"></i>'
        };
        return icons[type] || icons.info;
    }

    getTitle(type) {
        const titles = {
            success: 'Berhasil',
            error: 'Error',
            warning: 'Peringatan',
            info: 'Informasi'
        };
        return titles[type] || titles.info;
    }
}

// Initialize global toast instance
if (typeof window !== 'undefined') {
    window.toast = new ToastNotification();
}


