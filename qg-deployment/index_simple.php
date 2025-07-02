<?php
// index_simple.php - Simplified dashboard for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Quality Gates Dashboard - Simple</title>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "</head><body>";

echo "<h1>🔧 Quality Gates Dashboard - Debug Mode</h1>";
echo "<hr>";

// Test basic functionality
echo "<h2>System Status</h2>";
echo "✅ PHP Version: " . phpversion() . "<br>";
echo "✅ Current Time: " . date('Y-m-d H:i:s') . "<br>";

// Test session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Session Status</h2>";
if (isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in']) {
    echo "✅ <strong>Logged in as:</strong> " . ($_SESSION['sso_nama'] ?? 'Unknown') . "<br>";
    echo "📧 <strong>Email:</strong> " . ($_SESSION['sso_email'] ?? 'N/A') . "<br>";
    echo "🏢 <strong>Unit Kerja:</strong> " . ($_SESSION['sso_unit_kerja']['nama_level'] ?? 'N/A') . "<br>";
    
    echo "<br><strong>Available Actions:</strong><br>";
    echo "<a href='profile.php' style='margin-right:10px;'>📋 Profile</a>";
    echo "<a href='monitoring.php' style='margin-right:10px;'>📊 Monitoring</a>";
    echo "<a href='sso_logout.php'>🚪 Logout</a>";
    
} else {
    echo "❌ <strong>Not logged in</strong><br>";
    echo "<a href='sso_login.php'>🔐 Login with SSO BPS</a>";
}

echo "<hr>";
echo "<h2>Debug Tools</h2>";
echo "<a href='debug_production.php' style='margin-right:10px;'>🔍 Full Debug</a>";
echo "<a href='simple.php' style='margin-right:10px;'>🧪 Simple Test</a>";
echo "<a href='main_simple.php'>🔄 Simple Main</a>";

echo "</body></html>";
?> 