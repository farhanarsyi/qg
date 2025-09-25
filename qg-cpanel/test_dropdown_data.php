<?php
// test_dropdown_data.php - Test data dropdown provinsi dan kabupaten
header('Content-Type: application/json');

// Test 1: Cek apakah file daftar_daerah.json ada
echo "<h1>Test 1: Cek File daftar_daerah.json</h1>";
$jsonFile = __DIR__ . '/daftar_daerah.json';
if (file_exists($jsonFile)) {
    echo "✅ File daftar_daerah.json ditemukan<br>";
    
    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON valid<br>";
        echo "Total data: " . count($data) . " items<br>";
        
        // Test 2: Cek struktur data
        echo "<h1>Test 2: Struktur Data</h1>";
        if (count($data) > 0) {
            $firstItem = $data[0];
            echo "Struktur item pertama:<br>";
            echo "<pre>" . print_r($firstItem, true) . "</pre>";
            
            // Test 3: Filter provinsi
            echo "<h1>Test 3: Filter Provinsi (kode berakhir 00)</h1>";
            $provinsiData = array_filter($data, function($item) {
                return substr($item['kode'], -2) === '00';
            });
            echo "Jumlah provinsi: " . count($provinsiData) . "<br>";
            
            if (count($provinsiData) > 0) {
                echo "Contoh provinsi:<br>";
                $count = 0;
                foreach ($provinsiData as $prov) {
                    if ($count < 5) {
                        echo "- " . $prov['kode'] . ": " . $prov['daerah'] . "<br>";
                        $count++;
                    }
                }
            }
            
            // Test 4: Filter kabupaten untuk provinsi tertentu
            echo "<h1>Test 4: Filter Kabupaten untuk DKI Jakarta (31)</h1>";
            $kabupatenData = array_filter($data, function($item) {
                return substr($item['kode'], 0, 2) === '31' && 
                       substr($item['kode'], -2) !== '00' && 
                       $item['kode'] !== '3100';
            });
            echo "Jumlah kabupaten di DKI Jakarta: " . count($kabupatenData) . "<br>";
            
            if (count($kabupatenData) > 0) {
                echo "Kabupaten di DKI Jakarta:<br>";
                foreach ($kabupatenData as $kab) {
                    echo "- " . $kab['kode'] . ": " . $kab['daerah'] . "<br>";
                }
            }
            
        } else {
            echo "❌ Data kosong<br>";
        }
        
    } else {
        echo "❌ JSON tidak valid: " . json_last_error_msg() . "<br>";
    }
} else {
    echo "❌ File daftar_daerah.json tidak ditemukan<br>";
}

// Test 5: Test API endpoint
echo "<h1>Test 5: Test API Endpoint daftar_daerah.php</h1>";
$apiUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/daftar_daerah.php';
echo "API URL: " . $apiUrl . "<br>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);
if ($response !== false) {
    $apiData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ API endpoint berfungsi<br>";
        echo "Data dari API: " . count($apiData) . " items<br>";
    } else {
        echo "❌ API endpoint mengembalikan JSON tidak valid<br>";
    }
} else {
    echo "❌ API endpoint tidak dapat diakses<br>";
}

echo "<h2>Test Selesai!</h2>";
echo "<p>Jika semua test menunjukkan ✅, maka dropdown data sudah siap.</p>";
?>
