<?php
// sso_logout.php - Logout dari SSO BPS

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

require_once 'vendor/autoload.php';
require_once 'sso_config.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

// Inisialisasi provider Keycloak
$provider = new Keycloak([
    'authServerUrl' => SSO_AUTH_SERVER_URL,
    'realm' => SSO_REALM,
    'clientId' => SSO_CLIENT_ID,
    'clientSecret' => SSO_CLIENT_SECRET,
    'redirectUri' => SSO_REDIRECT_URI
]);

// Dapatkan logout URL dari library
$logout_url = $provider->getLogoutUrl([
    'redirect_uri' => SSO_REDIRECT_URI // Redirect kembali ke login page
]);

// Hapus session lokal
logout();

// Redirect ke SSO logout
header('Location: ' . $logout_url);
exit;
?> 