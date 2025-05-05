<?php
require_once 'config.php';

// Check if user is authenticated and has superadmin privileges
if (!isAuthenticated() || !isSuperAdmin()) {
    echo json_encode([
        'status' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Set header to JSON
header('Content-Type: application/json');

// Handle different API actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'start_sync':
            startSynchronization();
            break;
            
        case 'sync_progress':
            getSyncProgress();
            break;
            
        case 'cancel_sync':
            cancelSynchronization();
            break;
            
        case 'sync_projects':
            syncProjects();
            break;
            
        case 'sync_coverages':
            syncCoverages();
            break;
            
        case 'sync_activities':
            syncActivities();
            break;
            
        case 'get_last_sync':
            getLastSyncInfo();
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

// Function to start a new synchronization process
function startSynchronization() {
    global $conn;
    
    try {
        // Get sync parameters
        $years = isset($_POST['years']) ? json_decode($_POST['years'], true) : [];
        $projects = isset($_POST['projects']) ? json_decode($_POST['projects'], true) : [];
        $regions = isset($_POST['regions']) ? json_decode($_POST['regions'], true) : [];
        
        // Validate input
        if (empty($years)) {
            echo json_encode([
                'status' => false,
                'message' => 'Please select at least one year'
            ]);
            return;
        }
        
        // Start a transaction
        $conn->begin_transaction();
        
        // Create a new sync_status entry
        $sql = "INSERT INTO sync_status (sync_start, sync_by, is_complete) VALUES (NOW(), ?, FALSE)";
        $stmt = $conn->prepare($sql);
        $userId = $_SESSION['user_id'];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Get the newly created sync ID
        $syncId = $conn->insert_id;
        
        // Log the start of synchronization
        $logMessage = "Starting synchronization for " . count($years) . " year(s), " 
                    . (empty($projects) ? "all projects" : count($projects) . " project(s)") . ", "
                    . (empty($regions) ? "all regions" : count($regions) . " region(s)");
        
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $logMessage);
        $logStmt->execute();
        
        // Create sync selections based on parameters
        foreach ($years as $year) {
            if (empty($projects)) {
                // If no specific projects are selected, create a year-level selection
                $sql = "INSERT INTO sync_selections (sync_id, year) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $syncId, $year);
                $stmt->execute();
            } else {
                // Create selections for each project in this year
                foreach ($projects as $projectId) {
                    if (empty($regions)) {
                        // If no specific regions, create a project-level selection
                        $sql = "INSERT INTO sync_selections (sync_id, year, project_id) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("iss", $syncId, $year, $projectId);
                        $stmt->execute();
                    } else {
                        // Create selections for each region in this project
                        foreach ($regions as $region) {
                            list($prov, $kab) = explode('|', $region);
                            
                            // Create region-level selection (ignoring activity selection)
                            $sql = "INSERT INTO sync_selections (sync_id, year, project_id, prov, kab) 
                                   VALUES (?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("issss", $syncId, $year, $projectId, $prov, $kab);
                            $stmt->execute();
                        }
                    }
                }
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        echo json_encode([
            'status' => true,
            'message' => 'Synchronization started successfully',
            'sync_id' => $syncId
        ]);
        
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => false,
            'message' => 'Error starting synchronization: ' . $e->getMessage()
        ]);
    }
}

// Function to get the current progress of synchronization
function getSyncProgress() {
    global $conn;
    
    try {
        $syncId = $_POST['sync_id'];
        
        // Get sync status
        $sql = "SELECT * FROM sync_status WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $syncId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Synchronization not found'
            ]);
            return;
        }
        
        $syncStatus = $result->fetch_assoc();
        
        // Get sync logs
        $logSql = "SELECT * FROM sync_progress_logs WHERE sync_id = ? ORDER BY log_time DESC LIMIT 50";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("i", $syncId);
        $logStmt->execute();
        $logResult = $logStmt->get_result();
        
        $logs = [];
        while ($row = $logResult->fetch_assoc()) {
            $logs[] = $row;
        }
        
        // Get count of total selections and processed selections
        $countSql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_processed = 1 THEN 1 ELSE 0 END) as processed
                     FROM sync_selections WHERE sync_id = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $syncId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countData = $countResult->fetch_assoc();
        
        // Calculate progress percentage
        $totalItems = $countData['total'];
        $processedItems = $countData['processed'];
        $percentComplete = ($totalItems > 0) ? round(($processedItems / $totalItems) * 100, 2) : 0;
        
        echo json_encode([
            'status' => true,
            'data' => [
                'sync_status' => $syncStatus,
                'logs' => $logs,
                'total_items' => $totalItems,
                'processed_items' => $processedItems,
                'percent_complete' => $percentComplete,
                'is_complete' => (bool)$syncStatus['is_complete']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error getting sync progress: ' . $e->getMessage()
        ]);
    }
}

