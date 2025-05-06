<?php
// download.php


// Set execution time limit to 24 hours
set_time_limit(86400);
ini_set('max_execution_time', 86400);

// Database configuration
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'qg_sync';

// Connect to database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create qg_sync table if not exists
$sql = "CREATE TABLE IF NOT EXISTS qg_sync (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(10) NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    region_id VARCHAR(100) NOT NULL,
    data_type VARCHAR(50) NOT NULL,
    cache_key VARCHAR(255) NOT NULL,
    data_json LONGTEXT NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY cache_idx (year, project_id, region_id, data_type, cache_key)
)";

if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
}

// Create download_logs table if not exists
$sql = "CREATE TABLE IF NOT EXISTS download_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    year VARCHAR(10) NOT NULL,
    project_id VARCHAR(100) DEFAULT 'all',
    region_id VARCHAR(100) DEFAULT 'all',
    status VARCHAR(20) NOT NULL,
    progress INT(3) DEFAULT 0,
    total_items INT(11) DEFAULT 0,
    completed_items INT(11) DEFAULT 0,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    error_message TEXT NULL
)";

if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
}

// Create download_session table to track active downloads
$sql = "CREATE TABLE IF NOT EXISTS download_session (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    year VARCHAR(10) NOT NULL,
    project_id VARCHAR(100) NOT NULL,
    region_id VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'running',
    progress INT(3) DEFAULT 0,
    total_items INT(11) DEFAULT 0,
    completed_items INT(11) DEFAULT 0,
    logs LONGTEXT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY session_idx (session_id)
)";

if (!$conn->query($sql)) {
    echo "Error creating table: " . $conn->error;
}

// Include database functions
include_once 'db_functions.php';

// Session management for download process
session_start();

// AJAX endpoint for download operations
if (isset($_POST['download_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['download_action'];
    
    if ($action === 'start_download') {
        // Get parameters
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : '';
        $force_refresh = isset($_POST['force_refresh']) && $_POST['force_refresh'] == 1;
        
        // Validate parameters
        if (empty($year) || empty($project_id) || empty($region_id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Parameter tidak lengkap'
            ]);
            exit;
        }
        
        // Generate session ID
        $session_id = md5($year . $project_id . $region_id . microtime());
        $_SESSION['download_session_id'] = $session_id;
        
        // Prepare empty log array
        $logs = json_encode([]);
        
        // Create new download session
        $sql = "INSERT INTO download_session 
                (session_id, year, project_id, region_id, status, logs) 
                VALUES (?, ?, ?, ?, 'running', ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $session_id, $year, $project_id, $region_id, $logs);
        
        if (!$stmt->execute()) {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal membuat sesi download'
            ]);
            exit;
        }
        
        // Start background download process
        // In real production environment, you might want to use a queue system
        // For this implementation, we'll use a simple approach - start the work here
        
        // Create a log entry
        $log_id = logDownloadAction($conn, $year, $project_id, $region_id, 'started');
        
        // Calculate total items to download (example method)
        $total_items = calculateTotalItems($year, $project_id, $region_id);
        
        // Update session with total items
        $sql = "UPDATE download_session SET total_items = ? WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $total_items, $session_id);
        $stmt->execute();
        
        // Return success response
        echo json_encode([
            'status' => true,
            'message' => 'Download started',
            'session_id' => $session_id,
            'total_items' => $total_items
        ]);
        
        // Start the download process in the background
        // This would typically be done with a worker process or queue
        // For this example, we'll simulate the process
        startDownloadProcess($conn, $session_id, $year, $project_id, $region_id, $force_refresh, $log_id);
        
        exit;
    }
    
    if ($action === 'stop_download') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : '';
        $session_id = $_SESSION['download_session_id'] ?? '';
        
        if (empty($session_id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada sesi download yang aktif'
            ]);
            exit;
        }
        
        // Update session status to stopped
        $sql = "UPDATE download_session SET status = 'stopped' WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $session_id);
        
        if ($stmt->execute()) {
            // Update log status
            logDownloadAction(
                $conn, 
                $year, 
                $project_id, 
                $region_id, 
                'completed', 
                0, 
                0, 
                0, 
                'Dihentikan oleh pengguna'
            );
            
            echo json_encode([
                'status' => true,
                'message' => 'Download stopped'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghentikan download'
            ]);
        }
        
        exit;
    }
    
    if ($action === 'get_progress') {
        $session_id = $_SESSION['download_session_id'] ?? '';
        
        if (empty($session_id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada sesi download yang aktif'
            ]);
            exit;
        }
        
        // Get session data
        $sql = "SELECT * FROM download_session WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $session = $result->fetch_assoc();
            
            // Get new logs since last check
            $logs = json_decode($session['logs'] ?? '[]', true);
            
            // Determine if download is completed
            $is_completed = in_array($session['status'], ['completed', 'error']);
            
            // Calculate progress percentage
            $progress = 0;
            if ($session['total_items'] > 0) {
                $progress = round(($session['completed_items'] / $session['total_items']) * 100);
            }
            
            echo json_encode([
                'status' => true,
                'progress' => $progress,
                'completed_items' => $session['completed_items'],
                'total_items' => $session['total_items'],
                'status_text' => getStatusText($session['status']),
                'is_completed' => $is_completed,
                'is_error' => $session['status'] === 'error',
                'logs' => $logs
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Sesi download tidak ditemukan'
            ]);
        }
        
        exit;
    }
    
    if ($action === 'get_logs') {
        // Get the latest download logs
        $logs = getDownloadLogs($conn, 20);
        
        echo json_encode([
            'status' => true,
            'logs' => $logs
        ]);
        
        exit;
    }
    
    echo json_encode(['status' => false, 'message' => 'Invalid action']);
    exit;
}

