<?php
// classes/SSOManager.php - SSO Authentication Manager

class SSOManager {
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/sso.php';
        $this->startSession();
    }
    
    /**
     * Start session if not already started
     */
    private function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_data']) && !empty($_SESSION['user_data']);
    }
    
    /**
     * Get user data from session
     */
    public function getUserData() {
        return $_SESSION['user_data'] ?? null;
    }
    
    /**
     * Set user data in session
     */
    public function setUserData($userData) {
        $_SESSION['user_data'] = $userData;
    }
    
    /**
     * Get access token for API calls
     */
    public function getAccessToken() {
        $ch = curl_init($this->config['api']['token_url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['client_id'] . ":" . $this->config['client_secret']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        curl_close($ch);
        $json = json_decode($response, true);
        
        return $json['access_token'] ?? null;
    }
    
    /**
     * Get employee data by username
     */
    public function getPegawaiByUsername($username, $accessToken) {
        $url = $this->config['api']['pegawai_url'] . '/username/' . urlencode($username);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }
    
    /**
     * Determine unit kerja from organization code
     */
    public function determineUnitKerja($kodeOrganisasi) {
        if (empty($kodeOrganisasi)) {
            return [
                'level' => 'unknown',
                'kode_unit' => '',
                'kode_provinsi' => '',
                'kode_kabupaten' => '',
                'nama_level' => 'Tidak Diketahui'
            ];
        }
        
        $kode_provinsi = substr($kodeOrganisasi, 0, 2);
        $kode_kabupaten = substr($kodeOrganisasi, 2, 2);
        $kode_unit = substr($kodeOrganisasi, 7, 5);
        
        $level = 'kabupaten';
        $nama_level = 'Kabupaten/Kota';
        
        if ($kode_provinsi == '00' || $kode_unit == '10000') {
            $level = 'pusat';
            $nama_level = 'Pusat';
        } elseif ($kode_kabupaten == '00' || $kode_kabupaten == '71') {
            $level = 'provinsi';
            $nama_level = 'Provinsi';
        }
        
        return [
            'level' => $level,
            'kode_unit' => $kode_unit,
            'kode_provinsi' => $kode_provinsi,
            'kode_kabupaten' => $kode_kabupaten,
            'nama_level' => $nama_level
        ];
    }
    
    /**
     * Get authorization URL for SSO login
     */
    public function getAuthorizationUrl($state = null) {
        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->config['scope']
        ];
        
        if ($state) {
            $params['state'] = $state;
        }
        
        return $this->config['auth_server_url'] . '/auth/realms/' . $this->config['realm'] . '/protocol/openid-connect/auth?' . http_build_query($params);
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Require login - redirect if not logged in
     */
    public function requireLogin($currentPage = '') {
        if (!$this->isLoggedIn()) {
            $redirectUrl = 'sso_login.php';
            if ($currentPage) {
                $redirectUrl .= '?redirect=' . urlencode($currentPage);
            }
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
} 