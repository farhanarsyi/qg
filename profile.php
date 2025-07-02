<?php
// profile.php - Halaman profil pegawai dengan menu navigasi
require_once 'sso_config.php';

startSession();

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header('Location: sso_login.php');
    exit;
}

$user_data = getUserData();

// Fungsi untuk mendapatkan nilai atribut pegawai
function getAttributeValue($pegawai_data, $key) {
    if (is_array($pegawai_data) && count($pegawai_data) > 0) {
        $first_result = $pegawai_data[0];
        if (isset($first_result['attributes'][$key]) && is_array($first_result['attributes'][$key])) {
            return $first_result['attributes'][$key][0] ?? '';
        }
    }
    return '';
}

// Fungsi untuk mendapatkan username
function getUsername($pegawai_data) {
    if (is_array($pegawai_data) && count($pegawai_data) > 0) {
        return $pegawai_data[0]['username'] ?? '';
    }
    return '';
}

// Fungsi untuk mendapatkan nilai dari session user data
function getUserValue($user_data, $key, $default = '') {
    return isset($user_data[$key]) ? $user_data[$key] : $default;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pegawai - Quality Gates</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Navbar CSS -->
    <link rel="stylesheet" href="navbar.css">
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --neutral-color: #6b7280;
            --light-color: #f9fafb;
            --dark-color: #111827;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: var(--dark-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        /* Navbar styles are in navbar.css */
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .profile-header {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
            font-size: 3rem;
            color: white;
            font-weight: 600;
        }
        
        .profile-name {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .profile-position {
            font-size: 1.1rem;
            color: var(--neutral-color);
            margin-bottom: 1rem;
        }
        
        .profile-nip {
            display: inline-block;
            background: rgba(5, 150, 105, 0.1);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            padding: 1.5rem;
        }
        
        .profile-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .profile-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .profile-item:last-child {
            border-bottom: none;
        }
        
        .profile-label {
            font-weight: 500;
            color: var(--neutral-color);
            font-size: 0.9rem;
        }
        
        .profile-value {
            font-weight: 500;
            color: var(--dark-color);
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }
        
        .menu-section {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 2rem;
        }
        
        .menu-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        
        .menu-card {
            background: linear-gradient(135deg, rgba(5, 150, 105, 0.05), rgba(16, 185, 129, 0.05));
            border: 2px solid rgba(5, 150, 105, 0.1);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(5, 150, 105, 0.15);
            border-color: var(--primary-color);
            text-decoration: none;
            color: inherit;
        }
        
        .menu-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            color: white;
        }
        
        .menu-card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .menu-card-desc {
            color: var(--neutral-color);
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 1rem auto;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
            }
            
            .profile-name {
                font-size: 1.5rem;
            }
            
            .profile-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .menu-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container-fluid">
            <span class="navbar-brand">
                <i class="fas fa-chart-line me-2"></i>
                Dashboard Quality Gates - BPS
            </span>
            <div>
                <a href="sso_logout.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php 
                $nama = getUserValue($user_data, 'nama', $user_info['name'] ?? 'User');
                echo strtoupper(substr($nama, 0, 1));
                ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($nama); ?></h1>
            <p class="profile-position"><?php echo htmlspecialchars(getUserValue($user_data, 'jabatan', 'Pegawai BPS')); ?></p>
            <div class="profile-nip">
                <i class="fas fa-id-badge me-2"></i>
                NIP: <?php echo htmlspecialchars(getUserValue($user_data, 'nip', 'N/A')); ?>
            </div>
            
            <?php 
            $unit_kerja = getUserValue($user_data, 'unit_kerja', array());
            if (!empty($unit_kerja) && isset($unit_kerja['nama_level'])):
            ?>
            <div class="profile-nip" style="background: rgba(16, 185, 129, 0.1); color: #10b981; margin-top: 0.5rem;">
                <i class="fas fa-building me-2"></i>
                Unit Kerja: <?php echo htmlspecialchars($unit_kerja['nama_level']); ?>
                <?php if (isset($unit_kerja['level']) && $unit_kerja['level'] != 'pusat'): ?>
                    - <?php echo htmlspecialchars(getUserValue($user_data, 'provinsi')); ?>
                    <?php if ($unit_kerja['level'] == 'kabupaten'): ?>
                        / <?php echo htmlspecialchars(getUserValue($user_data, 'kabupaten')); ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Profile Details -->
        <div class="profile-grid">
            <!-- Informasi Personal -->
            <div class="profile-card">
                <h5><i class="fas fa-user"></i> Informasi Personal</h5>
                <div class="profile-item">
                    <span class="profile-label">Nama Lengkap</span>
                    <span class="profile-value"><?php echo htmlspecialchars($nama); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Username</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'username', $user_info['preferred_username'] ?? 'N/A')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Email</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'email', $user_info['email'] ?? 'N/A')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">NIP</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'nip')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">NIP Baru</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'nipbaru')); ?></span>
                </div>
            </div>

            <!-- Informasi Jabatan -->
            <div class="profile-card">
                <h5><i class="fas fa-briefcase"></i> Informasi Jabatan</h5>
                <div class="profile-item">
                    <span class="profile-label">Jabatan</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'jabatan')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Eselon</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'eselon')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Golongan</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'golongan')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Kode Organisasi</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'kodeorganisasi')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Kode Organisasi Lengkap</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'kodeorganisasi_full')); ?></span>
                </div>
            </div>

            <!-- Informasi Lokasi -->
            <div class="profile-card">
                <h5><i class="fas fa-map-marker-alt"></i> Informasi Lokasi</h5>
                <div class="profile-item">
                    <span class="profile-label">Provinsi</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'provinsi')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Kabupaten/Kota</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'kabupaten')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Kode Provinsi</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'kodeprovinsi')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Kode Kabupaten</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'kodekabupaten')); ?></span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Alamat Kantor</span>
                    <span class="profile-value"><?php echo htmlspecialchars(getUserValue($user_data, 'alamatkantor')); ?></span>
                </div>
            </div>

            <!-- Informasi Unit Kerja & Akses Wilayah -->
            <?php 
            $unit_kerja = getUserValue($user_data, 'unit_kerja', array());
            $wilayah_filter = getUserWilayahFilter();
            if (!empty($unit_kerja) || !empty($wilayah_filter)): 
            ?>
            <div class="profile-card">
                <h5><i class="fas fa-shield-alt"></i> Unit Kerja & Akses Wilayah</h5>
                <?php if (!empty($unit_kerja)): ?>
                <div class="profile-item">
                    <span class="profile-label">Level Unit Kerja</span>
                    <span class="profile-value">
                        <span class="badge" style="background: 
                            <?php echo ($unit_kerja['level'] == 'pusat') ? '#059669' : 
                                      (($unit_kerja['level'] == 'provinsi') ? '#10b981' : '#f59e0b'); ?>; 
                            color: white; padding: 0.25rem 0.5rem; border-radius: 4px;">
                            <?php echo htmlspecialchars($unit_kerja['nama_level']); ?>
                        </span>
                    </span>
                </div>
                <?php if ($unit_kerja['level'] != 'pusat'): ?>
                <div class="profile-item">
                    <span class="profile-label">Kode Unit</span>
                    <span class="profile-value"><?php echo htmlspecialchars($unit_kerja['kode_unit']); ?></span>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <?php if (!empty($wilayah_filter)): ?>
                <div class="profile-item">
                    <span class="profile-label">Akses Data</span>
                    <span class="profile-value">
                        <?php if ($wilayah_filter['can_view_all']): ?>
                            <span style="color: #059669; font-weight: 600;">✓ Seluruh Indonesia</span>
                        <?php elseif ($wilayah_filter['level'] == 'provinsi'): ?>
                            <span style="color: #10b981; font-weight: 600;">✓ Provinsi <?php echo htmlspecialchars(getUserValue($user_data, 'provinsi')); ?></span>
                        <?php elseif ($wilayah_filter['level'] == 'kabupaten'): ?>
                            <span style="color: #f59e0b; font-weight: 600;">✓ <?php echo htmlspecialchars(getUserValue($user_data, 'kabupaten')); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="profile-item">
                    <span class="profile-label">Filter Monitoring</span>
                    <span class="profile-value">
                        <small style="color: #6b7280;">
                            <?php if ($wilayah_filter['can_view_all']): ?>
                                Data dari seluruh provinsi dan kabupaten/kota
                            <?php elseif ($wilayah_filter['level'] == 'provinsi'): ?>
                                Data dari semua kabupaten/kota di provinsi <?php echo htmlspecialchars(getUserValue($user_data, 'provinsi')); ?>
                            <?php elseif ($wilayah_filter['level'] == 'kabupaten'): ?>
                                Data khusus untuk <?php echo htmlspecialchars(getUserValue($user_data, 'kabupaten')); ?>
                            <?php endif; ?>
                        </small>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Menu Navigation -->
        <div class="menu-section">
            <h2 class="menu-title">Pilih Menu Aplikasi</h2>
            <div class="menu-grid">
                <a href="index.php" class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="menu-card-title">Dashboard</h3>
                    <p class="menu-card-desc">Dashboard per wilayah untuk Quality Gates dengan visualisasi data dan analytics</p>
                </a>

                <a href="monitoring.php" class="menu-card">
                    <div class="menu-icon">
                        <i class="fas fa-desktop"></i>
                    </div>
                    <h3 class="menu-card-title">Monitoring</h3>
                    <p class="menu-card-desc">Monitoring real-time Quality Gates dengan tabel data dan filtering</p>
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 