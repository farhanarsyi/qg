<?php
// File debug untuk melihat data lengkap dari SSO
// Sesuai pertanyaan Farhan: "nanti setelah login, kita bisa jadi tau kah itu akun satker nya dimana"

require_once 'auth-check.php';

// Cek apakah user sudah login
if (!isUserAuthenticated()) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Debug SSO - Not Logged In</title>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .error { background: #fee; padding: 20px; border: 1px solid #fcc; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>❌ User Belum Login</h2>
            <p>Silakan login dulu melalui SSO untuk melihat data debug.</p>
            <a href="login.php">Login</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$user_data = getUserData();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Data SSO BPS</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { background: #059669; color: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .section { margin: 20px 0; }
        .level-analysis { background: #e6f7ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .button { background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px; }
        .button-danger { background: #ef4444; }
        .raw-data { background: #f5f5f5; padding: 15px; border: 1px solid #ddd; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Debug Data SSO BPS</h1>
        <p><strong>Tujuan:</strong> Mengecek data satuan kerja dan informasi lengkap dari SSO (Farhan & Fachrunisa)</p>
    </div>

    <div class="section">
        <h2>🏢 Informasi Satuan Kerja</h2>
        <table>
            <tr><th>Field</th><th>Value</th></tr>
            <tr><td><strong>Nama</strong></td><td><?= htmlspecialchars($user_data['nama'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>NIP</strong></td><td><?= htmlspecialchars($user_data['nip'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>NIP Baru</strong></td><td><?= htmlspecialchars($user_data['nip_baru'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Username</strong></td><td><?= htmlspecialchars($user_data['username'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($user_data['email'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Jabatan</strong></td><td><?= htmlspecialchars($user_data['jabatan'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Golongan</strong></td><td><?= htmlspecialchars($user_data['golongan'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Eselon</strong></td><td><?= htmlspecialchars($user_data['eselon'] ?? 'Tidak tersedia') ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>🗺️ Lokasi Satuan Kerja</h2>
        <table>
            <tr><th>Field</th><th>Value</th></tr>
            <tr><td><strong>Kode Organisasi</strong></td><td><?= htmlspecialchars($user_data['kode_organisasi'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Kode Provinsi</strong></td><td><?= htmlspecialchars($user_data['kode_provinsi'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Kode Kabupaten</strong></td><td><?= htmlspecialchars($user_data['kode_kabupaten'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Nama Provinsi</strong></td><td><?= htmlspecialchars($user_data['provinsi'] ?? 'Tidak tersedia') ?></td></tr>
            <tr><td><strong>Nama Kabupaten</strong></td><td><?= htmlspecialchars($user_data['kabupaten'] ?? 'Tidak tersedia') ?></td></tr>
        </table>
    </div>

    <div class="level-analysis">
        <h2>🎯 Analisis Level Akses</h2>
        <?php 
        $kode_prov = $user_data['kode_provinsi'] ?? '';
        $kode_kab = $user_data['kode_kabupaten'] ?? '';

        if ($kode_prov === '00' && $kode_kab === '00') {
            echo "✅ <strong>AKUN PUSAT</strong> - Dapat mengakses data seluruh Indonesia";
        } elseif ($kode_prov !== '00' && $kode_kab === '00') {
            echo "✅ <strong>AKUN PROVINSI</strong> - Dapat mengakses data provinsi " . htmlspecialchars($user_data['provinsi'] ?? $kode_prov);
        } elseif ($kode_prov !== '00' && $kode_kab !== '00') {
            echo "✅ <strong>AKUN KABUPATEN/KOTA</strong> - Dapat mengakses data " . htmlspecialchars($user_data['kabupaten'] ?? $kode_kab) . ", " . htmlspecialchars($user_data['provinsi'] ?? $kode_prov);
        } else {
            echo "⚠️ <strong>TIDAK DIKENALI</strong> - Data kode wilayah tidak valid";
        }
        ?>
    </div>

    <div class="section">
        <h2>📋 Raw Data (untuk Developer)</h2>
        <div class="raw-data">
            <pre><?php print_r($user_data); ?></pre>
        </div>
    </div>

    <div class="section">
        <h2>🔧 Session Info</h2>
        <table>
            <tr><th>Field</th><th>Value</th></tr>
            <tr><td><strong>Session Status</strong></td><td><?= session_status() === PHP_SESSION_ACTIVE ? '✅ Active' : '❌ Inactive' ?></td></tr>
            <tr><td><strong>Authenticated</strong></td><td><?= isUserAuthenticated() ? '✅ Yes' : '❌ No' ?></td></tr>
            <tr><td><strong>Access Token</strong></td><td><?= isset($_SESSION['access_token']) ? '✅ Present' : '❌ Missing' ?></td></tr>
            <tr><td><strong>Logout URL</strong></td><td><?= isset($_SESSION['logout_url']) ? '✅ Present' : '❌ Missing' ?></td></tr>
        </table>
    </div>

    <div class="section">
        <h2>🔗 Navigasi</h2>
        <a href="index.php" class="button">🏠 Dashboard</a>
        <a href="monitoring.php" class="button">📊 Monitoring</a>
        <a href="logout.php" class="button button-danger">🚪 Logout</a>
    </div>
</body>
</html> 