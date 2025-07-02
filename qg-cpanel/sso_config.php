<?php
// sso_config.php - Konfigurasi SSO BPS

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

// Konfigurasi SSO BPS
define('SSO_AUTH_SERVER_URL', 'https://sso.bps.go.id');
define('SSO_REALM', 'pegawai-bps');
define('SSO_CLIENT_ID', '07300-dashqg-l30');
define('SSO_CLIENT_SECRET', 'e1c46e44-f33a-45f0-ace1-62c445333ae7');
define('SSO_REDIRECT_URI', 'https://dashboardqg.web.bps.go.id/main/sso_callback.php'); // Production URL
define('SSO_SCOPE', 'openid profile-pegawai');

// URL API Pegawai BPS
define('API_BASE_URL', 'https://sso.bps.go.id/auth/');
define('API_TOKEN_URL', API_BASE_URL . 'realms/pegawai-bps/protocol/openid-connect/token');
define('API_PEGAWAI_URL', API_BASE_URL . 'realms/pegawai-bps/api-pegawai');

// Fungsi untuk mendapatkan access token menggunakan client credentials
function getAccessToken() {
    $ch = curl_init(API_TOKEN_URL);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, SSO_CLIENT_ID . ":" . SSO_CLIENT_SECRET);  
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response_token = curl_exec($ch);
    
    if(curl_errno($ch)){
        throw new Exception(curl_error($ch));
    }
    
    curl_close($ch);
    $json_token = json_decode($response_token, true);
    
    if (isset($json_token['access_token'])) {
        return $json_token['access_token'];
    }
    
    return null;
}

// Fungsi untuk mendapatkan data pegawai berdasarkan username
function getPegawaiByUsername($username, $access_token) {
    $query_search = '/username/' . urlencode($username);
    $url = API_PEGAWAI_URL . $query_search;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ));  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    
    if(curl_errno($ch)){
        throw new Exception(curl_error($ch));
    }
    
    curl_close($ch);
    $json = json_decode($response, true);
    
    return $json;
}

// Fungsi untuk menentukan unit kerja berdasarkan kode organisasi (sesuai contoh LoginSSO.php)
function determineUnitKerja($kodeOrganisasi) {
    if (empty($kodeOrganisasi)) {
        return array(
            'level' => 'unknown',
            'kode_unit' => '',
            'kode_provinsi' => '',
            'kode_kabupaten' => '',
            'nama_level' => 'Tidak Diketahui'
        );
    }
    
    // Ekstrak kode berdasarkan posisi seperti di LoginSSO.php
    $kode_provinsi = substr($kodeOrganisasi, 0, 2);
    $kode_kabupaten = substr($kodeOrganisasi, 2, 2);
    $kode_unit = substr($kodeOrganisasi, 7, 5); // 5 digit terakhir
    
    // Tentukan level unit kerja
    $level = 'kabupaten'; // default
    $nama_level = 'Kabupaten/Kota';
    
    // Logika penentuan level berdasarkan kode
    if ($kode_provinsi == '00' || $kode_unit == '10000') {
        $level = 'pusat';
        $nama_level = 'Pusat';
    } elseif ($kode_kabupaten == '00' || $kode_kabupaten == '71') {
        $level = 'provinsi';
        $nama_level = 'Provinsi';
    }
    
    return array(
        'level' => $level,
        'kode_unit' => $kode_unit,
        'kode_provinsi' => $kode_provinsi,
        'kode_kabupaten' => $kode_kabupaten,
        'kode_organisasi_full' => $kodeOrganisasi,
        'nama_level' => $nama_level
    );
}

// Fungsi untuk menyiapkan data pegawai dengan unit kerja
function preparePegawaiData($user_sso, $pegawai_data) {
    // Ambil data dari SSO user object
    $data = array(
        "nama" => $user_sso->getName(),
        "email" => $user_sso->getEmail(),
        "username" => $user_sso->getUsername(),
        "nip" => $user_sso->getNip(),
        "nipbaru" => $user_sso->getNipBaru(),
        "kodeorganisasi_full" => $user_sso->getKodeOrganisasi(),
        "kodeorganisasi" => substr($user_sso->getKodeOrganisasi(), 7, 5), // 5 digit terakhir
        "kodeprovinsi" => $user_sso->getKodeProvinsi(),
        "kodekabupaten" => $user_sso->getKodeKabupaten(),
        "alamatkantor" => $user_sso->getProvinsi(), // Gunakan provinsi sebagai alamat
        "provinsi" => $user_sso->getProvinsi(),
        "kabupaten" => $user_sso->getKabupaten(),
        "golongan" => $user_sso->getGolongan(),
        "jabatan" => $user_sso->getJabatan(),
        "foto" => $user_sso->getUrlFoto(),
        "eselon" => $user_sso->getEselon(),
    );
    
    // Tentukan unit kerja
    $unit_kerja = determineUnitKerja($user_sso->getKodeOrganisasi());
    $data['unit_kerja'] = $unit_kerja;
    
    // Tambahkan data dari API pegawai jika ada
    if ($pegawai_data && is_array($pegawai_data) && count($pegawai_data) > 0) {
        $data['pegawai_api_data'] = $pegawai_data;
    }
    
    return $data;
}

