<?php
// main.php - Entry point untuk sistem Quality Gates dengan SSO BPS

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'sso_integration.php';

    // Log untuk debugging
    error_log('main.php accessed - checking login status');
    
    // Cek status login SSO
    if (isLoggedIn()) {
        // User sudah login SSO
        error_log('User is logged in - getting user data');
        $user_data = getUserData();
        $wilayah_filter = getSSOWilayahFilter();
        
        // Log user data untuk debug
        error_log('User data retrieved: ' . ($user_data ? 'OK' : 'NULL'));
        
        // Redirect berdasarkan parameter atau ke dashboard secara default
        $redirect_to = $_GET['page'] ?? 'dashboard';
        error_log('Redirecting to: ' . $redirect_to);
        
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
        error_log('User not logged in - redirecting to SSO login');
        $redirect_after_login = $_GET['page'] ?? 'dashboard';
        header('Location: sso_login.php?redirect=' . urlencode($redirect_after_login));
    }
} catch (Exception $e) {
    // Jika terjadi error, tampilkan pesan error
    error_log('main.php Error: ' . $e->getMessage());
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>🚨 Error</h1>";
    echo "<p>Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='debug.php'>🐛 Debug Info</a> | <a href='sso_login.php'>🔑 Login</a></p>";
    echo "</body></html>";
    exit;
}
exit;
?> 