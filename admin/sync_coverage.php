<?php
require_once '../config.php';

// Check if user is authenticated and is superadmin
if (!isAuthenticated() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = '';

// Process sync request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // If sync_status AJAX request
    if (isset($_POST['check_status'])) {
        $sql = "SELECT * FROM sync_logs WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_POST['log_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $log = $result->fetch_assoc();
        $stmt->close();
        
        // Add time for display
        $log['time'] = date('H:i:s', strtotime($log['updated_at'] ?? $log['created_at']));
        
        header('Content-Type: application/json');
        echo json_encode($log);
        exit;
    }
    
    // If manual sync request
    if (isset($_POST['sync'])) {
        // Create initial sync log
        $stmt = $conn->prepare("INSERT INTO sync_logs (sync_type, status, message) VALUES (?, ?, ?)");
        $syncType = "sync_started";
        $syncStatus = true;
        $syncMessage = "Memulai proses sinkronisasi...";
        $stmt->bind_param("sis", $syncType, $syncStatus, $syncMessage);
        $stmt->execute();
        $syncLogId = $conn->insert_id;
        $stmt->close();
        
        // Return the log ID to the browser immediately for polling
        header('Content-Type: application/json');
        echo json_encode(['log_id' => $syncLogId]);
        
        // Flush output buffer to send response immediately
        ob_flush();
        flush();
        
        // Run the sync process
        try {
            // 1. Get years (hardcoded as per requirements)
            $years = ['2023', '2024', '2025'];
            
            // Update log
            updateSyncLog($syncLogId, "Mengambil data tahun: " . implode(", ", $years), true);
            
            $totalProjects = 0;
            $totalCoverages = 0;
            $startTime = microtime(true);
            
            // Special handling - process 2025 first to ensure it works
            $specialYear = '2025';
            updateSyncLog($syncLogId, "Memproses tahun {$specialYear} terlebih dahulu untuk memastikan berhasil", true);
            
            // Directly use fix_2025_data.php approach
            $url = $api_base_url . "Projects/fetchAll";
            $postData = http_build_query(["year" => $specialYear]);
            updateSyncLog($syncLogId, "Mengambil data proyek tahun {$specialYear} dengan metode khusus", true);
            
            $response = callApi($url, $postData);
            
            // Log full API response to debug file
            $debugFile = fopen("../logs/debug_2025.txt", "w");
            fwrite($debugFile, "API URL: " . $url . "\n");
            fwrite($debugFile, "POST Data: " . $postData . "\n");
            fwrite($debugFile, "API Response: " . $response . "\n");
            fclose($debugFile);
            
            $jsonData = json_decode(extractJson($response), true);
            
            if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
                $projects = $jsonData['data'];
                $projectCount = count($projects);
                $totalProjects += $projectCount;
                
                updateSyncLog($syncLogId, "Berhasil mengambil {$projectCount} proyek tahun {$specialYear}", true);
                
                // Begin transaction for this year's data
                $conn->begin_transaction();
                
                // Process each project
                foreach ($projects as $projectIndex => $project) {
                    // Skip if missing critical data
                    if (empty($project['id']) || empty($project['name'])) {
                        updateSyncLog($syncLogId, "Melewati proyek tidak valid: " . json_encode($project), false);
                        continue;
                    }
                    
                    $projectProgress = ($projectIndex + 1) . "/" . $projectCount;
                    updateSyncLog($syncLogId, "Memproses proyek {$project['name']} ({$projectProgress})", true);
                    
                    // Insert or update project
                    $stmt = $conn->prepare("INSERT INTO projects (id, year, name, last_synced) VALUES (?, ?, ?, NOW()) 
                                          ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                    $stmt->bind_param("ssss", $project['id'], $specialYear, $project['name'], $project['name']);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Get coverage for each project
                    $url = $api_base_url . "coverages/fetchAll";
                    $postData = http_build_query(["id_project" => $project['id']]);
                    
                    $responseC = callApi($url, $postData);
                    $jsonDataC = json_decode(extractJson($responseC), true);
                    
                    if (isset($jsonDataC['status']) && $jsonDataC['status'] && isset($jsonDataC['data'])) {
                        $coverages = $jsonDataC['data'];
                        $coverageCount = count($coverages);
                        $totalCoverages += $coverageCount;
                        
                        // Insert pusat (National) coverage
                        $pusatName = 'Pusat - Nasional';
                        $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                              VALUES (?, ?, '00', '00', ?) 
                                              ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                        $stmt->bind_param("ssss", $project['id'], $specialYear, $pusatName, $pusatName);
                        $stmt->execute();
                        $stmt->close();
                        
                        // Insert other coverages
                        foreach ($coverages as $coverage) {
                            // Skip if missing critical data
                            if (empty($coverage['prov']) || empty($coverage['kab']) || empty($coverage['name'])) {
                                continue;
                            }
                            
                            $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                                  VALUES (?, ?, ?, ?, ?) 
                                                  ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                            $stmt->bind_param("ssssss", $project['id'], $specialYear, $coverage['prov'], $coverage['kab'], $coverage['name'], $coverage['name']);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                    
                    // Commit after each project
                    $conn->commit();
                    $conn->begin_transaction();
                }
                
                // Final commit
                $conn->commit();
                
                updateSyncLog($syncLogId, "Berhasil menyimpan data {$projectCount} proyek tahun {$specialYear}", true);
            } else {
                updateSyncLog($syncLogId, "Gagal mengambil data proyek tahun {$specialYear}: " . json_encode($jsonData), false);
            }
            
            // Now process the regular years
            foreach ($years as $yearIndex => $year) {
                // Skip 2025 as we already processed it
                if ($year === '2025') continue;
                
                // Update log for current year
                updateSyncLog($syncLogId, "Memproses tahun {$year} (" . ($yearIndex + 1) . "/" . count($years) . ")", true);
                
                // 2. Get projects for each year
                $url = $api_base_url . "Projects/fetchAll";
                $postData = http_build_query(["year" => $year]);
                updateSyncLog($syncLogId, "Mengambil data proyek tahun {$year} dari API", true);
                
                $response = callApi($url, $postData);
                $jsonData = json_decode(extractJson($response), true);
                
                // Tambahkan log khusus untuk 2025
                if ($year === '2025') {
                    // Log respons API untuk 2025 ke file
                    $logFile = fopen("../logs/sync_2025_log.txt", "a");
                    fwrite($logFile, "==== Log Sync 2025 ====\n");
                    fwrite($logFile, "Time: " . date('Y-m-d H:i:s') . "\n");
                    fwrite($logFile, "API Response: " . substr($response, 0, 1000) . "\n");
                    fwrite($logFile, "Parsed data: " . json_encode($jsonData) . "\n\n");
                    fclose($logFile);
                }
                
                if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
                    $projects = $jsonData['data'];
                    $projectCount = count($projects);
                    $totalProjects += $projectCount;
                    
                    updateSyncLog($syncLogId, "Berhasil mengambil {$projectCount} proyek tahun {$year}", true);
                    
                    // Begin transaction for this year's data
                    $conn->begin_transaction();
                    
                    // Log sync for year's projects
                    $stmt = $conn->prepare("INSERT INTO sync_logs (sync_type, status, message) VALUES (?, ?, ?)");
                    $syncType = "projects_" . $year;
                    $syncStatus = true;
                    $syncMessage = "Synced " . count($projects) . " projects for year " . $year;
                    $stmt->bind_param("sis", $syncType, $syncStatus, $syncMessage);
                    $stmt->execute();
                    $stmt->close();
                    
                    // 3. Process each project
                    foreach ($projects as $projectIndex => $project) {
                        // Update log for current project
                        $projectProgress = ($projectIndex + 1) . "/" . $projectCount;
                        updateSyncLog($syncLogId, "Memproses proyek {$project['name']} ({$projectProgress})", true);
                        
                        // Insert or update project
                        $stmt = $conn->prepare("INSERT INTO projects (id, year, name, last_synced) VALUES (?, ?, ?, NOW()) 
                                              ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                        $stmt->bind_param("ssss", $project['id'], $year, $project['name'], $project['name']);
                        $stmt->execute();
                        $stmt->close();
                        
                        // 4. Get coverage for each project
                        $url = $api_base_url . "coverages/fetchAll";
                        $postData = http_build_query(["id_project" => $project['id']]);
                        updateSyncLog($syncLogId, "Mengambil data cakupan untuk proyek: {$project['name']}", true);
                        
                        $responseC = callApi($url, $postData);
                        $jsonDataC = json_decode(extractJson($responseC), true);
                        
                        if (isset($jsonDataC['status']) && $jsonDataC['status'] && isset($jsonDataC['data'])) {
                            $coverages = $jsonDataC['data'];
                            $coverageCount = count($coverages);
                            $totalCoverages += $coverageCount;
                            
                            updateSyncLog($syncLogId, "Berhasil mengambil {$coverageCount} cakupan untuk proyek: {$project['name']}", true);
                            
                            // Insert pusat (National) coverage
                            $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                                  VALUES (?, ?, '00', '00', 'Pusat - Nasional') 
                                                  ON DUPLICATE KEY UPDATE name = 'Pusat - Nasional', last_synced = NOW()");
                            $stmt->bind_param("ss", $project['id'], $year);
                            $stmt->execute();
                            $stmt->close();
                            
                            // Insert other coverages in batches of 50
                            $batchSize = 50;
                            $batches = array_chunk($coverages, $batchSize);
                            $totalBatches = count($batches);
                            
                            foreach ($batches as $batchIndex => $batch) {
                                $batchProgress = ($batchIndex + 1) . "/" . $totalBatches;
                                updateSyncLog($syncLogId, "Menyimpan batch cakupan {$batchProgress} untuk proyek: {$project['name']}", true);
                                
                                foreach ($batch as $coverage) {
                                    $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                                          VALUES (?, ?, ?, ?, ?) 
                                                          ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                                    $stmt->bind_param("ssssss", $project['id'], $year, $coverage['prov'], $coverage['kab'], $coverage['name'], $coverage['name']);
                                    $stmt->execute();
                                    $stmt->close();
                                }
                                
                                // Commit after each batch
                                $conn->commit();
                                
                                // Start a new transaction for the next batch
                                $conn->begin_transaction();
                            }
                        } else {
                            updateSyncLog($syncLogId, "Gagal mengambil data cakupan untuk proyek: {$project['name']}", false);
                        }
                        
                        // Intermediate commit after each project to ensure data is saved
                        // even if a later project fails
                        $conn->commit();
                        
                        // Start a new transaction for the next project
                        $conn->begin_transaction();
                    }
                    
                    // Final commit for this year
                    $conn->commit();
                    
                } else {
                    updateSyncLog($syncLogId, "Gagal mengambil data proyek tahun {$year}", false);
                    
                    // Tambahkan penanganan khusus untuk 2025
                    if ($year === '2025') {
                        updateSyncLog($syncLogId, "Mencoba metode alternatif untuk tahun 2025...", true);
                        
                        // Gunakan pendekatan yang sama dengan fix_2025_data.php
                        $url = $api_base_url . "Projects/fetchAll";
                        $postData = http_build_query(["year" => "2025"]);
                        $response = callApi($url, $postData);
                        $jsonData = json_decode(extractJson($response), true);
                        
                        if (isset($jsonData['status']) && $jsonData['status'] && isset($jsonData['data'])) {
                            $projects = $jsonData['data'];
                            $projectCount = count($projects);
                            $totalProjects += $projectCount;
                            
                            updateSyncLog($syncLogId, "Berhasil mengambil {$projectCount} proyek tahun 2025 dengan metode alternatif", true);
                            
                            // Begin transaction for this year's data
                            $conn->begin_transaction();
                            
                            // Process projects one by one
                            foreach ($projects as $projectIndex => $project) {
                                $projectProgress = ($projectIndex + 1) . "/" . $projectCount;
                                updateSyncLog($syncLogId, "Memproses proyek {$project['name']} ({$projectProgress})", true);
                                
                                // Insert project
                                $stmt = $conn->prepare("INSERT INTO projects (id, year, name, last_synced) VALUES (?, '2025', ?, NOW()) 
                                                      ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                                $stmt->bind_param("sss", $project['id'], $project['name'], $project['name']);
                                $stmt->execute();
                                $stmt->close();
                                
                                // Get coverage
                                $url = $api_base_url . "coverages/fetchAll";
                                $postData = http_build_query(["id_project" => $project['id']]);
                                $responseCov = callApi($url, $postData);
                                $jsonDataCov = json_decode(extractJson($responseCov), true);
                                
                                if (isset($jsonDataCov['status']) && $jsonDataCov['status'] && isset($jsonDataCov['data'])) {
                                    $coverages = $jsonDataCov['data'];
                                    $coverageCount = count($coverages);
                                    $totalCoverages += $coverageCount;
                                    
                                    // Insert national coverage
                                    $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                                          VALUES (?, '2025', '00', '00', 'Pusat - Nasional') 
                                                          ON DUPLICATE KEY UPDATE name = 'Pusat - Nasional', last_synced = NOW()");
                                    $stmt->bind_param("s", $project['id']);
                                    $stmt->execute();
                                    $stmt->close();
                                    
                                    // Insert other coverages
                                    foreach ($coverages as $coverage) {
                                        $stmt = $conn->prepare("INSERT INTO coverages (project_id, year, prov, kab, name) 
                                                              VALUES (?, '2025', ?, ?, ?) 
                                                              ON DUPLICATE KEY UPDATE name = ?, last_synced = NOW()");
                                        $stmt->bind_param("sssss", $project['id'], $coverage['prov'], $coverage['kab'], $coverage['name'], $coverage['name']);
                                        $stmt->execute();
                                        $stmt->close();
                                    }
                                }
                                
                                // Commit after each project
                                $conn->commit();
                                $conn->begin_transaction();
                            }
                            
                            // Final commit
                            $conn->commit();
                        }
                    }
                }
            }
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            // Final log update
            $finalMessage = "Sinkronisasi selesai. Total {$totalProjects} proyek dan {$totalCoverages} cakupan berhasil disinkronkan dalam {$duration} detik.";
            updateSyncLog($syncLogId, $finalMessage, true);
            
            $message = $finalMessage;
            $messageType = 'success';
            
        } catch (Exception $e) {
            // Log error
            updateSyncLog($syncLogId, "Error: " . $e->getMessage(), false);
            
            // Try to rollback if a transaction is active
            try {
                $conn->rollback();
            } catch (Exception $rollbackException) {
                // Ignore rollback errors
            }
            
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'danger';
        }
        
        // End the script since we already sent the response
        exit;
    }
}

// Function to update sync log
function updateSyncLog($logId, $message, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE sync_logs SET message = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("sis", $message, $status, $logId);
    $stmt->execute();
    $stmt->close();
    
    // Also log to sync_logs history
    $stmt = $conn->prepare("INSERT INTO sync_logs (sync_type, status, message) VALUES (?, ?, ?)");
    $syncType = "sync_progress";
    $stmt->bind_param("sis", $syncType, $status, $message);
    $stmt->execute();
    $stmt->close();
}

// Get sync logs for display
$syncLogs = [];
$result = $conn->query("SELECT * FROM sync_logs ORDER BY created_at DESC LIMIT 10");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $syncLogs[] = $row;
    }
}

// Count total coverages
$countResult = $conn->query("SELECT COUNT(*) as total FROM coverages");
$totalCoverages = $countResult->fetch_assoc()['total'];

// Count total projects
$projectResult = $conn->query("SELECT COUNT(*) as total FROM projects");
$totalProjects = $projectResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync Coverage - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #0071e3;
            --success-color: #34c759;
            --warning-color: #ff9f0a;
            --danger-color: #ff3b30;
            --neutral-color: #8e8e93;
            --light-color: #f5f5f7;
            --dark-color: #1d1d1f;
            --border-color: #d2d2d7;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            min-height: 100vh;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .sidebar {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        .content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        .nav-link {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(0,113,227,0.1);
            color: var(--primary-color);
        }
        
        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .nav-link i {
            width: 24px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #005bbc;
            transform: translateY(-1px);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        
        .card-stat {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--neutral-color);
            margin-top: 0.5rem;
        }

        /* Live log styles */
        .live-log {
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            height: 300px;
            overflow-y: auto;
            padding: 1rem;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        
        .live-log-entry {
            margin-bottom: 0.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .log-time {
            color: var(--neutral-color);
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }
        
        .log-success {
            color: var(--success-color);
        }
        
        .log-error {
            color: var(--danger-color);
        }
        
        .log-info {
            color: var(--primary-color);
        }
        
        .sync-progress {
            height: 12px;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .blink {
            animation: blink-animation 1s steps(2, start) infinite;
        }
        
        @keyframes blink-animation {
            to {
                visibility: hidden;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="mb-0">Admin Dashboard</h1>
            <div>
                <span class="me-3"><?php echo $_SESSION['name']; ?></span>
                <a href="../logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="sidebar">
                    <h5 class="mb-3">Menu</h5>
                    <div class="nav flex-column">
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="sync_coverage.php" class="nav-link active">
                            <i class="fas fa-sync"></i> Sync Coverage
                        </a>
                        <a href="manage_users.php" class="nav-link">
                            <i class="fas fa-users"></i> Kelola Pengguna
                        </a>
                        <a href="../index.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Quality Gates
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mb-4">Sinkronisasi Data Coverage</h2>
                    
                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card card-stat h-100">
                                <div class="stat-icon">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="stat-value"><?php echo number_format($totalProjects); ?></div>
                                <div class="stat-label">Total Projects</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-stat h-100">
                                <div class="stat-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div class="stat-value"><?php echo number_format($totalCoverages); ?></div>
                                <div class="stat-label">Total Coverages</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sync Form -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Sinkronisasi Data</h5>
                            <p class="card-text">
                                Proses ini akan mengambil data proyek dan cakupan dari API BPS dan menyimpannya di database lokal.
                                Proses ini akan mengambil data untuk tahun 2023, 2024, dan 2025. Proses ini dapat memakan waktu beberapa menit.
                            </p>
                            
                            <form method="post" action="" id="syncForm">
                                <input type="hidden" name="sync" value="1">
                                <button type="submit" class="btn btn-primary" id="syncButton">
                                    <i class="fas fa-sync me-2"></i> Mulai Sinkronisasi
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Progress Section -->
                    <div class="card mb-4" id="progressSection" style="display: none;">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Progress Sinkronisasi</h5>
                            
                            <div class="progress sync-progress mb-3">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <span class="fw-bold me-2">Status:</span>
                                    <span id="syncStatus" class="badge bg-info">Berjalan</span>
                                </div>
                                <div>
                                    <span class="fw-bold me-2">Waktu:</span>
                                    <span id="syncTime">00:00:00</span>
                                </div>
                            </div>
                            
                            <h6 class="mb-2">Log Aktivitas:</h6>
                            <div class="live-log" id="liveLog">
                                <div class="live-log-entry">
                                    <span class="log-time">[<?php echo date('H:i:s'); ?>]</span>
                                    <span class="log-info">Menunggu proses sinkronisasi dimulai...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Sync Logs -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Riwayat Sinkronisasi</h5>
                            
                            <?php if (empty($syncLogs)): ?>
                            <p class="text-muted">Belum ada riwayat sinkronisasi</p>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Waktu</th>
                                            <th>Tipe</th>
                                            <th>Status</th>
                                            <th>Pesan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($syncLogs as $log): ?>
                                        <tr>
                                            <td><?php echo date('d M Y H:i:s', strtotime($log['created_at'])); ?></td>
                                            <td><?php echo $log['sync_type']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $log['status'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $log['status'] ? 'Sukses' : 'Gagal'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo $log['message']; ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let syncStartTime;
            let syncLogId;
            let syncInterval;
            let isSync = false;
            
            // Start timer function
            function startTimer() {
                syncStartTime = new Date().getTime();
                syncInterval = setInterval(updateTimer, 1000);
            }
            
            // Update timer display
            function updateTimer() {
                const now = new Date().getTime();
                const timeDiff = now - syncStartTime;
                
                const hours = Math.floor(timeDiff / (1000 * 60 * 60));
                const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                
                const timeString = 
                    (hours < 10 ? "0" + hours : hours) + ":" + 
                    (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                    (seconds < 10 ? "0" + seconds : seconds);
                
                $("#syncTime").text(timeString);
            }
            
            // Add log entry function
            function addLogEntry(message, type = 'info') {
                const now = new Date();
                const timeString = 
                    (now.getHours() < 10 ? "0" + now.getHours() : now.getHours()) + ":" + 
                    (now.getMinutes() < 10 ? "0" + now.getMinutes() : now.getMinutes()) + ":" + 
                    (now.getSeconds() < 10 ? "0" + now.getSeconds() : now.getSeconds());
                
                const logClass = type === 'success' ? 'log-success' : 
                                 type === 'error' ? 'log-error' : 'log-info';
                
                const logEntry = 
                    `<div class="live-log-entry">
                        <span class="log-time">[${timeString}]</span>
                        <span class="${logClass}">${message}</span>
                    </div>`;
                
                $("#liveLog").append(logEntry);
                
                // Scroll to bottom
                const liveLog = document.getElementById('liveLog');
                liveLog.scrollTop = liveLog.scrollHeight;
            }
            
            // Check sync status function
            function checkSyncStatus() {
                if (!syncLogId) return;
                
                $.ajax({
                    url: 'sync_coverage.php',
                    type: 'POST',
                    data: {
                        check_status: true,
                        log_id: syncLogId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response) {
                            // Update the log with the latest message if it's new
                            const existingMessages = [];
                            $('#liveLog .live-log-entry').each(function() {
                                existingMessages.push($(this).text().trim());
                            });
                            
                            // Check if this message is already displayed
                            const messageWithTime = `[${response.time}]${response.message}`;
                            if (!existingMessages.includes(messageWithTime)) {
                                addLogEntry(response.message, response.status ? 'success' : 'error');
                            }
                            
                            // Check updated_at time difference from created_at
                            // to detect if sync is still running
                            const createdAt = new Date(response.created_at).getTime();
                            const updatedAt = response.updated_at ? new Date(response.updated_at).getTime() : createdAt;
                            const now = new Date().getTime();
                            
                            // If no update in last 30 seconds, assume sync is completed or failed
                            if (now - updatedAt > 30000 && isSync) {
                                finishSync(response.status, response.message);
                            }
                        }
                    },
                    error: function() {
                        addLogEntry("Error checking status", 'error');
                    }
                });
            }
            
            // Finish sync function
            function finishSync(status, message) {
                isSync = false;
                clearInterval(syncInterval);
                
                // Update UI
                $("#syncButton").html('<i class="fas fa-sync me-2"></i> Mulai Sinkronisasi');
                $("#syncButton").prop('disabled', false);
                
                $("#syncStatus").removeClass('bg-info').addClass(status ? 'bg-success' : 'bg-danger');
                $("#syncStatus").text(status ? 'Selesai' : 'Gagal');
                
                $(".progress-bar").removeClass('progress-bar-animated');
                $(".progress-bar").css('width', '100%');
                
                // Add final log message
                addLogEntry("Sinkronisasi " + (status ? "selesai" : "gagal") + ": " + message, status ? 'success' : 'error');
                
                // Reload page after 3 seconds to show updated logs
                setTimeout(function() {
                    location.reload();
                }, 3000);
            }
            
            // Handle form submission
            $("#syncForm").on('submit', function(e) {
                e.preventDefault();
                
                if (isSync) return false;
                
                isSync = true;
                
                // Show progress section
                $("#progressSection").show();
                
                // Update button
                $("#syncButton").html('<i class="fas fa-sync fa-spin me-2"></i> Sinkronisasi Berjalan...');
                $("#syncButton").prop('disabled', true);
                
                // Clear log and add initial entry
                $("#liveLog").empty();
                addLogEntry("Memulai proses sinkronisasi...");
                
                // Start timer
                startTimer();
                
                // Submit form via AJAX
                $.ajax({
                    url: 'sync_coverage.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.log_id) {
                            syncLogId = response.log_id;
                            
                            // Start polling for status updates
                            setInterval(checkSyncStatus, 2000);
                        }
                    },
                    error: function() {
                        addLogEntry("Error starting sync process", 'error');
                        finishSync(false, "Error connecting to server");
                    }
                });
                
                return false;
            });
        });
    </script>
</body>
</html> 