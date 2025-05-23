<?php
// dashboard.php - Dashboard per wilayah untuk Quality Gates
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
    
    .navbar {
      background: #ffffff;
      box-shadow: 0 2px 20px rgba(0,0,0,0.04);
      border-bottom: 1px solid var(--border-color);
      padding: 1rem 0;
    }
    
    .navbar-brand {
      font-weight: 600;
      font-size: 1.25rem;
      color: var(--primary-color) !important;
    }
    
    .user-info {
      display: flex;
      align-items: center;
      gap: 1rem;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, var(--primary-color), #005bbc);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 600;
    }
    
    .user-details {
      display: flex;
      flex-direction: column;
    }
    
    .user-name {
      font-weight: 500;
      color: var(--dark-color);
      font-size: 0.9rem;
    }
    
    .user-role {
      font-size: 0.8rem;
      color: var(--neutral-color);
    }
    
    .btn-logout {
      background: none;
      border: 1px solid var(--border-color);
      color: var(--neutral-color);
      border-radius: 8px;
      padding: 0.5rem 1rem;
      font-size: 0.85rem;
      transition: all 0.2s ease;
    }
    
    .btn-logout:hover {
      background: var(--danger-color);
      border-color: var(--danger-color);
      color: white;
    }
    
    .container-fluid {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
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
    
    .table-wrapper {
      overflow: auto;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin: 0;
      max-height: 70vh;
    }
    
    .table-dashboard {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #fff;
      margin: 0;
    }
    
    .table-dashboard th {
      background-color: #f5f5f7;
      font-weight: 500;
      padding: 1rem 1.25rem;
      font-size: 0.9rem;
      color: var(--dark-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: left;
      border-bottom: 1px solid var(--border-color);
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
      background-color: rgba(0,113,227,0.03);
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
    
    .spinner {
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
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">
        <i class="fas fa-tasks me-2"></i>Quality Gates Dashboard
      </a>
      
      <div class="user-info">
        <a href="monitoring.php" class="btn btn-outline-secondary me-3">
          <i class="fas fa-chart-line me-1"></i>Monitoring
        </a>
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
    <!-- Header -->
    <div class="row mb-4">
      <div class="col-12">
        <h1 id="dashboardTitle">Dashboard Quality Gates</h1>
        <p class="dashboard-subtitle" id="dashboardSubtitle">Pantau progress kegiatan quality gates <span id="selectedRegionName"></span></p>
      </div>
    </div>

    <!-- Filters -->
    <div class="card" id="filtersCard">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Data</span>
        <div>
          <span id="filterRequiredNote" class="text-danger me-3" style="display: none;">
            <i class="fas fa-exclamation-triangle me-1"></i>Mohon isi filter untuk memuat data
          </span>
          <button class="btn btn-sm btn-outline-secondary" id="clearFilters">
            <i class="fas fa-times me-1"></i>Reset Filter
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-2">
            <label for="filterYear" class="form-label">Tahun</label>
            <select id="filterYear" class="form-select">
              <option value="">Pilih Tahun</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="filterRegion" class="form-label">Wilayah</label>
            <select id="filterRegion" class="form-select">
              <option value="">Pilih Wilayah</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="filterProject" class="form-label">Kegiatan</label>
            <select id="filterProject" class="form-select">
              <option value="">Pilih Kegiatan</option>
            </select>
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
                <th>Kegiatan</th>
                <th>Tahun</th>
                <th>Gate</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Status</th>
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

  <!-- Loading Spinner -->
  <div id="spinner" class="spinner">
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
      <div class="spinner-text">Memuat data...</div>
    </div>
  </div>

  <script>
    $(function(){
      const API_URL = "api.php";
      let currentUser = null;
      let dashboardData = [];
      let filteredData = [];
      
      // Cache selector DOM
      const $spinner = $("#spinner");
      const $dashboardTableBody = $("#dashboardTableBody");
      const $emptyState = $("#emptyState");
      const $resultCount = $("#resultCount");
      const $filtersCard = $("#filtersCard");
      
      // Helper Functions
      const extractJson = response => {
        const start = response.indexOf('{');
        const end = response.lastIndexOf('}');
        return (start !== -1 && end !== -1 && end > start)
          ? response.substring(start, end + 1)
          : response;
      };
      
      const showError = message => {
        console.error(message);
        alert(message); // Ganti dengan notifikasi yang lebih baik jika perlu
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
        const date = new Date(dateStr);
        const options = { 
          year: 'numeric', 
          month: 'short', 
          day: 'numeric',
          timeZone: 'Asia/Jakarta'
        };
        return date.toLocaleDateString('id-ID', options);
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
          
          // Tampilkan filter untuk semua level sekarang
          loadFilterOptions();
          
          // Untuk level pusat, tampilkan peringatan wajib filter
          if (currentUser.role === 'pusat') {
            $("#filterRequiredNote").show();
            $("#initialState").show();
            $("#emptyState").hide();
          } else {
            // Untuk provinsi dan kabupaten, langsung load data tanpa filter
            loadDashboardData();
            
            // Khusus untuk kabupaten, hidden region filter karena sudah fix
            if (currentUser.role === 'kabupaten') {
              $("#filterRegion").closest('.col-md-4').hide();
              // Perlebar kolom kegiatan
              $("#filterProject").closest('.col-md-4').removeClass('col-md-4').addClass('col-md-6');
              
              // Load nama wilayah untuk header
              loadRegionNameForKabupaten();
            }
          }
          
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
        
        // Untuk level pusat, wajibkan filter tahun dan wilayah
        if (currentUser.role === 'pusat') {
          if (!filters.filter_year || !filters.filter_region) {
            $("#initialState").show();
            $("#emptyState").hide();
            $dashboardTableBody.empty();
            $resultCount.text("0 data");
            return;
          }
        }
        
        $spinner.show();
        $("#initialState").hide();
        
        try {
          const requestData = {
            action: "fetchDashboardData",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            ...filters
          };
          
          const response = await makeAjaxRequest(API_URL, requestData);
          
          if (response.status && response.data) {
            dashboardData = response.data;
            filteredData = [...dashboardData];
            displayDashboardData();
            
            // Update nama wilayah di header jika ada filter wilayah
            updateSelectedRegionName(filters.filter_region);
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
      
      // Update nama wilayah yang dipilih di header
      const updateSelectedRegionName = (regionId) => {
        const $selectedRegionName = $("#selectedRegionName");
        
        if (regionId && regionId.length >= 4) {
          // Cari nama wilayah dari data yang tersedia
          const $filterRegion = $("#filterRegion");
          const selectedText = $filterRegion.find(`option[value="${regionId}"]`).text();
          
          if (selectedText && selectedText !== "Pilih Wilayah") {
            $selectedRegionName.html(`- <strong>${selectedText}</strong>`);
          } else {
            $selectedRegionName.text("");
          }
        } else if (currentUser.role === 'kabupaten') {
          // Untuk level kabupaten, tampilkan nama wilayahnya
          loadRegionNameForKabupaten();
        } else {
          $selectedRegionName.text("");
        }
      };
      
      // Load nama wilayah untuk level kabupaten
      const loadRegionNameForKabupaten = async () => {
        try {
          const response = await makeAjaxRequest(API_URL, {
            action: "fetchAvailableRegions",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab
          });
          
          if (response.status && response.data && response.data.length > 0) {
            const regionName = response.data[0].name;
            $("#selectedRegionName").html(`- <strong>${regionName}</strong>`);
          }
        } catch(error) {
          console.error("Error loading region name:", error);
        }
      };
      
      // Tampilkan data dashboard dengan merge kegiatan yang sama
      const displayDashboardData = () => {
        if (filteredData.length === 0) {
          $dashboardTableBody.empty();
          $emptyState.show();
          $("#initialState").hide();
          $resultCount.text("0 data");
          return;
        }
        
        $emptyState.hide();
        $("#initialState").hide();
        
        // Group data berdasarkan project_name dan year
        const groupedData = {};
        filteredData.forEach(item => {
          const key = `${item.project_name}_${item.year}`;
          if (!groupedData[key]) {
            groupedData[key] = {
              project_name: item.project_name,
              year: item.year,
              gates: []
            };
          }
          groupedData[key].gates.push(item);
        });
        
        let tableHtml = '';
        let totalGates = 0;
        
        Object.values(groupedData).forEach(project => {
          // Urutkan gates berdasarkan gate_number
          project.gates.sort((a, b) => a.gate_number - b.gate_number);
          
          project.gates.forEach((gate, index) => {
            const dateStatus = getDateStatus(gate.prev_insert_start, gate.cor_upload_end);
            const isFirstGate = index === 0;
            const rowSpan = project.gates.length;
            
            tableHtml += `<tr>`;
            
            // Project name dan year hanya ditampilkan pada baris pertama dengan rowspan
            if (isFirstGate) {
              tableHtml += `
                <td rowspan="${rowSpan}">
                  <div class="project-name">${project.project_name}</div>
                </td>
                <td rowspan="${rowSpan}">${project.year}</td>
              `;
            }
            
            tableHtml += `
              <td>
                <div class="gate-name">GATE${gate.gate_number}: ${gate.gate_name}</div>
              </td>
              <td>${formatDate(gate.prev_insert_start)}</td>
              <td>${formatDate(gate.cor_upload_end)}</td>
              <td>
                <span class="${dateStatus.class}">${dateStatus.text}</span>
              </td>
            </tr>`;
            
            totalGates++;
          });
        });
        
        $dashboardTableBody.html(tableHtml);
        $resultCount.text(`${totalGates} gate`);
      };
      
      // Load filter options
      const loadFilterOptions = async () => {
        if (!currentUser) return;
        
        try {
          // Load years (semua level bisa akses)
          const yearsResponse = await makeAjaxRequest(API_URL, {
            action: "fetchAvailableYears",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab
          });
          
          if (yearsResponse.status && yearsResponse.data) {
            const $filterYear = $("#filterYear");
            $filterYear.empty().append('<option value="">Pilih Tahun</option>');
            yearsResponse.data.forEach(item => {
              $filterYear.append(`<option value="${item.year}">${item.year}</option>`);
            });
          }
          
          // Load regions (untuk pusat dan provinsi)
          if (currentUser.role === 'pusat' || currentUser.role === 'provinsi') {
            const regionsResponse = await makeAjaxRequest(API_URL, {
              action: "fetchAvailableRegions",
              user_prov: currentUser.prov,
              user_kab: currentUser.kab
            });
            
            if (regionsResponse.status && regionsResponse.data) {
              const $filterRegion = $("#filterRegion");
              $filterRegion.empty().append('<option value="">Pilih Wilayah</option>');
              regionsResponse.data.forEach(item => {
                const regionId = `${item.prov}${item.kab}`;
                $filterRegion.append(`<option value="${regionId}">${item.name}</option>`);
              });
            }
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
        
        const year = $("#filterYear").val();
        if (year) filters.filter_year = year;
        
        const region = $("#filterRegion").val();
        if (region) filters.filter_region = region;
        
        const project = $("#filterProject").val();
        if (project) filters.filter_project = project;
        
        loadDashboardData(filters);
      });
      
      $("#clearFilters").on('click', function(){
        $("#filterYear, #filterProject, #filterRegion").val('');
        
        // Reset nama wilayah di header
        if (currentUser.role === 'kabupaten') {
          updateSelectedRegionName();
        } else {
          $("#selectedRegionName").text("");
        }
        
        // Untuk non-pusat, load data kosong setelah clear
        if (currentUser.role !== 'pusat') {
          loadDashboardData();
        } else {
          // Untuk pusat, tampilkan initial state
          $("#initialState").show();
          $("#emptyState").hide();
          $dashboardTableBody.empty();
          $resultCount.text("0 data");
        }
      });
      
      // Filter tahun mengupdate wilayah dan kegiatan
      $("#filterYear").on('change', async function(){
        const selectedYear = $(this).val();
        
        // Clear dependent filters
        $("#filterProject").empty().append('<option value="">Pilih Kegiatan</option>');
        
        if (currentUser.role === 'pusat' || currentUser.role === 'provinsi') {
          // Update regions berdasarkan tahun (jika diperlukan)
          try {
            const regionsResponse = await makeAjaxRequest(API_URL, {
              action: "fetchAvailableRegions",
              user_prov: currentUser.prov,
              user_kab: currentUser.kab,
              year: selectedYear
            });
            
            if (regionsResponse.status && regionsResponse.data) {
              const $filterRegion = $("#filterRegion");
              $filterRegion.empty().append('<option value="">Pilih Wilayah</option>');
              regionsResponse.data.forEach(item => {
                const regionId = `${item.prov}${item.kab}`;
                $filterRegion.append(`<option value="${regionId}">${item.name}</option>`);
              });
            }
          } catch(error) {
            console.error("Error loading regions:", error);
          }
        } else if (currentUser.role === 'kabupaten') {
          // Untuk kabupaten, langsung load kegiatan berdasarkan tahun
          try {
            const projectsResponse = await makeAjaxRequest(API_URL, {
              action: "fetchAvailableProjects",
              user_prov: currentUser.prov,
              user_kab: currentUser.kab,
              year: selectedYear
            });
            
            if (projectsResponse.status && projectsResponse.data) {
              const $filterProject = $("#filterProject");
              $filterProject.empty().append('<option value="">Pilih Kegiatan</option>');
              projectsResponse.data.forEach(item => {
                $filterProject.append(`<option value="${item.id}">${item.name}</option>`);
              });
            }
          } catch(error) {
            console.error("Error loading projects:", error);
          }
        }
      });
      
      // Filter wilayah mengupdate kegiatan
      $("#filterRegion").on('change', async function(){
        const selectedRegion = $(this).val();
        const selectedYear = $("#filterYear").val();
        
        try {
          const projectsResponse = await makeAjaxRequest(API_URL, {
            action: "fetchAvailableProjects",
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            year: selectedYear,
            region: selectedRegion
          });
          
          if (projectsResponse.status && projectsResponse.data) {
            const $filterProject = $("#filterProject");
            $filterProject.empty().append('<option value="">Pilih Kegiatan</option>');
            projectsResponse.data.forEach(item => {
              $filterProject.append(`<option value="${item.id}">${item.name}</option>`);
            });
          }
        } catch(error) {
          console.error("Error loading projects:", error);
        }
      });
      
      // Inisialisasi
      if (initUser()) {
        loadDashboardData();
      }
    });
  </script>
  
  <!-- SweetAlert2 untuk notifikasi -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 