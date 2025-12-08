<?php
/**
 * Script untuk Setup Stored Procedures Lab SE
 * File ini akan menjalankan semua stored procedure dari stored_procedures.sql
 * 
 * CARA MENGGUNAKAN:
 * 1. Pastikan file stored_procedures.sql ada di folder LAB_SE/
 * 2. Buka browser dan akses: http://localhost/PBL_SE-TI_2E-/LAB_SE/setup_stored_procedures.php
 * 3. Tunggu hingga proses selesai
 * 4. HAPUS file ini setelah selesai untuk keamanan!
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/koneksi.php';

if (!$koneksi) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Setup Stored Procedures - Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: #fff; padding: 30px; border-radius: 8px; border-left: 4px solid #dc3545; max-width: 800px; margin: 0 auto; }
            h1 { color: #dc3545; margin-top: 0; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>❌ Koneksi Database Gagal!</h1>
            <p>Silakan periksa konfigurasi database di <code>config/koneksi.php</code></p>
        </div>
    </body>
    </html>
    ");
}

$sql_file = __DIR__ . '/stored_procedures.sql';
if (!file_exists($sql_file)) {
    die("
    <!DOCTYPE html>
    <html>
    <head>
        <title>Setup Stored Procedures - Error</title>
        <style>
            body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
            .error-box { background: #fff; padding: 30px; border-radius: 8px; border-left: 4px solid #dc3545; max-width: 800px; margin: 0 auto; }
            h1 { color: #dc3545; margin-top: 0; }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h1>❌ File stored_procedures.sql Tidak Ditemukan!</h1>
            <p>Pastikan file <code>stored_procedures.sql</code> ada di folder <code>LAB_SE/</code></p>
        </div>
    </body>
    </html>
    ");
}

$sql_content = file_get_contents($sql_file);
if ($sql_content === false) {
    die("Gagal membaca file stored_procedures.sql");
}

// Hapus CREATE DATABASE statement jika ada
$sql_content = preg_replace('/CREATE\s+DATABASE\s+[^;]+;/i', '', $sql_content);

// Hitung jumlah function yang akan dibuat
preg_match_all('/CREATE\s+OR\s+REPLACE\s+FUNCTION/i', $sql_content, $matches);
$total_functions = count($matches[0]);

// Function untuk log
function logMessage($message, $type = 'info') {
    $colors = [
        'success' => 'log-success',
        'error' => 'log-error',
        'info' => 'log-info',
        'warning' => 'log-warning'
    ];
    $class = $colors[$type] ?? 'log-info';
    echo "<div class='$class'>" . htmlspecialchars($message) . "</div>\n";
    flush();
    ob_flush();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup Stored Procedures Lab SE</title>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }
        .stat-card .label {
            color: #6c757d;
            font-size: 14px;
            margin-top: 5px;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 12px;
        }
        .log-container {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-success { color: #4ec9b0; }
        .log-error { color: #f48771; }
        .log-info { color: #569cd6; }
        .log-warning { color: #dcdcaa; }
        .summary {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Setup Stored Procedures Lab SE</h1>
            <p>Membuat stored procedures untuk operasi database</p>
        </div>
        <div class="content">
            <div class="stats">
                <div class="stat-card">
                    <div class="number" id="total-functions"><?php echo $total_functions; ?></div>
                    <div class="label">Total Functions</div>
                </div>
                <div class="stat-card">
                    <div class="number" id="success-count">0</div>
                    <div class="label">Berhasil</div>
                </div>
                <div class="stat-card">
                    <div class="number" id="error-count">0</div>
                    <div class="label">Error</div>
                </div>
            </div>
            
            <div class="progress-bar">
                <div class="progress-fill" id="progress" style="width: 0%">0%</div>
            </div>
            
            <h3 style="margin-bottom: 15px;">Log Eksekusi:</h3>
            <div class="log-container" id="log-container">
                <div class="log-info">Memulai setup stored procedures...</div>
            </div>
            
            <div class="summary" id="summary" style="display: none;">
                <h3>📊 Ringkasan</h3>
                <div id="summary-content"></div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$success_count = 0;
$error_count = 0;
$errors = [];

logMessage("📋 Ditemukan $total_functions stored procedure untuk dibuat", 'info');

// Drop function sp_get_statistik_dashboard jika ada (untuk menghindari error return type)
logMessage("Menghapus function sp_get_statistik_dashboard yang sudah ada (jika ada)...", 'info');
@pg_query($koneksi, "DROP FUNCTION IF EXISTS sp_get_statistik_dashboard() CASCADE");

// Coba eksekusi langsung seluruh file SQL dulu
logMessage("Mencoba menjalankan seluruh file SQL sekaligus...", 'info');
$result = @pg_query($koneksi, $sql_content);

if ($result) {
    $success_count = $total_functions;
    $error_count = 0;
    logMessage("✅ Semua stored procedure berhasil dibuat!", 'success');
    echo "<script>
        document.getElementById('progress').style.width = '100%';
        document.getElementById('progress').textContent = '100%';
        document.getElementById('success-count').textContent = '$success_count';
        document.getElementById('error-count').textContent = '$error_count';
    </script>\n";
} else {
    // Jika gagal, coba parse dan jalankan satu per satu
    logMessage("Eksekusi sekaligus gagal, mencoba satu per satu...", 'warning');
    
    // Extract functions - gunakan pendekatan manual untuk handle dollar-quoted strings
    $functions = [];
    $parts = preg_split('/(?=CREATE\s+OR\s+REPLACE\s+FUNCTION)/i', $sql_content);
    
    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part) || !preg_match('/^CREATE\s+OR\s+REPLACE\s+FUNCTION/i', $part)) {
            continue;
        }
        
        // Cari akhir function: $$ LANGUAGE plpgsql;
        // Gunakan strpos untuk mencari pattern yang lebih reliable
        $lang_pos = stripos($part, 'LANGUAGE plpgsql;');
        if ($lang_pos !== false) {
            // Pastikan ada $$ sebelum LANGUAGE
            $before_lang = substr($part, 0, $lang_pos);
            if (strpos($before_lang, '$$') !== false) {
                $functions[] = trim(substr($part, 0, $lang_pos + strlen('LANGUAGE plpgsql;')));
            }
        }
    }
    
    // Jika masih kosong, coba dengan regex yang lebih sederhana
    if (empty($functions)) {
        // Pattern: CREATE ... FUNCTION ... $$ ... $$ LANGUAGE plpgsql;
        // Gunakan pattern yang mencari dari CREATE sampai LANGUAGE plpgsql;
        $pattern = '/CREATE\s+OR\s+REPLACE\s+FUNCTION.*?LANGUAGE\s+plpgsql\s*;/is';
        preg_match_all($pattern, $sql_content, $matches);
        if (!empty($matches[0])) {
            $functions = array_map('trim', $matches[0]);
        }
    }
    
    logMessage("Ditemukan " . count($functions) . " function untuk dieksekusi satu per satu", 'info');
    
    foreach ($functions as $index => $function) {
        $func_num = $index + 1;
        $func_trimmed = trim($function);
        
        if (empty($func_trimmed) || strlen($func_trimmed) < 20) {
            continue;
        }
        
        // Extract function name
        preg_match('/FUNCTION\s+(\w+)\s*\(/i', $func_trimmed, $name_match);
        $func_name = $name_match[1] ?? 'unknown';
        
        // Special handling untuk sp_get_statistik_dashboard - drop dulu jika ada
        if ($func_name === 'sp_get_statistik_dashboard') {
            @pg_query($koneksi, "DROP FUNCTION IF EXISTS sp_get_statistik_dashboard() CASCADE");
        }
        
        $result = @pg_query($koneksi, $func_trimmed);
        
        if ($result) {
            $success_count++;
            logMessage("✅ [$func_num] Berhasil: $func_name", 'success');
        } else {
            $error_count++;
            $error_msg = pg_last_error($koneksi);
            
            $skip_errors = [
                'already exists',
                'does not exist',
                'duplicate key'
            ];
            
            $should_skip = false;
            foreach ($skip_errors as $skip_error) {
                if (stripos($error_msg, $skip_error) !== false) {
                    $should_skip = true;
                    $success_count++;
                    logMessage("⚠️  [$func_num] Sudah ada (skip): $func_name", 'warning');
                    break;
                }
            }
            
            if (!$should_skip) {
                logMessage("❌ [$func_num] Error: $func_name", 'error');
                logMessage("   Pesan: " . $error_msg, 'error');
                $errors[] = [
                    'function' => $func_name,
                    'error' => $error_msg,
                    'number' => $func_num
                ];
            }
        }
        
        $total = count($functions);
        $progress = $total > 0 ? (($index + 1) / $total) * 100 : 0;
        echo "<script>
            document.getElementById('progress').style.width = '$progress%';
            document.getElementById('progress').textContent = Math.round($progress) + '%';
            document.getElementById('success-count').textContent = '$success_count';
            document.getElementById('error-count').textContent = '$error_count';
        </script>\n";
        flush();
        ob_flush();
    }
}

// Summary
logMessage("\n" . str_repeat("=", 60), 'info');
logMessage("✨ Setup Stored Procedures Selesai!", 'success');
logMessage("Total Function: " . ($total_functions > 0 ? $total_functions : count($functions)), 'info');
logMessage("Berhasil: $success_count", 'success');
logMessage("Error: $error_count", $error_count > 0 ? 'error' : 'info');

if (count($errors) > 0) {
    logMessage("\n📋 Detail Error:", 'error');
    foreach ($errors as $error) {
        logMessage("Function #{$error['number']}: {$error['function']}", 'error');
        logMessage("   → {$error['error']}", 'error');
    }
}

echo "<script>
    document.getElementById('summary').style.display = 'block';
    document.getElementById('summary-content').innerHTML = `
        <div style='display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;'>
            <span>Total Function:</span>
            <strong>" . ($total_functions > 0 ? $total_functions : count($functions)) . "</strong>
        </div>
        <div style='display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #dee2e6;'>
            <span>Berhasil:</span>
            <strong style='color: #28a745;'>$success_count</strong>
        </div>
        <div style='display: flex; justify-content: space-between; padding: 8px 0;'>
            <span>Error:</span>
            <strong style='color: #dc3545;'>$error_count</strong>
        </div>
    `;
</script>\n";

if ($error_count === 0) {
    logMessage("\n🎉 Semua stored procedure berhasil dibuat! Database siap digunakan.", 'success');
} else {
    logMessage("\n⚠️  Ada beberapa error, tetapi sebagian besar stored procedure berhasil dibuat.", 'warning');
}

logMessage("\n💡 PENTING: Hapus file setup_stored_procedures.php setelah selesai untuk keamanan!", 'warning');
?>
