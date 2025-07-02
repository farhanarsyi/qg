<?php
// debug_production.php - Debug Production Issues
// Upload file ini ke production server untuk troubleshooting

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>🔍 Production Debug Tool</h1>";
echo "<hr>";

// 1. Check PHP version and extensions
echo "<h2>📋 PHP Environment</h2>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "<strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "<strong>Current Directory:</strong> " . getcwd() . "<br>";
echo "<hr>";

// 2. Check required PHP extensions
echo "<h2>🔧 PHP Extensions</h2>";
$required_extensions = ['curl', 'json', 'openssl', 'mbstring', 'session'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<strong>$ext:</strong> $status<br>";
}
echo "<hr>";

// 3. Check file permissions and existence
echo "<h2>📁 File System Check</h2>";
$critical_files = [
    'index.php',
    'sso_config.php', 
    'sso_login.php',
    'sso_callback.php',
    'profile.php',
    'vendor/autoload.php',
    'vendor/irsadarief/jkd-sso'
];

foreach ($critical_files as $file) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -4);
        echo "<strong>$file:</strong> ✅ (permissions: $perms)<br>";
    } else {
        echo "<strong>$file:</strong> ❌ <span style='color:red'>NOT FOUND</span><br>";
    }
}
echo "<hr>";

// 4. Test autoload
echo "<h2>📦 Composer Autoload Test</h2>";
try {
    require_once 'vendor/autoload.php';
    echo "✅ Composer autoload: <strong>OK</strong><br>";
    
    // Test SSO library specifically
    if (class_exists('Irsadarief\\JkdSso\\Provider\\Keycloak')) {
        echo "✅ JKD SSO Library: <strong>LOADED</strong><br>";
    } else {
        echo "❌ JKD SSO Library: <strong>NOT FOUND</strong><br>";
    }
    
} catch (Exception $e) {
    echo "❌ Composer autoload: <strong>ERROR</strong><br>";
    echo "<span style='color:red'>Error: " . $e->getMessage() . "</span><br>";
}
echo "<hr>";

// 5. Test session functionality
echo "<h2>🔐 Session Test</h2>";
try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['test'] = 'working';
    
    if (isset($_SESSION['test']) && $_SESSION['test'] === 'working') {
        echo "✅ PHP Sessions: <strong>WORKING</strong><br>";
        unset($_SESSION['test']);
    } else {
        echo "❌ PHP Sessions: <strong>NOT WORKING</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ PHP Sessions: <strong>ERROR</strong><br>";
    echo "<span style='color:red'>Error: " . $e->getMessage() . "</span><br>";
}
echo "<hr>";

// 6. Test SSO config
echo "<h2>⚙️ SSO Configuration Test</h2>";
try {
    if (file_exists('sso_config.php')) {
        require_once 'sso_config.php';
        echo "✅ SSO Config loaded<br>";
        
        // Check constants
        $constants = ['SSO_AUTH_SERVER_URL', 'SSO_CLIENT_ID', 'SSO_REDIRECT_URI'];
        foreach ($constants as $const) {
            if (defined($const)) {
                echo "<strong>$const:</strong> " . constant($const) . "<br>";
            } else {
                echo "<strong>$const:</strong> ❌ <span style='color:red'>NOT DEFINED</span><br>";
            }
        }
    } else {
        echo "❌ sso_config.php: <strong>NOT FOUND</strong><br>";
    }
} catch (Exception $e) {
    echo "❌ SSO Config: <strong>ERROR</strong><br>";
    echo "<span style='color:red'>Error: " . $e->getMessage() . "</span><br>";
}
echo "<hr>";

// 7. Test database/API connection
echo "<h2>🌐 Network Test</h2>";
$test_urls = [
    'https://sso.bps.go.id' => 'SSO BPS Server',
    'https://sso.bps.go.id/auth/realms/pegawai-bps' => 'BPS Realm'
];

foreach ($test_urls as $url => $name) {
    echo "<strong>Testing $name:</strong> ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request only
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result !== false && $http_code < 400) {
        echo "✅ <strong>OK</strong> (HTTP $http_code)<br>";
    } else {
        echo "❌ <strong>FAILED</strong>";
        if ($error) echo " - $error";
        if ($http_code) echo " (HTTP $http_code)";
        echo "<br>";
    }
}
echo "<hr>";

// 8. Error log check
echo "<h2>📝 Recent Error Log</h2>";
$error_log_files = [
    'error_log',
    '../error_log', 
    '../../error_log',
    '/tmp/php_errors.log'
];

$found_errors = false;
foreach ($error_log_files as $log_file) {
    if (file_exists($log_file) && is_readable($log_file)) {
        echo "<strong>Found error log:</strong> $log_file<br>";
        $errors = file_get_contents($log_file);
        
        // Show last 10 lines
        $lines = explode("\n", $errors);
        $recent_lines = array_slice($lines, -10);
        
        echo "<pre style='background:#f5f5f5;padding:10px;border:1px solid #ccc;max-height:200px;overflow:auto'>";
        echo htmlspecialchars(implode("\n", $recent_lines));
        echo "</pre>";
        
        $found_errors = true;
        break;
    }
}

if (!$found_errors) {
    echo "ℹ️ No error log found in common locations<br>";
}

echo "<hr>";
echo "<h2>🔧 Quick Fixes</h2>";
echo "<ol>";
echo "<li><strong>If vendor/autoload.php missing:</strong> Re-upload vendor folder</li>";
echo "<li><strong>If JKD SSO not found:</strong> Run 'composer install' on server</li>";
echo "<li><strong>If permissions issue:</strong> Set folders to 755, files to 644</li>";
echo "<li><strong>If session not working:</strong> Check PHP session settings</li>";
echo "<li><strong>If network issue:</strong> Check firewall/SSL settings</li>";
echo "</ol>";

echo "<p><strong>Next step:</strong> Check specific page errors by accessing each page directly</p>";
?> 