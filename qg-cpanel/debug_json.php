<?php
// Debug JSON loading

echo "Testing JSON file loading:\n";

$json_file = __DIR__ . '/daftar_daerah.json';
if (file_exists($json_file)) {
    echo "JSON file exists\n";
    $json_content = file_get_contents($json_file);
    echo "JSON content length: " . strlen($json_content) . "\n";
    
    $data = json_decode($json_content, true);
    if ($data === null) {
        echo "ERROR: JSON decode failed\n";
        echo "JSON error: " . json_last_error_msg() . "\n";
    } else {
        echo "JSON decoded successfully. Total records: " . count($data) . "\n";
        
        // Test the mapping
        $daerah_map = [];
        foreach ($data as $item) {
            if (isset($item['kode']) && isset($item['daerah'])) {
                $daerah_map[$item['kode']] = $item['daerah'];
            }
        }
        
        echo "Mapping created. Total mappings: " . count($daerah_map) . "\n";
        echo "Test mappings:\n";
        echo "  1100 => " . (isset($daerah_map['1100']) ? $daerah_map['1100'] : 'NOT FOUND') . "\n";
        echo "  1101 => " . (isset($daerah_map['1101']) ? $daerah_map['1101'] : 'NOT FOUND') . "\n";
    }
} else {
    echo "ERROR: daftar_daerah.json file not found!\n";
}
?> 