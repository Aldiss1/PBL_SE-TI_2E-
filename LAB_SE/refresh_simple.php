<?php
/**
 * Script Sederhana untuk Refresh Materialized Views
 * Cocok untuk pemula
 * 
 * Cara pakai:
 * 1. Via web: http://localhost/LAB_SE/refresh_simple.php
 * 2. Via command: php refresh_simple.php
 */

require_once __DIR__ . '/fungsi/materialized_views_simple.php';

// Cek apakah dijalankan via CLI atau web
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Refresh Materialized Views</title></head><body>";
    echo "<h1>Refresh Materialized Views</h1>";
    echo "<pre>";
}

echo "========================================\n";
echo "Refresh Materialized Views (Simple)\n";
echo "Waktu: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

if (refresh_views_simple()) {
    echo "✓ Berhasil refresh semua materialized views!\n\n";
    
    // Tampilkan statistik untuk verifikasi
    $stats = get_stats_simple();
    if ($stats) {
        echo "Statistik Terbaru:\n";
        echo "  - Total Anggota: " . $stats['total_anggota'] . "\n";
        echo "  - Total Pendaftar: " . $stats['total_pendaftar'] . "\n";
        echo "  - Pending: " . $stats['pendaftar_pending'] . "\n";
        echo "  - Accepted: " . $stats['pendaftar_accepted'] . "\n";
        echo "  - Rejected: " . $stats['pendaftar_rejected'] . "\n";
    }
} else {
    echo "❌ Error: Gagal refresh materialized views!\n";
    echo "Pastikan materialized views sudah dibuat di database.\n";
    echo "Jalankan: psql -d lab_se -f materialized_views.sql\n";
    if (!$is_cli) echo "</pre></body></html>";
    exit(1);
}

echo "\nSelesai pada: " . date('Y-m-d H:i:s') . "\n";

if (!$is_cli) {
    echo "</pre>";
    echo "<p><a href='javascript:location.reload()'>Refresh Lagi</a> | ";
    echo "<a href='module/admin/dashboard.php'>Kembali ke Dashboard</a></p>";
    echo "</body></html>";
}

?>

