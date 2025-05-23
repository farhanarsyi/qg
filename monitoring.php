<?php
// monitoring.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quality Gates Monitor - Dashboard</title>
  
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  
  <!-- Google Fonts - Modern Professional -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Feather Icons -->
  <script src="https://unpkg.com/feather-icons"></script>
  
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  
  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    :root {
      /* Green Dominant Color Scheme */
      --primary-green: #059669;      /* Emerald 600 */
      --primary-green-light: #10b981; /* Emerald 500 */
      --primary-green-dark: #047857;  /* Emerald 700 */
      --secondary-green: #d1fae5;     /* Emerald 100 */
      --accent-green: #34d399;        /* Emerald 400 */
      
      /* Supporting Colors */
      --success-color: #22c55e;       /* Green 500 */
      --warning-color: #f59e0b;       /* Amber 500 */
      --danger-color: #ef4444;        /* Red 500 */
      --info-color: #3b82f6;          /* Blue 500 */
      
      /* Neutral Colors */
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-300: #d1d5db;
      --gray-400: #9ca3af;
      --gray-500: #6b7280;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-800: #1f2937;
      --gray-900: #111827;
      
      /* Background */
      --bg-primary: #f0fdf4;          /* Green 50 */
      --bg-secondary: #ffffff;
      --bg-card: rgba(255, 255, 255, 0.95);
      
      /* Shadows */
      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      
      /* Border Radius */
      --radius-sm: 0.375rem;
      --radius-md: 0.5rem;
      --radius-lg: 0.75rem;
      --radius-xl: 1rem;
      --radius-2xl: 1.5rem;
    }
    
    * {
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, var(--bg-primary) 0%, var(--gray-50) 100%);
      color: var(--gray-800);
      line-height: 1.6;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    /* Container with adaptive width */
    .main-container {
      max-width: min(1600px, 95vw);
      margin: 0 auto;
      padding: clamp(1rem, 3vw, 2rem);
      min-height: 100vh;
    }
    
    /* Header Section */
    .header-section {
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
      border-radius: var(--radius-2xl);
      padding: clamp(2rem, 4vw, 3rem);
      margin-bottom: 2rem;
      box-shadow: var(--shadow-xl);
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .header-section::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 50%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }
    
    .header-content {
      position: relative;
      z-index: 2;
    }
    
    .header-title {
      font-size: clamp(1.75rem, 4vw, 2.5rem);
      font-weight: 800;
      margin-bottom: 0.5rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .header-subtitle {
      font-size: clamp(1rem, 2vw, 1.25rem);
      opacity: 0.9;
      font-weight: 400;
      margin: 0;
    }
    
    .header-icon {
      background: rgba(255, 255, 255, 0.2);
      padding: 1rem;
      border-radius: var(--radius-lg);
      backdrop-filter: blur(10px);
    }
    
    /* Card Styles */
    .modern-card {
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--gray-200);
      margin-bottom: 2rem;
      overflow: hidden;
      backdrop-filter: blur(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .modern-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-xl);
    }
    
    .card-header-modern {
      background: linear-gradient(135deg, var(--secondary-green) 0%, rgba(16, 185, 129, 0.1) 100%);
      border-bottom: 1px solid var(--gray-200);
      padding: 1.5rem 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .card-header-icon {
      background: var(--primary-green);
      color: white;
      width: 2.5rem;
      height: 2.5rem;
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.125rem;
    }
    
    .card-title {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--gray-800);
      margin: 0;
    }
    
    .card-body-modern {
      padding: 2rem;
    }
    
    /* Form Styles */
    .form-group-modern {
      margin-bottom: 1.5rem;
    }
    
    .form-label-modern {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--gray-700);
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    
    .form-control-modern, .form-select-modern {
      border-radius: var(--radius-lg);
      border: 2px solid var(--gray-200);
      padding: 0.875rem 1.25rem;
      font-size: 1rem;
      font-weight: 500;
      background: var(--bg-secondary);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      width: 100%;
    }
    
    .form-control-modern:focus, .form-select-modern:focus {
      border-color: var(--primary-green);
      box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
      outline: none;
      transform: translateY(-1px);
    }
    
    .form-control-modern:disabled, .form-select-modern:disabled {
      background-color: var(--gray-100);
      color: var(--gray-400);
      cursor: not-allowed;
    }
    
    /* Button Styles */
    .btn-modern {
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
      border: none;
      border-radius: var(--radius-lg);
      padding: 1rem 2rem;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: inline-flex;
      align-items: center;
      gap: 0.75rem;
      text-decoration: none;
      min-height: 3rem;
      justify-content: center;
    }
    
    .btn-modern:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
      background: linear-gradient(135deg, var(--primary-green-light) 0%, var(--primary-green) 100%);
      color: white;
    }
    
    .btn-modern:active {
      transform: translateY(0);
    }
    
    .btn-modern:disabled {
      background: var(--gray-300);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    /* Table Styles */
    .table-container {
      background: var(--bg-secondary);
      border-radius: var(--radius-xl);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
      border: 1px solid var(--gray-200);
    }
    
    .table-wrapper {
      overflow: auto;
      max-height: 70vh;
      scrollbar-width: thin;
      scrollbar-color: var(--primary-green) var(--gray-200);
    }
    
    .table-wrapper::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    
    .table-wrapper::-webkit-scrollbar-track {
      background: var(--gray-100);
      border-radius: var(--radius-sm);
    }
    
    .table-wrapper::-webkit-scrollbar-thumb {
      background: var(--primary-green);
      border-radius: var(--radius-sm);
    }
    
    .table-wrapper::-webkit-scrollbar-thumb:hover {
      background: var(--primary-green-dark);
    }
    
    .table-modern {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      font-size: 0.875rem;
      margin: 0;
    }
    
    .table-modern th {
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
      color: white;
      font-weight: 600;
      padding: 1rem 1.25rem;
      text-align: left;
      position: sticky;
      top: 0;
      z-index: 10;
      border-bottom: 1px solid var(--primary-green-dark);
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      white-space: nowrap;
    }
    
    .table-modern td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--gray-200);
      background: var(--bg-secondary);
      transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover td {
      background: var(--secondary-green);
      transform: scale(1.001);
    }
    
    .table-modern tr:last-child td {
      border-bottom: none;
    }
    
    /* Status Badges */
    .status-badge {
      padding: 0.5rem 1rem;
      border-radius: 9999px;
      font-weight: 600;
      font-size: 0.75rem;
      text-align: center;
      white-space: nowrap;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    .status-success {
      background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
      color: var(--success-color);
      border: 1px solid #86efac;
    }
    
    .status-danger {
      background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
      color: var(--danger-color);
      border: 1px solid #fca5a5;
    }
    
    .status-warning {
      background: linear-gradient(135deg, #fffbeb 0%, #fed7aa 100%);
      color: var(--warning-color);
      border: 1px solid #fdba74;
    }
    
    .status-info {
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      color: var(--info-color);
      border: 1px solid #93c5fd;
    }
    
    .status-neutral {
      background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
      color: var(--gray-600);
      border: 1px solid var(--gray-300);
    }
    
    /* Code and ID Styles */
    .gate-code, .uk-code {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 600;
      color: var(--primary-green);
      background: var(--secondary-green);
      padding: 0.25rem 0.5rem;
      border-radius: var(--radius-sm);
      font-size: 0.8rem;
      margin-right: 0.5rem;
      border: 1px solid var(--accent-green);
    }
    
    /* Activity Number */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 2rem;
      height: 2rem;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-green-dark) 100%);
      color: white;
      font-weight: 700;
      margin-right: 0.75rem;
      font-size: 0.75rem;
      box-shadow: var(--shadow-sm);
    }
    
    /* Date Styles */
    .date-column {
      text-align: center;
      font-family: 'JetBrains Mono', monospace;
      font-weight: 500;
    }
    
    .date-in-range {
      color: var(--success-color);
      font-weight: 700;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }
    
    /* Loading Spinner */
    .spinner-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(5px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    
    .spinner-container {
      background: var(--bg-secondary);
      border-radius: var(--radius-xl);
      padding: 2rem;
      box-shadow: var(--shadow-xl);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
      border: 1px solid var(--gray-200);
    }
    
    .spinner-modern {
      width: 3rem;
      height: 3rem;
      border: 3px solid var(--gray-200);
      border-top: 3px solid var(--primary-green);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .spinner-text {
      font-weight: 600;
      color: var(--gray-700);
      font-size: 1rem;
    }
    
    /* Region Header */
    .region-header {
      background: linear-gradient(135deg, var(--info-color) 0%, #1e40af 100%);
      color: white;
      font-weight: 600;
      text-align: center;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
      .main-container {
        max-width: 100%;
        padding: 1rem;
      }
      
      .header-section {
        padding: 2rem;
      }
      
      .card-body-modern {
        padding: 1.5rem;
      }
    }
    
    @media (max-width: 768px) {
      .main-container {
        padding: 0.75rem;
      }
      
      .header-section {
        padding: 1.5rem;
        margin-bottom: 1rem;
      }
      
      .header-title {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
      }
      
      .card-header-modern {
        padding: 1rem 1.5rem;
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
      }
      
      .card-body-modern {
        padding: 1rem;
      }
      
      .table-modern th,
      .table-modern td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
      }
      
      .btn-modern {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
      }
    }
    
    @media (max-width: 576px) {
      .header-title {
        font-size: 1.5rem;
      }
      
      .header-subtitle {
        font-size: 1rem;
      }
      
      .form-control-modern,
      .form-select-modern {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
      }
      
      .table-wrapper {
        max-height: 60vh;
      }
      
      .table-modern th,
      .table-modern td {
        padding: 0.5rem;
        font-size: 0.75rem;
      }
      
      .status-badge {
        padding: 0.375rem 0.75rem;
        font-size: 0.7rem;
      }
    }
    
    /* Column widths for better layout */
    .table-modern th:nth-child(1), .table-modern td:nth-child(1) { min-width: 200px; }
    .table-modern th:nth-child(2), .table-modern td:nth-child(2) { min-width: 220px; }
    .table-modern th:nth-child(3), .table-modern td:nth-child(3) { min-width: 100px; text-align: center; }
    .table-modern th:nth-child(4), .table-modern td:nth-child(4) { min-width: 280px; }
    .table-modern th:nth-child(5), .table-modern td:nth-child(5) { min-width: 140px; }
    .table-modern th:nth-child(6), .table-modern td:nth-child(6) { min-width: 140px; }
    .table-modern th:nth-child(7), .table-modern td:nth-child(7) { min-width: 120px; text-align: center; }
    
    /* UK Group alternating colors */
    .uk-group-even { background: var(--bg-secondary); }
    .uk-group-odd { background: var(--gray-50); }
    
    /* Smooth animations */
    * {
      transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1),
                  box-shadow 0.2s cubic-bezier(0.4, 0, 0.2, 1),
                  background-color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    /* SweetAlert2 Custom Styling */
    .modern-alert {
      border-radius: var(--radius-xl) !important;
      box-shadow: var(--shadow-xl) !important;
      border: 1px solid var(--gray-200) !important;
    }
    
    .modern-alert-title {
      font-family: 'Plus Jakarta Sans', sans-serif !important;
      font-weight: 700 !important;
      color: var(--gray-800) !important;
    }
    
    .modern-alert-content {
      font-family: 'Plus Jakarta Sans', sans-serif !important;
      color: var(--gray-600) !important;
    }
    
    .swal2-icon.swal2-error {
      border-color: var(--danger-color) !important;
      color: var(--danger-color) !important;
    }
    
    .swal2-icon.swal2-success {
      border-color: var(--success-color) !important;
      color: var(--success-color) !important;
    }
    
    .swal2-icon.swal2-warning {
      border-color: var(--warning-color) !important;
      color: var(--warning-color) !important;
    }
    
    .swal2-icon.swal2-info {
      border-color: var(--info-color) !important;
      color: var(--info-color) !important;
    }
    
    /* Animation classes */
    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
      }
      to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
      }
    }
    
    @keyframes fadeOutUp {
      from {
        opacity: 1;
      }
      to {
        opacity: 0;
        transform: translate3d(0, -100%, 0);
      }
    }
    
    .animate__animated {
      animation-duration: 0.3s;
      animation-fill-mode: both;
    }
    
    .animate__fadeInDown {
      animation-name: fadeInDown;
    }
    
    .animate__fadeOutUp {
      animation-name: fadeOutUp;
    }
  </style>