// Function to cancel an ongoing synchronization
function cancelSynchronization() {
    global $conn;
    
    try {
        $syncId = $_POST['sync_id'];
        
        // Update sync status to complete (canceled)
        $sql = "UPDATE sync_status SET is_complete = TRUE, sync_end = NOW(), notes = 'Canceled by user' 
                WHERE id = ? AND is_complete = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $syncId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Synchronization not found or already completed'
            ]);
            return;
        }
        
        // Log the cancellation
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, 'Synchronization canceled by user', 'warning')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("i", $syncId);
        $logStmt->execute();
        
        echo json_encode([
            'status' => true,
            'message' => 'Synchronization canceled successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error canceling synchronization: ' . $e->getMessage()
        ]);
    }
}

// Function to synchronize projects from the API
function syncProjects() {
    global $conn;
    
    try {
        $syncId = $_POST['sync_id'];
        $year = $_POST['year'];
        
        // Get sync status
        $sql = "SELECT * FROM sync_status WHERE id = ? AND is_complete = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $syncId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Synchronization not found or already completed'
            ]);
            return;
        }
        
        // Log the start of project synchronization
        $logMessage = "Fetching projects for year: " . $year;
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $logMessage);
        $logStmt->execute();
        
        // Call the API to get projects
        $url = "https://webapps.bps.go.id/nqaf/qgate/api/Projects/fetchAll";
        $postData = http_build_query(["year" => $year]);
        $apiResponse = callApi($url, $postData);
        $jsonData = json_decode(extractJson($apiResponse), true);
        
        // Check if API returned valid data
        if (!isset($jsonData['status']) || !$jsonData['status']) {
            $errorMessage = "API error: " . ($jsonData['message'] ?? 'Unknown error');
            $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("is", $syncId, $errorMessage);
            $logStmt->execute();
            
            echo json_encode([
                'status' => false,
                'message' => $errorMessage
            ]);
            return;
        }
        
        // Process and save projects
        $projects = $jsonData['data'];
        $count = 0;
        
        // Start a transaction
        $conn->begin_transaction();
        
        foreach ($projects as $project) {
            // Insert or update the project
            $sql = "INSERT INTO projects (id, year, name, last_synced) VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $project['id'], $year, $project['name'], $project['name']);
            $stmt->execute();
            $count++;
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Log success
        $successMessage = "Successfully synchronized " . $count . " projects for year " . $year;
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'success')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $successMessage);
        $logStmt->execute();
        
        // Update projects_synced count
        $updateSql = "UPDATE sync_status SET projects_synced = projects_synced + ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $count, $syncId);
        $updateStmt->execute();
        
        echo json_encode([
            'status' => true,
            'message' => $successMessage,
            'projects' => $projects
        ]);
        
    } catch (Exception $e) {
        // Roll back the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        
        // Log error
        $errorMessage = "Error synchronizing projects: " . $e->getMessage();
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $errorMessage);
        $logStmt->execute();
        
        echo json_encode([
            'status' => false,
            'message' => $errorMessage
        ]);
    }
}

