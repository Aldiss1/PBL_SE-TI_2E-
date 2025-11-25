/**
 * ImagePreview - Kelas untuk preview gambar sebelum upload
 */
class ImagePreview {
    constructor(config = {}) {
        this.inputId = config.inputId || 'foto';
        this.previewContainerId = config.previewContainerId || 'previewFoto';
        this.previewImgId = config.previewImgId || 'previewImg';
        this.allowedTypes = config.allowedTypes || ['image/jpeg', 'image/png', 'image/webp'];
        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        const input = document.getElementById(this.inputId);
        if (!input) {
            console.warn('ImagePreview: Input element not found with id:', this.inputId);
            return;
        }

        input.addEventListener('change', (e) => this.handleFileChange(e));
    }

    handleFileChange(event) {
        const file = event.target.files && event.target.files[0];
        const previewContainer = document.getElementById(this.previewContainerId);
        const previewImg = document.getElementById(this.previewImgId);

        if (!file) {
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
            return;
        }

        // Check file type
        if (this.allowedTypes.indexOf(file.type) === -1) {
            alert('Format file tidak didukung. Gunakan JPEG, PNG, atau WebP.');
            if (previewContainer) {
                previewContainer.style.display = 'none';
            }
            return;
        }

        // Read file and show preview
        const reader = new FileReader();
        reader.onload = (e) => {
            if (previewImg) {
                previewImg.src = e.target.result;
            }
            if (previewContainer) {
                previewContainer.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }
}

