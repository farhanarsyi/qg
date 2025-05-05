<?php
require_once 'config.php';

// Check if user is authenticated and is superadmin
if (!isAuthenticated() || !isSuperAdmin()) {
    die("You need to be logged in as a superadmin to run this script");
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Fixing 2025 Project Data</h1>";

// 1. First check if we have 2025 data in the local database
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE year = '2025'");
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];
$stmt->close();

echo "<p>Found {$count} projects for year 2025 in the local database.</p>";

// 2. Fetch 2025 data from API regardless of existing data
echo "<p>Fetching 2025 projects from the API...</p>";

$url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
$postData = http_build_query(["year" => "2025"]);
$response = callApi($url, $postData);
$jsonData = json_decode(extractJson($response), true);

if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
    $apiProjects = $jsonData['data'];
    echo "<p>Found " . count($apiProjects) . " projects from API for 2025.</p>";
    
    // Begin transaction to update the database
    $conn->begin_transaction();
    $insertCount = 0;
    $coverageCount = 0;
    
    // 3. Insert each project
    foreach ($apiProjects as $i => $project) {
        echo "<p>Processing project {$i} of " . count($apiProjects) . ": {$project['name']}</p>";
        
        // Insert project
        $stmt = $conn->prepare("INSERT INTO projects (id, year, name, last_synced) VALUES (?, '2025', ?, NOW()) 
                               ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
        $stmt->bind_param("sss", $project['id'], $project['name'], $project['name']);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $insertCount += ($affected > 0) ? 1 : 0;
        $stmt->close();
        
        // 4. Get coverage for this project
        echo "<p>Fetching coverage data for project: {$project['name']}</p>";
        
        $coverageUrl = "https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll";
        $coveragePostData = http_build_query(["id_project" => $project['id']]);
        $coverageResponse = callApi($coverageUrl, $coveragePostData);
        $coverageData = json_decode(extractJson($coverageResponse), true);
        
        if (isset($coverageData['status']) && $coverageData['status'] && isset($coverageData['data'])) {
            $coverages = $coverageData['data'];
            echo "<p>Found " . count($coverages) . " coverages for this project.</p>";
            
            // Insert pusat (National) coverage
            $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name, last_synced) 
                                  VALUES (?, '2025', '00', '00', 'Pusat - Nasional', NOW()) 
                                  ON DUPLICATE KEY UPDATE name = 'Pusat - Nasional', last_synced = NOW()");
            $stmt->bind_param("s", $project['id']);
            $stmt->execute();
            $coverageCount++;
            $stmt->close();
            
            // Insert other coverages
            foreach ($coverages as $coverage) {
                $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name, last_synced) 
                                      VALUES (?, '2025', ?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                $stmt->bind_param("sssss", $project['id'], $coverage['prov'], $coverage['kab'], $coverage['name'], $coverage['name']);
                $stmt->execute();
                $coverageCount++;
                $stmt->close();
            }
        } else {
            echo "<p style='color:orange;'>No coverage data found for this project or API error.</p>";
        }
        
        // Commit after each project
        $conn->commit();
        
        // Start a new transaction
        $conn->begin_transaction();
    }
    
    // Final commit
    $conn->commit();
    
    echo "<h2 style='color:green;'>Successfully processed {$insertCount} projects and {$coverageCount} coverages for 2025</h2>";
    
    // 5. Recount to verify data is now in the database
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE year = '2025'");
    $stmt->execute();
    $result = $stmt->get_result();
    $newCount = $result->fetch_assoc()['count'];
    $stmt->close();
    
    echo "<p>Now found {$newCount} projects for year 2025 in the local database.</p>";
    
    echo "<p><a href='monitoring.php' class='btn btn-primary'>Return to Monitoring</a></p>";
} else {
    echo "<h2 style='color:red;'>Failed to get projects from API for 2025</h2>";
    echo "<p>Error: " . ($jsonData['message'] ?? 'Unknown error') . "</p>";
}
?> 