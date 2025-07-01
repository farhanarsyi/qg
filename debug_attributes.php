<?php
// debug_attributes.php - Debug untuk melihat semua data dari SSO BPS menggunakan library JKD SSO
require_once 'vendor/autoload.php';
require_once 'sso_config.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

echo "<h1>Debug SSO BPS - Data Analysis</h1>";

// Cek apakah sudah login
if (isLoggedIn()) {
    $user_data = $_SESSION['user_data'];
    
    echo "<h2 style='color: green;'>✅ User Sudah Login</h2>";
    echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    
    echo "<h3>Data Processed:</h3>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%; margin: 10px 0;'>";
    echo "<tr><th style='background: #e6f3ff;'>Field</th><th style='background: #e6f3ff;'>Value</th></tr>";
    
    foreach ($user_data as $key => $value) {
        if (is_array($value) || is_object($value)) {
            continue; // Skip complex objects for display
        }
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($key) . "</strong></td>";
        echo "<td>" . htmlspecialchars($value) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Tampilkan raw data jika ada
    if (isset($user_data['raw_data'])) {
        echo "<h3>Raw Data dari SSO:</h3>";
        echo "<div style='background: #fff0f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; margin: 10px 0;'>";
        echo "<tr><th>SSO Key</th><th>Value</th></tr>";
        
        foreach ($user_data['raw_data'] as $key => $value) {
            echo "<tr>";
            echo "<td><code>" . htmlspecialchars($key) . "</code></td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Test data yang tersedia dari library JKD SSO
    if (isset($user_data['user_object'])) {
        echo "<h2>Data Available from JKD SSO Library:</h2>";
        echo "<div style='background: #fff0f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        
        $user_obj = $user_data['user_object'];
        $methods = [
            'getName' => 'Nama',
            'getEmail' => 'Email',
            'getUsername' => 'Username',
            'getNip' => 'NIP Lama',
            'getNipBaru' => 'NIP Baru',
            'getKodeOrganisasi' => 'Kode Organisasi',
            'getKodeProvinsi' => 'Kode Provinsi',
            'getKodeKabupaten' => 'Kode Kabupaten',
            'getAlamatKantor' => 'Alamat Kantor',
            'getProvinsi' => 'Provinsi',
            'getKabupaten' => 'Kabupaten',
            'getGolongan' => 'Golongan',
            'getJabatan' => 'Jabatan',
            'getEselon' => 'Eselon',
            'getUrlFoto' => 'URL Foto'
        ];
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; margin: 10px 0;'>";
        echo "<tr><th>Method</th><th>Value</th><th>Available</th></tr>";
        
        foreach ($methods as $method => $label) {
            $value = '';
            $available = '❌';
            
            if (method_exists($user_obj, $method)) {
                try {
                    $value = $user_obj->$method();
                    $available = !empty($value) ? '✅' : '⚠️ (empty)';
                } catch (Exception $e) {
                    $value = 'Error: ' . $e->getMessage();
                    $available = '❌';
                }
            }
            
            echo "<tr>";
            echo "<td><code>$method()</code><br><small>$label</small></td>";
            echo "<td>" . htmlspecialchars($value) . "</td>";
            echo "<td style='text-align: center;'>$available</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
    }
    
} else {
    echo "<h2 style='color: orange;'>⚠️ User Belum Login</h2>";
    echo "<p>Silakan login terlebih dahulu untuk melihat data SSO.</p>";
    echo "<p><a href='sso_login.php' style='background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login SSO BPS</a></p>";
}

// Test manual dengan library JKD SSO
echo "<hr>";
echo "<h2>Manual Test Library JKD SSO</h2>";
echo "<p>Inisialisasi library tanpa login:</p>";

try {
    $provider = new Keycloak([
        'authServerUrl' => SSO_AUTH_SERVER_URL,
        'realm' => SSO_REALM,
        'clientId' => SSO_CLIENT_ID,
        'clientSecret' => SSO_CLIENT_SECRET,
        'redirectUri' => SSO_REDIRECT_URI
    ]);
    
    echo "<div style='background: #f0fff0; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<p>✅ Library JKD SSO berhasil diinisialisasi</p>";
    echo "<p><strong>Auth Server URL:</strong> " . SSO_AUTH_SERVER_URL . "</p>";
    echo "<p><strong>Realm:</strong> " . SSO_REALM . "</p>";
    echo "<p><strong>Client ID:</strong> " . SSO_CLIENT_ID . "</p>";
    
    $auth_url = $provider->getAuthorizationUrl();
    echo "<p><strong>Auth URL:</strong> <a href='" . htmlspecialchars($auth_url) . "' target='_blank'>Test Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h2>Demo dengan Data Dummy</h2>";
echo "<p>Untuk testing tanpa koneksi API, gunakan:</p>";
echo "<ul>";
echo "<li><a href='demo_pegawai.php'>Demo Pegawai</a> - Demo dengan data dummy lengkap</li>";
echo "<li><a href='demo_unit_levels.php'>Demo Unit Levels</a> - Test berbagai level unit kerja</li>";
echo "</ul>";

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; }
th { background-color: #f5f5f5; }
code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
</style>";
?> 