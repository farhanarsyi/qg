<?php
require_once 'config.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Content-Type: application/json');
    echo json_encode(['status' => false, 'message' => 'Authentication required']);
    exit;
}

// Get user area
$userArea = getUserArea();
$userLevel = isset($_POST['userLevel']) ? $_POST['userLevel'] : $userArea['level'];
$userProv = isset($_POST['userProv']) ? $_POST['userProv'] : $userArea['prov'];
$userKab = isset($_POST['userKab']) ? $_POST['userKab'] : $userArea['kab'];

// Process API action
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    header('Content-Type: application/json');
    
    switch ($action) {
        case 'getProjects':
            // Get projects from database for dropdown
            $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
            $projects = [];
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
            
            try {
                $stmt = $conn->prepare($sql);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    $projects[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'year' => $row['year']
                    ];
                }
                
                $stmt->close();
                
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
            break;
            
        case 'getRegions':
            // Get regions/coverages from database for dropdown
            $projectId = isset($_POST['projectId']) ? $_POST['projectId'] : '';
            $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
            $regions = [];
            
            if (empty($projectId)) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Project ID is required',
                    'data' => []
                ]);
                break;
            }
            
            // Add pusat region for superadmin and pusat users
            if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
                $regions[] = [
                    'id' => 'pusat',
                    'prov' => '00',
                    'kab' => '00',
                    'name' => 'Pusat - Nasional'
                ];
            }
            
            try {
                $sql = "";
                $params = [];
                $types = "";
                
                // Prepare query based on user level
                if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
                    // Get all regions
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
                        if ($row['prov'] === '00' && $row['kab'] === '00') {
                            continue;
                        }
                        
                        $regions[] = [
                            'id' => $row['prov'] . $row['kab'],
                            'prov' => $row['prov'],
                            'kab' => $row['kab'],
                            'name' => $row['name']
                        ];
                    }
                    $stmt->close();
                }
                
                echo json_encode([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $regions
                ]);
            } catch (Exception $e) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'data' => []
                ]);
            }
            break;
            
        default:
            echo json_encode([
                'status' => false,
                'message' => 'Invalid action',
                'data' => []
            ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => false, 
        'message' => 'No action specified'
    ]);
}
?> 