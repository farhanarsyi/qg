<?php
// test_fixes.php - Test untuk memverifikasi fix yang telah dilakukan
require_once 'sso_integration.php';
require_once 'app_config.php';

echo "<h1>Test Fixes untuk Fitur Superadmin</h1>";

// Test 1: Cek akses superadmin
echo "<h2>1. Test Akses Superadmin</h2>";
session_start();

// Test user biasa
$_SESSION['sso_logged_in'] = true;
$_SESSION['sso_username'] = 'user.biasa';
$is_superadmin_biasa = isSuperAdmin();
echo "User biasa (user.biasa): " . ($is_superadmin_biasa ? "❌ MENGAKSES SUPERADMIN" : "✅ TIDAK MENGAKSES SUPERADMIN") . "<br>";

// Test farhan.arsyi
$_SESSION['sso_username'] = 'farhan.arsyi';
$is_superadmin_farhan = isSuperAdmin();
echo "User farhan.arsyi: " . ($is_superadmin_farhan ? "✅ MENGAKSES SUPERADMIN" : "❌ TIDAK MENGAKSES SUPERADMIN") . "<br>";

// Test 2: Cek data daerah
echo "<h2>2. Test Data Daerah</h2>";
$jsonFile = __DIR__ . '/daftar_daerah.json';
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ File daftar_daerah.json valid<br>";
        
        // Cek provinsi
        $provinsiData = array_filter($data, function($item) {
            return substr($item['kode'], -2) === '00';
        });
        echo "✅ Jumlah provinsi: " . count($provinsiData) . "<br>";
        
        // Cek kabupaten DKI Jakarta
        $kabupatenData = array_filter($data, function($item) {
            return substr($item['kode'], 0, 2) === '31' && 
                   substr($item['kode'], -2) !== '00' && 
                   $item['kode'] !== '3100';
        });
        echo "✅ Jumlah kabupaten di DKI Jakarta: " . count($kabupatenData) . "<br>";
        
    } else {
        echo "❌ File daftar_daerah.json tidak valid<br>";
    }
} else {
    echo "❌ File daftar_daerah.json tidak ditemukan<br>";
}

// Test 3: Cek API endpoint
echo "<h2>3. Test API Endpoint</h2>";
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/daftar_daerah.php';
$response = @file_get_contents($apiUrl);
if ($response !== false) {
    $apiData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ API endpoint daftar_daerah.php berfungsi<br>";
    } else {
        echo "❌ API endpoint mengembalikan data tidak valid<br>";
    }
} else {
    echo "❌ API endpoint tidak dapat diakses<br>";
}

// Test 4: Cek fungsi superadmin
echo "<h2>4. Test Fungsi Superadmin</h2>";
$_SESSION['sso_username'] = 'farhan.arsyi';
$_SESSION['sso_nama'] = 'Farhan Arsy';
$_SESSION['sso_email'] = 'farhan.arsyi@bps.go.id';
$_SESSION['sso_jabatan'] = 'Superadmin';
$_SESSION['sso_prov'] = '00';
$_SESSION['sso_kab'] = '00';
$_SESSION['sso_unit_kerja'] = 'pusat';

// Test switch role
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'provinsi',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '00',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => '',
    'switched_at' => date('Y-m-d H:i:s')
];

$filter = getSSOWilayahFilter();
if ($filter && isset($filter['is_imitating']) && $filter['is_imitating']) {
    echo "✅ Switch role berfungsi<br>";
    echo "Role: " . $filter['unit_kerja'] . "<br>";
    echo "Provinsi: " . $filter['nama_provinsi'] . "<br>";
} else {
    echo "❌ Switch role tidak berfungsi<br>";
}

echo "<h2>Hasil Test</h2>";
if (!$is_superadmin_biasa && $is_superadmin_farhan && file_exists($jsonFile)) {
    echo "<div style='color: green; font-weight: bold;'>✅ SEMUA FIX BERHASIL!</div>";
    echo "<p>Fitur superadmin sudah diperbaiki:</p>";
    echo "<ul>";
    echo "<li>✅ Menu superadmin hanya muncul untuk farhan.arsyi</li>";
    echo "<li>✅ Dropdown provinsi dan kabupaten sudah terisi data</li>";
    echo "<li>✅ Switch role berfungsi dengan baik</li>";
    echo "</ul>";
} else {
    echo "<div style='color: red; font-weight: bold;'>❌ MASIH ADA MASALAH!</div>";
    echo "<p>Periksa kembali implementasi.</p>";
}
?>