// Function to synchronize coverages/regions from the API
function syncCoverages() {
    global $conn;
    
    try {
        $syncId = $_POST['sync_id'];
        $projectId = $_POST['project_id'];
        $year = $_POST['year'];
        
        // Get sync status
        $sql = "SELECT * FROM sync_status WHERE id = ? AND is_complete = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $syncId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Synchronization not found or already completed'
            ]);
            return;
        }
        
        // Check if we have specific regions for this sync
        $regionsQuery = "SELECT DISTINCT prov, kab FROM sync_selections 
                        WHERE sync_id = ? AND year = ? AND project_id = ? AND prov != ''";
        $regionsStmt = $conn->prepare($regionsQuery);
        $regionsStmt->bind_param("iss", $syncId, $year, $projectId);
        $regionsStmt->execute();
        $regionsResult = $regionsStmt->get_result();
        
        $selectedRegions = [];
        while ($region = $regionsResult->fetch_assoc()) {
            $selectedRegions[] = [
                'prov' => $region['prov'],
                'kab' => $region['kab']
            ];
        }
        
        // Log the start of coverage synchronization
        $logMessage = "Fetching regions for project: " . $projectId . " (Year: " . $year . ")";
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $logMessage);
        $logStmt->execute();
        
        // Call the API to get coverages
        $url = "https://webapps.bps.go.id/nqaf/qgate/api/coverages/fetchAll";
        $postData = http_build_query([
            "id_project" => $projectId,
            "year" => $year
        ]);
        
        // Log the API request
        $apiLogMessage = "API call: " . $url . " with parameters: id_project=" . $projectId . ", year=" . $year;
        $apiLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $apiLogStmt = $conn->prepare($apiLogSql);
        $apiLogStmt->bind_param("is", $syncId, $apiLogMessage);
        $apiLogStmt->execute();
        
        $apiResponse = callApi($url, $postData);
        
        // Check for PHP error page or bad response
        if (strpos($apiResponse, 'An uncaught Exception was encountered') !== false ||
            strpos($apiResponse, 'Fatal error') !== false ||
            strpos($apiResponse, 'Parse error') !== false ||
            strpos($apiResponse, 'syntax error') !== false) {
            
            $errorDetails = "API returned a PHP error page instead of valid data for region coverages.";
            $errorLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $errorLogStmt = $conn->prepare($errorLogSql);
            $errorLogStmt->bind_param("is", $syncId, $errorDetails);
            $errorLogStmt->execute();
            
            // Return error with more details
            echo json_encode([
                'status' => false,
                'message' => 'Error: BPS API returned an error page instead of valid data. Please check logs.'
            ]);
            return;
        }
        
        $jsonData = json_decode(extractJson($apiResponse), true);
        
        // Check if API returned valid data
        if (!isset($jsonData['status']) || !$jsonData['status']) {
            $errorMessage = "API error: " . ($jsonData['message'] ?? 'Unknown error');
            $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("is", $syncId, $errorMessage);
            $logStmt->execute();
            
            echo json_encode([
                'status' => false,
                'message' => $errorMessage
            ]);
            return;
        }
        
        // Process and save coverages
        $coverages = $jsonData['data'];
        
        // Filter coverages if we have selected regions
        if (!empty($selectedRegions)) {
            $filteredCoverages = [];
            foreach ($coverages as $coverage) {
                foreach ($selectedRegions as $selected) {
                    if ($coverage['prov'] == $selected['prov'] && $coverage['kab'] == $selected['kab']) {
                        $filteredCoverages[] = $coverage;
                        break;
                    }
                }
            }
            $coverages = $filteredCoverages;
            
            // Log the filtered coverages
            $logMessage = "Filtered to " . count($coverages) . " selected regions";
            $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("is", $syncId, $logMessage);
            $logStmt->execute();
        }
        
        $count = 0;
        
        // Start a transaction
        $conn->begin_transaction();
        
        foreach ($coverages as $coverage) {
            // Insert or update the coverage
            $sql = "INSERT INTO coverages (project_id, year, prov, kab, name, last_synced) 
                    VALUES (?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $projectId, $year, $coverage['prov'], $coverage['kab'], 
                              $coverage['name'], $coverage['name']);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $count++;
                // Log each successful region insert/update
                $regionLogMsg = "Saved region: " . $coverage['name'] . " (" . $coverage['prov'] . $coverage['kab'] . ")";
                $regionLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                $regionLogStmt = $conn->prepare($regionLogSql);
                $regionLogStmt->bind_param("is", $syncId, $regionLogMsg);
                $regionLogStmt->execute();
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Log success
        $successMessage = "Successfully synchronized " . $count . " regions for project " . $projectId;
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'success')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $successMessage);
        $logStmt->execute();
        
        // Update regions_synced count
        $updateSql = "UPDATE sync_status SET regions_synced = regions_synced + ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $count, $syncId);
        $updateStmt->execute();
        
        echo json_encode([
            'status' => true,
            'message' => $successMessage,
            'coverages' => $coverages
        ]);
        
    } catch (Exception $e) {
        // Roll back the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        
        // Log error
        $errorMessage = "Error synchronizing regions: " . $e->getMessage();
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $errorMessage);
        $logStmt->execute();
        
        echo json_encode([
            'status' => false,
            'message' => $errorMessage
        ]);
    }
}

