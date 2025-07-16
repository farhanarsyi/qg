<?php
/**
 * SSO Manager Class
 * Handles all SSO-related operations for BPS authentication
 */

use JKD\SSO\Client\Provider\Keycloak;

class SSOManager {
    
    private $provider;
    private $logger;
    
    public function __construct() {
        $this->initializeProvider();
        $this->logger = new Logger('SSO');
    }
    
    /**
     * Initialize Keycloak provider
     */
    private function initializeProvider() {
        $this->provider = new Keycloak([
            'authServerUrl' => SSO_AUTH_SERVER_URL,
            'realm' => SSO_REALM,
            'clientId' => SSO_CLIENT_ID,
            'clientSecret' => SSO_CLIENT_SECRET,
            'redirectUri' => SSO_REDIRECT_URI
        ]);
    }
    
    /**
     * Generate authorization URL for login
     * @return string Authorization URL
     */
    public function getAuthorizationUrl() {
        $authUrl = $this->provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = $this->provider->getState();
        return $authUrl;
    }
    
    /**
     * Handle SSO callback and validate user
     * @param string $code Authorization code
     * @param string $state State parameter
     * @return array User data array
     * @throws Exception
     */
    public function handleCallback($code, $state) {
        // Validate state for CSRF protection
        if (empty($state) || ($state !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
            throw new Exception('Invalid state parameter - CSRF protection');
        }
        
        try {
            // Exchange authorization code for access token
            $token = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            
            // Get user data from SSO
            $user = $this->provider->getResourceOwner($token);
            
            // Prepare user data
            $userData = $this->prepareUserData($user, $token);
            
            // Save to session
            $this->saveUserSession($userData);
            
            // Cleanup
            unset($_SESSION['oauth2state']);
            
            $this->logger->info('SSO login successful', ['username' => $userData['username']]);
            
            return $userData;
            
        } catch (Exception $e) {
            $this->logger->error('SSO callback error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Prepare user data from SSO response
     * @param object $user SSO user object
     * @param object $token Access token
     * @return array Formatted user data
     */
    private function prepareUserData($user, $token) {
        // Extract basic user information
        $userData = [
            'nama' => $user->getName() ?: '',
            'email' => $user->getEmail() ?: '',
            'username' => $user->getUsername() ?: '',
            'nip' => $user->getNip() ?: '',
            'nipbaru' => $user->getNipBaru() ?: '',
            'kodeorganisasi_full' => $user->getKodeOrganisasi() ?: '',
            'kodeprovinsi' => $user->getKodeProvinsi() ?: '',
            'kodekabupaten' => $user->getKodeKabupaten() ?: '',
            'alamatkantor' => $user->getAlamatKantor() ?: $user->getProvinsi(),
            'provinsi' => $user->getProvinsi() ?: '',
            'kabupaten' => $user->getKabupaten() ?: '',
            'golongan' => $user->getGolongan() ?: '',
            'jabatan' => $user->getJabatan() ?: '',
            'foto' => $user->getUrlFoto() ?: '',
            'eselon' => $user->getEselon() ?: '',
        ];
        
        // Add organization code (5 digit)
        $userData['kodeorganisasi'] = strlen($userData['kodeorganisasi_full']) >= 8 
            ? substr($userData['kodeorganisasi_full'], -5) 
            : '';
        
        // Determine unit kerja
        $userData['unit_kerja'] = $this->determineUnitKerja($userData['kodeorganisasi_full']);
        
        // Extract province and district codes for QG system
        $codes = $this->extractLocationCodes($userData['kodeorganisasi_full'], 
                                           $userData['kodeprovinsi'], 
                                           $userData['kodekabupaten']);
        $userData['prov_code'] = $codes['prov'];
        $userData['kab_code'] = $codes['kab'];
        
        // Add token information
        $userData['access_token'] = $token->getToken();
        $userData['refresh_token'] = $token->getRefreshToken();
        $userData['login_time'] = time();
        
        return $userData;
    }
    
    /**
     * Determine unit kerja based on organization code
     * @param string $kodeOrganisasi Organization code
     * @return array Unit kerja information
     */
    private function determineUnitKerja($kodeOrganisasi) {
        if (empty($kodeOrganisasi)) {
            return [
                'level' => 'unknown',
                'kode_unit' => '',
                'kode_provinsi' => '',
                'kode_kabupaten' => '',
                'nama_level' => 'Tidak Diketahui'
            ];
        }
        
        // Extract codes based on position
        $kode_provinsi = substr($kodeOrganisasi, 0, 2);
        $kode_kabupaten = substr($kodeOrganisasi, 2, 2);
        $kode_unit = substr($kodeOrganisasi, 7, 5);
        
        // Determine level
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
            'kode_organisasi_full' => $kodeOrganisasi,
            'nama_level' => $nama_level
        ];
    }
    
    /**
     * Extract location codes for QG system
     * @param string $kodeOrganisasi Full organization code
     * @param string $kodeProvinsi Province code from SSO
     * @param string $kodeKabupaten District code from SSO
     * @return array Location codes
     */
    private function extractLocationCodes($kodeOrganisasi, $kodeProvinsi, $kodeKabupaten) {
        $prov_code = '00';
        $kab_code = '00';
        
        if ($kodeOrganisasi && strlen($kodeOrganisasi) >= 13) {
            // Extract from organization code
            $prov_code = substr($kodeOrganisasi, 0, 2);
            $kab_code = substr($kodeOrganisasi, 2, 2);
        } else {
            // Fallback to direct codes
            $prov_code = $kodeProvinsi ?: '00';
            $kab_code = $kodeKabupaten ?: '00';
        }
        
        // Ensure 2-digit format
        $prov_code = str_pad($prov_code, 2, '0', STR_PAD_LEFT);
        $kab_code = str_pad($kab_code, 2, '0', STR_PAD_LEFT);
        
        return ['prov' => $prov_code, 'kab' => $kab_code];
    }
    
    /**
     * Save user data to session
     * @param array $userData User data array
     */
    private function saveUserSession($userData) {
        // Save in new format
        $_SESSION['sso_logged_in'] = true;
        $_SESSION['sso_username'] = $userData['username'];
        $_SESSION['sso_nama'] = $userData['nama'];
        $_SESSION['sso_email'] = $userData['email'];
        $_SESSION['sso_nip'] = $userData['nip'];
        $_SESSION['sso_nipbaru'] = $userData['nipbaru'];
        $_SESSION['sso_jabatan'] = $userData['jabatan'];
        $_SESSION['sso_golongan'] = $userData['golongan'];
        $_SESSION['sso_eselon'] = $userData['eselon'];
        $_SESSION['sso_prov'] = $userData['prov_code'];
        $_SESSION['sso_kab'] = $userData['kab_code'];
        $_SESSION['sso_unit_kerja'] = $userData['unit_kerja']['level'];
        $_SESSION['sso_unit_kerja_data'] = $userData['unit_kerja'];
        $_SESSION['sso_kode_organisasi'] = $userData['kodeorganisasi_full'];
        $_SESSION['sso_provinsi'] = $userData['provinsi'];
        $_SESSION['sso_kabupaten'] = $userData['kabupaten'];
        $_SESSION['sso_alamat_kantor'] = $userData['alamatkantor'];
        $_SESSION['sso_foto'] = $userData['foto'];
        $_SESSION['sso_login_time'] = $userData['login_time'];
        
        // Save complete user data for backward compatibility
        $_SESSION['user_data'] = $userData;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['sso_logged_in']) && 
               $_SESSION['sso_logged_in'] === true && 
               !empty($_SESSION['sso_username']);
    }
    
    /**
     * Get current user data
     * @return array|null User data or null if not logged in
     */
    public function getUserData() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $_SESSION['user_data'] ?? null;
    }
    
    /**
     * Get user unit kerja information
     * @return array|null Unit kerja data
     */
    public function getUserUnitKerja() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return $_SESSION['sso_unit_kerja_data'] ?? null;
    }
    