// Helper Functions for Download Process

// Calculate total items to download
function calculateTotalItems($year, $project_id, $region_id) {
    // This is a simplified example
    // In a real implementation, you'd query the API to get actual counts
    
    // Base count per project
    $count = 50;
    
    // If multiple years
    if ($year === 'all') {
        $count *= 3; // 3 years
    }
    
    // If multiple projects 
    if ($project_id === 'all') {
        $count *= 5; // Assume 5 projects per year
    }
    
    // If multiple regions
    if ($region_id === 'all') {
        $count *= 10; // Assume 10 regions
    } 
    // If province is selected (ending with "00"), account for districts
    else if (substr($region_id, 2) === "00" && $region_id !== "pusat") {
        // Estimate average 8 districts per province
        $count *= 8;
    }
    
    return $count;
}

// Get status text for UI display
function getStatusText($status) {
    switch ($status) {
        case 'running':
            return 'Mengunduh...';
        case 'stopped':
            return 'Dihentikan';
        case 'completed':
            return 'Selesai';
        case 'error':
            return 'Error';
        default:
            return ucfirst($status);
    }
}

// Add log to session
function addSessionLog($conn, $session_id, $message, $type = 'info') {
    // Get current logs
    $sql = "SELECT logs FROM download_session WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $logs = json_decode($row['logs'] ?? '[]', true);
        
        // Add new log
        $logs[] = [
            'time' => date('Y-m-d H:i:s'),
            'message' => $message,
            'type' => $type
        ];
        
        // Update session
        $logs_json = json_encode($logs);
        $sql = "UPDATE download_session SET logs = ? WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $logs_json, $session_id);
        $stmt->execute();
        
        return true;
    }
    
    return false;
}

// Update download progress
function updateDownloadProgress($conn, $session_id, $completed_items, $total_items = null) {
    // Calculate progress percentage
    $progress = 0;
    if ($total_items === null) {
        // Get total from session
        $sql = "SELECT total_items FROM download_session WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $total_items = $result->fetch_assoc()['total_items'];
        }
    }
    
    if ($total_items > 0) {
        $progress = round(($completed_items / $total_items) * 100);
    }
    
    // Update session
    $sql = "UPDATE download_session SET progress = ?, completed_items = ? WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $progress, $completed_items, $session_id);
    
    return $stmt->execute();
}

// Mark download as completed
function completeDownload($conn, $session_id, $status = 'completed', $error_message = null) {
    // Get session data for log update
    $sql = "SELECT year, project_id, region_id, completed_items, total_items FROM download_session WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $session = $result->fetch_assoc();
        
        // Update session status
        $sql = "UPDATE download_session SET status = ? WHERE session_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $status, $session_id);
        $stmt->execute();
        
        // Update log
        $progress = 0;
        if ($session['total_items'] > 0) {
            $progress = round(($session['completed_items'] / $session['total_items']) * 100);
        }
        
        logDownloadAction(
            $conn, 
            $session['year'], 
            $session['project_id'], 
            $session['region_id'], 
            $status, 
            $progress, 
            $session['total_items'], 
            $session['completed_items'], 
            $error_message
        );
        
        return true;
    }
    
    return false;
}