</head>
<body>
  <div class="main-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <h1 class="header-title">
          <div class="header-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div>
            <div>Quality Gates Monitor</div>
            <p class="header-subtitle">Dashboard Monitoring & Evaluasi Sistem Kualitas</p>
          </div>
        </h1>
      </div>
    </div>
    
    <!-- Filter Section -->
    <div class="modern-card">
      <div class="card-header-modern">
        <div class="card-header-icon">
          <i class="fas fa-filter"></i>
        </div>
        <h3 class="card-title">Filter & Pencarian Data</h3>
      </div>
      <div class="card-body-modern">
        <div class="row g-4">
          <div class="col-lg-3 col-md-6">
            <div class="form-group-modern">
              <label for="yearSelect" class="form-label-modern">
                <i class="fas fa-calendar-alt"></i>
                Tahun Monitoring
              </label>
              <select id="yearSelect" class="form-select-modern">
                <option value="">Pilih Tahun</option>
                <option value="2023">2023</option>
                <option value="2024">2024</option>
                <option value="2025">2025</option>
              </select>
            </div>
          </div>
          
          <div class="col-lg-4 col-md-6">
            <div class="form-group-modern">
              <label for="projectSelect" class="form-label-modern">
                <i class="fas fa-project-diagram"></i>
                Kegiatan/Project
              </label>
              <select id="projectSelect" class="form-select-modern" disabled>
                <option value="">Pilih Kegiatan</option>
              </select>
            </div>
          </div>
          
          <div class="col-lg-3 col-md-6">
            <div class="form-group-modern">
              <label for="regionSelect" class="form-label-modern">
                <i class="fas fa-map-marker-alt"></i>
                Cakupan Wilayah
              </label>
              <select id="regionSelect" class="form-select-modern" disabled>
                <option value="">Pilih Cakupan Wilayah</option>
              </select>
            </div>
          </div>
          
          <div class="col-lg-2 col-md-6">
            <div class="form-group-modern">
              <label class="form-label-modern" style="opacity: 0;">Action</label>
              <button id="loadData" class="btn-modern w-100">
                <i class="fas fa-search"></i>
                Tampilkan Data
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Results Container -->
    <div id="resultsContainer"></div>
  </div>

  <!-- Loading Spinner -->
  <div id="spinner" class="spinner-overlay" style="display: none;">
    <div class="spinner-container">
      <div class="spinner-modern"></div>
      <div class="spinner-text">
        <i class="fas fa-sync-alt me-2"></i>
        Memuat data monitoring...
      </div>
    </div>
  </div>

  <script>
    // Initialize Feather Icons
    feather.replace();
    
    $(function(){
      const API_URL = "api.php";
      let selectedProject, year, selectedRegion = null;
      let coverageData = [];
      let activityData = {}; // Untuk menyimpan data status per aktivitas per wilayah

      // Cache selector DOM
      const $yearSelect    = $("#yearSelect");
      const $projectSelect = $("#projectSelect");
      const $regionSelect  = $("#regionSelect");
      const $resultsContainer = $("#resultsContainer");
      const $spinner       = $("#spinner");

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
          confirmButtonColor: '#059669', // Green theme color
          confirmButtonText: 'Mengerti',
          customClass: {
            popup: 'modern-alert',
            title: 'modern-alert-title',
            content: 'modern-alert-content',
            confirmButton: 'btn-modern'
          },
          showClass: {
            popup: 'animate__animated animate__fadeInDown'
          },
          hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
          }
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

      const formatDate = (dateString) => {
        if (!dateString || dateString === "0000-00-00" || dateString === "") {
          return '<span class="text-muted">Tidak ditentukan</span>';
        }
        
        try {
          const date = new Date(dateString);
          if (isNaN(date.getTime())) {
            return '<span class="text-muted">Format tidak valid</span>';
          }
          
          // Format: DD/MM/YYYY
          const day = String(date.getDate()).padStart(2, '0');
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const year = date.getFullYear();
          
          return `${day}/${month}/${year}`;
        } catch (error) {
          return '<span class="text-muted">Error format</span>';
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

      const getStatusBadge = (status) => {
        const statusMap = {
          "Sudah": {
            class: "status-success",
            icon: "fas fa-check-circle",
            text: "Selesai"
          },
          "Belum": {
            class: "status-danger", 
            icon: "fas fa-times-circle",
            text: "Belum"
          },
          "Sebagian": {
            class: "status-warning",
            icon: "fas fa-exclamation-triangle", 
            text: "Sebagian"
          },
          "Tidak perlu": {
            class: "status-neutral",
            icon: "fas fa-minus-circle",
            text: "Tidak Perlu"
          },
          "Tidak tersedia": {
            class: "status-info",
            icon: "fas fa-question-circle",
            text: "Tidak Tersedia"
          }
        };
        
        const statusInfo = statusMap[status] || {
          class: "status-neutral",
          icon: "fas fa-question",
          text: status
        };
        
        return `<span class="status-badge ${statusInfo.class}">
          <i class="${statusInfo.icon}"></i>
          ${statusInfo.text}
        </span>`;
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
          <div class="modern-card">
            <div class="card-header-modern">
              <div class="card-header-icon">
                <i class="fas fa-table"></i>
              </div>
              <h3 class="card-title">Hasil Monitoring Quality Gates</h3>
              <div class="ms-auto">
                <span class="status-badge status-info">
                  <i class="fas fa-list-ol"></i>
                  ${Object.keys(activityData).length} Aktivitas
                </span>
              </div>
            </div>
            <div class="table-container">
              <div class="table-wrapper">
                <table class="table-modern">
                  <thead>
                    <tr>
                      <th><i class="fas fa-gate-open me-2"></i>Quality Gate</th>
                      <th><i class="fas fa-ruler me-2"></i>Ukuran Kualitas</th>
                      <th><i class="fas fa-layer-group me-2"></i>Level</th>
                      <th><i class="fas fa-tasks me-2"></i>Aktivitas</th>
                      <th class="date-column"><i class="fas fa-calendar-plus me-2"></i>Mulai</th>
                      <th class="date-column"><i class="fas fa-calendar-check me-2"></i>Selesai</th>
        `;
        
        // Tambahkan kolom status untuk setiap wilayah
        regions.forEach(region => {
          const regionIcon = region.id === "pusat" ? "fas fa-building" : 
                           region.kab === "00" ? "fas fa-map" : "fas fa-map-marker-alt";
          tableHtml += `<th class="region-header">
            <i class="${regionIcon} me-2"></i>${region.name}
          </th>`;
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
                <td rowspan="${rowspanValue}">
                  <span class="gate-code">${data.gate}</span>
                </td>
                <td rowspan="${rowspanValue}">
                  <span class="uk-code">${data.uk}</span>
                </td>
                <td rowspan="${rowspanValue}" class="text-center">
                  <span class="status-badge status-info">
                    <i class="fas fa-layer-group"></i>
                    ${ukLevels[data.uk]}
                  </span>
                </td>
              `;
            }
            
            // Tentukan apakah tanggal dalam rentang aktif
            const startDate = data.start;
            const endDate = data.end;
            const isInDateRange = isDateInRange(startDate, endDate);
            
            // Tambahkan class untuk tanggal yang dalam rentang
            const startDateClass = isInDateRange ? 'date-in-range' : '';
            const endDateClass = isInDateRange ? 'date-in-range' : '';
            
            // Icon untuk berbagai jenis aktivitas
            const activityIcons = {
              1: "fas fa-user-edit",      // Pengisian nama pelaksana aksi preventif
              2: "fas fa-cloud-upload-alt", // Upload bukti pelaksanaan aksi preventif
              3: "fas fa-chart-bar",       // Penilaian ukuran kualitas
              4: "fas fa-check-double",    // Approval Gate oleh Sign-off
              5: "fas fa-user-cog",        // Pengisian pelaksana aksi korektif
              6: "fas fa-upload"           // Upload bukti pelaksanaan aksi korektif
            };
            
            const activityIcon = activityIcons[activityNumber] || "fas fa-tasks";
            
            tableHtml += `
              <td>
                <div class="d-flex align-items-center">
                  <div class="activity-number">${activityNumber}</div>
                  <div>
                    <i class="${activityIcon} me-2 text-muted"></i>
                    ${data.activity}
                  </div>
                </div>
              </td>
              <td class="date-column ${startDateClass}">
                <i class="fas fa-calendar-alt me-2 text-muted"></i>
                ${formatDate(startDate)}
              </td>
              <td class="date-column ${endDateClass}">
                <i class="fas fa-calendar-check me-2 text-muted"></i>
                ${formatDate(endDate)}
              </td>
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
        
        // Initialize feather icons for any new content
        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      };

      // --- Fungsi untuk Load Data (Projects & Regions) ---

      const loadProjects = async () => {
        $projectSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          const response = await makeAjaxRequest(API_URL, { action: "fetchProjects", year });
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          if (response.status && response.data.length) {
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
        $regionSelect.prop('disabled', !selectedProject).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        selectedRegion  = null;
        if (selectedProject) await loadRegions();
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
          const regionData = coverageData.find(r => r.id === selectedRegion);
          if (!regionData)
            throw new Error("Data wilayah tidak ditemukan");
          
          // Tentukan daftar wilayah yang akan diproses
          let regionsToProcess = [];
          
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
      year = $yearSelect.val();
      loadProjects();
      $spinner.hide();
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
