<?php
// index.php - Dashboard per wilayah untuk Quality Gates (Home Page)

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'sso_integration.php';
    
    // Log untuk debugging
    error_log('index.php accessed - checking SSO login');
    
    // Pastikan user sudah login SSO
    requireSSOLogin('index.php');
    
    // Log setelah cek login
    error_log('SSO login check passed - getting user data');
    
    // Dapatkan filter wilayah berdasarkan SSO user
    $wilayah_filter = getSSOWilayahFilter();
    $user_data = getUserData();
    
    // Log user data
    error_log('User data: ' . ($user_data ? json_encode($user_data) : 'NULL'));
    
} catch (Exception $e) {
    // Jika terjadi error, tampilkan pesan error
    error_log('index.php Error: ' . $e->getMessage());
    echo "<!DOCTYPE html><html><head><title>Error - Dashboard</title></head><body>";
    echo "<h1>🚨 Dashboard Error</h1>";
    echo "<p>Terjadi kesalahan saat memuat dashboard: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='debug.php?show=debug'>🐛 Debug Info</a> | <a href='sso_login.php'>🔑 Login Ulang</a> | <a href='index.php'>🏠 Kembali</a></p>";
    echo "</body></html>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Quality Gates</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Navbar CSS -->
  <link rel="stylesheet" href="navbar.css">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- SheetJS for Excel export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <style>
    :root {
      --primary-color: #059669;   /* Emerald green */
      --primary-hover: #047857;   /* Darker emerald */
      --primary-light: #d1fae5;   /* Light emerald */
      --success-color: #10b981;   /* Success green */
      --warning-color: #f59e0b;   /* Amber */
      --danger-color: #ef4444;    /* Red */
      --neutral-color: #6b7280;   /* Gray */
      --light-color: #f9fafb;     /* Light gray */
      --dark-color: #111827;      /* Dark gray */
      --border-color: #e5e7eb;    /* Border gray */
      --text-secondary: #374151;  /* Secondary text */
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
      color: var(--dark-color);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
    

    
    .container-fluid {
      max-width: 95vw;
      margin: 0 auto;
      padding: 1rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
      font-size: 1.5rem;
    }
    
    .dashboard-subtitle {
      color: var(--neutral-color);
      margin-bottom: 2rem;
      font-size: 1rem;
    }
    
    .card {      border-radius: 12px;      box-shadow: 0 8px 32px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.04);      border: none;      margin-bottom: 1rem;      background-color: #ffffff;      overflow: visible;      transform: translateY(0);      transition: all 0.3s ease;    }        .card:hover {      transform: translateY(-2px);      box-shadow: 0 12px 40px rgba(0,0,0,0.12), 0 8px 24px rgba(0,0,0,0.08);    }        .card-body {      overflow: visible;    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid var(--border-color);
      padding: 0.5rem 0.6rem;
      font-weight: 500;
      font-size: 0.7rem;
      color: var(--dark-color);
      line-height: 1.2;
    }
    
    .card-body {
      padding: 0.6rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.25rem;
      color: var(--dark-color);
      font-size: 0.7rem;
      line-height: 1.2;
    }
    
    .form-control, .form-select {
      border-radius: 4px;
      border: 1px solid var(--border-color);
      padding: 0.3rem 0.5rem;
      font-size: 0.7rem;
      background-color: #fff;
      transition: all 0.2s ease;
      line-height: 1.2;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
      outline: none;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 4px;
      padding: 0.4rem 0.8rem;
      font-weight: 500;
      font-size: 0.7rem;
      transition: all 0.2s ease;
    }
    
    .btn-primary:hover {
      background-color: var(--primary-hover);
      border-color: var(--primary-hover);
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
    }
    
    .table-wrapper {
      overflow: auto;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin: 0;
      max-height: calc(100vh - 220px);
      min-height: 400px;
    }
    
    .table-dashboard {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #fff;
      margin: 0;
    }
    
    .table-dashboard th {
      background: linear-gradient(135deg, var(--primary-light) 0%, #e6fffa 100%);
      font-weight: 600;
      padding: 0.65rem 0.85rem;
      font-size: 0.65rem;
      color: var(--primary-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: left;
      border-bottom: 2px solid var(--primary-color);
      text-transform: uppercase;
      letter-spacing: 0.3px;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;
    }
    
    .table-dashboard td {
      padding: 0.65rem 0.85rem;
      vertical-align: middle;
      font-size: 0.7rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-dashboard tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:hover {
      background-color: rgba(5, 150, 105, 0.04);
    }
    
    .project-name {
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .gate-name {
      font-weight: 500;
      color: var(--dark-color);
    }
    
    .region-name {
      color: var(--neutral-color);
      font-size: 0.85rem;
    }
    
    .date-range {
      white-space: nowrap;
    }
    
    .date-active {
      background: linear-gradient(135deg, var(--success-color), #30d158);
      color: white;
      padding: 0.2rem 0.6rem;
      border-radius: 16px;
      font-weight: 500;
      font-size: 0.65rem;
      display: inline-block;
      animation: pulse 2s infinite;
    }
    
    .date-upcoming {
      background: rgba(255,159,10,0.12);
      color: var(--warning-color);
      padding: 0.2rem 0.6rem;
      border-radius: 16px;
      font-weight: 500;
      font-size: 0.65rem;
      display: inline-block;
    }
    
    .date-completed {
      background: rgba(142,142,147,0.12);
      color: var(--neutral-color);
      padding: 0.2rem 0.6rem;
      border-radius: 16px;
      font-weight: 500;
      font-size: 0.65rem;
      display: inline-block;
    }
    

    
    @keyframes pulse {
      0% { 
        opacity: 1; 
        box-shadow: 0 0 0 0 rgba(52,199,89,0.4);
      }
      50% { 
        opacity: 0.8; 
        box-shadow: 0 0 0 8px rgba(52,199,89,0.1);
      }
      100% { 
        opacity: 1; 
        box-shadow: 0 0 0 0 rgba(52,199,89,0);
      }
    }
    
    .empty-state {
      text-align: center;
      padding: 3rem 2rem;
      color: var(--neutral-color);
    }
    
    .empty-state i {
      font-size: 3rem;
      margin-bottom: 1rem;
      opacity: 0.5;
    }
    
    /* Enhanced Loading Spinner */
    .spinner {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(5, 150, 105, 0.1);
      backdrop-filter: blur(8px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
      animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    .spinner-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2rem;
      padding: 3rem 2.5rem;
      border-radius: 20px;
      background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
      backdrop-filter: blur(20px);
      box-shadow: 
        0 20px 60px rgba(5, 150, 105, 0.15),
        0 8px 32px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
      border: 1px solid rgba(5, 150, 105, 0.2);
      transform: scale(0.9);
      animation: scaleIn 0.4s ease-out 0.1s forwards;
    }
    
    @keyframes scaleIn {
      to { transform: scale(1); }
    }
    
    .spinner-text {
      font-weight: 600;
      color: var(--primary-color);
      font-size: 0.9rem;
      text-align: center;
      letter-spacing: 0.3px;
    }
    
    /* Custom Loading Animation */
    .loading-animation {
      width: 60px;
      height: 60px;
      position: relative;
    }
    
    .loading-dots {
      width: 100%;
      height: 100%;
      position: relative;
      transform-origin: center;
      animation: rotate 2s linear infinite;
    }
    
    .loading-dot {
      position: absolute;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), var(--success-color));
      animation: bounce 1.4s ease-in-out infinite;
      box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
    }
    
    .loading-dot:nth-child(1) { top: 0; left: 50%; margin-left: -6px; animation-delay: 0s; }
    .loading-dot:nth-child(2) { top: 50%; right: 0; margin-top: -6px; animation-delay: -0.2s; }
    .loading-dot:nth-child(3) { bottom: 0; left: 50%; margin-left: -6px; animation-delay: -0.4s; }
    .loading-dot:nth-child(4) { top: 50%; left: 0; margin-top: -6px; animation-delay: -0.6s; }
    .loading-dot:nth-child(5) { top: 15%; right: 15%; animation-delay: -0.8s; }
    .loading-dot:nth-child(6) { bottom: 15%; right: 15%; animation-delay: -1s; }
    .loading-dot:nth-child(7) { bottom: 15%; left: 15%; animation-delay: -1.2s; }
    .loading-dot:nth-child(8) { top: 15%; left: 15%; animation-delay: -1.4s; }
    
    @keyframes rotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @keyframes bounce {
      0%, 80%, 100% { 
        transform: scale(0.6); 
        opacity: 0.5;
      }
      40% { 
        transform: scale(1.2); 
        opacity: 1;
      }
    }
    
    /* Sorting styles */
    .table-dashboard th:hover {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--success-color) 100%);
      color: white;
    }
    
    .table-dashboard th .sort-icon {
      margin-left: 0.5rem;
      opacity: 0.5;
      transition: opacity 0.2s ease;
    }
    
    .table-dashboard th:hover .sort-icon {
      opacity: 1;
    }
    
    .table-dashboard th.sorted .sort-icon {
      opacity: 1;
      color: var(--primary-color);
    }
    
    .table-dashboard th.sorted:hover .sort-icon {
      color: white;
    }
    
    /* Searchable Dropdown Styles */
    .searchable-dropdown {
      position: relative;
    }
    
    .dropdown-search-input {
      border-radius: 0 0 8px 8px !important;
      border-top: 1px solid var(--border-color) !important;
    }
    .dropdown-options {
      position: absolute;
      bottom: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid var(--border-color);
      border-bottom: none;
      border-radius: 4px 4px 0 0;
      max-height: 200px;
      overflow-y: auto;
      z-index: 99999;
      box-shadow: 0 -6px 12px rgba(0, 0, 0, 0.2);
    }
    
    .dropdown-option {
      padding: 0.4rem 0.6rem;
      cursor: pointer;
      border-bottom: 1px solid var(--border-color);
      transition: background-color 0.2s ease;
      font-size: 0.7rem;
    }
    
    .dropdown-option:last-child {
      border-bottom: none;
    }
    
    .dropdown-option:hover {
      background-color: var(--primary-light);
    }
    
    .dropdown-option.selected {
      background-color: var(--primary-color);
      color: white;
    }
    
    .dropdown-no-results {
      padding: 0.4rem 0.6rem;
      color: var(--neutral-color);
      text-align: center;
      font-style: italic;
      font-size: 0.7rem;
    }

    /* Statistics Cards - Ultra Compact */
    #statsCards .card {
      transition: all 0.2s ease;
      height: auto;
      min-height: auto;
    }
    
    #statsCards .card:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    #statsCards .card-body {
      padding: 0.4rem 0.15rem !important;
    }
    
    #statsCards h6 {
      font-size: 0.5rem;
      margin-bottom: 0.025rem;
      line-height: 1.0;
      font-weight: 600;
    }
    
    #statsCards h3 {
      font-size: 0.8rem;
      margin-bottom: 0;
      font-weight: 700;
    }
    
    #statsCards .d-flex {
      margin-bottom: 0.05rem;
      align-items: center;
    }
    
    #statsCards i {
      font-size: 0.55rem;
      margin-right: 0.1rem !important;
    }

    /* Zoom and Resolution optimizations */
    @media screen {
      html {
        zoom: 1;
        width: 100%;
        overflow-x: auto;
      }
      
      body {
        min-width: 1200px;
        width: auto !important;
        overflow-x: auto;
      }
      
      .container-fluid {
        width: 95vw !important;
        min-width: 1150px;
      }
      
      .table-wrapper {
        width: 100%;
        overflow-x: auto;
        overflow-y: auto;
      }
    }

    /* Responsif untuk resolusi kecil */
    @media (max-width: 1400px) {
      .container-fluid {
        max-width: 98vw;
        padding: 0.8rem;
      }
      
      #statsCards .card-body {
        padding: 0.3rem 0.2rem;
      }
      
      #statsCards h6 {
        font-size: 0.55rem;
      }
      
      #statsCards h3 {
        font-size: 0.9rem;
      }
    }
    
    @media (max-width: 992px) {
      .container-fluid {
        padding: 0.8rem;
        min-width: 1000px;
      }
      
      .card-body {
        padding: 0.6rem;
      }
      
      .user-details {
        display: none;
      }
      
      #statsCards .col-md-2 {
        margin-bottom: 0.3rem;
      }
    }
    
    @media (max-width: 768px) {
      .container-fluid {
        padding: 0.5rem;
        min-width: 900px;
      }
      
      h1 {
        font-size: 1.3rem;
        margin-bottom: 0.2rem;
      }
      
      .card-header {
        padding: 0.5rem 0.6rem;
      }
      
      .card-body {
        padding: 0.6rem;
      }
      
      .table-dashboard th,
      .table-dashboard td {
        padding: 0.5rem;
        font-size: 0.7rem;
      }
      
      #statsCards .col-md-2 {
        width: 50%;
        margin-bottom: 0.3rem;
      }
      
      #statsCards h6 {
        font-size: 0.5rem;
      }
      
    #statsCards h3 {
      font-size: 0.85rem;
    }
  }

  /* Download button styles */
  .download-btn {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 500;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    text-decoration: none;
    cursor: pointer;
  }
  
  .download-btn:hover {
    background: linear-gradient(135deg, #059669, #047857);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }
  
  .download-btn:active {
    transform: translateY(0);
  }
  
  .table-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  </style>
</head>
<body>
  <!-- SSO Integrated Navigation -->
  <?php renderSSONavbar('dashboard'); ?>

  <div class="container-fluid">
    <!-- Filters -->
    <div class="card" id="filtersCard" style="margin-bottom: 0.75rem;">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-10">
            <label for="filterProjectSearch" class="form-label">Kegiatan</label>
            <div class="searchable-dropdown">
              <input type="text" class="form-control dropdown-search-input" id="filterProjectSearch" placeholder="Cari kegiatan..." value="Semua Kegiatan">
              <div class="dropdown-options" id="filterProjectOptions" style="display: none;"></div>
              <input type="hidden" id="filterProject">
            </div>
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button id="applyFilters" class="btn btn-primary w-100">
              <i class="fas fa-filter me-2"></i>Tampilkan Data
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row" id="statsCards" style="display: none; margin-bottom: 0rem;">
      <div class="col-md-2">
        <div class="card border-0 bg-dark bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-project-diagram text-dark me-2"></i>
              <h6 class="mb-0 text-dark fw-semibold">Kegiatan</h6>
            </div>
            <h3 class="mb-0 text-dark fw-bold" id="statProjects">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-info bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-list text-info me-2"></i>
              <h6 class="mb-0 text-info fw-semibold">Total Gate</h6>
            </div>
            <h3 class="mb-0 text-info fw-bold" id="statTotal">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-secondary bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-check-circle text-secondary me-2"></i>
              <h6 class="mb-0 text-secondary fw-semibold">Selesai</h6>
            </div>
            <h3 class="mb-0 text-secondary fw-bold" id="statCompleted">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-play-circle text-success me-2"></i>
              <h6 class="mb-0 text-success fw-semibold">Sedang Berlangsung</h6>
            </div>
            <h3 class="mb-0 text-success fw-bold" id="statActive">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-clock text-warning me-2"></i>
              <h6 class="mb-0 text-warning fw-semibold">Akan Datang</h6>
            </div>
            <h3 class="mb-0 text-warning fw-bold" id="statUpcoming">0</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Results -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center" id="dashboardTableHeader" style="display: none;">
        <span>Data Dashboard</span>
        <div class="table-header-actions">
          <span id="resultCount" class="badge bg-primary rounded-pill">0 data</span>
          <button id="downloadExcel" class="download-btn">
            <i class="fas fa-file-excel"></i>
            Download Excel
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-wrapper">
          <table class="table-dashboard">
            <thead>
              <tr>
                <th data-sort="project_name">Kegiatan<i class="fas fa-sort sort-icon"></i></th>
                <th data-sort="gate_name">Gate<i class="fas fa-sort sort-icon"></i></th>
                <th data-sort="start_date">Tanggal Mulai<i class="fas fa-sort sort-icon"></i></th>
                <th data-sort="end_date">Tanggal Selesai<i class="fas fa-sort sort-icon"></i></th>
                <th data-sort="status">Status<i class="fas fa-sort sort-icon"></i></th>
              </tr>
            </thead>
            <tbody id="dashboardTableBody">
              <!-- Data akan dimuat di sini -->
            </tbody>
          </table>
        </div>
        
        <div id="emptyState" class="empty-state" style="display: none;">
          <i class="fas fa-inbox"></i>
          <h5>Tidak ada data</h5>
          <p>Belum ada project-gate yang sesuai dengan filter Anda</p>
        </div>
        
        <div id="initialState" class="empty-state">
          <i class="fas fa-filter"></i>
          <h5>Pilih Filter</h5>
          <p>Silakan pilih filter untuk melihat data project-gate</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Enhanced Loading Spinner -->
  <div id="spinner" class="spinner">
    <div class="spinner-container">
      <div class="loading-animation">
        <div class="loading-dots">
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
          <div class="loading-dot"></div>
        </div>
      </div>
      <div class="spinner-text">Memuat data dashboard...</div>
    </div>
  </div>

  <script>
    $(function(){
      const API_URL = "api.php";
      let currentUser = null;
      let dashboardData = [];
      let filteredData = [];
      let sortColumn = null;
      let sortDirection = 'asc';
      let filterProjectData = [];
      let filterProjectDropdown;
      
      // Cache selector DOM
      const $spinner = $("#spinner");
      const $dashboardTableBody = $("#dashboardTableBody");
      const $emptyState = $("#emptyState");
      const $resultCount = $("#resultCount");
      const $filtersCard = $("#filtersCard");
      
      // Helper Functions
      
      // Searchable Dropdown Implementation
      const createSearchableDropdown = (searchInput, optionsContainer, hiddenInput, data, valueKey, textKey, onSelect) => {
        const $search = $(searchInput);
        const $options = $(optionsContainer);
        const $hidden = $(hiddenInput);
        
        // Show options on focus and clear text
        $search.on('focus', function() {
          $(this).select(); // Select all text so user can type over it
          renderOptions(data);
          $options.show();
        });
        
        // Hide options when clicking outside
        $(document).on('click', function(e) {
          if (!$(e.target).closest($search.parent()).length) {
            $options.hide();
          }
        });
        
        // Filter options on input
        $search.on('input', function() {
          const query = $(this).val().toLowerCase();
          const filtered = data.filter(item => 
            item[textKey].toLowerCase().includes(query)
          );
          renderOptions(filtered);
        });
        
        // Render options
        const renderOptions = (items) => {
          $options.empty();
          
          if (items.length === 0) {
            $options.append('<div class="dropdown-no-results">Tidak ada hasil ditemukan</div>');
            return;
          }
          
          items.forEach(item => {
            const $option = $(`<div class="dropdown-option" data-value="${item[valueKey]}">${item[textKey]}</div>`);
            $option.on('click', function() {
              const value = $(this).data('value');
              const text = $(this).text();
              
              $search.val(text);
              $hidden.val(value);
              $options.hide();
              
              if (onSelect) onSelect(value, text);
            });
            $options.append($option);
          });
        };
        
        // Public methods
        return {
          setData: (newData) => {
            data = newData;
          },
          setValue: (value, text) => {
            $search.val(text || '');
            $hidden.val(value || '');
          },
          enable: () => {
            $search.prop('disabled', false);
          },
          disable: () => {
            $search.prop('disabled', true);
            $options.hide();
          },
          clear: () => {
            $search.val('Semua Kegiatan');
            $hidden.val('');
            $options.hide();
          }
        };
      };
      
      const extractJson = response => {
        const start = response.indexOf('{');
        const end = response.lastIndexOf('}');
        return (start !== -1 && end !== -1 && end > start)
          ? response.substring(start, end + 1)
          : response;
      };
      
      const showError = message => {
        console.error(message);
        alert(message);
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
                reject("Terjadi kesalahan saat memproses data");
              }
            },
            error: () => reject("Terjadi kesalahan pada server")
          });
        });
      };
      
      const formatDate = dateStr => {
        if (!dateStr || dateStr === '-') return '-';
        
        // Handle both date-only and datetime formats
        let datePart = dateStr;
        if (dateStr.includes(' ')) {
          datePart = dateStr.split(' ')[0]; // Remove time part
        }
        
        const parts = datePart.split('-');
        if (parts.length === 3) {
          const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                          'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
          return `${parts[2]} ${months[parseInt(parts[1]) - 1]} ${parts[0]}`;
        }
        return datePart;
      };
      
      const isDateInRange = (startDateStr, endDateStr) => {
        if (!startDateStr || !endDateStr || startDateStr === '-' || endDateStr === '-') {
          return false;
        }
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const startDate = new Date(startDateStr);
        startDate.setHours(0, 0, 0, 0);
        
        const endDate = new Date(endDateStr);
        endDate.setHours(23, 59, 59, 999);
        
        return today >= startDate && today <= endDate;
      };
      
      const getDateStatus = (startDateStr, endDateStr) => {
        if (!startDateStr || !endDateStr || startDateStr === '-' || endDateStr === '-') {
          return { status: 'unknown', text: 'Tidak diketahui', class: '' };
        }
        
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const startDate = new Date(startDateStr);
        startDate.setHours(0, 0, 0, 0);
        
        const endDate = new Date(endDateStr);
        endDate.setHours(23, 59, 59, 999);
        
        if (today < startDate) {
          return { 
            status: 'upcoming', 
            text: 'Akan datang', 
            class: 'date-upcoming' 
          };
        } else if (today >= startDate && today <= endDate) {
          return { 
            status: 'active', 
            text: 'Sedang berlangsung', 
            class: 'date-active' 
          };
        } else {
          return { 
            status: 'completed', 
            text: 'Selesai', 
            class: 'date-completed' 
          };
        }
      };
      
      // Inisialisasi user dengan data SSO
      const initUser = () => {
        console.log('🔍 [DEBUG] Initializing user...');
        
        // Data user sudah tersedia dari SSO PHP session
        currentUser = {
          username: '<?= isset($_SESSION["sso_username"]) ? $_SESSION["sso_username"] : "" ?>',
          name: '<?= isset($_SESSION["sso_nama"]) ? $_SESSION["sso_nama"] : "" ?>',
          email: '<?= isset($_SESSION["sso_email"]) ? $_SESSION["sso_email"] : "" ?>',
          role_name: '<?= isset($_SESSION["sso_jabatan"]) ? $_SESSION["sso_jabatan"] : "User" ?>',
          prov: '<?= isset($_SESSION["sso_prov"]) ? $_SESSION["sso_prov"] : "00" ?>',
          kab: '<?= isset($_SESSION["sso_kab"]) ? $_SESSION["sso_kab"] : "00" ?>',
          unit_kerja: '<?= isset($_SESSION["sso_unit_kerja"]) ? $_SESSION["sso_unit_kerja"] : "kabupaten" ?>'
        };
        
        console.log('👤 [DEBUG] Current User Data:', currentUser);
        console.log('📝 [DEBUG] Username check:', {
          username: currentUser.username,
          isEmpty: !currentUser.username,
          length: currentUser.username ? currentUser.username.length : 0
        });
        console.log('🗺️ [DEBUG] Wilayah filter:', {
          prov: currentUser.prov,
          kab: currentUser.kab,
          unit_kerja: currentUser.unit_kerja,
          prov_empty: !currentUser.prov,
          kab_empty: !currentUser.kab
        });
        
        // Validasi data user dengan delay untuk mencegah infinite redirect
        if (!currentUser.username) {
          console.error('❌ [ERROR] Username kosong! Redirecting to SSO login...');
          console.log('🔗 [DEBUG] Redirecting to: sso_login.php');
          alert('Session SSO tidak ditemukan. Akan diarahkan ke halaman login SSO.');
          setTimeout(() => {
            window.location.href = 'sso_login.php';
          }, 1000);
          return false;
        }
        
        console.log('✅ [SUCCESS] User validation passed!');
        
        // Update UI dengan info user
        $("#userName").text(currentUser.name || currentUser.username);
        $("#userRole").text(currentUser.role_name || 'User');
        $("#userAvatar").text(currentUser.name ? currentUser.name.charAt(0).toUpperCase() : currentUser.username.charAt(0).toUpperCase());
        
        console.log('🎨 [DEBUG] UI updated with user info');
        
        // Load filter options dan data
        console.log('📊 [DEBUG] Loading filter options and dashboard data...');
        loadFilterOptions();
        loadDashboardData();
        
        return true;
      };
      
      // Load data dashboard
      const loadDashboardData = async (filters = {}, forceRefresh = false) => {
        if (!currentUser) return;
        
        // Cek apakah ada data tersimpan di localStorage dan tidak ada permintaan refresh paksa
        if (!forceRefresh) {
          const savedData = localStorage.getItem('qg_dashboard_data');
          const savedFilters = localStorage.getItem('qg_dashboard_filters');
          const savedView = sessionStorage.getItem('qg_dashboard_view');
          
          if (savedData && savedFilters && savedView === 'active') {
            try {
              console.log('📂 [DEBUG] Loading data from localStorage');
              dashboardData = JSON.parse(savedData);
              filteredData = [...dashboardData];
              
              // Terapkan filter yang tersimpan jika tidak ada filter baru
              if (Object.keys(filters).length === 0) {
                filters = JSON.parse(savedFilters);
              }
              
              // Tampilkan data tanpa loading spinner
              displayDashboardData();
              
              // Simpan data ke sessionStorage untuk mempertahankan tampilan saat navigasi
              sessionStorage.setItem('qg_dashboard_view', 'active');
              return;
            } catch (e) {
              console.error('❌ [ERROR] Failed to load data from localStorage:', e);
              // Lanjutkan dengan memuat data dari server jika terjadi kesalahan
            }
          }
        }
        
        $spinner.show();
        $("#initialState").hide();
        
        try {
          const requestData = {
            action: "fetchDashboardData",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            filter_year: "2025",
            ...filters
          };
          
          console.log('📡 [DEBUG] API Request Data:', requestData);
          
          const response = await makeAjaxRequest(API_URL, requestData);
          
          console.log('📨 [DEBUG] API Response:', response);
          
          if (response.status && response.data) {
            dashboardData = response.data;
            filteredData = [...dashboardData];
            
            // Simpan data dan filter ke localStorage
            localStorage.setItem('qg_dashboard_data', JSON.stringify(dashboardData));
            localStorage.setItem('qg_dashboard_filters', JSON.stringify(filters));
            
            displayDashboardData();
          } else {
            throw new Error(response.message || "Gagal memuat data dashboard");
          }
        } catch(error) {
          showError(error.message || "Terjadi kesalahan saat memuat data");
          dashboardData = [];
          filteredData = [];
          displayDashboardData();
        } finally {
          $spinner.hide();
        }
      };
      
      // Sorting function
      const sortData = (column) => {
        if (sortColumn === column) {
          sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          sortColumn = column;
          sortDirection = 'asc';
        }
        
        filteredData.sort((a, b) => {
          let valueA, valueB;
          
          switch(column) {
            case 'project_name':
              valueA = a.project_name;
              valueB = b.project_name;
              break;
            case 'gate_name':
              valueA = `GATE${a.gate_number}: ${a.gate_name}`;
              valueB = `GATE${b.gate_number}: ${b.gate_name}`;
              break;
            case 'start_date':
              valueA = new Date(a.prev_insert_start || '1900-01-01');
              valueB = new Date(b.prev_insert_start || '1900-01-01');
              break;
            case 'end_date':
              valueA = new Date(a.cor_upload_end || '1900-01-01');
              valueB = new Date(b.cor_upload_end || '1900-01-01');
              break;
            case 'status':
              const statusA = getDateStatus(a.prev_insert_start, a.cor_upload_end);
              const statusB = getDateStatus(b.prev_insert_start, b.cor_upload_end);
              valueA = statusA.text;
              valueB = statusB.text;
              break;
            default:
              return 0;
          }
          
          if (valueA < valueB) return sortDirection === 'asc' ? -1 : 1;
          if (valueA > valueB) return sortDirection === 'asc' ? 1 : -1;
          return 0;
        });
        
        updateSortIcons();
        renderTable();
      };
      
      // Update sort icons
      const updateSortIcons = () => {
        $('.table-dashboard th').removeClass('sorted');
        $('.table-dashboard th .sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        
        if (sortColumn) {
          const $th = $(`.table-dashboard th[data-sort="${sortColumn}"]`);
          $th.addClass('sorted');
          const $icon = $th.find('.sort-icon');
          $icon.removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
      };
      
      // Render table
      const renderTable = () => {
        let tableHtml = '';
        filteredData.forEach(gate => {
          const dateStatus = getDateStatus(gate.prev_insert_start, gate.cor_upload_end);
          
          tableHtml += `<tr>
            <td>
              <div class="project-name">${gate.project_name}</div>
            </td>
            <td>
              <div class="gate-name">GATE${gate.gate_number}: ${gate.gate_name}</div>
            </td>
            <td>${formatDate(gate.prev_insert_start)}</td>
            <td>${formatDate(gate.cor_upload_end)}</td>
            <td>
              <span class="${dateStatus.class}">${dateStatus.text}</span>
            </td>
          </tr>`;
        });
        
        $dashboardTableBody.html(tableHtml);
        $resultCount.text(`${filteredData.length} data`);
      };
      
      // Calculate and display statistics
      const calculateStatistics = () => {
        const stats = {
          active: 0,
          upcoming: 0,
          completed: 0,
          total: filteredData.length,
          projects: new Set()
        };
        
        filteredData.forEach(gate => {
          // Add project to set for unique count
          stats.projects.add(gate.project_id);
          
          // Get date status
          const dateStatus = getDateStatus(gate.prev_insert_start, gate.cor_upload_end);
          
          switch(dateStatus.status) {
            case 'active':
              stats.active++;
              break;
            case 'upcoming':
              stats.upcoming++;
              break;
            case 'completed':
              stats.completed++;
              break;
          }
        });
        
        // Update UI
        $("#statActive").text(stats.active);
        $("#statUpcoming").text(stats.upcoming);
        $("#statCompleted").text(stats.completed);
        $("#statTotal").text(stats.total);
        $("#statProjects").text(stats.projects.size);
        
        // Show stats cards
        $("#statsCards").show();
      };

      // Tampilkan data dashboard
      const displayDashboardData = () => {
        if (filteredData.length === 0) {
          $dashboardTableBody.empty();
          $emptyState.show();
          $("#initialState").hide();
          $("#dashboardTableHeader").hide();
          $resultCount.text("0 data");
          $("#statsCards").hide();
          return;
        }
        
        $emptyState.hide();
        $("#initialState").hide();
        $("#dashboardTableHeader").show();
        
        // Calculate statistics
        calculateStatistics();
        
        // Default sort by project name
        if (!sortColumn) {
          sortColumn = 'project_name';
          sortDirection = 'asc';
        }
        
        sortData(sortColumn);
      };

      // --- Excel Export Function ---
      const exportToExcel = () => {
        if (filteredData.length === 0) {
          showError("Tidak ada data untuk diekspor");
          return;
        }

        try {
          // Prepare data for export
          const exportData = [];
          
          // Create header row
          const headerRow = [
            'Kegiatan',
            'Gate',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Status'
          ];
          exportData.push(headerRow);

          // Add data rows
          filteredData.forEach(gate => {
            const dateStatus = getDateStatus(gate.prev_insert_start, gate.cor_upload_end);
            
            const row = [
              gate.project_name,
              `GATE${gate.gate_number}: ${gate.gate_name}`,
              gate.prev_insert_start || '-',
              gate.cor_upload_end || '-',
              dateStatus.text
            ];
            exportData.push(row);
          });

          // Create workbook and worksheet
          const wb = XLSX.utils.book_new();
          const ws = XLSX.utils.aoa_to_sheet(exportData);

          // Set column widths
          const colWidths = [
            { wch: 30 }, // Kegiatan
            { wch: 40 }, // Gate
            { wch: 15 }, // Tanggal Mulai
            { wch: 15 }, // Tanggal Selesai
            { wch: 20 }  // Status
          ];
          ws['!cols'] = colWidths;

          // Add worksheet to workbook
          XLSX.utils.book_append_sheet(wb, ws, 'Dashboard Data');

          // Generate filename with timestamp
          const now = new Date();
          const timestamp = now.toISOString().slice(0, 19).replace(/:/g, '-');
          const filename = `Dashboard_Quality_Gates_${timestamp}.xlsx`;

          // Save file
          XLSX.writeFile(wb, filename);

          // Show success message
          const toast = $(`
            <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 10000; min-width: 250px; font-size: 0.8rem;">
              <i class="fas fa-check-circle me-2"></i>
              <strong style="font-size: 0.85rem;">Excel berhasil diekspor!</strong><br>
              <small style="font-size: 0.7rem;">File: ${filename}</small>
              <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
            </div>
          `);
          $('body').append(toast);
          
          // Auto remove after 4 seconds
          setTimeout(() => {
            toast.fadeOut(300, function() { $(this).remove(); });
          }, 4000);

        } catch (error) {
          console.error('Excel export error:', error);
          showError("Terjadi kesalahan saat mengekspor ke Excel: " + error.message);
        }
      };
      
      // Load filter options
      const loadFilterOptions = async () => {
        if (!currentUser) return;
        
        try {
          const projectsResponse = await makeAjaxRequest(API_URL, {
            action: "fetchAvailableProjects",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            year: "2025"
          });
          
          if (projectsResponse.status && projectsResponse.data) {
            filterProjectData = [
              { value: '', text: 'Semua Kegiatan' },
              ...projectsResponse.data.map(item => ({
                value: item.id,
                text: item.name
              }))
            ];
            
            filterProjectDropdown.setData(filterProjectData);
          }
          
        } catch(error) {
          console.error("Error loading filter options:", error);
        }
      };
      
      // Event Handlers
      $("#logoutBtn").on('click', function(){
        if (confirm('Apakah Anda yakin ingin logout?')) {
          window.location.href = 'sso_logout.php';
        }
      });
      
      $("#applyFilters").on('click', function(){
        const filters = {};
        
        const project = $("#filterProject").val();
        if (project) filters.filter_project = project;
        
        loadDashboardData(filters);
      });
      
      $("#clearFilters").on('click', function(){
        filterProjectDropdown.clear();
        loadDashboardData();
      });
      
      // Table sorting event handlers
      $(document).on('click', '.table-dashboard th[data-sort]', function() {
        const column = $(this).data('sort');
        sortData(column);
      });

      // Download Excel handler
      $(document).on('click', '#downloadExcel', function(){
        exportToExcel();
      });
      
      // Inisialisasi
      console.log('🚀 [DEBUG] Starting page initialization...');
      
      try {
        if (initUser()) {
          console.log('✅ [DEBUG] User initialization successful, setting up dropdown...');
          
          // Initialize searchable dropdown
          filterProjectDropdown = createSearchableDropdown(
            '#filterProjectSearch',
            '#filterProjectOptions',
            '#filterProject',
            [],
            'value',
            'text',
            (value, text) => {
              // Auto apply filter when selection changes
              const filters = {};
              if (value) filters.filter_project = value;
              loadDashboardData(filters);
            }
          );
          
          console.log('🎯 [DEBUG] Page initialization completed successfully!');
        } else {
          console.error('❌ [ERROR] User initialization failed!');
        }
      } catch (error) {
        console.error('💥 [ERROR] Exception during initialization:', error);
        alert('Terjadi kesalahan saat inisialisasi. Silakan refresh halaman atau hubungi admin.');
      }
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- SSO Wilayah Filter JavaScript -->
  <?php 
  // Inject wilayah JS only if functions exist and user is logged in
  if (function_exists('injectWilayahJS') && isset($_SESSION['sso_logged_in']) && $_SESSION['sso_logged_in']) {
    try {
      injectWilayahJS(); 
    } catch (Exception $e) {
      echo "<!-- Error loading wilayah JS: " . htmlspecialchars($e->getMessage()) . " -->";
    }
  }
  ?>
  
  <!-- Debug Info (hanya muncul jika ada parameter ?debug) -->
  <?php 
  if (isset($_GET['debug']) && function_exists('renderDebugWilayahInfo')) {
    try {
      renderDebugWilayahInfo(); 
    } catch (Exception $e) {
      echo "<!-- Error loading debug info: " . htmlspecialchars($e->getMessage()) . " -->";
    }
  }
  ?>
</body>
</html>