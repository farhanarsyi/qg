<?php
// test_sso_library.php - Test implementasi SSO BPS dengan library JKD SSO
require_once 'vendor/autoload.php';
require_once 'sso_config.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

echo "<h1>Test SSO BPS dengan Library JKD SSO</h1>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
.success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 5px; margin: 10px 0; }
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
code { background-color: #f0f0f0; padding: 2px 4px; border-radius: 3px; }
</style>";

// Test 1: Inisialisasi Library
echo "<h2>Test 1: Inisialisasi Library JKD SSO</h2>";
try {
    $provider = new Keycloak([
        'authServerUrl' => SSO_AUTH_SERVER_URL,
        'realm' => SSO_REALM,
        'clientId' => SSO_CLIENT_ID,
        'clientSecret' => SSO_CLIENT_SECRET,
        'redirectUri' => SSO_REDIRECT_URI
    ]);
    
    echo "<div class='success'>✅ Library JKD SSO berhasil diinisialisasi</div>";
    
    echo "<table>";
    echo "<tr><th>Parameter</th><th>Value</th></tr>";
    echo "<tr><td>Auth Server URL</td><td>" . SSO_AUTH_SERVER_URL . "</td></tr>";
    echo "<tr><td>Realm</td><td>" . SSO_REALM . "</td></tr>";
    echo "<tr><td>Client ID</td><td>" . SSO_CLIENT_ID . "</td></tr>";
    echo "<tr><td>Redirect URI</td><td>" . SSO_REDIRECT_URI . "</td></tr>";
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 2: Generate Authorization URL
echo "<h2>Test 2: Generate Authorization URL</h2>";
try {
    $auth_url = $provider->getAuthorizationUrl();
    $state = $provider->getState();
    
    echo "<div class='success'>✅ Authorization URL berhasil dibuat</div>";
    echo "<div class='info'>";
    echo "<p><strong>Authorization URL:</strong></p>";
    echo "<p><code>" . htmlspecialchars($auth_url) . "</code></p>";
    echo "<p><strong>State (CSRF Protection):</strong> <code>$state</code></p>";
    echo "<p><a href='" . htmlspecialchars($auth_url) . "' target='_blank' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔗 Test Login</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 3: Generate Logout URL
echo "<h2>Test 3: Generate Logout URL</h2>";
try {
    $logout_url = $provider->getLogoutUrl([
        'redirect_uri' => SSO_REDIRECT_URI
    ]);
    
    echo "<div class='success'>✅ Logout URL berhasil dibuat</div>";
    echo "<div class='info'>";
    echo "<p><strong>Logout URL:</strong></p>";
    echo "<p><code>" . htmlspecialchars($logout_url) . "</code></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 4: Cek Class Methods
echo "<h2>Test 4: Available Methods in KeycloakResourceOwner</h2>";
try {
    $reflection = new ReflectionClass('JKD\SSO\Client\Provider\KeycloakResourceOwner');
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
    
    echo "<div class='success'>✅ Berhasil menganalisis class KeycloakResourceOwner</div>";
    echo "<table>";
    echo "<tr><th>Method</th><th>Description</th></tr>";
    
    $method_descriptions = [
        'getName' => 'Mendapatkan nama lengkap pegawai',
        'getEmail' => 'Mendapatkan alamat email',
        'getUsername' => 'Mendapatkan username',
        'getNip' => 'Mendapatkan NIP lama',
        'getNipBaru' => 'Mendapatkan NIP baru',
        'getKodeOrganisasi' => 'Mendapatkan kode organisasi',
        'getKodeProvinsi' => 'Mendapatkan kode provinsi',
        'getKodeKabupaten' => 'Mendapatkan kode kabupaten',
        'getAlamatKantor' => 'Mendapatkan alamat kantor',
        'getProvinsi' => 'Mendapatkan nama provinsi',
        'getKabupaten' => 'Mendapatkan nama kabupaten',
        'getGolongan' => 'Mendapatkan golongan',
        'getJabatan' => 'Mendapatkan jabatan',
        'getEselon' => 'Mendapatkan eselon',
        'getUrlFoto' => 'Mendapatkan URL foto',
        'toArray' => 'Mendapatkan semua data dalam array'
    ];
    
    foreach ($methods as $method) {
        $method_name = $method->getName();
        if (strpos($method_name, 'get') === 0 || $method_name === 'toArray') {
            $description = isset($method_descriptions[$method_name]) 
                ? $method_descriptions[$method_name] 
                : 'Method standar';
            echo "<tr>";
            echo "<td><code>$method_name()</code></td>";
            echo "<td>$description</td>";
            echo "</tr>";
        }
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Test 5: Status Login
echo "<h2>Test 5: Status Login Saat Ini</h2>";
if (isLoggedIn()) {
    $user_data = $_SESSION['user_data'];
    echo "<div class='success'>✅ User sudah login</div>";
    echo "<table>";
    echo "<tr><th>Data</th><th>Value</th></tr>";
    
    $display_fields = ['nama', 'email', 'username', 'nip', 'nipbaru', 'jabatan', 'provinsi'];
    foreach ($display_fields as $field) {
        $value = isset($user_data[$field]) ? $user_data[$field] : 'N/A';
        echo "<tr><td>" . ucfirst($field) . "</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
    
    echo "<p><a href='profile.php' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>👤 Lihat Profile</a></p>";
    echo "<p><a href='sso_logout.php' style='background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🚪 Logout</a></p>";
    
} else {
    echo "<div class='info'>ℹ️ User belum login</div>";
    echo "<p><a href='sso_login.php' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;'>🔐 Login SSO BPS</a></p>";
}

// Test 6: Check Dependencies
echo "<h2>Test 6: Check Dependencies</h2>";
$required_classes = [
    'JKD\SSO\Client\Provider\Keycloak' => 'Main SSO provider class',
    'JKD\SSO\Client\Provider\KeycloakResourceOwner' => 'User data resource owner',
    'League\OAuth2\Client\Provider\AbstractProvider' => 'OAuth2 base provider',
    'Firebase\JWT\JWT' => 'JWT token handling'
];

foreach ($required_classes as $class => $description) {
    if (class_exists($class)) {
        echo "<div class='success'>✅ $class - $description</div>";
    } else {
        echo "<div class='error'>❌ $class - $description (Missing!)</div>";
    }
}

echo "<hr>";
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href='sso_login.php'>SSO Login Page</a></li>";
echo "<li><a href='debug_attributes.php'>Debug SSO Data</a></li>";
echo "<li><a href='demo_pegawai.php'>Demo dengan Data Dummy</a></li>";
echo "<li><a href='demo_unit_levels.php'>Demo Unit Levels</a></li>";
echo "<li><a href='profile.php'>Profile Page</a></li>";
echo "</ul>";
?> 