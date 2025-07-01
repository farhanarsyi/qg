<?php
session_start();

// Simpan logout URL sebelum menghapus session
$logout_url = null;
if (isset($_SESSION['logout_url'])) {
    $logout_url = $_SESSION['logout_url'];
}

// Hapus semua data session
session_unset();
session_destroy();

// Jika ada logout URL dari SSO, redirect ke sana untuk logout SSO juga
if ($logout_url) {
    // Tambahkan redirect URL setelah logout dari SSO
    $redirect_after_logout = 'https://dashboardqg.web.bps.go.id/login.php';
    $full_logout_url = $logout_url . '?redirect_uri=' . urlencode($redirect_after_logout);
    header('Location: ' . $full_logout_url);
} else {
    // Jika tidak ada logout URL SSO, redirect ke login lokal
    header('Location: login.php?message=logged_out');
}
exit;
?> 