<?php
// sso_callback.php - Callback handler untuk SSO BPS

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'sso_config.php';
require_once 'sso_logging.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

// Log callback access
error_log('SSO Callback accessed with params: ' . json_encode($_GET));

// Cek apakah ada parameter code dan state
if (!isset($_GET['code']) || !isset($_GET['state'])) {
    header('Location: sso_login.php?error=missing_params');
    exit;
}

// Validasi state untuk CSRF protection
if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    header('Location: sso_login.php?error=invalid_state');
    exit;
}

try {
    // Inisialisasi provider Keycloak dengan library JKD SSO
    $provider = new Keycloak([
        'authServerUrl' => SSO_AUTH_SERVER_URL,
        'realm' => SSO_REALM,
        'clientId' => SSO_CLIENT_ID,
        'clientSecret' => SSO_CLIENT_SECRET,
        'redirectUri' => SSO_REDIRECT_URI
    ]);

    // Exchange authorization code untuk access token
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Mendapatkan data user menggunakan library JKD SSO
    $user = $provider->getResourceOwner($token);
    
    // Extract semua data yang tersedia dari library
    $nama = $user->getName() ?: '';
    $email = $user->getEmail() ?: '';
    $username = $user->getUsername() ?: '';
    $nip = $user->getNip() ?: ''; // NIP Lama
    $nipBaru = $user->getNipBaru() ?: ''; // NIP Baru
    $kodeOrganisasi = $user->getKodeOrganisasi() ?: '';
    $kodeProvinsi = $user->getKodeProvinsi() ?: '';
    $kodeKabupaten = $user->getKodeKabupaten() ?: '';
    $alamatKantor = $user->getAlamatKantor() ?: '';
    $provinsi = $user->getProvinsi() ?: '';
    $kabupaten = $user->getKabupaten() ?: '';
    $golongan = $user->getGolongan() ?: '';
    $jabatan = $user->getJabatan() ?: '';
    $eselon = $user->getEselon() ?: '';
    $urlFoto = $user->getUrlFoto() ?: '';
    
    // Debug: log data yang ditemukan
    error_log('SSO Data - Nama: ' . $nama);
    error_log('SSO Data - Username: ' . $username);
    error_log('SSO Data - NIP: ' . $nip);
    error_log('SSO Data - NIP Baru: ' . $nipBaru);
    error_log('SSO Data - Kode Organisasi: ' . $kodeOrganisasi);
    error_log('SSO Data - Provinsi: ' . $provinsi);
    error_log('SSO Data - Jabatan: ' . $jabatan);
    
    // Siapkan data pegawai sesuai format LoginSSO.php
    $prepared_data = array(
        "nama" => $nama,
        "email" => $email,
        "username" => $username,
        "nip" => $nip,
        "nipbaru" => $nipBaru,
        "kodeorganisasi_full" => $kodeOrganisasi,
        "kodeorganisasi" => strlen($kodeOrganisasi) >= 8 ? substr($kodeOrganisasi, -5) : '', // 5 digit terakhir
        "kodeprovinsi" => $kodeProvinsi,
        "kodekabupaten" => $kodeKabupaten,
        "alamatkantor" => $alamatKantor ?: $provinsi, // Gunakan alamat kantor atau provinsi
        "provinsi" => $provinsi,
        "kabupaten" => $kabupaten,
        "golongan" => $golongan,
        "jabatan" => $jabatan,
        "foto" => $urlFoto,
        "eselon" => $eselon,
    );
    
    // Tentukan unit kerja
    $unit_kerja_data = determineUnitKerja($kodeOrganisasi);
    $prepared_data['unit_kerja'] = $unit_kerja_data;
    
    // Ekstrak kode provinsi dan kabupaten untuk sistem QG
    $prov_code = '';
    $kab_code = '';
    
    if ($kodeOrganisasi && strlen($kodeOrganisasi) >= 13) {
        // Format: 3273000080000 (contoh)
        // Posisi 1-2: kode provinsi (32)
        // Posisi 3-4: kode kabupaten (73)
        $prov_code = substr($kodeOrganisasi, 0, 2);
        $kab_code = substr($kodeOrganisasi, 2, 2);
    } else {
        // Fallback menggunakan kode provinsi/kabupaten langsung
        $prov_code = $kodeProvinsi ?: '00';
        $kab_code = $kodeKabupaten ?: '00';
    }
    
    // Pastikan format 2 digit
    $prov_code = str_pad($prov_code, 2, '0', STR_PAD_LEFT);
    $kab_code = str_pad($kab_code, 2, '0', STR_PAD_LEFT);
    
    // Simpan data SSO dalam format yang diperlukan index.php dan monitoring.php
    $_SESSION['sso_logged_in'] = true;
    $_SESSION['sso_username'] = $username;
    $_SESSION['sso_nama'] = $nama;
    $_SESSION['sso_email'] = $email;
    $_SESSION['sso_nip'] = $nip;
    $_SESSION['sso_nipbaru'] = $nipBaru;
    $_SESSION['sso_jabatan'] = $jabatan;
    $_SESSION['sso_golongan'] = $golongan;
    $_SESSION['sso_eselon'] = $eselon;
    $_SESSION['sso_prov'] = $prov_code;
    $_SESSION['sso_kab'] = $kab_code;
    $_SESSION['sso_unit_kerja'] = $unit_kerja_data['level']; // Simpan level saja untuk kompatibilitas
    $_SESSION['sso_unit_kerja_data'] = $unit_kerja_data; // Simpan data lengkap untuk reference
    $_SESSION['sso_kode_organisasi'] = $kodeOrganisasi;
    $_SESSION['sso_provinsi'] = $provinsi;
    $_SESSION['sso_kabupaten'] = $kabupaten;
    $_SESSION['sso_alamat_kantor'] = $alamatKantor;
    $_SESSION['sso_foto'] = $urlFoto;
    $_SESSION['sso_login_time'] = time();
    
    // Simpan juga dalam format lama untuk backward compatibility
    $_SESSION['user_data'] = array_merge($prepared_data, array(
        'access_token' => $token->getToken(),
        'refresh_token' => $token->getRefreshToken(),
        'user_object' => $user, // Simpan object user untuk referensi
        'raw_data' => $user->toArray(), // Simpan raw data untuk debugging
        'login_time' => time()
    ));
    
    // Log untuk debugging
    error_log('SSO Session Saved - Username: ' . $username);
    error_log('SSO Session Saved - Prov Code: ' . $prov_code);
    error_log('SSO Session Saved - Kab Code: ' . $kab_code);
    error_log('SSO Session Saved - Unit Kerja: ' . $unit_kerja_data['level']);
    
    // Simpan log login ke database
    $log_data = array(
        'username' => $username,
        'nama' => $nama,
        'provinsi' => $provinsi,
        'kabupaten' => $kabupaten,
        'waktu_login' => date('Y-m-d H:i:s') // Generate waktu login saat ini
    );
    
    $log_result = saveSSOLoginLog($log_data);
    if ($log_result) {
        error_log('SSO Login logged to database successfully - Log ID: ' . $log_result);
    } else {
        error_log('SSO Login logging failed - check database connection');
    }
    
    // Cleanup
    unset($_SESSION['oauth2state']);
    
    // Redirect berdasarkan parameter atau ke dashboard secara default
    $redirect_to = $_GET['redirect'] ?? 'index.php';
    
    // Validasi redirect target untuk keamanan
    $allowed_redirects = ['index.php', 'monitoring.php', 'dashboard', 'monitoring'];
    
    if (in_array($redirect_to, $allowed_redirects)) {
        if ($redirect_to === 'dashboard') $redirect_to = 'index.php';
        if ($redirect_to === 'monitoring') $redirect_to = 'monitoring.php';
        
        header('Location: ' . $redirect_to);
    } else {
        header('Location: index.php'); // Default ke dashboard
    }
    exit;
    
} catch (Exception $e) {
    // Log error
    error_log('SSO Callback Error: ' . $e->getMessage());
    error_log('SSO Callback Error Stack: ' . $e->getTraceAsString());
    
    // Tampilkan error page yang informatif
    echo "<!DOCTYPE html><html><head><title>SSO Callback Error</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:40px;} .error{background:#ffebee;border:1px solid #e57373;padding:20px;border-radius:5px;}</style>";
    echo "</head><body>";
    echo "<h1>🚨 SSO Callback Error</h1>";
    echo "<div class='error'>";
    echo "<h3>Error Details:</h3>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "</div>";
    echo "<h3>Possible Solutions:</h3>";
    echo "<ul>";
    echo "<li>Check if SSO server is accessible</li>";
    echo "<li>Verify SSO configuration</li>";
    echo "<li>Check network connectivity</li>";
    echo "<li>Contact system administrator</li>";
    echo "</ul>";
    echo "<p><a href='debug.php?show=debug'>🐛 Debug Info</a> | ";
    echo "<a href='sso_login.php'>🔑 Try Login Again</a> | ";
    echo "<a href='index.php'>🏠 Home</a></p>";
    echo "</body></html>";
    exit;
}
?> 