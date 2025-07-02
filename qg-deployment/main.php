<?php
// main.php - Entry point untuk sistem Quality Gates dengan SSO BPS
require_once 'sso_integration.php';

// Cek status login SSO
if (isLoggedIn()) {
    // User sudah login SSO
    $user_data = getUserData();
    $wilayah_filter = getSSOWilayahFilter();
    
    // Redirect berdasarkan parameter atau ke dashboard secara default
    $redirect_to = $_GET['page'] ?? 'dashboard';
    
    switch ($redirect_to) {
        case 'monitoring':
            header('Location: monitoring.php');
            break;
        case 'profile':
            header('Location: profile.php');
            break;
        case 'dashboard':
        default:
            header('Location: index.php');
            break;
    }
} else {
    // User belum login, arahkan ke login SSO
    $redirect_after_login = $_GET['page'] ?? 'dashboard';
    header('Location: sso_login.php?redirect=' . urlencode($redirect_after_login));
}
exit;
?> 