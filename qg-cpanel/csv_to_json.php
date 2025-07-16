<?php
// Convert daftar_daerah.csv to daftar_daerah.json as a valid JSON array
$csvFile = __DIR__ . '/daftar_daerah.csv';
$jsonFile = __DIR__ . '/daftar_daerah.json';

if (!file_exists($csvFile)) {
    echo "CSV file not found!\n";
    exit(1);
}

$rows = [];
if (($handle = fopen($csvFile, 'r')) !== false) {
    $header = fgetcsv($handle);
    while (($data = fgetcsv($handle)) !== false) {
        $row = array_combine($header, $data);
        $rows[] = $row;
    }
    fclose($handle);
}

file_put_contents($jsonFile, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Converted $csvFile to $jsonFile successfully.\n"; 