    /**
     * Get wilayah filter based on user's unit kerja
     * @return array Filter configuration
     */
    public function getWilayahFilter() {
        $unitKerja = $this->getUserUnitKerja();
        if (!$unitKerja) {
            return ['level' => 'unknown', 'can_view_all' => false];
        }
        
        $level = $unitKerja['level'] ?? 'unknown';
        
        $filter = [
            'level' => $level,
            'can_view_all' => false,
            'provinsi_filter' => [],
            'kabupaten_filter' => []
        ];
        
        switch ($level) {
            case 'pusat':
                $filter['can_view_all'] = true;
                $filter['provinsi_filter'] = 'all';
                $filter['kabupaten_filter'] = 'all';
                break;
                
            case 'provinsi':
                $filter['provinsi_filter'] = [$unitKerja['kode_provinsi']];
                $filter['kabupaten_filter'] = 'all_in_province';
                break;
                
            case 'kabupaten':
                $filter['provinsi_filter'] = [$unitKerja['kode_provinsi']];
                $filter['kabupaten_filter'] = [$unitKerja['kode_kabupaten']];
                break;
        }
        
        return $filter;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear SSO session data
        $ssoKeys = [
            'sso_logged_in', 'sso_username', 'sso_nama', 'sso_email',
            'sso_nip', 'sso_nipbaru', 'sso_jabatan', 'sso_golongan',
            'sso_eselon', 'sso_prov', 'sso_kab', 'sso_unit_kerja',
            'sso_unit_kerja_data', 'sso_kode_organisasi', 'sso_provinsi',
            'sso_kabupaten', 'sso_alamat_kantor', 'sso_foto', 'sso_login_time',
            'user_data', 'oauth2state'
        ];
        
        foreach ($ssoKeys as $key) {
            unset($_SESSION[$key]);
        }
        
        $this->logger->info('User logged out');
    }
    
    /**
     * Force redirect to login if not authenticated
     * @param string $redirectAfterLogin Where to redirect after successful login
     */
    public function requireLogin($redirectAfterLogin = 'index.php') {
        if (!$this->isLoggedIn()) {
            $loginUrl = 'sso_login.php';
            if ($redirectAfterLogin !== 'index.php') {
                $loginUrl .= '?redirect=' . urlencode($redirectAfterLogin);
            }
            header('Location: ' . $loginUrl);
            exit;
        }
    }
} 