// Start the download process
function startDownloadProcess($conn, $session_id, $year, $project_id, $region_id, $force_refresh, $log_id) {
    // In a real implementation, this would be a background task
    // For simplicity, we'll simulate the process
    
    try {
        // Add initial log
        addSessionLog($conn, $session_id, "Memulai proses download untuk tahun $year", 'info');
        
        // Get years to process
        $years = [];
        if ($year === 'all') {
            $years = array_keys(getAvailableYears());
        } else {
            $years = [$year];
        }
        
        // Estimate total items
        $total_items = calculateTotalItems($year, $project_id, $region_id);
        $completed_items = 0;
        
        // Process each year
        foreach ($years as $currentYear) {
            // Check if we should stop
            if (shouldStopDownload($conn, $session_id)) {
                addSessionLog($conn, $session_id, "Download dihentikan oleh pengguna", 'warning');
                break;
            }
            
            addSessionLog($conn, $session_id, "Memproses data tahun $currentYear", 'info');
            
            // Get projects for this year
            $projects = [];
            if ($project_id === 'all') {
                $apiResponse = callApi('https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll',
                    http_build_query(['year' => $currentYear])
                );
                
                $jsonData = json_decode(extractJson($apiResponse), true);
                if ($jsonData && $jsonData['status'] && !empty($jsonData['data'])) {
                    foreach ($jsonData['data'] as $project) {
                        $projects[] = $project['id'];
                    }
                }
            } else {
                $projects = [$project_id];
            }
            
            // Process each project
            foreach ($projects as $currentProject) {
                // Check if we should stop
                if (shouldStopDownload($conn, $session_id)) {
                    break;
                }
                
                addSessionLog($conn, $session_id, "Memproses kegiatan ID $currentProject", 'info');
                
                // Get regions for this project
                $regions = [];
                if ($region_id === 'all') {
                    $apiResponse = callApi('https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll',
                        http_build_query(['id_project' => $currentProject])
                    );
                    
                    $jsonData = json_decode(extractJson($apiResponse), true);
                    if ($jsonData && $jsonData['status'] && !empty($jsonData['data'])) {
                        // Add "pusat" region
                        $regions[] = [
                            'id' => 'pusat',
                            'prov' => '00',
                            'kab' => '00'
                        ];
                        
                        // Add other regions
                        foreach ($jsonData['data'] as $region) {
                            $regions[] = [
                                'id' => $region['prov'] . $region['kab'],
                                'prov' => $region['prov'],
                                'kab' => $region['kab']
                            ];
                        }
                    }
                } else if ($region_id === 'pusat') {
                    $regions[] = [
                        'id' => 'pusat',
                        'prov' => '00',
                        'kab' => '00'
                    ];
                } else {
                    // Extract prov and kab from region_id
                    $prov = substr($region_id, 0, 2);
                    $kab = substr($region_id, 2);
                    
                    // Check if this is a province (kab is "00")
                    if ($kab === "00") {
                        // Add the province itself
                        $regions[] = [
                            'id' => $region_id,
                            'prov' => $prov,
                            'kab' => $kab
                        ];
                        
                        // Get all districts in this province
                        $apiResponse = callApi('https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll',
                            http_build_query(['id_project' => $currentProject])
                        );
                        
                        $jsonData = json_decode(extractJson($apiResponse), true);
                        if ($jsonData && $jsonData['status'] && !empty($jsonData['data'])) {
                            // Add all districts that belong to this province
                            $districtCount = 0;
                            foreach ($jsonData['data'] as $region) {
                                if ($region['prov'] === $prov && $region['kab'] !== "00") {
                                    $regions[] = [
                                        'id' => $region['prov'] . $region['kab'],
                                        'prov' => $region['prov'],
                                        'kab' => $region['kab']
                                    ];
                                    $districtCount++;
                                }
                            }
                            
                            // Add summary log about districts
                            addSessionLog($conn, $session_id, "Ditemukan $districtCount kabupaten/kota di provinsi $prov yang akan diproses", 'info');
                        }
                    } else {
                        // Just a single district
                        $regions[] = [
                            'id' => $region_id,
                            'prov' => $prov,
                            'kab' => $kab
                        ];
                    }
                }
                
                // Process each region
                foreach ($regions as $currentRegion) {
                    // Check if we should stop
                    if (shouldStopDownload($conn, $session_id)) {
                        break;
                    }
                    
                    addSessionLog($conn, $session_id, "Memproses wilayah {$currentRegion['id']}", 'info');
                    
                    // Get gates for this project
                    $apiResponse = callApi('https://webapps.bps.go.id/nqaf/qgate/api/gates/fetchAll',
                        http_build_query(['id_project' => $currentProject])
                    );
                    
                    $jsonData = json_decode(extractJson($apiResponse), true);
                    if ($jsonData && $jsonData['status'] && !empty($jsonData['data'])) {
                        $gates = $jsonData['data'];
                        
                        // Save gates data
                        $cacheKey = json_encode(['action' => 'fetchGates', 'id_project' => $currentProject]);
                        saveToLocalDB(
                            $conn, 
                            $currentYear, 
                            $currentProject, 
                            $currentRegion['id'], 
                            'fetchGates', 
                            $cacheKey, 
                            json_encode($jsonData)
                        );
                        
                        // Process each gate
                        foreach ($gates as $gate) {
                            // Check if we should stop
                            if (shouldStopDownload($conn, $session_id)) {
                                break;
                            }
                            
                            // Get measurements for this gate
                            $apiResponse = callApi('https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAll',
                                http_build_query([
                                    'id_project' => $currentProject,
                                    'id_gate' => $gate['id'],
                                    'prov' => $currentRegion['prov'],
                                    'kab' => $currentRegion['kab']
                                ])
                            );
                            
                            $jsonData = json_decode(extractJson($apiResponse), true);
                            if ($jsonData && $jsonData['status']) {
                                // Save measurements data
                                $cacheKey = json_encode([
                                    'action' => 'fetchMeasurements', 
                                    'id_project' => $currentProject, 
                                    'id_gate' => $gate['id'],
                                    'prov' => $currentRegion['prov'],
                                    'kab' => $currentRegion['kab']
                                ]);
                                
                                saveToLocalDB(
                                    $conn, 
                                    $currentYear, 
                                    $currentProject, 
                                    $currentRegion['id'], 
                                    'fetchMeasurements', 
                                    $cacheKey, 
                                    json_encode($jsonData)
                                );
                                
                                // Process other API endpoints for this gate/region
                                downloadExtraData(
                                    $conn, 
                                    $session_id, 
                                    $currentYear, 
                                    $currentProject, 
                                    $gate, 
                                    $currentRegion
                                );
                                
                                // Update progress
                                $completed_items++;
                                updateDownloadProgress($conn, $session_id, $completed_items, $total_items);
                                
                                // Simulate delay for UI responsiveness
                                usleep(200000); // 0.2 seconds
                            }
                        }
                    }
                }
            }
        }
        
        // Mark download as completed
        completeDownload($conn, $session_id, 'completed');
        addSessionLog($conn, $session_id, "Download selesai", 'success');
        
    } catch (Exception $e) {
        // Log error
        addSessionLog($conn, $session_id, "Error: " . $e->getMessage(), 'error');
        completeDownload($conn, $session_id, 'error', $e->getMessage());
    }
}

