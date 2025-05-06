<?php
// index.php - Landing page with links to monitoring and download
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quality Gates Dashboard</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #0071e3; /* Apple blue */
      --success-color: #34c759; /* Apple green */
      --light-color: #f5f5f7;   /* Apple light gray */
      --dark-color: #1d1d1f;    /* Apple dark */
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background-color: #f5f5f7;
      color: #1d1d1f;
      line-height: 1.5;
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .container {
      max-width: 1000px;
      padding: 2rem;
    }
    
    h1 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      color: var(--dark-color);
      font-size: 2.5rem;
      text-align: center;
    }
    
    .subtitle {
      text-align: center;
      margin-bottom: 3rem;
      font-size: 1.1rem;
      color: #6c757d;
    }
    
    .card {
      border-radius: 16px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.05);
      border: none;
      margin-bottom: 2rem;
      background-color: #ffffff;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .card-body {
      padding: 2.5rem;
      text-align: center;
    }
    
    .icon-wrapper {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: var(--light-color);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }
    
    .icon-wrapper i {
      font-size: 2.2rem;
      color: var(--primary-color);
    }
    
    .card-title {
      font-weight: 600;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    
    .card-text {
      color: #6c757d;
      margin-bottom: 1.5rem;
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
      transform: translateY(-2px);
    }
    
    .btn-primary:active {
      transform: translateY(0);
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      h1 {
        font-size: 2rem;
      }
      
      .card-body {
        padding: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Quality Gates Dashboard</h1>
    <p class="subtitle">Pilih salah satu modul di bawah ini untuk memulai</p>
    
    <div class="row">
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="icon-wrapper">
              <i class="fas fa-download"></i>
            </div>
            <h5 class="card-title">Download Data</h5>
            <p class="card-text">Download data dari server BPS dan simpan ke database lokal untuk digunakan pada modul monitoring.</p>
            <a href="download.php" class="btn btn-primary">
              <i class="fas fa-download me-2"></i>Download Data
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <div class="icon-wrapper">
              <i class="fas fa-tasks"></i>
            </div>
            <h5 class="card-title">Monitoring</h5>
            <p class="card-text">Lihat dan pantau status kegiatan quality gates dari data yang sudah didownload sebelumnya.</p>
            <a href="monitoring.php" class="btn btn-primary">
              <i class="fas fa-search me-2"></i>Lihat Monitoring
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html> 