<?php
// test_superadmin_access.php - Test akses superadmin
require_once 'sso_integration.php';
require_once 'app_config.php';

// Test 1: User biasa (bukan superadmin)
echo "<h1>Test 1: User Biasa (bukan superadmin)</h1>";
session_start();
$_SESSION['sso_logged_in'] = true;
$_SESSION['sso_username'] = 'user.biasa';
$_SESSION['sso_nama'] = 'User Biasa';
$_SESSION['sso_email'] = 'user.biasa@bps.go.id';
$_SESSION['sso_jabatan'] = 'Pegawai';
$_SESSION['sso_prov'] = '31';
$_SESSION['sso_kab'] = '01';
$_SESSION['sso_unit_kerja'] = 'kabupaten';

$is_superadmin = isSuperAdmin();
echo "Username: " . ($_SESSION['sso_username'] ?? 'N/A') . "<br>";
echo "Is Superadmin: " . ($is_superadmin ? "YES" : "NO") . "<br>";
echo "Expected: NO<br>";
echo "Result: " . ($is_superadmin ? "❌ FAIL" : "✅ PASS") . "<br><br>";

// Test 2: User farhan.arsyi (superadmin)
echo "<h1>Test 2: User farhan.arsyi (superadmin)</h1>";
$_SESSION['sso_username'] = 'farhan.arsyi';
$_SESSION['sso_nama'] = 'Farhan Arsy';
$_SESSION['sso_email'] = 'farhan.arsyi@bps.go.id';
$_SESSION['sso_jabatan'] = 'Superadmin';
$_SESSION['sso_prov'] = '00';
$_SESSION['sso_kab'] = '00';
$_SESSION['sso_unit_kerja'] = 'pusat';

$is_superadmin = isSuperAdmin();
echo "Username: " . ($_SESSION['sso_username'] ?? 'N/A') . "<br>";
echo "Is Superadmin: " . ($is_superadmin ? "YES" : "NO") . "<br>";
echo "Expected: YES<br>";
echo "Result: " . ($is_superadmin ? "✅ PASS" : "❌ FAIL") . "<br><br>";

// Test 3: User lain dengan nama mirip
echo "<h1>Test 3: User dengan nama mirip</h1>";
$_SESSION['sso_username'] = 'farhan.arsyi.test';
$_SESSION['sso_nama'] = 'Farhan Arsy Test';
$_SESSION['sso_email'] = 'farhan.arsyi.test@bps.go.id';
$_SESSION['sso_jabatan'] = 'Pegawai';

$is_superadmin = isSuperAdmin();
echo "Username: " . ($_SESSION['sso_username'] ?? 'N/A') . "<br>";
echo "Is Superadmin: " . ($is_superadmin ? "YES" : "NO") . "<br>";
echo "Expected: NO<br>";
echo "Result: " . ($is_superadmin ? "❌ FAIL" : "✅ PASS") . "<br><br>";

// Test 4: Username case sensitive
echo "<h1>Test 4: Username case sensitive</h1>";
$_SESSION['sso_username'] = 'Farhan.Arsyi';
$_SESSION['sso_nama'] = 'Farhan Arsy';
$_SESSION['sso_email'] = 'farhan.arsyi@bps.go.id';
$_SESSION['sso_jabatan'] = 'Superadmin';

$is_superadmin = isSuperAdmin();
echo "Username: " . ($_SESSION['sso_username'] ?? 'N/A') . "<br>";
echo "Is Superadmin: " . ($is_superadmin ? "YES" : "NO") . "<br>";
echo "Expected: NO (case sensitive)<br>";
echo "Result: " . ($is_superadmin ? "❌ FAIL" : "✅ PASS") . "<br><br>";

echo "<h2>Test Selesai!</h2>";
echo "<p>Jika semua test menunjukkan PASS, maka akses superadmin sudah benar.</p>";
?>
