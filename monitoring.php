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
    
    .table-wrapper {
      overflow: auto;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin: 0;
      max-height: 600px; /* Fixed height untuk tabel */
    }
    
    .table-monitoring {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: #fff;
    }
    
    .table-monitoring th {
      background-color: #f5f5f7;
      font-weight: 500;
      padding: 1rem 1.25rem;
      font-size: 0.9rem;
      color: var(--dark-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10; /* Lebih tinggi untuk memastikan tetap di atas */
      text-align: left;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-monitoring td {
      padding: 1rem 1.25rem;
      vertical-align: middle;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-color);
    }
    
    .table-monitoring tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:hover {
      background-color: rgba(0,113,227,0.03);
    }
    
    /* Status badges */
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
    
    /* Region header styles */
    .region-header {
      font-size: 0.85rem;
      font-weight: 500;
      text-align: center;
      background-color: rgba(0,113,227,0.04);
    }
    
    /* Perbaikan tampilan tabel */
    .table-monitoring th:nth-child(1), /* Gate */
    .table-monitoring td:nth-child(1) {
      min-width: 200px;
    }
    
    .table-monitoring th:nth-child(2), /* UK */
    .table-monitoring td:nth-child(2) {
      min-width: 220px;
    }
    
    .table-monitoring th:nth-child(3), /* Level */
    .table-monitoring td:nth-child(3) {
      min-width: 100px;
      text-align: center;
    }
    
    .table-monitoring th:nth-child(4), /* Aktivitas */
    .table-monitoring td:nth-child(4) {
      min-width: 280px;
    }
    
    /* Kolom tanggal */
    .table-monitoring th:nth-child(5), /* Tanggal Mulai */
    .table-monitoring td:nth-child(5),
    .table-monitoring th:nth-child(6), /* Tanggal Selesai */
    .table-monitoring td:nth-child(6) {
      min-width: 160px;
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
      background-color: #ffffff;
    }
    
    .table-monitoring .uk-group-odd {
      background-color: rgba(245,245,247,0.4);
    }
    
    /* Activity number */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 24px;
      height: 24px;
      border-radius: 12px;
      background-color: rgba(0,113,227,0.1);
      color: var(--primary-color);
      font-weight: 600;
      margin-right: 12px;
      font-size: 0.75rem;
    }
    
    /* Status column */
    .status-column {
      min-width: 120px;
      text-align: center;
      white-space: nowrap;
    }
    
    /* Row hover and focus */
    .table-monitoring tr:hover td {
      background-color: rgba(0,113,227,0.04);
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
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg" style="background: #ffffff; box-shadow: 0 2px 20px rgba(0,0,0,0.04); border-bottom: 1px solid var(--border-color); padding: 1rem 0; margin-bottom: 2rem;">
    <div class="container-fluid">
      <a class="navbar-brand" href="#" style="font-weight: 600; font-size: 1.25rem; color: var(--primary-color) !important;">
        <i class="fas fa-chart-line me-2"></i>Quality Gates Monitoring
      </a>
      
      <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="dashboard.php" class="btn btn-outline-secondary me-3">
          <i class="fas fa-tachometer-alt me-1"></i>Dashboard
        </a>
        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary-color), #005bbc); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;" id="userAvatar">
          <i class="fas fa-user"></i>
        </div>
        <div style="display: flex; flex-direction: column;">
          <div style="font-weight: 500; color: var(--dark-color); font-size: 0.9rem;" id="userName">Loading...</div>
          <div style="font-size: 0.8rem; color: var(--neutral-color);" id="userRole">Loading...</div>
        </div>
        <button class="btn" style="background: none; border: 1px solid var(--border-color); color: var(--neutral-color); border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.85rem; transition: all 0.2s ease;" id="logoutBtn">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </button>
      </div>
    </div>
  </nav>

  <div class="container-fluid">

    
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
            <label for="projectSelect" class="form-label">Pilih Kegiatan</label>
            <select id="projectSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-3" id="regionSelectContainer">
            <label for="regionSelect" class="form-label">Pilih Cakupan Wilayah</label>
            <select id="regionSelect" class="form-select" disabled></select>
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

  <!-- Spinner Loading -->
  <div id="spinner">
    <div class="spinner-container">
      <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
      <div class="spinner-text">Memuat data...</div>
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
      const $resultsContainer = $("#resultsContainer");
      const $spinner       = $("#spinner");

      // --- Helper Functions ---

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

      // --- Fungsi untuk Load Data (Projects & Regions) ---

      const loadProjects = async () => {
        if (!currentUser || !year) return;
        
        $projectSelect.empty().append('<option value="">Memuat data...</option>');
        try {
          // Gunakan tahun yang dipilih, bukan hardcode 2025
          const response = await makeAjaxRequest(API_URL, { 
            action: "fetchAvailableProjects", 
            user_prov: currentUser.prov,
            user_kab: currentUser.kab,
            year: year // Gunakan tahun yang dipilih
          });
          
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          if (response.status && response.data.length) {
            response.data.forEach(project => {
              $projectSelect.append(`<option value="${project.id}">${project.name}</option>`);
            });
            // Enable dropdown project setelah data berhasil dimuat
            $projectSelect.prop('disabled', false);
          } else {
            $projectSelect.append('<option value="" disabled>Tidak ada kegiatan ditemukan</option>');
            $projectSelect.prop('disabled', false);
          }
        } catch (error) {
          showError("Gagal memuat daftar kegiatan");
          $projectSelect.empty().append('<option value="">Pilih Kegiatan</option>');
          $projectSelect.prop('disabled', false);
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
            $regionSelect.empty().append('<option value="">Memuat data...</option>');
            filteredData = response.data || [];
            
            // Tambahkan opsi Pusat untuk user pusat
            $regionSelect.empty();
            $regionSelect.append(`<option value="pusat">Pusat - Nasional</option>`);
            coverageData.push({
              id: "pusat",
              prov: "00", 
              kab: "00",
              name: "Pusat - Nasional"
            });
            
            if (filteredData.length > 0) {
              const apiData = filteredData.map(cov => ({
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
            }
            
            // Set default ke pusat
            selectedRegion = "pusat";
            $regionSelect.val(selectedRegion);
            $regionSelect.prop('disabled', false);
            
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
              
              // Auto-select provinsi user (tidak perlu dropdown)
              const userProvince = coverageData.find(cov => 
                cov.prov === currentUser.prov && cov.kab === "00"
              );
              
              if (userProvince) {
                selectedRegion = userProvince.id;
              } else {
                throw new Error("Data provinsi Anda tidak ditemukan");
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
        $projectSelect.prop('disabled', true).empty().append('<option value="">Pilih Kegiatan</option>');
        
        // Reset dropdown region hanya untuk user pusat
        if (currentUser.prov === "00" && currentUser.kab === "00") {
          $regionSelect.prop('disabled', true).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        }
        
        selectedProject = null;
        selectedRegion  = null;
        
        // Load projects jika tahun dipilih
        if (year && currentUser) {
          await loadProjects();
        }
      });

      $projectSelect.on('change', async function(){
        selectedProject = $(this).val();
        
        // Reset dropdown dan selected region
        selectedRegion = null;
        
        if (currentUser.prov === "00" && currentUser.kab === "00") {
          // User pusat - reset dropdown wilayah
          $regionSelect.prop('disabled', !selectedProject).empty().append('<option value="">Pilih Cakupan Wilayah</option>');
        }
        
        if (selectedProject) await loadRegions();
      });

      $regionSelect.on('change', function(){
        selectedRegion = $(this).val();
      });

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
          const regionData = coverageData.find(r => r.id === selectedRegion);
          if (!regionData)
            throw new Error("Data wilayah tidak ditemukan");
          
          // Tentukan daftar wilayah yang akan diproses
          let regionsToProcess = [];
          
          if (selectedRegion === "pusat") {
            // Level Pusat - Hanya kolom pusat
            regionsToProcess = [{ id: "pusat", prov: "00", kab: "00", name: "Pusat" }];
          } else if (regionData.kab === "00") {
            // Level Provinsi - Kolom provinsi + semua kabupaten di provinsi itu
            const prov = regionData.prov;
            regionsToProcess = [
              { id: `${prov}00`, prov: prov, kab: "00", name: regionData.name }
            ];
            
            // Tambahkan kabupaten yang ada di provinsi ini
            const kabupatenList = coverageData.filter(r => r.prov === prov && r.kab !== "00");
            regionsToProcess = [...regionsToProcess, ...kabupatenList];
          } else {
            // Level Kabupaten - Hanya kolom kabupaten yang dipilih
            regionsToProcess = [regionData];
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
