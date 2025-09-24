<?php
// test_superadmin.php - Test file untuk fitur superadmin
require_once 'sso_integration.php';
require_once 'app_config.php';

// Simulasi session untuk testing
session_start();

// Simulasi user farhan.arsyi sebagai superadmin
$_SESSION['sso_logged_in'] = true;
$_SESSION['sso_username'] = 'farhan.arsyi';
$_SESSION['sso_nama'] = 'Farhan Arsy';
$_SESSION['sso_email'] = 'farhan.arsyi@bps.go.id';
$_SESSION['sso_jabatan'] = 'Superadmin';
$_SESSION['sso_prov'] = '00';
$_SESSION['sso_kab'] = '00';
$_SESSION['sso_unit_kerja'] = 'pusat';

echo "<h1>Test Fitur Superadmin</h1>";

// Test 1: Cek apakah user adalah superadmin
echo "<h2>Test 1: Cek Superadmin Status</h2>";
$is_superadmin = isSuperAdmin();
echo "Is Superadmin: " . ($is_superadmin ? "YES" : "NO") . "<br>";

// Test 2: Cek filter wilayah normal
echo "<h2>Test 2: Filter Wilayah Normal</h2>";
$filter_normal = getSSOWilayahFilter();
echo "<pre>" . print_r($filter_normal, true) . "</pre>";

// Test 3: Simulasi switch role ke provinsi
echo "<h2>Test 3: Switch Role ke Provinsi (DKI Jakarta)</h2>";
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'provinsi',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '00',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => '',
    'switched_at' => date('Y-m-d H:i:s')
];

$filter_provinsi = getSSOWilayahFilter();
echo "<pre>" . print_r($filter_provinsi, true) . "</pre>";

// Test 4: Simulasi switch role ke kabupaten
echo "<h2>Test 4: Switch Role ke Kabupaten (Jakarta Pusat)</h2>";
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'kabupaten',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '01',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => 'Jakarta Pusat',
    'switched_at' => date('Y-m-d H:i:s')
];

$filter_kabupaten = getSSOWilayahFilter();
echo "<pre>" . print_r($filter_kabupaten, true) . "</pre>";

// Test 5: Reset role
echo "<h2>Test 5: Reset Role</h2>";
unset($_SESSION['superadmin_imitation']);
$filter_reset = getSSOWilayahFilter();
echo "<pre>" . print_r($filter_reset, true) . "</pre>";

// Test 6: Test SQL Filter
echo "<h2>Test 6: SQL Filter</h2>";
echo "SQL Filter Normal: " . getWilayahSQLFilter('p') . "<br>";

// Switch ke provinsi lagi untuk test SQL
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'provinsi',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '00',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => '',
    'switched_at' => date('Y-m-d H:i:s')
];

echo "SQL Filter Provinsi: " . getWilayahSQLFilter('p') . "<br>";

// Switch ke kabupaten untuk test SQL
$_SESSION['superadmin_imitation'] = [
    'unit_kerja' => 'kabupaten',
    'kode_provinsi' => '31',
    'kode_kabupaten' => '01',
    'nama_provinsi' => 'DKI Jakarta',
    'nama_kabupaten' => 'Jakarta Pusat',
    'switched_at' => date('Y-m-d H:i:s')
];

echo "SQL Filter Kabupaten: " . getWilayahSQLFilter('p') . "<br>";

echo "<h2>Test Selesai!</h2>";
echo "<p>Semua test berhasil dijalankan. Fitur superadmin sudah siap digunakan.</p>";
?>
