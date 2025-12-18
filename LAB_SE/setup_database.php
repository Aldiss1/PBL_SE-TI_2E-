<?php
/**
 * ============================================
 * SETUP DATABASE LAB SE
 * ============================================
 * Script ini akan membuat database dan tabel
 * yang diperlukan untuk aplikasi Lab SE
 * ============================================
 */

// Konfigurasi Database
$config = [
    'host' => 'localhost',
    'port' => '5432',
    'username' => 'postgres',
    'password' => '123456',
    'database' => 'lab_se'
];

// Cek apakah extension PostgreSQL sudah terinstall
if (!extension_loaded('pgsql')) {
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
            .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 20px; }
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
            
            <p><strong>Setelah mengaktifkan extension, refresh halaman ini.</strong></p>
        </div>
    </body>
    </html>
    ");
}

// Fungsi untuk menampilkan pesan
function showMessage($message, $type = 'info') {
    $colors = [
        'success' => '#28a745',
        'error' => '#dc3545',
        'warning' => '#ffc107',
        'info' => '#17a2b8'
    ];
    $color = $colors[$type] ?? $colors['info'];
    echo "<div style='padding: 15px; margin: 10px 0; background: " . $color . "; color: white; border-radius: 5px;'>";
    echo htmlspecialchars($message);
    echo "</div>";
}

