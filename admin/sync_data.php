<?php
require_once '../config.php';

// Check if user is authenticated and has superadmin privileges
if (!isAuthenticated() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get available years (current year and 5 years back)
$currentYear = date('Y');
$years = [];
for ($i = 0; $i <= 5; $i++) {
    $years[] = $currentYear - $i;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Synchronize Data - QGate Monitoring</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-light: #4CAF50;
            --primary-dark: #1B5E20;
            --success-color: #43A047;
            --warning-color: #FFC107;
            --danger-color: #D32F2F;
            --neutral-color: #757575;
            --light-color: #F5F5F5;
            --dark-color: #212121;
            --border-color: #E0E0E0;
            --bg-color: #FAFAFA;
            --card-bg: #FFFFFF;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--bg-color);
            color: var(--dark-color);
            line-height: 1.5;
        }
        
        .main-title {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 1.75rem;
            margin: 0;
            display: flex;
            align-items: center;
        }
        
        .main-title i {
            color: var(--primary-color);
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }
        
        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: var(--light-color);
            border-bottom: 1px solid var(--border-color);
            padding: 0.75rem 1.25rem;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .progress {
            height: 1.5rem;
            margin: 1rem 0;
        }
        
        .sync-log {
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.75rem;
            font-family: monospace;
            font-size: 0.875rem;
        }
        
        .sync-log .log-entry {
            margin-bottom: 0.25rem;
            padding: 0.25rem 0;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .sync-log .log-entry:last-child {
            border-bottom: none;
        }
        
        .sync-log .log-time {
            color: #6c757d;
            margin-right: 0.5rem;
        }
        
        .sync-log .log-info {
            color: #0d6efd;
        }
        
        .sync-log .log-success {
            color: #198754;
        }
        
        .sync-log .log-warning {
            color: #ffc107;
        }
        
        .sync-log .log-error {
            color: #dc3545;
        }
        
        #lastSyncInfo {
            background-color: #e9f5e9;
            padding: 0.75rem;
            border-radius: 0.25rem;
            margin-bottom: 1rem;
            border-left: 4px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="main-title">
                        <i class="fas fa-sync-alt"></i> Synchronize Data
                    </h1>
                    <div>
                        <a href="../monitoring.php" class="btn btn-outline-primary">
                            <i class="fas fa-chart-line"></i> Monitoring
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-cog me-2"></i> Synchronization Settings
                    </div>
                    <div class="card-body">
                        <div id="lastSyncInfo" style="display: none;">
                            <h6><i class="fas fa-info-circle me-2"></i> Last Synchronization:</h6>
                            <div id="lastSyncDetails"></div>
                        </div>
                        
                        <form id="syncForm">
                            <div class="mb-3">
                                <label class="form-label">Select Year(s):</label>
                                <select class="form-select" id="yearsSelect" multiple required>
                                    <?php foreach ($years as $year): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Hold Ctrl or Cmd to select multiple years</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Select Project(s):</label>
                                <select class="form-select" id="projectsSelect" multiple disabled>
                                    <option value="">-- First select year(s) --</option>
                                </select>
                                <div class="form-text">Optional - If none selected, all projects will be synchronized</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Select Region(s):</label>
                                <select class="form-select" id="regionsSelect" multiple disabled>
                                    <option value="">-- First select project(s) --</option>
                                </select>
                                <div class="form-text">Optional - If none selected, all regions will be synchronized</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="startSyncBtn">
                                    <i class="fas fa-sync me-2"></i> Start Synchronization
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-7">
                <div class="card" id="syncProgressCard" style="display: none;">
                    <div class="card-header">
                        <i class="fas fa-tasks me-2"></i> Synchronization Progress
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span id="progressText">Initializing...</span>
                            <span id="progressPercent">0%</span>
                        </div>
                        
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 id="progressBar" role="progressbar" style="width: 0%;" 
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <span class="badge bg-primary me-2">Projects: <span id="projectsCount">0</span></span>
                                <span class="badge bg-info me-2">Regions: <span id="regionsCount">0</span></span>
                            </div>
                            <button class="btn btn-danger" id="cancelSyncBtn">
                                <i class="fas fa-stop-circle me-2"></i> Cancel
                            </button>
                        </div>
                        
                        <h6 class="mt-4 mb-2">Sync Log:</h6>
                        <div class="sync-log" id="syncLog">
                            <div class="log-entry">
                                <span class="log-time">[<?php echo date('H:i:s'); ?>]</span>
                                <span class="log-info">Waiting to start synchronization...</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card" id="syncCompletedCard" style="display: none;">
                    <div class="card-header">
                        <i class="fas fa-check-circle me-2"></i> Synchronization Complete
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle me-2"></i> Synchronization Completed Successfully!</h5>
                            <p>The data has been successfully synchronized from the API to the local database.</p>
                            <hr>
                            <div id="syncSummary"></div>
                        </div>
                        
                        <div class="d-grid">
                            <a href="../monitoring.php" class="btn btn-primary">
                                <i class="fas fa-chart-line me-2"></i> Go to Monitoring Page
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            let syncId = null;
            let syncInterval = null;
            let isSync = false;
            
            // Load last sync info on page load
            getLastSyncInfo();
            
            // Helper function to extract JSON from response
            const extractJson = response => {
                const start = response.indexOf('{');
                const end = response.lastIndexOf('}');
                return (start !== -1 && end !== -1 && end > start)
                  ? response.substring(start, end + 1)
                  : response;
            };
            
            // Year selection change event
            $('#yearsSelect').on('change', function() {
                const years = $(this).val();
                if (years && years.length > 0) {
                    loadProjects(years[0]); // Load projects for the first selected year
                    $('#projectsSelect').prop('disabled', false);
                } else {
                    $('#projectsSelect').html('<option value="">-- First select year(s) --</option>');
                    $('#projectsSelect').prop('disabled', true);
                    $('#regionsSelect').html('<option value="">-- First select project(s) --</option>');
                    $('#regionsSelect').prop('disabled', true);
                }
            });
            
            // Project selection change event
            $('#projectsSelect').on('change', function() {
                const projects = $(this).val();
                const years = $('#yearsSelect').val();
                if (projects && projects.length > 0 && years && years.length > 0) {
                    loadRegions(projects[0], years[0]); // Load regions for the first selected project
                    $('#regionsSelect').prop('disabled', false);
                } else {
                    $('#regionsSelect').html('<option value="">-- First select project(s) --</option>');
                    $('#regionsSelect').prop('disabled', true);
                }
            });
            
            // Region selection change event
            $('#regionsSelect').on('change', function() {
                const regions = $(this).val();
                // We don't need to load activities anymore, just validate the form
            });
            
            // Function to load regions for a project
            function loadRegions(projectId, year) {
                $.ajax({
                    url: '../api_local.php',
                    type: 'POST',
                    data: {
                        action: 'fetchCoverages',
                        id_project: projectId,
                        year: year
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            
                            if (jsonData.status && jsonData.data && jsonData.data.length > 0) {
                                let options = '';
                                jsonData.data.forEach(function(region) {
                                    const regionId = region.prov + '|' + region.kab;
                                    options += '<option value="' + regionId + '">' + region.name + '</option>';
                                });
                                $('#regionsSelect').html(options);
                            } else {
                                $('#regionsSelect').html('<option value="">No regions found</option>');
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            $('#regionsSelect').html('<option value="">Error parsing response</option>');
                        }
                    },
                    error: function() {
                        $('#regionsSelect').html('<option value="">Error loading regions</option>');
                    }
                });
            }
            
            // Start synchronization
            $('#syncForm').on('submit', function(e) {
                e.preventDefault();
                
                const years = $('#yearsSelect').val();
                if (!years || years.length === 0) {
                    alert('Please select at least one year to synchronize.');
                    return;
                }
                
                // Get selected options
                const projects = $('#projectsSelect').val() || [];
                const regions = $('#regionsSelect').val() || [];
                
                // Start synchronization
                startSynchronization(years, projects, regions);
            });
            
            // Cancel synchronization
            $('#cancelSyncBtn').on('click', function() {
                if (confirm('Are you sure you want to cancel the synchronization? This will stop the process and mark it as incomplete.')) {
                    cancelSynchronization();
                }
            });
            
            // Function to get the last sync info
            function getLastSyncInfo() {
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'get_last_sync'
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status && jsonData.has_data) {
                                $('#lastSyncInfo').show();
                                
                                const syncInfo = jsonData.sync_info;
                                const stats = jsonData.stats;
                                
                                let html = '<p>Synchronized on: <strong>' + syncInfo.sync_end + '</strong> by <strong>' + syncInfo.sync_by_name + '</strong></p>';
                                html += '<p>Data: <strong>' + stats.projects_count + '</strong> projects, <strong>' + stats.regions_count + '</strong> regions</p>';
                                
                                $('#lastSyncDetails').html(html);
                            } else {
                                $('#lastSyncInfo').hide();
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            $('#lastSyncInfo').hide();
                        }
                    },
                    error: function() {
                        $('#lastSyncInfo').hide();
                    }
                });
            }
            
            // Function to load projects for a year
            function loadProjects(year) {
                $.ajax({
                    url: '../api_local.php',
                    type: 'POST',
                    data: {
                        action: 'fetchProjects',
                        year: year
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            
                            if (jsonData.status && jsonData.data && jsonData.data.length > 0) {
                                let options = '';
                                jsonData.data.forEach(function(project) {
                                    options += '<option value="' + project.id + '">' + project.name + '</option>';
                                });
                                $('#projectsSelect').html(options);
                            } else {
                                $('#projectsSelect').html('<option value="">No projects found</option>');
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            $('#projectsSelect').html('<option value="">Error parsing response</option>');
                        }
                    },
                    error: function() {
                        $('#projectsSelect').html('<option value="">Error loading projects</option>');
                    }
                });
            }
            
            // Function to start the synchronization process
            function startSynchronization(years, projects, regions) {
                // Disable form elements
                $('#syncForm :input').prop('disabled', true);
                $('#startSyncBtn').html('<i class="fas fa-spinner fa-spin me-2"></i> Starting...');
                
                // Show progress card
                $('#syncProgressCard').show();
                $('#syncCompletedCard').hide();
                
                // Clear log
                $('#syncLog').html('<div class="log-entry"><span class="log-time">[' + getCurrentTime() + ']</span><span class="log-info">Starting synchronization...</span></div>');
                
                // Initialize progress
                updateProgress(0, 'Initializing synchronization...');
                $('#projectsCount').text('0');
                $('#regionsCount').text('0');
                
                // Start the synchronization
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'start_sync',
                        years: JSON.stringify(years),
                        projects: JSON.stringify(projects),
                        regions: JSON.stringify(regions)
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                addLogEntry('Synchronization started with ID: ' + jsonData.sync_id, 'info');
                                syncId = jsonData.sync_id;
                                isSync = true;
                                
                                // Start polling for progress updates
                                syncInterval = setInterval(function() {
                                    getProgress();
                                }, 2000);
                                
                                // Start processing the sync queue
                                processQueue(years);
                            } else {
                                addLogEntry('Error starting synchronization: ' + jsonData.message, 'error');
                                resetSyncForm();
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing response', 'error');
                            resetSyncForm();
                        }
                    },
                    error: function() {
                        addLogEntry('Error starting synchronization. Please try again.', 'error');
                        resetSyncForm();
                    }
                });
            }
            
            // Function to show completion message
            function showCompletionMessage() {
                // Don't hide progress card immediately
                $('#syncProgressCard').show();
                
                // Create completion card but don't auto-hide progress
                $('#syncCompletedCard').show();
                
                // Set summary details
                const projects = $('#projectsCount').text();
                const regions = $('#regionsCount').text();
                const summary = '<p>Successfully synchronized <strong>' + projects + '</strong> projects, <strong>' + 
                               regions + '</strong> regions.</p>' +
                               '<p>Synchronized at: <strong>' + getCurrentTime() + '</strong></p>' +
                               '<p><strong>Note:</strong> You can still view the logs above. The progress card will remain visible until you navigate away.</p>';
                
                $('#syncSummary').html(summary);
                
                // Add button to view logs instead of auto-hiding
                $('#syncCompletedCard .card-body').append('<div class="mt-3"><button class="btn btn-secondary" id="viewLogsBtn">View Detailed Logs</button> <a href="../monitoring.php" class="btn btn-primary">Go to Monitoring</a></div>');
                
                // Toggle between logs and summary
                $('#viewLogsBtn').on('click', function() {
                    if ($('#syncProgressCard').is(':visible')) {
                        $('#syncProgressCard').hide();
                        $(this).text('View Detailed Logs');
                    } else {
                        $('#syncProgressCard').show();
                        $(this).text('Hide Logs');
                    }
                });
                
                // Reset form but don't hide cards
                resetSyncForm(false);
            }
            
            // Add a delay between API calls to allow seeing progress
            function delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
            
            // Function to process the sync queue
            async function processQueue(years) {
                if (!isSync) return;
                
                // First sync all projects for each year
                await syncProjects(years, 0);
            }
            
            // Function to sync projects for a specific year
            async function syncProjects(years, index) {
                if (!isSync || index >= years.length) {
                    // We've processed all years, check progress
                    getProgress();
                    return;
                }
                
                const year = years[index];
                addLogEntry('Starting synchronization for year: ' + year, 'info');
                
                // Add small delay to make logs visible
                await delay(500);
                
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'sync_projects',
                        sync_id: syncId,
                        year: year
                    },
                    dataType: 'text',
                    success: async function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                addLogEntry(jsonData.message, 'success');
                                
                                // Add small delay to make logs visible
                                await delay(1000);
                                
                                // Projects synced, now sync coverages for each project
                                if (jsonData.projects && jsonData.projects.length > 0) {
                                    await syncCoverages(year, jsonData.projects, 0);
                                } else {
                                    // No projects, move to next year
                                    await syncProjects(years, index + 1);
                                }
                            } else {
                                addLogEntry('Error syncing projects for year ' + year + ': ' + jsonData.message, 'error');
                                // Continue with next year despite error
                                await delay(2000); // Longer delay for errors
                                await syncProjects(years, index + 1);
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing response for year ' + year, 'error');
                            // Continue with next year despite error
                            await delay(2000); // Longer delay for errors
                            await syncProjects(years, index + 1);
                        }
                    },
                    error: async function() {
                        addLogEntry('Error syncing projects for year ' + year, 'error');
                        // Continue with next year despite error
                        await delay(2000); // Longer delay for errors
                        await syncProjects(years, index + 1);
                    }
                });
            }
            
            // Function to sync coverages for projects
            async function syncCoverages(year, projects, index) {
                if (!isSync || index >= projects.length) {
                    // We've processed all projects for this year, move to next year
                    await syncProjects([year], 1); // This will move to the next year
                    return;
                }
                
                const project = projects[index];
                addLogEntry('Syncing regions for project: ' + project.name, 'info');
                
                // Add small delay to make logs visible
                await delay(500);
                
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'sync_coverages',
                        sync_id: syncId,
                        project_id: project.id,
                        year: year
                    },
                    dataType: 'text',
                    success: async function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                addLogEntry(jsonData.message, 'success');
                                
                                // Add small delay to make logs visible
                                await delay(1000);
                                
                                // Coverages synced, now sync activities for each coverage
                                if (jsonData.coverages && jsonData.coverages.length > 0) {
                                    await syncActivitiesForCoverages(year, project.id, jsonData.coverages, 0);
                                } else {
                                    // No coverages, move to next project
                                    await syncCoverages(year, projects, index + 1);
                                }
                            } else {
                                addLogEntry('Error syncing regions for project ' + project.id + ': ' + jsonData.message, 'error');
                                // Continue with next project despite error
                                await delay(2000); // Longer delay for errors
                                await syncCoverages(year, projects, index + 1);
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing response for project ' + project.id, 'error');
                            // Continue with next project despite error
                            await delay(2000); // Longer delay for errors
                            await syncCoverages(year, projects, index + 1);
                        }
                    },
                    error: async function() {
                        addLogEntry('Error syncing regions for project ' + project.id, 'error');
                        // Continue with next project despite error
                        await delay(2000); // Longer delay for errors
                        await syncCoverages(year, projects, index + 1);
                    }
                });
            }
            
            // Function to sync activities for coverages
            async function syncActivitiesForCoverages(year, projectId, coverages, index) {
                if (!isSync || index >= coverages.length) {
                    // We've processed all coverages for this project, move to next project
                    await syncCoverages(year, [{id: projectId}], 1); // This will move to the next project
                    return;
                }
                
                const coverage = coverages[index];
                addLogEntry('Syncing activities for region: ' + coverage.name, 'info');
                
                // Add small delay to make logs visible
                await delay(500);
                
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'sync_activities',
                        sync_id: syncId,
                        project_id: projectId,
                        year: year,
                        prov: coverage.prov,
                        kab: coverage.kab
                    },
                    dataType: 'text',
                    success: async function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                addLogEntry(jsonData.message, 'success');
                                
                                // Add small delay between regions
                                await delay(1000);
                                
                                if (jsonData.all_complete) {
                                    // All activities are synced and sync is completed
                                    clearInterval(syncInterval);
                                    isSync = false;
                                    
                                    getProgress(function() {
                                        showCompletionMessage();
                                    });
                                } else {
                                    // Move to next coverage
                                    await syncActivitiesForCoverages(year, projectId, coverages, index + 1);
                                }
                            } else {
                                addLogEntry('Error syncing activities for region ' + coverage.name + ': ' + jsonData.message, 'error');
                                // Continue with next coverage despite error
                                await delay(2000); // Longer delay for errors
                                await syncActivitiesForCoverages(year, projectId, coverages, index + 1);
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing response for region ' + coverage.name, 'error');
                            // Continue with next coverage despite error
                            await delay(2000); // Longer delay for errors
                            await syncActivitiesForCoverages(year, projectId, coverages, index + 1);
                        }
                    },
                    error: async function() {
                        addLogEntry('Error syncing activities for region ' + coverage.name, 'error');
                        // Continue with next coverage despite error
                        await delay(2000); // Longer delay for errors
                        await syncActivitiesForCoverages(year, projectId, coverages, index + 1);
                    }
                });
            }
            
            // Function to get progress updates
            function getProgress(callback) {
                if (!syncId) return;
                
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'sync_progress',
                        sync_id: syncId
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                const data = jsonData.data;
                                
                                // Update progress bar
                                updateProgress(data.percent_complete, 'Synchronizing data... ' + data.processed_items + ' / ' + data.total_items + ' items');
                                
                                // Update counters
                                $('#projectsCount').text(data.sync_status.projects_synced);
                                $('#regionsCount').text(data.sync_status.regions_synced);
                                
                                // Update logs
                                updateLogs(data.logs);
                                
                                // Check if sync is complete
                                if (data.is_complete) {
                                    clearInterval(syncInterval);
                                    isSync = false;
                                    showCompletionMessage();
                                }
                                
                                // Call the callback if provided
                                if (typeof callback === 'function') {
                                    callback();
                                }
                            } else {
                                addLogEntry('Error getting progress: ' + jsonData.message, 'error');
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing progress response', 'error');
                        }
                    },
                    error: function() {
                        addLogEntry('Error getting progress updates', 'error');
                    }
                });
            }
            
            // Function to cancel the synchronization
            function cancelSynchronization() {
                if (!syncId) return;
                
                $.ajax({
                    url: '../api_sync.php',
                    type: 'POST',
                    data: {
                        action: 'cancel_sync',
                        sync_id: syncId
                    },
                    dataType: 'text',
                    success: function(response) {
                        try {
                            const jsonData = JSON.parse(extractJson(response));
                            if (jsonData.status) {
                                addLogEntry('Synchronization canceled by user', 'warning');
                                clearInterval(syncInterval);
                                isSync = false;
                                
                                // Reset form after a delay
                                setTimeout(function() {
                                    resetSyncForm();
                                }, 3000);
                            } else {
                                addLogEntry('Error canceling synchronization: ' + jsonData.message, 'error');
                            }
                        } catch (error) {
                            console.error("JSON parsing error:", error);
                            addLogEntry('Error parsing cancellation response', 'error');
                        }
                    },
                    error: function() {
                        addLogEntry('Error canceling synchronization', 'error');
                    }
                });
            }
            
            // Function to update progress bar and text
            function updateProgress(percent, message) {
                $('#progressBar').css('width', percent + '%');
                $('#progressBar').attr('aria-valuenow', percent);
                $('#progressBar').text(percent + '%');
                $('#progressPercent').text(percent + '%');
                $('#progressText').text(message);
            }
            
            // Function to add a log entry
            function addLogEntry(message, type) {
                const time = getCurrentTime();
                const entry = '<div class="log-entry"><span class="log-time">[' + time + ']</span><span class="log-' + type + '">' + message + '</span></div>';
                $('#syncLog').prepend(entry);
            }
            
            // Function to update logs from API response
            function updateLogs(logs) {
                if (!logs || logs.length === 0) return;
                
                $('#syncLog').empty();
                logs.forEach(function(log) {
                    const time = new Date(log.log_time).toLocaleTimeString();
                    const entry = '<div class="log-entry"><span class="log-time">[' + time + ']</span><span class="log-' + log.log_type + '">' + log.log_message + '</span></div>';
                    $('#syncLog').prepend(entry);
                });
            }
            
            // Function to get current time as string
            function getCurrentTime() {
                return new Date().toLocaleTimeString();
            }
            
            // Function to reset the sync form
            function resetSyncForm(showForm = true) {
                // Clear sync ID and interval
                syncId = null;
                clearInterval(syncInterval);
                isSync = false;
                
                // Enable form elements
                $('#syncForm :input').prop('disabled', false);
                $('#startSyncBtn').html('<i class="fas fa-sync me-2"></i> Start Synchronization');
                
                // Reset selects to initial state
                $('#projectsSelect').html('<option value="">-- First select year(s) --</option>');
                $('#projectsSelect').prop('disabled', true);
                $('#regionsSelect').html('<option value="">-- First select project(s) --</option>');
                $('#regionsSelect').prop('disabled', true);
                
                // Hide progress card if needed
                if (showForm) {
                    $('#syncProgressCard').hide();
                    $('#syncCompletedCard').hide();
                }
                
                // Refresh last sync info
                getLastSyncInfo();
            }
        });
    </script>
</body>
</html> 