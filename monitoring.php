<?php
// monitoring.php
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
      max-width: 1600px; /* Wider container */
      margin: 0 auto;
      padding: 1.5rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 2rem;
      color: var(--dark-color);
      font-size: 2rem;
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

    .card {
      border-radius: 12px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.04);
      border: none;
      margin-bottom: 1.5rem;
      background-color: #ffffff;
      overflow: visible;
    }
    
    .card-body {
      overflow: visible;
    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid var(--border-color);
      padding: 0.75rem 1rem;
      font-weight: 500;
      font-size: 0.9rem;
      color: var(--dark-color);
    }
    
    .card-body {
      padding: 1rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid var(--border-color);
      padding: 0.6rem 0.8rem;
      font-size: 0.9rem;
      background-color: #fff;
      transition: all 0.2s ease;
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
    
    .btn-primary:active {
      transform: translateY(0);
    }
    
    .table-wrapper {
      overflow: auto;
      margin: 0;
      max-height: calc(100vh - 280px);
      min-height: 500px;
    }
    
    .table-monitoring {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
    }
    
    /* Fix gap between sticky columns */
    .table-monitoring th:nth-child(1),
    .table-monitoring td:nth-child(1),
    .table-monitoring th:nth-child(2),
    .table-monitoring td:nth-child(2),
    .table-monitoring th:nth-child(3),
    .table-monitoring td:nth-child(3),
    .table-monitoring th:nth-child(4),
    .table-monitoring td:nth-child(4),
    .table-monitoring th:nth-child(5),
    .table-monitoring td:nth-child(5),
    .table-monitoring th:nth-child(6),
    .table-monitoring td:nth-child(6) {
      box-shadow: 2px 0 0 0 #fff, -1px 0 0 0 #fff;
      z-index: 10;
      box-sizing: border-box;
      position: relative;
    }
    
    /* Add vertical lines using pseudo-elements that stay in place */
    .table-monitoring th:nth-child(1)::after,
    .table-monitoring td:nth-child(1)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th:nth-child(2)::after,
    .table-monitoring td:nth-child(2)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th:nth-child(3)::after,
    .table-monitoring td:nth-child(3)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th:nth-child(4)::after,
    .table-monitoring td:nth-child(4)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th:nth-child(5)::after,
    .table-monitoring td:nth-child(5)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 1px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th:nth-child(6)::after,
    .table-monitoring td:nth-child(6)::after {
      content: '';
      position: absolute;
      right: 0;
      top: 0;
      bottom: 0;
      width: 2px;
      background-color: #000;
      z-index: 15;
    }
    
    .table-monitoring th {
      border: 1px solid #000;
      padding: 8px;
      text-align: center;
      font-weight: normal;
      background-color: #fff;
    }
    
    .table-monitoring td {
      border: 1px solid #000;
      padding: 8px;
      vertical-align: middle;
    }
    
    /* Status badges */
    .status-badge {
      display: inline-block;
    }
    
    /* Gate dan UK codes */
    .gate-code, .uk-code {
      font-weight: normal;
      color: #000;
      margin-right: 5px;
    }
    
    /* Kolom-kolom */
    .date-column {
      text-align: center;
    }
    
    .status-column {
      text-align: center;
    }
    
    /* Header */
    .table-monitoring th:first-child,
    .table-monitoring th:nth-child(2) {
      text-align: center;
    }
    
    /* Konten Gate dan UK */
    .table-monitoring td:first-child,
    .table-monitoring td:nth-child(2) {
      text-align: left;
      font-weight: normal;
      color: #000;
    }
    
    .table-monitoring th:nth-child(3),
    .table-monitoring td:nth-child(3) {
      text-align: center;
    }
    
    .activity-number {
      background: #000;
      color: white;
      padding: 2px 6px;
      margin-right: 8px;
    }
    
    /* Enhanced Loading Spinner */
    #spinner {
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
    
    /* Region header styles */
    .region-header {
      font-weight: normal;
      text-align: center;
      background: #fff !important;
      color: #000 !important;
    }
    
    /* Frozen columns */
    .table-monitoring th:nth-child(1), /* Gate */
    .table-monitoring td:nth-child(1) {
      position: sticky;
      left: 0;
      background-color: #fff;
      z-index: 10;
      width: 201px;
      min-width: 201px;
      max-width: 201px;
      margin-right: -1px;
    }
    
    .table-monitoring th:nth-child(2), /* Ukuran Kualitas */
    .table-monitoring td:nth-child(2) {
      position: sticky;
      left: 200px;
      background-color: #fff;
      z-index: 10;
      width: 221px;
      min-width: 221px;
      max-width: 221px;
      margin-right: -1px;
    }
    
    .table-monitoring th:nth-child(3), /* Level */
    .table-monitoring td:nth-child(3) {
      position: sticky;
      left: 420px;
      background-color: #fff;
      z-index: 10;
      width: 101px;
      min-width: 101px;
      max-width: 101px;
      text-align: center;
      margin-right: -1px;
    }
    
    .table-monitoring th:nth-child(4), /* Aktivitas */
    .table-monitoring td:nth-child(4) {
      position: sticky;
      left: 520px;
      background-color: #fff;
      z-index: 10;
      width: 281px;
      min-width: 281px;
      max-width: 281px;
      margin-right: -1px;
    }
    
    .table-monitoring th:nth-child(5), /* Tanggal Mulai */
    .table-monitoring td:nth-child(5) {
      position: sticky;
      left: 800px;
      background-color: #fff;
      z-index: 10;
      width: 121px;
      min-width: 121px;
      max-width: 121px;
      text-align: center;
      margin-right: -1px;
    }
    
    .table-monitoring th:nth-child(6), /* Tanggal Selesai */
    .table-monitoring td:nth-child(6) {
      position: sticky;
      left: 920px;
      background-color: #fff;
      z-index: 10;
      width: 121px;
      min-width: 121px;
      max-width: 121px;
      text-align: center;
      margin-right: -1px;
    }
    
    /* Date in range */
    .date-in-range {
      color: #000;
      font-weight: normal;
    }
    
    /* Untuk kompatibilitas */
    .date-column {
      text-align: center;
    }
    
    /* Activity number */
    .activity-number {
      display: inline-block;
      background: #000;
      color: white;
      padding: 2px 6px;
      margin-right: 8px;
    }
    
    /* Status column */
    .status-column {
      text-align: center;
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
        margin-bottom: 1.5rem;
      }
      
      .card-header {
        padding: 1rem;
      }
      
      .card-body {
        padding: 1rem;
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
  <nav class="navbar navbar-expand-lg" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--success-color) 100%); box-shadow: 0 4px 20px rgba(5, 150, 105, 0.15); border-bottom: none; padding: 0.25rem 0; margin-bottom: 0.75rem;">
    <div class="container-fluid">
              <a class="navbar-brand" href="index.php" style="font-weight: 600; font-size: 1rem; color: white !important; padding-top: 0.25rem; padding-bottom: 0.25rem;">
        <i class="fas fa-tasks me-2" style="background: rgba(255,255,255,0.2); padding: 6px; border-radius: 6px; font-size: 0.9rem;"></i>Quality Gates
      </a>
      
      <!-- Navigation Tabs - Integrated into navbar -->
      <div class="navbar-nav-tabs">
        <a class="nav-link" href="index.php">
          <i class="fas fa-chart-bar"></i>Dashboard
        </a>
        <a class="nav-link active" href="monitoring.php">
          <i class="fas fa-chart-line"></i>Monitoring
        </a>
      </div>
      
      <div style="display: flex; align-items: center; gap: 0.5rem;">
        <div style="width: 28px; height: 28px; background: rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; backdrop-filter: blur(10px); font-size: 0.75rem;" id="userAvatar">
          <i class="fas fa-user"></i>
        </div>
        <div style="display: flex; flex-direction: column;">
          <div style="font-weight: 600; color: white; font-size: 0.75rem; line-height: 1;" id="userName">Loading...</div>
          <div style="font-size: 0.65rem; color: rgba(255,255,255,0.8); line-height: 1;" id="userRole">Loading...</div>
        </div>
        <button class="btn" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; border-radius: 6px; padding: 0.25rem 0.6rem; font-size: 0.7rem; transition: all 0.2s ease; backdrop-filter: blur(10px);" id="logoutBtn">
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
              <i class="fas fa-check-circle text-success me-2"></i>
              <h6 class="mb-0 text-success fw-semibold">Sudah</h6>
            </div>
            <h3 class="mb-0 text-success fw-bold" id="statSudah">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-danger bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-times-circle text-danger me-2"></i>
              <h6 class="mb-0 text-danger fw-semibold">Belum</h6>
            </div>
            <h3 class="mb-0 text-danger fw-bold" id="statBelum">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-secondary bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-minus-circle text-secondary me-2"></i>
              <h6 class="mb-0 text-secondary fw-semibold">Tidak Perlu</h6>
            </div>
            <h3 class="mb-0 text-secondary fw-bold" id="statTidakPerlu">0</h3>
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
            <h3 class="mb-0 text-warning fw-bold" id="statAkanDatang">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-primary bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-play-circle text-primary me-2"></i>
              <h6 class="mb-0 text-primary fw-semibold">Berlangsung</h6>
            </div>
            <h3 class="mb-0 text-primary fw-bold" id="statBerlangsung">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-info bg-opacity-10">
          <div class="card-body text-center p-3">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-list text-info me-2"></i>
              <h6 class="mb-0 text-info fw-semibold">Total</h6>
            </div>
            <h3 class="mb-0 text-info fw-bold" id="statTotal">0</h3>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Input Filters -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Data</span>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-2">
            <label for="yearSelect" class="form-label">Tahun</label>
            <select id="yearSelect" class="form-select">
              <option value="2023">2023</option>
              <option value="2024">2024</option>
              <option value="2025" selected>2025</option>
            </select>
          </div>
          <div id="projectSelectContainer" class="col-md-5">
            <label for="projectSearch" class="form-label">Pilih Kegiatan</label>
            <div class="searchable-dropdown">
              <input type="text" class="form-control dropdown-search-input" id="projectSearch" placeholder="Cari kegiatan..." disabled>
              <div class="dropdown-options" id="projectOptions" style="display: none;"></div>
              <input type="hidden" id="projectSelect">
            </div>
          </div>
          <div class="col-md-3" id="regionSelectContainer">
            <label for="regionSearch" class="form-label">Pilih Cakupan Wilayah</label>
            <div class="searchable-dropdown">
              <input type="text" class="form-control dropdown-search-input" id="regionSearch" placeholder="Cari wilayah..." disabled>
              <div class="dropdown-options" id="regionOptions" style="display: none;"></div>
              <input type="hidden" id="regionSelect">
            </div>
          </div>
          <div id="loadButtonContainer" class="col-md-2 d-flex align-items-end">
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

  <!-- Enhanced Loading Spinner -->
  <div id="spinner">
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
      <div class="spinner-text">Memuat data monitoring...</div>
    </div>
  </div>

  <script>
    $(function(){
      const API_URL = "api.php";
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      let activityData = {}; // Untuk menyimpan data status per aktivitas per wilayah
      let currentUser = null; // User yang sedang login

      // Cache selector DOM
      const $yearSelect    = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect  = $("#regionSelect");
      const $projectSearch = $("#projectSearch");
      const $regionSearch  = $("#regionSearch");
      const $projectOptions = $("#projectOptions");
      const $regionOptions = $("#regionOptions");
      const $resultsContainer = $("#resultsContainer");
      const $spinner       = $("#spinner");
      
      let projectData = [];
      let regionDropdownData = []; // Renamed to avoid conflict
      
      // Initialize searchable dropdowns
      let projectDropdown, regionDropdown;

      // --- Helper Functions ---

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
            $search.val('');
            $hidden.val('');
            $options.hide();
          }
        };
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
          
          // Atur layout berdasarkan role user
          if (currentUser.prov === "00" && currentUser.kab === "00") {
            // User pusat - tampilkan dropdown wilayah
            $("#regionSelectContainer").show();
            $("#projectSelectContainer").removeClass("col-md-7").addClass("col-md-5");
            $("#loadButtonContainer").removeClass("col-md-3").addClass("col-md-2");
          } else {
            // User provinsi/kabupaten - sembunyikan dropdown wilayah
            $("#regionSelectContainer").hide();
            $("#projectSelectContainer").removeClass("col-md-5").addClass("col-md-7");
            $("#loadButtonContainer").removeClass("col-md-2").addClass("col-md-3");
          }
          
          return true;
        } catch(e) {
          localStorage.removeItem('qg_user');
          window.location.href = 'login.php';
          return false;
        }
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
        Swal.fire({
          icon: 'error',
          title: 'Terjadi Kesalahan',
          text: message,
          confirmButtonColor: '#0071e3'
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
        const rawLevel = measurement.assessment_level;
        const level = parseInt(rawLevel || 1);
        
        let result;
        if (level === 1) result = "Pusat";
        else if (level === 2) result = "Provinsi";
        else if (level === 3) result = "Kabupaten";
        else result = "Tidak diketahui";
        
        return result;
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
          
          // Cek upload bukti dari tabel project_doc_preventives
          const docResponse = await getDataFromCacheOrApi(
            'docPreventives',
            'fetchDocPreventives',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          if (docResponse.status && docResponse.data.length > 0) {
            // Cek apakah ada file yang sudah di-upload dan tidak dihapus
            const uploadedDoc = docResponse.data.find(doc => 
              doc.filename && doc.filename.trim() !== '' && !doc.is_deleted
            );
            return uploadedDoc ? "Sudah diunggah" : "Belum diunggah";
          }
          
          return "Belum diunggah";
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
        // Assessment data adalah array dengan format [{"idm":259,"ass":"3"},{"idm":260,"ass":"1"}]
        let assessmentStatus = "Belum dinilai";
        let isAssessed = false;
        let isApproved = false;
        let assessmentValue = null;
        
        if (assessmentsResponse.status && assessmentsResponse.data.length > 0) {
          const assessmentRecord = assessmentsResponse.data[0];
          
          // Parse assessment JSON array
          if (assessmentRecord.assessment && Array.isArray(assessmentRecord.assessment)) {
            const measurementAssessment = assessmentRecord.assessment.find(item => 
              item.idm && item.idm == measurement.id
            );
            
            if (measurementAssessment && measurementAssessment.ass) {
              assessmentValue = measurementAssessment.ass;
              
              if (assessmentValue === "1" || assessmentValue === 1) {
                assessmentStatus = "Sudah dinilai (merah)";
              } else if (assessmentValue === "2" || assessmentValue === 2) {
                assessmentStatus = "Sudah dinilai (kuning)";
              } else if (assessmentValue === "3" || assessmentValue === 3) {
                assessmentStatus = "Sudah dinilai (hijau)";
              }
              
              isAssessed = assessmentStatus.startsWith("Sudah dinilai");
            }
          }
          
          // Check approval status
          isApproved = assessmentRecord.state === "1" || assessmentRecord.status === "1";
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
          
          // Cek upload bukti dari tabel project_doc_correctives
          const docCorResponse = await getDataFromCacheOrApi(
            'docCorrectives',
            'fetchDocCorrectives',
            {
              year, id_project: selectedProject, id_gate: gate.id,
              id_measurement: measurement.id, prov, kab
            }
          );
          
          if (docCorResponse.status && docCorResponse.data.length > 0) {
            // Cek apakah ada file yang sudah di-upload dan tidak dihapus
            const uploadedDoc = docCorResponse.data.find(doc => 
              doc.filename && doc.filename.trim() !== '' && !doc.is_deleted
            );
            return uploadedDoc ? "Sudah diunggah" : "Belum diunggah";
          }
          
          return "Belum diunggah";
        }
        
        return "Tidak tersedia";
      };

      // Calculate and display statistics for monitoring
      const calculateMonitoringStatistics = (regions) => {
        const stats = {
          sudah: 0,
          belum: 0,
          tidakPerlu: 0,
          akanDatang: 0,
          berlangsung: 0,
          total: 0
        };
        
        // Count statuses from all activities and regions
        for (const key in activityData) {
          const activity = activityData[key];
          
          // Check if activity dates are in range for "berlangsung"
          const isInDateRange = isDateInRange(activity.start, activity.end);
          if (isInDateRange) {
            stats.akanDatang++; // Will be corrected below
          }
          
          // Count statuses for each region
          regions.forEach(region => {
            const status = activity.statuses[region.id] || "Tidak tersedia";
            stats.total++;
            
            if (status.startsWith('Sudah')) {
              stats.sudah++;
            } else if (status.startsWith('Belum')) {
              stats.belum++;
            } else if (status === 'Tidak perlu') {
              stats.tidakPerlu++;
            } else if (isInDateRange) {
              stats.berlangsung++;
            }
          });
        }
        
        // Count activities that are "akan datang" (future dates)
        for (const key in activityData) {
          const activity = activityData[key];
          const today = new Date();
          today.setHours(0, 0, 0, 0);
          
          if (activity.start) {
            const startDate = new Date(activity.start);
            startDate.setHours(0, 0, 0, 0);
            
            if (today < startDate) {
              stats.akanDatang += regions.length; // Multiply by regions count
            }
          }
        }
        
        // Update UI
        $("#statSudah").text(stats.sudah);
        $("#statBelum").text(stats.belum);
        $("#statTidakPerlu").text(stats.tidakPerlu);
        $("#statAkanDatang").text(stats.akanDatang);
        $("#statBerlangsung").text(stats.berlangsung);
        $("#statTotal").text(stats.total);
        
        // Show stats cards
        $("#statsCards").show();
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
            <div class="card-header d-flex justify-content-between align-items-center">
              <span>Hasil Monitoring</span>
              <span id="resultCount" class="badge bg-primary rounded-pill">${Object.keys(activityData).length} aktivitas</span>
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
        
        // Versi tabel tanpa merge - setiap baris menampilkan semua data lengkap
        const orderedActivities = [];
        
        // 1. Ambil semua aktivitas dan urutkan berdasarkan gate, UK, dan proses
        for (const key in activityData) {
          const data = activityData[key];
          orderedActivities.push(data);
        }
        
        // 2. Urutkan aktivitas berdasarkan proses
        const activityOrder = {
          "Pengisian nama pelaksana aksi preventif": 1,
          "Upload bukti pelaksanaan aksi preventif": 2,
          "Penilaian ukuran kualitas": 3,
          "Approval Gate oleh Sign-off": 4,
          "Pengisian pelaksana aksi korektif": 5,
          "Upload bukti pelaksanaan aksi korektif": 6
        };
        
        // 3. Urutkan seluruh aktivitas berdasarkan gate, UK, dan proses
        orderedActivities.sort((a, b) => {
          // Ekstrak nomor gate
          const gateNumA = parseInt(a.gate.match(/GATE(\d+)/)[1]);
          const gateNumB = parseInt(b.gate.match(/GATE(\d+)/)[1]);
          
          if (gateNumA !== gateNumB) {
            return gateNumA - gateNumB;
          }
          
          // Ekstrak nomor UK
          const ukNumA = parseInt(a.uk.match(/UK(\d+)/)[1]);
          const ukNumB = parseInt(b.uk.match(/UK(\d+)/)[1]);
          
          if (ukNumA !== ukNumB) {
            return ukNumA - ukNumB;
          }
          
          // Urutkan berdasarkan proses
          return activityOrder[a.activity] - activityOrder[b.activity];
        });
        
        // 4. Buat baris untuk setiap aktivitas (tanpa merge)
        for (let i = 0; i < orderedActivities.length; i++) {
          const data = orderedActivities[i];
          const rowClass = i % 2 === 0 ? 'uk-group-even' : 'uk-group-odd';
          
          // Ambil nomor aktivitas (1-6) berdasarkan activityOrder
          const activityNumber = activityOrder[data.activity];
          
          // Konversi level assessment
          const rawLevel = data.assessmentLevel || 1;
          let levelLabel;
          if (rawLevel === 1) levelLabel = "Pusat";
          else if (rawLevel === 2) levelLabel = "Provinsi";
          else if (rawLevel === 3) levelLabel = "Kabupaten";
          else levelLabel = "Tidak diketahui";
          
          tableHtml += `<tr class="${rowClass}">`;
          
          // Tampilkan semua kolom untuk setiap baris (tanpa rowspan)
          tableHtml += `
            <td>${data.gate}</td>
            <td>${data.uk}</td>
            <td style="text-align: center;">${levelLabel}</td>
          `;
          
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
        
        tableHtml += `
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `;
        
        $resultsContainer.html(tableHtml);
        
        // Calculate and display statistics
        calculateMonitoringStatistics(regions);
      };

      // --- Fungsi untuk Load Data (Projects & Regions) ---

      const loadProjects = async () => {
        if (!currentUser || !year) return;
        
        projectDropdown.setValue('', 'Memuat data...');
        projectDropdown.disable();
        
        try {
          const response = await makeAjaxRequest(API_URL, { 
            action: "fetchAvailableProjects", 
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            year: year
          });
          
          if (response.status && response.data.length) {
            projectData = response.data.map(project => ({
              value: project.id,
              text: project.name
            }));
            
            projectDropdown.setData(projectData);
            projectDropdown.setValue('', 'Pilih Kegiatan');
            projectDropdown.enable();
          } else {
            projectData = [];
            projectDropdown.setValue('', 'Tidak ada kegiatan ditemukan');
            projectDropdown.disable();
          }
        } catch (error) {
          showError("Gagal memuat daftar kegiatan");
          projectData = [];
          projectDropdown.setValue('', 'Pilih Kegiatan');
          projectDropdown.enable();
        }
      };

      const loadRegions = async () => {
        if (!currentUser) return;
        
        try {
          const response = await makeAjaxRequest(API_URL, { action: "fetchCoverages", id_project: selectedProject });
          coverageData = [];
          
          if (!response.status)
            throw new Error("Gagal memuat data wilayah");
          
          // Filter berdasarkan role user
          let filteredData = [];
          
          if (currentUser.prov === "00" && currentUser.kab === "00") {
            // Pusat - bisa lihat semua, dan perlu dropdown
            filteredData = response.data || [];
            
            // Tambahkan opsi Pusat untuk user pusat
            coverageData = [{
              id: "pusat",
              prov: "00", 
              kab: "00",
              name: "Pusat - Nasional"
            }];
            
            if (filteredData.length > 0) {
              const apiData = filteredData.map(cov => ({
                id: `${cov.prov}${cov.kab}`,
                prov: cov.prov,
                kab: cov.kab,
                name: cov.name
              }));
              coverageData = [...coverageData, ...apiData];
            }
            

            
            // Filter provinsi (kab == "00" dan prov tidak "00") untuk dropdown
            let provinces = coverageData.filter(cov => cov.kab === "00" && cov.prov !== "00");
            
            // Jika tidak ada provinsi tertentu tapi ada kabupatennya, buat virtual province
            const kabupatenByProv = {};
            coverageData.filter(cov => cov.kab !== "00" && cov.prov !== "00").forEach(kab => {
              if (!kabupatenByProv[kab.prov]) {
                kabupatenByProv[kab.prov] = [];
              }
              kabupatenByProv[kab.prov].push(kab);
            });
            
            // Tambahkan virtual provinces untuk provinsi yang tidak punya entry provinsi tapi punya kabupaten
            Object.keys(kabupatenByProv).forEach(provCode => {
              const existingProvince = provinces.find(p => p.prov === provCode);
              if (!existingProvince) {
                // Buat virtual province
                const firstKab = kabupatenByProv[provCode][0];
                let provinceName = `Provinsi ${provCode}`;
                
                // Coba ekstrak nama provinsi dari nama kabupaten
                if (firstKab && firstKab.name) {
                  const kabName = firstKab.name;
                  if (kabName.includes(' - ')) {
                    // Format: "Nama Provinsi - Nama Kabupaten"
                    provinceName = kabName.split(' - ')[0];
                  } else {
                    // Coba beberapa pattern umum nama kabupaten
                    const provNames = {
                      '11': 'ACEH', '12': 'SUMATERA UTARA', '13': 'SUMATERA BARAT', 
                      '14': 'RIAU', '15': 'JAMBI', '16': 'SUMATERA SELATAN',
                      '17': 'BENGKULU', '18': 'LAMPUNG', '19': 'KEPULAUAN BANGKA BELITUNG',
                      '21': 'KEPULAUAN RIAU', '31': 'DKI JAKARTA', '32': 'JAWA BARAT',
                      '33': 'JAWA TENGAH', '34': 'DI YOGYAKARTA', '35': 'JAWA TIMUR',
                      '36': 'BANTEN', '51': 'BALI', '52': 'NUSA TENGGARA BARAT',
                      '53': 'NUSA TENGGARA TIMUR', '61': 'KALIMANTAN BARAT',
                      '62': 'KALIMANTAN TENGAH', '63': 'KALIMANTAN SELATAN',
                      '64': 'KALIMANTAN TIMUR', '65': 'KALIMANTAN UTARA',
                      '71': 'SULAWESI UTARA', '72': 'SULAWESI TENGAH',
                      '73': 'SULAWESI SELATAN', '74': 'SULAWESI TENGGARA',
                      '75': 'GORONTALO', '76': 'SULAWESI BARAT',
                      '81': 'MALUKU', '82': 'MALUKU UTARA',
                      '91': 'PAPUA BARAT', '94': 'PAPUA'
                    };
                    provinceName = provNames[provCode] || `Provinsi ${provCode}`;
                  }
                }
                
                const virtualProvince = {
                  id: `${provCode}00`,
                  prov: provCode,
                  kab: "00",
                  name: provinceName,
                  isVirtual: true
                };
                
                // Tambahkan ke coverageData dan provinces
                coverageData.push(virtualProvince);
                provinces.push(virtualProvince);
              }
            });
            
            // Sort provinces by code (prov) instead of alphabetically
            provinces.sort((a, b) => a.prov.localeCompare(b.prov));
            
            regionDropdownData = [
              { value: "pusat", text: "Pusat - Nasional" },
              ...provinces.map(province => ({
                value: province.id,
                text: province.name
              }))
            ];
            
            regionDropdown.setData(regionDropdownData);
            regionDropdown.setValue("pusat", "Pusat - Nasional");
            regionDropdown.enable();
            
            // Set default ke pusat
            selectedRegion = "pusat";
            
          } else if (currentUser.prov !== "00" && currentUser.kab === "00") {
            // User provinsi - otomatis pilih provinsi + kabupaten di dalamnya
            filteredData = (response.data || []).filter(cov => 
              cov.prov === currentUser.prov
            );
            
            if (filteredData.length > 0) {
              const apiData = filteredData.map(cov => ({
                id: `${cov.prov}${cov.kab}`,
                prov: cov.prov,
                kab: cov.kab,
                name: cov.name
              }));
              coverageData = apiData;
              
              // Cek apakah ada data untuk provinsi user
              const userProvince = coverageData.find(cov => 
                cov.prov === currentUser.prov && cov.kab === "00"
              );
              
              if (userProvince) {
                // Jika ada data provinsi, pilih provinsi
                selectedRegion = userProvince.id;
              } else {
                // Jika tidak ada data provinsi, buat virtual region provinsi
                // Ambil nama provinsi dari kabupaten pertama (potong nama kabupaten)
                const firstKabupaten = coverageData.find(cov => 
                  cov.prov === currentUser.prov && cov.kab !== "00"
                );
                
                if (firstKabupaten) {
                  // Buat virtual provinsi entry
                  // Ekstrak nama provinsi dari nama kabupaten
                  let provinceName = `Prov. ${currentUser.prov}`;
                  
                  // Coba ekstrak dari nama kabupaten
                  const kabName = firstKabupaten.name;
                  if (kabName.includes(' - ')) {
                    // Format: "Nama Provinsi - Nama Kabupaten"
                    provinceName = kabName.split(' - ')[0];
                  } else {
                    // Jika tidak ada format khusus, gunakan nama generik
                    provinceName = `Provinsi (${currentUser.prov})`;
                  }
                  
                  const virtualProvince = {
                    id: `${currentUser.prov}00`,
                    prov: currentUser.prov,
                    kab: "00",
                    name: provinceName,
                    isVirtual: true // Mark sebagai virtual untuk reference
                  };
                  
                  // Tambahkan virtual provinsi ke coverageData
                  coverageData.unshift(virtualProvince);
                  selectedRegion = virtualProvince.id;
                } else {
                  throw new Error("Data provinsi Anda tidak ditemukan");
                }
              }
            } else {
              throw new Error("Tidak ada data untuk provinsi Anda");
            }
            
          } else {
            // User kabupaten - otomatis pilih kabupaten spesifik
            filteredData = (response.data || []).filter(cov => 
              cov.prov === currentUser.prov && cov.kab === currentUser.kab
            );
            
            if (filteredData.length > 0) {
              const apiData = filteredData.map(cov => ({
                id: `${cov.prov}${cov.kab}`,
                prov: cov.prov,
                kab: cov.kab,
                name: cov.name
              }));
              coverageData = apiData;
              
              // Auto-select kabupaten user (tidak perlu dropdown)
              selectedRegion = coverageData[0].id;
            } else {
              throw new Error("Tidak ada data untuk kabupaten/kota Anda");
            }
          }
          
        } catch (error) {
          showError(error.message || "Gagal memuat daftar wilayah");
          if (currentUser.prov === "00" && currentUser.kab === "00") {
            $regionSelect.empty().append('<option value="">Pilih Cakupan Wilayah</option>');
          }
          coverageData = [];
          selectedRegion = null;
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
          allActions: {},
          docPreventives: {},
          docCorrectives: {}
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
            
            // Filter region berdasarkan assessment_level
            const applicableRegions = regions.filter(region => {
              if (assessmentLevel === 1) {
                // Khusus pusat (00, 00)
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
            
            // Optimasi untuk fetchAllActions: gunakan cache untuk kabupaten dari provinsi yang sama
            // Pengelompokan region berdasarkan provinsi untuk level 3 (kabupaten)
            const provinceGroups = {};
            if (assessmentLevel === 3) {
              applicableRegions.forEach(region => {
                if (!provinceGroups[region.prov]) {
                  provinceGroups[region.prov] = [];
                }
                provinceGroups[region.prov].push(region);
              });
            }
            
            // Pre-load data hanya untuk region yang sesuai dengan assessment_level
            if (assessmentLevel === 3) {
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
              // Untuk level pusat dan provinsi, tetap gunakan cara normal
              for (const region of applicableRegions) {
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
                const rawAssessmentLevel = parseInt(measurement.assessment_level || 1);
                activityData[activityKey] = {
                  gate: gateName,
                  uk: ukName,
                  ukLevel: ukLevel,
                  assessmentLevel: rawAssessmentLevel, // Simpan level mentah juga
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

      // Logout handler
      $("#logoutBtn").on('click', function(){
        if (confirm('Apakah Anda yakin ingin logout?')) {
          localStorage.removeItem('qg_user');
          window.location.href = 'login.php';
        }
      });

      $yearSelect.on('change', async function(){
        year = $(this).val();
        
        // Reset dropdowns
        projectDropdown.clear();
        projectDropdown.disable();
        
        // Reset dropdown region hanya untuk user pusat
        if (currentUser.prov === "00" && currentUser.kab === "00") {
          regionDropdown.clear();
          regionDropdown.disable();
        }
        
        selectedProject = null;
        selectedRegion  = null;
        
        // Load projects jika tahun dipilih
        if (year && currentUser) {
          await loadProjects();
        }
      });

      // Project selection handler akan diatur saat inisialisasi dropdown
      
      // Region selection handler akan diatur saat inisialisasi dropdown

      $("#loadData").on('click', async function(){
        if (!selectedProject) {
          showError("Silakan pilih kegiatan terlebih dahulu");
          return;
        }
        
        if (!selectedRegion) {
          if (currentUser.prov === "00" && currentUser.kab === "00") {
            showError("Silakan pilih cakupan wilayah terlebih dahulu");
          } else {
            showError("Gagal memuat data wilayah Anda. Silakan refresh halaman.");
          }
          return;
        }
        $spinner.fadeIn(200);
        $resultsContainer.empty();
        try {
          // Tentukan daftar wilayah yang akan diproses
          let regionsToProcess = [];
          
          if (selectedRegion === "pusat") {
            // Level Pusat - Hanya kolom pusat
            regionsToProcess = [{ id: "pusat", prov: "00", kab: "00", name: "Pusat" }];
          } else {
            // Cari data wilayah dengan multiple fallback methods
            let selectedRegionData = null;
            
            // Method 1: Exact match (strict equality)
            selectedRegionData = coverageData.find(r => r.id === selectedRegion);
            
            // Method 2: Loose equality (in case of type mismatch)
            if (!selectedRegionData) {
              selectedRegionData = coverageData.find(r => r.id == selectedRegion);
            }
            
            // Method 3: String conversion and match
            if (!selectedRegionData && selectedRegion) {
              const selectedStr = String(selectedRegion);
              selectedRegionData = coverageData.find(r => String(r.id) === selectedStr);
            }
            
            // Method 4: Parse selectedRegion and match by prov+kab
            if (!selectedRegionData && selectedRegion) {
              const selectedStr = String(selectedRegion);
              if (selectedStr.length === 4 && /^\d{4}$/.test(selectedStr)) {
                const prov = selectedStr.substring(0, 2);
                const kab = selectedStr.substring(2, 4);
                selectedRegionData = coverageData.find(r => r.prov === prov && r.kab === kab);
              }
            }
            
            if (!selectedRegionData) {
              throw new Error(`Data wilayah tidak ditemukan untuk: ${selectedRegion}. Available: ${coverageData.map(r => r.id).join(', ')}`);
            }
              
            if (selectedRegionData.kab === "00") {
            // Level Provinsi - Kolom provinsi + semua kabupaten di provinsi itu
            const prov = selectedRegionData.prov;
            
            // Cek apakah ini virtual province (tidak ada data coverage untuk provinsi)
            if (selectedRegionData.isVirtual) {
              // Untuk virtual province, hanya tampilkan kabupaten-kabupaten saja
              const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
              regionsToProcess = kabupatenList;
            } else {
              // Untuk provinsi normal, tampilkan provinsi + kabupaten
              regionsToProcess = [
                { id: `${prov}00`, prov: prov, kab: "00", name: selectedRegionData.name }
              ];
              
              // Tambahkan kabupaten yang ada di provinsi ini
              const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
              regionsToProcess = [...regionsToProcess, ...kabupatenList];
            }
            } else {
              // Level Kabupaten - Hanya kolom kabupaten yang dipilih
              regionsToProcess = [selectedRegionData];
            }
          }
          
          // Proses data untuk semua wilayah terpilih
          await processData(regionsToProcess);
          
          // Tampilkan hasil dalam format tabel
          displayResultTable(regionsToProcess);
          
        } catch(error) {
          showError(error.message || "Terjadi kesalahan saat memuat data");
        } finally {
          $spinner.fadeOut(200);
        }
      });

      // Inisialisasi
      if (initUser()) {
        // Initialize searchable dropdowns
        projectDropdown = createSearchableDropdown(
          '#projectSearch', 
          '#projectOptions', 
          '#projectSelect',
          [],
          'value',
          'text',
          async (value, text) => {
            selectedProject = value;
            selectedRegion = null;
            
            if (currentUser.prov === "00" && currentUser.kab === "00") {
              regionDropdown.clear();
              regionDropdown.disable();
            }
            
            if (selectedProject) await loadRegions();
          }
        );
        
        regionDropdown = createSearchableDropdown(
          '#regionSearch',
          '#regionOptions', 
          '#regionSelect',
          [],
          'value',
          'text',
          (value, text) => {
            selectedRegion = value;
          }
        );
        
        year = $yearSelect.val() || "2025";
        
        // Load projects untuk tahun yang sudah terpilih (default 2025)
        if (year) {
          loadProjects();
        }
      }
      $spinner.hide();
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
