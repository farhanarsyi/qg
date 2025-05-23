<?php
// monitoring.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Monitoring Quality Gates</title>
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <!-- Google Fonts - Modern Professional -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <style>
    :root {
      /* Modern Color Palette */
      --midnight-green: #265964;
      --cambridge-blue: #89BDB2;
      --white: #FFFFFF;
      --mint-cream: #E6EEEA;
      --emerald: #00CA90;
      
      /* Additional UI Colors */
      --text-primary: #1a1a1a;
      --text-secondary: #6b7280;
      --border-light: #e5e7eb;
      --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
      
      /* Status Colors */
      --status-success: var(--emerald);
      --status-danger: #ef4444;
      --status-warning: #f59e0b;
      --status-neutral: #6b7280;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, var(--mint-cream) 0%, #f8fafc 100%);
      color: var(--text-primary);
      line-height: 1.6;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    /* Header Styles */
    .main-header {
      background: linear-gradient(135deg, var(--midnight-green) 0%, var(--cambridge-blue) 100%);
      padding: 2rem 0;
      margin-bottom: 2rem;
      position: relative;
      overflow: hidden;
    }
    
    .main-header::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
      opacity: 0.3;
    }
    
    .main-header h1 {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: clamp(1.8rem, 4vw, 2.5rem);
      color: var(--white);
      margin: 0;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative;
      z-index: 2;
    }
    
    .main-header .header-icon {
      background: rgba(255,255,255,0.2);
      padding: 1rem;
      border-radius: 50%;
      margin-right: 1rem;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.3);
    }
    
    /* Container */
    .main-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 0 1rem;
    }
    
    /* Card Styles */
    .modern-card {
      background: var(--white);
      border-radius: 24px;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border-light);
      margin-bottom: 2rem;
      overflow: hidden;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .modern-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-xl);
    }
    
    .card-header-modern {
      background: linear-gradient(135deg, var(--white) 0%, var(--mint-cream) 100%);
      padding: 1.5rem 2rem;
      border-bottom: 2px solid var(--mint-cream);
      position: relative;
    }
    
    .card-header-modern h5 {
      font-family: 'Poppins', sans-serif;
      font-weight: 600;
      color: var(--midnight-green);
      margin: 0;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
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
      color: var(--midnight-green);
      margin-bottom: 0.5rem;
      display: block;
      font-size: 0.95rem;
    }
    
    .form-control-modern, .form-select-modern {
      border: 2px solid var(--border-light);
      border-radius: 12px;
      padding: 0.875rem 1.25rem;
      font-size: 0.95rem;
      background: var(--white);
      transition: all 0.3s ease;
      width: 100%;
      color: var(--text-primary);
      font-weight: 500;
    }
    
    .form-control-modern:focus, .form-select-modern:focus {
      border-color: var(--cambridge-blue);
      box-shadow: 0 0 0 4px rgba(137, 189, 178, 0.1);
      outline: none;
      background: var(--white);
    }
    
    /* Button Styles */
    .btn-modern {
      background: linear-gradient(135deg, var(--emerald) 0%, var(--cambridge-blue) 100%);
      border: none;
      border-radius: 12px;
      padding: 0.875rem 2rem;
      font-weight: 600;
      font-size: 0.95rem;
      color: var(--white);
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      box-shadow: var(--shadow-md);
      position: relative;
      overflow: hidden;
    }
    
    .btn-modern:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
      color: var(--white);
    }
    
    .btn-modern:active {
      transform: translateY(0);
    }
    
    .btn-modern::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }
    
    .btn-modern:hover::before {
      left: 100%;
    }
    
    /* Table Container */
    .table-container {
      background: var(--white);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border-light);
      position: relative;
    }
    
    .table-scroll-wrapper {
      overflow: auto;
      max-height: 70vh;
      min-height: 500px;
      scrollbar-width: thin;
      scrollbar-color: var(--cambridge-blue) var(--mint-cream);
    }
    
    .table-scroll-wrapper::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-track {
      background: var(--mint-cream);
      border-radius: 4px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-thumb {
      background: var(--cambridge-blue);
      border-radius: 4px;
    }
    
    .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
      background: var(--midnight-green);
    }
    
    .table-modern {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      margin: 0;
      font-size: 0.9rem;
    }
    
    .table-modern th {
      background: linear-gradient(135deg, var(--midnight-green) 0%, var(--cambridge-blue) 100%);
      color: var(--white);
      font-weight: 600;
      padding: 1.25rem 1rem;
      text-align: left;
      position: sticky;
      top: 0;
      z-index: 10;
      white-space: nowrap;
      font-size: 0.85rem;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      border-bottom: 3px solid var(--emerald);
    }
    
    .table-modern td {
      padding: 1.25rem 1rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-light);
      background: var(--white);
      transition: all 0.3s ease;
    }
    
    .table-modern tr:hover td {
      background: var(--mint-cream);
      transform: scale(1.01);
    }
    
    .table-modern tr:last-child td {
      border-bottom: none;
    }
    
    /* Status Icons */
    .status-icon {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.85rem;
      white-space: nowrap;
      transition: all 0.3s ease;
      min-width: 120px;
      justify-content: center;
      box-shadow: var(--shadow-sm);
    }
    
    .status-success {
      background: linear-gradient(135deg, var(--emerald), #10b981);
      color: var(--white);
    }
    
    .status-danger {
      background: linear-gradient(135deg, var(--status-danger), #dc2626);
      color: var(--white);
    }
    
    .status-neutral {
      background: linear-gradient(135deg, var(--status-neutral), #64748b);
      color: var(--white);
    }
    
    .status-warning {
      background: linear-gradient(135deg, var(--status-warning), #d97706);
      color: var(--white);
    }
    
    /* Activity Numbers */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--emerald), var(--cambridge-blue));
      color: var(--white);
      font-weight: 700;
      font-size: 0.8rem;
      margin-right: 1rem;
      box-shadow: var(--shadow-md);
    }
    
    /* Gate and UK Codes */
    .gate-code, .uk-code {
      font-weight: 700;
      color: var(--midnight-green);
      margin-bottom: 0.25rem;
      font-size: 0.9rem;
    }
    
    .gate-description, .uk-description {
      color: var(--text-secondary);
      font-size: 0.85rem;
      line-height: 1.4;
    }
    
    /* Date Styling */
    .date-display {
      font-weight: 600;
      color: var(--midnight-green);
      font-size: 0.9rem;
      text-align: center;
      padding: 0.5rem;
      border-radius: 8px;
      background: var(--mint-cream);
    }
    
    .date-active {
      background: linear-gradient(135deg, var(--emerald), #10b981);
      color: var(--white);
      animation: pulse-glow 2s infinite;
      box-shadow: 0 0 10px rgba(0, 202, 144, 0.3);
    }
    
    @keyframes pulse-glow {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.8; transform: scale(1.02); }
    }
    
    /* Level Indicators */
    .level-indicator {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.8rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      min-width: 80px;
    }
    
    .level-pusat {
      background: linear-gradient(135deg, var(--midnight-green), #1e5a66);
      color: var(--white);
    }
    
    .level-provinsi {
      background: linear-gradient(135deg, var(--cambridge-blue), #7bb3a8);
      color: var(--white);
    }
    
    .level-kabupaten {
      background: linear-gradient(135deg, var(--emerald), #10b981);
      color: var(--white);
    }
    
    /* Loading Spinner */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      z-index: 9999;
      display: none;
      align-items: center;
      justify-content: center;
    }
    
    .loading-content {
      text-align: center;
      padding: 3rem;
      background: var(--white);
      border-radius: 24px;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--border-light);
    }
    
    .spinner-modern {
      width: 60px;
      height: 60px;
      border: 4px solid var(--mint-cream);
      border-top: 4px solid var(--emerald);
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin: 0 auto 1.5rem;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-text {
      color: var(--midnight-green);
      font-weight: 600;
      font-size: 1.1rem;
    }
    
    /* Responsive Design */
    @media (max-width: 1200px) {
      .main-container {
        padding: 0 1rem;
      }
      
      .table-scroll-wrapper {
        max-height: 60vh;
      }
    }
    
    @media (max-width: 992px) {
      .main-header {
        padding: 1.5rem 0;
      }
      
      .card-body-modern {
        padding: 1.5rem;
      }
      
      .table-scroll-wrapper {
        max-height: 55vh;
      }
      
      .table-modern th,
      .table-modern td {
        padding: 1rem 0.75rem;
      }
    }
    
    @media (max-width: 768px) {
      .main-header {
        padding: 1rem 0;
      }
      
      .main-header h1 {
        font-size: 1.5rem;
      }
      
      .card-header-modern,
      .card-body-modern {
        padding: 1rem;
      }
      
      .form-control-modern,
      .form-select-modern {
        padding: 0.75rem 1rem;
      }
      
      .btn-modern {
        padding: 0.75rem 1.5rem;
        font-size: 0.9rem;
      }
      
      .table-scroll-wrapper {
        max-height: 50vh;
        min-height: 400px;
      }
      
      .table-modern {
        font-size: 0.8rem;
      }
      
      .table-modern th,
      .table-modern td {
        padding: 0.75rem 0.5rem;
      }
      
      .activity-number {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
        margin-right: 0.75rem;
      }
      
      .status-icon {
        min-width: 100px;
        padding: 0.4rem 0.75rem;
        font-size: 0.75rem;
      }
    }
    
    @media (max-width: 576px) {
      .main-container {
        padding: 0 0.75rem;
      }
      
      .modern-card {
        border-radius: 16px;
        margin-bottom: 1rem;
      }
      
      .table-scroll-wrapper {
        max-height: 45vh;
        min-height: 350px;
      }
      
      .table-modern th {
        font-size: 0.75rem;
        padding: 0.75rem 0.4rem;
      }
      
      .table-modern td {
        padding: 0.75rem 0.4rem;
        font-size: 0.75rem;
      }
    }
    
    /* Column specific widths */
    .table-modern th:nth-child(1), .table-modern td:nth-child(1) {
      min-width: 200px;
    }
    
    .table-modern th:nth-child(2), .table-modern td:nth-child(2) {
      min-width: 220px;
    }
    
    .table-modern th:nth-child(3), .table-modern td:nth-child(3) {
      min-width: 100px;
      text-align: center;
    }
    
    .table-modern th:nth-child(4), .table-modern td:nth-child(4) {
      min-width: 280px;
    }
    
    .table-modern th:nth-child(5), .table-modern td:nth-child(5),
    .table-modern th:nth-child(6), .table-modern td:nth-child(6) {
      min-width: 140px;
      text-align: center;
    }
    
    /* Legacy compatibility - maintaining old class names for backward compatibility */
    .container-fluid {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem;
    }
    
    .card {
      border-radius: 24px;
      box-shadow: var(--shadow-lg);
      border: 1px solid var(--border-light);
      margin-bottom: 2rem;
      background-color: var(--white);
      overflow: hidden;
    }
    
    .card-header {
      background: linear-gradient(135deg, var(--white) 0%, var(--mint-cream) 100%);
      border-bottom: 2px solid var(--mint-cream);
      padding: 1.5rem 2rem;
      font-weight: 600;
      font-size: 1.1rem;
      color: var(--midnight-green);
    }
    
    .card-body {
      padding: 2rem;
    }
    
    .form-label {
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: var(--midnight-green);
    }
    
    .form-control, .form-select {
      border: 2px solid var(--border-light);
      border-radius: 12px;
      padding: 0.875rem 1.25rem;
      font-size: 0.95rem;
      background: var(--white);
      transition: all 0.3s ease;
      font-weight: 500;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--cambridge-blue);
      box-shadow: 0 0 0 4px rgba(137, 189, 178, 0.1);
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--emerald) 0%, var(--cambridge-blue) 100%);
      border: none;
      border-radius: 12px;
      padding: 0.875rem 2rem;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: var(--shadow-md);
    }
    
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
      background: linear-gradient(135deg, var(--emerald) 0%, var(--cambridge-blue) 100%);
      border: none;
    }
    
    .table-wrapper {
      overflow: auto;
      border-radius: 20px;
      box-shadow: var(--shadow-lg);
      margin: 0;
      max-height: 70vh;
      min-height: 500px;
      scrollbar-width: thin;
      scrollbar-color: var(--cambridge-blue) var(--mint-cream);
    }
    
    .table-monitoring {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background-color: var(--white);
      font-size: 0.9rem;
    }
    
    .table-monitoring th {
      background: linear-gradient(135deg, var(--midnight-green) 0%, var(--cambridge-blue) 100%);
      color: var(--white);
      font-weight: 600;
      padding: 1.25rem 1rem;
      font-size: 0.85rem;
      white-space: nowrap;
      position: sticky;
      top: 0;
      z-index: 10;
      text-align: left;
      border-bottom: 3px solid var(--emerald);
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    
    .table-monitoring td {
      padding: 1.25rem 1rem;
      vertical-align: middle;
      font-size: 0.9rem;
      border-bottom: 1px solid var(--border-light);
      background: var(--white);
      transition: all 0.3s ease;
    }
    
    .table-monitoring tr:last-child td {
      border-bottom: none;
    }
    
    tbody tr:hover {
      background-color: var(--mint-cream) !important;
    }
    
    tbody tr:hover td {
      background-color: var(--mint-cream) !important;
      transform: scale(1.005);
    }
    
    /* Status badges with modern styling */
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      padding: 0.5rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      font-size: 0.85rem;
      text-align: center;
      white-space: nowrap;
      min-width: 120px;
      justify-content: center;
      box-shadow: var(--shadow-sm);
      transition: all 0.3s ease;
    }
    
    .status-success {
      background: linear-gradient(135deg, var(--emerald), #10b981);
      color: var(--white);
    }
    
    .status-danger {
      background: linear-gradient(135deg, var(--status-danger), #dc2626);
      color: var(--white);
    }
    
    .status-warning {
      background: linear-gradient(135deg, var(--status-warning), #d97706);
      color: var(--white);
    }
    
    .status-neutral {
      background: linear-gradient(135deg, var(--status-neutral), #64748b);
      color: var(--white);
    }
    
    /* Gate dan UK codes */
    .gate-code, .uk-code {
      font-weight: 700;
      color: var(--midnight-green);
      margin-right: 5px;
    }
    
    /* Spinner overlay */
    #spinner {
      display: none;
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }
    
    .spinner-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1.5rem;
      padding: 3rem;
      border-radius: 24px;
      background: var(--white);
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--border-light);
    }
    
    .spinner-text {
      font-weight: 600;
      color: var(--midnight-green);
      font-size: 1.1rem;
    }
    
    /* Modern spinner */
    .spinner-border {
      width: 60px !important;
      height: 60px !important;
      border: 4px solid var(--mint-cream) !important;
      border-top: 4px solid var(--emerald) !important;
    }
    
    /* Region header styles */
    .region-header {
      font-size: 0.85rem;
      font-weight: 500;
      text-align: center;
      background: linear-gradient(135deg, var(--midnight-green), var(--cambridge-blue));
      color: var(--white);
    }
    
    /* Date in range (enhanced blinking effect) */
    .date-in-range {
      color: var(--white) !important;
      font-weight: 600;
      background: linear-gradient(135deg, var(--emerald), #10b981);
      padding: 0.5rem;
      border-radius: 8px;
      animation: pulse-glow 2s infinite;
      box-shadow: 0 0 10px rgba(0, 202, 144, 0.3);
    }
    
    /* Date column */
    .date-column {
      text-align: center;
      font-weight: 600;
      color: var(--midnight-green);
    }
    
    /* Activity number with modern styling */
    .activity-number {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--emerald), var(--cambridge-blue));
      color: var(--white);
      font-weight: 700;
      margin-right: 1rem;
      font-size: 0.8rem;
      box-shadow: var(--shadow-md);
    }
    
    /* Status column */
    .status-column {
      min-width: 140px;
      text-align: center;
      white-space: nowrap;
    }
    
    /* Enhanced column specific widths */
    .table-monitoring th:nth-child(1), .table-monitoring td:nth-child(1) {
      min-width: 200px;
    }
    
    .table-monitoring th:nth-child(2), .table-monitoring td:nth-child(2) {
      min-width: 220px;
    }
    
    .table-monitoring th:nth-child(3), .table-monitoring td:nth-child(3) {
      min-width: 100px;
      text-align: center;
    }
    
    .table-monitoring th:nth-child(4), .table-monitoring td:nth-child(4) {
      min-width: 280px;
    }
    
    .table-monitoring th:nth-child(5), .table-monitoring td:nth-child(5),
    .table-monitoring th:nth-child(6), .table-monitoring td:nth-child(6) {
      min-width: 140px;
      text-align: center;
    }
  </style>
