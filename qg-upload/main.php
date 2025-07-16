<?php
// main.php - Entry point untuk sistem Quality Gates (Refactored)

try {
    require_once 'bootstrap.php';

    // Check login status
    if (isLoggedIn()) {
        // User already logged in, redirect based on parameter or to dashboard
        $redirectTo = $_GET['page'] ?? 'dashboard';
        
        switch ($redirectTo) {
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
        // User not logged in, redirect to SSO login
        $redirectAfterLogin = $_GET['page'] ?? 'dashboard';
        header('Location: sso_login.php?redirect=' . urlencode($redirectAfterLogin));
    }
} catch (Exception $e) {
    error_log('main.php Error: ' . $e->getMessage());
    echo "<!DOCTYPE html><html><head><title>Error</title></head><body>";
    echo "<h1>🚨 Error</h1>";
    echo "<p>Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='sso_login.php'>🔑 Login</a></p>";
    echo "</body></html>";
    exit;
}
exit; 