<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test years
$years = ['2023', '2024', '2025'];

echo "<h1>Test Local Database Projects</h1>";

foreach ($years as $year) {
    echo "<h2>Year: {$year}</h2>";
    
    // Check for projects in the local database
    $sql = "SELECT COUNT(*) as count FROM projects WHERE year = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    
    echo "<p>Projects found in database for {$year}: {$count}</p>";
    
    if ($count > 0) {
        // Display some sample projects
        $sql = "SELECT * FROM projects WHERE year = ? LIMIT 5";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>ID: {$row['id']}, Name: {$row['name']}</li>";
        }
        echo "</ul>";
        $stmt->close();
    } else {
        echo "<p style='color:red;'>No projects found in the local database for this year!</p>";
        
        // Let's try to sync some projects for this year directly
        echo "<p>Attempting to sync projects from API for {$year}...</p>";
        
        $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
        $postData = http_build_query(["year" => $year]);
        $response = callApi($url, $postData);
        $jsonData = json_decode(extractJson($response), true);
        
        if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
            $apiProjects = $jsonData['data'];
            echo "<p>Found " . count($apiProjects) . " projects from API. Inserting to database...</p>";
            
            $conn->begin_transaction();
            $insertCount = 0;
            
            foreach ($apiProjects as $project) {
                // Insert project
                $stmt = $conn->prepare("INSERT INTO projects (id, year, name, last_synced) VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                $stmt->bind_param("ssss", $project['id'], $year, $project['name'], $project['name']);
                $stmt->execute();
                $insertCount += $stmt->affected_rows;
                $stmt->close();
            }
            
            $conn->commit();
            echo "<p style='color:green;'>Successfully inserted/updated {$insertCount} projects for {$year}.</p>";
        } else {
            echo "<p style='color:red;'>Failed to get projects from API for {$year}.</p>";
        }
    }
    
    echo "<hr>";
}
?> 