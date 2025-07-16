<?php
// Test file to verify JSON conversion works
require_once 'api.php';

// Test the getNamaDaerah function with JSON
echo "Testing getNamaDaerah function with JSON file:\n";
echo "Kode 1100: " . getNamaDaerah('1100') . "\n";
echo "Kode 1101: " . getNamaDaerah('1101') . "\n";
echo "Kode 9999: " . getNamaDaerah('9999') . "\n";

// Test JSON file exists and is valid
$json_file = __DIR__ . '/daftar_daerah.json';
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $data = json_decode($json_content, true);
    echo "\nJSON file loaded successfully. Total records: " . count($data) . "\n";
    
    // Show first few records
    echo "First 3 records:\n";
    for ($i = 0; $i < min(3, count($data)); $i++) {
        echo "  " . $data[$i]['kode'] . " => " . $data[$i]['daerah'] . "\n";
    }
} else {
    echo "ERROR: daftar_daerah.json file not found!\n";
}
?> 