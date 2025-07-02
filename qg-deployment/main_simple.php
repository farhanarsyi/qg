<?php
// main_simple.php - Simplified version for troubleshooting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "✅ Main simple is working<br>";

// Test session start
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "✅ Session started<br>";
}

// Check if logged in (simple check)
if (isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in']) {
    echo "✅ User is logged in<br>";
    echo "Redirecting to dashboard...<br>";
    echo "<script>setTimeout(function(){ window.location.href = 'index.php'; }, 2000);</script>";
} else {
    echo "ℹ️ User not logged in<br>";
    echo "Redirecting to login...<br>";
    echo "<script>setTimeout(function(){ window.location.href = 'sso_login.php'; }, 2000);</script>";
}
?> 