// Function to synchronize activities and their monitoring data from the API
function syncActivities() {
    global $conn;
    
    try {
        $syncId = $_POST['sync_id'];
        $projectId = $_POST['project_id'];
        $year = $_POST['year'];
        $prov = $_POST['prov'];
        $kab = $_POST['kab'];
        
        // Get sync status
        $sql = "SELECT * FROM sync_status WHERE id = ? AND is_complete = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $syncId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => false,
                'message' => 'Synchronization not found or already completed'
            ]);
            return;
        }
        
        // Log the start of activity synchronization
        $logMessage = "Fetching activities for project: " . $projectId . " (Year: " . $year . ", Region: " . $prov . $kab . ")";
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $logMessage);
        $logStmt->execute();
        
        // Add a delay to slow down processing and make logs visible (1 second)
        sleep(1);
        
        // Call the API to get activities
        $url = "https://webapps.bps.go.id/nqaf/qgate/api/monitoring/fetchData";
        $postData = http_build_query([
            "id_project" => $projectId,
            "year" => $year,
            "prov" => $prov,
            "kab" => $kab
        ]);
        
        // Log API call details
        $apiLogMessage = "API call with parameters: id_project=" . $projectId . ", year=" . $year . ", prov=" . $prov . ", kab=" . $kab;
        $apiLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $apiLogStmt = $conn->prepare($apiLogSql);
        $apiLogStmt->bind_param("is", $syncId, $apiLogMessage);
        $apiLogStmt->execute();
        
        $apiResponse = callApi($url, $postData);
        
        // Save raw response for debugging (truncated if too long)
        $rawResponse = substr($apiResponse, 0, 1000);
        if (strlen($apiResponse) > 1000) {
            $rawResponse .= "... [truncated]";
        }
        $rawLogMessage = "Raw API response (truncated): " . $rawResponse;
        $rawLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
        $rawLogStmt = $conn->prepare($rawLogSql);
        $rawLogStmt->bind_param("is", $syncId, $rawLogMessage);
        $rawLogStmt->execute();
        
        // Check if response contains PHP error messages
        if (strpos($apiResponse, 'An uncaught Exception was encountered') !== false ||
            strpos($apiResponse, 'Fatal error') !== false ||
            strpos($apiResponse, 'Parse error') !== false ||
            strpos($apiResponse, 'syntax error') !== false) {
            
            // This is a PHP error page, not valid JSON
            $errorDetails = "API returned a PHP error page instead of data. Server-side error detected.";
            
            // Add more specific error info if available
            if (strpos($apiResponse, "unexpected '.0' (T_DNUMBER)") !== false) {
                $errorDetails .= " Specific error: Known BPS API syntax error with decimal numbers.";
            } elseif (preg_match('/Line Number: (\d+)/', $apiResponse, $matches)) {
                $errorDetails .= " Error on line " . $matches[1] . " of BPS server code.";
            }
            
            $errorLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $errorLogStmt = $conn->prepare($errorLogSql);
            $errorLogStmt->bind_param("is", $syncId, $errorDetails);
            $errorLogStmt->execute();
            
            // Check if any data exists for this region before marking as completed
            $checkSql = "SELECT COUNT(*) as count FROM monitoring_data 
                         WHERE project_id = ? AND year = ? AND prov = ? AND kab = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ssss", $projectId, $year, $prov, $kab);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingCount = $checkResult->fetch_assoc()['count'];
            
            if ($existingCount > 0) {
                $infoMessage = "Region already has " . $existingCount . " activities in database. Using existing data.";
                $infoLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                $infoLogStmt = $conn->prepare($infoLogSql);
                $infoLogStmt->bind_param("is", $syncId, $infoMessage);
                $infoLogStmt->execute();
            } else {
                $warnMessage = "No existing data found for this region and API returned error.";
                $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
                $warnLogStmt = $conn->prepare($warnLogSql);
                $warnLogStmt->bind_param("is", $syncId, $warnMessage);
                $warnLogStmt->execute();
            }
            
            // Mark as processed and continue the sync
            $workaroundMsg = "Marking region " . $prov . $kab . " as processed despite API error (server-side issue)";
            $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
            $warnLogStmt = $conn->prepare($warnLogSql);
            $warnLogStmt->bind_param("is", $syncId, $workaroundMsg);
            $warnLogStmt->execute();
            
            // Update the sync selection as processed
            $selectionSql = "UPDATE sync_selections SET is_processed = TRUE 
                             WHERE sync_id = ? AND year = ? AND project_id = ? AND prov = ? AND kab = ?";
            $selectionStmt = $conn->prepare($selectionSql);
            $selectionStmt->bind_param("issss", $syncId, $year, $projectId, $prov, $kab);
            $selectionStmt->execute();
            
            // Add another delay before checking completion (2 seconds)
            sleep(2);
            
            // Check if all selections have been processed
            checkAndCompleteSync($syncId, $conn);
            
            echo json_encode([
                'status' => true,
                'message' => $workaroundMsg,
                'activities' => [],
                'all_complete' => false
            ]);
            return;
        }
        
        // Extract and parse JSON from response
        $extractedJson = extractJson($apiResponse);
        
        // Check if extracted JSON is empty or invalid
        if (empty($extractedJson) || $extractedJson === $apiResponse) {
            $errorDetails = "Could not extract valid JSON from API response";
            $errorLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $errorLogStmt = $conn->prepare($errorLogSql);
            $errorLogStmt->bind_param("is", $syncId, $errorDetails);
            $errorLogStmt->execute();
            
            // Check if any data exists for this region before marking as completed
            $checkSql = "SELECT COUNT(*) as count FROM monitoring_data 
                         WHERE project_id = ? AND year = ? AND prov = ? AND kab = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ssss", $projectId, $year, $prov, $kab);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingCount = $checkResult->fetch_assoc()['count'];
            
            if ($existingCount > 0) {
                $infoMessage = "Region already has " . $existingCount . " activities in database. Using existing data.";
                $infoLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                $infoLogStmt = $conn->prepare($infoLogSql);
                $infoLogStmt->bind_param("is", $syncId, $infoMessage);
                $infoLogStmt->execute();
            } else {
                $warnMessage = "No existing data found for this region and API returned invalid JSON.";
                $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
                $warnLogStmt = $conn->prepare($warnLogSql);
                $warnLogStmt->bind_param("is", $syncId, $warnMessage);
                $warnLogStmt->execute();
            }
            
            // Mark as processed and continue the sync
            $workaroundMsg = "Marking region " . $prov . $kab . " as processed despite invalid JSON (server-side issue)";
            $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
            $warnLogStmt = $conn->prepare($warnLogSql);
            $warnLogStmt->bind_param("is", $syncId, $workaroundMsg);
            $warnLogStmt->execute();
            
            // Update the sync selection as processed
            $selectionSql = "UPDATE sync_selections SET is_processed = TRUE 
                             WHERE sync_id = ? AND year = ? AND project_id = ? AND prov = ? AND kab = ?";
            $selectionStmt = $conn->prepare($selectionSql);
            $selectionStmt->bind_param("issss", $syncId, $year, $projectId, $prov, $kab);
            $selectionStmt->execute();
            
            // Add another delay before checking completion (2 seconds)
            sleep(2);
            
            // Check if all selections have been processed
            checkAndCompleteSync($syncId, $conn);
            
            echo json_encode([
                'status' => true,
                'message' => $workaroundMsg,
                'activities' => [],
                'all_complete' => false
            ]);
            return;
        }
        
        // Now try to parse the JSON
        $jsonData = @json_decode($extractedJson, true);
        
        // Process and save activities
        if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data']) && !empty($jsonData['data'])) {
            $activities = $jsonData['data'];
            $count = 0;
            $insertedCount = 0;
            
            // Log the number of activities found
            $foundMsg = "Found " . count($activities) . " activities for project " . $projectId . " (Region: " . $prov . $kab . ")";
            $foundLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
            $foundLogStmt = $conn->prepare($foundLogSql);
            $foundLogStmt->bind_param("is", $syncId, $foundMsg);
            $foundLogStmt->execute();
            
            // Start a transaction
            $conn->begin_transaction();
            
            foreach ($activities as $activity) {
                // Insert or update the activity monitoring data
                $sql = "INSERT INTO monitoring_data 
                        (project_id, year, prov, kab, activity_id, activity_name, start_date, end_date, 
                         plan_date, realization_date, progress, status, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                        activity_name = ?, start_date = ?, end_date = ?, 
                        plan_date = ?, realization_date = ?, progress = ?, status = ?, notes = ?, 
                        last_updated = NOW()";
                
                $stmt = $conn->prepare($sql);
                
                // Convert dates to MySQL format or NULL
                $startDate = !empty($activity['start_date']) ? date('Y-m-d', strtotime($activity['start_date'])) : null;
                $endDate = !empty($activity['end_date']) ? date('Y-m-d', strtotime($activity['end_date'])) : null;
                $planDate = !empty($activity['plan_date']) ? date('Y-m-d', strtotime($activity['plan_date'])) : null;
                $realizationDate = !empty($activity['realization_date']) ? date('Y-m-d', strtotime($activity['realization_date'])) : null;
                
                $stmt->bind_param(
                    "sssssssssssss" . "sssssss", 
                    $projectId, $year, $prov, $kab, $activity['id'], $activity['name'], 
                    $startDate, $endDate, $planDate, $realizationDate, 
                    $activity['progress'], $activity['status'], $activity['notes'],
                    // Duplicate fields for ON DUPLICATE KEY UPDATE
                    $activity['name'], $startDate, $endDate, $planDate, $realizationDate, 
                    $activity['progress'], $activity['status'], $activity['notes']
                );
                
                $stmt->execute();
                
                // Only count actual insertions or updates
                if ($stmt->affected_rows > 0) {
                    $insertedCount++;
                    
                    // Log each successful activity insert/update (but limit to first 10 to avoid log spam)
                    if ($count < 10) {
                        $activityLogMsg = "Saved activity: " . $activity['name'] . " (ID: " . $activity['id'] . ")";
                        $activityLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                        $activityLogStmt = $conn->prepare($activityLogSql);
                        $activityLogStmt->bind_param("is", $syncId, $activityLogMsg);
                        $activityLogStmt->execute();
                    } else if ($count == 10) {
                        $moreLogMsg = "More activities being processed... (not individually logged)";
                        $moreLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                        $moreLogStmt = $conn->prepare($moreLogSql);
                        $moreLogStmt->bind_param("is", $syncId, $moreLogMsg);
                        $moreLogStmt->execute();
                    }
                }
                
                $count++;
                
                // Update the sync selection as processed
                $selectionSql = "UPDATE sync_selections SET is_processed = TRUE 
                                 WHERE sync_id = ? AND year = ? AND project_id = ? AND prov = ? AND kab = ? 
                                 AND (activity_id = ? OR activity_id IS NULL)";
                $selectionStmt = $conn->prepare($selectionSql);
                $selectionStmt->bind_param("isssss", $syncId, $year, $projectId, $prov, $kab, $activity['id']);
                $selectionStmt->execute();
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Log success
            $successMessage = "Successfully synchronized " . $insertedCount . " activities for project " . $projectId . " (Region: " . $prov . $kab . ")";
            $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'success')";
            $logStmt = $conn->prepare($logSql);
            $logStmt->bind_param("is", $syncId, $successMessage);
            $logStmt->execute();
            
            // Update activities_synced count
            $updateSql = "UPDATE sync_status SET activities_synced = activities_synced + ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $insertedCount, $syncId);
            $updateStmt->execute();
            
            // Add a delay before checking completion (2 seconds)
            sleep(2);
            
            // Check if all selections have been processed
            $allComplete = checkAndCompleteSync($syncId, $conn);
            
            echo json_encode([
                'status' => true,
                'message' => $successMessage,
                'activities' => $activities,
                'all_complete' => $allComplete
            ]);
            return;
        } else {
            // API returned status false or no data
            $errorMessage = "API error: " . ($jsonData['message'] ?? 'Unknown error');
            
            // Add debug information
            $errorDetails = "Error details: ";
            if (isset($jsonData['message'])) {
                $errorDetails .= $jsonData['message'];
            } else {
                $errorDetails .= "No specific error message.";
            }
            
            if (isset($jsonData['error'])) {
                $errorDetails .= " Error code: " . $jsonData['error'];
            }
            
            $errorLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
            $errorLogStmt = $conn->prepare($errorLogSql);
            $errorLogStmt->bind_param("is", $syncId, $errorDetails);
            $errorLogStmt->execute();
            
            // Check if any data exists for this region before marking as completed
            $checkSql = "SELECT COUNT(*) as count FROM monitoring_data 
                         WHERE project_id = ? AND year = ? AND prov = ? AND kab = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ssss", $projectId, $year, $prov, $kab);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingCount = $checkResult->fetch_assoc()['count'];
            
            if ($existingCount > 0) {
                $infoMessage = "Region already has " . $existingCount . " activities in database. Using existing data.";
                $infoLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'info')";
                $infoLogStmt = $conn->prepare($infoLogSql);
                $infoLogStmt->bind_param("is", $syncId, $infoMessage);
                $infoLogStmt->execute();
            } else {
                $warnMessage = "No existing data found for this region and API returned error.";
                $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
                $warnLogStmt = $conn->prepare($warnLogSql);
                $warnLogStmt->bind_param("is", $syncId, $warnMessage);
                $warnLogStmt->execute();
            }
            
            // Mark the region as processed anyway
            $workaroundMsg = "Marking region " . $prov . $kab . " as complete despite API error";
            $warnLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'warning')";
            $warnLogStmt = $conn->prepare($warnLogSql);
            $warnLogStmt->bind_param("is", $syncId, $workaroundMsg);
            $warnLogStmt->execute();
            
            // Update the sync selection as processed
            $selectionSql = "UPDATE sync_selections SET is_processed = TRUE 
                            WHERE sync_id = ? AND year = ? AND project_id = ? AND prov = ? AND kab = ?";
            $selectionStmt = $conn->prepare($selectionSql);
            $selectionStmt->bind_param("issss", $syncId, $year, $projectId, $prov, $kab);
            $selectionStmt->execute();
            
            // Add a delay before checking completion (2 seconds)
            sleep(2);
            
            // Check if all selections have been processed
            checkAndCompleteSync($syncId, $conn);
            
            echo json_encode([
                'status' => true,
                'message' => $workaroundMsg,
                'activities' => [],
                'all_complete' => false
            ]);
            return;
        }
    } catch (Exception $e) {
        // Roll back the transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        
        // Log error
        $errorMessage = "Error synchronizing activities: " . $e->getMessage();
        $logSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'error')";
        $logStmt = $conn->prepare($logSql);
        $logStmt->bind_param("is", $syncId, $errorMessage);
        $logStmt->execute();
        
        echo json_encode([
            'status' => false,
            'message' => $errorMessage
        ]);
    }
}

