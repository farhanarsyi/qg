<?php
/**
 * Local Development Environment Configuration
 */

// Application Settings
define('QG_DEBUG', true);
define('QG_LOG_LEVEL', 'debug');

// Base URLs
define('QG_BASE_URL', 'http://localhost/qg/qg-final');
define('QG_ASSETS_URL', QG_BASE_URL . '/assets');

// SSO Configuration
define('SSO_AUTH_SERVER_URL', 'https://sso.bps.go.id');
define('SSO_REALM', 'pegawai-bps');
define('SSO_CLIENT_ID', '07300-dashqg-l30');
define('SSO_CLIENT_SECRET', 'e1c46e44-f33a-45f0-ace1-62c445333ae7');
define('SSO_REDIRECT_URI', QG_BASE_URL . '/sso_callback.php');
define('SSO_SCOPE', 'openid profile-pegawai');

// API Configuration
define('API_BASE_URL', 'https://sso.bps.go.id/auth/');
define('API_TOKEN_URL', API_BASE_URL . 'realms/pegawai-bps/protocol/openid-connect/token');
define('API_PEGAWAI_URL', API_BASE_URL . 'realms/pegawai-bps/api-pegawai');

// Error Reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 