<?php
// sso_integration.php - Integrasi SSO untuk Dashboard dan Monitoring dengan Filtering Wilayah

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

require_once 'vendor/autoload.php';
require_once 'sso_config.php';

// Fungsi untuk mengecek apakah user adalah superadmin
function isSuperAdmin() {
    if (!isLoggedIn()) {
        return false;
    }
    
    $user_data = getUserData();
    $username = $user_data['username'] ?? '';
    
    // Username farhan.arsyi adalah superadmin
    return ($username === 'farhan.arsyi');
}

// Fungsi untuk mendapatkan filter wilayah berdasarkan data SSO user dengan dukungan superadmin
function getSSOWilayahFilter() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $user_data = getUserData();
    $is_superadmin = isSuperAdmin();
    
    // Jika superadmin sedang dalam mode imitation, gunakan data dari session
    if ($is_superadmin && isset($_SESSION['superadmin_imitation'])) {
        $imitation_data = $_SESSION['superadmin_imitation'];
        
        $filter = array(
            'unit_kerja' => $imitation_data['unit_kerja'],
            'kode_provinsi' => $imitation_data['kode_provinsi'],
            'kode_kabupaten' => $imitation_data['kode_kabupaten'],
            'nama_provinsi' => $imitation_data['nama_provinsi'],
            'nama_kabupaten' => $imitation_data['nama_kabupaten'],
            'nama_user' => $user_data['nama'] ?? '',
            'jabatan' => $user_data['jabatan'] ?? '',
            'can_access_all_indonesia' => ($imitation_data['unit_kerja'] === 'pusat'),
            'can_access_province' => ($imitation_data['unit_kerja'] === 'pusat' || $imitation_data['unit_kerja'] === 'provinsi'),
            'restricted_to_kabupaten' => ($imitation_data['unit_kerja'] === 'kabupaten'),
            'is_superadmin' => true,
            'is_imitating' => true,
            'original_unit_kerja' => $user_data['unit_kerja'] ?? 'kabupaten'
        );
        
        error_log('Superadmin Filter Debug - Imitating: ' . $imitation_data['unit_kerja'] . ' - ' . $imitation_data['nama_provinsi'] . ' - ' . $imitation_data['nama_kabupaten']);
        
        return $filter;
    }
    
    // Normal user atau superadmin dalam mode normal
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
        'restricted_to_kabupaten' => ($unit_kerja === 'kabupaten'),
        'is_superadmin' => $is_superadmin,
        'is_imitating' => false
    );
    
    // Debug log untuk troubleshooting
    error_log('Filter Debug - Unit Kerja: ' . $unit_kerja);
    error_log('Filter Debug - Kode Provinsi: ' . ($user_data['kodeprovinsi'] ?? 'empty'));
    error_log('Filter Debug - Kode Kabupaten: ' . ($user_data['kodekabupaten'] ?? 'empty'));
    error_log('Filter Debug - Is Superadmin: ' . ($is_superadmin ? 'Yes' : 'No'));
    
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
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="monitoring.php" class="nav-link ' . ($active_page === 'monitoring' ? 'active' : '') . '">
                    <i class="fas fa-tasks"></i> Monitoring
                </a>
            </div>
            
            <div class="d-flex align-items-center gap-3">
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
                    <ul class="dropdown-menu dropdown-menu-end">' . 
                        (isSuperAdmin() ? '
                        <li><h6 class="dropdown-header">
                            <i class="fas fa-crown me-2 text-warning"></i>Superadmin
                        </h6></li>
                        <li><a class="dropdown-item" href="#" onclick="openSuperAdminModal()">
                            <i class="fas fa-exchange-alt me-2"></i>Switch Role
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="resetSuperAdminRole()">
                            <i class="fas fa-undo me-2"></i>Reset to Original
                        </a></li>
                        <li><hr class="dropdown-divider"></li>' : '') . '
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
    $border_color = 'var(--primary-color)';
    
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
    
    // Jika superadmin sedang imitating, ubah warna border dan tambahkan indicator
    if (isset($filter['is_imitating']) && $filter['is_imitating']) {
        $border_color = '#ffc107';
        $superadmin_badge = '<span class="badge bg-warning text-dark ms-2">
            <i class="fas fa-crown me-1"></i>Superadmin Mode
        </span>';
    } else {
        $superadmin_badge = '';
    }
    
    echo '<div class="alert alert-light border-start border-4 mb-3" style="border-left-color: ' . $border_color . ' !important;">
        <div class="d-flex align-items-center">
            <div class="badge ' . $badge_color . ' me-3" style="padding: 0.5rem;">
                <i class="' . $icon . '"></i>
            </div>
            <div class="flex-grow-1">
                <div class="fw-bold mb-1">Cakupan Data Anda:' . $superadmin_badge . '</div>
                <div class="text-muted">' . getWilayahFilterLabel($filter) . '</div>
                <small class="text-secondary">
                    ' . (isset($filter['is_imitating']) && $filter['is_imitating'] ? 
                        'Anda sedang mengimitasi akses wilayah ini sebagai superadmin' : 
                        'Data yang ditampilkan telah disesuaikan dengan wilayah kerja Anda') . '
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
    
    // Superadmin functions
    window.openSuperAdminModal = function() {
        $("#superAdminModal").modal("show");
    };
    
    window.resetSuperAdminRole = function() {
        if (confirm("Apakah Anda yakin ingin kembali ke role asli?")) {
            $.ajax({
                url: "api.php",
                method: "POST",
                data: {
                    action: "reset_superadmin_role"
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Terjadi kesalahan saat reset role");
                }
            });
        }
    };
    
    window.switchSuperAdminRole = function() {
        const role = $("#superadminRole").val();
        const provinsi = $("#superadminProvinsi").val();
        const kabupaten = $("#superadminKabupaten").val();
        
        if (!role) {
            alert("Pilih role terlebih dahulu");
            return;
        }
        
        if (role === "provinsi" && !provinsi) {
            alert("Pilih provinsi terlebih dahulu");
            return;
        }
        
        if (role === "kabupaten" && (!provinsi || !kabupaten)) {
            alert("Pilih provinsi dan kabupaten terlebih dahulu");
            return;
        }
        
        $.ajax({
            url: "api.php",
            method: "POST",
            data: {
                action: "switch_superadmin_role",
                role: role,
                provinsi: provinsi,
                kabupaten: kabupaten
            },
            success: function(response) {
                if (response.status) {
                    $("#superAdminModal").modal("hide");
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function() {
                alert("Terjadi kesalahan saat switch role");
            }
        });
    };
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

// Fungsi untuk render modal superadmin
function renderSuperAdminModal() {
    if (!isSuperAdmin()) return '';
    
    echo '
    <!-- Superadmin Role Switching Modal -->
    <div class="modal fade" id="superAdminModal" tabindex="-1" aria-labelledby="superAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="superAdminModalLabel">
                        <i class="fas fa-crown me-2"></i>Superadmin - Switch Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Mode Superadmin:</strong> Anda dapat mengimitasi akses wilayah dan privilege seperti user daerah tertentu.
                    </div>
                    
                    <form id="superAdminForm">
                        <div class="mb-3">
                            <label for="superadminRole" class="form-label">Pilih Role:</label>
                            <select class="form-select" id="superadminRole" onchange="handleRoleChange()">
                                <option value="">-- Pilih Role --</option>
                                <option value="pusat">Pusat (Akses Seluruh Indonesia)</option>
                                <option value="provinsi">Provinsi</option>
                                <option value="kabupaten">Kabupaten/Kota</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="provinsiContainer" style="display: none;">
                            <label for="superadminProvinsi" class="form-label">Pilih Provinsi:</label>
                            <select class="form-select" id="superadminProvinsi" onchange="handleProvinsiChange()">
                                <option value="">-- Pilih Provinsi --</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="kabupatenContainer" style="display: none;">
                            <label for="superadminKabupaten" class="form-label">Pilih Kabupaten/Kota:</label>
                            <select class="form-select" id="superadminKabupaten">
                                <option value="">-- Pilih Kabupaten/Kota --</option>
                            </select>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Setelah switch role, Anda akan melihat data dan memiliki akses persis seperti user daerah yang dipilih.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" onclick="switchSuperAdminRole()">
                        <i class="fas fa-exchange-alt me-2"></i>Switch Role
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Load daerah data for superadmin modal
    function loadDaerahForSuperadmin() {
        fetch("daftar_daerah.php")
            .then(response => response.json())
            .then(data => {
                const provinsiSelect = document.getElementById("superadminProvinsi");
                const kabupatenSelect = document.getElementById("superadminKabupaten");
                
                // Clear existing options
                provinsiSelect.innerHTML = "<option value=\"\">-- Pilih Provinsi --</option>";
                kabupatenSelect.innerHTML = "<option value=\"\">-- Pilih Kabupaten/Kota --</option>";
                
                // Populate provinsi
                data.forEach(provinsi => {
                    const option = document.createElement("option");
                    option.value = provinsi.kode;
                    option.textContent = provinsi.nama;
                    provinsiSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error("Error loading daerah data:", error);
            });
    }
    
    function handleRoleChange() {
        const role = document.getElementById("superadminRole").value;
        const provinsiContainer = document.getElementById("provinsiContainer");
        const kabupatenContainer = document.getElementById("kabupatenContainer");
        
        if (role === "pusat") {
            provinsiContainer.style.display = "none";
            kabupatenContainer.style.display = "none";
        } else if (role === "provinsi") {
            provinsiContainer.style.display = "block";
            kabupatenContainer.style.display = "none";
        } else if (role === "kabupaten") {
            provinsiContainer.style.display = "block";
            kabupatenContainer.style.display = "block";
        }
    }
    
    function handleProvinsiChange() {
        const provinsiCode = document.getElementById("superadminProvinsi").value;
        const kabupatenSelect = document.getElementById("superadminKabupaten");
        
        // Clear kabupaten options
        kabupatenSelect.innerHTML = "<option value=\"\">-- Pilih Kabupaten/Kota --</option>";
        
        if (provinsiCode) {
            fetch("daftar_daerah.php")
                .then(response => response.json())
                .then(data => {
                    const selectedProvinsi = data.find(p => p.kode === provinsiCode);
                    if (selectedProvinsi && selectedProvinsi.kabupaten) {
                        selectedProvinsi.kabupaten.forEach(kabupaten => {
                            const option = document.createElement("option");
                            option.value = kabupaten.kode;
                            option.textContent = kabupaten.nama;
                            kabupatenSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error("Error loading kabupaten data:", error);
                });
        }
    }
    
    // Load daerah data when modal is shown
    document.getElementById("superAdminModal").addEventListener("shown.bs.modal", function() {
        loadDaerahForSuperadmin();
    });
    </script>';
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