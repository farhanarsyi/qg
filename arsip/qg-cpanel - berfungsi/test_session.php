<?php
// test_session.php - Test session untuk debugging SSO
session_start();

echo "<!DOCTYPE html><html><head><title>Test Session</title>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🧪 Test Session SSO</h1>";

echo "<h2>📊 Session Status</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . "\n";
echo "Session Started: " . (session_status() === PHP_SESSION_ACTIVE ? 'YES' : 'NO') . "\n";
echo "</pre>";

echo "<h2>🔐 SSO Session Data</h2>";
echo "<pre>";
$sso_fields = [
    'sso_logged_in',
    'sso_username', 
    'sso_nama',
    'sso_email',
    'sso_nip',
    'sso_jabatan',
    'sso_prov',
    'sso_kab',
    'sso_unit_kerja',
    'sso_login_time'
];

foreach ($sso_fields as $field) {
    $value = isset($_SESSION[$field]) ? $_SESSION[$field] : 'NOT SET';
    echo "$field: $value\n";
}
echo "</pre>";

echo "<h2>📝 All Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>⚙️ SSO Integration Functions</h2>";
echo "<pre>";

try {
    require_once 'sso_integration.php';
    
    echo "isLoggedIn(): " . (isLoggedIn() ? 'TRUE' : 'FALSE') . "\n";
    
    $userData = getUserData();
    echo "getUserData(): " . ($userData ? 'OK' : 'NULL') . "\n";
    if ($userData) {
        echo "User Name: " . ($userData['nama'] ?? 'N/A') . "\n";
        echo "Username: " . ($userData['username'] ?? 'N/A') . "\n";
    }
    
    $wilayahFilter = getSSOWilayahFilter();
    echo "getSSOWilayahFilter(): " . ($wilayahFilter ? 'OK' : 'NULL') . "\n";
    if ($wilayahFilter) {
        echo "Provinsi: " . ($wilayahFilter['prov'] ?? 'N/A') . "\n";
        echo "Kabupaten: " . ($wilayahFilter['kab'] ?? 'N/A') . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";

echo "<h2>🔗 Actions</h2>";
echo "<p>";
echo "<a href='sso_login.php'>🔑 SSO Login</a> | ";
echo "<a href='sso_logout.php'>🚪 Logout</a> | ";
echo "<a href='index.php'>🏠 Dashboard</a> | ";
echo "<a href='debug.php?show=debug'>🐛 Full Debug</a>";
echo "</p>";

echo "</body></html>";
?> 