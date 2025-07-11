<?php
// debug_session.php - Debug halaman untuk mengecek session SSO
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug SSO Session</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #fee; border: 1px solid #fcc; }
        .success { background: #efe; border: 1px solid #cfc; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow: auto; }
    </style>
</head>
<body>
    <h1>🔍 Debug SSO Session</h1>
    
    <div class="debug-box">
        <h3>📊 Session Status</h3>
        <p><strong>Session ID:</strong> <?= session_id() ?: 'No session' ?></p>
        <p><strong>Session Started:</strong> <?= isset($_SESSION) ? 'Yes' : 'No' ?></p>
    </div>
    
    <div class="debug-box">
        <h3>🔐 SSO Data</h3>
        <?php if (isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in']): ?>
            <div class="success">✅ SSO Login Status: LOGGED IN</div>
            
            <p><strong>Username:</strong> <?= isset($_SESSION['sso_username']) ? $_SESSION['sso_username'] : 'Not set' ?></p>
            <p><strong>Nama:</strong> <?= isset($_SESSION['sso_nama']) ? $_SESSION['sso_nama'] : 'Not set' ?></p>
            <p><strong>Email:</strong> <?= isset($_SESSION['sso_email']) ? $_SESSION['sso_email'] : 'Not set' ?></p>
            <p><strong>Jabatan:</strong> <?= isset($_SESSION['sso_jabatan']) ? $_SESSION['sso_jabatan'] : 'Not set' ?></p>
            <p><strong>Prov:</strong> <?= isset($_SESSION['sso_prov']) ? $_SESSION['sso_prov'] : 'Not set' ?></p>
            <p><strong>Kab:</strong> <?= isset($_SESSION['sso_kab']) ? $_SESSION['sso_kab'] : 'Not set' ?></p>
            <p><strong>Unit Kerja:</strong> <?php 
                if (isset($_SESSION['sso_unit_kerja'])) {
                    echo $_SESSION['sso_unit_kerja'];
                    if (isset($_SESSION['sso_unit_kerja_data']) && is_array($_SESSION['sso_unit_kerja_data'])) {
                        echo " <small style='color: #666;'>(" . ($_SESSION['sso_unit_kerja_data']['nama_level'] ?? 'N/A') . ")</small>";
                    }
                } else {
                    echo 'Not set';
                }
            ?></p>
            <?php if (isset($_SESSION['sso_unit_kerja_data']) && is_array($_SESSION['sso_unit_kerja_data'])): ?>
            <p><strong>Detail Unit Kerja:</strong> <pre style="font-size: 11px; margin: 5px 0; padding: 8px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 3px;"><?= print_r($_SESSION['sso_unit_kerja_data'], true) ?></pre></p>
            <?php endif; ?>
            <p><strong>Kode Organisasi:</strong> <?= isset($_SESSION['sso_kode_organisasi']) ? $_SESSION['sso_kode_organisasi'] : 'Not set' ?></p>
            <p><strong>Login Time:</strong> <?= isset($_SESSION['sso_login_time']) ? date('Y-m-d H:i:s', $_SESSION['sso_login_time']) : 'Not set' ?></p>
        <?php else: ?>
            <div class="error">❌ SSO Login Status: NOT LOGGED IN</div>
            <p>Anda belum login SSO atau session telah expire.</p>
            <?php if (isset($_SESSION['user_data'])): ?>
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px;">
                    ⚠️ <strong>Data session lama ditemukan!</strong><br>
                    Ada session dengan format lama yang perlu dibersihkan.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <div class="debug-box">
        <h3>🧪 Function Tests</h3>
        <?php 
        require_once 'sso_config.php';
        echo "<p><strong>isLoggedIn():</strong> " . (isLoggedIn() ? 'TRUE ✅' : 'FALSE ❌') . "</p>";
        $user_data = getUserData();
        if ($user_data) {
            echo "<p><strong>getUserData():</strong> ✅ Data tersedia</p>";
            echo "<p><strong>Username from getUserData():</strong> " . ($user_data['username'] ?? 'Empty') . "</p>";
            echo "<p><strong>Nama from getUserData():</strong> " . ($user_data['nama'] ?? 'Empty') . "</p>";
                 } else {
             echo "<p><strong>getUserData():</strong> ❌ NULL atau kosong</p>";
         }
         
         // Test SSO integration functions
         if (function_exists('getSSOWilayahFilter')) {
             require_once 'sso_integration.php';
             $filter = getSSOWilayahFilter();
             if ($filter) {
                 echo "<p><strong>getSSOWilayahFilter():</strong> ✅ Filter tersedia</p>";
                 echo "<p><strong>SQL Filter:</strong> <code>" . getWilayahSQLFilter() . "</code></p>";
                 echo "<details><summary><strong>Filter Data</strong> (klik untuk expand)</summary>";
                 echo "<pre style='font-size: 11px; background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; border-radius: 3px;'>" . print_r($filter, true) . "</pre>";
                 echo "</details>";
             } else {
                 echo "<p><strong>getSSOWilayahFilter():</strong> ❌ Filter NULL</p>";
             }
         }
         ?>
    </div>
    
    <div class="debug-box">
        <h3>📝 All Session Data</h3>
        <pre><?= print_r($_SESSION, true) ?></pre>
    </div>
    
    <div class="debug-box">
        <h3>🔧 Debug Actions</h3>
        <p><a href="sso_login.php" style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🔐 Login SSO</a></p>
        <p><a href="sso_logout.php" style="background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🚪 Logout SSO</a></p>
        <p><a href="main.php" style="background: #6f42c1; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🏠 Main (Entry Point)</a></p>
        <p><a href="index.php" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📊 Dashboard</a></p>
        <p><a href="monitoring.php" style="background: #fd7e14; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📈 Monitoring</a></p>
        <p><a href="profile.php" style="background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">👤 Profile</a></p>
        <p><a href="?" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🔄 Refresh Debug</a></p>
    </div>
    
    <div class="debug-box">
        <h3>📋 JavaScript Debug</h3>
        <script>
            console.log('=== SSO SESSION DEBUG ===');
            console.log('Username:', '<?= isset($_SESSION["sso_username"]) ? $_SESSION["sso_username"] : "" ?>');
            console.log('Nama:', '<?= isset($_SESSION["sso_nama"]) ? $_SESSION["sso_nama"] : "" ?>');
            console.log('Email:', '<?= isset($_SESSION["sso_email"]) ? $_SESSION["sso_email"] : "" ?>');
            console.log('Logged In:', <?= isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in'] ? 'true' : 'false' ?>);
            
            // Test JavaScript object
            const testUser = {
                username: '<?= isset($_SESSION["sso_username"]) ? $_SESSION["sso_username"] : "" ?>',
                name: '<?= isset($_SESSION["sso_nama"]) ? $_SESSION["sso_nama"] : "" ?>',
                email: '<?= isset($_SESSION["sso_email"]) ? $_SESSION["sso_email"] : "" ?>'
            };
            
            console.log('Test User Object:', testUser);
            console.log('Username empty?', !testUser.username);
            
            if (!testUser.username) {
                console.error('⚠️ USERNAME KOSONG - Ini penyebab infinite redirect!');
            } else {
                console.log('✅ Username tersedia:', testUser.username);
            }
        </script>
        <p>Check browser console (F12) untuk informasi lebih detail.</p>
    </div>
    
    <div class="debug-box">
        <h3>ℹ️ Instructions</h3>
        <ol>
            <li>Jika SSO Login Status = NOT LOGGED IN, klik "Login SSO" terlebih dahulu</li>
            <li>Jika Username kosong setelah login, ada masalah dengan callback SSO</li>
            <li>Jika semua data lengkap, masalah ada di JavaScript index.php</li>
        </ol>
    </div>
</body>
</html> 