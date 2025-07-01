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
    error_log('SSO Session Saved - Unit Kerja: ' . $unit_kerja);
    
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