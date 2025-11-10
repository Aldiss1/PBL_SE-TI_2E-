<?php
// Fungsi untuk menampilkan pesan kilat (flash message)
function pesan_kilat($pesan, $tipe = 'success') {
    $_SESSION['pesan'] = $pesan;
    $_SESSION['tipe_pesan'] = $tipe;
}

function tampilkan_pesan() {
    if (isset($_SESSION['pesan'])) {
        $pesan = $_SESSION['pesan'];
        $tipe = isset($_SESSION['tipe_pesan']) ? $_SESSION['tipe_pesan'] : 'success';
        
        $alert_class = '';
        switch ($tipe) {
            case 'success':
                $alert_class = 'alert-success';
                break;
            case 'error':
            case 'danger':
                $alert_class = 'alert-danger';
                break;
            case 'warning':
                $alert_class = 'alert-warning';
                break;
            case 'info':
                $alert_class = 'alert-info';
                break;
        }
        
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($pesan);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        unset($_SESSION['pesan']);
        unset($_SESSION['tipe_pesan']);
    }
}
?>

