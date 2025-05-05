<?php
// monitoring.php
require_once 'config.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Get user area information
$userArea = getUserArea();
$userLevel = $userArea['level'];
$userProv = $userArea['prov'];
$userKab = $userArea['kab'];

// Get projects from database for dropdown
function getProjectsFromDB($year, $userLevel, $userProv, $userKab) {
    global $conn;
    
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
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $projects[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $stmt->close();
    return $projects;
}

// Get regions/coverages from database for dropdown
function getRegionsFromDB($projectId, $year, $userLevel, $userProv, $userKab) {
    global $conn;
    
    $regions = [];
    $sql = "";
    $params = [];
    $types = "";
    
    // Add pusat region for superadmin and pusat users
    if ($userLevel === 'pusat' || $userLevel === 'superadmin') {
        $regions[] = [
            'id' => 'pusat',
            'prov' => '00',
            'kab' => '00',
            'name' => 'Pusat - Nasional'
        ];
    }
    
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
    
    return $regions;
}

// Get projects for initial load
$initialYear = date('Y');
$projects = getProjectsFromDB($initialYear, $userLevel, $userProv, $userKab);
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
  <style>
    :root {
      --primary-color: #2E7D32; /* Forest green */
      --primary-light: #4CAF50; /* Medium green */
      --primary-dark: #1B5E20; /* Dark green */
      --success-color: #43A047; /* Green */
      --warning-color: #FFC107; /* Amber */
      --danger-color: #D32F2F;  /* Red */
      --neutral-color: #757575; /* Medium gray */
      --light-color: #F5F5F5;   /* Light gray */
      --dark-color: #212121;    /* Very dark gray */
      --border-color: #E0E0E0;  /* Light border */
      --bg-color: #FAFAFA;      /* Off-white background */
      --card-bg: #FFFFFF;       /* White card background */
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background-color: var(--bg-color);
      color: var(--dark-color);
      line-height: 1.5;
      margin: 0;
      padding: 0;
    }
    
    .container-fluid {
      max-width: 1600px; /* Increased from 1400px */
      margin: 0 auto;
      padding: 1.5rem; /* Reduced from 2rem */
    }
    
    .main-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
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
    
    .user-controls {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .user-name {
      font-weight: 500;
      color: var(--neutral-color);
    }
    
    .logout-btn {
      padding: 0.4rem 0.75rem;
      border-radius: 6px;
      background-color: var(--light-color);
      color: var(--dark-color);
      border: 1px solid var(--border-color);
      font-size: 0.875rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .logout-btn:hover {
      background-color: #f0f0f0;
      color: var(--danger-color);
    }
    
    .logout-btn i {
      margin-right: 0.4rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 2rem;
      color: var(--dark-color);
      font-size: 2rem;
    }
    
    .card {
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      border: none;
      margin-bottom: 1rem;
      background-color: var(--card-bg);
      overflow: hidden;
    }
    
    .card-header {
      background-color: var(--card-bg);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 1.25rem;
      font-weight: 500;
      font-size: 1rem;
      color: var(--dark-color);
    }
    
    .card-body {
      padding: 1.25rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.35rem;
      color: var(--dark-color);
      font-size: 0.9rem;
    }
    
    .form-control, .form-select {
      border-radius: 6px;
      border: 1px solid var(--border-color);
      padding: 0.6rem 0.75rem;
      font-size: 0.95rem;
      background-color: #fff;
      transition: all 0.2s ease;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.2);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 6px;
      padding: 0.6rem 1rem;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }
    
    .btn-primary:active {
      background-color: var(--primary-dark);
      border-color: var(--primary-dark);
    }
    
    .table-wrapper {
      overflow: auto;
      border-radius: 8px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      margin: 0;
      max-height: calc(100vh - 220px); /* Optimized height to fit more content */
    }
    
    .table-monitoring {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: var(--card-bg);
    }
    
    .table-monitoring th {
      background-color: #f0f5f0;
      font-weight: 600;
      padding: 0.85rem 1rem;
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
      padding: 0.85rem 1rem;
      vertical-align: middle;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-monitoring tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:hover {
      background-color: rgba(46, 125, 50, 0.03);
    }
    
    /* Status badges */
    .status-badge {
      padding: 0.3rem 0.65rem;
      border-radius: 100px;
      font-weight: 500;
      font-size: 0.8rem;
      text-align: center;
      white-space: nowrap;
      display: inline-block;
    }
    
    .status-success {
      background-color: rgba(67, 160, 71, 0.12);
      color: var(--success-color);
    }
    
    .status-danger {
      background-color: rgba(211, 47, 47, 0.12);
      color: var(--danger-color);
    }
    
    .status-warning {
      background-color: rgba(255, 193, 7, 0.12);
      color: var(--warning-color);
    }
    
    .status-neutral {
      background-color: rgba(117, 117, 117, 0.12);
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
      gap: 1rem;
      padding: 1.5rem;
      border-radius: 12px;
      background-color: rgba(255,255,255,0.8);
      backdrop-filter: blur(10px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.06);
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
      background-color: rgba(46, 125, 50, 0.05);
    }
    
    /* Perbaikan tampilan tabel */
    .table-monitoring th:nth-child(1), /* Gate */
    .table-monitoring td:nth-child(1) {
      min-width: 180px;
    }
    
    .table-monitoring th:nth-child(2), /* UK */
    .table-monitoring td:nth-child(2) {
      min-width: 200px;
    }
    
    .table-monitoring th:nth-child(3), /* Level */
    .table-monitoring td:nth-child(3) {
      min-width: 90px;
      text-align: center;
    }
    
    .table-monitoring th:nth-child(4), /* Aktivitas */
    .table-monitoring td:nth-child(4) {
      min-width: 250px;
    }
    
    /* Kolom tanggal */
    .table-monitoring th:nth-child(5), /* Tanggal Mulai */
    .table-monitoring td:nth-child(5),
    .table-monitoring th:nth-child(6), /* Tanggal Selesai */
    .table-monitoring td:nth-child(6) {
      min-width: 140px;
      text-align: center;
    }
    
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
    
    /* Untuk kompatibilitas */
    .date-column {
      text-align: center;
    }
    
    /* Row colors alternating by UK group */
    .table-monitoring .uk-group-even {
      background-color: var(--card-bg);
    }
    
    .table-monitoring .uk-group-odd {
      background-color: rgba(240, 245, 240, 0.6);
    }
    
    /* Activity number */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 22px;
      height: 22px;
      border-radius: 11px;
      background-color: rgba(46, 125, 50, 0.12);
      color: var(--primary-color);
      font-weight: 600;
      margin-right: 10px;
      font-size: 0.75rem;
    }
    
    /* Status column */
    .status-column {
      min-width: 110px;
      text-align: center;
      white-space: nowrap;
    }
    
    /* Row hover and focus */
    .table-monitoring tr:hover td {
      background-color: rgba(46, 125, 50, 0.04);
    }
    
    /* Responsif */
    @media (max-width: 992px) {
      .container-fluid {
        padding: 1rem;
      }
      
      .card-body {
        padding: 1rem;
      }
    }
    
    @media (max-width: 768px) {
      .container-fluid {
        padding: 0.75rem;
      }
      
      h1 {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
      }
      
      .card-header {
        padding: 0.75rem;
      }
      
      .card-body {
        padding: 0.75rem;
      }
      
      .main-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }
      
      .user-controls {
        width: 100%;
        justify-content: flex-end;
      }
    }
    
    /* Add this to ensure we don't waste vertical space */
    .form-label {
      font-weight: 500;
      margin-bottom: 0.35rem;
      color: var(--dark-color);
      font-size: 0.9rem;
    }
    
    /* Make badge use more theme colors */
    .badge.bg-success {
      background-color: var(--primary-color) !important;
    }
    
    .text-primary {
      color: var(--primary-color) !important;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="main-header">
      <h1 class="main-title">
        <i class="fas fa-chart-line"></i> Monitoring Quality Gates
      </h1>
      
      <div>
        <?php if (isSuperAdmin()): ?>
        <a href="admin/sync_data.php" class="btn btn-success">
          <i class="fas fa-sync"></i> Sync Data
        </a>
        <?php endif; ?>
        
        <span id="syncInfoBadge" class="badge bg-light text-dark me-2" style="display: none;"></span>
        
        <a href="logout.php" class="btn btn-outline-danger">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
    
    <!-- Input Filters -->
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center py-2">
        <span>Filter Data</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-2">
            <label for="yearSelect" class="form-label">Tahun</label>
            <select id="yearSelect" class="form-select">
              <option value="">Pilih Tahun</option>
              <option value="2023">2023</option>
              <option value="2024" selected>2024</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="<?php echo ($userLevel === 'pusat' || $userLevel === 'superadmin') ? 'col-md-5' : 'col-md-8'; ?>">
            <label for="projectSelect" class="form-label">Pilih Kegiatan</label>
            <select id="projectSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-3" id="regionSelectContainer">
            <label for="regionSelect" class="form-label">Pilih Cakupan Wilayah</label>
            <select id="regionSelect" class="form-select" disabled></select>
          </div>
          <div class="<?php echo ($userLevel === 'pusat' || $userLevel === 'superadmin') ? 'col-md-2' : 'col-md-2'; ?>">
            <label class="form-label d-none d-md-block">&nbsp;</label>
            <button id="loadData" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
              <i class="fas fa-search me-2"></i>Tampilkan Data
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
      const API_URL = "api_local.php";
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      let activityData = {}; // Untuk menyimpan data status per aktivitas per wilayah
      
      // User area restrictions
      const userLevel = "<?php echo $userLevel; ?>";
      const userProv = "<?php echo $userProv; ?>";
      const userKab = "<?php echo $userKab; ?>";

      // Cache selector DOM
      const $yearSelect    = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect  = $("#regionSelect");
      const $resultsContainer = $("#resultsContainer");
      const $spinner       = $("#spinner");

      // Hide region field for provinsi and kabkot users on page load
      if (userLevel === 'provinsi' || userLevel === 'kabkot') {
        $("#regionSelectContainer").hide();
      }

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
          confirmButtonColor: '#0071e3',
          footer: 'Silakan refresh halaman atau hubungi administrator'
        });
      };

      const makeAjaxRequest = (url, data) => {
        return new Promise((resolve, reject) => {
          $.ajax({
            url,
            method: "POST",
            data,
            dataType: "text",
            cache: false,
            success: response => {
              try {
                const jsonData = JSON.parse(extractJson(response));
                resolve(jsonData);
              } catch(e) {
                console.error("Parse error:", e, "Response:", response);
                reject("Terjadi kesalahan saat memproses data: " + e.message);
              }
            },
            error: (jqXHR, textStatus, errorThrown) => {
              console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
              reject("Terjadi kesalahan pada server: " + textStatus + " - " + (errorThrown || "Tidak ada detail"));
            }
          });
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
            <div class="card-header d-flex justify-content-between align-items-center py-2">
              <span>Hasil Monitoring</span>
              <span id="resultCount" class="badge bg-success rounded-pill ms-2">${Object.keys(activityData).length} aktivitas</span>
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
          const data = activityData[key];
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
          
          // Ekstrak nomor gate
          const gateNumA = parseInt(gateA.match(/GATE(\d+)/)[1]);
          const gateNumB = parseInt(gateB.match(/GATE(\d+)/)[1]);
          
          if (gateNumA !== gateNumB) {
            return gateNumA - gateNumB;
          }
          
          // Ekstrak nomor UK
          const ukNumA = parseInt(ukA.match(/UK(\d+)/)[1]);
          const ukNumB = parseInt(ukB.match(/UK(\d+)/)[1]);
          
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
          const ukKey = activities[0].uk;
          
          // Urutkan aktivitas berdasarkan urutan proses
          activities.sort((a, b) => {
            return activityOrder[a.activity] - activityOrder[b.activity];
          });
          
          // Buat baris untuk setiap kelompok
          for (let i = 0; i < activities.length; i++) {
            const data = activities[i];
            const isFirstRow = i === 0;
            const rowspanValue = activities.length;
            
            // Ambil nomor aktivitas (1-6) berdasarkan activityOrder
            const activityNumber = activityOrder[data.activity];
            
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
              const status = data.statuses[region.id] || "Tidak tersedia";
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

      // --- Remote Data Loading ---

      // Load projects from PHP instead of API
      const loadProjects = async () => {
        try {
          // Get projects from PHP variable first
          if (year === '<?php echo $initialYear; ?>') {
            // Use pre-loaded PHP data for initial year
            const phpProjects = <?php echo json_encode($projects); ?>;
            $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
            
            if (phpProjects.length > 0) {
              phpProjects.forEach(project => {
                $projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
              });
            } else {
              $projectSelect.append('<option value="" disabled>Tidak ada kegiatan ditemukan</option>');
            }
            return;
          }
          
          // For other years or refresh, use PHP endpoint instead of direct API
          const response = await $.ajax({
            url: API_URL,
            type: 'POST',
            data: {
              action: 'fetchProjects',
              year: year
            },
            dataType: 'json'
          });
          
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          if (response.status && response.data.length > 0) {
            response.data.forEach(project => {
              $projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
            });
          } else {
            $projectSelect.append('<option value="" disabled>Tidak ada kegiatan ditemukan</option>');
          }
        } catch (error) {
          showError("Gagal memuat daftar kegiatan");
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
        }
      };

      const loadRegions = async () => {
        $regionSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          // Use PHP endpoint instead of direct API
          const response = await $.ajax({
            url: API_URL,
            type: 'POST',
            data: {
              action: 'fetchCoverages',
              id_project: selectedProject,
              year: year
            },
            dataType: 'json'
          });
          
          $regionSelect.empty();
          coverageData = [];
          
          if (!response.status || !response.data || response.data.length === 0) {
            throw new Error("Tidak ada data wilayah yang tersedia");
          }
          
          coverageData = response.data;
          
          // Auto-select region based on user level
          if (userLevel === 'kabkot') {
            // Hide region select dropdown for kabupaten users - they only see their own district
            $("#regionSelectContainer").hide();
            selectedRegion = `${userProv}${userKab}`;
            $regionSelect.append(`<option value="${selectedRegion}" selected>${coverageData.find(r => r.prov === userProv && r.kab === userKab)?.name || 'Kabupaten/Kota'}</option>`);
          } else if (userLevel === 'provinsi') {
            // Hide region select dropdown for province users - they only see their own province
            $("#regionSelectContainer").hide();
            selectedRegion = `${userProv}00`;
            $regionSelect.append(`<option value="${selectedRegion}" selected>${coverageData.find(r => r.prov === userProv && r.kab === '00')?.name || 'Provinsi'}</option>`);
          } else {
            // Show region container for pusat/superadmin
            $("#regionSelectContainer").show();

            // Build dropdowns based on coverage data
            // For pusat/superadmin
            const pusatData = coverageData.find(r => r.id === 'pusat');
            if (pusatData) {
              $regionSelect.append(`<option value="pusat">${pusatData.name}</option>`);
            }
            
            // Add provinces
            const provinces = coverageData.filter(cov => cov.kab === "00" && cov.prov !== "00");
            if (provinces.length > 0) {
              provinces.forEach(province => {
                $regionSelect.append(`<option value="${province.id}">${province.name}</option>`);
              });
            }
            
            // Add kabupaten if no provinces
            if (provinces.length === 0) {
              // For pusat/superadmin if no provinces
              const kabupatens = coverageData.filter(cov => cov.kab !== "00");
              kabupatens.forEach(kabupaten => {
                $regionSelect.append(`<option value="${kabupaten.id}">${kabupaten.name}</option>`);
              });
            }
            
            // Auto-select first option for pusat/superadmin
            if ($regionSelect.find('option[value="pusat"]').length > 0) {
              selectedRegion = "pusat";
              $regionSelect.val(selectedRegion);
            } else if ($regionSelect.find('option').length > 0) {
              selectedRegion = $regionSelect.find('option:first').val();
              $regionSelect.val(selectedRegion);
            }
          }
          
        } catch (error) {
          showError(error.message || "Gagal memuat daftar wilayah");
          $regionSelect.empty().append('<option value="">Pilih Cakupan Wilayah</option>');
          coverageData = [];
        }
      };

      // --- Fungsi untuk memproses dan menyimpan data aktivitas per wilayah ---
      const processData = async (regions) => {
        // Reset data
        activityData = {};
        
        // Dapatkan semua gates
        const gatesResponse = await makeAjaxRequest(API_URL, {
          action: "fetchGates",
          id_project: selectedProject
        });
        
        if (!gatesResponse.status || !gatesResponse.data.length) {
          throw new Error("Tidak ada data gate");
        }
        
        const gates = gatesResponse.data;
        
        // Cache untuk data API
        const apiCache = {
          measurements: {},
          preventives: {},
          preventivesKab: {},
          assessments: {},
          correctives: {},
          correctivesKab: {},
          allActions: {}
        };
        
        // Fungsi untuk mendapatkan data dari cache atau API
        const getDataFromCacheOrApi = async (cacheKey, apiAction, apiParams) => {
          const cacheKeyString = JSON.stringify({ action: apiAction, ...apiParams });
          
          if (!apiCache[cacheKey][cacheKeyString]) {
            apiCache[cacheKey][cacheKeyString] = await makeAjaxRequest(API_URL, { 
              action: apiAction, 
              ...apiParams 
            });
          }
          
          return apiCache[cacheKey][cacheKeyString];
        };
        
        // 1. Pre-load semua measurements untuk semua gates
        for (const gate of gates) {
          // Dapatkan measurements untuk gate ini (dari pusat)
          const measurementsResponse = await getDataFromCacheOrApi(
            'measurements',
            'fetchMeasurements', 
            {
              id_project: selectedProject,
              id_gate: gate.id,
              prov: "00",
              kab: "00"
            }
          );
          
          if (!measurementsResponse.status || !measurementsResponse.data.length) {
            continue; // Skip jika tidak ada measurements
          }
          
          const measurements = measurementsResponse.data;
          const gateNumber = gate.gate_number || gates.indexOf(gate) + 1;
          const gateName = `GATE${gateNumber}: ${gate.gate_name}`;
          
          // 2. Untuk setiap ukuran kualitas, tentukan region yang sesuai berdasarkan assessment_level
          for (let j = 0; j < measurements.length; j++) {
            const measurement = measurements[j];
            const ukNumber = j + 1;
            const ukName = `UK${ukNumber}: ${measurement.measurement_name}`;
            const ukLevel = getUkLevelLabel(measurement);
            
            // Tentukan assessment_level (default ke 1 jika tidak ada)
            const assessmentLevel = parseInt(measurement.assessment_level || 1);
            
            // Filter region berdasarkan assessment_level dan user level
            const applicableRegions = regions.filter(region => {
              // Filter berdasarkan assessment_level
              if (assessmentLevel === 1) {
                // Khusus pusat (kode 00)
                return region.prov === "00" && region.kab === "00";
              } else if (assessmentLevel === 2) {
                // Khusus level provinsi (kab = 00, prov != 00)
                return region.prov !== "00" && region.kab === "00";
              } else if (assessmentLevel === 3) {
                // Khusus level kabupaten (kab != 00)
                return region.kab !== "00";
              }
              return false;
            });
            
            // Optimasi untuk fetchAllActions berdasarkan user level
            let regionsToFetch = [];

            // Untuk level kabupaten, hanya fetch data kabupaten sendiri
            if (userLevel === 'kabkot') {
              regionsToFetch = applicableRegions.filter(r => r.prov === userProv && r.kab === userKab);
            }
            // Untuk level provinsi, hanya fetch data provinsi dan kabupaten di bawahnya
            else if (userLevel === 'provinsi') {
              regionsToFetch = applicableRegions.filter(r => r.prov === userProv);
            }
            // Untuk pusat, fetch semua regions yang applicable
            else {
              regionsToFetch = applicableRegions;
            }
            
            // Optimasi untuk kabupaten level
            if (assessmentLevel === 3 && userLevel !== 'kabkot') {
              // Pengelompokan region berdasarkan provinsi untuk level 3 (kabupaten)
              const provinceGroups = {};
              regionsToFetch.forEach(region => {
                if (!provinceGroups[region.prov]) {
                  provinceGroups[region.prov] = [];
                }
                provinceGroups[region.prov].push(region);
              });
              
              // Untuk level kabupaten, ambil sampel 1 kabupaten per provinsi untuk fetchAllActions
              for (const prov in provinceGroups) {
                if (provinceGroups[prov].length > 0) {
                  const sampleRegion = provinceGroups[prov][0];
                  
                  // Fetch allActions hanya untuk sampel kabupaten
                  const actionsKey = JSON.stringify({
                    action: 'fetchAllActions',
                    id_project: selectedProject,
                    id_gate: gate.id,
                    prov: sampleRegion.prov,
                    kab: sampleRegion.kab
                  });
                  
                  // Ambil data Actions untuk sampel
                  if (!apiCache.allActions[actionsKey]) {
                    apiCache.allActions[actionsKey] = await makeAjaxRequest(API_URL, {
                      action: 'fetchAllActions',
                      id_project: selectedProject,
                      id_gate: gate.id,
                      prov: sampleRegion.prov,
                      kab: sampleRegion.kab
                    });
                  }
                  
                  // Clone response untuk kabupaten lain dalam provinsi yang sama
                  for (let i = 1; i < provinceGroups[prov].length; i++) {
                    const otherRegion = provinceGroups[prov][i];
                    const otherActionsKey = JSON.stringify({
                      action: 'fetchAllActions',
                      id_project: selectedProject,
                      id_gate: gate.id,
                      prov: otherRegion.prov,
                      kab: otherRegion.kab
                    });
                    
                    // Gunakan data yang sama
                    apiCache.allActions[otherActionsKey] = JSON.parse(JSON.stringify(apiCache.allActions[actionsKey]));
                  }
                  
                  // Tetap fetch assessments untuk semua kabupaten
                  for (const region of provinceGroups[prov]) {
                    await getDataFromCacheOrApi(
                      'assessments',
                      'fetchAssessments',
                      {
                        id_project: selectedProject,
                        id_gate: gate.id,
                        prov: region.prov,
                        kab: region.kab
                      }
                    );
                  }
                }
              }
            } else {
              // Untuk level pusat dan provinsi, atau jika pengguna kabupaten melihat data kabupaten sendiri
              for (const region of regionsToFetch) {
                // Pre-load allActions untuk gate & region
                await getDataFromCacheOrApi(
                  'allActions',
                  'fetchAllActions',
                  {
                    id_project: selectedProject,
                    id_gate: gate.id,
                    prov: region.prov,
                    kab: region.kab
                  }
                );
                
                // Pre-load assessments data untuk gate & region
                await getDataFromCacheOrApi(
                  'assessments',
                  'fetchAssessments',
                  {
                    id_project: selectedProject,
                    id_gate: gate.id,
                    prov: region.prov,
                    kab: region.kab
                  }
                );
              }
            }
            
            // Daftar aktivitas standar untuk gate ini
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
            
            // 4. Cari status untuk setiap aktivitas dan setiap wilayah
            for (const activity of activities) {
              const activityKey = `${gateName}|${ukName}|${activity.name}`;
              
              // Simpan info aktivitas
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
              
              // 5. Untuk setiap wilayah, isi status
              for (const region of regions) {
                // Cek apakah ukuran kualitas sesuai dengan level wilayah
                const isApplicable = isUkApplicableForRegion(measurement, region);
                
                if (!isApplicable) {
                  activityData[activityKey].statuses[region.id] = "Tidak perlu";
                  continue;
                }
                
                // Dapatkan status aktivitas
                const status = await determineActivityStatus(
                  gate, measurement, year, activity.name, region.prov, region.kab, apiCache, getDataFromCacheOrApi
                );
                
                // Simpan status
                activityData[activityKey].statuses[region.id] = status;
              }
            }
          }
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
        
        // For kabupaten and provinsi users, we'll auto-select region and auto-load data
        if (selectedProject && (userLevel === 'kabkot' || userLevel === 'provinsi')) {
          await loadRegions();
          
          // Automatically load data for kabupaten and provinsi users
          $("#loadData").trigger('click');
        } else {
          // For pusat/superadmin, just enable the region dropdown
          $regionSelect.prop('disabled', !selectedProject).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
          selectedRegion = null;
          if (selectedProject) await loadRegions();
        }
      });

      $regionSelect.on('change', function(){
        selectedRegion = $(this).val();
      });

      $("#loadData").on('click', async function(){
        if (!year || !selectedProject || !selectedRegion) {
          showError("Silakan pilih tahun, kegiatan, dan cakupan wilayah terlebih dahulu");
          return;
        }
        $spinner.fadeIn(200);
        $resultsContainer.empty();
        try {
          // Tentukan daftar wilayah yang akan diproses berdasarkan user level
          let regionsToProcess = [];
          
          if (userLevel === 'kabkot') {
            // Untuk kabupaten, hanya tampilkan wilayah mereka sendiri
            const kabRegion = coverageData.find(r => r.prov === userProv && r.kab === userKab);
            if (kabRegion) {
              regionsToProcess = [kabRegion];
            } else {
              throw new Error("Data wilayah tidak ditemukan");
            }
          } else if (userLevel === 'provinsi') {
            // Untuk provinsi, tampilkan provinsi dan kabupaten di bawahnya
            const provRegion = coverageData.find(r => r.prov === userProv && r.kab === '00');
            if (!provRegion) {
              throw new Error("Data provinsi tidak ditemukan");
            }
            
            // Provinsi
            regionsToProcess = [provRegion];
            
            // Tambahkan kabupaten di provinsi tersebut
            const kabupatenList = coverageData.filter(r => r.prov === userProv && r.kab !== '00');
            regionsToProcess = [...regionsToProcess, ...kabupatenList];
          } else {
            // Untuk pusat/superadmin, gunakan logika yang sudah ada
            const regionData = coverageData.find(r => r.id === selectedRegion);
            if (!regionData) {
              throw new Error("Data wilayah tidak ditemukan");
            }
            
            if (selectedRegion === "pusat") {
              // Hanya pusat
              regionsToProcess = [{ id: "pusat", prov: "00", kab: "00", name: "Pusat" }];
            } else {
              const prov = regionData.prov;
              // Provinsi dan semua kabupaten di dalamnya
              regionsToProcess = [
                { id: `${prov}00`, prov: prov, kab: "00", name: regionData.name }
              ];
              
              // Tambahkan kabupaten
              const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
              regionsToProcess = [...regionsToProcess, ...kabupatenList];
            }
          }
          
          // Proses data untuk semua wilayah terpilih
          await processData(regionsToProcess);
          
          // Tampilkan hasil dalam format tabel
          displayResultTable(regionsToProcess);
          
        } catch(error) {
          console.error("Error details:", error);
          showError(error.message || "Terjadi kesalahan saat memuat data");
        } finally {
          $spinner.fadeOut(200);
        }
      });

      // Inisialisasi
      year = $yearSelect.val();
      loadProjects();
      $spinner.hide();
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
