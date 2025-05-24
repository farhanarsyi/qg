<?php
// login.php - Halaman login untuk Quality Gates
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Quality Gates</title>
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
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .login-container {
      max-width: 420px;
      width: 100%;
      margin: 2rem;
    }
    
    .login-card {
      background: #ffffff;
      border-radius: 16px;
      box-shadow: 0 20px 40px rgba(5, 150, 105, 0.08);
      padding: 3rem 2.5rem;
      border: none;
      overflow: hidden;
    }
    
    .login-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }
    
    .login-title {
      font-size: 2rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 0.5rem;
    }
    
    .login-subtitle {
      color: var(--neutral-color);
      font-size: 1rem;
      font-weight: 400;
    }
    
    .login-icon {
      width: 80px;
      height: 80px;
      background: linear-gradient(135deg, var(--primary-color), var(--success-color));
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
      box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
    }
    
    .login-icon i {
      font-size: 2rem;
      color: white;
    }
    
    .form-group {
      margin-bottom: 1.5rem;
    }
    
    .form-label {
      font-weight: 500;
      margin-bottom: 0.75rem;
      color: var(--dark-color);
      font-size: 0.95rem;
    }
    
    .form-control {
      border-radius: 12px;
      border: 1px solid var(--border-color);
      padding: 1rem 1.25rem;
      font-size: 1rem;
      background-color: #fff;
      transition: all 0.2s ease;
      height: auto;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15);
      outline: none;
    }
    
    .input-group {
      position: relative;
    }
    
    .input-group .form-control {
      padding-left: 3.5rem;
    }
    
    .input-group-text {
      position: absolute;
      left: 1.25rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--neutral-color);
      z-index: 10;
      padding: 0;
    }
    
    .btn-login {
      background: linear-gradient(135deg, var(--primary-color), var(--success-color));
      border: none;
      border-radius: 12px;
      padding: 1rem 2rem;
      font-weight: 500;
      font-size: 1rem;
      color: white;
      width: 100%;
      transition: all 0.2s ease;
      margin-top: 1rem;
    }
    
    .btn-login:hover {
      background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
      color: white;
    }
    
    .btn-login:active {
      transform: translateY(0);
    }
    
    .btn-login:disabled {
      opacity: 0.6;
      transform: none;
      box-shadow: none;
    }
    
    .alert {
      border-radius: 12px;
      border: none;
      padding: 1rem 1.25rem;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
    }
    
    .alert-danger {
      background-color: rgba(239, 68, 68, 0.12);
      color: var(--danger-color);
    }
    
    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
    }
    
    @media (max-width: 480px) {
      .login-container {
        margin: 1rem;
      }
      
      .login-card {
        padding: 2rem 1.5rem;
      }
      
      .login-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-icon">
          <i class="fas fa-shield-alt"></i>
        </div>
        <h1 class="login-title">Quality Gates</h1>
        <p class="login-subtitle">Masuk ke Dashboard Anda</p>
      </div>
      
      <form id="loginForm">
        <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>
        
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-user"></i>
            </span>
            <input type="text" class="form-control" id="username" name="username" 
                   placeholder="Masukkan username Anda" required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="fas fa-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" 
                   placeholder="Masukkan password Anda" required>
          </div>
        </div>
        
        <button type="submit" class="btn btn-login" id="loginBtn">
          <span class="login-text">Masuk</span>
          <span class="login-spinner" style="display: none;">
            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
            Memproses...
          </span>
        </button>
      </form>
    </div>
  </div>

  <script>
    $(function(){
      const API_URL = "api.php";
      
      // Helper function untuk menampilkan error
      const showError = (message) => {
        $("#errorAlert").text(message).fadeIn();
        setTimeout(() => {
          $("#errorAlert").fadeOut();
        }, 5000);
      };
      
      // Helper function untuk extract JSON dari response
      const extractJson = response => {
        const start = response.indexOf('{');
        const end = response.lastIndexOf('}');
        return (start !== -1 && end !== -1 && end > start)
          ? response.substring(start, end + 1)
          : response;
      };
      
      // Event handler untuk form login
      $("#loginForm").on('submit', function(e){
        e.preventDefault();
        
        const username = $("#username").val().trim();
        const password = $("#password").val().trim();
        
        if (!username || !password) {
          showError("Silakan isi username dan password");
          return;
        }
        
        // Tampilkan loading state
        $("#loginBtn").prop('disabled', true);
        $(".login-text").hide();
        $(".login-spinner").show();
        $("#errorAlert").hide();
        
        // Kirim request login
        $.ajax({
          url: API_URL,
          method: "POST",
          data: {
            action: "login",
            username: username,
            password: password
          },
          dataType: "text",
          success: function(response) {
            try {
              const jsonData = JSON.parse(extractJson(response));
              
              if (jsonData.status && jsonData.data) {
                // Login berhasil - simpan data user di localStorage
                localStorage.setItem('qg_user', JSON.stringify(jsonData.data));
                
                // Redirect ke dashboard
                window.location.href = 'dashboard.php';
              } else {
                showError(jsonData.message || "Login gagal");
              }
            } catch(e) {
              console.error("Parse error:", e);
              showError("Terjadi kesalahan saat memproses data");
            }
          },
          error: function() {
            showError("Terjadi kesalahan pada server");
          },
          complete: function() {
            // Reset loading state
            $("#loginBtn").prop('disabled', false);
            $(".login-text").show();
            $(".login-spinner").hide();
          }
        });
      });
      
      // Auto focus pada username input
      $("#username").focus();
      
      // Enter key navigation
      $("#username").on('keypress', function(e){
        if (e.which === 13) {
          $("#password").focus();
        }
      });
      
      // Cek apakah sudah login (ada data user di localStorage)
      const userData = localStorage.getItem('qg_user');
      if (userData) {
        try {
          const user = JSON.parse(userData);
          if (user && user.username) {
            // Sudah login, redirect ke dashboard
            window.location.href = 'dashboard.php';
          }
        } catch(e) {
          // Data tidak valid, hapus dari localStorage
          localStorage.removeItem('qg_user');
        }
      }
      
      // Cek apakah ada quick login username
      const quickLoginUsername = localStorage.getItem('quick_login_username');
      if (quickLoginUsername) {
        $("#username").val(quickLoginUsername);
        $("#password").focus();
        localStorage.removeItem('quick_login_username'); // Hapus setelah digunakan
      }
    });
  </script>
</body>
</html> 