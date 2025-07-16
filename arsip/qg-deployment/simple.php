<?php
// simple.php - Test basic PHP functionality
echo "✅ PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Test if files exist
$files = ['index.php', 'sso_config.php', 'vendor/autoload.php'];
echo "<br><strong>File check:</strong><br>";
foreach ($files as $file) {
    echo "$file: " . (file_exists($file) ? "✅ EXISTS" : "❌ NOT FOUND") . "<br>";
}

// Test basic autoload
echo "<br><strong>Autoload test:</strong><br>";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "✅ Autoload included<br>";
    
    if (class_exists('Irsadarief\\JkdSso\\Provider\\Keycloak')) {
        echo "✅ SSO Library found<br>";
    } else {
        echo "❌ SSO Library NOT found<br>";
    }
} else {
    echo "❌ Autoload file not found<br>";
}
?> 