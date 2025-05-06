<?php
// db_functions.php - Shared database functions for monitoring and download pages

// Set execution time limit to 24 hours
set_time_limit(86400);
ini_set('max_execution_time', 86400);


// Function to save data to local db
function saveToLocalDB($conn, $year, $project_id, $region_id, $data_type, $cache_key, $data_json) {
    // If any required parameter is empty, return false
    if (empty($year) || empty($project_id) || empty($data_type) || empty($cache_key) || empty($data_json)) {
        return false;
    }
    
    $year = $conn->real_escape_string($year);
    $project_id = $conn->real_escape_string($project_id);
    $region_id = $conn->real_escape_string($region_id);
    $data_type = $conn->real_escape_string($data_type);
    $cache_key = $conn->real_escape_string($cache_key);
    $data_json = $conn->real_escape_string($data_json);
    
    $sql = "INSERT INTO qg_sync (year, project_id, region_id, data_type, cache_key, data_json) 
            VALUES ('$year', '$project_id', '$region_id', '$data_type', '$cache_key', '$data_json')
            ON DUPLICATE KEY UPDATE data_json = '$data_json', last_updated = CURRENT_TIMESTAMP";
    
    return $conn->query($sql);
}

// Function to get data from local db
function getFromLocalDB($conn, $year, $project_id, $region_id, $data_type, $cache_key) {
    // If any required parameter is empty, return not found
    if (empty($year) || empty($project_id) || empty($data_type) || empty($cache_key)) {
        return ['found' => false];
    }
    
    $year = $conn->real_escape_string($year);
    $project_id = $conn->real_escape_string($project_id);
    $region_id = $conn->real_escape_string($region_id);
    $data_type = $conn->real_escape_string($data_type);
    $cache_key = $conn->real_escape_string($cache_key);
    
    $sql = "SELECT data_json, last_updated FROM qg_sync 
            WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id' 
            AND data_type = '$data_type' AND cache_key = '$cache_key'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'found' => true,
            'data' => json_decode($row['data_json'], true),
            'last_updated' => $row['last_updated']
        ];
    }
    
    return ['found' => false];
}

// Function to check if we should use cached data
function shouldUseCache($conn, $year, $project_id, $region_id = 'all') {
    // If any required parameter is empty, return false
    if (empty($year) || empty($project_id)) {
        return false;
    }
    
    $year = $conn->real_escape_string($year);
    $project_id = $conn->real_escape_string($project_id);
    $region_id = $conn->real_escape_string($region_id);
    
    $sql = "SELECT COUNT(*) as count FROM qg_sync 
            WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
    
    return false;
}

// Function to call API
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

// Function to extract JSON from response
function extractJson($response) {
    $start = strpos($response, '{');
    $end = strrpos($response, '}');
    return ($start !== false && $end !== false && $end > $start)
        ? substr($response, $start, $end - $start + 1)
        : $response;
}

// Function to log download progress
function logDownloadAction($conn, $year, $project_id = 'all', $region_id = 'all', $status = 'started', $progress = 0, $total = 0, $completed = 0, $error = null) {
    // Start a new log
    if ($status === 'started') {
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        $region_id = $conn->real_escape_string($region_id);
        
        $sql = "INSERT INTO download_logs (year, project_id, region_id, status, progress, total_items, completed_items) 
                VALUES ('$year', '$project_id', '$region_id', 'in_progress', 0, $total, 0)";
        
        if ($conn->query($sql)) {
            return $conn->insert_id;
        }
        return false;
    }
    
    // Update an existing log
    elseif ($status === 'update' && !empty($year)) {
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        $region_id = $conn->real_escape_string($region_id);
        
        $sql = "UPDATE download_logs 
                SET progress = $progress, completed_items = $completed 
                WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id' 
                AND status = 'in_progress'
                ORDER BY id DESC LIMIT 1";
        
        return $conn->query($sql);
    }
    
    // Complete a log (success or error)
    elseif (($status === 'completed' || $status === 'error') && !empty($year)) {
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        $region_id = $conn->real_escape_string($region_id);
        $error = $conn->real_escape_string($error ?? '');
        
        $sql = "UPDATE download_logs 
                SET status = '$status', progress = $progress, completed_items = $completed, 
                    end_time = CURRENT_TIMESTAMP, error_message = " . ($error ? "'$error'" : "NULL") . "
                WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id' 
                AND status = 'in_progress'
                ORDER BY id DESC LIMIT 1";
        
        return $conn->query($sql);
    }
    
    // Get current log status
    elseif ($status === 'get' && !empty($year)) {
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        $region_id = $conn->real_escape_string($region_id);
        
        $sql = "SELECT * FROM download_logs 
                WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id' 
                AND status = 'in_progress'
                ORDER BY id DESC LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    return false;
}

// Function to get available years
function getAvailableYears() {
    return [
        '2023' => '2023',
        '2024' => '2024',
        '2025' => '2025'
    ];
}

// Function to get download logs
function getDownloadLogs($conn, $limit = 10) {
    $sql = "SELECT * FROM download_logs ORDER BY start_time DESC LIMIT $limit";
    $result = $conn->query($sql);
    
    if ($result) {
        $logs = [];
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
        return $logs;
    }
    
    return [];
}
?> 