// Fungsi untuk menjalankan query
function runQuery($connection, $query, $description = '') {
    if ($description) {
        echo "<p style='color: #666;'>⏳ $description...</p>";
    }
    
    $result = @pg_query($connection, $query);
    if (!$result) {
        $error = pg_last_error($connection);
        showMessage("❌ Error: $error", 'error');
        if ($description) {
            showMessage("Gagal: $description", 'error');
        }
        return false;
    }
    
    if ($description) {
        showMessage("✅ $description berhasil!", 'success');
    }
    return $result;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database - Lab SE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #2F80ED 0%, #1E5FCC 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 30px;
        }
        .config-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #2F80ED;
        }
        .config-box h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            font-weight: 600;
            color: #495057;
        }
        .config-value {
            color: #6c757d;
            font-family: monospace;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #27AE60 0%, #229954 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            margin: 10px 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        }
        .btn-secondary:hover {
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        .status-box {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .step-list {
            list-style: none;
            padding: 0;
        }
        .step-list li {
            padding: 12px;
            margin: 8px 0;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #2F80ED;
        }
        .step-list li strong {
            color: #2F80ED;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            color: #e83e8c;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Setup Database Lab SE</h1>
            <p>Instalasi dan konfigurasi database PostgreSQL</p>
        </div>
        
        <div class="content">
            <div class="config-box">
                <h3>📋 Konfigurasi Database</h3>
                <div class="config-item">
                    <span class="config-label">Host:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['host']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Port:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['port']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Username:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['username']); ?></span>
                </div>
                <div class="config-item">
                    <span class="config-label">Database:</span>
                    <span class="config-value"><?php echo htmlspecialchars($config['database']); ?></span>
                </div>
            </div>

            <?php
            // Cek apakah form sudah disubmit
            if (isset($_POST['setup_database'])) {
                echo "<div class='status-box'>";
                echo "<h3 style='color: #2c3e50; margin-bottom: 20px;'>🚀 Memulai Setup Database...</h3>";
                
                // Koneksi ke PostgreSQL (tanpa database)
                $connection_string = "host={$config['host']} port={$config['port']} user={$config['username']} password={$config['password']}";
                $conn = @pg_connect($connection_string);
                
                if (!$conn) {
                    showMessage("❌ Gagal terhubung ke PostgreSQL server. Periksa konfigurasi di atas.", 'error');
                    echo "<p style='margin-top: 20px;'><strong>Kemungkinan masalah:</strong></p>";
                    echo "<ul style='margin-left: 20px; margin-top: 10px;'>";
                    echo "<li>PostgreSQL service belum berjalan</li>";
                    echo "<li>Username atau password salah</li>";
                    echo "<li>Port tidak sesuai</li>";
                    echo "<li>PostgreSQL belum terinstall</li>";
                    echo "</ul>";
                } else {
                    showMessage("✅ Berhasil terhubung ke PostgreSQL server!", 'success');
                    
                    // Cek apakah database sudah ada
                    $check_db = "SELECT 1 FROM pg_database WHERE datname = '{$config['database']}'";
                    $result_check = pg_query($conn, $check_db);
                    
                    if (pg_num_rows($result_check) > 0) {
                        showMessage("⚠️ Database '{$config['database']}' sudah ada. Akan menggunakan database yang sudah ada.", 'warning');
                    } else {
                        // Buat database
                        $create_db = "CREATE DATABASE {$config['database']} WITH ENCODING = 'UTF8'";
                        if (runQuery($conn, $create_db, "Membuat database '{$config['database']}'")) {
                            showMessage("✅ Database '{$config['database']}' berhasil dibuat!", 'success');
                        }
                    }
                    
                    // Tutup koneksi tanpa database
                    pg_close($conn);
                    
                    // Koneksi ke database yang baru dibuat
                    $connection_string = "host={$config['host']} port={$config['port']} dbname={$config['database']} user={$config['username']} password={$config['password']}";
                    $conn = @pg_connect($connection_string);
                    
                    if (!$conn) {
                        showMessage("❌ Gagal terhubung ke database '{$config['database']}'.", 'error');
                    } else {
                        showMessage("✅ Berhasil terhubung ke database '{$config['database']}'!", 'success');
                        
                        // Baca file SQL
                        $sql_file = __DIR__ . '/database.sql';
                        if (!file_exists($sql_file)) {
                            showMessage("❌ File database.sql tidak ditemukan di: " . $sql_file, 'error');
                        } else {
                            $sql_content = file_get_contents($sql_file);
                            
                            // Hapus perintah CREATE DATABASE jika ada (karena sudah dibuat)
                            $sql_content = preg_replace('/CREATE\s+DATABASE\s+[^;]+;/i', '', $sql_content);
                            
                            // Split SQL menjadi array per statement
                            $statements = array_filter(
                                array_map('trim', explode(';', $sql_content)),
                                function($stmt) {
                                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                                }
                            );
                            
                            $success_count = 0;
                            $error_count = 0;
                            
                            foreach ($statements as $statement) {
                                if (empty(trim($statement))) continue;
                                
                                // Skip komentar
                                if (preg_match('/^--/', $statement)) continue;
                                
                                $result = @pg_query($conn, $statement);
                                if ($result) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                    $error = pg_last_error($conn);
                                    // Hanya tampilkan error yang penting (bukan "already exists")
                                    if (strpos($error, 'already exists') === false && 
                                        strpos($error, 'does not exist') === false) {
                                        echo "<p style='color: #dc3545; font-size: 12px;'>⚠️ " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                                    }
                                }
                            }
                            
                            if ($error_count == 0 || ($success_count > 0 && $error_count < 5)) {
                                showMessage("✅ Setup database selesai! ($success_count statement berhasil)", 'success');
                                
                                // Set encoding
                                pg_set_client_encoding($conn, "UTF8");
                                
                                // Cek apakah tabel sudah dibuat
                                $check_tables = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'";
                                $result_tables = pg_query($conn, $check_tables);
                                $table_count = pg_num_rows($result_tables);
                                
                                if ($table_count > 0) {
                                    showMessage("✅ Ditemukan $table_count tabel di database.", 'success');
                                }
                                
                                echo "<div style='margin-top: 30px; padding: 20px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px;'>";
                                echo "<h3 style='color: #155724; margin-bottom: 15px;'>🎉 Setup Berhasil!</h3>";
                                echo "<p style='color: #155724; margin-bottom: 10px;'>Database sudah siap digunakan. Anda dapat:</p>";
                                echo "<ul style='margin-left: 20px; color: #155724;'>";
                                echo "<li>Mengakses halaman admin di: <code>module/auth/login.php</code></li>";
                                echo "<li>Username default: <code>admin</code></li>";
                                echo "<li>Password default: <code>admin</code> (disarankan untuk diubah)</li>";
                                echo "</ul>";
                                echo "</div>";
                                
                                echo "<div style='margin-top: 20px;'>";
                                echo "<a href='module/auth/login.php' class='btn'>🔐 Masuk ke Admin Panel</a>";
                                echo "<a href='index.php' class='btn btn-secondary'>🏠 Lihat Landing Page</a>";
                                echo "</div>";
                            } else {
                                showMessage("⚠️ Setup selesai dengan beberapa error. ($success_count berhasil, $error_count error)", 'warning');
                            }
                        }
                        
                        pg_close($conn);
                    }
                }
                
                echo "</div>";
            } else {
                // Tampilkan form setup
                ?>
                <div class="status-box">
                    <h3 style="color: #2c3e50; margin-bottom: 20px;">📝 Instruksi Setup</h3>
                    <ol class="step-list">
                        <li>
                            <strong>Pastikan PostgreSQL sudah terinstall dan berjalan</strong><br>
                            <small>Untuk Windows: Cek di Services atau jalankan <code>pg_ctl start</code></small>
                        </li>
                        <li>
                            <strong>Pastikan extension pgsql sudah aktif di PHP</strong><br>
                            <small>Buka <code>php.ini</code> dan pastikan <code>extension=pgsql</code> tidak di-comment</small>
                        </li>
                        <li>
                            <strong>Periksa konfigurasi database di atas</strong><br>
                            <small>Jika berbeda, edit file <code>config/koneksi.php</code> dan <code>setup_database.php</code></small>
                        </li>
                        <li>
                            <strong>Klik tombol "Setup Database" di bawah</strong><br>
                            <small>Script akan membuat database dan semua tabel yang diperlukan</small>
                        </li>
                    </ol>
                </div>

                <form method="POST" action="">
                    <button type="submit" name="setup_database" class="btn" style="width: 100%; margin-top: 20px;">
                        🚀 Setup Database Sekarang
                    </button>
                </form>

                <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px;">
                    <strong>⚠️ Peringatan:</strong>
                    <p style="margin-top: 10px; color: #856404;">
                        Script ini akan membuat database dan tabel. Jika database sudah ada, 
                        tabel yang sudah ada tidak akan dihapus, tetapi akan dibuat ulang jika belum ada.
                    </p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>


