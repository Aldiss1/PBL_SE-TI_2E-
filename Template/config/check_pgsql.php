<?php
/**
 * Helper untuk mengecek apakah extension PostgreSQL sudah diaktifkan
 * Include file ini di awal aplikasi untuk memastikan extension tersedia
 */

if (!function_exists('pg_connect')) {
    // Cek apakah extension pgsql ada di php.ini
    $extensions = get_loaded_extensions();
    if (!in_array('pgsql', $extensions)) {
        die("
        <html>
        <head>
            <title>Error - Extension PostgreSQL Tidak Ditemukan</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
                .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                h1 { color: #dc3545; margin-top: 0; }
                .code { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; margin: 20px 0; }
                .step { margin: 15px 0; padding-left: 20px; }
            </style>
        </head>
        <body>
            <div class='error-box'>
                <h1>❌ Extension PostgreSQL Tidak Ditemukan</h1>
                <p>Extension <strong>pgsql</strong> belum diaktifkan di PHP. Silakan ikuti langkah berikut:</p>
                
                <h3>Untuk Laragon:</h3>
                <div class='step'>
                    <p><strong>1.</strong> Buka file <code>php.ini</code> (biasanya di <code>C:\\laragon\\bin\\php\\php-[version]\\php.ini</code>)</p>
                    <p><strong>2.</strong> Cari baris <code>;extension=pgsql</code> dan hapus tanda <code>;</code> di depannya</p>
                    <p><strong>3.</strong> Ubah menjadi: <code>extension=pgsql</code></p>
                    <p><strong>4.</strong> Simpan file dan restart Laragon</p>
                </div>
                
                <div class='code'>
                    ;extension=pgsql  ← Hapus tanda ; di depan<br>
                    extension=pgsql  ← Seharusnya seperti ini
                </div>
                
                <h3>Alternatif (Manual):</h3>
                <div class='step'>
                    <p>Jika extension tidak ada, download dan install extension pgsql untuk PHP versi Anda.</p>
                </div>
                
                <p><strong>Setelah mengaktifkan extension, refresh halaman ini.</strong></p>
            </div>
        </body>
        </html>
        ");
    }
}
?>

