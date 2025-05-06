<?php
// download.php - For downloading data from external API

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

// AJAX endpoint for database operations
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
    
    if ($db_action === 'save_processed_data') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        $data_json = isset($_POST['data_json']) ? $_POST['data_json'] : '';
        
        $result = saveProcessedData($conn, $year, $project_id, $region_id, json_decode($data_json, true));
        echo json_encode(['status' => $result ? true : false]);
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
    
    if ($db_action === 'save_data') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        $data_type = isset($_POST['data_type']) ? $_POST['data_type'] : '';
        $cache_key = isset($_POST['cache_key']) ? $_POST['cache_key'] : '';
        $data_json = isset($_POST['data_json']) ? $_POST['data_json'] : '';
        
        $result = saveToLocalDB($conn, $year, $project_id, $region_id, $data_type, $cache_key, $data_json);
        echo json_encode(['status' => $result ? true : false]);
        exit;
    }
    
    if ($db_action === 'clear_cache') {
        $year = isset($_POST['year']) ? $_POST['year'] : '';
        $project_id = isset($_POST['project_id']) ? $_POST['project_id'] : '';
        $region_id = isset($_POST['region_id']) ? $_POST['region_id'] : 'all';
        
        // Skip if missing required parameters
        if (empty($year) || empty($project_id)) {
            echo json_encode(['status' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        $year = $conn->real_escape_string($year);
        $project_id = $conn->real_escape_string($project_id);
        $region_id = $conn->real_escape_string($region_id);
        
        $sql = "DELETE FROM qg_sync WHERE year = '$year' AND project_id = '$project_id' AND region_id = '$region_id'";
        $result = $conn->query($sql);
        
        echo json_encode(['status' => $result ? true : false]);
        exit;
    }
    
    if ($db_action === 'get_download_logs') {
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $logs = getDownloadLogs($conn, $limit);
        echo json_encode(['status' => true, 'logs' => $logs]);
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
  <title>Download Quality Gates Data</title>
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
    
    /* Download logs table */
    .table {
      border-collapse: separate;
      border-spacing: 0;
      width: 100%;
    }
    
    .table th {
      background-color: #f5f5f7;
      font-weight: 500;
      padding: 1rem;
      font-size: 0.9rem;
    }
    
    .table td {
      padding: 1rem;
      vertical-align: middle;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    .progress {
      height: 10px;
      border-radius: 5px;
    }
    
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
    
    /* Responsif */
    @media (max-width: 992px) {
      .container-fluid {
        padding: 1.5rem;
      }
      
      .card-body {
        padding: 1.25rem;
      }
    }
    
    @media (max-width: 768px) {
      .container-fluid {
        padding: 1rem;
      }
      
      h1 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
      }
      
      .card-header {
        padding: 1rem;
      }
      
      .card-body {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <h1><i class="fas fa-download me-3"></i>Download Quality Gates Data</h1>
    
    <!-- Input Filters -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Download Data</span>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-2">
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
          <div class="col-md-2 d-flex align-items-end">
            <button id="downloadData" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
              <i class="fas fa-download me-2"></i>Download Data
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Download Logs -->
    <div class="card">
      <div class="card-header">
        <span>Download History</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Tahun</th>
                <th>Kegiatan</th>
                <th>Wilayah</th>
                <th>Status</th>
                <th>Progress</th>
              </tr>
            </thead>
            <tbody id="downloadLogs">
              <tr>
                <td colspan="6" class="text-center">Tidak ada data download</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Spinner Loading -->
  <div id="spinner">
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
      <div class="spinner-text">Downloading data...</div>
      <div id="progressInfo" class="mt-2">0%</div>
    </div>
  </div>

  <script>
    $(function(){
      const API_URL = "api.php";
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      
      // Cache selector DOM
      const $yearSelect    = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect  = $("#regionSelect");
      const $downloadLogs  = $("#downloadLogs");
      const $spinner       = $("#spinner");
      const $progressInfo  = $("#progressInfo");
      const $downloadData  = $("#downloadData");

      // --- Helper Functions ---
      const extractJson = response => {
        const start = response.indexOf('{');
        const end = response.lastIndexOf('}');
        return (start !== -1 && end !== -1 && end > start)
          ? response.substring(start, end + 1)
          : response;
      };

      const showError = message => {
        console.error(message);
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: message,
          confirmButtonColor: '#0071e3'
        });
      };

      const makeAjaxRequest = async (url, data, cacheData = true) => {
        return new Promise((resolve, reject) => {
          $.ajax({
            url,
            method: "POST",
            data,
            dataType: "text",
            cache: false,
            success: async response => {
              try {
                const jsonData = JSON.parse(extractJson(response));
                
                // Save to cache if successful and caching is enabled
                if (cacheData && jsonData.status && year) {
                  const cacheDataType = data.action;
                  const cacheKey = JSON.stringify(data);
                  
                  // For fetchProjects, we save with project_id 'all' since we don't have a selected project yet
                  const projectIdToUse = (cacheDataType === 'fetchProjects') ? 'all' : selectedProject;
                  
                  // Only proceed if we have a valid project ID (either 'all' for project list or an actual project ID)
                  if (projectIdToUse) {
                    // Special handling for fetchProjectSpesific to ensure project name is available
                    if (cacheDataType === 'fetchProjectSpesific' && jsonData.status && jsonData.data) {
                      // Save with the actual project ID
                      await $.ajax({
                        url: window.location.href,
                        method: "POST",
                        data: {
                          db_action: "save_data",
                          year: year,
                          project_id: data.project_id,
                          region_id: selectedRegion || 'all',
                          data_type: cacheDataType,
                          cache_key: cacheKey,
                          data_json: JSON.stringify(jsonData)
                        }
                      });
                    } else {
                      // Normal saving for other data types
                      await $.ajax({
                        url: window.location.href,
                        method: "POST",
                        data: {
                          db_action: "save_data",
                          year: year,
                          project_id: projectIdToUse,
                          region_id: selectedRegion || 'all',
                          data_type: cacheDataType,
                          cache_key: cacheKey,
                          data_json: JSON.stringify(jsonData)
                        }
                      });
                    }
                  }
                }
                
                resolve(jsonData);
              } catch(e) {
                reject("Terjadi kesalahan saat memproses data");
              }
            },
            error: () => reject("Terjadi kesalahan pada server")
          });
        });
      };

      // --- Fungsi untuk Load Data (Projects & Regions) ---
      const loadProjects = async () => {
        $projectSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          const response = await makeAjaxRequest(API_URL, { action: "fetchProjects", year });
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          if (response.status && response.data && response.data.length) {
            // Log raw response for debugging
            console.log("Raw API projects response:", JSON.stringify(response));
            console.log("Projects data structure:", response.data[0]);
            
            // DIRECTLY save the projects list exactly as received
            try {
              // First save without any transformation to preserve original structure
              const saveResponse = await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "save_data",
                  year: year,
                  project_id: 'all',
                  region_id: 'all',
                  data_type: 'fetchProjects',
                  cache_key: JSON.stringify({ action: "fetchProjects", year }),
                  data_json: JSON.stringify(response)
                },
                dataType: "json"
              });
              console.log("Original project list saved:", saveResponse);
            } catch (error) {
              console.error("Error saving original project list:", error);
            }
            
            // Add projects to dropdown
            response.data.forEach(project => {
              console.log(`Adding project to dropdown: ID=${project.id}, Name=${project.name}`);
              $projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
            });
            
            // Save each project details separately with names
            for (const project of response.data) {
              try {
                console.log(`Processing project: ${project.id} - ${project.name}`);
                
                // 1. CRITICAL: Save individual project data with the same structure as fetchProjects
                // This ensures project names are available in the same format for both list and individual lookups
                await $.ajax({
                  url: window.location.href,
                  method: "POST",
                  data: {
                    db_action: "save_data",
                    year: year,
                    project_id: project.id,
                    region_id: 'all',
                    data_type: 'fetchProjects',
                    cache_key: JSON.stringify({ action: "fetchProjects", year, projectId: project.id }),
                    data_json: JSON.stringify({
                      status: true,
                      data: [project] // Preserve array structure but with just this project
                    })
                  },
                  dataType: "json"
                });
                
                // 2. Get and save specific project details
                const projectResponse = await makeAjaxRequest(API_URL, { 
                  action: "fetchProjectSpesific", 
                  year: year,
                  project_id: project.id 
                });
                
                console.log(`Project ${project.id} specific response:`, projectResponse);
                
                // Ensure project name is included in both common locations
                if (projectResponse.status) {
                  // Create an enhanced response with guaranteed name fields
                  const enhancedResponse = {
                    status: true,
                    data: {
                      ...(projectResponse.data || {}),
                      // Ensure these name fields exist
                      name: project.name,
                      project_name: project.name
                    }
                  };
                  
                  console.log(`Enhanced project ${project.id} data:`, enhancedResponse);
                  
                  // Save the enhanced project data
                  await $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: {
                      db_action: "save_data",
                      year: year,
                      project_id: project.id,
                      region_id: 'all',
                      data_type: 'fetchProjectSpesific',
                      cache_key: JSON.stringify({ action: "fetchProjectSpesific", year, project_id: project.id }),
                      data_json: JSON.stringify(enhancedResponse)
                    },
                    dataType: "json"
                  });
                  
                  console.log(`Project ${project.id} details saved with name: ${project.name}`);
                }
              } catch (error) {
                console.error(`Error processing project ${project.id}:`, error);
              }
            }
          } else {
            console.warn("No projects returned from API:", response);
            $projectSelect.append('<option value="" disabled>Tidak ada kegiatan ditemukan</option>');
          }
        } catch (error) {
          console.error("Error in loadProjects:", error);
          showError("Gagal memuat daftar kegiatan");
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
        }
      };

      const loadRegions = async () => {
        $regionSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          const response = await makeAjaxRequest(API_URL, { action: "fetchCoverages", id_project: selectedProject });
          $regionSelect.empty();
          coverageData = [];
          if (!response.status)
            throw new Error("Gagal memuat data wilayah");
          
          // Tambahkan opsi Pusat
          $regionSelect.append(`<option value="pusat">Pusat - Nasional</option>`);
          coverageData.push({
            id: "pusat",
            prov: "00",
            kab: "00",
            name: "Pusat - Nasional"
          });
          
          if (response.data && response.data.length) {
            const apiData = response.data.map(cov => ({
              id: `${cov.prov}${cov.kab}`,
              prov: cov.prov,
              kab: cov.kab,
              name: cov.name
            }));
            coverageData = [...coverageData, ...apiData];
            
            // Filter provinsi (kab == "00" dan prov tidak "00")
            const provinces = coverageData.filter(cov => cov.kab === "00" && cov.prov !== "00");
            provinces.forEach(province => {
              $regionSelect.append(`<option value="${province.id}">${province.name}</option>`);
            });
            
            // Jika tidak ada provinsi, tampilkan semua kabupaten/kota
            if (provinces.length === 0) {
              // Filter kabupaten/kota (kab != "00")
              const kabupatens = coverageData.filter(cov => cov.kab !== "00");
              kabupatens.forEach(kabupaten => {
                $regionSelect.append(`<option value="${kabupaten.id}">${kabupaten.name}</option>`);
              });
            }
          }
          
          // Selalu pilih Pusat sebagai default jika ada
          if ($regionSelect.find('option[value="pusat"]').length > 0) {
            selectedRegion = "pusat";
            $regionSelect.val(selectedRegion);
          } else if ($regionSelect.find('option').length > 0) {
            // Jika tidak ada pusat, pilih opsi pertama yang tersedia
            selectedRegion = $regionSelect.find('option:first').val();
            $regionSelect.val(selectedRegion);
          }
          
          if (coverageData.length === 0)
            throw new Error("Tidak ada data wilayah yang tersedia");
        } catch (error) {
          showError(error.message || "Gagal memuat daftar wilayah");
          $regionSelect.empty().append('<option value="">Pilih Cakupan Wilayah</option>');
          coverageData = [];
        }
      };
      
      // Function to load download logs
      const loadDownloadLogs = async () => {
        try {
          const response = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "get_download_logs",
              limit: 10
            },
            dataType: "json"
          });
          
          if (response.status && response.logs.length > 0) {
            let html = "";
            response.logs.forEach(log => {
              const startTime = new Date(log.start_time).toLocaleString('id-ID');
              const status = log.status === 'completed' ? 
                '<span class="status-badge status-success">Completed</span>' : 
                (log.status === 'error' ? 
                  '<span class="status-badge status-danger">Error</span>' : 
                  '<span class="status-badge status-warning">In Progress</span>');
              
              const progressPercent = Math.round(log.progress);
              
              html += `
                <tr>
                  <td>${startTime}</td>
                  <td>${log.year}</td>
                  <td>${log.project_id}</td>
                  <td>${log.region_id}</td>
                  <td>${status}</td>
                  <td>
                    <div class="progress">
                      <div class="progress-bar ${log.status === 'error' ? 'bg-danger' : ''}" 
                           role="progressbar" 
                           style="width: ${progressPercent}%" 
                           aria-valuenow="${progressPercent}" 
                           aria-valuemin="0" 
                           aria-valuemax="100"></div>
                    </div>
                    <small class="d-block mt-1">${log.completed_items}/${log.total_items} items</small>
                  </td>
                </tr>
              `;
            });
            
            $downloadLogs.html(html);
          } else {
            $downloadLogs.html('<tr><td colspan="6" class="text-center">Tidak ada data download</td></tr>');
          }
        } catch (error) {
          console.error("Error loading download logs:", error);
          $downloadLogs.html('<tr><td colspan="6" class="text-center">Error loading download logs</td></tr>');
        }
      };
      
      // Download data function
      const downloadData = async () => {
        if (!year || !selectedProject || !selectedRegion) {
          showError("Silakan pilih tahun, kegiatan, dan cakupan wilayah terlebih dahulu");
          return;
        }
        
        $spinner.fadeIn(200);
        $progressInfo.text("0%");
        
        try {
          const regionData = coverageData.find(r => r.id === selectedRegion);
          if (!regionData)
            throw new Error("Data wilayah tidak ditemukan");
          
          // Start the download log
          const logStartResult = await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "log_download_action",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion,
              status: "started",
              total: 100 // Initial estimation
            },
            dataType: "json"
          });
          
          // Dapatkan project detail
          const projectDetail = await makeAjaxRequest(API_URL, {
            action: "fetchProjectSpesific", 
            year: year,
            project_id: selectedProject
          });
          
          if (!projectDetail.status) {
            throw new Error("Gagal memuat detail project");
          }
          
          // Dapatkan semua gates
          const gatesResponse = await makeAjaxRequest(API_URL, {
            action: "fetchGates",
            id_project: selectedProject
          });
          
          if (!gatesResponse.status) {
            throw new Error("Gagal memuat data gate");
          }
          
          const gates = gatesResponse.data;
          const totalSteps = gates.length * 6; // 6 API calls per gate on average
          let completedSteps = 0;
          
          // Update the download log with proper total
          await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "log_download_action",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion,
              status: "update",
              progress: 0,
              total: totalSteps,
              completed: 0
            }
          });
          
          // Get regions to process
          let regionsToProcess = [];
          
          if (selectedRegion === "pusat") {
            // Only process the center
            regionsToProcess = [{ id: "pusat", prov: "00", kab: "00", name: "Pusat" }];
          } else {
            const prov = regionData.prov;
            // Process the province and all kabupaten in it
            regionsToProcess = [
              { id: `${prov}00`, prov: prov, kab: "00", name: regionData.name }
            ];
            
            // Add kabupaten
            const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
            regionsToProcess = [...regionsToProcess, ...kabupatenList];
          }
          
          // Process each gate
          for (let i = 0; i < gates.length; i++) {
            const gate = gates[i];
            
            // For each region
            for (const region of regionsToProcess) {
              // Get measurements for this gate
              await makeAjaxRequest(API_URL, {
                action: "fetchMeasurements",
                id_project: selectedProject,
                id_gate: gate.id,
                prov: region.prov,
                kab: region.kab
              });
              
              completedSteps++;
              const progress = Math.round((completedSteps / totalSteps) * 100);
              $progressInfo.text(`${progress}%`);
              
              await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "log_download_action",
                  year: year,
                  project_id: selectedProject,
                  region_id: selectedRegion,
                  status: "update",
                  progress: progress,
                  total: totalSteps,
                  completed: completedSteps
                }
              });
              
              // Get assessments
              await makeAjaxRequest(API_URL, {
                action: "fetchAssessments",
                id_project: selectedProject,
                id_gate: gate.id,
                prov: region.prov,
                kab: region.kab
              });
              
              completedSteps++;
              const progressAfterAssessments = Math.round((completedSteps / totalSteps) * 100);
              $progressInfo.text(`${progressAfterAssessments}%`);
              
              await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "log_download_action",
                  year: year,
                  project_id: selectedProject,
                  region_id: selectedRegion,
                  status: "update",
                  progress: progressAfterAssessments,
                  total: totalSteps,
                  completed: completedSteps
                }
              });
              
              // Get all actions
              await makeAjaxRequest(API_URL, {
                action: "fetchAllActions",
                id_project: selectedProject,
                id_gate: gate.id,
                prov: region.prov,
                kab: region.kab
              });
              
              completedSteps++;
              const progressAfterActions = Math.round((completedSteps / totalSteps) * 100);
              $progressInfo.text(`${progressAfterActions}%`);
              
              await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "log_download_action",
                  year: year,
                  project_id: selectedProject,
                  region_id: selectedRegion,
                  status: "update",
                  progress: progressAfterActions,
                  total: totalSteps,
                  completed: completedSteps
                }
              });
            }
          }
          
          // Complete the download
          await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "log_download_action",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion,
              status: "completed",
              progress: 100,
              total: totalSteps,
              completed: totalSteps
            }
          });
          
          // Process the data for monitoring
          await processMonitoringData(gates, regionsToProcess);
          
          Swal.fire({
            icon: 'success',
            title: 'Download Selesai',
            text: 'Data berhasil didownload dan disimpan ke database lokal.',
            confirmButtonColor: '#0071e3'
          });
          
          // Refresh the download logs
          loadDownloadLogs();
          
        } catch (error) {
          const errorMessage = error.message || "Terjadi kesalahan saat download data";
          showError(errorMessage);
          
          // Log the error
          await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "log_download_action",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion,
              status: "error",
              error: errorMessage
            }
          });
          
          // Refresh the download logs
          loadDownloadLogs();
        } finally {
          $spinner.fadeOut(200);
        }
      };

      // Process monitoring data function 
      const processMonitoringData = async (gates, regions) => {
        try {
          // Initialize activityData object
          const activityData = {};
          
          // Cache for data API retrieval
          const apiCache = {
            measurements: {},
            preventives: {},
            preventivesKab: {},
            assessments: {},
            correctives: {},
            correctivesKab: {},
            allActions: {}
          };
          
          // Helper function to check if ukuran kualitas applies to a region
          const isUkApplicableForRegion = (measurement, region) => {
            const level = parseInt(measurement.assessment_level || 1);
            const isPusat = region.prov === "00" && region.kab === "00";
            if (isPusat) return level === 1;
            const isProvinsi = region.prov !== "00" && region.kab === "00";
            if (isProvinsi) return level === 2;
            return level === 3;
          };
          
          // Helper function to get UK level label
          const getUkLevelLabel = (measurement) => {
            const level = parseInt(measurement.assessment_level || 1);
            if (level === 1) return "Pusat";
            if (level === 2) return "Provinsi";
            if (level === 3) return "Kabupaten";
            return "Tidak diketahui";
          };
          
          // Helper function to determine activity status
          const determineActivityStatus = async (gate, measurement, activityName, prov, kab) => {
            // Get actions data from cache or database
            const actionsKey = JSON.stringify({ 
              action: 'fetchAllActions', 
              id_project: selectedProject, 
              id_gate: gate.id, 
              prov, kab 
            });
            
            let actionsResponse = null;
            
            // Try to get from database first
            const actionsDbResponse = await $.ajax({
              url: window.location.href,
              method: "POST",
              data: {
                db_action: "get_data",
                year: year,
                project_id: selectedProject,
                region_id: `${prov}${kab}`,
                data_type: 'fetchAllActions',
                cache_key: actionsKey
              },
              dataType: "json"
            });
            
            if (actionsDbResponse.found) {
              actionsResponse = actionsDbResponse.data;
            }
            
            // If no actions data, all status "Belum"
            if (!actionsResponse || !actionsResponse.status || !actionsResponse.data || actionsResponse.data.length === 0) {
              if (activityName === "Pengisian nama pelaksana aksi preventif") return "Belum ditentukan";
              if (activityName === "Upload bukti pelaksanaan aksi preventif") return "Belum ditentukan";
              if (activityName === "Penilaian ukuran kualitas") return "Belum dinilai";
              if (activityName === "Approval Gate oleh Sign-off") return "Belum dinilai";
              if (activityName === "Pengisian pelaksana aksi korektif") return "Belum disetujui";
              if (activityName === "Upload bukti pelaksanaan aksi korektif") return "Belum disetujui";
            }
            
            // Get assessments data
            const assessmentsKey = JSON.stringify({ 
              action: 'fetchAssessments', 
              id_project: selectedProject, 
              id_gate: gate.id, 
              prov, kab 
            });
            
            let assessmentsResponse = null;
            
            const assessmentsDbResponse = await $.ajax({
              url: window.location.href,
              method: "POST",
              data: {
                db_action: "get_data",
                year: year,
                project_id: selectedProject,
                region_id: `${prov}${kab}`,
                data_type: 'fetchAssessments',
                cache_key: assessmentsKey
              },
              dataType: "json"
            });
            
            if (assessmentsDbResponse.found) {
              assessmentsResponse = assessmentsDbResponse.data;
            }
            
            // Get preventives data if needed
            if (activityName.includes("preventif")) {
              const prevKey = JSON.stringify({ 
                action: 'fetchPreventivesByMeasurement', 
                year,
                id_project: selectedProject, 
                id_gate: gate.id,
                id_measurement: measurement.id, 
                prov, kab 
              });
              
              const prevDbResponse = await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "get_data",
                  year: year,
                  project_id: selectedProject,
                  region_id: `${prov}${kab}`,
                  data_type: 'fetchPreventivesByMeasurement',
                  cache_key: prevKey
                },
                dataType: "json"
              });
              
              if (prevDbResponse.found) {
                const prevData = prevDbResponse.data;
                
                if (activityName === "Pengisian nama pelaksana aksi preventif") {
                  return (prevData.status && prevData.data.length > 0) ? "Sudah ditentukan" : "Belum ditentukan";
                }
                
                if (activityName === "Upload bukti pelaksanaan aksi preventif") {
                  const isPrevNameFilled = prevData.status && prevData.data.length > 0;
                  
                  if (!isPrevNameFilled) {
                    return "Belum ditentukan";
                  }
                  
                  const prevKabKey = JSON.stringify({
                    data: {
                      year, 
                      id_project: selectedProject, 
                      id_gate: gate.id,
                      id_measurement: measurement.id, 
                      prov, kab
                    }
                  });
                  
                  const prevKabDbResponse = await $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: {
                      db_action: "get_data",
                      year: year,
                      project_id: selectedProject,
                      region_id: `${prov}${kab}`,
                      data_type: 'fetchPreventivesByKab',
                      cache_key: prevKabKey
                    },
                    dataType: "json"
                  });
                  
                  if (prevKabDbResponse.found) {
                    const prevKabData = prevKabDbResponse.data;
                    return (prevKabData.status && prevKabData.data.length > 0 && prevKabData.data[0].filename)
                      ? "Sudah diunggah" : "Belum diunggah";
                  }
                  
                  return "Belum diunggah";
                }
              }
              
              return activityName === "Pengisian nama pelaksana aksi preventif" ? "Belum ditentukan" : "Belum diunggah";
            }
            
            // Check if measurement has been assessed
            let assessmentStatus = "Belum dinilai";
            let isAssessed = false;
            let isApproved = false;
            
            if (assessmentsResponse && assessmentsResponse.status) {
              const measurementAssessment = assessmentsResponse.data.find(m => m.id == measurement.id);
              
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
            }
            
            // Assessment and approval status
            if (activityName === "Penilaian ukuran kualitas") {
              return assessmentStatus;
            }
            
            if (activityName === "Approval Gate oleh Sign-off") {
              if (!isAssessed) return "Belum dinilai";
              return isApproved ? "Sudah disetujui" : "Belum disetujui";
            }
            
            // Corrective actions
            if (activityName.includes("korektif")) {
              if (!isApproved) return "Belum disetujui";
              if (assessmentStatus === "Sudah dinilai (hijau)") return "Tidak perlu";
              
              const corKey = JSON.stringify({ 
                action: 'fetchCorrectivesByMeasurement', 
                year,
                id_project: selectedProject, 
                id_gate: gate.id,
                id_measurement: measurement.id, 
                prov, kab 
              });
              
              const corDbResponse = await $.ajax({
                url: window.location.href,
                method: "POST",
                data: {
                  db_action: "get_data",
                  year: year,
                  project_id: selectedProject,
                  region_id: `${prov}${kab}`,
                  data_type: 'fetchCorrectivesByMeasurement',
                  cache_key: corKey
                },
                dataType: "json"
              });
              
              if (corDbResponse.found) {
                const corData = corDbResponse.data;
                
                if (activityName === "Pengisian pelaksana aksi korektif") {
                  return (corData.status && corData.data.length > 0) ? "Sudah ditentukan" : "Belum ditentukan";
                }
                
                if (activityName === "Upload bukti pelaksanaan aksi korektif") {
                  if (!(corData.status && corData.data.length > 0)) {
                    return "Belum ditentukan";
                  }
                  
                  const corKabKey = JSON.stringify({
                    data: {
                      year, 
                      id_project: selectedProject, 
                      id_gate: gate.id,
                      id_measurement: measurement.id, 
                      prov, kab
                    }
                  });
                  
                  const corKabDbResponse = await $.ajax({
                    url: window.location.href,
                    method: "POST",
                    data: {
                      db_action: "get_data",
                      year: year,
                      project_id: selectedProject,
                      region_id: `${prov}${kab}`,
                      data_type: 'fetchCorrectivesByKab',
                      cache_key: corKabKey
                    },
                    dataType: "json"
                  });
                  
                  if (corKabDbResponse.found) {
                    const corKabData = corKabDbResponse.data;
                    return (corKabData.status && corKabData.data.length > 0 && corKabData.data[0].filename)
                      ? "Sudah diunggah" : "Belum diunggah";
                  }
                  
                  return "Belum diunggah";
                }
              }
              
              return activityName === "Pengisian pelaksana aksi korektif" ? "Belum ditentukan" : "Belum diunggah";
            }
            
            return "Tidak tersedia";
          };
          
          // Process each gate
          for (const gate of gates) {
            // Get measurements for this gate (from center)
            const measurementsKey = JSON.stringify({
              action: 'fetchMeasurements',
              id_project: selectedProject,
              id_gate: gate.id,
              prov: "00",
              kab: "00"
            });
            
            const measurementsDbResponse = await $.ajax({
              url: window.location.href,
              method: "POST",
              data: {
                db_action: "get_data",
                year: year,
                project_id: selectedProject,
                region_id: 'all',
                data_type: 'fetchMeasurements',
                cache_key: measurementsKey
              },
              dataType: "json"
            });
            
            if (!measurementsDbResponse.found || !measurementsDbResponse.data.status) {
              continue;
            }
            
            const measurements = measurementsDbResponse.data.data;
            if (!measurements || !measurements.length) {
              continue;
            }
            
            const gateNumber = gate.gate_number || 0;
            const gateName = `GATE${gateNumber}: ${gate.gate_name}`;
            
            // Process each measurement
            for (let j = 0; j < measurements.length; j++) {
              const measurement = measurements[j];
              const ukNumber = j + 1;
              const ukName = `UK${ukNumber}: ${measurement.measurement_name}`;
              const ukLevel = getUkLevelLabel(measurement);
              
              // List of standard activities for this gate
              const activities = [
                {
                  name: "Pengisian nama pelaksana aksi preventif",
                  start: gate.prev_insert_start,
                  end: gate.prev_insert_end
                },
                {
                  name: "Upload bukti pelaksanaan aksi preventif",
                  start: gate.prev_upload_start,
                  end: gate.prev_upload_end
                },
                {
                  name: "Penilaian ukuran kualitas",
                  start: gate.evaluation_start,
                  end: gate.evaluation_end
                },
                {
                  name: "Approval Gate oleh Sign-off",
                  start: gate.approval_start,
                  end: gate.approval_end
                },
                {
                  name: "Pengisian pelaksana aksi korektif",
                  start: gate.cor_insert_start,
                  end: gate.cor_insert_end
                },
                {
                  name: "Upload bukti pelaksanaan aksi korektif",
                  start: gate.cor_upload_start,
                  end: gate.cor_upload_end
                }
              ];
              
              // Process each activity
              for (const activity of activities) {
                const activityKey = `${gateName}|${ukName}|${activity.name}`;
                
                // Initialize activity object
                if (!activityData[activityKey]) {
                  activityData[activityKey] = {
                    gate: gateName,
                    uk: ukName,
                    ukLevel: ukLevel,
                    activity: activity.name,
                    start: activity.start,
                    end: activity.end,
                    statuses: {}
                  };
                }
                
                // Process each region
                for (const region of regions) {
                  // Check if UK applies to this region level
                  const isApplicable = isUkApplicableForRegion(measurement, region);
                  
                  if (!isApplicable) {
                    activityData[activityKey].statuses[region.id] = "Tidak perlu";
                    continue;
                  }
                  
                  // Get activity status
                  const status = await determineActivityStatus(
                    gate, measurement, activity.name, region.prov, region.kab
                  );
                  
                  activityData[activityKey].statuses[region.id] = status;
                }
              }
            }
          }
          
          // Add metadata
          activityData._meta = {
            lastUpdated: new Date().toISOString()
          };
          
          // Save the processed data for monitoring
          await $.ajax({
            url: window.location.href,
            method: "POST",
            data: {
              db_action: "save_processed_data",
              year: year,
              project_id: selectedProject,
              region_id: selectedRegion,
              data_json: JSON.stringify(activityData)
            },
            dataType: "json"
          });
          
          console.log("Monitoring data processed and saved successfully");
        } catch (error) {
          console.error("Error processing monitoring data:", error);
        }
      };

      // --- Event Handlers ---
      $yearSelect.on('change', async function(){
        year = $(this).val();
        $projectSelect.prop('disabled', false).empty().append('<option value="">Pilih Kegiatan</option>');
        $regionSelect.prop('disabled', true).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        selectedProject = null;
        selectedRegion  = null;
        await loadProjects();
      });

      $projectSelect.on('change', async function(){
        selectedProject = $(this).val();
        $regionSelect.prop('disabled', !selectedProject).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        selectedRegion  = null;
        if (selectedProject) await loadRegions();
      });

      $regionSelect.on('change', function(){
        selectedRegion = $(this).val();
      });
      
      $downloadData.on('click', downloadData);

      // Initialize
      loadDownloadLogs();
      $spinner.hide();
    });
  </script>
</body>
</html> 