</head>
<body>
  <!-- Main Header -->
  <header class="main-header">
    <div class="container-fluid">
      <h1 class="d-flex align-items-center">
        <span class="header-icon">
          <i class="fas fa-chart-line"></i>
        </span>
        Monitoring Quality Gates
      </h1>
    </div>
  </header>

  <div class="container-fluid">
    <!-- Input Filters -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-filter me-3"></i>Filter Data</span>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <div class="col-md-2">
            <label for="yearSelect" class="form-label">
              <i class="fas fa-calendar-alt me-2"></i>Tahun
            </label>
            <select id="yearSelect" class="form-select">
              <option value="">Pilih Tahun</option>
              <option value="2023">2023</option>
              <option value="2024">2024</option>
              <option value="2025">2025</option>
            </select>
          </div>
          <div class="col-md-5">
            <label for="projectSelect" class="form-label">
              <i class="fas fa-project-diagram me-2"></i>Pilih Kegiatan
            </label>
            <select id="projectSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-3">
            <label for="regionSelect" class="form-label">
              <i class="fas fa-map-marked-alt me-2"></i>Pilih Cakupan Wilayah
            </label>
            <select id="regionSelect" class="form-select" disabled></select>
          </div>
          <div class="col-md-2 d-flex align-items-end">
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
      <div class="spinner-border text-primary" role="status"></div>
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
          confirmButtonColor: '#00CA90',
          background: '#fff',
          color: '#265964'
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

      // Modern date formatting (date, month, year only)
      const formatDate = dateStr => {
        if (!dateStr || dateStr === '-') return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
          const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                          'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
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

      // Modern status badge with icons
      const getStatusBadge = status => {
        if (status.startsWith('Sudah')) {
          return `<span class="status-badge status-success">
            <i class="fas fa-check-circle"></i> ${status}
          </span>`;
        }
        if (status.startsWith('Belum')) {
          return `<span class="status-badge status-danger">
            <i class="fas fa-times-circle"></i> ${status}
          </span>`;
        }
        if (status === 'Tidak perlu') {
          return `<span class="status-badge status-neutral">
            <i class="fas fa-stop-circle"></i> ${status}
          </span>`;
        }
        return `<span class="status-badge status-warning">
          <i class="fas fa-exclamation-circle"></i> ${status}
        </span>`;
      };

      // Get level indicator with modern styling
      const getLevelIndicator = (level) => {
        if (level === "Pusat") {
          return `<span class="level-indicator level-pusat">
            <i class="fas fa-crown me-1"></i> ${level}
          </span>`;
        }
        if (level === "Provinsi") {
          return `<span class="level-indicator level-provinsi">
            <i class="fas fa-map me-1"></i> ${level}
          </span>`;
        }
        if (level === "Kabupaten") {
          return `<span class="level-indicator level-kabupaten">
            <i class="fas fa-building me-1"></i> ${level}
          </span>`;
        }
        return `<span class="level-indicator">
          <i class="fas fa-question me-1"></i> ${level}
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
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <span><i class="fas fa-table me-3"></i>Hasil Monitoring</span>
              <span id="resultCount" class="badge" style="background: linear-gradient(135deg, var(--emerald), var(--cambridge-blue)); color: white; padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600;">${Object.keys(activityData).length} aktivitas</span>
            </div>
            <div class="card-body p-0">
              <div class="table-wrapper">
                <table class="table-monitoring">
                  <thead>
                    <tr>
                      <th><i class="fas fa-door-open me-2"></i>Gate</th>
                      <th><i class="fas fa-chart-bar me-2"></i>Ukuran Kualitas</th>
                      <th><i class="fas fa-layer-group me-2"></i>Level</th>
                      <th><i class="fas fa-tasks me-2"></i>Aktivitas</th>
                      <th class="date-column"><i class="fas fa-calendar-day me-2"></i>Tanggal Mulai</th>
                      <th class="date-column"><i class="fas fa-calendar-check me-2"></i>Tanggal Selesai</th>
        `;
        
        // Tambahkan kolom status untuk setiap wilayah
        regions.forEach(region => {
          tableHtml += `<th class="status-column region-header"><i class="fas fa-map-marker-alt me-2"></i>${region.name}</th>`;
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
                <td rowspan="${rowspanValue}" style="text-align: center;">${getLevelIndicator(ukLevels[data.uk])}</td>
              `;
            }
            
            // Tentukan apakah tanggal dalam rentang aktif
            const startDate = data.start;
            const endDate = data.end;
            const isInDateRange = isDateInRange(startDate, endDate);
            
            // Format tanggal dengan styling modern
            const startDateFormatted = formatDate(startDate);
            const endDateFormatted = formatDate(endDate);
            
            // Buat HTML untuk tanggal dengan styling yang tepat
            const startDateHtml = isInDateRange ? 
              `<div class="date-display date-active">${startDateFormatted}</div>` : 
              `<div class="date-display">${startDateFormatted}</div>`;
              
            const endDateHtml = isInDateRange ? 
              `<div class="date-display date-active">${endDateFormatted}</div>` : 
              `<div class="date-display">${endDateFormatted}</div>`;
            
            tableHtml += `
              <td><span class="activity-number">${activityNumber}</span>${data.activity}</td>
              <td class="date-column">${startDateHtml}</td>
              <td class="date-column">${endDateHtml}</td>
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
</body>
</html>
