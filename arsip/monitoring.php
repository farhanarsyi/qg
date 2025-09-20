<?php
// monitoring.php
require_once 'sso_integration.php';

// Pastikan user sudah login SSO
requireSSOLogin('monitoring.php');

// Dapatkan filter wilayah berdasarkan SSO user
$wilayah_filter = getSSOWilayahFilter();
$user_data = getUserData();
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
  <!-- Navbar CSS -->
  <link rel="stylesheet" href="navbar.css">
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
      max-width: 95vw; /* Much wider container, responsive to viewport */
      margin: 0 auto;
      padding: 1rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 1.5rem;
      color: var(--dark-color);
      font-size: 1.5rem;
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
      padding: 0.5rem 0.6rem;
      font-weight: 500;
      font-size: 0.7rem;
      color: var(--dark-color);
    }
    
    .card-body {
      padding: 0.6rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.25rem;
      color: var(--dark-color);
      font-size: 0.7rem;
    }
    
    .form-control, .form-select {
      border-radius: 4px;
      border: 1px solid var(--border-color);
      padding: 0.3rem 0.5rem;
      font-size: 0.7rem;
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
    
    .btn-primary:active {
      transform: translateY(0);
    }
    
    .table-wrapper {
      overflow: auto;
      margin: 0;
      max-height: calc(100vh - 220px);
      min-height: 400px;
      box-shadow: 0 4px 16px rgba(5, 150, 105, 0.1);
      background: white;
    }
    
    .table-monitoring {
      width: 100%;
      border-spacing: 0;
      border: none;
    }
    
    .table-monitoring th {
      padding: 8px 6px;
      text-align: center;
      font-weight: 600;
      background: linear-gradient(135deg, var(--primary-color), var(--success-color));
      color: white;
      font-size: 0.7rem;
      position: sticky;
      top: 0;
      z-index: 1;
      border: none;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    
    .table-monitoring td {
      padding: 8px 6px;
      vertical-align: middle;
      font-size: 0.7rem;
      border: none;
    }
    
    /* Alternating row colors - Green pastel theme */
    .table-monitoring tbody tr:nth-child(odd) td {
      background-color: #d1fae5; /* Light green pastel */
    }
    
    .table-monitoring tbody tr:nth-child(even) td {
      background-color: #a7f3d0; /* Medium green pastel */
    }
    
    /* Alternating column colors overlay */
    .table-monitoring td:nth-child(odd) {
      filter: brightness(0.95);
    }
    
    .table-monitoring td:nth-child(even) {
      filter: brightness(1.05);
    }
    
    /* Simple hover effects */
    .table-monitoring tbody tr:hover td {
      filter: brightness(1.05) !important;
      transition: all 0.2s ease;
    }
    
    /* Capitalize region names and content */
    .table-monitoring td {
      text-transform: capitalize;
    }
    
    /* Don't capitalize certain elements */
    .table-monitoring .activity-number,
    .table-monitoring .status-icon {
      text-transform: none;
    }
    
    /* Modern Status icons */
    .status-icon {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .status-circle {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 13px;
      font-weight: 700;
      color: white;
      border: none;
      transition: all 0.2s ease;
    }
    
    .status-success .status-circle {
      background: linear-gradient(135deg, #10b981, #059669);
    }
    
    .status-danger .status-circle {
      background: linear-gradient(135deg, #ef4444, #dc2626);
    }
    
    .status-neutral .status-circle {
      background: linear-gradient(135deg, #6b7280, #4b5563);
    }
    
    .status-warning .status-circle {
      background: linear-gradient(135deg, #f59e0b, #d97706);
    }
    
    /* Simple hover effects for status icons */
    .status-icon:hover .status-circle {
      transform: scale(1.1);
      cursor: pointer;
      transition: transform 0.2s ease;
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
      font-weight: 600;
      text-align: center;
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
      color: white !important;
      white-space: normal;
      word-wrap: break-word;
      font-size: 0.65rem;
      line-height: 1.1;
      padding: 8px 6px;
      min-width: 90px;
      max-width: 120px;
      text-transform: capitalize;
      letter-spacing: 0.3px;
    }
    
    /* Frozen columns */
    .table-monitoring th:nth-child(1), /* Gate */
    .table-monitoring td:nth-child(1) {
      position: sticky;
      left: 0;
      background-color: #fff;
      z-index: 10;
      width: 140px;
      min-width: 140px;
      max-width: 140px;
      margin-right: -1px;
      font-size: 0.7rem;
      padding: 4px;
    }
    
    /* Freeze Column 1: Gate */
    .table-monitoring th:nth-child(1),
    .table-monitoring td:nth-child(1) {
      position: sticky !important;
      left: 0 !important;
      width: 140px;
      min-width: 140px;
      z-index: 3;
    }
    
    .table-monitoring th:nth-child(1) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Freeze Column 2: Ukuran Kualitas */
    .table-monitoring th:nth-child(2),
    .table-monitoring td:nth-child(2) {
      position: sticky;
      left: 140px;
      width: 160px;
      min-width: 160px;
    }
    
    .table-monitoring th:nth-child(2) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Freeze Column 3: Level */
    .table-monitoring th:nth-child(3),
    .table-monitoring td:nth-child(3) {
      position: sticky;
      left: 300px;
      width: 60px;
      min-width: 60px;
      text-align: center;
    }
    
    .table-monitoring th:nth-child(3) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Freeze Column 4: Aktivitas */
    .table-monitoring th:nth-child(4),
    .table-monitoring td:nth-child(4) {
      position: sticky;
      left: 360px;
      width: 180px;
      min-width: 180px;
      word-wrap: break-word;
      white-space: normal;
      line-height: 1.2;
    }
    
    .table-monitoring th:nth-child(4) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Freeze Column 5: Tanggal Mulai */
    .table-monitoring th:nth-child(5),
    .table-monitoring td:nth-child(5) {
      position: sticky;
      left: 540px;
      width: 42px;
      min-width: 42px;
      text-align: center;
      font-size: 0.65rem;
    }
    
    .table-monitoring th:nth-child(5) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Freeze Column 6: Tanggal Selesai */
    .table-monitoring th:nth-child(6),
    .table-monitoring td:nth-child(6) {
      position: sticky;
      left: 582px;
      width: 48px;
      min-width: 48px;
      text-align: center;
      font-size: 0.65rem;
    }
    
    .table-monitoring th:nth-child(6) {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    /* Z-index for frozen data cells */
    .table-monitoring td:nth-child(1) {
      z-index: 5 !important;
    }
    
    /* Z-index for frozen data cells */
    .table-monitoring td:nth-child(2),
    .table-monitoring td:nth-child(3),
    .table-monitoring td:nth-child(4),
    .table-monitoring td:nth-child(5),
    .table-monitoring td:nth-child(6) {
      z-index: 8 !important;
    }
    
    /* Preserve alternating colors for frozen columns */
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(1),
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(2),
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(3),
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(4),
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(5),
    .table-monitoring tbody tr:nth-child(odd) td:nth-child(6) {
      background-color: #d1fae5 !important; /* Light green pastel */
    }
    
    .table-monitoring tbody tr:nth-child(even) td:nth-child(1),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(2),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(3),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(4),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(5),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(6) {
      background-color: #a7f3d0 !important; /* Medium green pastel */
    }
    
    /* Higher z-index for frozen column headers to stay on top */
    .table-monitoring th:nth-child(1) {
      z-index: 50 !important;
      position: sticky !important;
      top: 0 !important;
      left: 0 !important;
    }
    
    .table-monitoring th:nth-child(2),
    .table-monitoring th:nth-child(3),
    .table-monitoring th:nth-child(4),
    .table-monitoring th:nth-child(5),
    .table-monitoring th:nth-child(6) {
      z-index: 10;
      position: sticky !important;
      top: 0 !important;
    }
    
    /* Force sticky for first column - additional safety */
    .table-wrapper .table-monitoring th:first-child {
      position: sticky !important;
      left: 0 !important;
      top: 0 !important;
      z-index: 100 !important;
      background: linear-gradient(135deg, var(--primary-color), var(--success-color)) !important;
    }
    
    .table-wrapper .table-monitoring td:first-child {
      position: sticky !important;
      left: 0 !important;
      z-index: 5 !important;
    }
    
    /* Date in range */
    .date-in-range {
      color: #000;
      font-weight: normal;
    }
    
    /* Untuk kompatibilitas */
    .date-column {
      text-align: center;
      font-size: 0.65rem;
    }
    
    /* Activity number */
    .activity-number {
      display: inline-block;
      background: #000;
      color: white;
      padding: 1px 3px;
      margin-right: 4px;
      font-size: 0.6rem;
      border-radius: 2px;
      font-weight: bold;
      min-width: 14px;
      text-align: center;
    }
    
    /* Activity text */
    .activity-text {
      font-size: 0.7rem;
      line-height: 1.2;
    }
    
    /* Status column */
    .status-column {
      text-align: center;
      width: auto;
      min-width: 80px;
      padding: 3px;
      font-size: 0.65rem;
      white-space: nowrap;
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
      border-radius: 0 0 6px 6px;
      max-height: 200px;
      overflow-y: auto;
      z-index: 99999;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
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
      height: 100%;
    }
    
    #statsCards .card:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    #statsCards .card-body {
      padding: 0.3rem 0.25rem;
    }
    
    #statsCards h6 {
      font-size: 0.55rem;
      margin-bottom: 0.05rem;
      line-height: 1.1;
      font-weight: 600;
    }
    
    #statsCards h3 {
      font-size: 0.9rem;
      margin-bottom: 0;
      font-weight: 700;
    }
    
    #statsCards .d-flex {
      margin-bottom: 0.1rem;
      align-items: center;
    }
    
    #statsCards i {
      font-size: 0.6rem;
      margin-right: 0.15rem !important;
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
        margin-bottom: 1rem;
      }
      
      .card-header {
        padding: 0.5rem 0.6rem;
      }
      
      .card-body {
        padding: 0.6rem;
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
  </style>
</head>
<body>
  <!-- SSO Integrated Navigation -->
  <?php renderSSONavbar('monitoring'); ?>

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
      <div class="spinner-text" id="spinnerText">Memuat data monitoring...</div>
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

      // Inisialisasi user dengan data SSO
      const initUser = () => {
        console.log('🔍 [MONITORING] Initializing user...');
        
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
        
        console.log('👤 [MONITORING] Current User Data:', currentUser);
        console.log('🗺️ [MONITORING] Wilayah filter:', {
          prov: currentUser.prov,
          kab: currentUser.kab,
          unit_kerja: currentUser.unit_kerja,
          prov_empty: !currentUser.prov,
          kab_empty: !currentUser.kab
        });
        
        // Validasi data user dengan delay untuk mencegah infinite redirect
        if (!currentUser.username) {
          console.error('❌ [MONITORING] Username kosong! Redirecting to SSO login...');
          alert('Session SSO tidak ditemukan. Akan diarahkan ke halaman login SSO.');
          setTimeout(() => {
            window.location.href = 'sso_login.php';
          }, 1000);
          return false;
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
          // Format: dd/mm (tanpa tahun untuk hemat space)
          return `${parts[2]}/${parts[1]}`;
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
        if (status.startsWith('Sudah')) {
          return `<div class="status-icon status-success" title="${status}">
                    <div class="status-circle">
                      <i class="fas fa-check"></i>
                    </div>
                  </div>`;
        }
        if (status.startsWith('Belum')) {
          return `<div class="status-icon status-danger" title="${status}">
                    <div class="status-circle">
                      <i class="fas fa-minus"></i>
                    </div>
                  </div>`;
        }
        if (status === 'Tidak perlu') {
          return `<div class="status-icon status-neutral" title="${status}">
                    <div class="status-circle">
                      <i class="fas fa-ban"></i>
                    </div>
                  </div>`;
        }
        return `<div class="status-icon status-warning" title="${status}">
                  <div class="status-circle">
                    <i class="fas fa-clock"></i>
                  </div>
                </div>`;
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
                      <th class="date-column">Mulai</th>
                      <th class="date-column">Selesai</th>
        `;
        
        // Tambahkan kolom status untuk setiap wilayah
        regions.forEach(region => {
          // Capitalize each word in region name
          const capitalizedName = region.name.split(' ').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
          ).join(' ');
          tableHtml += `<th class="status-column region-header">${capitalizedName}</th>`;
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
            <td><span class="activity-number">${activityNumber}</span><span class="activity-text">${data.activity}</span></td>
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

      // --- Fungsi untuk memproses dan menyimpan data aktivitas per wilayah (Optimized) ---
      const processData = async (regions) => {
        // Reset data
        activityData = {};
        
        // Panggil API baru yang mengambil semua data sekaligus
        const monitoringResponse = await makeAjaxRequest(API_URL, {
          action: "fetchMonitoringData",
          year: year,
          id_project: selectedProject,
          regions: JSON.stringify(regions)
        });
        
        if (!monitoringResponse.status) {
          throw new Error(monitoringResponse.message || "Gagal memuat data monitoring");
        }
        
        const data = monitoringResponse.data;
        const gates = data.gates || [];
        const monitoringData = data.monitoring_data || {};
        
        if (gates.length === 0) {
          throw new Error("Tidak ada data gate");
        }
        
        // Process data untuk setiap gate dan measurement
        for (const gate of gates) {
          const gateNumber = gate.gate_number || gates.indexOf(gate) + 1;
          const gateName = `GATE${gateNumber}: ${gate.gate_name}`;
          
          // Ambil measurements dari region pertama (assumsi measurements sama untuk semua region)
          let measurements = [];
          for (const regionId in monitoringData) {
            if (monitoringData[regionId][gate.id] && monitoringData[regionId][gate.id].measurements) {
              measurements = monitoringData[regionId][gate.id].measurements;
              break;
            }
          }
          
          if (measurements.length === 0) continue;
          
          // Proses setiap measurement
          for (let j = 0; j < measurements.length; j++) {
            const measurement = measurements[j];
            const ukNumber = j + 1;
            const ukName = `UK${ukNumber}: ${measurement.measurement_name}`;
            const ukLevel = getUkLevelLabel(measurement);
            const assessmentLevel = parseInt(measurement.assessment_level || 1);
            
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
            
            // Proses setiap aktivitas
            for (const activity of activities) {
              const activityKey = `${gateName}|${ukName}|${activity.name}`;
              
              // Simpan info aktivitas
              if (!activityData[activityKey]) {
                activityData[activityKey] = {
                  gate: gateName,
                  uk: ukName,
                  ukLevel: ukLevel,
                  assessmentLevel: assessmentLevel,
                  activity: activity.name,
                  start: activity.start,
                  end: activity.end,
                  statuses: {}
                };
              }
              
              // Untuk setiap wilayah, tentukan status
              for (const region of regions) {
                // Cek apakah ukuran kualitas sesuai dengan level wilayah
                const isApplicable = isUkApplicableForRegion(measurement, region);
                
                if (!isApplicable) {
                  activityData[activityKey].statuses[region.id] = "Tidak perlu";
                  continue;
                }
                
                // Ambil data monitoring untuk region ini
                const regionData = monitoringData[region.id] && monitoringData[region.id][gate.id];
                if (!regionData) {
                  activityData[activityKey].statuses[region.id] = "Tidak tersedia";
                  continue;
                }
                
                // Tentukan status berdasarkan aktivitas
                const status = determineActivityStatusFromData(
                  regionData, measurement, activity.name
                );
                
                activityData[activityKey].statuses[region.id] = status;
              }
            }
          }
        }
      };
      
      // Fungsi helper untuk menentukan status dari data yang sudah dimuat
      const determineActivityStatusFromData = (regionData, measurement, activityName) => {
        const assessment = regionData.assessment;
        const preventiveCount = regionData.preventives[measurement.id] || 0;
        const correctiveCount = regionData.correctives[measurement.id] || 0;
        const docPreventiveCount = regionData.doc_preventives[measurement.id] || 0;
        const docCorrectiveCount = regionData.doc_correctives[measurement.id] || 0;
        
        // Cek status assessment
        let assessmentValue = null;
        let isAssessed = false;
        let isApproved = false;
        
        if (assessment && assessment.assessment && Array.isArray(assessment.assessment)) {
          const measurementAssessment = assessment.assessment.find(item => 
            item.idm && item.idm == measurement.id
          );
          
          if (measurementAssessment && measurementAssessment.ass) {
            assessmentValue = measurementAssessment.ass;
            isAssessed = true;
          }
        }
        
        if (assessment) {
          isApproved = assessment.state === "1" || assessment.status === "1";
        }
        
        // Tentukan status berdasarkan aktivitas
        switch (activityName) {
          case "Pengisian nama pelaksana aksi preventif":
            return preventiveCount > 0 ? "Sudah ditentukan" : "Belum ditentukan";
            
          case "Upload bukti pelaksanaan aksi preventif":
            if (preventiveCount === 0) return "Belum ditentukan";
            return docPreventiveCount > 0 ? "Sudah diunggah" : "Belum diunggah";
            
          case "Penilaian ukuran kualitas":
            if (!isAssessed) return "Belum dinilai";
            if (assessmentValue === "1" || assessmentValue === 1) return "Sudah dinilai (merah)";
            if (assessmentValue === "2" || assessmentValue === 2) return "Sudah dinilai (kuning)";
            if (assessmentValue === "3" || assessmentValue === 3) return "Sudah dinilai (hijau)";
            return "Belum dinilai";
            
          case "Approval Gate oleh Sign-off":
            if (!isAssessed) return "Belum dinilai";
            return isApproved ? "Sudah disetujui" : "Belum disetujui";
            
          case "Pengisian pelaksana aksi korektif":
            if (!isApproved) return "Belum disetujui";
            if (assessmentValue === "3" || assessmentValue === 3) return "Tidak perlu";
            return correctiveCount > 0 ? "Sudah ditentukan" : "Belum ditentukan";
            
          case "Upload bukti pelaksanaan aksi korektif":
            if (!isApproved) return "Belum disetujui";
            if (assessmentValue === "3" || assessmentValue === 3) return "Tidak perlu";
            if (correctiveCount === 0) return "Belum ditentukan";
            return docCorrectiveCount > 0 ? "Sudah diunggah" : "Belum diunggah";
            
          default:
            return "Tidak tersedia";
        }
      };

      // --- Event Handlers ---

      // Logout handler
      $("#logoutBtn").on('click', function(){
        if (confirm('Apakah Anda yakin ingin logout?')) {
          window.location.href = 'sso_logout.php';
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
        
        const startTime = Date.now();
        $spinner.fadeIn(200);
        $("#spinnerText").text("Mengumpulkan data dari database...");
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
          $("#spinnerText").text(`Memproses data untuk ${regionsToProcess.length} wilayah...`);
          await processData(regionsToProcess);
          
          $("#spinnerText").text("Menyiapkan tampilan tabel...");
          // Tampilkan hasil dalam format tabel
          displayResultTable(regionsToProcess);
          
          const loadTime = ((Date.now() - startTime) / 1000).toFixed(1);
          console.log(`✅ Data monitoring berhasil dimuat dalam ${loadTime} detik (${regionsToProcess.length} wilayah)`);
          
          // Show success message in console for performance tracking
          if (regionsToProcess.length > 10) {
            console.log(`🚀 Optimasi berhasil! Memuat ${Object.keys(activityData).length} aktivitas untuk ${regionsToProcess.length} wilayah hanya dalam ${loadTime} detik`);
          }
          
          // Show performance info to user
          setTimeout(() => {
            const toast = $(`
              <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 10000; min-width: 300px;">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Data berhasil dimuat!</strong><br>
                <small>${Object.keys(activityData).length} aktivitas • ${regionsToProcess.length} wilayah • ${loadTime}s</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>
            `);
            $('body').append(toast);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
              toast.fadeOut(300, function() { $(this).remove(); });
            }, 4000);
                     }, 300);
          
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
  
  <!-- SSO Wilayah Filter JavaScript -->
  <?php injectWilayahJS(); ?>
  
  <!-- Debug Info (hanya muncul jika ada parameter ?debug) -->
  <?php renderDebugWilayahInfo(); ?>
</body>
</html>
