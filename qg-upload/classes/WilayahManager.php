<?php
// classes/WilayahManager.php - Regional Access Management

class WilayahManager {
    private $ssoManager;
    private $daerahMap;
    
    public function __construct(SSOManager $ssoManager) {
        $this->ssoManager = $ssoManager;
        $this->loadDaerahMap();
    }
    
    /**
     * Load daerah mapping from CSV file
     */
    private function loadDaerahMap() {
        $this->daerahMap = [];
        $csvFile = 'daftar_daerah.csv';
        
        if (file_exists($csvFile)) {
            $handle = fopen($csvFile, 'r');
            if ($handle) {
                // Skip header
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) >= 3) {
                        $kode = $data[0];
                        $nama = $data[1];
                        $this->daerahMap[$kode] = $nama;
                    }
                }
                fclose($handle);
            }
        }
    }
    
    /**
     * Get nama daerah by kode
     */
    public function getNamaDaerah($kode) {
        return $this->daerahMap[$kode] ?? 'Tidak Diketahui';
    }
    
    /**
     * Get wilayah filter based on SSO user
     */
    public function getWilayahFilter() {
        if (!$this->ssoManager->isLoggedIn()) {
            return null;
        }
        
        $userData = $this->ssoManager->getUserData();
        $unitKerja = $userData['unit_kerja'] ?? 'kabupaten';
        
        $filter = [
            'unit_kerja' => $unitKerja,
            'kode_provinsi' => $userData['kodeprovinsi'] ?? '',
            'kode_kabupaten' => $userData['kodekabupaten'] ?? '',
            'nama_provinsi' => $userData['provinsi'] ?? '',
            'nama_kabupaten' => $userData['kabupaten'] ?? '',
            'nama_user' => $userData['nama'] ?? '',
            'jabatan' => $userData['jabatan'] ?? '',
            'can_access_all_indonesia' => ($unitKerja === 'pusat'),
            'can_access_province' => ($unitKerja === 'pusat' || $unitKerja === 'provinsi'),
            'restricted_to_kabupaten' => ($unitKerja === 'kabupaten')
        ];
        
        return $filter;
    }
    
    /**
     * Generate SQL WHERE clause based on user's regional access
     */
    public function getSQLFilter($tableAlias = '') {
        $filter = $this->getWilayahFilter();
        
        if (!$filter) {
            return "1=0"; // No access if not logged in
        }
        
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        
        switch ($filter['unit_kerja']) {
            case 'pusat':
                return "1=1"; // Central office can access all regions
                
            case 'provinsi':
                return "{$prefix}kode_provinsi = '" . $filter['kode_provinsi'] . "'";
                
            case 'kabupaten':
            default:
                return "{$prefix}kode_provinsi = '" . $filter['kode_provinsi'] . "' AND {$prefix}kode_kabupaten = '" . $filter['kode_kabupaten'] . "'";
        }
    }
    
    /**
     * Get JavaScript options for frontend filtering
     */
    public function getJSOptions() {
        $filter = $this->getWilayahFilter();
        
        if (!$filter) {
            return json_encode(['error' => 'Not logged in']);
        }
        
        $options = [
            'unitKerja' => $filter['unit_kerja'],
            'kodeProvinsi' => $filter['kode_provinsi'],
            'kodeKabupaten' => $filter['kode_kabupaten'],
            'namaProvinsi' => $filter['nama_provinsi'],
            'namaKabupaten' => $filter['nama_kabupaten'],
            'canAccessAllIndonesia' => $filter['can_access_all_indonesia'],
            'canAccessProvince' => $filter['can_access_province'],
            'restrictedToKabupaten' => $filter['restricted_to_kabupaten'],
            'filterLabel' => $this->getFilterLabel($filter)
        ];
        
        return json_encode($options);
    }
    
    /**
     * Get user-friendly filter label
     */
    public function getFilterLabel($filter) {
        switch ($filter['unit_kerja']) {
            case 'pusat':
                return "Seluruh Indonesia (Akses Pusat)";
                
            case 'provinsi':
                return "Provinsi " . $filter['nama_provinsi'];
                
            case 'kabupaten':
            default:
                return $filter['nama_kabupaten'] . ", " . $filter['nama_provinsi'];
        }
    }
} 