<?php
// File untuk mengecek sistem dan troubleshooting error 500
// Akses: https://dashboardqg.web.bps.go.id/check-system.php

?>
<!DOCTYPE html>
<html>
<head>
    <title>🔧 System Check - Dashboard Quality Gates</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .command { background: #f5f5f5; padding: 10px; border-radius: 3px; font-family: monospace; }
        h2 { background: #059669; color: white; padding: 10px; margin: -15px -15px 15px -15px; }
    </style>
</head>
<body>
    <h1>🔧 System Check - Dashboard Quality Gates</h1>
    <p><strong>Tujuan:</strong> Mengecek kenapa SSO error 500 dan cara mengatasinya</p>

    <div class="section">
        <h2>1. PHP Info</h2>
        <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
        <p><strong>Server:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></p>
        <p><strong>Document Root:</strong> <?= $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown' ?></p>
        <p><strong>Current Directory:</strong> <?= __DIR__ ?></p>
    </div>

    <div class="section">
        <h2>2. File Structure Check</h2>
        <?php
        $files_to_check = [
            'composer.json' => 'Konfigurasi Composer',
            'vendor/autoload.php' => 'Autoload Composer (PENTING!)',
            'vendor/irsadarief/jkd-sso' => 'Package SSO BPS',
            'sso-auth.php' => 'File SSO Auth',
            'sso-callback.php' => 'File SSO Callback',
            'auth-check.php' => 'File Auth Check'
        ];

        foreach ($files_to_check as $file => $desc) {
            $exists = file_exists($file);
            $status = $exists ? 'ok' : 'error';
            $icon = $exists ? '✅' : '❌';
            echo "<p class='$status'>$icon <strong>$file</strong> - $desc</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>3. Composer Check</h2>
        <?php
        if (file_exists('vendor/autoload.php')) {
            echo "<p class='ok'>✅ <strong>Composer terinstall</strong> - vendor/autoload.php ada</p>";
            
            // Check jika bisa load autoload
            try {
                require_once 'vendor/autoload.php';
                echo "<p class='ok'>✅ <strong>Autoload berhasil</strong> - dapat di-require</p>";
                
                // Check class SSO
                if (class_exists('IrsadArief\JKD\SSO\Client\Provider\Keycloak')) {
                    echo "<p class='ok'>✅ <strong>Class SSO ada</strong> - IrsadArief\JKD\SSO\Client\Provider\Keycloak</p>";
                } else {
                    echo "<p class='error'>❌ <strong>Class SSO tidak ada</strong> - Package mungkin tidak terinstall dengan benar</p>";
                }
            } catch (Exception $e) {
                echo "<p class='error'>❌ <strong>Error loading autoload:</strong> " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ <strong>Composer BELUM terinstall</strong> - vendor/autoload.php tidak ada</p>";
            echo "<p class='warning'>⚠️ <strong>INI PENYEBAB ERROR 500!</strong></p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>4. Error Log Check</h2>
        <?php
        // Try to read error log
        $error_log_locations = [
            'error_log',
            '../error_log', 
            '../../error_log',
            '/var/log/apache2/error.log',
            '/var/log/nginx/error.log'
        ];
        
        $found_log = false;
        foreach ($error_log_locations as $log_file) {
            if (file_exists($log_file) && is_readable($log_file)) {
                $found_log = true;
                echo "<p class='ok'>✅ <strong>Error log ditemukan:</strong> $log_file</p>";
                
                // Read last 10 lines
                $lines = array_slice(file($log_file), -10);
                echo "<div class='command'>";
                echo "<strong>Last 10 lines:</strong><br>";
                foreach ($lines as $line) {
                    echo htmlspecialchars($line) . "<br>";
                }
                echo "</div>";
                break;
            }
        }
        
        if (!$found_log) {
            echo "<p class='warning'>⚠️ <strong>Error log tidak ditemukan atau tidak dapat dibaca</strong></p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>5. Test SSO Components</h2>
        <?php
        if (file_exists('vendor/autoload.php')) {
            echo "<p class='ok'>✅ Mencoba test SSO components...</p>";
            
            try {
                require_once 'vendor/autoload.php';
                
                // Try to create SSO provider
                $provider = new IrsadArief\JKD\SSO\Client\Provider\Keycloak([
                    'authServerUrl' => 'https://sso.bps.go.id',
                    'realm' => 'pegawai-bps',
                    'clientId' => '07300-dashqg-l30',
                    'clientSecret' => 'e1c46e44-f33a-45f0-ace1-62c445333ae7',
                    'redirectUri' => 'https://dashboardqg.web.bps.go.id/sso-callback.php'
                ]);
                
                echo "<p class='ok'>✅ <strong>SSO Provider berhasil dibuat!</strong></p>";
                echo "<p class='ok'>✅ <strong>Kemungkinan SSO akan bekerja normal</strong></p>";
                
            } catch (Exception $e) {
                echo "<p class='error'>❌ <strong>Error creating SSO provider:</strong> " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p class='error'>❌ Tidak bisa test SSO karena vendor/autoload.php tidak ada</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>6. Solusi</h2>
        
        <?php if (!file_exists('vendor/autoload.php')): ?>
        <div style="background: #ffe6e6; padding: 15px; border-radius: 5px; margin: 10px 0;">
            <h3>🚨 MASALAH UTAMA: Composer Package Belum Terinstall</h3>
            <p><strong>Penyebab Error 500:</strong> File <code>vendor/autoload.php</code> tidak ada</p>
        </div>
        
        <h3>🔧 Cara Mengatasi:</h3>
        
        <h4>Opsi 1: Install via Terminal cPanel</h4>
        <div class="command">
cd ~/public_html/qg<br>
composer install
        </div>
        
        <h4>Opsi 2: Install di Local lalu Upload</h4>
        <div class="command">
# Di komputer local:<br>
composer require irsadarief/jkd-sso<br>
# Lalu upload folder vendor/ ke cPanel
        </div>
        
        <h4>Opsi 3: Manual Download (Fallback)</h4>
        <p>Jika composer tidak bisa, saya buatkan fallback manual</p>
        
        <?php else: ?>
        <div style="background: #e6ffe6; padding: 15px; border-radius: 5px; margin: 10px 0;">
            <h3>✅ Composer Package Terinstall</h3>
            <p>Error 500 mungkin disebabkan hal lain. Cek error log di atas.</p>
        </div>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>7. Quick Actions</h2>
        <p><a href="sso-auth.php" style="background: #059669; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">🔄 Test SSO Auth</a></p>
        <p><a href="." style="background: #666; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">📁 Lihat File List</a></p>
    </div>

</body>
</html> 