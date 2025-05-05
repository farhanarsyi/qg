<?php
require_once 'config.php';

if(isset($_POST['action'])){
    $action = $_POST['action'];
    
    // Optional user filtering (for auth restricted views)
    $filterByUser = isset($_POST['filterByUser']) && $_POST['filterByUser'] === 'true';
    $userLevel = isset($_POST['userLevel']) ? $_POST['userLevel'] : null;
    $userProv = isset($_POST['userProv']) ? $_POST['userProv'] : '00';
    $userKab = isset($_POST['userKab']) ? $_POST['userKab'] : '00';
    
    switch($action){
        case "fetchProjects":
            $year = $_POST['year'];
            
            try {
                // Get projects from local DB first
                $localProjects = [];
                $localProjectsData = [];
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
                
                while ($row = $result->fetch_assoc()) {
                    $localProjects[$row['id']] = true;
                    $localProjectsData[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'year' => $row['year']
                    ];
                }
                
                $stmt->close();
                
                // If we have local projects, use them directly
                if (!empty($localProjectsData)) {
                    // Return data directly from local database
                    echo json_encode([
                        'status' => true,
                        'message' => 'Success',
                        'data' => $localProjectsData
                    ]);
                    break;
                }
                
                // If no local projects, try to get from API
                $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
                $postData = http_build_query([
                    "year" => $year
                ]);
                $apiResponse = callApi($url, $postData);
                $jsonData = json_decode(extractJson($apiResponse), true);
                
                // Check if API returned valid data
                if (!isset($jsonData['status']) || !$jsonData['status']) {
                    // API error, but we have local data, use it
                    if (!empty($localProjectsData)) {
                        echo json_encode([
                            'status' => true,
                            'message' => 'Using local data (API unavailable)',
                            'data' => $localProjectsData
                        ]);
                    } else {
                        // No API data and no local data
                        echo json_encode([
                            'status' => false,
                            'message' => 'No project data available',
                            'data' => []
                        ]);
                    }
                    break;
                }
                
                // If we don't have any projects in local DB yet, return all from API
                if (empty($localProjects)) {
                    echo $apiResponse;
                    break;
                }
                
                // Filter API results based on local DB data
                $filteredData = [];
                foreach ($jsonData['data'] as $project) {
                    if (isset($localProjects[$project['id']])) {
                        $filteredData[] = $project;
                    }
                }
                
                // If we don't have matches between API and local DB, just return local data
                if (empty($filteredData) && !empty($localProjectsData)) {
                    echo json_encode([
                        'status' => true,
                        'message' => 'Using local data only',
                        'data' => $localProjectsData
                    ]);
                } else {
                    // Return filtered data
                    $jsonData['data'] = $filteredData;
                    echo json_encode($jsonData);
                }
            } catch (Exception $e) {
                // On any error, return error message
                echo json_encode([
                    'status' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'data' => []
                ]);
            }
            break;
            
        case "fetchCoverages":
            $id_project = $_POST['id_project'];
            
            try {
                // First try to get coverages from local database
                $localCoverages = [];
                $sql = "";
                $params = [];
                $types = "";
                
                // Prepare query based on user level
                if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
                    // Pusat and superadmin can see all coverages
                    $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ?";
                    $types = "ss";
                    $params = [$id_project, $_POST['year'] ?? date('Y')];
                } else if ($userLevel === 'provinsi') {
                    // Province can see coverages in that province
                    $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ? AND prov = ?";
                    $types = "sss";
                    $params = [$id_project, $_POST['year'] ?? date('Y'), $userProv];
                } else if ($userLevel === 'kabkot') {
                    // Kabkot can see coverages in that kabkot
                    $sql = "SELECT * FROM coverages WHERE project_id = ? AND year = ? AND prov = ? AND (kab = ? OR kab = '00')";
                    $types = "ssss";
                    $params = [$id_project, $_POST['year'] ?? date('Y'), $userProv, $userKab];
                }
                
                if (!empty($sql)) {
                    $stmt = $conn->prepare($sql);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    while ($row = $result->fetch_assoc()) {
                        $localCoverages[] = [
                            'prov' => $row['prov'],
                            'kab' => $row['kab'],
                            'name' => $row['name']
                        ];
                    }
                    
                    $stmt->close();
                }
                
                // If we have local coverages, use them directly
                if (!empty($localCoverages)) {
                    echo json_encode([
                        'status' => true,
                        'message' => 'Success',
                        'data' => $localCoverages
                    ]);
                    break;
                }
                
                // Get coverages from API as fallback
                $url = "https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll";
                $postData = http_build_query([
                    "id_project" => $id_project
                ]);
                $apiResponse = callApi($url, $postData);
                $jsonData = json_decode(extractJson($apiResponse), true);
                
                // If API fails but we have local data, use local data
                if (!isset($jsonData['status']) || !$jsonData['status']) {
                    if (!empty($localCoverages)) {
                        echo json_encode([
                            'status' => true,
                            'message' => 'Using local data (API unavailable)',
                            'data' => $localCoverages
                        ]);
                    } else {
                        echo json_encode([
                            'status' => false,
                            'message' => 'No coverage data available',
                            'data' => []
                        ]);
                    }
                    break;
                }
                
                // If filtering by user, filter the results
                if ($filterByUser && !empty($jsonData['data'])) {
                    // Filter based on user level
                    if ($userLevel === 'provinsi') {
                        // Province can only see data for province and kabkot within
                        $filteredData = array_filter($jsonData['data'], function($item) use ($userProv) {
                            return $item['prov'] === $userProv;
                        });
                        $jsonData['data'] = array_values($filteredData);
                        
                    } else if ($userLevel === 'kabkot') {
                        // Kabkot can only see data for their kabkot and their province
                        $filteredData = array_filter($jsonData['data'], function($item) use ($userProv, $userKab) {
                            return ($item['prov'] === $userProv && ($item['kab'] === $userKab || $item['kab'] === '00'));
                        });
                        $jsonData['data'] = array_values($filteredData);
                    }
                }
                
                echo json_encode($jsonData);
            } catch (Exception $e) {
                // On any error, return error message
                echo json_encode([
                    'status' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                    'data' => []
                ]);
            }
            break;
            
        case "fetchGates":
            $id_project = $_POST['id_project'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/gates/fetchAll";
            $postData = http_build_query([
                "id_project" => $id_project
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchMeasurements":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAll";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate"    => $id_gate,
                "prov"       => $prov,
                "kab"        => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchPreventivesByKab":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Preventives/fetchByKab";
            $postData = http_build_query([
                "data[year]" => $year,
                "data[id_project]" => $id_project,
                "data[id_gate]" => $id_gate,
                "data[id_measurement]" => $id_measurement,
                "data[prov]" => $prov,
                "data[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchPreventivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab  = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Preventives/fetchByMeasurement";
            $postData = http_build_query([
                "year" => $year,
                "param[year]" => $year,
                "param[id_project]" => $id_project,
                "param[id_gate]" => $id_gate,
                "param[id_measurement]" => $id_measurement,
                "param[prov]" => $prov,
                "param[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchAssessments":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Measurements/fetchAllAssessments";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab
            ]);
            
            echo callApi($url, $postData);
            break;
        case "fetchNeedCorrectives":
            $year = isset($_POST['year']) ? $_POST['year'] : "2025";
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/assessments/fetchNeedCorrectives";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab,
                "year" => $year
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchProjectSpesific":
            $year = $_POST['year'];
            $project_id = $_POST['project_id'];
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchSpesific";
            $postData = http_build_query([
                "year" => $year,
                "project_id" => $project_id
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchCorrectivesByKab":
            $year = $_POST['data']['year'];
            $id_project = $_POST['data']['id_project'];
            $id_gate = $_POST['data']['id_gate'];
            $id_measurement = $_POST['data']['id_measurement'];
            $prov = isset($_POST['data']['prov']) ? $_POST['data']['prov'] : "00";
            $kab = isset($_POST['data']['kab']) ? $_POST['data']['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Correctives/fetchByKab";
            $postData = http_build_query([
                "data[year]" => $year,
                "data[id_project]" => $id_project,
                "data[id_gate]" => $id_gate,
                "data[id_measurement]" => $id_measurement,
                "data[prov]" => $prov,
                "data[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchCorrectivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/Correctives/fetchByMeasurement";
            $postData = http_build_query([
                "year" => $year,
                "param[year]" => $year,
                "param[id_project]" => $id_project,
                "param[id_gate]" => $id_gate,
                "param[id_measurement]" => $id_measurement,
                "param[prov]" => $prov,
                "param[kab]" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        case "fetchAllActions":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            $url = "https://webapps.bps.go.id/nqaf/qgate/api/measurements/fetchAllActions";
            $postData = http_build_query([
                "id_project" => $id_project,
                "id_gate" => $id_gate,
                "prov" => $prov,
                "kab" => $kab
            ]);
            echo callApi($url, $postData);
            break;
        default:
            echo json_encode(["status" => false, "message" => "Invalid action"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "No action specified"]);
}

function callApi($url, $postData){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With: XMLHttpRequest',
        'Cookie: ci_session=5503368b0cb86766c2be9ed5d93c038762698865'
    ]);
    $response = curl_exec($ch);
    if(curl_errno($ch)){
        $error_msg = curl_error($ch);
        curl_close($ch);
        return json_encode(["status" => false, "message" => $error_msg]);
    }
    curl_close($ch);
    return $response;
}
?>
