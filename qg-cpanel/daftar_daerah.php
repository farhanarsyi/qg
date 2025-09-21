<?php
/**
 * API endpoint untuk melayani data daerah
 * Alternatif jika file daftar_daerah.json tidak dapat diakses
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Baca file JSON
$jsonFile = __DIR__ . '/daftar_daerah.json';

if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    
    // Validasi JSON
    $data = json_decode($jsonContent, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $jsonContent;
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'Invalid JSON format',
            'message' => 'File daftar_daerah.json contains invalid JSON'
        ]);
    }
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'File not found',
        'message' => 'daftar_daerah.json not found'
    ]);
}
?>
