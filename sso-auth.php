<?php
session_start();

// Auto load composer untuk library SSO BPS (sesuai saran Fachrunisa)
require_once 'vendor/autoload.php';

use IrsadArief\JKD\SSO\Client\Provider\Keycloak;

// Konfigurasi SSO BPS
$provider = new Keycloak([
    'authServerUrl'         => 'https://sso.bps.go.id',
    'realm'                 => 'pegawai-bps',
    'clientId'              => '07300-dashqg-l30',
    'clientSecret'          => 'e1c46e44-f33a-45f0-ace1-62c445333ae7',
    'redirectUri'           => 'https://dashboardqg.web.bps.go.id/sso-callback.php'
]);

if (!isset($_GET['code'])) {
    // Jika belum ada authorization code, redirect ke SSO
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => 'openid profile-pegawai'
    ]);
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authUrl);
    exit;

// Validasi state untuk mencegah CSRF attack
} elseif (empty($_GET['state']) || !isset($_SESSION['oauth2state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    header('Location: login.php?error=invalid_state');
    exit;

} else {
    try {
        // Mendapatkan access token
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
        
        // Mendapatkan informasi user dari SSO
        $user = $provider->getResourceOwner($token);
        
        // Menyimpan data user ke session
        $_SESSION['user_authenticated'] = true;
        $_SESSION['user_data'] = [
            'nama' => $user->getName(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'nip' => $user->getNip(),
            'nip_baru' => $user->getNipBaru(),
            'kode_organisasi' => $user->getKodeOrganisasi(),
            'kode_provinsi' => $user->getKodeProvinsi(),
            'kode_kabupaten' => $user->getKodeKabupaten(),
            'provinsi' => $user->getProvinsi(),
            'kabupaten' => $user->getKabupaten(),
            'golongan' => $user->getGolongan(),
            'jabatan' => $user->getJabatan(),
            'foto' => $user->getUrlFoto(),
            'eselon' => $user->getEselon(),
        ];
        $_SESSION['access_token'] = $token->getToken();
        $_SESSION['logout_url'] = $provider->getLogoutUrl();
        
        // Redirect ke dashboard
        header('Location: index.php');
        exit;
        
    } catch (Exception $e) {
        // Jika terjadi error, redirect ke login dengan pesan error
        header('Location: login.php?error=sso_failed&message=' . urlencode($e->getMessage()));
        exit;
    }
}
?> 