<?php
// SQL Server connection setup
$serverName = "DCSQLSRV03.bps.go.id"; // or IP "10.0.77.23"
$connectionOptions = [
    "Database" => "QG_PROD",
    "Uid" => "qgreadonly",
    "PWD" => "A!rNeoHa55"
];

// Function to establish database connection
function getConnection() {
    global $serverName, $connectionOptions;
    try {
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        if ($conn === false) {
            return null;
        }
        return $conn;
    } catch (Exception $e) {
        return null;
    }
}

// Function to execute query and return JSON response
function executeQuery($query, $params = []) {
    $conn = getConnection();
    if ($conn === null) {
        $errors = sqlsrv_errors();
        $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Connection failed";
        return json_encode(["status" => false, "message" => $errorMsg]);
    }
    
    $stmt = sqlsrv_query($conn, $query, $params);
    if ($stmt === false) {
        $errors = sqlsrv_errors();
        $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
        sqlsrv_close($conn);
        return json_encode(["status" => false, "message" => $errorMsg]);
    }
    
    $result = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Convert DateTime objects to string format
        foreach ($row as $key => $value) {
            if ($value instanceof DateTime) {
                $row[$key] = $value->format('Y-m-d H:i:s');
            }
        }
        $result[] = $row;
    }
    
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    return json_encode(["status" => true, "data" => $result]);
}

// Function to format assessment data to match the old API format
function formatAssessmentResponse($data) {
    if (empty($data)) {
        return ["status" => true, "data" => []];
    }
    
    $assessment = $data[0];
    
    // Convert state to status field to match old API
    // The old API seems to use a "status" field while database uses "state"
    if (isset($assessment['state'])) {
        $assessment['status'] = $assessment['state'];
    }
    
    // Additional processing if needed
    // For example, ensure assessment data is properly formatted as JSON
    if (isset($assessment['assessment']) && !is_array($assessment['assessment'])) {
        $assessment['assessment'] = json_decode($assessment['assessment'], true);
    }
    
    // Format notes field as needed
    if (isset($assessment['notes']) && !is_array($assessment['notes'])) {
        $assessment['notes'] = json_decode($assessment['notes'], true);
    }
    
    return ["status" => true, "data" => $assessment];
}

