<?php
// sso_callback.php - Callback handler untuk SSO BPS
require_once 'vendor/autoload.php';
require_once 'sso_config.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

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
    $unit_kerja = determineUnitKerja($kodeOrganisasi);
    $prepared_data['unit_kerja'] = $unit_kerja;
    
    // Simpan data user ke session
    $_SESSION['user_data'] = array_merge($prepared_data, array(
        'access_token' => $token->getToken(),
        'refresh_token' => $token->getRefreshToken(),
        'user_object' => $user, // Simpan object user untuk referensi
        'raw_data' => $user->toArray(), // Simpan raw data untuk debugging
        'login_time' => time()
    ));
    
    // Cleanup
    unset($_SESSION['oauth2state']);
    
    // Redirect berdasarkan parameter atau ke dashboard secara default
    $redirect_to = $_GET['redirect'] ?? 'index.php';
    
    // Validasi redirect target untuk keamanan
    $allowed_redirects = ['index.php', 'monitoring.php', 'profile.php', 'dashboard', 'monitoring', 'profile'];
    
    if (in_array($redirect_to, $allowed_redirects)) {
        if ($redirect_to === 'dashboard') $redirect_to = 'index.php';
        if ($redirect_to === 'monitoring') $redirect_to = 'monitoring.php';
        if ($redirect_to === 'profile') $redirect_to = 'profile.php';
        
        header('Location: ' . $redirect_to);
    } else {
        header('Location: index.php'); // Default ke dashboard
    }
    exit;
    
} catch (Exception $e) {
    // Log error
    error_log('SSO Callback Error: ' . $e->getMessage());
    
    // Redirect ke login dengan error
    header('Location: sso_login.php?error=' . urlencode($e->getMessage()));
    exit;
}
?> 