// Download additional data for a gate/region
function downloadExtraData($conn, $session_id, $year, $project_id, $gate, $region) {
    // Check if we should stop
    if (shouldStopDownload($conn, $session_id)) {
        return;
    }
    
    // List of API endpoints to download
    $endpoints = [
        [
            'action' => 'fetchAssessments',
            'url' => 'https://webapps.bps.go.id/nqaf/qgate/api/Measurements/fetchAllAssessments',
            'params' => [
                'id_project' => $project_id,
                'id_gate' => $gate['id'],
                'prov' => $region['prov'],
                'kab' => $region['kab']
            ]
        ],
        [
            'action' => 'fetchAllActions',
            'url' => 'https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAllActions',
            'params' => [
                'id_project' => $project_id,
                'id_gate' => $gate['id'],
                'prov' => $region['prov'],
                'kab' => $region['kab']
            ]
        ],
        [
            'action' => 'fetchNeedCorrectives',
            'url' => 'https://webapps.bps.go.id/nqaf/qgate/api/assessments/fetchNeedCorrectives',
            'params' => [
                'id_project' => $project_id,
                'id_gate' => $gate['id'],
                'prov' => $region['prov'],
                'kab' => $region['kab'],
                'year' => $year
            ]
        ]
    ];
    
    foreach ($endpoints as $endpoint) {
        // Check if we should stop
        if (shouldStopDownload($conn, $session_id)) {
            return;
        }
        
        try {
            $apiResponse = callApi($endpoint['url'], http_build_query($endpoint['params']));
            $jsonData = json_decode(extractJson($apiResponse), true);
            
            if ($jsonData && $jsonData['status']) {
                // Create cache key
                $cacheKey = json_encode(array_merge(['action' => $endpoint['action']], $endpoint['params']));
                
                // Also save with monitoring-compatible format cache key
                $monitoringCacheKey = json_encode(['action' => $endpoint['action'], ...$endpoint['params']]);
                
                // Save to database with both formats
                saveToLocalDB(
                    $conn, 
                    $year, 
                    $project_id, 
                    $region['id'], 
                    $endpoint['action'], 
                    $cacheKey, 
                    json_encode($jsonData)
                );
                
                // Save again with monitoring-compatible format
                saveToLocalDB(
                    $conn, 
                    $year, 
                    $project_id, 
                    $region['id'], 
                    $endpoint['action'], 
                    $monitoringCacheKey, 
                    json_encode($jsonData)
                );
                
                // Add log
                addSessionLog(
                    $conn, 
                    $session_id, 
                    "Data {$endpoint['action']} untuk gate {$gate['id']} wilayah {$region['id']} berhasil disimpan", 
                    'info'
                );
            }
        } catch (Exception $e) {
            // Just log the error but continue with other endpoints
            addSessionLog(
                $conn, 
                $session_id, 
                "Error mengunduh {$endpoint['action']} untuk gate {$gate['id']} wilayah {$region['id']}: " . $e->getMessage(), 
                'error'
            );
        }
    }
}