if(isset($_POST['action'])){
    $action = $_POST['action'];
    
    switch($action){
        case "fetchProjects":
            $year = $_POST['year'];
            $query = "SELECT * FROM [projects] WHERE [year] = ? AND [is_deleted] IS NULL ORDER BY [id]";
            echo executeQuery($query, [$year]);
            break;
            
        case "fetchGates":
            $id_project = $_POST['id_project'];
            $query = "SELECT * FROM [project_gates] WHERE [id_project] = ? AND [is_deleted] IS NULL ORDER BY [gate_number]";
            echo executeQuery($query, [$id_project]);
            break;
            
        case "fetchMeasurements":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            $query = "SELECT * FROM [project_measurements] 
                      WHERE [id_project] = ? AND [id_gate] = ? 
                      AND [is_deleted] IS NULL 
                      ORDER BY [measurement_number]";
            
            echo executeQuery($query, [$id_project, $id_gate]);
            break;
            
        case "fetchPreventivesByKab":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            $query = "SELECT * FROM [project_preventives] 
                      WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
                      AND [id_measurement] = ? AND [prov] = ? AND [kab] = ? 
                      AND [is_deleted] IS NULL 
                      ORDER BY [index_action]";
            
            echo executeQuery($query, [$year, $id_project, $id_gate, $id_measurement, $prov, $kab]);
            break;
            
        case "fetchPreventivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            $query = "SELECT * FROM [project_preventives] 
                      WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
                      AND [id_measurement] = ? 
                      AND [is_deleted] IS NULL 
                      ORDER BY [prov], [kab], [index_action]";
            
            echo executeQuery($query, [$year, $id_project, $id_gate, $id_measurement]);
            break;
            
        case "fetchAssessments":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            $query = "SELECT * FROM [project_assessments] 
                      WHERE [id_project] = ? AND [id_gate] = ? 
                      AND [prov] = ? AND [kab] = ?";
            
            $conn = getConnection();
            if ($conn === null) {
                echo json_encode(["status" => false, "message" => "Connection failed"]);
                break;
            }
            
            $stmt = sqlsrv_query($conn, $query, [$id_project, $id_gate, $prov, $kab]);
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
                sqlsrv_close($conn);
                echo json_encode(["status" => false, "message" => $errorMsg]);
                break;
            }
            
            $result = [];
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Convert DateTime objects to string format
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $row[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
                
                // Add status field to match old API format
                if (isset($row['state'])) {
                    $row['status'] = $row['state'];
                }
                
                // Ensure assessment JSON is properly decoded
                if (isset($row['assessment'])) {
                    $row['assessment'] = json_decode($row['assessment'], true);
                }
                
                // Format notes field if present
                if (isset($row['notes']) && $row['notes'] !== null) {
                    $row['notes'] = json_decode($row['notes'], true);
                }
                
                // Put the row in an array to match the original API format
                $result[] = $row;
            }
            
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            
            echo json_encode(["status" => true, "data" => $result]);
            break;
            
        case "fetchNeedCorrectives":
            $year = isset($_POST['year']) ? $_POST['year'] : "2025";
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            // First get assessments to check which ones need correctives
            $assessQuery = "SELECT * FROM [project_assessments] 
                           WHERE [id_project] = ? AND [id_gate] = ? 
                           AND [prov] = ? AND [kab] = ? AND [year] = ?";
            
            $conn = getConnection();
            if ($conn === null) {
                echo json_encode(["status" => false, "message" => "Connection failed"]);
                break;
            }
            
            $assessStmt = sqlsrv_query($conn, $assessQuery, [$id_project, $id_gate, $prov, $kab, $year]);
            if ($assessStmt === false) {
                $errors = sqlsrv_errors();
                $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
                sqlsrv_close($conn);
                echo json_encode(["status" => false, "message" => $errorMsg]);
                break;
            }
            
            $assessment = null;
            if ($row = sqlsrv_fetch_array($assessStmt, SQLSRV_FETCH_ASSOC)) {
                $assessment = $row;
            }
            sqlsrv_free_stmt($assessStmt);
            
            if ($assessment === null) {
                sqlsrv_close($conn);
                echo json_encode(["status" => true, "data" => []]);
                break;
            }
            
            // Parse assessment JSON to determine which measurements need correctives
            $assessmentData = json_decode($assessment['assessment'], true);
            if (!is_array($assessmentData)) {
                sqlsrv_close($conn);
                echo json_encode(["status" => true, "data" => []]);
                break;
            }
            
            $needCorrectiveMeasurements = [];
            foreach ($assessmentData as $item) {
                if (isset($item['idm']) && isset($item['ass']) && ($item['ass'] == "1" || $item['ass'] == "2")) {
                    $needCorrectiveMeasurements[] = $item['idm'];
                }
            }
            
            if (empty($needCorrectiveMeasurements)) {
                sqlsrv_close($conn);
                echo json_encode(["status" => true, "data" => []]);
                break;
            }
            
            // Fetch measurements that need correctives
            $placeholders = implode(',', array_fill(0, count($needCorrectiveMeasurements), '?'));
            $measureQuery = "SELECT * FROM [project_measurements] 
                            WHERE [id_project] = ? AND [id_gate] = ? 
                            AND [id] IN ($placeholders) AND [is_deleted] IS NULL";
            
            $params = array_merge([$id_project, $id_gate], $needCorrectiveMeasurements);
            $measureStmt = sqlsrv_query($conn, $measureQuery, $params);
            
            if ($measureStmt === false) {
                $errors = sqlsrv_errors();
                $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
                sqlsrv_close($conn);
                echo json_encode(["status" => false, "message" => $errorMsg]);
                break;
            }
            
            $result = [];
            while ($row = sqlsrv_fetch_array($measureStmt, SQLSRV_FETCH_ASSOC)) {
                // Convert DateTime objects to string format
                foreach ($row as $key => $value) {
                    if ($value instanceof DateTime) {
                        $row[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
                $result[] = $row;
            }
            
            sqlsrv_free_stmt($measureStmt);
            sqlsrv_close($conn);
            
            echo json_encode(["status" => true, "data" => $result]);
            break;
            
        case "fetchProjectSpesific":
            $year = $_POST['year'];
            $project_id = $_POST['project_id'];
            
            $query = "SELECT * FROM [projects] WHERE [year] = ? AND [id] = ? AND [is_deleted] IS NULL";
            
            echo executeQuery($query, [$year, $project_id]);
            break;
            
        case "fetchCoverages":
            $id_project = $_POST['id_project'];
            
            $query = "SELECT * FROM [project_coverages] WHERE [id_project] = ? ORDER BY [prov], [kab]";
            
            echo executeQuery($query, [$id_project]);
            break;
            
        case "fetchCorrectivesByKab":
            $year = $_POST['data']['year'];
            $id_project = $_POST['data']['id_project'];
            $id_gate = $_POST['data']['id_gate'];
            $id_measurement = $_POST['data']['id_measurement'];
            $prov = isset($_POST['data']['prov']) ? $_POST['data']['prov'] : "00";
            $kab = isset($_POST['data']['kab']) ? $_POST['data']['kab'] : "00";
            
            $query = "SELECT * FROM [project_correctives] 
                      WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
                      AND [id_measurement] = ? AND [prov] = ? AND [kab] = ? 
                      AND [is_deleted] IS NULL 
                      ORDER BY [index_action]";
            
            echo executeQuery($query, [$year, $id_project, $id_gate, $id_measurement, $prov, $kab]);
            break;
            
        case "fetchCorrectivesByMeasurement":
            $year = $_POST['year'];
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $id_measurement = $_POST['id_measurement'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            $query = "SELECT * FROM [project_correctives] 
                      WHERE [year] = ? AND [id_project] = ? AND [id_gate] = ? 
                      AND [id_measurement] = ? 
                      AND [is_deleted] IS NULL 
                      ORDER BY [prov], [kab], [index_action]";
            
            echo executeQuery($query, [$year, $id_project, $id_gate, $id_measurement]);
            break;
            
        case "fetchAllActions":
            $id_project = $_POST['id_project'];
            $id_gate = $_POST['id_gate'];
            $prov = isset($_POST['prov']) ? $_POST['prov'] : "00";
            $kab = isset($_POST['kab']) ? $_POST['kab'] : "00";
            
            // Get assessment data first
            $assessQuery = "SELECT * FROM [project_assessments] 
                           WHERE [id_project] = ? AND [id_gate] = ? 
                           AND [prov] = ? AND [kab] = ?";
            
            $conn = getConnection();
            if ($conn === null) {
                echo json_encode(["status" => false, "message" => "Connection failed"]);
                break;
            }
            
            $assessStmt = sqlsrv_query($conn, $assessQuery, [$id_project, $id_gate, $prov, $kab]);
            if ($assessStmt === false) {
                $errors = sqlsrv_errors();
                $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
                sqlsrv_close($conn);
                echo json_encode(["status" => false, "message" => $errorMsg]);
                break;
            }
            
            $assessment = null;
            if ($row = sqlsrv_fetch_array($assessStmt, SQLSRV_FETCH_ASSOC)) {
                $assessment = $row;
                
                // Add status field to match old API format
                if (isset($assessment['state'])) {
                    $assessment['status'] = $assessment['state'];
                }
            }
            sqlsrv_free_stmt($assessStmt);
            
            if ($assessment === null) {
                sqlsrv_close($conn);
                echo json_encode(["status" => true, "data" => []]);
                break;
            }
            
            // Get year from assessment
            $year = $assessment['year'];
            
            // Get all measurements for this project and gate
            $measureQuery = "SELECT * FROM [project_measurements] 
                            WHERE [id_project] = ? AND [id_gate] = ? 
                            AND [is_deleted] IS NULL";
            
            $measureStmt = sqlsrv_query($conn, $measureQuery, [$id_project, $id_gate]);
            if ($measureStmt === false) {
                $errors = sqlsrv_errors();
                $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
                sqlsrv_close($conn);
                echo json_encode(["status" => false, "message" => $errorMsg]);
                break;
            }
            
            $measurements = [];
            while ($row = sqlsrv_fetch_array($measureStmt, SQLSRV_FETCH_ASSOC)) {
                $measurements[] = $row;
            }
            sqlsrv_free_stmt($measureStmt);
            
            if (empty($measurements)) {
                sqlsrv_close($conn);
                echo json_encode(["status" => true, "data" => []]);
                break;
            }
            
            // Parse assessment JSON to get the assessment value for each measurement
            $assessmentData = json_decode($assessment['assessment'], true);
            $assessmentMap = [];
            if (is_array($assessmentData)) {
                foreach ($assessmentData as $item) {
                    if (isset($item['idm']) && isset($item['ass'])) {
                        $assessmentMap[$item['idm']] = $item['ass'];
                    }
                }
            }
            
            $result = [];
            
            // For each measurement, get the appropriate actions based on assessment
            foreach ($measurements as $measurement) {
                $id_measurement = $measurement['id'];
                $assessValue = isset($assessmentMap[$id_measurement]) ? $assessmentMap[$id_measurement] : "";
                
                $actions = [];
                
                // Determine which actions to fetch based on assessment value
                if ($assessValue == "3") {
                    // Green assessment - get preventive actions
                    $actions = json_decode($measurement['green_preventif'], true);
                } else if ($assessValue == "1") {
                    // Red assessment - get corrective actions
                    $actions = json_decode($measurement['red_action'], true);
                } else if ($assessValue == "2") {
                    // Yellow assessment - get yellow actions
                    $actions = json_decode($measurement['yellow_action'], true);
                }
                
                if (!empty($actions)) {
                    $result[] = [
                        'measurement' => $measurement,
                        'assessment' => $assessValue,
                        'actions' => $actions
                    ];
                }
            }
            
            sqlsrv_close($conn);
            echo json_encode(["status" => true, "data" => $result]);
            break;
            
        default:
            echo json_encode(["status" => false, "message" => "Invalid action"]);
    }
} else {
    echo json_encode(["status" => false, "message" => "No action specified"]);
}
?>