// Helper function to check if all selections have been processed and mark sync as complete if they have
function checkAndCompleteSync($syncId, $conn) {
    // Check if all selections have been processed
    $checkSql = "SELECT COUNT(*) as remaining FROM sync_selections WHERE sync_id = ? AND is_processed = FALSE";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $syncId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $remaining = $checkResult->fetch_assoc()['remaining'];
    
    // If all selections have been processed, mark the sync as complete
    if ($remaining == 0) {
        $completeSql = "UPDATE sync_status SET is_complete = TRUE, sync_end = NOW() WHERE id = ?";
        $completeStmt = $conn->prepare($completeSql);
        $completeStmt->bind_param("i", $syncId);
        $completeStmt->execute();
        
        $completeMessage = "Synchronization completed successfully.";
        $completeLogSql = "INSERT INTO sync_progress_logs (sync_id, log_message, log_type) VALUES (?, ?, 'success')";
        $completeLogStmt = $conn->prepare($completeLogSql);
        $completeLogStmt->bind_param("is", $syncId, $completeMessage);
        $completeLogStmt->execute();
        
        return true;
    }
    
    return false;
}

// Function to get information about the last successful synchronization
function getLastSyncInfo() {
    global $conn;
    
    try {
        // Get the last successful sync
        $sql = "SELECT s.*, u.name as sync_by_name
                FROM sync_status s
                JOIN users u ON s.sync_by = u.id
                WHERE s.is_complete = TRUE
                ORDER BY s.sync_end DESC
                LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'status' => true,
                'message' => 'No synchronization found',
                'has_data' => false
            ]);
            return;
        }
        
        $syncInfo = $result->fetch_assoc();
        
        // Get some stats about the synchronization
        $statsSql = "SELECT COUNT(DISTINCT project_id) as projects_count,
                            COUNT(DISTINCT CONCAT(prov, kab)) as regions_count,
                            COUNT(*) as activities_count,
                            MAX(last_updated) as last_update
                     FROM monitoring_data";
        
        $statsResult = $conn->query($statsSql);
        $stats = $statsResult->fetch_assoc();
        
        echo json_encode([
            'status' => true,
            'has_data' => true,
            'sync_info' => $syncInfo,
            'stats' => $stats
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Error getting last sync info: ' . $e->getMessage()
        ]);
    }
}
?> 