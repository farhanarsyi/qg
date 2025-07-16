<?php
// debug.php - Halaman debug untuk troubleshooting production
require_once 'debug_helper.php';

// Hanya tampilkan debug jika ada parameter debug
if (isset($_GET['show']) && $_GET['show'] === 'debug') {
    fullDebug();
} else {
    // Halaman simple untuk akses debug
    echo "<!DOCTYPE html><html><head><title>Debug Access</title></head><body>";
    echo "<h1>🔧 Debug Tools</h1>";
    echo "<p><a href='?show=debug'>🐛 Show Full Debug Info</a></p>";
    echo "<p><a href='index.php'>← Back to Dashboard</a></p>";
    echo "</body></html>";
}
?> 