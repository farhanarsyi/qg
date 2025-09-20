<?php
// contoh_filter_wilayah.php - Contoh implementasi filter wilayah di monitoring/dashboard
require_once 'sso_config.php';

startSession();

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: sso_login.php');
    exit;
}

$user_data = getUserData();
$unit_kerja = getUserUnitKerja();
$wilayah_filter = getUserWilayahFilter();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contoh Filter Wilayah - Quality Gates</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #059669;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --neutral-color: #6b7280;
            --dark-color: #111827;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: var(--dark-color);
            margin: 0;
            padding: 2rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .filter-info {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.05), rgba(16, 185, 129, 0.05));
            border: 1px solid rgba(5, 150, 105, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-pusat { background: #059669; color: white; }
        .badge-provinsi { background: #10b981; color: white; }
        .badge-kabupaten { background: #f59e0b; color: white; }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            margin: 0.25rem;
            display: inline-block;
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-secondary { background: var(--neutral-color); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-filter"></i> Contoh Implementasi Filter Wilayah</h1>
        <p class="text-muted">Cara menggunakan filter wilayah berdasarkan unit kerja pegawai untuk monitoring dan dashboard Quality Gates.</p>
        
        <!-- Info User saat ini -->
        <div class="card">
            <h4><i class="fas fa-user"></i> Informasi User Saat Ini</h4>
            <div class="filter-info">
                <strong>Nama:</strong> <?php echo htmlspecialchars($user_data['nama'] ?? 'N/A'); ?><br>
                <strong>Unit Kerja:</strong> 
                <?php if ($unit_kerja): ?>
                    <span class="badge badge-<?php echo $unit_kerja['level']; ?>">
                        <?php echo htmlspecialchars($unit_kerja['nama_level']); ?>
                    </span>
                <?php else: ?>
                    <span class="badge badge-secondary">Tidak Diketahui</span>
                <?php endif; ?>
                <br>
                <strong>Wilayah:</strong> 
                <?php 
                if ($unit_kerja['level'] == 'pusat') {
                    echo 'Seluruh Indonesia';
                } elseif ($unit_kerja['level'] == 'provinsi') {
                    echo htmlspecialchars($user_data['provinsi'] ?? 'N/A');
                } elseif ($unit_kerja['level'] == 'kabupaten') {
                    echo htmlspecialchars($user_data['kabupaten'] ?? 'N/A');
                }
                ?>
            </div>
        </div>
        
        <!-- Filter Configuration -->
        <div class="card">
            <h4><i class="fas fa-cog"></i> Konfigurasi Filter untuk User Ini</h4>
            <?php if ($wilayah_filter): ?>
            <div class="code-block">
Level: <?php echo htmlspecialchars($wilayah_filter['level']); ?>
Can View All: <?php echo $wilayah_filter['can_view_all'] ? 'true' : 'false'; ?>
Provinsi Filter: <?php echo is_array($wilayah_filter['provinsi_filter']) ? json_encode($wilayah_filter['provinsi_filter']) : $wilayah_filter['provinsi_filter']; ?>
Kabupaten Filter: <?php echo is_array($wilayah_filter['kabupaten_filter']) ? json_encode($wilayah_filter['kabupaten_filter']) : $wilayah_filter['kabupaten_filter']; ?>
            </div>
            <?php else: ?>
            <p class="text-danger">Filter wilayah tidak dapat ditentukan.</p>
            <?php endif; ?>
        </div>
        
        <!-- Contoh Query SQL -->
        <div class="card">
            <h4><i class="fas fa-database"></i> Contoh Query SQL dengan Filter</h4>
            <p>Berikut contoh bagaimana filter ini bisa diterapkan dalam query database:</p>
            
            <?php if ($wilayah_filter): ?>
            <div class="code-block">
<?php
// Contoh query berdasarkan level akses
$base_query = "SELECT * FROM monitoring_data WHERE 1=1";

if ($wilayah_filter['can_view_all']) {
    // User pusat - tidak perlu filter tambahan
    echo $base_query . "\n-- User pusat: akses semua data";
} elseif ($wilayah_filter['level'] == 'provinsi') {
    // User provinsi - filter berdasarkan kode provinsi
    $kode_prov = $user_data['kodeprovinsi'];
    echo $base_query . "\nAND kode_provinsi = '{$kode_prov}'\n-- User provinsi: hanya data provinsi " . htmlspecialchars($user_data['provinsi']);
} elseif ($wilayah_filter['level'] == 'kabupaten') {
    // User kabupaten - filter berdasarkan kode provinsi dan kabupaten
    $kode_prov = $user_data['kodeprovinsi'];
    $kode_kab = $user_data['kodekabupaten'];
    echo $base_query . "\nAND kode_provinsi = '{$kode_prov}' AND kode_kabupaten = '{$kode_kab}'\n-- User kabupaten: hanya data " . htmlspecialchars($user_data['kabupaten']);
}
?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Contoh Filter Form -->
        <div class="card">
            <h4><i class="fas fa-search"></i> Contoh Filter Form untuk UI</h4>
            <p>Form filter yang disesuaikan dengan hak akses user:</p>
            
            <form class="row g-3">
                <?php if ($wilayah_filter && $wilayah_filter['can_view_all']): ?>
                <!-- User pusat: bisa memilih semua provinsi -->
                <div class="col-md-6">
                    <label for="provinsi" class="form-label">Provinsi</label>
                    <select class="form-select" id="provinsi">
                        <option value="">Semua Provinsi</option>
                        <option value="11">Aceh</option>
                        <option value="12">Sumatera Utara</option>
                        <option value="31">DKI Jakarta</option>
                        <option value="32">Jawa Barat</option>
                        <!-- dst... -->
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                    <select class="form-select" id="kabupaten">
                        <option value="">Semua Kabupaten/Kota</option>
                        <!-- Dinamis berdasarkan provinsi terpilih -->
                    </select>
                </div>
                
                <?php elseif ($wilayah_filter && $wilayah_filter['level'] == 'provinsi'): ?>
                <!-- User provinsi: provinsi tetap, bisa pilih kabupaten -->
                <div class="col-md-6">
                    <label for="provinsi" class="form-label">Provinsi</label>
                    <select class="form-select" id="provinsi" disabled>
                        <option value="<?php echo htmlspecialchars($user_data['kodeprovinsi']); ?>" selected>
                            <?php echo htmlspecialchars($user_data['provinsi']); ?>
                        </option>
                    </select>
                    <small class="text-muted">Akses terbatas pada provinsi Anda</small>
                </div>
                <div class="col-md-6">
                    <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                    <select class="form-select" id="kabupaten">
                        <option value="">Semua Kabupaten/Kota di <?php echo htmlspecialchars($user_data['provinsi']); ?></option>
                        <!-- List kabupaten di provinsi user -->
                    </select>
                </div>
                
                <?php elseif ($wilayah_filter && $wilayah_filter['level'] == 'kabupaten'): ?>
                <!-- User kabupaten: provinsi dan kabupaten tetap -->
                <div class="col-md-6">
                    <label for="provinsi" class="form-label">Provinsi</label>
                    <select class="form-select" id="provinsi" disabled>
                        <option value="<?php echo htmlspecialchars($user_data['kodeprovinsi']); ?>" selected>
                            <?php echo htmlspecialchars($user_data['provinsi']); ?>
                        </option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="kabupaten" class="form-label">Kabupaten/Kota</label>
                    <select class="form-select" id="kabupaten" disabled>
                        <option value="<?php echo htmlspecialchars($user_data['kodekabupaten']); ?>" selected>
                            <?php echo htmlspecialchars($user_data['kabupaten']); ?>
                        </option>
                    </select>
                    <small class="text-muted">Akses terbatas pada kabupaten/kota Anda</small>
                </div>
                <?php endif; ?>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filter Data
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Test Links -->
        <div class="card">
            <h4><i class="fas fa-vial"></i> Test Berbagai Level Akses</h4>
            <p>Coba berbagai level akses untuk melihat perbedaan filter:</p>
            
            <a href="demo_unit_levels.php?level=pusat" class="btn btn-primary">
                <i class="fas fa-globe"></i> Test Akses Pusat
            </a>
            <a href="demo_unit_levels.php?level=provinsi" class="btn btn-primary">
                <i class="fas fa-map"></i> Test Akses Provinsi
            </a>
            <a href="demo_unit_levels.php?level=kabupaten" class="btn btn-primary">
                <i class="fas fa-map-marker-alt"></i> Test Akses Kabupaten
            </a>
            
            <br><br>
            
            <a href="profile.php" class="btn btn-secondary">
                <i class="fas fa-user"></i> Kembali ke Profil
            </a>
            <a href="sso_logout.php" class="btn btn-secondary">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 