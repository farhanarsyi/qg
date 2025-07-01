<?php
// sso_integration.php - Integrasi SSO untuk Dashboard dan Monitoring dengan Filtering Wilayah
require_once 'vendor/autoload.php';
require_once 'sso_config.php';

// Fungsi untuk mendapatkan filter wilayah berdasarkan data SSO user
function getSSOWilayahFilter() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_data = getUserData();
    $unit_kerja = $user_data['unit_kerja'] ?? 'kabupaten';
    
    $filter = array(
        'unit_kerja' => $unit_kerja,
        'kode_provinsi' => $user_data['kodeprovinsi'] ?? '',
        'kode_kabupaten' => $user_data['kodekabupaten'] ?? '',
        'nama_provinsi' => $user_data['provinsi'] ?? '',
        'nama_kabupaten' => $user_data['kabupaten'] ?? '',
        'nama_user' => $user_data['nama'] ?? '',
        'jabatan' => $user_data['jabatan'] ?? '',
        'can_access_all_indonesia' => ($unit_kerja === 'pusat'),
        'can_access_province' => ($unit_kerja === 'pusat' || $unit_kerja === 'provinsi'),
        'restricted_to_kabupaten' => ($unit_kerja === 'kabupaten')
    );
    
    return $filter;
}

// Fungsi untuk generate SQL WHERE clause berdasarkan wilayah user
function getWilayahSQLFilter($table_alias = '') {
    $filter = getSSOWilayahFilter();
    
    if (!$filter) {
        return "1=0"; // Tidak ada akses jika tidak login
    }
    
    $prefix = $table_alias ? $table_alias . '.' : '';
    
    switch ($filter['unit_kerja']) {
        case 'pusat':
            // Pusat bisa akses semua wilayah
            return "1=1";
            
        case 'provinsi':
            // Provinsi hanya bisa akses wilayah provinsinya
            return "{$prefix}kode_provinsi = '" . $filter['kode_provinsi'] . "'";
            
        case 'kabupaten':
        default:
            // Kabupaten hanya bisa akses wilayah kabupatennya
            return "{$prefix}kode_provinsi = '" . $filter['kode_provinsi'] . "' AND {$prefix}kode_kabupaten = '" . $filter['kode_kabupaten'] . "'";
    }
}

// Fungsi untuk generate JavaScript filter options untuk frontend
function getWilayahJSOptions() {
    $filter = getSSOWilayahFilter();
    
    if (!$filter) {
        return json_encode(array('error' => 'Not logged in'));
    }
    
    $options = array(
        'unitKerja' => $filter['unit_kerja'],
        'kodeProvinsi' => $filter['kode_provinsi'],
        'kodeKabupaten' => $filter['kode_kabupaten'],
        'namaProvinsi' => $filter['nama_provinsi'],
        'namaKabupaten' => $filter['nama_kabupaten'],
        'canAccessAllIndonesia' => $filter['can_access_all_indonesia'],
        'canAccessProvince' => $filter['can_access_province'],
        'restrictedToKabupaten' => $filter['restricted_to_kabupaten'],
        'filterLabel' => getWilayahFilterLabel($filter)
    );
    
    return json_encode($options);
}

