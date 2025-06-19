<?php
// index.php - Dashboard per wilayah untuk Quality Gates (Home Page)
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
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    
    .navbar {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--success-color) 100%);
      box-shadow: 0 4px 20px rgba(5, 150, 105, 0.15);
      border-bottom: none;
      padding: 0.25rem 0;
      margin-bottom: 0.75rem;
    }
    
    .navbar-brand {
      font-weight: 600;
      font-size: 1rem;
      color: white !important;
      padding-top: 0.25rem;
      padding-bottom: 0.25rem;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .user-avatar {
      width: 28px;
      height: 28px;
      background: rgba(255,255,255,0.2);
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
      backdrop-filter: blur(10px);
      font-size: 0.75rem;
    }
    
    .user-details {
      display: flex;
      flex-direction: column;
    }
    
    .user-name {
      font-weight: 600;
      color: white;
      font-size: 0.75rem;
      line-height: 1;
    }
    
    .user-role {
      font-size: 0.65rem;
      color: rgba(255,255,255,0.8);
      line-height: 1;
    }
    
    .btn-logout {
      background: rgba(255,255,255,0.1);
      border: 1px solid rgba(255,255,255,0.3);
      color: white;
      border-radius: 6px;
      padding: 0.25rem 0.6rem;
      font-size: 0.7rem;
      transition: all 0.2s ease;
      backdrop-filter: blur(10px);
    }
    
    .btn-logout:hover {
      background: var(--danger-color);
      border-color: var(--danger-color);
      color: white;
    }

    /* Compact Navigation Tabs - Integrated into navbar */
    .navbar-nav-tabs {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-left: 2rem;
    }
    
    .navbar-nav-tabs .nav-link {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: rgba(255, 255, 255, 0.9);
      font-weight: 500;
      padding: 0.3rem 0.8rem;
      border-radius: 6px;
      position: relative;
      transition: all 0.3s ease;
      text-decoration: none;
      font-size: 0.8rem;
      backdrop-filter: blur(10px);
    }
    
    .navbar-nav-tabs .nav-link:hover {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border-color: rgba(255, 255, 255, 0.4);
    }
    
    .navbar-nav-tabs .nav-link.active {
      background: white;
      color: var(--primary-color);
      border-color: white;
      font-weight: 600;
    }

    .navbar-nav-tabs .nav-link i {
      margin-right: 0.3rem;
      font-size: 0.75rem;
    }
    
    .container-fluid {
      max-width: 1600px;
      margin: 0 auto;
      padding: 1.5rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
      font-size: 2rem;
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
      padding: 0.5rem 0.75rem;
      font-weight: 500;
      font-size: 0.85rem;
      color: var(--dark-color);
      line-height: 1.2;
    }
    
    .card-body {
      padding: 0.75rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.3rem;
      color: var(--dark-color);
      font-size: 0.85rem;
      line-height: 1.2;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid var(--border-color);
      padding: 0.5rem 0.7rem;
      font-size: 0.85rem;
      background-color: #fff;
      transition: all 0.2s ease;
      line-height: 1.3;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
      outline: none;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 8px;
      padding: 0.6rem 1.2rem;
      font-weight: 500;
      font-size: 0.9rem;
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
      max-height: calc(100vh - 280px);
      min-height: 500px;
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
      padding: 1rem 1.25rem;
      font-size: 0.85rem;
      color: var(--primary-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: left;
      border-bottom: 2px solid var(--primary-color);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      cursor: pointer;
      user-select: none;
      transition: all 0.2s ease;
    }
    
    .table-dashboard td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      font-size: 0.9rem;
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
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.8rem;
      display: inline-block;
      animation: pulse 2s infinite;
    }
    
    .date-upcoming {
      background: rgba(255,159,10,0.12);
      color: var(--warning-color);
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.8rem;
      display: inline-block;
    }
    
    .date-completed {
      background: rgba(142,142,147,0.12);
      color: var(--neutral-color);
      padding: 0.25rem 0.75rem;
      border-radius: 20px;
      font-weight: 500;
      font-size: 0.8rem;
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
      font-size: 1.1rem;
      text-align: center;
      letter-spacing: 0.5px;
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
      border-radius: 8px 8px 0 0 !important;
      border-bottom: 1px solid var(--border-color) !important;
    }
    .dropdown-options {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid var(--border-color);
      border-top: none;
      border-radius: 0 0 8px 8px;
      max-height: 250px;
      overflow-y: auto;
      z-index: 99999;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.25);
    }
    
    .dropdown-option {
      padding: 0.75rem 1rem;
      cursor: pointer;
      border-bottom: 1px solid var(--border-color);
      transition: background-color 0.2s ease;
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
      padding: 0.75rem 1rem;
      color: var(--neutral-color);
      text-align: center;
      font-style: italic;
    }

    /* Statistics Cards - Ultra Compact */
    #statsCards .card {
      transition: all 0.2s ease;
      height: 100%;
    }
    
    #statsCards .card:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    #statsCards .card-body {
      padding: 0.5rem 0.4rem;
    }
    
    #statsCards h6 {
      font-size: 0.65rem;
      margin-bottom: 0.15rem;
      line-height: 1.1;
      font-weight: 600;
    }
    
    #statsCards h3 {
      font-size: 1.1rem;
      margin-bottom: 0;
      font-weight: 700;
    }
    
    #statsCards .d-flex {
      margin-bottom: 0.3rem;
      align-items: center;
    }
    
    #statsCards i {
      font-size: 0.8rem;
      margin-right: 0.3rem !important;
    }

    /* Responsif */
    @media (max-width: 992px) {
      .container-fluid {
        padding: 1.5rem;
      }
      
      .card-body {
        padding: 1.25rem;
      }
      
      .user-details {
        display: none;
      }
      
      #statsCards .col-md-2 {
        margin-bottom: 0.5rem;
      }
    }
    
    @media (max-width: 768px) {
      .container-fluid {
        padding: 1rem;
      }
      
      h1 {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
      }
      
      .card-header {
        padding: 1rem;
      }
      
      .card-body {
        padding: 1rem;
      }
      
      .table-dashboard th,
      .table-dashboard td {
        padding: 0.75rem;
        font-size: 0.8rem;
      }
      
      #statsCards .col-md-2 {
        width: 50%;
        margin-bottom: 0.5rem;
      }
      
      #statsCards h6 {
        font-size: 0.6rem;
      }
      
      #statsCards h3 {
        font-size: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-tasks me-2" style="background: rgba(255,255,255,0.2); padding: 6px; border-radius: 6px; font-size: 0.9rem;"></i>Quality Gates
      </a>
      
      <!-- Navigation Tabs - Integrated into navbar -->
      <div class="navbar-nav-tabs">
        <a class="nav-link active" href="index.php">
          <i class="fas fa-chart-bar"></i>Dashboard
        </a>
        <a class="nav-link" href="monitoring.php">
          <i class="fas fa-chart-line"></i>Monitoring
        </a>
      </div>
      
      <div class="user-info">
        <div class="user-avatar" id="userAvatar">
          <i class="fas fa-user"></i>
        </div>
        <div class="user-details">
          <div class="user-name" id="userName">Loading...</div>
          <div class="user-role" id="userRole">Loading...</div>
        </div>
        <button class="btn btn-logout" id="logoutBtn">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </button>
      </div>
    </div>
  </nav>

  <div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-2" id="statsCards" style="display: none;">
      <div class="col-md-2">
        <div class="card border-0 bg-success bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-play-circle text-success me-2"></i>
              <h6 class="mb-0 text-success fw-semibold">Sedang Berlangsung</h6>
            </div>
            <h3 class="mb-0 text-success fw-bold" id="statActive">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-warning bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-clock text-warning me-2"></i>
              <h6 class="mb-0 text-warning fw-semibold">Akan Datang</h6>
            </div>
            <h3 class="mb-0 text-warning fw-bold" id="statUpcoming">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-secondary bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-check-circle text-secondary me-2"></i>
              <h6 class="mb-0 text-secondary fw-semibold">Selesai</h6>
            </div>
            <h3 class="mb-0 text-secondary fw-bold" id="statCompleted">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-info bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-list text-info me-2"></i>
              <h6 class="mb-0 text-info fw-semibold">Total Gate</h6>
            </div>
            <h3 class="mb-0 text-info fw-bold" id="statTotal">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-primary bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-percentage text-primary me-2"></i>
              <h6 class="mb-0 text-primary fw-semibold">Progress</h6>
            </div>
            <h3 class="mb-0 text-primary fw-bold" id="statProgress">0%</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-dark bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-project-diagram text-dark me-2"></i>
              <h6 class="mb-0 text-dark fw-semibold">Kegiatan</h6>
            </div>
            <h3 class="mb-0 text-dark fw-bold" id="statProjects">0</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" id="filtersCard">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Data</span>
        <div>
          <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
            <i class="fas fa-times me-1"></i>Reset Filter
          </button>
        </div>
      </div>
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

    <!-- Results -->
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Project-Gate</span>
        <span id="resultCount" class="badge bg-primary rounded-pill">0 data</span>
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
      
      // Inisialisasi user
      const initUser = () => {
        const userData = localStorage.getItem('qg_user');
        if (!userData) {
          window.location.href = 'login.php';
          return false;
        }
        
        try {
          currentUser = JSON.parse(userData);
          if (!currentUser || !currentUser.username) {
            throw new Error('Invalid user data');
          }
          
          // Update UI dengan info user
          $("#userName").text(currentUser.name || currentUser.username);
          $("#userRole").text(currentUser.role_name || 'User');
          $("#userAvatar").text(currentUser.name ? currentUser.name.charAt(0).toUpperCase() : currentUser.username.charAt(0).toUpperCase());
          
          // Load filter options dan data
          loadFilterOptions();
          loadDashboardData();
          
          return true;
        } catch(e) {
          localStorage.removeItem('qg_user');
          window.location.href = 'login.php';
          return false;
        }
      };
      
      // Load data dashboard
      const loadDashboardData = async (filters = {}) => {
        if (!currentUser) return;
        
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
          
          const response = await makeAjaxRequest(API_URL, requestData);
          
          if (response.status && response.data) {
            dashboardData = response.data;
            filteredData = [...dashboardData];
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
        $resultCount.text(`${filteredData.length} gate`);
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
        
        // Calculate progress (completed / total * 100)
        const progress = stats.total > 0 ? Math.round((stats.completed / stats.total) * 100) : 0;
        
        // Update UI
        $("#statActive").text(stats.active);
        $("#statUpcoming").text(stats.upcoming);
        $("#statCompleted").text(stats.completed);
        $("#statTotal").text(stats.total);
        $("#statProgress").text(progress + '%');
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
          $resultCount.text("0 data");
          $("#statsCards").hide();
          return;
        }
        
        $emptyState.hide();
        $("#initialState").hide();
        
        // Calculate statistics
        calculateStatistics();
        
        // Default sort by project name
        if (!sortColumn) {
          sortColumn = 'project_name';
          sortDirection = 'asc';
        }
        
        sortData(sortColumn);
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
          localStorage.removeItem('qg_user');
          window.location.href = 'login.php';
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
      
      // Inisialisasi
      if (initUser()) {
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
      }
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 