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
      --primary-color: #4f46e5;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --neutral-color: #6b7280;
      --light-color: #f9fafb;
      --dark-color: #1f2937;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: #334155;
    }
    
    .container-fluid {
      max-width: 100%;
      padding: 1.5rem;
    }
    
    h1 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: var(--dark-color);
    }
    
    .card {
      border-radius: 0.5rem;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
      border: none;
      margin-bottom: 1.5rem;
    }
    
    .card-header {
      background-color: #fff;
      border-bottom: 1px solid #e5e7eb;
      padding: 1rem 1.5rem;
      font-weight: 600;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
    }
    
    .form-control, .form-select {
      border-radius: 0.375rem;
      border: 1px solid #d1d5db;
      padding: 0.625rem 0.75rem;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(79,70,229,0.25);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
      border-radius: 0.375rem;
      padding: 0.625rem 1.25rem;
      font-weight: 500;
    }
    
    .btn-primary:hover {
      background-color: #4338ca;
      border-color: #4338ca;
    }
    
    .table {
      border-radius: 0.5rem;
      overflow: hidden;
      border-spacing: 0;
      margin-bottom: 0;
    }
    
    .table th {
      background-color: #f8fafc;
      font-weight: 600;
      padding: 1rem;
      font-size: 0.875rem;
      color: var(--dark-color);
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 1;
    }
    
    .table td {
      padding: 1rem;
      vertical-align: middle;
      font-size: 0.875rem;
    }
    
    tbody tr:hover {
      background-color: rgba(243,244,246,0.5);
    }
    
    /* Status badges */
    .status-badge {
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-weight: 500;
      font-size: 0.75rem;
      text-align: center;
      white-space: nowrap;
      display: inline-block;
    }
    
    .status-success {
      background-color: rgba(16,185,129,0.1);
      color: var(--success-color);
    }
    
    .status-danger {
      background-color: rgba(239,68,68,0.1);
      color: var(--danger-color);
    }
    
    .status-warning {
      background-color: rgba(245,158,11,0.1);
      color: var(--warning-color);
    }
    
    .status-neutral {
      background-color: rgba(107,114,128,0.1);
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
      background: rgba(255,255,255,0.8);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    
    .spinner-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
    }
    
    .spinner-text {
      font-weight: 500;
      color: var(--primary-color);
    }
    
    /* Region header styles */
    .region-header {
      font-size: 0.8rem;
      font-weight: 600;
      text-align: center;
      background-color: rgba(79,70,229,0.05);
      border-bottom: 2px solid #e5e7eb;
    }
    
    /* Perbaikan tampilan tabel */
    .table-wrapper {
      overflow-x: auto;
      position: relative;
      width: 100%;
      margin-bottom: 1rem;
      border-radius: 0.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .table-monitoring {
      min-width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
      width: 100%;
    }
    
    .table-monitoring th,
    .table-monitoring td {
      padding: 0.75rem;
      vertical-align: middle;
    }
    
    /* Menetapkan lebar kolom */
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
      min-width: 100px;
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
      min-width: 150px;
      text-align: center;
    }
    
    /* Header tetap saat scroll */
    .table-monitoring thead th {
      position: sticky;
      top: 0;
      background-color: #f8fafc;
      z-index: 10;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
      font-weight: 600;
      text-align: left;
      white-space: nowrap;
    }
    
    /* Untuk kompatibilitas */
    .date-column {
      text-align: center;
    }
    
    /* Row colors alternating by UK group */
    .table-monitoring .uk-group-even {
      background-color: rgba(243, 244, 246, 0.5);
    }
    
    .table-monitoring .uk-group-odd {
      background-color: #ffffff;
    }
    
    /* Styling for rows */
    .table-monitoring tr {
      border-bottom: 1px solid #e5e7eb;
    }
    
    .table-monitoring td {
      border-right: 1px solid #f0f0f0;
    }
    
    /* Activity number */
    .activity-number {
      display: inline-block;
      font-weight: 600;
      margin-right: 8px;
      color: var(--primary-color);
    }
    
    /* Status column */
    .status-column {
      min-width: 120px;
      text-align: center;
      white-space: nowrap;
    }
    
    /* Responsif */
    @media (max-width: 768px) {
      .container-fluid {
        padding: 1rem;
      }
      
      h1 {
        font-size: 1.5rem;
      }
      
      .card-header {
        padding: 0.75rem 1rem;
      }
      
      .card-body {
        padding: 1rem;
      }
      
      .row.g-3 {
        margin-bottom: 0;
      }
      
      .table-monitoring th,
      .table-monitoring td {
        padding: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <h1><i class="fas fa-tasks me-2"></i>Monitoring Quality Gates</h1>
    
    <!-- Input Filters -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Data</span>
      </div>
      <div class="card-body">
        <div class="row g-3">
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
            <button id="loadData" class="btn btn-primary w-100">
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
          confirmButtonColor: '#4f46e5'
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
            
            tableHtml += `
              <td><span class="activity-number">${activityNumber}.</span>${data.activity}</td>
              <td class="date-column">${formatDate(data.start)}</td>
              <td class="date-column">${formatDate(data.end)}</td>
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
            
            // Pre-load data hanya untuk region yang sesuai dengan assessment_level
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
