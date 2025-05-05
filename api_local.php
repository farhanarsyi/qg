<?php
require_once 'config.php';

// Set header to JSON
header('Content-Type: application/json');

// Check if user is authenticated
if (!isAuthenticated()) {
    echo json_encode([
        'status' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get user area information
$userArea = getUserArea();
$userLevel = $userArea['level'];
$userProv = $userArea['prov'];
$userKab = $userArea['kab'];

// Handle different API actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case "fetchProjects":
            fetchProjects();
            break;
            
        case "fetchCoverages":
            fetchCoverages();
            break;
            
        case "fetchActivities":
            fetchActivities();
            break;
            
        case "fetchMonitoringData":
            fetchMonitoringData();
            break;
            
        case "getSyncInfo":
            getSyncInfo();
            break;
            
        default:
            echo json_encode([
                'status' => false,
                'message' => 'Invalid action'
            ]);
            break;
    }
} else {
    echo json_encode([
        'status' => false,
        'message' => 'No action specified'
    ]);
}

// Function to fetch projects from the local database
function fetchProjects() {
    global $conn, $userLevel, $userProv, $userKab;
    
    try {
        $year = $_POST['year'] ?? date('Y');
        
        $sql = "";
        $params = [];
        $types = "";
        
        // Prepare query based on user level
        if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
            // Pusat and superadmin can see all projects
            $sql = "SELECT * FROM projects WHERE year = ?";
            $types = "s";
            $params = [$year];
        } else if ($userLevel === 'provinsi') {
            // Province can see projects with coverage in that province
            $sql = "SELECT DISTINCT p.* FROM projects p 
                  JOIN coverages c ON p.id = c.project_id AND p.year = c.year 
                  WHERE p.year = ? AND c.prov = ?";
            $types = "ss";
            $params = [$year, $userProv];
        } else if ($userLevel === 'kabkot') {
            // Kabkot can see projects with coverage in that kabkot
            $sql = "SELECT DISTINCT p.* FROM projects p 
                  JOIN coverages c ON p.id = c.project_id AND p.year = c.year 
                  WHERE p.year = ? AND c.prov = ? AND (c.kab = ? OR c.kab = '00')";
            $types = "sss";
            $params = [$year, $userProv, $userKab];
        }
        
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $projects = [];
        while ($row = $result->fetch_assoc()) {
            $projects[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'year' => $row['year']
            ];
        }
        
        echo json_encode([
            'status' => true,
            'message' => 'Success',
            'data' => $projects
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

// Function to fetch coverages/regions from the local database
function fetchCoverages() {
    global $conn, $userLevel, $userProv, $userKab;
    
    try {
        $projectId = $_POST['id_project'];
        $year = $_POST['year'] ?? date('Y');
        
        $sql = "";
        $params = [];
        $types = "";
        
        // Add pusat region for superadmin and pusat users
        $coverages = [];
        if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
            $coverages[] = [
                'prov' => '00',
                'kab' => '00',
                'name' => 'Pusat - Nasional'
            ];
        }
        
        // Prepare query based on user level
        if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
            // Get all coverages
            $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ?";
            $types = "ss";
            $params = [$projectId, $year];
        } else if ($userLevel === 'provinsi') {
            // Province can see coverages in that province
            $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ? AND prov = ?";
            $types = "sss";
            $params = [$projectId, $year, $userProv];
        } else if ($userLevel === 'kabkot') {
            // Kabkot can see coverages in that kabkot
            $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ? AND prov = ? AND (kab = ? OR kab = '00')";
            $types = "ssss";
            $params = [$projectId, $year, $userProv, $userKab];
        }
        
        if (!empty($sql)) {
            $stmt = $conn->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                // Skip pusat if already added
                if ($row['prov'] === '00' && $row['kab'] === '00' && !empty($coverages)) {
                    continue;
                }
                
                $coverages[] = [
                    'prov' => $row['prov'],
                    'kab' => $row['kab'],
                    'name' => $row['name']
                ];
            }
        }
        
        echo json_encode([
            'status' => true,
            'message' => 'Success',
            'data' => $coverages
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

// Function to fetch activities for a project's coverage area
function fetchActivities() {
    global $conn, $userLevel, $userProv, $userKab;
    
    try {
        $projectId = $_POST['id_project'];
        $year = $_POST['year'] ?? date('Y');
        $prov = $_POST['prov'] ?? '00';
        $kab = $_POST['kab'] ?? '00';
        
        // Check if user has access to this region
        if (!checkUserRegionAccess($userLevel, $userProv, $userKab, $prov, $kab)) {
            echo json_encode([
                'status' => false,
                'message' => 'You do not have access to this region',
                'data' => []
            ]);
            return;
        }
        
        // Get activities for this project and region
        $sql = "SELECT DISTINCT activity_id, activity_name 
                FROM monitoring_data 
                WHERE project_id = ? AND year = ? AND prov = ? AND kab = ?
                ORDER BY activity_name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $projectId, $year, $prov, $kab);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'id' => $row['activity_id'],
                'name' => $row['activity_name']
            ];
        }
        
        echo json_encode([
            'status' => true,
            'message' => 'Success',
            'data' => $activities
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

// Function to fetch monitoring data from the local database
function fetchMonitoringData() {
    global $conn, $userLevel, $userProv, $userKab;
    
    try {
        $projectId = $_POST['id_project'];
        $year = $_POST['year'] ?? date('Y');
        $prov = $_POST['prov'] ?? '00';
        $kab = $_POST['kab'] ?? '00';
        
        // Check if user has access to this region
        if (!checkUserRegionAccess($userLevel, $userProv, $userKab, $prov, $kab)) {
            echo json_encode([
                'status' => false,
                'message' => 'You do not have access to this region',
                'data' => []
            ]);
            return;
        }
        
        // Get monitoring data from the view
        $sql = "SELECT * FROM v_monitoring 
                WHERE project_id = ? AND year = ? AND prov = ? AND kab = ?
                ORDER BY activity_name";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $projectId, $year, $prov, $kab);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $monitoringData = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates
            $startDate = !empty($row['start_date']) ? date('d-m-Y', strtotime($row['start_date'])) : '';
            $endDate = !empty($row['end_date']) ? date('d-m-Y', strtotime($row['end_date'])) : '';
            $planDate = !empty($row['plan_date']) ? date('d-m-Y', strtotime($row['plan_date'])) : '';
            $realizationDate = !empty($row['realization_date']) ? date('d-m-Y', strtotime($row['realization_date'])) : '';
            
            $monitoringData[] = [
                'id' => $row['id'],
                'project_name' => $row['project_name'],
                'region_name' => $row['region_name'],
                'activity_id' => $row['activity_id'],
                'activity_name' => $row['activity_name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'plan_date' => $planDate,
                'realization_date' => $realizationDate,
                'progress' => $row['progress'],
                'status' => $row['status'],
                'notes' => $row['notes']
            ];
        }
        
        echo json_encode([
            'status' => true,
            'message' => 'Success',
            'data' => $monitoringData
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'data' => []
        ]);
    }
}

// Function to get information about the last sync
function getSyncInfo() {
    global $conn;
    
    try {
        // Get the last successful sync
        $sql = "SELECT s.*, u.name as sync_by_name, s.sync_end as sync_date
                FROM sync_status s
                JOIN users u ON s.sync_by = u.id
                WHERE s.is_complete = TRUE
                ORDER BY s.sync_end DESC
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => true,
                'has_sync' => false,
                'message' => 'No synchronization data found'
            ]);
            return;
        }
        
        $syncInfo = $result->fetch_assoc();
        
        echo json_encode([
            'status' => true,
            'has_sync' => true,
            'sync_info' => [
                'date' => date('d-m-Y H:i:s', strtotime($syncInfo['sync_date'])),
                'by' => $syncInfo['sync_by_name'],
                'projects' => $syncInfo['projects_synced'],
                'regions' => $syncInfo['regions_synced'],
                'activities' => $syncInfo['activities_synced']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// Helper function to check if a user has access to a region
function checkUserRegionAccess($userLevel, $userProv, $userKab, $requestedProv, $requestedKab) {
    // Superadmin and pusat can access all regions
    if ($userLevel === 'superadmin' || $userLevel === 'pusat') {
        return true;
    }
    
    // Provinsi can access only its province and kabkot within
    if ($userLevel === 'provinsi') {
        return $requestedProv === $userProv;
    }
    
    // Kabkot can access only its kabkot
    if ($userLevel === 'kabkot') {
        return ($requestedProv === $userProv && 
                ($requestedKab === $userKab || $requestedKab === '00'));
    }
    
    return false;
}
?> 