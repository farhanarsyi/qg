<?php
// monitoring.php
require_once 'sso_integration.php';
require_once 'app_config.php';

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
  <!-- SheetJS for Excel export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
  <!-- Persistence Manager -->
  <script src="persistence_manager.js"></script>
  <!-- App Configuration -->
  <script><?php echo getCardConfigJS(); ?></script>
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
      padding: 0.5rem;
    }
    
    h1 {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--dark-color);
      font-size: 1.2rem;
    }
    


    .card {
      border-radius: 8px;
      box-shadow: 0 1px 10px rgba(0,0,0,0.04);
      border: none;
      margin-bottom: 0.3rem;
      background-color: #ffffff;
      overflow: visible;
    }
    
    .card-body {
      overflow: visible;
    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid var(--border-color);
      padding: 0.3rem 0.4rem;
      font-weight: 500;
      font-size: 0.6rem;
      color: var(--dark-color);
    }
    
    .card-body {
      padding: 0.4rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.15rem;
      color: var(--dark-color);
      font-size: 0.6rem;
    }
    
    .form-control, .form-select {
      border-radius: 4px;
      border: 1px solid var(--border-color);
      padding: 0.2rem 0.4rem;
      font-size: 0.6rem;
      background-color: #fff;
      transition: all 0.2s ease;
      will-change: auto;
      transform: translateZ(0);
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
      padding: 0.3rem 0.6rem;
      font-weight: 500;
      font-size: 0.6rem;
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
      border-collapse: collapse;
    }
    
    .table-monitoring th {
      padding: 4px 3px;
      text-align: center;
      font-weight: 600;
      background-color: var(--primary-color);
      color: white;
      font-size: 0.6rem;
      position: sticky;
      top: 0;
      z-index: 1;
      border: none;
      text-transform: uppercase;
      letter-spacing: 0.2px;
      white-space: nowrap;
    }
    
    .table-monitoring td {
      padding: 4px 3px;
      vertical-align: middle;
      font-size: 0.6rem;
      border: none;
      background-color: white;
    }
    
    /* Simple alternating row colors */
    .table-monitoring tbody tr:nth-child(even) td {
      background-color: #f9fafb;
    }
    
    /* Simple hover effects */
    .table-monitoring tbody tr:hover td {
      background-color: #f0f9ff !important;
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
      background-color: #10b981;
    }
    
    .status-danger .status-circle {
      background-color: #ef4444;
    }
    
    .status-neutral .status-circle {
      background-color: #6b7280;
    }
    
    .status-warning .status-circle {
      background-color: #f59e0b;
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
      min-width: 130px;
      max-width: 180px;
      text-transform: capitalize;
      letter-spacing: 0.3px;
    }
    
    /* Frozen columns */
    /* === CLEAN STICKY COLUMNS IMPLEMENTATION === */
    
    /* Column 1: Gate (leftmost, highest z-index) */
    .table-monitoring th:nth-child(1),
    .table-monitoring td:nth-child(1) {
      position: sticky !important;
      left: 0px !important;
      width: 75px;
      min-width: 75px;
      max-width: 75 px;
      z-index: 15 !important;
    }
    
    .table-monitoring th:nth-child(1) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 25 !important;
    }
    
    /* Column 2: Ukuran Kualitas */
    .table-monitoring th:nth-child(2),
    .table-monitoring td:nth-child(2) {
      position: sticky !important;
      left: 75px !important;
      width: 75px;
      min-width: 75px;
      max-width: 75px;
      z-index: 14 !important;
    }
    
    .table-monitoring th:nth-child(2) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 24 !important;
    }
    
    /* Column 3: Level */
    .table-monitoring th:nth-child(3),
    .table-monitoring td:nth-child(3) {
      position: sticky !important;
      left: 150px !important;
      width: 75px;
      min-width: 75px;
      max-width: 75px;
      text-align: center;
      z-index: 13 !important;
    }
    
    .table-monitoring th:nth-child(3) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 23 !important;
    }
    
    /* Column 4: Aktivitas */
    .table-monitoring th:nth-child(4),
    .table-monitoring td:nth-child(4) {
      position: sticky !important;
      left: 225px !important;
      width: 250px;
      min-width: 250px;
      max-width: 250px;
      word-wrap: break-word;
      white-space: normal;
      line-height: 1.2;
      z-index: 12 !important;
    }
    
    .table-monitoring th:nth-child(4) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 22 !important;
    }
    
    /* Column 5: Tanggal Mulai */
    .table-monitoring th:nth-child(5),
    .table-monitoring td:nth-child(5) {
      position: sticky !important;
      left: 475px !important;
      width: 60px;
      min-width: 60px;
      max-width: 60px;
      text-align: center;
      font-size: 0.65rem;
      white-space: nowrap;
      z-index: 11 !important;
    }
    
    .table-monitoring th:nth-child(5) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 21 !important;
    }
    
    /* Column 6: Tanggal Selesai */
    .table-monitoring th:nth-child(6),
    .table-monitoring td:nth-child(6) {
      position: sticky !important;
      left: 535px !important;
      width: 65px;
      min-width: 65px;
      max-width: 65px;
      text-align: center;
      font-size: 0.65rem;
      white-space: nowrap;
      z-index: 10 !important;
    }
    
    /* Column 7: Deadline */
    .table-monitoring th:nth-child(7),
    .table-monitoring td:nth-child(7) {
      position: sticky !important;
      left: 600px !important;
      width: 65px;
      min-width: 65px;
      max-width: 65px;
      text-align: center;
      font-size: 0.65rem;
      white-space: nowrap;
      z-index: 9 !important;
    }
    
    .table-monitoring th:nth-child(7) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 19 !important;
    }
    
    /* Animasi kedap-kedip untuk tanggal yang sedang berlangsung */
    @keyframes blink {
      0%, 50% { opacity: 1; }
      51%, 100% { opacity: 1; }
    }
    
    .date-in-range {
      color: #10b981 !important;
      font-weight: 600 !important;
      animation: blink 2s infinite !important;
    }
    
    .table-monitoring th:nth-child(6) {
      background-color: var(--primary-color) !important;
      top: 0 !important;
      z-index: 20 !important;
    }
    
    /* Ensure alternating row colors work with sticky columns */
    .table-monitoring tbody tr:nth-child(even) td:nth-child(1),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(2),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(3),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(4),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(5),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(6),
    .table-monitoring tbody tr:nth-child(even) td:nth-child(7) {
      background-color: #f9fafb !important;
    }
    
    /* Clean borderless design - visual separation through background only */
    

    
    /* Untuk kompatibilitas */
    .date-column {
      text-align: center;
      font-size: 0.65rem;
    }
    
    /* Pastikan animasi kedap-kedip tidak ditimpa oleh CSS lain */
    .date-column.date-in-range {
      color: #10b981 !important;
      font-weight: 600 !important;
      animation: blink 2s infinite !important;
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
    
    /* Gate and UK Badge Styles */
    .gate-badge {
      display: inline-block;
      border-radius: 10px;
      background: #d1fae5; /* hijau pastel */
      color: #047857;
      font-weight: bold;
      padding: 2px 10px;
      font-size: 0.7rem;
      box-shadow: none;
      border: 1px solid #a7f3d0;
      margin-bottom: 2px;
      cursor: default;
      transition: none;
      text-align: center;
      min-width: 60px;
    }
    .uk-badge {
      display: inline-block;
      border-radius: 10px;
      background: #dbeafe; /* biru pastel */
      color: #1e40af;
      font-weight: bold;
      padding: 2px 10px;
      font-size: 0.7rem;
      box-shadow: none;
      border: 1px solid #bfdbfe;
      margin-bottom: 2px;
      cursor: default;
      transition: none;
      text-align: center;
      min-width: 60px;
    }
    .gate-badge:hover, .uk-badge:hover {
      background: inherit;
      color: inherit;
    }
    /* Center content in table cell for gate and uk */
    .table-monitoring td:nth-child(1),
    .table-monitoring td:nth-child(2) {
      text-align: center;
      vertical-align: middle;
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
      margin-bottom: 0.3rem;
      line-height: 1.0;
      font-weight: 600;
    }
    
    #statsCards h3 {
      font-size: 0.8rem;
      margin-bottom: 0.3rem;
      font-weight: 700;
    }
    
    #statsCards .d-flex {
      margin-bottom: 0.3rem;
      align-items: center;
    }
    
    #statsCards i {
      font-size: 0.55rem;
      margin-right: 0.1rem !important;
    }

    /* Level Filter Cards */
    .level-filter-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 6px;
      padding: 0.4rem 0.8rem;
      cursor: pointer;
      transition: all 0.2s ease;
      font-size: 0.75rem;
      font-weight: 500;
      color: #6b7280;
      user-select: none;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .level-filter-card:hover {
      border-color: #059669;
      color: #059669;
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(5, 150, 105, 0.2);
    }

    .level-filter-card.active {
      background: #059669;
      border-color: #059669;
      color: white;
      box-shadow: 0 2px 6px rgba(5, 150, 105, 0.3);
    }

    .level-filter-card.active:hover {
      background: #047857;
      border-color: #047857;
      transform: translateY(-1px);
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

    /* Gate badge colors */
    .gate-badge-1  { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
    .gate-badge-2  { background: #fef9c3; color: #b45309; border-color: #fde68a; }
    .gate-badge-3  { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
    .gate-badge-4  { background: #e0e7ff; color: #3730a3; border-color: #c7d2fe; }
    .gate-badge-5  { background: #f3e8ff; color: #7c3aed; border-color: #e9d5ff; }
    .gate-badge-6  { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .gate-badge-7  { background: #fef2f2; color: #be123c; border-color: #fecdd3; }
    .gate-badge-8  { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .gate-badge-9  { background: #f0fdfa; color: #0e7490; border-color: #99f6e4; }
    .gate-badge-10 { background: #fefce8; color: #a16207; border-color: #fef9c3; }
    .gate-badge-11 { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
    .gate-badge-12 { background: #f3f4f6; color: #6d28d9; border-color: #ddd6fe; }
    .gate-badge-13 { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }
    .gate-badge-14 { background: #f0fdf4; color: #059669; border-color: #bbf7d0; }
    .gate-badge-15 { background: #f1f5f9; color: #0369a1; border-color: #bae6fd; }

    /* UK badge colors */
    .uk-badge-1  { background: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
    .uk-badge-2  { background: #fef3c7; color: #92400e; border-color: #fde68a; }
    .uk-badge-3  { background: #fce7f3; color: #be185d; border-color: #fbcfe8; }
    .uk-badge-4  { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
    .uk-badge-5  { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
    .uk-badge-6  { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
    .uk-badge-7  { background: #fef2f2; color: #be123c; border-color: #fecdd3; }
    .uk-badge-8  { background: #f0fdf4; color: #166534; border-color: #bbf7d0; }
    .uk-badge-9  { background: #f0fdfa; color: #0e7490; border-color: #99f6e4; }
    .uk-badge-10 { background: #fefce8; color: #a16207; border-color: #fef9c3; }
    .uk-badge-11 { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
    .uk-badge-12 { background: #f3f4f6; color: #7c3aed; border-color: #ddd6fe; }
    .uk-badge-13 { background: #fce7f3; color: #be185d; border-color: #fbcfe8; }
    .uk-badge-14 { background: #f0fdf4; color: #059669; border-color: #bbf7d0; }
    .uk-badge-15 { background: #f1f5f9; color: #0369a1; border-color: #bae6fd; }

    /* Level badge styles */
    .level-badge {
      display: inline-block;
      border-radius: 10px;
      font-weight: bold;
      padding: 2px 10px;
      font-size: 0.7rem;
      box-shadow: none;
      margin-bottom: 2px;
      cursor: default;
      transition: none;
      text-align: center;
      min-width: 60px;
    }
    
    .level-badge:hover {
      background: inherit;
      color: inherit;
    }
    
    /* Level badge colors */
    .level-badge-pusat { 
      background: #fed7aa; /* orange pastel */
      color: #c2410c; 
      border: 1px solid #fdba74; 
    }
    .level-badge-provinsi { 
      background: #dbeafe; /* blue pastel */
      color: #1e40af; 
      border: 1px solid #bfdbfe; 
    }
    .level-badge-kabkot { 
      background: #d1fae5; /* green pastel */
      color: #047857; 
      border: 1px solid #a7f3d0; 
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

    /* Sorting styles */
    .sortable-header {
      cursor: pointer;
      user-select: none;
      position: relative;
      transition: all 0.2s ease;
    }
    
    .sortable-header:hover {
      background-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    .sort-icon {
      margin-left: 0.5rem;
      opacity: 0.5;
      transition: opacity 0.2s ease;
      font-size: 0.6rem;
    }
    
    .sortable-header:hover .sort-icon {
      opacity: 1;
    }
    
    .sortable-header.sorted .sort-icon {
      opacity: 1;
      color: #fff;
    }
    
    .sortable-header.sorted:hover .sort-icon {
      color: #fff;
    }

    /* Secondary Filter Styles */
    #secondaryFilterCard .form-select[multiple] {
      min-height: 80px;
      font-size: 0.7rem;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
      background-color: white;
      will-change: auto;
      transform: translateZ(0);
    }
    
    #secondaryFilterCard .form-select[multiple]:focus {
      border-color: #059669;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
      outline: none;
    }
    
    /* Make all filter options green when selected */
    #secondaryFilterCard .form-select[multiple] option {
      padding: 0.3rem 0.5rem;
      background-color: white;
      color: #374151;
      transition: all 0.2s ease;
    }
    
    #secondaryFilterCard .form-select[multiple] option:checked {
      background-color: #059669 !important;
      color: white !important;
      font-weight: 600;
    }
    
    #secondaryFilterCard .form-select[multiple] option:hover {
      background-color: #f0fdf4 !important;
      color: #059669 !important;
    }
    
    /* Additional styling for better visibility of selected options */
    #secondaryFilterCard .form-select[multiple] option:checked:hover {
      background-color: #047857 !important;
      color: white !important;
    }
    
    /* Ensure the select element itself shows active state when options are selected */
    #secondaryFilterCard .form-select[multiple]:not([value=""]) {
      border-color: #059669;
      background-color: #ffffff;
    }
    
    /* Enhanced styling for select elements with selected options */
    #secondaryFilterCard .form-select[multiple].has-selected-options {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    #secondaryFilterCard .form-select[multiple].has-selected-options:focus {
      border-color: #047857 !important;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.3) !important;
    }
    
    /* Ensure has-selected-options state persists even after losing focus */
    #secondaryFilterCard .form-select[multiple].has-selected-options:not(:focus) {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    /* Style for when multiple options are selected */
    #secondaryFilterCard .form-select[multiple][multiple] option:checked {
      background: linear-gradient(135deg, #059669, #047857) !important;
      color: white !important;
      font-weight: 600;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    
    #secondaryFilterCard .form-label {
      font-size: 0.65rem;
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    
    /* Active Filter Styles */
    .filter-active {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
      will-change: auto;
      transform: translateZ(0);
    }
    
    .filter-label-active {
      color: #374151 !important;
      font-weight: 700 !important;
      will-change: auto;
      transform: translateZ(0);
    }
    
    .filter-active:focus {
      border-color: #047857 !important;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.3) !important;
    }
    
    /* Override default form-select focus styles for active filters */
    .filter-active:focus,
    .filter-active:not(:focus) {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    /* Ensure active state persists even after losing focus */
    #secondaryFilterCard .form-select[multiple].filter-active {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    #secondaryFilterCard .form-select[multiple].filter-active:focus {
      border-color: #047857 !important;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.3) !important;
    }
    
    /* Override any conflicting styles with higher specificity */
    #secondaryFilterCard .form-select[multiple].filter-active.has-selected-options {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    #secondaryFilterCard .form-select[multiple].filter-active.has-selected-options:focus {
      border-color: #047857 !important;
      box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.3) !important;
    }
    
    #secondaryFilterCard .form-select[multiple].filter-active.has-selected-options:not(:focus) {
      border-color: #059669 !important;
      background-color: #ffffff !important;
      box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2) !important;
    }
    
    #secondaryFilterCard .card-header {
      background: linear-gradient(135deg, #f8fafc, #e2e8f0);
      border-bottom: 1px solid #cbd5e1;
      font-size: 0.75rem;
      font-weight: 600;
    }
    
    #secondaryFilterCard .btn-sm {
      font-size: 0.65rem;
      padding: 0.25rem 0.5rem;
    }
    
    .filter-option {
      font-size: 0.7rem;
      padding: 0.2rem 0.4rem;
    }
    
    /* Ensure all filter selects have consistent styling */
    select[multiple] option {
      padding: 0.3rem 0.5rem;
      background-color: white;
      color: #374151;
    }
    
    select[multiple] option:checked {
      background-color: #059669 !important;
      color: white !important;
    }
    
    select[multiple] option:hover {
      background-color: #f0fdf4 !important;
      color: #059669 !important;
    }
    
    .filter-count {
      background: var(--primary-color);
      color: white;
      border-radius: 50%;
      padding: 0.1rem 0.3rem;
      font-size: 0.6rem;
      margin-left: 0.3rem;
    }
  </style>
</head>
<body>
  <!-- SSO Integrated Navigation -->
  <?php renderSSONavbar('monitoring'); ?>

  <div class="container-fluid">
    <!-- Wilayah Info Box -->
    <?php renderWilayahInfoBox(); ?>
    <!-- Input Filters -->
    <div class="card" style="margin-bottom: 0.5rem;">
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

    <!-- Statistics Cards -->
    <?php if (shouldShowMonitoringCards()): ?>
    <div class="row" id="statsCards" style="display: none; margin-bottom: 0.3rem;">
      <div class="col-md-2">
        <div class="card border-0 bg-primary bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-play-circle text-primary me-2"></i>
              <h6 class="mb-0 text-primary fw-semibold">Sedang Berjalan</h6>
            </div>
            <h3 class="mb-0 text-primary fw-bold" id="statSedangBerjalan">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-2">
        <div class="card border-0 bg-info bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-list text-info me-2"></i>
              <h6 class="mb-0 text-info fw-semibold">Total</h6>
            </div>
            <h3 class="mb-0 text-info fw-bold" id="statTotal">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10">
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-check-circle text-success me-2"></i>
              <h6 class="mb-0 text-success fw-semibold">Sudah</h6>
            </div>
            <h3 class="mb-0 text-success fw-bold" id="statSudah">0</h3>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card border-0 bg-danger bg-opacity-10">
          <div class="card-body text-center">
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
          <div class="card-body text-center">
            <div class="d-flex align-items-center justify-content-center mb-2">
              <i class="fas fa-ban text-secondary me-2"></i>
              <h6 class="mb-0 text-secondary fw-semibold">Tidak Perlu</h6>
            </div>
            <h3 class="mb-0 text-secondary fw-bold" id="statTidakPerlu">0</h3>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>


    <!-- Toggle Advanced Filters Button -->
    <div class="mb-1" id="toggleAdvancedFiltersContainer" style="display: none;">
      <button id="toggleAdvancedFilters" class="btn btn-sm btn-outline-secondary" style="font-size: 0.6rem; padding: 0.2rem 0.5rem;">
        <i class="fas fa-chevron-down me-1"></i>Filter Lanjutan
      </button>
    </div>

    <!-- Secondary Universal Filter -->
    <div class="card" id="secondaryFilterCard" style="display: none; margin-bottom: 0.3rem;">
      <div class="card-header d-flex justify-content-between align-items-center" style="padding: 0.3rem 0.4rem;">
        <span style="font-size: 0.65rem;"><i class="fas fa-filter me-1"></i>Filter Lanjutan</span>
        <button id="clearSecondaryFilters" class="btn btn-sm btn-outline-secondary" style="font-size: 0.65rem; padding: 0.15rem 0.4rem;">
          <i class="fas fa-times me-1"></i>Reset
        </button>
      </div>
      <div class="card-body" style="padding: 0.4rem;">
        <div class="row g-1">
          <div class="col-md-2">
            <label for="filterGate" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">Gate</label>
            <select id="filterGate" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua Gate</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filterUK" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">UK</label>
            <select id="filterUK" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua UK</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filterLevel" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">Level</label>
            <select id="filterLevel" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua Level</option>
              <option value="1">Pusat</option>
              <option value="2">Provinsi</option>
              <option value="3">Kabkot</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filterActivity" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">Aktivitas</label>
            <select id="filterActivity" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua Aktivitas</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filterStatus" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">Status</label>
            <select id="filterStatus" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua Status</option>
            </select>
          </div>
          <div class="col-md-2">
            <label for="filterDeadline" class="form-label" style="font-size: 0.65rem; margin-bottom: 0.1rem;">Deadline</label>
            <select id="filterDeadline" class="form-select" multiple style="font-size: 0.65rem; padding: 0.15rem 0.3rem;">
              <option value="">Semua Deadline</option>
              <option value="3days">3 hari</option>
              <option value="week">Minggu ini</option>
              <option value="month">Bulan ini</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Section -->
    <div id="resultsContainer" style="margin-top: 0.1rem;"></div>
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
      
      // Debug logging helper function
      function debugLog(message, type = 'log') {
        if (window.appConfig && window.appConfig.debugMode) {
          const prefix = '🔍 [DEBUG] ';
          switch (type) {
            case 'warn':
              console.warn(prefix + message);
              break;
            case 'error':
              console.error(prefix + message);
              break;
            default:
              console.log(prefix + message);
              break;
          }
        }
      }
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      let activityData = {}; // Untuk menyimpan data status per aktivitas per wilayah
      let currentUser = null; // User yang sedang login
      let activeLevelFilters = new Set(); // Track active level filters
      let currentDisplayRegions = []; // Simpan regions yang sedang ditampilkan di tabel
      let sortColumn = null; // Kolom yang sedang di-sort
      let sortDirection = 'asc'; // Arah sorting (asc/desc)
      let secondaryFilters = {
        gate: [],
        uk: [],
        level: [],
        activity: [],
        status: [],
        deadline: []
      }; // Secondary filter values
      let allActivityData = {}; // Simpan semua data untuk filtering

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
      let daerahData = []; // Data dari daftar_daerah.json
      
      // Initialize searchable dropdowns
      let projectDropdown, regionDropdown;

      // --- Helper Functions ---

      // Load daerah data from JSON file
      const loadDaerahData = async () => {
        try {
          const response = await fetch('daftar_daerah.json');
          
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          
          const contentType = response.headers.get('content-type');
          if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Response is not JSON');
          }
          
          daerahData = await response.json();
          debugLog('🗺️ [MONITORING] Daerah data loaded: ' + daerahData.length + ' entries');
          return true;
        } catch (error) {
          debugLog('❌ [MONITORING] Failed to load daerah data: ' + error.message, 'error');
          
          // Show user-friendly error message
          if (error.message.includes('403')) {
            debugLog('⚠️ [MONITORING] Access denied to daftar_daerah.json - this may be a server configuration issue', 'warn');
          } else if (error.message.includes('404')) {
            debugLog('⚠️ [MONITORING] daftar_daerah.json not found', 'warn');
          } else if (error.message.includes('Response is not JSON')) {
            debugLog('⚠️ [MONITORING] Server returned non-JSON response (possibly HTML error page)', 'warn');
          }
          
          // Fallback: try to load from PHP endpoint or different path
          try {
            // Try PHP endpoint first
            const phpResponse = await fetch('daftar_daerah.php');
            if (phpResponse.ok) {
              daerahData = await phpResponse.json();
              debugLog('🗺️ [MONITORING] Daerah data loaded from PHP endpoint: ' + daerahData.length + ' entries');
              return true;
            }
          } catch (phpError) {
            debugLog('⚠️ [MONITORING] PHP endpoint failed: ' + phpError.message, 'warn');
          }
          
          try {
            // Try different path
            const fallbackResponse = await fetch('./daftar_daerah.json');
            if (fallbackResponse.ok) {
              daerahData = await fallbackResponse.json();
              debugLog('🗺️ [MONITORING] Daerah data loaded from fallback path: ' + daerahData.length + ' entries');
              return true;
            }
          } catch (fallbackError) {
            debugLog('⚠️ [MONITORING] Fallback path also failed: ' + fallbackError.message, 'warn');
          }
          
          // Use minimal fallback data
          daerahData = [
            { "kode": "0000", "daerah": "Nasional" },
            { "kode": "pusat", "daerah": "Pusat" }
          ];
          debugLog('🗺️ [MONITORING] Using minimal fallback daerah data');
          return false;
        }
      };

      // Get daerah name by kode
      const getDaerahName = (kode) => {
        if (!kode) return 'Wilayah Tidak Diketahui';
        
        // Handle special cases first
        if (kode === 'pusat' || kode === '00' || kode === '0000') {
          return 'Pusat';
        }
        
        // If daerahData is not loaded yet, return a fallback
        if (!daerahData || daerahData.length === 0) {
          return `Wilayah ${kode}`;
        }
        
        // Find exact match
        const daerah = daerahData.find(d => d.kode === kode);
        if (daerah) {
          return daerah.daerah;
        }
        
        // If not found, return original kode
        return `Wilayah ${kode}`;
      };

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

      // Inisialisasi user dengan data SSO (mendukung superadmin imitation)
      const initUser = () => {
        debugLog('🔍 [MONITORING] Initializing user...');
        
        // Gunakan data dari SSO filter yang sudah mendukung superadmin
        const ssoFilter = window.ssoWilayahFilter || {};
        
        // Data user sudah tersedia dari SSO PHP session
        currentUser = {
          username: '<?= isset($_SESSION["sso_username"]) ? $_SESSION["sso_username"] : "" ?>',
          name: '<?= isset($_SESSION["sso_nama"]) ? $_SESSION["sso_nama"] : "" ?>',
          email: '<?= isset($_SESSION["sso_email"]) ? $_SESSION["sso_email"] : "" ?>',
          role_name: '<?= isset($_SESSION["sso_jabatan"]) ? $_SESSION["sso_jabatan"] : "User" ?>',
          prov: ssoFilter.kodeProvinsi || '<?= isset($_SESSION["sso_prov"]) ? $_SESSION["sso_prov"] : "00" ?>',
          kab: ssoFilter.kodeKabupaten || '<?= isset($_SESSION["sso_kab"]) ? $_SESSION["sso_kab"] : "00" ?>',
          unit_kerja: ssoFilter.unitKerja || '<?= isset($_SESSION["sso_unit_kerja"]) ? $_SESSION["sso_unit_kerja"] : "kabupaten" ?>',
          is_superadmin: ssoFilter.is_superadmin || false,
          is_imitating: ssoFilter.is_imitating || false
        };
        
        debugLog('👤 [MONITORING] Current User Data: ' + JSON.stringify(currentUser));
        debugLog('🗺️ [MONITORING] Wilayah filter: ' + JSON.stringify({
          prov: currentUser.prov,
          kab: currentUser.kab,
          unit_kerja: currentUser.unit_kerja,
          prov_empty: !currentUser.prov,
          kab_empty: !currentUser.kab
        }));
        
        // Validasi data user dengan delay untuk mencegah infinite redirect
        if (!currentUser.username) {
          debugLog('❌ [MONITORING] Username kosong! Redirecting to SSO login...', 'error');
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
        debugLog(message, 'error');
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
      
// Function to calculate days until deadline
const calculateDaysUntilDeadline = (endDateStr) => {
  if (!endDateStr || endDateStr === '-') return '-';
  
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Reset time to start of day
  
  const parts = endDateStr.split('-');
  if (parts.length !== 3) return '-';
  
  const endDate = new Date(parts[0], parts[1] - 1, parts[2]);
  endDate.setHours(23, 59, 59, 999); // Set to end of day
  
  const diffTime = endDate - today;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays > 0) {
    return `-${diffDays}`; // Deadline not yet passed (negative number)
  } else if (diffDays === 0) {
    return '0'; // Today
  } else {
    return `+${Math.abs(diffDays)}`; // Deadline already passed (positive number)
  }
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

      // Ubah fungsi getStatusBadge agar menerima endDateStr
      const getStatusBadge = (status, endDateStr) => {
        // Extract progress information from status if available
        const progressMatch = status.match(/\((\d+)\/(\d+)\)/);
        let tooltipText = status;
        let progressInfo = '';
        
        if (progressMatch) {
          const uploaded = parseInt(progressMatch[1]);
          const total = parseInt(progressMatch[2]);
          const percentage = Math.round((uploaded / total) * 100);
          progressInfo = ` (${percentage}% selesai)`;
          tooltipText = status + progressInfo;
        }
        
        if (status.startsWith('Sudah')) {
          return `<div class="status-icon status-success" title="${tooltipText}">
                    <div class="status-circle">
                      <i class="fas fa-check"></i>
                    </div>
                  </div>`;
        }
        if (status.startsWith('Belum')) {
          // Cek apakah hari ini sudah lewat tanggal selesai
          let isOverdue = false;
          if (endDateStr && endDateStr !== '-') {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const parts = endDateStr.split('-');
            if (parts.length === 3) {
              const endDate = new Date(parts[0], parts[1] - 1, parts[2]);
              endDate.setHours(23, 59, 59, 999);
              isOverdue = today > endDate;
            }
          }
          if (isOverdue) {
            // Sudah lewat deadline, merah
            return `<div class="status-icon status-danger" title="${tooltipText} (Terlambat)">
                      <div class="status-circle">
                        <i class="fas fa-minus"></i>
                      </div>
                    </div>`;
          } else {
            // Belum lewat deadline, orange
            return `<div class="status-icon status-warning" title="${tooltipText} (Masih dalam tenggat)">
                      <div class="status-circle">
                        <i class="fas fa-exclamation"></i>
                      </div>
                    </div>`;
          }
        }
        // Gabungkan "Tidak perlu" dan "Tidak tersedia" menjadi satu dengan logo abu-abu dan tulisan "NA"
        if (status === 'Tidak perlu' || status.includes('Tidak ada aksi') || status === 'Tidak tersedia') {
          return `<div class="status-icon status-neutral" title="Tidak Perlu">
                    <div class="status-circle">
                      <i class="fas fa-ban"></i>
                    </div>
                  </div>`;
        }
        return `<div class="status-icon status-warning" title="${tooltipText}">
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
        else if (level === 3) result = "Kabkot";
        else result = "Tidak diketahui";
        
        return result;
      };



      // Calculate and display statistics for monitoring
      const calculateMonitoringStatistics = (regions) => {
        const stats = {
          sudah: 0,
          belum: 0,
          sedangBerjalan: 0,
          tidakPerlu: 0,
          total: 0
        };
        
        // Count individual cells (activity × region combinations)
        for (const key in activityData) {
          const activity = activityData[key];
          
          // Apply level filter if active
          if (activeLevelFilters.size > 0) {
            const rawLevel = activity.assessmentLevel || 1;
            if (!activeLevelFilters.has(rawLevel)) {
              continue; // Skip this activity if level filter doesn't match
            }
          }
          
          // Check if activity dates are in range for "sedang berjalan" (per row)
          const isInDateRange = isDateInRange(activity.start, activity.end);
          if (isInDateRange) {
            stats.sedangBerjalan++;
          }
          
          // Count status for each region (each cell)
          regions.forEach(region => {
            const status = activity.statuses[region.id] || "Tidak tersedia";
            
            // Count total cells
            stats.total++;
            
            // Count by status type
            if (status.startsWith('Sudah')) {
              stats.sudah++;
            } else if (status.startsWith('Belum')) {
              stats.belum++;
            } else if (status === 'Tidak perlu') {
              stats.tidakPerlu++;
            }
            // Note: "Tidak tersedia" and other statuses are not counted in specific categories
          });
        }
        
        // Update UI
        // Update stats cards only if monitoring cards are enabled
        if (window.appConfig && window.appConfig.showMonitoringCards) {
          $("#statSudah").text(stats.sudah);
          $("#statBelum").text(stats.belum);
          $("#statSedangBerjalan").text(stats.sedangBerjalan);
          $("#statTidakPerlu").text(stats.tidakPerlu);
          $("#statTotal").text(stats.total);
          $("#statsCards").show();
        }
        $("#levelFilterCards").show();
      };

      // --- Fungsi untuk merender body tabel saja (untuk filtering) ---
      const renderTableBody = (regions) => {
        // Urutkan regions berdasarkan kode, bukan nama
        regions.sort((a, b) => {
          // Pusat selalu di awal
          if (a.id === "pusat") return -1;
          if (b.id === "pusat") return 1;
          
          // Urutkan berdasarkan kode prov dan kab
          return a.id.localeCompare(b.id);
        });
        
        // Versi tabel tanpa merge - setiap baris menampilkan semua data lengkap
        const orderedActivities = [];
        
        // 1. Ambil semua aktivitas dan urutkan berdasarkan gate, UK, dan proses
        for (const key in activityData) {
          const data = activityData[key];
          
          // Apply level filter if active
          if (activeLevelFilters.size > 0) {
            const rawLevel = data.assessmentLevel || 1;
            if (!activeLevelFilters.has(rawLevel)) {
              continue; // Skip this activity if level filter doesn't match
            }
          }
          
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
        
        // 3. Urutkan seluruh aktivitas berdasarkan sorting yang dipilih
        if (sortColumn) {
          orderedActivities.sort((a, b) => {
            const valueA = getSortValue(a, sortColumn);
            const valueB = getSortValue(b, sortColumn);
            
            if (valueA < valueB) return sortDirection === 'asc' ? -1 : 1;
            if (valueA > valueB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
          });
        } else {
          // Default sorting berdasarkan gate, UK, dan proses
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
        }
        
        // 4. Buat baris untuk setiap aktivitas (tanpa merge)
        let tableBodyHtml = '';
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
          else if (rawLevel === 3) levelLabel = "Kabkot";
          else levelLabel = "Tidak diketahui";
          
          // Ambil nomor gate dan uk untuk badge warna
          let gateNum = 1;
          let ukNum = 1;
          const gateMatch = data.gate.match(/GATE\s?(\d+)/i);
          if (gateMatch) gateNum = parseInt(gateMatch[1]);
          const ukMatch = data.uk.match(/UK\s?(\d+)/i);
          if (ukMatch) ukNum = parseInt(ukMatch[1]);
          
          tableBodyHtml += `<tr class="${rowClass}">`;
          
          // Tampilkan semua kolom untuk setiap baris (tanpa rowspan)
          tableBodyHtml += `
            <td>
              <span class="gate-badge gate-badge-${gateNum}" title="${data.gate}">
                GATE ${gateNum}
              </span>
            </td>
            <td>
              <span class="uk-badge uk-badge-${ukNum}" title="${data.uk}">
                UK ${ukNum}
              </span>
            </td>
            <td style="text-align: center;">
              <span class="level-badge level-badge-${levelLabel.toLowerCase()}" title="${levelLabel}">
                ${levelLabel}
              </span>
            </td>
          `;
          
          // Tentukan apakah tanggal dalam rentang aktif
          const startDate = data.start;
          const endDate = data.end;
          const isInDateRange = isDateInRange(startDate, endDate);
          
          // Tambahkan class untuk tanggal yang dalam rentang
          const startDateClass = isInDateRange ? 'date-in-range' : '';
          const endDateClass = isInDateRange ? 'date-in-range' : '';
          
          tableBodyHtml += `
            <td><span class="activity-number">${activityNumber}</span><span class="activity-text">${data.activity}</span></td>
            <td class="date-column ${startDateClass}">${formatDate(startDate)}</td>
            <td class="date-column ${endDateClass}">${formatDate(endDate)}</td>
            <td class="date-column">H${calculateDaysUntilDeadline(endDate)}</td>
          `;
          
          // Tambahkan status untuk setiap wilayah
          regions.forEach(region => {
            const status = data.statuses[region.id] || "Tidak perlu";
            tableBodyHtml += `<td class="status-column">${getStatusBadge(status, data.end)}</td>`;
          });
          
          tableBodyHtml += `</tr>`;
        }
        
        // Update table body
        $('.table-monitoring tbody').html(tableBodyHtml);
        
        // Update sort icons
        updateSortIcons();
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
              <div class="table-header-actions">
                <span id="resultCount" class="badge bg-primary rounded-pill">${Object.keys(activityData).length} aktivitas</span>
                <button id="downloadExcel" class="download-btn" style="display: none;">
                  <i class="fas fa-file-excel"></i>
                  Download Excel
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-wrapper">
                <table class="table-monitoring">
                  <thead>
                    <tr>
                      <th class="sortable-header" data-sort="gate" style="width: 60px;">Gate<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header" data-sort="uk" style="width: 80px;">UK<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header" data-sort="level">Level<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header" data-sort="activity">Aktivitas<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header date-column" data-sort="start">Mulai<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header date-column" data-sort="end">Selesai<i class="fas fa-sort sort-icon"></i></th>
                      <th class="sortable-header date-column" data-sort="deadline">Deadline<i class="fas fa-sort sort-icon"></i></th>
        `;
        
        // Tambahkan kolom status untuk setiap wilayah
        regions.forEach(region => {
          // Use getDaerahName function to get proper name from daftar_daerah.json
          let regionName = getDaerahName(region.id);
          tableHtml += `<th class="status-column region-header">${regionName}</th>`;
        });
        
        tableHtml += `
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        `;
        
        $resultsContainer.html(tableHtml);
        
        // Simpan regions yang sedang ditampilkan untuk export Excel
        currentDisplayRegions = [...regions];
        
        // Render table body after HTML is displayed
        renderTableBody(regions);
        
        // Update sort icons
        updateSortIcons();
        
        // Update result count with filtered activities
        const orderedActivities = Object.keys(activityData);
        $("#resultCount").text(`${orderedActivities.length} aktivitas`);
        
        // Show download button
        $("#downloadExcel").show();
        
        // Show toggle button and secondary filter, then populate it
        $("#toggleAdvancedFiltersContainer").show();
        populateSecondaryFilters();
        
        // Calculate and display statistics using filtered data
        calculateMonitoringStatistics(regions);
      };

      // Function to apply level filter
      const applyLevelFilter = () => {
        // Find current regions from last display
        const currentRegions = [];
        $('.region-header').each(function() {
          const name = $(this).text().trim();
          // Try to match with existing regions
          if (name === "Pusat") {
            currentRegions.push({ id: "pusat", prov: "00", kab: "00", name: "Pusat" });
          } else {
            // For other regions, try to find from coverageData by matching the displayed name
            const region = coverageData.find(r => {
              const regionName = getDaerahName(r.id);
              return regionName === name;
            });
            if (region) {
              currentRegions.push(region);
            }
          }
        });
        
        if (currentRegions.length > 0) {
          displayResultTable(currentRegions);
        }
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
                name: getDaerahName(`${cov.prov}${cov.kab}`)
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
                // Buat virtual province dengan nama dari daftar_daerah.json
                const provinceName = getDaerahName(`${provCode}00`);
                
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
                text: getDaerahName(province.id)
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
                name: getDaerahName(`${cov.prov}${cov.kab}`)
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
                  // Buat virtual provinsi entry dengan nama dari daftar_daerah.json
                  const provinceName = getDaerahName(`${currentUser.prov}00`);
                  
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
                name: getDaerahName(`${cov.prov}${cov.kab}`)
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
        allActivityData = {};
        
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
                const activityInfo = {
                  gate: gateName,
                  uk: ukName,
                  ukLevel: ukLevel,
                  assessmentLevel: assessmentLevel,
                  activity: activity.name,
                  start: activity.start,
                  end: activity.end,
                  statuses: {},
                  totalPreventives: {},
                  totalCorrectives: {}
                };
                activityData[activityKey] = activityInfo;
                allActivityData[activityKey] = JSON.parse(JSON.stringify(activityInfo)); // Deep copy
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
                  activityData[activityKey].statuses[region.id] = "Tidak perlu";
                  continue;
                }
                
                // Simpan data total untuk region ini
                activityData[activityKey].totalPreventives[region.id] = regionData.total_preventives[measurement.id] || 0;
                activityData[activityKey].totalCorrectives[region.id] = regionData.total_correctives[measurement.id] || 0;
                allActivityData[activityKey].totalPreventives[region.id] = regionData.total_preventives[measurement.id] || 0;
                allActivityData[activityKey].totalCorrectives[region.id] = regionData.total_correctives[measurement.id] || 0;
                
                // Tentukan status berdasarkan aktivitas
                const status = determineActivityStatusFromData(
                  regionData, measurement, activity.name
                );
                
                activityData[activityKey].statuses[region.id] = status;
                allActivityData[activityKey].statuses[region.id] = status;
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
        const totalPreventiveCount = regionData.total_preventives[measurement.id] || 0;
        const totalCorrectiveCount = regionData.total_correctives[measurement.id] || 0;
        
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
            if (totalPreventiveCount === 0) return "Tidak ada aksi preventif";
            
            // Bandingkan jumlah yang diupload dengan total yang seharusnya
            if (docPreventiveCount >= totalPreventiveCount) {
              return `Sudah diunggah (${docPreventiveCount}/${totalPreventiveCount})`;
            } else if (docPreventiveCount > 0) {
              return `Belum diunggah (${docPreventiveCount}/${totalPreventiveCount})`;
            } else {
              return `Belum diunggah (0/${totalPreventiveCount})`;
            }
            
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
            if (totalCorrectiveCount === 0) return "Tidak ada aksi korektif";
            
            // Bandingkan jumlah yang diupload dengan total yang seharusnya
            if (docCorrectiveCount >= totalCorrectiveCount) {
              return `Sudah diunggah (${docCorrectiveCount}/${totalCorrectiveCount})`;
            } else if (docCorrectiveCount > 0) {
              return `Belum diunggah (${docCorrectiveCount}/${totalCorrectiveCount})`;
            } else {
              return `Belum diunggah (0/${totalCorrectiveCount})`;
            }
            
          default:
            return "Tidak perlu";
        }
      };

      // --- Helper function to hide table and stats ---
      const hideTableAndStats = () => {
        $resultsContainer.empty();
        $("#statsCards").hide();
        $("#levelFilterCards").hide();
        $("#secondaryFilterCard").hide();
        $("#toggleAdvancedFiltersContainer").hide();
        
        // Hide activity name display
        $('.activity-name-display').remove();
        
        activityData = {}; // Clear activity data
        allActivityData = {}; // Clear all data
        activeLevelFilters.clear(); // Clear level filters
        $('.level-filter-card').removeClass('active'); // Reset level filter cards
        sortColumn = null; // Reset sorting
        sortDirection = 'asc';
        // Reset secondary filters
        Object.keys(secondaryFilters).forEach(key => {
          secondaryFilters[key] = [];
        });
      };

      // --- Secondary Filter Functions ---
      const populateSecondaryFilters = () => {
        // Save current selected values before resetting dropdowns
        const currentSelections = {
          gate: $("#filterGate").val() || [],
          uk: $("#filterUK").val() || [],
          activity: $("#filterActivity").val() || [],
          status: $("#filterStatus").val() || []
        };
        
        // Populate Gate filter
        const gates = new Set();
        const uks = new Set();
        const activities = new Set();
        const statuses = new Set();
        
        Object.values(allActivityData).forEach(data => {
          // Extract gate number
          const gateMatch = data.gate.match(/GATE\s?(\d+)/i);
          if (gateMatch) gates.add(parseInt(gateMatch[1]));
          
          // Extract UK number
          const ukMatch = data.uk.match(/UK\s?(\d+)/i);
          if (ukMatch) uks.add(parseInt(ukMatch[1]));
          
          // Add activity
          activities.add(data.activity);
          
          // Add all statuses from all regions
          Object.values(data.statuses).forEach(status => {
            if (status && status !== "Tidak tersedia") {
              statuses.add(status);
            }
          });
        });
        
        // Populate Gate dropdown
        const $filterGate = $("#filterGate");
        $filterGate.empty().append('<option value="">Semua Gate</option>');
        Array.from(gates).sort((a, b) => a - b).forEach(gateNum => {
          $filterGate.append(`<option value="${gateNum}">GATE ${gateNum}</option>`);
        });
        
        // Populate UK dropdown
        const $filterUK = $("#filterUK");
        $filterUK.empty().append('<option value="">Semua UK</option>');
        Array.from(uks).sort((a, b) => a - b).forEach(ukNum => {
          $filterUK.append(`<option value="${ukNum}">UK ${ukNum}</option>`);
        });
        
        // Populate Activity dropdown
        const $filterActivity = $("#filterActivity");
        $filterActivity.empty().append('<option value="">Semua Aktivitas</option>');
        const activityOrder = {
          "Pengisian nama pelaksana aksi preventif": 1,
          "Upload bukti pelaksanaan aksi preventif": 2,
          "Penilaian ukuran kualitas": 3,
          "Approval Gate oleh Sign-off": 4,
          "Pengisian pelaksana aksi korektif": 5,
          "Upload bukti pelaksanaan aksi korektif": 6
        };
        Array.from(activities).sort((a, b) => (activityOrder[a] || 0) - (activityOrder[b] || 0)).forEach(activity => {
          $filterActivity.append(`<option value="${activity}">${activity}</option>`);
        });
        
        // Populate Status dropdown with advanced options
        const $filterStatus = $("#filterStatus");
        $filterStatus.empty().append('<option value="">Semua Status</option>');
        
        // Check if advanced status filter is enabled
        if (window.appConfig && window.appConfig.advancedStatusFilter) {
          $filterStatus.append('<option value="sudah">Sudah</option>');
          $filterStatus.append('<option value="belum">Belum</option>');
        } else {
          // Legacy options for backward compatibility
          $filterStatus.append('<option value="Sudah">Sudah</option>');
          $filterStatus.append('<option value="Belum">Belum</option>');
          $filterStatus.append('<option value="Tidak Perlu">Tidak Perlu</option>');
        }
        
        // Restore selected values and visual feedback
        setTimeout(() => {
          // Restore selections
          $filterGate.val(currentSelections.gate);
          $filterUK.val(currentSelections.uk);
          $filterActivity.val(currentSelections.activity);
          $filterStatus.val(currentSelections.status);
          
          // Restore visual feedback in a single batch to prevent flickering
          const filtersToRestore = [
            { type: 'gate', values: currentSelections.gate },
            { type: 'uk', values: currentSelections.uk },
            { type: 'activity', values: currentSelections.activity },
            { type: 'status', values: currentSelections.status }
          ];
          
          filtersToRestore.forEach(filter => {
            if (filter.values.length > 0) {
              const $select = $(`#filter${filter.type.charAt(0).toUpperCase() + filter.type.slice(1)}`);
              const $label = $select.prev('label');
              
              // Apply visual feedback directly without calling updateFilterVisualFeedback
              $select.addClass('filter-active has-selected-options');
              $label.addClass('filter-label-active');
            }
          });
        }, 5);
      };

      // Function to update visual feedback for filters
      const updateFilterVisualFeedback = (filterType, values) => {
        const $select = $(`#filter${filterType.charAt(0).toUpperCase() + filterType.slice(1)}`);
        const $label = $select.prev('label');
        
        // Remove existing active class
        $select.removeClass('filter-active has-selected-options');
        $label.removeClass('filter-label-active');
        
        if (values.length > 0) {
          // Add active styling
          $select.addClass('filter-active has-selected-options');
          $label.addClass('filter-label-active');
        } else {
          // Remove selected class when no values
          $select.removeClass('has-selected-options');
        }
      };

      const applySecondaryFilters = () => {
        // Filter activityData based on secondary filters
        const filteredData = {};
        
        Object.entries(allActivityData).forEach(([key, data]) => {
          let include = true;
          
          // Filter by Gate
          if (secondaryFilters.gate.length > 0) {
            const gateMatch = data.gate.match(/GATE\s?(\d+)/i);
            const gateNum = gateMatch ? parseInt(gateMatch[1]) : 0;
            if (!secondaryFilters.gate.includes(gateNum.toString())) {
              include = false;
            }
          }
          
          // Filter by UK
          if (include && secondaryFilters.uk.length > 0) {
            const ukMatch = data.uk.match(/UK\s?(\d+)/i);
            const ukNum = ukMatch ? parseInt(ukMatch[1]) : 0;
            if (!secondaryFilters.uk.includes(ukNum.toString())) {
              include = false;
            }
          }
          
          // Filter by Level
          if (include && secondaryFilters.level.length > 0) {
            const level = data.assessmentLevel || 1;
            if (!secondaryFilters.level.includes(level.toString())) {
              include = false;
            }
          }
          
          // Filter by Activity
          if (include && secondaryFilters.activity.length > 0) {
            if (!secondaryFilters.activity.includes(data.activity)) {
              include = false;
            }
          }
          
          // Filter by Status with advanced logic
          if (include && secondaryFilters.status.length > 0) {
            let hasMatchingStatus = false;
            
            // Check if advanced status filter is enabled
            if (window.appConfig && window.appConfig.advancedStatusFilter) {
              // Advanced status filtering logic
              if (secondaryFilters.status.includes('semua')) {
                hasMatchingStatus = true; // Show all records
              } else if (secondaryFilters.status.includes('sudah')) {
                // Show records where minimal 1 "sudah" AND tidak ada "belum"
                const allStatuses = Object.values(data.statuses);
                const hasSudah = allStatuses.some(status => {
                  return status.includes('Sudah') || status.includes('sudah') || 
                         status.includes('Selesai') || status.includes('selesai') ||
                         status.includes('Ditentukan') || status.includes('ditentukan') ||
                         status.includes('Sudah ditentukan') || status.includes('sudah ditentukan');
                });
                const hasBelum = allStatuses.some(status => {
                  return status.includes('Belum') || status.includes('belum') ||
                         status.includes('Belum ditentukan') || status.includes('belum ditentukan');
                });
                // Hanya tampilkan jika ada minimal 1 "sudah" DAN tidak ada "belum" sama sekali
                hasMatchingStatus = hasSudah && !hasBelum;
              } else if (secondaryFilters.status.includes('belum')) {
                // Show records where minimal 1 "belum"
                const allStatuses = Object.values(data.statuses);
                hasMatchingStatus = allStatuses.some(status => {
                  return status.includes('Belum') || status.includes('belum') ||
                         status.includes('Belum ditentukan') || status.includes('belum ditentukan');
                });
              }
            } else {
              // Legacy status filtering logic
              const allStatuses = Object.values(data.statuses);
              if (secondaryFilters.status.includes('Sudah')) {
                // Legacy: Show records where minimal 1 "sudah" AND tidak ada "belum"
                const hasSudah = allStatuses.some(status => {
                  return status.includes('Sudah') || status.includes('sudah') || 
                         status.includes('Selesai') || status.includes('selesai') ||
                         status.includes('Ditentukan') || status.includes('ditentukan') ||
                         status.includes('Sudah ditentukan') || status.includes('sudah ditentukan');
                });
                const hasBelum = allStatuses.some(status => {
                  return status.includes('Belum') || status.includes('belum') ||
                         status.includes('Belum ditentukan') || status.includes('belum ditentukan');
                });
                hasMatchingStatus = hasSudah && !hasBelum;
              } else if (secondaryFilters.status.includes('Belum')) {
                hasMatchingStatus = allStatuses.some(status => {
                  return status.includes('Belum') || status.includes('belum') ||
                         status.includes('Belum ditentukan') || status.includes('belum ditentukan');
                });
              } else if (secondaryFilters.status.includes('Tidak Perlu')) {
                hasMatchingStatus = allStatuses.some(status => {
                  return status.includes('Tidak Perlu') || status.includes('tidak perlu') ||
                         status.includes('Tidak perlu') || status.includes('tidak Perlu');
                });
              } else {
                hasMatchingStatus = allStatuses.some(status => secondaryFilters.status.includes(status));
              }
            }
            
            if (!hasMatchingStatus) {
              include = false;
            }
          }
          
          // Filter by Deadline
          if (include && secondaryFilters.deadline.length > 0) {
            const deadline = calculateDaysUntilDeadline(data.end);
            let deadlineCategory = '';
            
            if (deadline === '-') {
              deadlineCategory = 'all';
            } else {
              const days = parseInt(deadline.replace('+', '')) || 0;
              if (days < 0) {
                deadlineCategory = 'overdue';
              } else if (days <= 3) {
                deadlineCategory = '3days';
              } else if (days <= 7) {
                deadlineCategory = 'week';
              } else if (days <= 30) {
                deadlineCategory = 'month';
              } else {
                deadlineCategory = 'all';
              }
            }
            
            if (!secondaryFilters.deadline.includes(deadlineCategory)) {
              include = false;
            }
          }
          
          if (include) {
            filteredData[key] = data;
          }
        });
        
        // Update activityData with filtered results
        activityData = filteredData;
        
        // Re-display table with filtered data (only if we have regions to display)
        if (currentDisplayRegions.length > 0) {
          // Re-render the table body only, not the entire table
          renderTableBody(currentDisplayRegions);
          
          // Update statistics
          calculateMonitoringStatistics(currentDisplayRegions);
          
          // Update result count
          $("#resultCount").text(`${Object.keys(activityData).length} aktivitas`);
        }
      };

      // --- Sorting Functions ---
      const sortData = (column) => {
        if (sortColumn === column) {
          sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
          sortColumn = column;
          sortDirection = 'asc';
        }
        
        updateSortIcons();
        displayResultTable(currentDisplayRegions);
      };

      const updateSortIcons = () => {
        $('.sortable-header').removeClass('sorted');
        $('.sort-icon').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        
        if (sortColumn) {
          const $th = $(`.sortable-header[data-sort="${sortColumn}"]`);
          $th.addClass('sorted');
          const $icon = $th.find('.sort-icon');
          $icon.removeClass('fa-sort').addClass(sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
        }
      };

      const getSortValue = (data, column) => {
        switch(column) {
          case 'gate':
            const gateMatch = data.gate.match(/GATE\s?(\d+)/i);
            return gateMatch ? parseInt(gateMatch[1]) : 0;
          case 'uk':
            const ukMatch = data.uk.match(/UK\s?(\d+)/i);
            return ukMatch ? parseInt(ukMatch[1]) : 0;
          case 'level':
            return data.assessmentLevel || 1;
          case 'activity':
            const activityOrder = {
              "Pengisian nama pelaksana aksi preventif": 1,
              "Upload bukti pelaksanaan aksi preventif": 2,
              "Penilaian ukuran kualitas": 3,
              "Approval Gate oleh Sign-off": 4,
              "Pengisian pelaksana aksi korektif": 5,
              "Upload bukti pelaksanaan aksi korektif": 6
            };
            return activityOrder[data.activity] || 0;
          case 'start':
            return data.start ? new Date(data.start) : new Date('1900-01-01');
          case 'end':
            return data.end ? new Date(data.end) : new Date('1900-01-01');
          case 'deadline':
            const deadline = calculateDaysUntilDeadline(data.end);
            if (deadline === '-') return 999999;
            return parseInt(deadline.replace('+', '')) || 0;
          default:
            return '';
        }
      };

      // --- Excel Export Function ---
      const exportToExcel = () => {
        if (Object.keys(activityData).length === 0) {
          showError("Tidak ada data untuk diekspor");
          return;
        }

        try {
          // Prepare data for export
          const exportData = [];
          
          // Gunakan regions yang sama dengan yang ditampilkan di tabel
          const currentRegions = currentDisplayRegions.map(region => ({
            id: region.id,
            name: getDaerahName(region.id)
          }));

          // Create header row
          const headerRow = [
            'Gate',
            'Ukuran Kualitas', 
            'Level',
            'Aktivitas',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Deadline (Hari)',
            ...currentRegions.map(region => region.name)
          ];
          exportData.push(headerRow);

          // Add data rows
          const orderedActivities = [];
          for (const key in activityData) {
            const data = activityData[key];
            
            // Apply level filter if active
            if (activeLevelFilters.size > 0) {
              const rawLevel = data.assessmentLevel || 1;
              if (!activeLevelFilters.has(rawLevel)) {
                continue;
              }
            }
            
            orderedActivities.push(data);
          }


          // Sort activities
          const activityOrder = {
            "Pengisian nama pelaksana aksi preventif": 1,
            "Upload bukti pelaksanaan aksi preventif": 2,
            "Penilaian ukuran kualitas": 3,
            "Approval Gate oleh Sign-off": 4,
            "Pengisian pelaksana aksi korektif": 5,
            "Upload bukti pelaksanaan aksi korektif": 6
          };

          orderedActivities.sort((a, b) => {
            const gateNumA = parseInt(a.gate.match(/GATE(\d+)/)[1]);
            const gateNumB = parseInt(b.gate.match(/GATE(\d+)/)[1]);
            
            if (gateNumA !== gateNumB) {
              return gateNumA - gateNumB;
            }
            
            const ukNumA = parseInt(a.uk.match(/UK(\d+)/)[1]);
            const ukNumB = parseInt(b.uk.match(/UK(\d+)/)[1]);
            
            if (ukNumA !== ukNumB) {
              return ukNumA - ukNumB;
            }
            
            return activityOrder[a.activity] - activityOrder[b.activity];
          });

          // Add data rows
          orderedActivities.forEach(data => {
            const activityNumber = activityOrder[data.activity];
            const rawLevel = data.assessmentLevel || 1;
            let levelLabel;
            if (rawLevel === 1) levelLabel = "Pusat";
            else if (rawLevel === 2) levelLabel = "Provinsi";
            else if (rawLevel === 3) levelLabel = "Kabkot";
            else levelLabel = "Tidak diketahui";

            const row = [
              data.gate,
              data.uk,
              levelLabel,
              `${activityNumber}. ${data.activity}`,
              data.start || '-',
              data.end || '-',
              calculateDaysUntilDeadline(data.end),
              ...currentRegions.map(region => {
                // Gunakan data status yang sama dengan yang ditampilkan di tabel
                const status = data.statuses[region.id] || "Tidak tersedia";
                return status;
              })
            ];
            exportData.push(row);
          });

          // Create workbook and worksheet
          const wb = XLSX.utils.book_new();
          const ws = XLSX.utils.aoa_to_sheet(exportData);

          // Set column widths
          const colWidths = [
            { wch: 20 }, // Gate
            { wch: 20 }, // UK
            { wch: 12 }, // Level
            { wch: 40 }, // Aktivitas
            { wch: 15 }, // Mulai
            { wch: 15 }, // Selesai
            { wch: 12 }, // Deadline
            ...currentRegions.map(() => ({ wch: 20 })) // Region columns
          ];
          ws['!cols'] = colWidths;

          // Add worksheet to workbook
          XLSX.utils.book_append_sheet(wb, ws, 'Monitoring Data');

          // Generate filename with timestamp
          const now = new Date();
          const timestamp = now.toISOString().slice(0, 19).replace(/:/g, '-');
          const filename = `Monitoring_Quality_Gates_${timestamp}.xlsx`;

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
          debugLog('Excel export error: ' + error.message, 'error');
          showError("Terjadi kesalahan saat mengekspor ke Excel: " + error.message);
        }
      };

      // --- Event Handlers ---

      // Logout handler
      $("#logoutBtn").on('click', function(){
        if (confirm('Apakah Anda yakin ingin logout?')) {
          window.location.href = 'sso_logout.php';
        }
      });

      // Level Filter handlers
      $(document).on('click', '.level-filter-card', function(){
        const level = $(this).data('level');
        const $card = $(this);
        
        // Toggle active state
        if ($card.hasClass('active')) {
          $card.removeClass('active');
          activeLevelFilters.delete(level);
        } else {
          $card.addClass('active');
          activeLevelFilters.add(level);
        }
        
        // Re-filter table if data is already loaded
        if (Object.keys(activityData).length > 0) {
          applyLevelFilter();
        }
      });

      // Download Excel handler
      $(document).on('click', '#downloadExcel', function(){
        exportToExcel();
      });

      // Table sorting event handlers
      $(document).on('click', '.sortable-header', function(){
        const column = $(this).data('sort');
        sortData(column);
      });

      // Secondary filter event handlers
      $(document).on('change', '#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline', function(){
        const filterType = $(this).attr('id').replace('filter', '').toLowerCase();
        let selectedValues = $(this).val() || [];
        
        // Handle multiple select - if it's not an array, make it one
        if (!Array.isArray(selectedValues)) {
          selectedValues = [selectedValues];
        }
        
        // Remove empty string values (for "Semua" options)
        const cleanValues = selectedValues.filter(val => val !== '' && val !== null);
        secondaryFilters[filterType] = cleanValues;
        
        // Update visual feedback
        updateFilterVisualFeedback(filterType, cleanValues);
        
        // Save to persistence manager
        if (window.persistenceManager) {
          const filters = {
            project: selectedProject,
            projectName: $('#projectSearch').val(),
            region: selectedRegion,
            regionName: $('#regionSearch').val(),
            secondary: secondaryFilters
          };
          window.persistenceManager.saveFilters(filters);
        }
        
        applySecondaryFilters();
      });
      
      // Ensure visual feedback persists after losing focus
      $(document).on('blur', '#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline', function(){
        const filterType = $(this).attr('id').replace('filter', '').toLowerCase();
        let selectedValues = $(this).val() || [];
        
        // Handle multiple select - if it's not an array, make it one
        if (!Array.isArray(selectedValues)) {
          selectedValues = [selectedValues];
        }
        
        // Remove empty string values (for "Semua" options)
        const cleanValues = selectedValues.filter(val => val !== '' && val !== null);
        
        // Re-apply visual feedback to ensure it persists
        if (cleanValues.length > 0) {
          const $select = $(this);
          $select.addClass('filter-active has-selected-options');
          $select.prev('label').addClass('filter-label-active');
        }
      });

      // Clear secondary filters
      $(document).on('click', '#clearSecondaryFilters', function(){
        // Reset all filter dropdowns
        $('#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline').val([]);
        
        // Remove all visual feedback classes
        $('#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline').removeClass('filter-active has-selected-options');
        $('#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline').prev('label').removeClass('filter-label-active');
        
        // Reset secondary filters object
        Object.keys(secondaryFilters).forEach(key => {
          secondaryFilters[key] = [];
        });
        
        // Reset visual feedback for all filters
        $('#filterGate, #filterUK, #filterLevel, #filterActivity, #filterStatus, #filterDeadline').each(function(){
          const filterType = $(this).attr('id').replace('filter', '').toLowerCase();
          updateFilterVisualFeedback(filterType, []);
        });
        
        // Reset activityData to all data
        activityData = JSON.parse(JSON.stringify(allActivityData));
        
        // Re-display table
        if (currentDisplayRegions.length > 0) {
          displayResultTable(currentDisplayRegions);
        }
      });

      // Toggle advanced filters
      $(document).on('click', '#toggleAdvancedFilters', function(){
        const $card = $('#secondaryFilterCard');
        const $icon = $(this).find('i');
        const $text = $(this).contents().filter(function() {
          return this.nodeType === 3; // Text nodes
        }).last();
        
        if ($card.is(':visible')) {
          $card.slideUp(300);
          $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
          $text.replaceWith(' Filter Lanjutan');
        } else {
          $card.slideDown(300);
          $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
          $text.replaceWith(' Sembunyikan Filter');
        }
      });

      $yearSelect.on('change', async function(){
        year = $(this).val();
        
        // Hide table and stats when year changes
        hideTableAndStats();
        
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

      // Fungsi untuk memuat data dari localStorage jika tersedia
      const loadDataFromLocalStorage = () => {
        try {
          // Cek apakah ada data monitoring tersimpan
          const savedMonitoringData = localStorage.getItem('monitoringData');
          const savedRegions = localStorage.getItem('monitoringRegions');
          const savedFilters = localStorage.getItem('monitoringFilters');
          
          debugLog('🔍 [MONITORING] Checking localStorage: hasMonitoringData=' + !!savedMonitoringData + ', hasRegions=' + !!savedRegions + ', hasFilters=' + !!savedFilters);
          
          if (savedMonitoringData && savedRegions) {
            // Data tersimpan ditemukan, lanjutkan loading
            
            // Parse data yang tersimpan
            activityData = JSON.parse(savedMonitoringData);
            allActivityData = JSON.parse(savedMonitoringData);
            const regions = JSON.parse(savedRegions);
            
            // Jika ada filter tersimpan, terapkan
            if (savedFilters) {
              secondaryFilters = JSON.parse(savedFilters);
              
              // Update visual feedback untuk filter
              Object.keys(secondaryFilters).forEach(key => {
                if (secondaryFilters[key].length > 0) {
                  $(`#filter${key.charAt(0).toUpperCase() + key.slice(1)}`).val(secondaryFilters[key]);
                  updateFilterVisualFeedback(key, secondaryFilters[key]);
                }
              });
            }
            
            // Apply saved filters using persistence manager
            if (window.persistenceManager) {
              window.persistenceManager.applySavedFilters();
              window.persistenceManager.showSavedActivityName();
            }
            
            // Tampilkan data
            debugLog('✅ [MONITORING] Successfully loaded data from localStorage: activities=' + Object.keys(activityData).length + ', regions=' + regions.length + ', currentDisplayRegions=' + regions.length);
            
            displayResultTable(regions);
            currentDisplayRegions = regions;
            
            return true;
          } else {
            debugLog('ℹ️ [MONITORING] No saved data found in localStorage');
          }
        } catch (error) {
          debugLog("Error loading data from localStorage: " + error.message, 'error');
        }
        
        return false;
      };
      
      // Cek localStorage saat halaman dimuat
      $(document).ready(function() {
        // Muat data daerah terlebih dahulu
        loadDaerahData().then((success) => {
          if (success) {
            debugLog('✅ [MONITORING] Daerah data loaded successfully');
          } else {
            debugLog('⚠️ [MONITORING] Daerah data loaded with fallback', 'warn');
          }
          
          // Apply saved filters first before loading data
          if (window.persistenceManager) {
            window.persistenceManager.applySavedFilters();
          }
          
          // Setelah data daerah dimuat (atau fallback), coba load dari localStorage
          loadDataFromLocalStorage();
          
          // Show saved activity name after data is loaded
          if (window.persistenceManager) {
            window.persistenceManager.showSavedActivityName();
          }
        }).catch((error) => {
          debugLog('❌ [MONITORING] Failed to load daerah data: ' + error.message, 'error');
          
          // Apply saved filters first before loading data
          if (window.persistenceManager) {
            window.persistenceManager.applySavedFilters();
          }
          
          // Even if daerah data fails, try to load from localStorage
          loadDataFromLocalStorage();
          
          // Show saved activity name after data is loaded
          if (window.persistenceManager) {
            window.persistenceManager.showSavedActivityName();
          }
        });
      });

      $("#loadData").on('click', async function(){
        // Hapus data tersimpan saat tombol "Tampilkan Data" diklik
        localStorage.removeItem('monitoringData');
        localStorage.removeItem('monitoringRegions');
        localStorage.removeItem('monitoringFilters');
        
        // Hide activity name when filter is applied
        $(".activity-name-display").remove();
        
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
          
          // Check if regions data was provided via URL (from dashboard)
          if (window.urlRegionsData && window.urlRegionsData.length > 0) {
            debugLog('🗺️ [URL] Using regions data from URL parameter');
            regionsToProcess = window.urlRegionsData;
            // Clear the stored data after use
            window.urlRegionsData = null;
          } else if (selectedRegion === "pusat") {
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
          
          // Simpan data ke localStorage untuk digunakan saat berpindah halaman
          try {
            localStorage.setItem('monitoringData', JSON.stringify(activityData));
            localStorage.setItem('monitoringRegions', JSON.stringify(regionsToProcess));
            localStorage.setItem('monitoringFilters', JSON.stringify(secondaryFilters));
            
            // Save filters and activity name using persistence manager
            if (window.persistenceManager) {
              const filters = {
                project: selectedProject,
                projectName: $('#projectSearch').val(),
                region: selectedRegion,
                regionName: $('#regionSearch').val(),
                secondary: secondaryFilters
              };
              window.persistenceManager.saveFilters(filters);
              window.persistenceManager.updateActivityName();
            }
          } catch (error) {
            debugLog("Error saving data to localStorage: " + error.message, 'error');
          }
          
          const loadTime = ((Date.now() - startTime) / 1000).toFixed(1);
          debugLog(`✅ Data monitoring berhasil dimuat dalam ${loadTime} detik (${regionsToProcess.length} wilayah)`);
          
          // Show success message in console for performance tracking
          if (regionsToProcess.length > 10) {
            debugLog(`🚀 Optimasi berhasil! Memuat ${Object.keys(activityData).length} aktivitas untuk ${regionsToProcess.length} wilayah hanya dalam ${loadTime} detik`);
          }
          
          // Show performance info to user
          setTimeout(() => {
            const toast = $(`
              <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 100px; right: 20px; z-index: 10000; min-width: 250px; font-size: 0.8rem;">
                <i class="fas fa-check-circle me-2"></i>
                <strong style="font-size: 0.85rem;">Data berhasil dimuat!</strong><br>
                <small style="font-size: 0.7rem;">${Object.keys(activityData).length} aktivitas • ${regionsToProcess.length} wilayah • ${loadTime}s</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
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

      // Function to handle URL parameters from dashboard
      const handleURLParameters = () => {
        const urlParams = new URLSearchParams(window.location.search);
        const yearParam = urlParams.get('year');
        const projectParam = urlParams.get('project');
        const regionParam = urlParams.get('region');
        const regionsParam = urlParams.get('regions'); // Legacy parameter for regions array
        const sessionKeyParam = urlParams.get('session_key'); // New parameter for session key
        
        debugLog('🔗 [URL] Parameters received: ' + JSON.stringify({
          year: yearParam,
          project: projectParam,
          region: regionParam,
          regions: regionsParam,
          session_key: sessionKeyParam
        }));
        
        // Set year if provided
        if (yearParam) {
          year = yearParam;
          $yearSelect.val(year);
        }
        
        // Set project if provided
        if (projectParam) {
          selectedProject = projectParam;
        }
        
        // Set region if provided (legacy parameter)
        if (regionParam) {
          selectedRegion = regionParam;
        }
        
        // Handle session key parameter (new optimized format from dashboard)
        if (sessionKeyParam) {
          try {
            const regionsData = JSON.parse(sessionStorage.getItem(sessionKeyParam) || '[]');
            debugLog('🗺️ [SESSION] Loaded regions data from session:', regionsData);
            
            if (regionsData.length > 0) {
              // Store regions data for direct processing
              window.urlRegionsData = regionsData;
              
              // Set selectedRegion to the first region for compatibility
              selectedRegion = regionsData[0].id;
              
              // Clean up session storage after use
              sessionStorage.removeItem(sessionKeyParam);
            } else {
              debugLog('⚠️ [SESSION] No regions data found for session key: ' + sessionKeyParam, 'warn');
            }
          } catch (error) {
            debugLog('❌ [SESSION] Failed to load regions from session: ' + error.message, 'error');
          }
        }
        
        // Handle regions parameter (legacy format - still supported for backward compatibility)
        if (regionsParam && !sessionKeyParam) {
          try {
            const regionsData = JSON.parse(decodeURIComponent(regionsParam));
            debugLog('🗺️ [URL] Parsed regions data (legacy):', regionsData);
            
            // Store regions data for direct processing
            window.urlRegionsData = regionsData;
            
            // Set selectedRegion to the first region for compatibility
            if (regionsData.length > 0) {
              selectedRegion = regionsData[0].id;
            }
          } catch (error) {
            debugLog('❌ [URL] Failed to parse regions parameter: ' + error.message, 'error');
          }
        }
        
        return {
          hasYear: !!yearParam,
          hasProject: !!projectParam,
          hasRegion: !!regionParam,
          hasRegions: !!regionsParam,
          hasSessionKey: !!sessionKeyParam
        };
      };

      // Function to handle URL parameters after data is loaded
      const handleURLParametersAfterLoad = async () => {
        try {
          debugLog('🔄 [URL] Handling parameters after data load...');
          
          // Set project if provided
          if (selectedProject) {
            // Find project name from projectData
            const project = projectData.find(p => p.value == selectedProject);
            if (project) {
              projectDropdown.setValue(selectedProject, project.text);
              debugLog('✅ [URL] Project set: ' + project.text);
            }
          }
          
          // Set region if provided and user is pusat
          if (selectedRegion && currentUser.prov === "00" && currentUser.kab === "00") {
            // Find region name from regionDropdownData
            const region = regionDropdownData.find(r => r.value == selectedRegion);
            if (region) {
              regionDropdown.setValue(selectedRegion, region.text);
              debugLog('✅ [URL] Region set: ' + region.text);
            }
          }
          
          // Auto-load data if both project and region are set, or if regions data is available
          if (selectedProject && (selectedRegion || window.urlRegionsData)) {
            debugLog('🚀 [URL] Auto-loading data with parameters...');
            // Trigger load data button click
            $("#loadData").click();
          } else if (selectedProject && currentUser.prov !== "00" && currentUser.kab !== "00") {
            // For non-pusat users, auto-load with their region
            debugLog('🚀 [URL] Auto-loading data for non-pusat user...');
            $("#loadData").click();
          }
          
        } catch (error) {
          debugLog('❌ [URL] Error handling parameters: ' + error.message, 'error');
        }
      };

      // Inisialisasi
      const initializeApp = async () => {
        if (initUser()) {
          // Handle URL parameters first
          const urlParams = handleURLParameters();
          
          // Load daerah data first
          await loadDaerahData();
          
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
              
              // Hide table and stats when project changes
              hideTableAndStats();
              
              // Reset toggle filter lanjutan to show state
              const $toggleBtn = $('#toggleAdvancedFilters');
              const $icon = $toggleBtn.find('i');
              const $text = $toggleBtn.contents().filter(function() {
                return this.nodeType === 3; // Text nodes
              }).last();
              $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
              $text.replaceWith(' Filter Lanjutan');
              
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
              
              // Hide table and stats when region changes
              hideTableAndStats();
            }
          );
          
          year = $yearSelect.val() || "2025";
          
          // Load projects untuk tahun yang sudah terpilih (default 2025)
          if (year) {
            loadProjects().then(() => {
              // If URL parameters were provided, auto-select and load data
              if (urlParams.hasProject || urlParams.hasRegion) {
                handleURLParametersAfterLoad();
              }
            });
          }
        }
      };
      
      // Start initialization
      initializeApp();
      $spinner.hide();
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <!-- SSO Wilayah Filter JavaScript -->
  <?php injectWilayahJS(); ?>
  
  <!-- Superadmin Modal -->
  <?php renderSuperAdminModal(); ?>
  
  <!-- Debug Info (hanya muncul jika ada parameter ?debug) -->
  <?php renderDebugWilayahInfo(); ?>
</body>
</html>
