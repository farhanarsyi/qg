<?php
// debug_helper.php - Helper untuk debugging di production

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function untuk debug session
function debugSession() {
    echo "<h3>🔍 Debug Session Data</h3>";
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Status: " . session_status() . "\n";
    echo "Session Data:\n";
    print_r($_SESSION);
    echo "</pre>";
}

// Function untuk debug SSO status
function debugSSO() {
    echo "<h3>🔐 Debug SSO Status</h3>";
    echo "<pre>";
    echo "SSO Logged In: " . (isset($_SESSION['sso_logged_in']) ? ($_SESSION['sso_logged_in'] ? 'YES' : 'NO') : 'NOT SET') . "\n";
    echo "SSO Username: " . (isset($_SESSION['sso_username']) ? $_SESSION['sso_username'] : 'NOT SET') . "\n";
    echo "SSO Nama: " . (isset($_SESSION['sso_nama']) ? $_SESSION['sso_nama'] : 'NOT SET') . "\n";
    echo "SSO Prov: " . (isset($_SESSION['sso_prov']) ? $_SESSION['sso_prov'] : 'NOT SET') . "\n";
    echo "SSO Kab: " . (isset($_SESSION['sso_kab']) ? $_SESSION['sso_kab'] : 'NOT SET') . "\n";
    echo "SSO Unit Kerja: " . (isset($_SESSION['sso_unit_kerja']) ? $_SESSION['sso_unit_kerja'] : 'NOT SET') . "\n";
    echo "</pre>";
}

// Function untuk debug environment
function debugEnvironment() {
    echo "<h3>🌐 Debug Environment</h3>";
    echo "<pre>";
    echo "PHP Version: " . PHP_VERSION . "\n";
    echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
    echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";
    echo "HTTPS: " . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'NOT SET') . "\n";
    echo "</pre>";
}

// Function untuk debug SSO config
function debugSSOConfig() {
    echo "<h3>⚙️ Debug SSO Config</h3>";
    echo "<pre>";
    echo "SSO_AUTH_SERVER_URL: " . (defined('SSO_AUTH_SERVER_URL') ? SSO_AUTH_SERVER_URL : 'NOT DEFINED') . "\n";
    echo "SSO_REALM: " . (defined('SSO_REALM') ? SSO_REALM : 'NOT DEFINED') . "\n";
    echo "SSO_CLIENT_ID: " . (defined('SSO_CLIENT_ID') ? SSO_CLIENT_ID : 'NOT DEFINED') . "\n";
    echo "SSO_REDIRECT_URI: " . (defined('SSO_REDIRECT_URI') ? SSO_REDIRECT_URI : 'NOT DEFINED') . "\n";
    echo "</pre>";
}

// Main debug function
function fullDebug() {
    session_start();
    echo "<!DOCTYPE html><html><head><title>Debug - Quality Gates</title>";
    echo "<style>body{font-family:monospace;margin:20px;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";
    echo "</head><body>";
    echo "<h1>🐛 Debug Quality Gates Dashboard</h1>";
    
    debugEnvironment();
    debugSSOConfig();
    debugSSO();
    debugSession();
    
    echo "<h3>📄 Quick Actions</h3>";
    echo "<p><a href='sso_login.php'>🔑 Test SSO Login</a> | ";
    echo "<a href='index.php'>🏠 Test Dashboard</a> | ";
    echo "<a href='sso_logout.php'>🚪 Logout</a></p>";
    
    echo "</body></html>";
}
?> 