// Check if download should be stopped
function shouldStopDownload($conn, $session_id) {
    $sql = "SELECT status FROM download_session WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['status'] === 'stopped';
    }
    
    return true; // Default to stop if session not found
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Download Data Quality Gates</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root {
      --primary-color: #0071e3; /* Apple blue */
      --success-color: #34c759; /* Apple green */
      --warning-color: #ff9f0a; /* Apple orange */
      --danger-color: #ff3b30;  /* Apple red */
      --neutral-color: #8e8e93; /* Apple gray */
      --light-color: #f5f5f7;   /* Apple light gray */
      --dark-color: #1d1d1f;    /* Apple dark */
      --border-color: #d2d2d7;  /* Apple border */
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background-color: #f5f5f7;
      color: #1d1d1f;
      line-height: 1.5;
      margin: 0;
      padding: 0;
    }
    
    .container-fluid {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 2rem;
      color: var(--dark-color);
      font-size: 2rem;
    }
    
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.04);
      border: none;
      margin-bottom: 2rem;
      background-color: #ffffff;
      overflow: hidden;
    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid var(--border-color);
      padding: 1.25rem 1.5rem;
      font-weight: 500;
      font-size: 1rem;
      color: var(--dark-color);
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid var(--border-color);
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      background-color: #fff;
      transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(0,113,227,0.15);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
      background-color: #005bbc;
      border-color: #005bbc;
      transform: translateY(-1px);
    }
    
    .btn-danger {
      background-color: var(--danger-color);
      border-color: var(--danger-color);
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-danger:hover {
      background-color: #e02e21;
      border-color: #e02e21;
      transform: translateY(-1px);
    }
    
    /* Custom styling */
    #logContainer {
      max-height: 300px;
      overflow-y: auto;
      background-color: #1d1d1f;
      color: #f5f5f7;
      border-radius: 8px;
      padding: 1rem;
      font-family: monospace;
      margin-top: 1rem;
    }
    
    .progress {
      height: 0.8rem;
      border-radius: 100px;
      background-color: rgba(0,0,0,0.05);
      margin-top: 1rem;
    }
    
    .progress-bar {
      background-color: var(--primary-color);
      border-radius: 100px;
    }
    
    .log-entry {
      margin-bottom: 0.5rem;
      line-height: 1.4;
    }
    
    .log-time {
      color: #8e8e93;
      margin-right: 0.5rem;
    }
    
    .log-info {
      color: #0071e3;
    }
    
    .log-success {
      color: #34c759;
    }
    
    .log-error {
      color: #ff3b30;
    }
    
    .log-warning {
      color: #ff9f0a;
    }
    
    #downloadSummary {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 8px;
      background-color: rgba(0,113,227,0.05);
    }
    
    .nav-link {
      font-weight: 500;
      color: var(--dark-color);
    }
    
    .nav-link.active {
      color: var(--primary-color) !important;
      font-weight: 600;
    }
    
    .nav-pills .nav-link.active {
      background-color: rgba(0,113,227,0.1);
    }
    
    .spinner-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(255,255,255,0.7);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    
    .spinner-container {
      background-color: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      text-align: center;
    }
    
    .spinner-text {
      margin-top: 1rem;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <header class="d-flex justify-content-between align-items-center mb-4">
      <h1><i class="fas fa-cloud-download-alt me-3"></i>Download Data Quality Gates</h1>
      <a href="monitoring.php" class="btn btn-outline-primary"><i class="fas fa-chart-line me-2"></i>Go to Monitoring</a>
    </header>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-pills mb-4" id="downloadTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="download-tab" data-bs-toggle="tab" data-bs-target="#download-pane" type="button" role="tab" aria-controls="download-pane" aria-selected="true">
          <i class="fas fa-download me-2"></i>Download Data
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab" aria-controls="logs-pane" aria-selected="false">
          <i class="fas fa-history me-2"></i>Download History
        </button>
      </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="downloadTabsContent">
      <!-- Download Tab -->
      <div class="tab-pane fade show active" id="download-pane" role="tabpanel" aria-labelledby="download-tab">
        <!-- Download Form -->
        <div class="card mb-4">
          <div class="card-header">
            <i class="fas fa-filter me-2"></i>Select Data to Download
          </div>
          <div class="card-body">
            <form id="downloadForm">
              <div class="row g-4">
                <div class="col-md-3">
                  <label for="yearSelect" class="form-label">Tahun</label>
                  <select id="yearSelect" class="form-select" required>
                    <option value="">Pilih Tahun</option>
                    <?php foreach(getAvailableYears() as $key => $value): ?>
                    <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php endforeach; ?>
                    <option value="all">Semua Tahun</option>
                  </select>
                </div>
                <div class="col-md-5">
                  <label for="projectSelect" class="form-label">Pilih Kegiatan</label>
                  <select id="projectSelect" class="form-select" required>
                    <option value="">Pilih Kegiatan</option>
                    <option value="all">Semua Kegiatan</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="regionSelect" class="form-label">Pilih Cakupan Wilayah</label>
                  <select id="regionSelect" class="form-select" required>
                    <option value="">Pilih Cakupan Wilayah</option>
                    <option value="all">Semua Wilayah</option>
                  </select>
                  <small id="regionHelp" class="form-text text-muted d-none">
                    Jika memilih provinsi, semua kabupaten/kota dalam provinsi tersebut juga akan diunduh.
                  </small>
                </div>
              </div>
              
              <div class="d-flex justify-content-between mt-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="forceRefresh">
                  <label class="form-check-label" for="forceRefresh">
                    Paksa muat ulang data (abaikan data cache)
                  </label>
                </div>
                <div>
                  <button type="submit" id="startDownload" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Mulai Download
                  </button>
                  <button type="button" id="stopDownload" class="btn btn-danger d-none">
                    <i class="fas fa-stop-circle me-2"></i>Stop Download
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
        
        <!-- Download Progress -->
        <div id="downloadProgress" class="card mb-4 d-none">
          <div class="card-header">
            <i class="fas fa-tasks me-2"></i>Progress Download
          </div>
          <div class="card-body">
            <div class="progress">
              <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            
            <div id="downloadSummary" class="row mt-3">
              <div class="col-md-3">
                <div class="fw-bold mb-1">Status:</div>
                <div id="downloadStatus">Menunggu...</div>
              </div>
              <div class="col-md-3">
                <div class="fw-bold mb-1">Item Selesai:</div>
                <div id="completedItems">0 / 0</div>
              </div>
              <div class="col-md-3">
                <div class="fw-bold mb-1">Waktu Mulai:</div>
                <div id="startTime">-</div>
              </div>
              <div class="col-md-3">
                <div class="fw-bold mb-1">Estimasi Selesai:</div>
                <div id="estimatedEnd">-</div>
              </div>
            </div>
            
            <h6 class="mt-4 mb-2">Log Download:</h6>
            <div id="logContainer"></div>
          </div>
        </div>
      </div>
      
      <!-- Logs Tab -->
      <div class="tab-pane fade" id="logs-pane" role="tabpanel" aria-labelledby="logs-tab">
        <div class="card">
          <div class="card-header">
            <i class="fas fa-history me-2"></i>Riwayat Download
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Tahun</th>
                    <th>Kegiatan</th>
                    <th>Wilayah</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Durasi</th>
                  </tr>
                </thead>
                <tbody id="logsTableBody">
                  <!-- Log entries will be loaded here -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Spinner -->
  <div id="spinner" class="spinner-overlay" style="display: none;">
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <div class="spinner-text">Memuat data...</div>
    </div>
  </div>

  <!-- SweetAlert2 for notifications -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    $(function() {
      // API endpoint
      const API_URL = "api.php";
      
      // DOM elements
      const $yearSelect = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect = $("#regionSelect");
      const $forceRefresh = $("#forceRefresh");
      const $startDownload = $("#startDownload");
      const $stopDownload = $("#stopDownload");
      const $downloadForm = $("#downloadForm");
      const $downloadProgress = $("#downloadProgress");
      const $progressBar = $downloadProgress.find(".progress-bar");
      const $downloadStatus = $("#downloadStatus");
      const $completedItems = $("#completedItems");
      const $startTime = $("#startTime");
      const $estimatedEnd = $("#estimatedEnd");
      const $logContainer = $("#logContainer");
      const $spinner = $("#spinner");
      const $logsTableBody = $("#logsTableBody");
      
      // Variables
      let selectedYear = '';
      let selectedProject = '';
      let selectedRegion = '';
      let isDownloading = false;
      let shouldStopDownload = false;
      let downloadStartTime = null;
      let downloadItemsCompleted = 0;
      let downloadTotalItems = 0;
      let downloadProgressCheckInterval = null;
      
      // --- Helper Functions ---
      
      const showError = message => {
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: message,
          confirmButtonColor: '#0071e3'
        });
      };
      
      const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleString('id-ID');
      };
      
      const formatDuration = (startTime, endTime) => {
        if (!startTime || !endTime) return '-';
        
        const start = new Date(startTime);
        const end = new Date(endTime);
        const diffMs = end - start;
        
        // Format as minutes:seconds
        const minutes = Math.floor(diffMs / 60000);
        const seconds = Math.floor((diffMs % 60000) / 1000);
        
        return `${minutes}m ${seconds}s`;
      };
      
      const makeAjaxRequest = (url, data) => {
        return new Promise((resolve, reject) => {
          $.ajax({
            url,
            method: "POST",
            data,
            dataType: "text",
            cache: false,
            success: response => {
              try {
                // Extract JSON from response (may contain PHP notices/warnings)
                const jsonStr = response.substring(
                  response.indexOf('{'),
                  response.lastIndexOf('}') + 1
                );
                const jsonData = JSON.parse(jsonStr);
                resolve(jsonData);
              } catch(e) {
                reject("Terjadi kesalahan saat memproses data");
              }
            },
            error: () => reject("Terjadi kesalahan pada server")
          });
        });
      };
      
      // Add a log entry to the log container
      const addLog = (message, type = 'info') => {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID');
        
        const logEntry = `
          <div class="log-entry">
            <span class="log-time">[${timeStr}]</span>
            <span class="log-${type}">${message}</span>
          </div>
        `;
        
        $logContainer.append(logEntry);
        $logContainer.scrollTop($logContainer[0].scrollHeight);
      };
      
      // Update progress bar and status
      const updateProgress = (completed, total, status = null) => {
        const percent = total > 0 ? Math.round((completed / total) * 100) : 0;
        $progressBar.css('width', `${percent}%`).text(`${percent}%`);
        $progressBar.attr('aria-valuenow', percent);
        $completedItems.text(`${completed} / ${total}`);
        
        if (status) {
          $downloadStatus.text(status);
        }
        
        // Update estimated completion time
        if (downloadStartTime && completed > 0 && total > 0 && completed < total) {
          const now = new Date();
          const elapsed = (now - downloadStartTime) / 1000; // in seconds
          const itemsPerSecond = completed / elapsed;
          const remainingItems = total - completed;
          const remainingSeconds = Math.round(remainingItems / itemsPerSecond);
          
          const estimatedEnd = new Date();
          estimatedEnd.setSeconds(estimatedEnd.getSeconds() + remainingSeconds);
          $estimatedEnd.text(estimatedEnd.toLocaleTimeString('id-ID'));
        }
      };
      
      // --- Data Loading Functions ---
      
      // Load projects for selected year
      const loadProjects = async () => {
        $projectSelect.prop('disabled', true);
        $projectSelect.html('<option value="">Memuat data...</option>');
        
        try {
          // Clear any existing options except the defaults
          $projectSelect.empty();
          $projectSelect.append('<option value="">Pilih Kegiatan</option>');
          $projectSelect.append('<option value="all">Semua Kegiatan</option>');
          
          // If "all years" is selected, don't load specific projects
          if (selectedYear === 'all') {
            $projectSelect.prop('disabled', false);
            return;
          }
          
          // Load projects from API
          const response = await makeAjaxRequest(API_URL, {
            action: "fetchProjects",
            year: selectedYear
          });
          
          if (response.status && response.data.length > 0) {
            // Add projects to dropdown
            response.data.forEach(project => {
              $projectSelect.append(`
                <option value="${project.id}">${project.name}</option>
              `);
            });
          } else {
            $projectSelect.append('<option value="" disabled>Tidak ada kegiatan ditemukan</option>');
          }
        } catch (error) {
          showError(error.message || "Gagal memuat daftar kegiatan");
        } finally {
          $projectSelect.prop('disabled', false);
        }
      };
      
      // Load regions for selected project
      const loadRegions = async () => {
        $regionSelect.prop('disabled', true);
        $regionSelect.html('<option value="">Memuat data...</option>');
        
        try {
          // Clear any existing options except the defaults
          $regionSelect.empty();
          $regionSelect.append('<option value="">Pilih Cakupan Wilayah</option>');
          $regionSelect.append('<option value="all">Semua Wilayah</option>');
          
          // If "all projects" is selected, don't load specific regions
          if (selectedProject === 'all' || !selectedProject) {
            $regionSelect.prop('disabled', false);
            return;
          }
          
          // Load regions from API
          const response = await makeAjaxRequest(API_URL, {
            action: "fetchCoverages",
            id_project: selectedProject
          });
          
          if (response.status && response.data.length > 0) {
            // Add "Pusat" option
            $regionSelect.append(`<option value="pusat">Pusat - Nasional</option>`);
            
            // Add provinces
            const provinces = response.data.filter(cov => cov.kab === "00" && cov.prov !== "00");
            provinces.forEach(province => {
              $regionSelect.append(`
                <option value="${province.prov}${province.kab}">${province.name}</option>
              `);
            });
            
            // Add districts if no provinces
            if (provinces.length === 0) {
              const districts = response.data.filter(cov => cov.kab !== "00");
              districts.forEach(district => {
                $regionSelect.append(`
                  <option value="${district.prov}${district.kab}">${district.name}</option>
                `);
              });
            }
          } else {
            $regionSelect.append('<option value="" disabled>Tidak ada wilayah ditemukan</option>');
          }
        } catch (error) {
          showError(error.message || "Gagal memuat daftar wilayah");
        } finally {
          $regionSelect.prop('disabled', false);
        }
      };
      
      // --- Event Handlers ---
      
      // Year selection changed
      $yearSelect.on('change', function() {
        selectedYear = $(this).val();
        selectedProject = '';
        selectedRegion = '';
        
        $projectSelect.val('');
        $regionSelect.val('');
        
        if (selectedYear) {
          loadProjects();
        } else {
          $projectSelect.prop('disabled', true);
          $regionSelect.prop('disabled', true);
        }
      });
      
      // Project selection changed
      $projectSelect.on('change', function() {
        selectedProject = $(this).val();
        selectedRegion = '';
        
        $regionSelect.val('');
        
        if (selectedProject) {
          loadRegions();
        } else {
          $regionSelect.prop('disabled', true);
        }
      });
      
      // Region selection changed
      $regionSelect.on('change', function() {
        selectedRegion = $(this).val();
        
        // Show help text for province selections
        if (selectedRegion && selectedRegion !== 'all' && selectedRegion !== 'pusat' && selectedRegion.endsWith('00')) {
          $('#regionHelp').removeClass('d-none');
        } else {
          $('#regionHelp').addClass('d-none');
        }
      });
      
      // Download logs tab shown - load logs
      $('#logs-tab').on('shown.bs.tab', function() {
        loadDownloadLogs();
      });
      
      // Load download logs from the server
      const loadDownloadLogs = async () => {
        try {
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              download_action: "get_logs"
            },
            dataType: "json"
          });
          
          if (response.status && response.logs) {
            $logsTableBody.empty();
            
            response.logs.forEach(log => {
              const statusClass = log.status === 'completed' ? 'success' : 
                               (log.status === 'error' ? 'danger' : 
                               (log.status === 'in_progress' ? 'warning' : 'secondary'));
              
              const statusText = log.status === 'completed' ? 'Selesai' : 
                              (log.status === 'error' ? 'Error' : 
                              (log.status === 'in_progress' ? 'Sedang Proses' : log.status));
              
              $logsTableBody.append(`
                <tr>
                  <td>${log.id}</td>
                  <td>${log.year === 'all' ? 'Semua Tahun' : log.year}</td>
                  <td>${log.project_id === 'all' ? 'Semua Kegiatan' : log.project_id}</td>
                  <td>${log.region_id === 'all' ? 'Semua Wilayah' : log.region_id}</td>
                  <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                  <td>${log.progress}%</td>
                  <td>${formatDate(log.start_time)}</td>
                  <td>${formatDate(log.end_time)}</td>
                  <td>${formatDuration(log.start_time, log.end_time)}</td>
                </tr>
              `);
            });
          } else {
            $logsTableBody.html(`
              <tr>
                <td colspan="9" class="text-center py-4">Tidak ada data log download</td>
              </tr>
            `);
          }
        } catch (error) {
          console.error("Error loading logs:", error);
          $logsTableBody.html(`
            <tr>
              <td colspan="9" class="text-center py-4 text-danger">Error memuat data log: ${error.message || 'Unknown error'}</td>
            </tr>
          `);
        }
      };
      
      // --- Download Functions ---
      
      // Start the download process
      const startDownload = async () => {
        if (isDownloading) return;
        
        try {
          // Reset UI
          $downloadProgress.removeClass('d-none');
          $startDownload.addClass('d-none');
          $stopDownload.removeClass('d-none');
          $logContainer.empty();
          $progressBar.css('width', '0%').text('0%');
          $progressBar.attr('aria-valuenow', 0);
          $downloadStatus.text('Memulai...');
          $completedItems.text('0 / 0');
          $startTime.text('-');
          $estimatedEnd.text('-');
          
          // Set flags
          isDownloading = true;
          shouldStopDownload = false;
          downloadStartTime = new Date();
          $startTime.text(formatDate(downloadStartTime));
          
          // Add initial logs
          addLog('Memulai proses download...', 'info');
          addLog(`Tahun: ${selectedYear === 'all' ? 'Semua' : selectedYear}`, 'info');
          addLog(`Kegiatan: ${selectedProject === 'all' ? 'Semua' : $('#projectSelect option:selected').text()}`, 'info');
          addLog(`Wilayah: ${selectedRegion === 'all' ? 'Semua' : $('#regionSelect option:selected').text()}`, 'info');
          
          // Start download on server
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              download_action: "start_download",
              year: selectedYear,
              project_id: selectedProject,
              region_id: selectedRegion,
              force_refresh: $forceRefresh.prop('checked') ? 1 : 0
            },
            dataType: "json"
          });
          
          if (response.status) {
            addLog('Download dimulai pada server', 'success');
            
            // Start checking progress
            downloadItemsCompleted = 0;
            downloadTotalItems = response.total_items || 0;
            updateProgress(0, downloadTotalItems, 'Mengunduh...');
            
            // Start progress check interval
            if (downloadProgressCheckInterval) {
              clearInterval(downloadProgressCheckInterval);
            }
            
            downloadProgressCheckInterval = setInterval(checkDownloadProgress, 2000);
          } else {
            throw new Error(response.message || 'Error starting download');
          }
        } catch (error) {
          addLog(`Error: ${error.message || 'Error tidak diketahui'}`, 'error');
          showError(error.message || 'Terjadi kesalahan saat memulai download');
          resetDownload();
        }
      };
      
      // Stop the download process
      const stopDownload = async () => {
        shouldStopDownload = true;
        
        try {
          addLog('Menghentikan download...', 'warning');
          
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              download_action: "stop_download",
              year: selectedYear,
              project_id: selectedProject,
              region_id: selectedRegion
            },
            dataType: "json"
          });
          
          if (response.status) {
            addLog('Download dihentikan oleh pengguna', 'warning');
            updateProgress(downloadItemsCompleted, downloadTotalItems, 'Dihentikan');
            resetDownload(false);
          } else {
            throw new Error(response.message || 'Error stopping download');
          }
        } catch (error) {
          addLog(`Error: ${error.message || 'Error tidak diketahui'}`, 'error');
          showError(error.message || 'Terjadi kesalahan saat menghentikan download');
          resetDownload();
        }
      };
      
      // Check download progress
      const checkDownloadProgress = async () => {
        if (!isDownloading || shouldStopDownload) return;
        
        try {
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              download_action: "get_progress",
              year: selectedYear,
              project_id: selectedProject,
              region_id: selectedRegion
            },
            dataType: "json"
          });
          
          if (response.status) {
            // Update progress UI
            downloadItemsCompleted = response.completed_items || 0;
            downloadTotalItems = response.total_items || 0;
            
            updateProgress(
              downloadItemsCompleted,
              downloadTotalItems,
              response.status_text || 'Mengunduh...'
            );
            
            // Add any new logs
            if (response.logs && response.logs.length) {
              response.logs.forEach(log => {
                addLog(log.message, log.type);
              });
            }
            
            // Check if download is complete
            if (response.is_completed) {
              // Set progress to 100%
              updateProgress(downloadTotalItems, downloadTotalItems, 'Selesai');
              addLog('Download selesai!', 'success');
              
              // Reset UI
              resetDownload(false);
              
              // Show success message
              Swal.fire({
                icon: 'success',
                title: 'Download Selesai',
                text: `${downloadItemsCompleted} item telah berhasil disimpan ke database lokal.`,
                confirmButtonColor: '#0071e3'
              });
              
              // Reload download logs
              loadDownloadLogs();
            }
          } else if (response.is_error) {
            // Handle error
            addLog(`Error: ${response.message || 'Error tidak diketahui'}`, 'error');
            updateProgress(downloadItemsCompleted, downloadTotalItems, 'Error');
            resetDownload(false);
            
            // Show error message
            showError(response.message || 'Terjadi kesalahan saat mengunduh data');
          }
        } catch (error) {
          console.error('Error checking download progress:', error);
          // Don't reset on connection errors, just log them
          addLog(`Error koneksi: ${error.message || 'Error tidak diketahui'}`, 'error');
        }
      };
      
      // Reset download UI and state
      const resetDownload = (clearUI = true) => {
        // Clear interval
        if (downloadProgressCheckInterval) {
          clearInterval(downloadProgressCheckInterval);
          downloadProgressCheckInterval = null;
        }
        
        // Reset flags
        isDownloading = false;
        shouldStopDownload = false;
        
        // Reset UI
        $startDownload.removeClass('d-none');
        $stopDownload.addClass('d-none');
        
        // Clear progress UI if requested
        if (clearUI) {
          $downloadProgress.addClass('d-none');
          $progressBar.css('width', '0%').text('0%');
          $progressBar.attr('aria-valuenow', 0);
          $downloadStatus.text('Menunggu...');
          $completedItems.text('0 / 0');
          $startTime.text('-');
          $estimatedEnd.text('-');
          $logContainer.empty();
        }
      };
      
      // --- Event Handlers ---
      
      // Form submission
      $downloadForm.on('submit', function(e) {
        e.preventDefault();
        
        if (!selectedYear) {
          showError('Silakan pilih tahun!');
          return;
        }
        
        if (!selectedProject) {
          showError('Silakan pilih kegiatan!');
          return;
        }
        
        if (!selectedRegion) {
          showError('Silakan pilih wilayah!');
          return;
        }
        
        startDownload();
      });
      
      // Stop download button
      $stopDownload.on('click', function() {
        if (!isDownloading) return;
        
        Swal.fire({
          title: 'Hentikan Download?',
          text: 'Apakah Anda yakin ingin menghentikan proses download?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ff3b30',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Ya, Hentikan',
          cancelButtonText: 'Batal'
        }).then((result) => {
          if (result.isConfirmed) {
            stopDownload();
          }
        });
      });
      
      // Initialize
      loadDownloadLogs();
    });
  </script>
</body>
</html> 