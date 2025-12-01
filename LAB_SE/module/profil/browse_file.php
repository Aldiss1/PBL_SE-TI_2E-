<?php
// module/profil/browse_file.php

// Lokasi fisik folder konten: LAB_SE/assets/img/konten/
$konten_dir = __DIR__ . '/../../assets/img/konten/';
// URL root web untuk ditampilkan
$base_url_konten = '../../assets/img/konten/'; 

$files = [];

if (is_dir($konten_dir)) {
    $scan = scandir($konten_dir);
    if ($scan !== false) {
        foreach ($scan as $file) {
            // Filter hanya file gambar
            $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (is_file($konten_dir . $file) && in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $files[] = [
                    'name' => $file,
                    'url' => $base_url_konten . $file,
                ];
            }
        }
    }
}
?>

<div class="row row-cols-2 row-cols-md-4 g-3">
    <?php if (empty($files)): ?>
        <p class="text-center w-100 p-4 text-muted">Folder **assets/img/konten/** masih kosong.</p>
    <?php else: ?>
        <?php foreach ($files as $f): ?>
            <div class="col">
                <div class="card h-100 file-browser-item" data-url="<?php echo htmlspecialchars($f['url']); ?>">
                    <img src="<?php echo htmlspecialchars($f['url']); ?>" class="card-img-top p-2" alt="Preview" style="height: 120px; object-fit: contain; cursor: pointer;">
                    <div class="card-body p-2 border-top">
                        <p class="card-text mb-0 small text-truncate text-center"><?php echo htmlspecialchars($f['name']); ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    $('.file-browser-item').on('click', function() {
        const url = $(this).data('url');
        // Masukkan gambar ke Summernote
        $('#isi_konten_editor').summernote('insertImage', url, function ($image) {
            // Opsional: Tambahkan style setelah gambar dimasukkan
            $image.css('width', '50%'); 
        });
        
        // Tutup Modal
        $('#FileBrowserModal').modal('hide');
    });
});
</script>