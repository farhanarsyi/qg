<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test years
$years = ['2023', '2024', '2025'];

foreach ($years as $year) {
    echo "=================================================\n";
    echo "Testing API connection for year {$year}...\n";
    echo "=================================================\n";
    
    // Test fetching project data
    $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
    $postData = http_build_query([
        "year" => $year
    ]);

    // Make the API call
    $response = callApi($url, $postData);
    
    // Check if response is valid
    if ($response === false) {
        echo "API call failed!\n";
        continue;
    }
    
    // Extract and parse JSON
    $jsonString = extractJson($response);
    $jsonData = json_decode($jsonString, true);
    
    // Check if JSON is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON parsing error: " . json_last_error_msg() . "\n";
        echo "Raw response: " . substr($response, 0, 1000) . "...\n";
        continue;
    }
    
    // Display status
    echo "API Response Status: " . ($jsonData['status'] ? 'Success' : 'Failed') . "\n";
    echo "Message: " . ($jsonData['message'] ?? 'No message') . "\n";
    
    // Display data count if available
    if (isset($jsonData['data']) && is_array($jsonData['data'])) {
        echo "Projects found: " . count($jsonData['data']) . "\n";
        echo "First 3 projects (if any):\n";
        
        $count = 0;
        foreach ($jsonData['data'] as $project) {
            if ($count >= 3) break;
            echo "- ID: " . $project['id'] . ", Name: " . $project['name'] . "\n";
            $count++;
        }
    } else {
        echo "No projects found or invalid data format.\n";
    }
    
    echo "\n\n";
}
?> 