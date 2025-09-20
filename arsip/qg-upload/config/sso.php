<?php
// config/sso.php - SSO Configuration

return [
    'auth_server_url' => 'https://sso.bps.go.id',
    'realm' => 'pegawai-bps',
    'client_id' => '07300-dashqg-l30',
    'client_secret' => 'e1c46e44-f33a-45f0-ace1-62c445333ae7',
    'redirect_uri' => 'https://dashboardqg.web.bps.go.id/sso_callback.php',
    'scope' => 'openid profile-pegawai',
    
    'api' => [
        'base_url' => 'https://sso.bps.go.id/auth/',
        'token_url' => 'https://sso.bps.go.id/auth/realms/pegawai-bps/protocol/openid-connect/token',
        'pegawai_url' => 'https://sso.bps.go.id/auth/realms/pegawai-bps/api-pegawai'
    ]
]; 