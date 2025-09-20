<?php
<<<<<<< HEAD
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
=======
// debug_api.php - Debug API calls dengan data SSO
session_start();
require_once 'sso_config.php';

if (!isLoggedIn()) {
    die('<h1>❌ Belum login SSO</h1><p><a href="sso_login.php">Login SSO</a></p>');
}

$user_data = getUserData();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug API & Filtering</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #fee; border: 1px solid #fcc; }
        .success { background: #efe; border: 1px solid #cfc; }
        pre { background: #fff; padding: 10px; border: 1px solid #ddd; overflow: auto; }
        button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
        button:hover { background: #0056b3; }
        .result { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>🔍 Debug API & Filtering</h1>
    
    <div class="debug-box">
        <h3>👤 User Data SSO</h3>
        <p><strong>Username:</strong> <?= $user_data['username'] ?></p>
        <p><strong>Nama:</strong> <?= $user_data['nama'] ?></p>
        <p><strong>Unit Kerja:</strong> <?= $user_data['unit_kerja'] ?></p>
        <p><strong>Prov Code:</strong> <?= $user_data['kodeprovinsi'] ?></p>
        <p><strong>Kab Code:</strong> <?= $user_data['kodekabupaten'] ?></p>
        <p><strong>Provinsi:</strong> <?= $user_data['provinsi'] ?></p>
        <p><strong>Kabupaten:</strong> <?= $user_data['kabupaten'] ?></p>
    </div>
    
    <div class="debug-box">
        <h3>🗺️ Filter Wilayah</h3>
        <?php 
        require_once 'sso_integration.php';
        $filter = getSSOWilayahFilter();
        if ($filter) {
            echo "<p><strong>Unit Kerja:</strong> " . $filter['unit_kerja'] . "</p>";
            echo "<p><strong>Kode Provinsi Filter:</strong> " . $filter['kode_provinsi'] . "</p>";
            echo "<p><strong>Kode Kabupaten Filter:</strong> " . $filter['kode_kabupaten'] . "</p>";
            echo "<p><strong>Can Access All Indonesia:</strong> " . ($filter['can_access_all_indonesia'] ? 'YES' : 'NO') . "</p>";
            echo "<p><strong>SQL Filter:</strong> <code>" . getWilayahSQLFilter() . "</code></p>";
        } else {
            echo "<p class='error'>❌ Filter tidak tersedia</p>";
        }
        ?>
    </div>
    
    <div class="debug-box">
        <h3>🧪 Test API Calls</h3>
        <button onclick="testDashboardAPI()">📊 Test Dashboard API</button>
        <button onclick="testProjectsAPI()">📋 Test Projects API</button>
        <button onclick="testMonitoringAPI()">📈 Test Monitoring API</button>
        
        <div id="apiResults" class="result" style="display:none;">
            <h4>📡 API Results</h4>
            <div id="apiContent"></div>
        </div>
    </div>
    
    <div class="debug-box">
        <h3>🔧 Actions</h3>
        <p><a href="debug_session.php" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">🔍 Debug Session</a></p>
        <p><a href="index.php" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📊 Dashboard</a></p>
        <p><a href="monitoring.php" style="background: #fd7e14; color: white; padding: 8px 15px; text-decoration: none; border-radius: 3px;">📈 Monitoring</a></p>
    </div>

    <script>
        function testDashboardAPI() {
            console.log('🧪 Testing Dashboard API...');
            
            const requestData = {
                action: "fetchDashboardData",
                user_prov: "<?= $user_data['kodeprovinsi'] ?>",
                user_kab: "<?= $user_data['kodekabupaten'] ?>",
                filter_year: "2025"
            };
            
            console.log('📡 Request Data:', requestData);
            
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: requestData,
                dataType: 'text',
                success: function(response) {
                    console.log('📨 Raw Response:', response);
                    
                    try {
                        // Extract JSON from response
                        const start = response.indexOf('{');
                        const end = response.lastIndexOf('}');
                        const jsonStr = response.substring(start, end + 1);
                        const jsonData = JSON.parse(jsonStr);
                        
                        console.log('✅ Parsed Response:', jsonData);
                        
                        $('#apiResults').show();
                        $('#apiContent').html(`
                            <h5>Dashboard API Response:</h5>
                            <p><strong>Status:</strong> ${jsonData.status ? '✅ Success' : '❌ Failed'}</p>
                            <p><strong>Message:</strong> ${jsonData.message || 'N/A'}</p>
                            <p><strong>Data Count:</strong> ${jsonData.data ? jsonData.data.length : 0} items</p>
                            <details>
                                <summary>Raw Response</summary>
                                <pre>${JSON.stringify(jsonData, null, 2)}</pre>
                            </details>
                        `);
                    } catch (e) {
                        console.error('❌ Parse Error:', e);
                        $('#apiResults').show();
                        $('#apiContent').html(`
                            <h5>Parse Error:</h5>
                            <p class="error">❌ Failed to parse JSON response</p>
                            <details>
                                <summary>Raw Response</summary>
                                <pre>${response}</pre>
                            </details>
                        `);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('💥 AJAX Error:', error);
                    $('#apiResults').show();
                    $('#apiContent').html(`
                        <h5>AJAX Error:</h5>
                        <p class="error">❌ ${error}</p>
                        <p><strong>Status:</strong> ${status}</p>
                    `);
                }
            });
        }
        
        function testProjectsAPI() {
            console.log('🧪 Testing Projects API...');
            
            const requestData = {
                action: "fetchAvailableProjects",
                user_prov: "<?= $user_data['kodeprovinsi'] ?>",
                user_kab: "<?= $user_data['kodekabupaten'] ?>",
                year: "2025"
            };
            
            $.ajax({
                url: 'api.php',
                method: 'POST',
                data: requestData,
                dataType: 'text',
                success: function(response) {
                    try {
                        const start = response.indexOf('{');
                        const end = response.lastIndexOf('}');
                        const jsonStr = response.substring(start, end + 1);
                        const jsonData = JSON.parse(jsonStr);
                        
                        $('#apiResults').show();
                        $('#apiContent').html(`
                            <h5>Projects API Response:</h5>
                            <p><strong>Status:</strong> ${jsonData.status ? '✅ Success' : '❌ Failed'}</p>
                            <p><strong>Data Count:</strong> ${jsonData.data ? jsonData.data.length : 0} projects</p>
                            <details>
                                <summary>Projects List</summary>
                                <pre>${JSON.stringify(jsonData, null, 2)}</pre>
                            </details>
                        `);
                    } catch (e) {
                        $('#apiResults').show();
                        $('#apiContent').html(`<p class="error">❌ Parse Error</p><pre>${response}</pre>`);
                    }
                }
            });
        }
        
        function testMonitoringAPI() {
            alert('Monitoring API test akan ditambahkan setelah dashboard API berjalan dengan baik.');
        }
    </script>
>>>>>>> tambah-fitur
</body>
</html> 