// Fungsi untuk mendapatkan filter wilayah berdasarkan unit kerja
function getWilayahFilter($user_data) {
    $unit_kerja = $user_data['unit_kerja'] ?? array();
    $level = $unit_kerja['level'] ?? 'unknown';
    
    $filter = array(
        'level' => $level,
        'can_view_all' => false,
        'provinsi_filter' => array(),
        'kabupaten_filter' => array()
    );
    
    switch ($level) {
        case 'pusat':
            $filter['can_view_all'] = true;
            $filter['provinsi_filter'] = 'all';
            $filter['kabupaten_filter'] = 'all';
            break;
            
        case 'provinsi':
            $filter['provinsi_filter'] = array($unit_kerja['kode_provinsi']);
            $filter['kabupaten_filter'] = 'all_in_province';
            break;
            
        case 'kabupaten':
            $filter['provinsi_filter'] = array($unit_kerja['kode_provinsi']);
            $filter['kabupaten_filter'] = array($unit_kerja['kode_kabupaten']);
            break;
    }
    
    return $filter;
}

// Fungsi untuk memulai session jika belum ada
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Fungsi untuk cek apakah user sudah login
function isLoggedIn() {
    startSession();
    return isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in'] === true && !empty($_SESSION['sso_username']);
}

// Fungsi untuk mendapatkan data user dari session (format baru SSO)
function getUserData() {
    startSession();
    
    if (!isLoggedIn()) {
        return null;
    }
    
    // Return data dalam format yang diharapkan oleh sso_integration.php
    return array(
        'nama' => $_SESSION['sso_nama'] ?? '',
        'email' => $_SESSION['sso_email'] ?? '',
        'username' => $_SESSION['sso_username'] ?? '',
        'nip' => $_SESSION['sso_nip'] ?? '',
        'nipbaru' => $_SESSION['sso_nipbaru'] ?? '',
        'jabatan' => $_SESSION['sso_jabatan'] ?? '',
        'golongan' => $_SESSION['sso_golongan'] ?? '',
        'eselon' => $_SESSION['sso_eselon'] ?? '',
        'kodeorganisasi_full' => $_SESSION['sso_kode_organisasi'] ?? '',
        'kodeprovinsi' => $_SESSION['sso_prov'] ?? '00',
        'kodekabupaten' => $_SESSION['sso_kab'] ?? '00',
        'provinsi' => $_SESSION['sso_provinsi'] ?? '',
        'kabupaten' => $_SESSION['sso_kabupaten'] ?? '',
        'alamatkantor' => $_SESSION['sso_alamat_kantor'] ?? '',
        'foto' => $_SESSION['sso_foto'] ?? '',
        'unit_kerja' => $_SESSION['sso_unit_kerja'] ?? 'kabupaten',
        'login_time' => $_SESSION['sso_login_time'] ?? time()
    );
}

// Fungsi untuk mendapatkan unit kerja user
function getUserUnitKerja() {
    $user_data = getUserData();
    return isset($user_data['unit_kerja']) ? $user_data['unit_kerja'] : null;
}

// Fungsi untuk mendapatkan filter wilayah user
function getUserWilayahFilter() {
    $user_data = getUserData();
    if ($user_data) {
        return getWilayahFilter($user_data);
    }
    return null;
}

// Fungsi untuk logout
function logout() {
    startSession();
    
    // Hapus session SSO
    unset($_SESSION['sso_logged_in']);
    unset($_SESSION['sso_username']);
    unset($_SESSION['sso_nama']);
    unset($_SESSION['sso_email']);
    unset($_SESSION['sso_nip']);
    unset($_SESSION['sso_nipbaru']);
    unset($_SESSION['sso_jabatan']);
    unset($_SESSION['sso_golongan']);
    unset($_SESSION['sso_eselon']);
    unset($_SESSION['sso_prov']);
    unset($_SESSION['sso_kab']);
    unset($_SESSION['sso_unit_kerja']);
    unset($_SESSION['sso_kode_organisasi']);
    unset($_SESSION['sso_provinsi']);
    unset($_SESSION['sso_kabupaten']);
    unset($_SESSION['sso_alamat_kantor']);
    unset($_SESSION['sso_foto']);
    unset($_SESSION['sso_login_time']);
    
    // Hapus session lama juga untuk backward compatibility
    unset($_SESSION['user_data']);
    
    // Destroy session
    session_destroy();
}