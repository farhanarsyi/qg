<?php
require_once 'config.php';

// Check if user is authenticated and is superadmin
if (!isAuthenticated() || !isSuperAdmin()) {
    die("You need to be logged in as a superadmin to access this page");
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug Tool</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        pre {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            max-height: 400px;
            overflow: auto;
        }
        .api-response {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1>API Debug Tool</h1>
        <p>Gunakan halaman ini untuk menguji API QGate dan memahami responnya.</p>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Test Project API</div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="year" class="form-label">Tahun</label>
                                <select name="year" id="year" class="form-select">
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025" selected>2025</option>
                                </select>
                            </div>
                            <button type="submit" name="test_projects" class="btn btn-primary">Test Projects API</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Test Coverage API</div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="project_id" class="form-label">Project ID</label>
                                <input type="text" name="project_id" id="project_id" class="form-control" required>
                            </div>
                            <button type="submit" name="test_coverage" class="btn btn-primary">Test Coverage API</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        // Handle Projects API Test
        if (isset($_POST['test_projects'])) {
            $year = $_POST['year'];
            $url = $api_base_url . "Projects/fetchAll";
            $postData = http_build_query(["year" => $year]);
            
            echo "<h2 class='api-response'>Projects API Response for Year: {$year}</h2>";
            echo "<p>URL: {$url}</p>";
            echo "<p>POST Data: {$postData}</p>";
            
            $start = microtime(true);
            $response = callApi($url, $postData);
            $end = microtime(true);
            $duration = round($end - $start, 2);
            
            echo "<p>Response Time: {$duration} seconds</p>";
            
            // Try to parse JSON
            $jsonString = extractJson($response);
            $jsonData = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                // JSON is valid
                if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
                    echo "<div class='alert alert-success'>API returned successful response with " . count($jsonData['data']) . " projects.</div>";
                    
                    // Show projects table
                    if (!empty($jsonData['data'])) {
                        echo "<h3>Projects:</h3>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>ID</th><th>Name</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($jsonData['data'] as $project) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($project['id'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($project['name'] ?? 'N/A') . "</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody></table>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>API returned error: " . ($jsonData['message'] ?? 'Unknown error') . "</div>";
                }
                
                echo "<h3>Raw JSON Response:</h3>";
                echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                // JSON is invalid
                echo "<div class='alert alert-danger'>Invalid JSON response: " . json_last_error_msg() . "</div>";
                echo "<h3>Raw Response:</h3>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
            }
        }
        
        // Handle Coverage API Test
        if (isset($_POST['test_coverage'])) {
            $projectId = $_POST['project_id'];
            $url = $api_base_url . "coverages/fetchAll";
            $postData = http_build_query(["id_project" => $projectId]);
            
            echo "<h2 class='api-response'>Coverage API Response for Project ID: {$projectId}</h2>";
            echo "<p>URL: {$url}</p>";
            echo "<p>POST Data: {$postData}</p>";
            
            $start = microtime(true);
            $response = callApi($url, $postData);
            $end = microtime(true);
            $duration = round($end - $start, 2);
            
            echo "<p>Response Time: {$duration} seconds</p>";
            
            // Try to parse JSON
            $jsonString = extractJson($response);
            $jsonData = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                // JSON is valid
                if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
                    echo "<div class='alert alert-success'>API returned successful response with " . count($jsonData['data']) . " coverages.</div>";
                    
                    // Show coverages table
                    if (!empty($jsonData['data'])) {
                        echo "<h3>Coverages:</h3>";
                        echo "<div class='table-responsive'>";
                        echo "<table class='table table-striped'>";
                        echo "<thead><tr><th>Province</th><th>Kabupaten</th><th>Name</th></tr></thead>";
                        echo "<tbody>";
                        
                        foreach ($jsonData['data'] as $coverage) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($coverage['prov'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($coverage['kab'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($coverage['name'] ?? 'N/A') . "</td>";
                            echo "</tr>";
                        }
                        
                        echo "</tbody></table>";
                        echo "</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>API returned error: " . ($jsonData['message'] ?? 'Unknown error') . "</div>";
                }
                
                echo "<h3>Raw JSON Response:</h3>";
                echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                // JSON is invalid
                echo "<div class='alert alert-danger'>Invalid JSON response: " . json_last_error_msg() . "</div>";
                echo "<h3>Raw Response:</h3>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
            }
        }
        ?>
        
        <div class="mt-4">
            <a href="admin/index.php" class="btn btn-secondary">Kembali ke Admin</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 