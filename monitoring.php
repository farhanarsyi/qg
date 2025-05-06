<?php
// monitoring.php - View data from local database (no external API)

require_once 'db_functions.php';

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

// Create necessary tables if they don't exist
createTables($conn);

// AJAX endpoint for local database operations
if (isset($_POST['db_action'])) {
    header('Content-Type: application/json');
    $db_action = $_POST['db_action'];
    
    if ($db_action === 'check_cache') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        
        $useCache = shouldUseCache($conn, $year, $project_id, $region_id);
        echo json_encode(['useCache' => $useCache]);
        exit;
    }
    
    if ($db_action === 'get_processed_data') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        
        $data = getProcessedData($conn, $year, $project_id, $region_id);
        echo json_encode($data);
        exit;
    }
    
    if ($db_action === 'get_data') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : '';
        $cache_key = isset($_POST['cache_key']) ? $_POST['cache_key'] : '';
        
        $data = getFromLocalDB($conn, $year, $project_id, $region_id, $data_type, $cache_key);
        echo json_encode($data);
        exit;
    }
    
    if ($db_action === 'get_available_projects') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        
        if (empty($year)) {
            echo json_encode(['status' => false, 'message' => 'Year is required']);
            exit;
        }
        
        // Debug: Log input parameters
        error_log("get_available_projects for year: " . $year);
        
        // Get distinct project IDs for the selected year
        $year = $conn->real_escape_string($year);
        
        // First, try to find the projects list saved during the fetchProjects call
        $sql = "SELECT data_json FROM qg_sync 
                WHERE year = '$year' AND project_id = 'all' AND data_type = 'fetchProjects' 
                ORDER BY last_updated DESC LIMIT 1";
        
        $result = $conn->query($sql);
        $projects = [];
        
        if ($result && $result->num_rows > 0) {
            // We found the saved projects list
            $row = $result->fetch_assoc();
            $rawJson = $row['data_json'];
            error_log("Raw JSON from projects list: " . substr($rawJson, 0, 200) . "...");
            
            $projectsData = json_decode($rawJson, true);
            
            // Debug: Log structure of projectsData
            error_log("Project data structure keys: " . json_encode(array_keys($projectsData ?? [])));
            error_log("Project data has 'data' key: " . (isset($projectsData['data']) ? 'yes' : 'no'));
            
            if (isset($projectsData['data']) && is_array($projectsData['data'])) {
                // Log first project structure
                if (count($projectsData['data']) > 0) {
                    error_log("First project structure: " . json_encode($projectsData['data'][0]));
                }
                
                // Direct approach - extract just what we need
                foreach ($projectsData['data'] as $project) {
                    $projectId = isset($project['id']) ? $project['id'] : null;
                    $projectName = null;
                    
                    // Try different fields where name might be stored
                    if (isset($project['name'])) {
                        $projectName = $project['name'];
                    } elseif (isset($project['project_name'])) {
                        $projectName = $project['project_name'];
                    }
                    
                    if ($projectId && $projectName) {
                        $projects[] = [
                            'id' => $projectId,
                            'name' => $projectName
                        ];
                        error_log("Added project: $projectId - $projectName");
                    }
                }
            }
        }
        
        // If no projects found yet, try alternative approach
        if (empty($projects)) {
            error_log("No projects found in first attempt, trying alternative approaches...");
            
            // Get distinct project IDs
            $sql = "SELECT DISTINCT project_id FROM qg_sync WHERE year = '$year' AND project_id != 'all'";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $projectId = $row['project_id'];
                    $projectName = null;
                    
                    // Approach 1: Check fetchProjectSpesific
                    $sql1 = "SELECT data_json FROM qg_sync 
                            WHERE year = '$year' AND project_id = '$projectId' 
                            AND data_type = 'fetchProjectSpesific'
                            ORDER BY last_updated DESC LIMIT 1";
                    
                    $result1 = $conn->query($sql1);
                    if ($result1 && $result1->num_rows > 0) {
                        $row1 = $result1->fetch_assoc();
                        $rawJson = $row1['data_json'];
                        $data = json_decode($rawJson, true);
                        
                        // Debug specific project data
                        error_log("Project $projectId specific data: " . substr($rawJson, 0, 200) . "...");
                        error_log("Project $projectId structure keys: " . json_encode(array_keys($data ?? [])));
                        error_log("Project $projectId has 'data' key: " . (isset($data['data']) ? 'yes' : 'no'));
                        if (isset($data['data'])) {
                            error_log("Project $projectId data keys: " . json_encode(array_keys($data['data'] ?? [])));
                        }
                        
                        // Try multiple nested structures
                        if (isset($data['data']['name'])) {
                            $projectName = $data['data']['name'];
                            error_log("Found name in data.name: $projectName");
                        } elseif (isset($data['data']['project_name'])) {
                            $projectName = $data['data']['project_name'];
                            error_log("Found name in data.project_name: $projectName");
                        } elseif (isset($data['name'])) {
                            $projectName = $data['name'];
                            error_log("Found name in name: $projectName");
                        }
                    }
                    
                    // Approach 2: Check if project appears in the full list
                    if (!$projectName) {
                        $sql2 = "SELECT data_json FROM qg_sync 
                                WHERE year = '$year' AND project_id = 'all' 
                                AND data_type = 'fetchProjects'
                                ORDER BY last_updated DESC LIMIT 1";
                        
                        $result2 = $conn->query($sql2);
                        if ($result2 && $result2->num_rows > 0) {
                            $row2 = $result2->fetch_assoc();
                            $projectsList = json_decode($row2['data_json'], true);
                            
                            if (isset($projectsList['data']) && is_array($projectsList['data'])) {
                                foreach ($projectsList['data'] as $p) {
                                    if ($p['id'] == $projectId && isset($p['name'])) {
                                        $projectName = $p['name'];
                                        error_log("Found name in projects list: $projectName");
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // As a last resort, try to get name from any related data
                    if (!$projectName) {
                        $sql3 = "SELECT data_json FROM qg_sync 
                                WHERE year = '$year' AND project_id = '$projectId'
                                ORDER BY last_updated DESC LIMIT 1";
                        
                        $result3 = $conn->query($sql3);
                        if ($result3 && $result3->num_rows > 0) {
                            $row3 = $result3->fetch_assoc();
                            $anyData = json_decode($row3['data_json'], true);
                            
                            // Try to find any field that might contain the name
                            if (isset($anyData['data']['project_name'])) {
                                $projectName = $anyData['data']['project_name'];
                                error_log("Found name in any data.project_name: $projectName");
                            } elseif (isset($anyData['data']['name'])) {
                                $projectName = $anyData['data']['name'];
                                error_log("Found name in any data.name: $projectName");
                            } elseif (isset($anyData['name'])) {
                                $projectName = $anyData['name'];
                                error_log("Found name in any name: $projectName");
                            }
                        }
                    }
                    
                    // If still no name, use ID with a prefix to be clear
                    if (!$projectName) {
                        $projectName = "Kegiatan " . $projectId;
                        error_log("No name found, using fallback: $projectName");
                    }
                    
                    $projects[] = [
                        'id' => $projectId,
                        'name' => $projectName
                    ];
                }
            }
        }
        
        // Sort projects by name for better usability
        usort($projects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        error_log("Final projects array: " . json_encode($projects));
        echo json_encode(['status' => true, 'data' => $projects]);
        exit;
    }
    
    if ($db_action === 'get_available_regions') {
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        
        if (empty($project_id)) {
            echo json_encode(['status' => false, 'message' => 'Project ID is required']);
            exit;
        }
        
        // Get regions for the selected project
        $project_id = $conn->real_escape_string($project_id);
        $sql = "SELECT DISTINCT region_id, 
                (SELECT data_json FROM qg_sync WHERE project_id = '$project_id' AND data_type = 'fetchCoverages' LIMIT 1) as coverage_data
                FROM qg_sync 
                WHERE project_id = '$project_id'";
        
        $result = $conn->query($sql);
        $regions = [];
        
        // Always add "pusat" option
        $regions[] = [
            'id' => 'pusat',
            'prov' => '00',
            'kab' => '00',
            'name' => 'Pusat - Nasional'
        ];
        
        if ($result && $result->num_rows > 0) {
            $coverageData = null;
            
            while ($row = $result->fetch_assoc()) {
                if (empty($coverageData) && !empty($row['coverage_data'])) {
                    $coverageJson = json_decode($row['coverage_data'], true);
                    if (isset($coverageJson['data'])) {
                        $coverageData = $coverageJson['data'];
                    }
                }
                
                // If we have coverageData, we can map region_ids to actual region info
                if (!empty($coverageData)) {
                    foreach ($coverageData as $coverage) {
                        $regionId = $coverage['prov'] . $coverage['kab'];
                        if ($regionId !== '0000' && !in_array($regionId, array_column($regions, 'id'))) {
                            $regions[] = [
                                'id' => $regionId,
                                'prov' => $coverage['prov'],
                                'kab' => $coverage['kab'],
                                'name' => $coverage['name']
                            ];
                        }
                    }
                }
            }
        }
        
        echo json_encode(['status' => true, 'data' => $regions]);
        exit;
    }
    
    if ($db_action === 'get_project_name') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        
        if (empty($year) || empty($project_id)) {
            echo json_encode(['status' => false, 'message' => 'Year and project_id are required']);
            exit;
        }
        
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        
        // Try multiple approaches to get the project name
        $projectName = null;
        
        // Approach 1: Check fetchProjects list
        $sql1 = "SELECT data_json FROM qg_sync 
                WHERE year = '$year' AND project_id = 'all' AND data_type = 'fetchProjects' 
                ORDER BY last_updated DESC LIMIT 1";
        
        $result1 = $conn->query($sql1);
        if ($result1 && $result1->num_rows > 0) {
            $row1 = $result1->fetch_assoc();
            $projectsList = json_decode($row1['data_json'], true);
            
            if (isset($projectsList['data']) && is_array($projectsList['data'])) {
                foreach ($projectsList['data'] as $project) {
                    if ($project['id'] == $project_id && isset($project['name'])) {
                        $projectName = $project['name'];
                        break;
                    }
                }
            }
        }
        
        // Approach 2: Check project-specific data
        if (!$projectName) {
            $sql2 = "SELECT data_json FROM qg_sync 
                    WHERE year = '$year' AND project_id = '$project_id' AND data_type = 'fetchProjectSpesific'
                    ORDER BY last_updated DESC LIMIT 1";
            
            $result2 = $conn->query($sql2);
            if ($result2 && $result2->num_rows > 0) {
                $row2 = $result2->fetch_assoc();
                $projectData = json_decode($row2['data_json'], true);
                
                if (isset($projectData['data']['project_name'])) {
                    $projectName = $projectData['data']['project_name'];
                } elseif (isset($projectData['data']['name'])) {
                    $projectName = $projectData['data']['name'];
                } elseif (isset($projectData['name'])) {
                    $projectName = $projectData['name'];
                }
            }
        }
        
        // Approach 3: Check any data for this project
        if (!$projectName) {
            $sql3 = "SELECT data_json FROM qg_sync 
                    WHERE year = '$year' AND project_id = '$project_id'
                    ORDER BY last_updated DESC LIMIT 1";
            
            $result3 = $conn->query($sql3);
            if ($result3 && $result3->num_rows > 0) {
                $row3 = $result3->fetch_assoc();
                $anyData = json_decode($row3['data_json'], true);
                
                if (isset($anyData['data']['project_name'])) {
                    $projectName = $anyData['data']['project_name'];
                } elseif (isset($anyData['data']['name'])) {
                    $projectName = $anyData['data']['name'];
                } elseif (isset($anyData['name'])) {
                    $projectName = $anyData['name'];
                }
            }
        }
        
        // Fallback
        if (!$projectName) {
            $projectName = "Kegiatan " . $project_id;
        }
        
        echo json_encode(['status' => true, 'project_name' => $projectName]);
        exit;
    }
    
    echo json_encode(['status' => false, 'message' => 'Invalid db_action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Monitoring Quality Gates</title>
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
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    
    .btn-primary:active {
      transform: translateY(0);
    }
    
    .table-wrapper {
      overflow: auto;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin: 0;
      max-height: 600px; /* Fixed height untuk tabel */
    }
    
    .table-monitoring {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #fff;
    }
    
    .table-monitoring th {
      background-color: #f5f5f7;
      font-weight: 500;
      padding: 1rem 1.25rem;
      font-size: 0.9rem;
      color: var(--dark-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-monitoring td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-monitoring tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:hover {
      background-color: rgba(0,113,227,0.03);
    }
    
    /* Status badges */
    .status-badge {
      padding: 0.35rem 0.75rem;
      border-radius: 100px;
      font-weight: 500;
      font-size: 0.8rem;
      text-align: center;
      white-space: nowrap;
      display: inline-block;
    }
    
    .status-success {
      background-color: rgba(52,199,89,0.12);
      color: var(--success-color);
    }
    
    .status-danger {
      background-color: rgba(255,59,48,0.12);
      color: var(--danger-color);
    }
    
    .status-warning {
      background-color: rgba(255,159,10,0.12);
      color: var(--warning-color);
    }
    
    .status-neutral {
      background-color: rgba(142,142,147,0.12);
      color: var(--neutral-color);
    }
    
    /* Gate dan UK codes */
    .gate-code, .uk-code {
      font-weight: 600;
      color: var(--primary-color);
      margin-right: 5px;
    }
    
    /* Spinner overlay */
    #spinner {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(255,255,255,0.9);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    
    .spinner-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1.5rem;
      padding: 2rem;
      border-radius: 16px;
      background-color: rgba(255,255,255,0.8);
      backdrop-filter: blur(10px);
      box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    }
    
    .spinner-text {
      font-weight: 500;
      color: var(--primary-color);
      font-size: 1rem;
    }
    
    /* Region header styles */
    .region-header {
      font-size: 0.85rem;
      font-weight: 500;
      text-align: center;
      background-color: rgba(0,113,227,0.04);
    }
    
    /* Column widths */
    .table-monitoring th:nth-child(1), .table-monitoring td:nth-child(1) { min-width: 200px; }
    .table-monitoring th:nth-child(2), .table-monitoring td:nth-child(2) { min-width: 220px; }
    .table-monitoring th:nth-child(3), .table-monitoring td:nth-child(3) { min-width: 100px; text-align: center; }
    .table-monitoring th:nth-child(4), .table-monitoring td:nth-child(4) { min-width: 280px; }
    .table-monitoring th:nth-child(5), .table-monitoring td:nth-child(5), 
    .table-monitoring th:nth-child(6), .table-monitoring td:nth-child(6) { min-width: 160px; text-align: center; }
    
    /* Date in range (blinking effect) */
    .date-in-range {
      color: var(--success-color);
      font-weight: 600;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.7; }
      100% { opacity: 1; }
    }
    
    .date-column { text-align: center; }
    
    /* Row colors alternating by UK group */
    .table-monitoring .uk-group-even { background-color: #ffffff; }
    .table-monitoring .uk-group-odd { background-color: rgba(245,245,247,0.4); }
    
    /* Activity number */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 24px;
      height: 24px;
      border-radius: 12px;
      background-color: rgba(0,113,227,0.1);
      color: var(--primary-color);
      font-weight: 600;
      margin-right: 12px;
      font-size: 0.75rem;
    }
    
    /* Status column */
    .status-column {
      min-width: 120px;
      text-align: center;
      white-space: nowrap;
    }
    
    /* Row hover */
    .table-monitoring tr:hover td { background-color: rgba(0,113,227,0.04); }
    
    /* Responsiveness */
    @media (max-width: 992px) {
      .container-fluid { padding: 1.5rem; }
      .card-body { padding: 1.25rem; }
    }
    
    @media (max-width: 768px) {
      .container-fluid { padding: 1rem; }
      h1 { font-size: 1.5rem; margin-bottom: 1.5rem; }
      .card-header { padding: 1rem; }
      .card-body { padding: 1rem; }
    }
    
    /* No data message */
    .no-data-message {
      text-align: center;
      padding: 3rem;
      color: var(--neutral-color);
    }
    
    /* Download link */
    .download-link {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      margin-top: 1rem;
    }
    
    .download-link:hover {
      text-decoration: underline;
    }
    
    .download-link i {
      margin-right: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <h1><i class="fas fa-tasks me-3"></i>Monitoring Quality Gates</h1>
    
    <!-- Navigation Menu -->
    <div class="mb-4">
      <a href="download.php" class="download-link">
        <i class="fas fa-download"></i> Pergi ke halaman Download Data
      </a>
    </div>
    
    <!-- Input Filters -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Data</span>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-3">
            <label for="yearSelect" class="form-label">Tahun</label>
            <select id="yearSelect" class="form-select">
              <option value="">Pilih Tahun</option>
              <option value="2023">2023</option>
              <option value="2024">2024</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="col-md-5">
            <label for="projectSelect" class="form-label">Pilih Kegiatan</label>
            <select id="projectSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-3">
            <label for="regionSelect" class="form-label">Pilih Cakupan Wilayah</label>
            <select id="regionSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-1 d-flex align-items-end">
            <button id="loadData" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
              <i class="fas fa-search me-2"></i>Tampilkan
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer"></div>
  </div>

  <!-- Spinner Loading -->
  <div id="spinner">
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
      <div class="spinner-text">Memuat data...</div>
    </div>
  </div>

  <script>
    $(function(){
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      let activityData = {}; // Untuk menyimpan data status per aktivitas per wilayah
      
      // Cache selector DOM
      const $yearSelect    = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect  = $("#regionSelect");
      const $resultsContainer = $("#resultsContainer");
      const $spinner       = $("#spinner");

      // --- Helper Functions ---
      const showError = message => {
        console.error(message);
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: message,
          confirmButtonColor: '#0071e3'
        });
      };

      const formatDate = dateStr => {
        if (!dateStr || dateStr === '-') return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
          const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
          return `${parts[2]} ${months[parseInt(parts[1]) - 1]} ${parts[0]}`;
        }
        return dateStr;
      };
      
      // Function to check if date is within range
      const isDateInRange = (startDateStr, endDateStr) => {
        if (!startDateStr || !endDateStr || startDateStr === '-' || endDateStr === '-') return false;
        
        const today = new Date();
        today.setHours(0, 0, 0, 0); // Reset time to start of day
        
        const parts = startDateStr.split('-');
        const startDate = new Date(parts[0], parts[1] - 1, parts[2]);
        startDate.setHours(0, 0, 0, 0);
        
        const endParts = endDateStr.split('-');
        const endDate = new Date(endParts[0], endParts[1] - 1, endParts[2]);
        endDate.setHours(23, 59, 59, 999); // Set to end of day
        
        return today >= startDate && today <= endDate;
      };

      const getStatusBadge = status => {
        if (status.startsWith('Sudah')) return `<span class="status-badge status-success">${status}</span>`;
        if (status.startsWith('Belum')) return `<span class="status-badge status-danger">${status}</span>`;
        if (status === 'Tidak perlu') return `<span class="status-badge status-neutral">${status}</span>`;
        return `<span class="status-badge status-warning">${status}</span>`;
      };

      // --- Fungsi untuk mengecek apakah ukuran kualitas sesuai dengan level wilayah ---
      const isUkApplicableForRegion = (measurement, region) => {
        // Dapatkan assessment_level (default 1 jika tidak ada)
        const level = parseInt(measurement.assessment_level || 1);
        
        // Cek apakah ini pusat (kode 00)
        const isPusat = region.prov === "00" && region.kab === "00";
        if (isPusat) return level === 1;
        
        // Cek apakah ini provinsi (kab = 00, prov != 00)
        const isProvinsi = region.prov !== "00" && region.kab === "00";
        if (isProvinsi) return level === 2;
        
        // Selain itu, ini kabupaten/kota
        return level === 3;
      };
      
      // Fungsi untuk mendapatkan label level ukuran kualitas
      const getUkLevelLabel = (measurement) => {
        const level = parseInt(measurement.assessment_level || 1);
        if (level === 1) return "Pusat";
        if (level === 2) return "Provinsi";
        if (level === 3) return "Kabupaten";
        return "Tidak diketahui";
      };

      // --- Fungsi untuk menentukan status suatu aktivitas ---
      const determineActivityStatus = async (gate, measurement, year, activityName, prov, kab, apiCache, getDataFromCacheOrApi) => {
        // Dapatkan data actions yang sudah di-cache
        const actionsKey = JSON.stringify({ 
          action: 'fetchAllActions', 
          id_project: selectedProject, 
          id_gate: gate.id, 
          prov, kab 
        });
        
        const actionsResponse = apiCache.allActions[actionsKey] || { status: false };
        
        // Jika tidak ada data actions, semua status "Belum"
        if (!actionsResponse.status || !actionsResponse.data || actionsResponse.data.length === 0) {
          if (activityName === "Pengisian nama pelaksana aksi preventif") return "Belum ditentukan";
          if (activityName === "Upload bukti pelaksanaan aksi preventif") return "Belum ditentukan";
          if (activityName === "Penilaian ukuran kualitas") return "Belum dinilai";
          if (activityName === "Approval Gate oleh Sign-off") return "Belum dinilai";
          if (activityName === "Pengisian pelaksana aksi korektif") return "Belum disetujui";
          if (activityName === "Upload bukti pelaksanaan aksi korektif") return "Belum disetujui";
        }
        
        // Jika ini tentang preventif
        if (activityName === "Pengisian nama pelaksana aksi preventif") {
          const prevMeasureResponse = await getDataFromCacheOrApi(
            'preventives',
            'fetchPreventivesByMeasurement',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          return (prevMeasureResponse.status && prevMeasureResponse.data.length > 0) 
            ? "Sudah ditentukan" : "Belum ditentukan";
        }
        
        if (activityName === "Upload bukti pelaksanaan aksi preventif") {
          // Cek dulu apakah nama pelaksana sudah diisi
          const prevMeasureResponse = await getDataFromCacheOrApi(
            'preventives',
            'fetchPreventivesByMeasurement',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          const isPrevNameFilled = prevMeasureResponse.status && prevMeasureResponse.data.length > 0;
          
          if (!isPrevNameFilled) {
            return "Belum ditentukan";
          }
          
          const prevKabResponse = await getDataFromCacheOrApi(
            'preventivesKab',
            'fetchPreventivesByKab',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          return (prevKabResponse.status && prevKabResponse.data.length > 0 && prevKabResponse.data[0].filename)
            ? "Sudah diunggah" : "Belum diunggah";
        }
        
        // Dapatkan data assessments dari cache
        const assessmentsKey = JSON.stringify({ 
          action: 'fetchAssessments', 
          id_project: selectedProject, 
          id_gate: gate.id, 
          prov, kab 
        });
        
        const assessmentsResponse = apiCache.assessments[assessmentsKey] || { status: false };
        
        // Cek apakah measurement sudah dinilai
        const measurementAssessment = assessmentsResponse.status
          ? assessmentsResponse.data.find(m => m.id == measurement.id)
          : null;
        
        let assessmentStatus = "Belum dinilai";
        let isAssessed = false;
        let isApproved = false;
        
        if (measurementAssessment && measurementAssessment.assessment != null) {
          const val = measurementAssessment.assessment;
          if (val === "1" || val === 1) {
            assessmentStatus = "Sudah dinilai (merah)";
          } else if (val === "2" || val === 2) {
            assessmentStatus = "Sudah dinilai (kuning)";
          } else if (val === "3" || val === 3) {
            assessmentStatus = "Sudah dinilai (hijau)";
          }
          isAssessed = assessmentStatus.startsWith("Sudah dinilai");
          isApproved = measurementAssessment.state === "1";
        }
        
        // Status penilaian
        if (activityName === "Penilaian ukuran kualitas") {
          return assessmentStatus;
        }
        
        // Status approval
        if (activityName === "Approval Gate oleh Sign-off") {
          if (!isAssessed) return "Belum dinilai";
          return isApproved ? "Sudah disetujui" : "Belum disetujui";
        }
        
        // Aksi korektif
        if (activityName === "Pengisian pelaksana aksi korektif" || activityName === "Upload bukti pelaksanaan aksi korektif") {
          if (!isApproved) return "Belum disetujui";
          if (assessmentStatus === "Sudah dinilai (hijau)") return "Tidak perlu";
          
          // Untuk aksi korektif, lanjutkan hanya jika merah/kuning
          if (activityName === "Pengisian pelaksana aksi korektif") {
            const corMeasureResponse = await getDataFromCacheOrApi(
              'correctives',
              'fetchCorrectivesByMeasurement',
              {
                year, id_project: selectedProject, id_gate: gate.id,
                id_measurement: measurement.id, prov, kab
              }
            );
            
            return (corMeasureResponse.status && corMeasureResponse.data.length > 0)
              ? "Sudah ditentukan" : "Belum ditentukan";
          }
          
          // Upload bukti korektif
          const corMeasureResponse = await getDataFromCacheOrApi(
            'correctives',
            'fetchCorrectivesByMeasurement',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          if (!(corMeasureResponse.status && corMeasureResponse.data.length > 0)) {
            return "Belum ditentukan";
          }
          
          const corKabResponse = await getDataFromCacheOrApi(
            'correctivesKab',
            'fetchCorrectivesByKab',
            {
              data: {
                year, id_project: selectedProject, id_gate: gate.id,
                id_measurement: measurement.id, prov, kab
              }
            }
          );
          
          return (corKabResponse.status && corKabResponse.data.length > 0 && corKabResponse.data[0].filename)
            ? "Sudah diunggah" : "Belum diunggah";
        }
        
        return "Tidak tersedia";
      };

      // --- Fungsi untuk membuat dan menampilkan tabel hasil ---
      const displayResultTable = (regions) => {
        // Urutkan regions berdasarkan kode, bukan nama
        regions.sort((a, b) => {
          // Pusat selalu di awal
          if (a.id === "pusat") return -1;
          if (b.id === "pusat") return 1;
          
          // Urutkan berdasarkan kode prov dan kab
          return a.id.localeCompare(b.id);
        });
        
        // Buat header tabel dengan kolom status untuk setiap wilayah
        let tableHtml = `
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span>Hasil Monitoring</span>
              <span id="resultCount" class="badge bg-primary rounded-pill">${Object.keys(activityData).filter(key => !key.startsWith('_')).length} aktivitas</span>
            </div>
            <div class="card-body p-0">
              <div class="table-wrapper">
                <table class="table-monitoring">
                  <thead>
                    <tr>
                      <th>Gate</th>
                      <th>Ukuran Kualitas</th>
                      <th>Level</th>
                      <th>Aktivitas</th>
                      <th class="date-column">Tanggal Mulai</th>
                      <th class="date-column">Tanggal Selesai</th>
        `;
        
        // Tambahkan kolom status untuk setiap wilayah
        regions.forEach(region => {
          tableHtml += `<th class="status-column region-header">${region.name}</th>`;
        });
        
        tableHtml += `
                    </tr>
                  </thead>
                  <tbody>
        `;
        
        // Urutkan dan kelompokkan aktivitas berdasarkan gate dan UK
        // Implementasi merge cell yang benar menggunakan rowspan
        const orderedActivities = [];
        
        // 1. Kelompokkan berdasarkan gate dan UK
        const activityGroups = {};
        for (const key in activityData) {
          // Skip metadata fields
          if (key.startsWith('_')) continue;
          
          const data = activityData[key];
          if (!data || !data.gate || !data.uk) continue;
          
          const gateUkKey = `${data.gate}|${data.uk}`;
          
          if (!activityGroups[gateUkKey]) {
            activityGroups[gateUkKey] = [];
          }
          
          activityGroups[gateUkKey].push(data);
        }
        
        // 2. Urutkan aktivitas dalam setiap group berdasarkan proses, bukan abjad
        const activityOrder = {
          "Pengisian nama pelaksana aksi preventif": 1,
          "Upload bukti pelaksanaan aksi preventif": 2,
          "Penilaian ukuran kualitas": 3,
          "Approval Gate oleh Sign-off": 4,
          "Pengisian pelaksana aksi korektif": 5,
          "Upload bukti pelaksanaan aksi korektif": 6
        };
        
        // 3. Urutkan gate dan UK berdasarkan nomor
        const sortedGroupKeys = Object.keys(activityGroups).sort((a, b) => {
          const [gateA, ukA] = a.split('|');
          const [gateB, ukB] = b.split('|');
          
          // Ensure valid format before extracting numbers
          const gateAMatch = gateA.match(/GATE(\d+)/);
          const gateBMatch = gateB.match(/GATE(\d+)/);
          
          if (!gateAMatch || !gateBMatch) {
            return a.localeCompare(b); // Default to string comparison if format is unexpected
          }
          
          // Ekstrak nomor gate
          const gateNumA = parseInt(gateAMatch[1]);
          const gateNumB = parseInt(gateBMatch[1]);
          
          if (gateNumA !== gateNumB) {
            return gateNumA - gateNumB;
          }
          
          // Ekstrak nomor UK
          const ukAMatch = ukA.match(/UK(\d+)/);
          const ukBMatch = ukB.match(/UK(\d+)/);
          
          if (!ukAMatch || !ukBMatch) {
            return a.localeCompare(b); // Default to string comparison if format is unexpected
          }
          
          const ukNumA = parseInt(ukAMatch[1]);
          const ukNumB = parseInt(ukBMatch[1]);
          
          return ukNumA - ukNumB;
        });
        
        // Untuk menyimpan level UK
        const ukLevels = {};
        
        // 4. Proses setiap kelompok dan buat baris tabel
        for (let groupIndex = 0; groupIndex < sortedGroupKeys.length; groupIndex++) {
          const groupKey = sortedGroupKeys[groupIndex];
          const activities = activityGroups[groupKey];
          const groupClass = groupIndex % 2 === 0 ? 'uk-group-even' : 'uk-group-odd';
          
          // Dapatkan level UK dari aktivitas pertama
          const ukKey = activities[0]?.uk;
          
          // Urutkan aktivitas berdasarkan urutan proses
          activities.sort((a, b) => {
            return (activityOrder[a.activity] || 999) - (activityOrder[b.activity] || 999);
          });
          
          // Buat baris untuk setiap kelompok
          for (let i = 0; i < activities.length; i++) {
            const data = activities[i];
            const isFirstRow = i === 0;
            const rowspanValue = activities.length;
            
            // Ambil nomor aktivitas (1-6) berdasarkan activityOrder
            const activityNumber = activityOrder[data.activity] || '';
            
            // Simpan UK Level jika belum ada
            if (!ukLevels[data.uk]) {
              // Ekstrak measurement_id dari salah satu status key untuk mendapatkan level
              const someActivity = Object.values(activityGroups).find(acts => 
                acts.find(act => act.uk === data.uk)
              )[0];
              const ukLevel = someActivity.ukLevel || "Tidak diketahui";
              ukLevels[data.uk] = ukLevel;
            }
            
            tableHtml += `<tr class="${groupClass}">`;
            
            // Untuk baris pertama saja, tampilkan gate dan UK dengan rowspan
            if (isFirstRow) {
              tableHtml += `
                <td rowspan="${rowspanValue}">${data.gate}</td>
                <td rowspan="${rowspanValue}">${data.uk}</td>
                <td rowspan="${rowspanValue}" style="text-align: center;">${ukLevels[data.uk]}</td>
              `;
            }
            
            // Tentukan apakah tanggal dalam rentang aktif
            const startDate = data.start;
            const endDate = data.end;
            const isInDateRange = isDateInRange(startDate, endDate);
            
            // Tambahkan class untuk tanggal yang dalam rentang
            const startDateClass = isInDateRange ? 'date-in-range' : '';
            const endDateClass = isInDateRange ? 'date-in-range' : '';
            
            tableHtml += `
              <td><span class="activity-number">${activityNumber}</span>${data.activity}</td>
              <td class="date-column ${startDateClass}">${formatDate(startDate)}</td>
              <td class="date-column ${endDateClass}">${formatDate(endDate)}</td>
            `;
            
            // Tambahkan status untuk setiap wilayah
            regions.forEach(region => {
              const status = data.statuses?.[region.id] || "Tidak tersedia";
              tableHtml += `<td class="status-column">${getStatusBadge(status)}</td>`;
            });
            
            tableHtml += `</tr>`;
          }
        }
        
        tableHtml += `
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `;
        
        $resultsContainer.html(tableHtml);
      };

      // --- Load Data Functions (Projects & Regions) ---
      const loadProjects = async () => {
        $projectSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "get_available_projects",
              year: year
            },
            dataType: "json"
          });
          
          console.log("Project data response:", response);
          
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          
          if (response.status && response.data && response.data.length > 0) {
            // Display the actual data for debugging
            console.log("Projects to display:", JSON.stringify(response.data));
            
            // Check if we have real project names or just IDs with prefixes
            const needsFallback = response.data.some(p => 
              p.name === "Kegiatan " + p.id || 
              p.name === "Project ID: " + p.id || 
              p.name === "Project " + p.id
            );
            
            console.log("Needs fallback for names:", needsFallback);
            
            // If we need a fallback, get names one by one
            if (needsFallback) {
              console.log("Using fallback to get real project names");
              const projects = [];
              
              for (const project of response.data) {
                try {
                  // Get the actual project name directly
                  const nameResponse = await $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: {
                      db_action: "get_project_name",
                      year: year,
                      project_id: project.id
                    },
                    dataType: "json"
                  });
                  
                  if (nameResponse.status) {
                    console.log(`Got name for project ${project.id}:`, nameResponse.project_name);
                    projects.push({
                      id: project.id,
                      name: nameResponse.project_name
                    });
                  } else {
                    console.log(`Failed to get name for project ${project.id}, using default`);
                    projects.push(project);
                  }
                } catch (error) {
                  console.error(`Error getting name for project ${project.id}:`, error);
                  projects.push(project);
                }
              }
              
              // Sort and populate the dropdown
              projects.sort((a, b) => a.name.localeCompare(b.name));
              
              projects.forEach(project => {
                $projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
              });
            } else {
              // Sort projects by name for better usability
              const sortedProjects = [...response.data].sort((a, b) => a.name.localeCompare(b.name));
              
              sortedProjects.forEach(project => {
                // Make sure the project name doesn't start with "Project ID" or similar
                let projectName = project.name;
                
                // If it's just a numeric ID or has "Project ID" prefix, improve the display
                if (/^Project ID: \d+$/.test(projectName) || /^Kegiatan \d+$/.test(projectName)) {
                  projectName = "Kegiatan " + project.id;
                }
                
                $projectSelect.append(`<option value="${project.id}">${projectName}</option>`);
              });
            }
            
            $projectSelect.prop('disabled', false);
          } else {
            $projectSelect.append('<option value="" disabled>Tidak ada kegiatan tersedia di database lokal</option>');
            $projectSelect.prop('disabled', true);
            
            // Show information that they need to download data first
            $resultsContainer.html(`
              <div class="card">
                <div class="card-body">
                  <div class="no-data-message">
                    <i class="fas fa-info-circle fa-3x mb-3 text-primary"></i>
                    <h4>Tidak ada data untuk tahun ${year}</h4>
                    <p>Silahkan download data terlebih dahulu dari halaman Download Data.</p>
                    <a href="download.php" class="btn btn-primary mt-3">
                      <i class="fas fa-download me-2"></i>Download Data
                    </a>
                  </div>
                </div>
              </div>
            `);
          }
        } catch (error) {
          showError("Gagal memuat daftar kegiatan dari database lokal");
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
        }
      };

      const loadRegions = async () => {
        $regionSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "get_available_regions",
              project_id: selectedProject
            },
            dataType: "json"
          });
          
          $regionSelect.empty();
          coverageData = [];
          
          if (!response.status || !response.data || response.data.length === 0) {
            throw new Error("Tidak ada data wilayah tersedia di database lokal");
          }
          
          coverageData = response.data;
          
          // Display regions in the select element
          coverageData.forEach(region => {
            $regionSelect.append(`<option value="${region.id}">${region.name}</option>`);
          });
          
          // Select 'pusat' as default if available
          if ($regionSelect.find('option[value="pusat"]').length > 0) {
            selectedRegion = "pusat";
            $regionSelect.val(selectedRegion);
          } else if ($regionSelect.find('option').length > 0) {
            selectedRegion = $regionSelect.find('option:first').val();
            $regionSelect.val(selectedRegion);
          }
          
          $regionSelect.prop('disabled', false);
          
        } catch (error) {
          showError(error.message || "Gagal memuat daftar wilayah");
          $regionSelect.empty().append('<option value="">Pilih Cakupan Wilayah</option>');
          $regionSelect.prop('disabled', true);
          coverageData = [];
        }
      };

      // --- Main function to load monitoring data ---
      const loadMonitoringData = async () => {
        if (!year || !selectedProject || !selectedRegion) {
          showError("Silakan pilih tahun, kegiatan, dan cakupan wilayah terlebih dahulu");
          return;
        }
        
        $spinner.fadeIn(200);
        $resultsContainer.empty();
        
        try {
          const regionData = coverageData.find(r => r.id === selectedRegion);
          if (!regionData) {
            throw new Error("Data wilayah tidak ditemukan");
          }
          
          // Get regions to display in the table
          let regionsToProcess = [];
          
          if (selectedRegion === "pusat") {
            // Only show the center
            regionsToProcess = [{ id: "pusat", prov: "00", kab: "00", name: "Pusat" }];
          } else {
            const prov = regionData.prov;
            
            // Show the province and all kabupaten in it
            regionsToProcess = [
              { id: `${prov}00`, prov: prov, kab: "00", name: regionData.name }
            ];
            
            // Add kabupaten if they exist in coverageData
            const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
            regionsToProcess = [...regionsToProcess, ...kabupatenList];
          }
          
          // Get processed data from database
          const processedDataResponse = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "get_processed_data",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion
            },
            dataType: "json"
          });
          
          if (processedDataResponse.found) {
            // Use the pre-processed data
            activityData = processedDataResponse.data;
            displayResultTable(regionsToProcess);
            
            // Show when the data was last updated
            const lastUpdate = new Date(activityData._meta?.lastUpdated || processedDataResponse.last_updated).toLocaleString('id-ID');
            $("#resultsContainer").prepend(
              `<div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>Data terakhir diperbarui: ${lastUpdate}
                <a href="download.php" class="btn btn-sm btn-outline-primary ms-3">
                  <i class="fas fa-download me-1"></i>Download Data Baru
                </a>
              </div>`
            );
          } else {
            // No processed data available
            $resultsContainer.html(`
              <div class="card">
                <div class="card-body">
                  <div class="no-data-message">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                    <h4>Data Monitoring Belum Tersedia</h4>
                    <p>Silahkan download data terlebih dahulu dari halaman Download Data.</p>
                    <a href="download.php" class="btn btn-primary mt-3">
                      <i class="fas fa-download me-2"></i>Download Data
                    </a>
                  </div>
                </div>
              </div>
            `);
          }
        } catch (error) {
          showError(error.message || "Terjadi kesalahan saat memuat data monitoring");
        } finally {
          $spinner.fadeOut(200);
        }
      };

      // --- Event Handlers ---
      $yearSelect.on('change', async function(){
        year = $(this).val();
        $projectSelect.prop('disabled', true).empty().append('<option value="">Pilih Kegiatan</option>');
        $regionSelect.prop('disabled', true).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        selectedProject = null;
        selectedRegion  = null;
        $resultsContainer.empty();
        
        if (year) {
          await loadProjects();
        }
      });

      $projectSelect.on('change', async function(){
        selectedProject = $(this).val();
        $regionSelect.prop('disabled', true).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        selectedRegion  = null;
        $resultsContainer.empty();
        
        if (selectedProject) {
          await loadRegions();
        }
      });

      $regionSelect.on('change', function(){
        selectedRegion = $(this).val();
        $resultsContainer.empty();
      });

      $("#loadData").on('click', loadMonitoringData);

      // Initialize
      $spinner.hide();
    });
  </script>
</body>
</html>