// Fungsi untuk mendapatkan label filter yang user-friendly
function getWilayahFilterLabel($filter) {
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

// Fungsi untuk render navbar dengan info user SSO
function renderSSONavbar($active_page = 'dashboard') {
    if (!isLoggedIn()) {
        // Redirect ke login jika belum login
        header('Location: sso_login.php');
        exit;
    }
    
    $user_data = getUserData();
    $filter = getSSOWilayahFilter();
    $user_initial = substr($user_data['nama'] ?? 'U', 0, 1);
    
    echo '<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-chart-line me-2"></i>Quality Gates Dashboard
            </a>
            
            <!-- Navigation Tabs in Navbar -->
            <div class="navbar-nav-tabs">
                <a href="index.php" class="nav-link ' . ($active_page === 'dashboard' ? 'active' : '') . '">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a href="monitoring.php" class="nav-link ' . ($active_page === 'monitoring' ? 'active' : '') . '">
                    <i class="fas fa-monitor-heart-rate"></i>Monitoring
                </a>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Wilayah Filter Info -->
                <div class="wilayah-info text-white text-end" style="font-size: 0.7rem;">
                    <div style="font-weight: 600;">Cakupan Data:</div>
                    <div style="opacity: 0.9;">' . getWilayahFilterLabel($filter) . '</div>
                </div>
                
                <!-- User Info -->
                <div class="user-info">
                    <div class="user-avatar">' . strtoupper($user_initial) . '</div>
                    <div class="user-details">
                        <div class="user-name">' . htmlspecialchars($user_data['nama'] ?? 'User') . '</div>
                        <div class="user-role">' . htmlspecialchars($user_data['jabatan'] ?? 'Pegawai') . '</div>
                    </div>
                </div>
                
                <!-- Profile & Logout -->
                <div class="dropdown">
                    <button class="btn btn-logout dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php">
                            <i class="fas fa-user me-2"></i>Profile
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="sso_logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>';
}

// Fungsi untuk render info box wilayah kerja
function renderWilayahInfoBox() {
    $filter = getSSOWilayahFilter();
    
    if (!$filter) return '';
    
    $badge_color = '';
    $icon = '';
    
    switch ($filter['unit_kerja']) {
        case 'pusat':
            $badge_color = 'bg-primary';
            $icon = 'fas fa-building';
            break;
        case 'provinsi':
            $badge_color = 'bg-success';
            $icon = 'fas fa-map-marked-alt';
            break;
        case 'kabupaten':
        default:
            $badge_color = 'bg-info';
            $icon = 'fas fa-map-marker-alt';
            break;
    }
    
    echo '<div class="alert alert-light border-start border-4 border-primary mb-3" style="border-left-color: var(--primary-color) !important;">
        <div class="d-flex align-items-center">
            <div class="badge ' . $badge_color . ' me-3" style="padding: 0.5rem;">
                <i class="' . $icon . '"></i>
            </div>
            <div>
                <div class="fw-bold mb-1">Cakupan Data Anda:</div>
                <div class="text-muted">' . getWilayahFilterLabel($filter) . '</div>
                <small class="text-secondary">
                    Data yang ditampilkan telah disesuaikan dengan wilayah kerja Anda
                </small>
            </div>
        </div>
    </div>';
}

// Fungsi untuk inject JavaScript filter options
function injectWilayahJS() {
    $options = getWilayahJSOptions();
    
    echo '<script>
    // Global wilayah filter options dari SSO
    window.ssoWilayahFilter = ' . $options . ';
    
    // Helper function untuk mendapatkan filter SQL
    window.getSQLFilter = function(tableAlias = "") {
        const filter = window.ssoWilayahFilter;
        const prefix = tableAlias ? tableAlias + "." : "";
        
        switch (filter.unitKerja) {
            case "pusat":
                return "1=1";
            case "provinsi":
                return prefix + "kode_provinsi = \'" + filter.kodeProvinsi + "\'";
            case "kabupaten":
            default:
                return prefix + "kode_provinsi = \'" + filter.kodeProvinsi + "\' AND " + 
                       prefix + "kode_kabupaten = \'" + filter.kodeKabupaten + "\'";
        }
    };
    
    // Helper function untuk update UI berdasarkan filter
    window.updateFilterUI = function() {
        const filter = window.ssoWilayahFilter;
        
        // Disable/enable wilayah dropdowns berdasarkan akses
        if (filter.restrictedToKabupaten) {
            // Kabupaten: lock both provinsi dan kabupaten
            $("select[name*=\'provinsi\'], select[name*=\'province\']").val(filter.kodeProvinsi).prop("disabled", true);
            $("select[name*=\'kabupaten\'], select[name*=\'regency\']").val(filter.kodeKabupaten).prop("disabled", true);
        } else if (!filter.canAccessAllIndonesia && filter.canAccessProvince) {
            // Provinsi: lock provinsi, allow kabupaten selection
            $("select[name*=\'provinsi\'], select[name*=\'province\']").val(filter.kodeProvinsi).prop("disabled", true);
        }
        // Pusat: no restrictions
        
        // Add visual indicators
        $("select:disabled").addClass("bg-light").attr("title", "Dikunci sesuai wilayah kerja Anda");
    };
    
    // Auto-run on page load
    $(document).ready(function() {
        if (window.ssoWilayahFilter && !window.ssoWilayahFilter.error) {
            window.updateFilterUI();
        }
    });
    </script>';
}

// Fungsi untuk memastikan user sudah login SSO
function requireSSOLogin($redirect_page = null) {
    if (!isLoggedIn()) {
        $redirect_url = 'sso_login.php';
        if ($redirect_page) {
            $redirect_url .= '?redirect=' . urlencode($redirect_page);
        }
        header('Location: ' . $redirect_url);
        exit;
    }
}

// Fungsi untuk render debug info (development only)
function renderDebugWilayahInfo() {
    if (!isset($_GET['debug'])) return;
    
    $filter = getSSOWilayahFilter();
    echo '<div class="alert alert-warning mt-3">
        <strong>Debug Wilayah Filter:</strong>
        <pre>' . print_r($filter, true) . '</pre>
        <strong>SQL Filter:</strong> <code>' . getWilayahSQLFilter('p') . '</code>
    </div>';
}
?> 