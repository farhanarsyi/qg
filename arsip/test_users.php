<?php
// test_users.php - Testing halaman untuk melihat daftar user yang tersedia
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Test Users - Quality Gates</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background-color: #f5f5f7;
      padding: 2rem;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.04);
      border: none;
      background: white;
    }
    .table {
      margin: 0;
    }
    .badge {
      font-size: 0.75rem;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row">
      <div class="col-12">
        <h1 class="mb-4">Daftar User untuk Testing</h1>
        
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0">User Database</h5>
            <small class="text-muted">Password untuk semua user: <code>password123</code></small>
          </div>
          <div class="card-body p-0">
            <div id="loading" class="text-center p-4">
              <div class="spinner-border text-primary" role="status"></div>
              <div class="mt-2">Memuat data...</div>
            </div>
            
            <div id="errorMessage" class="alert alert-danger m-3" style="display: none;"></div>
            
            <div id="usersTable" style="display: none;">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Provinsi</th>
                    <th>Kabupaten</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody id="usersTableBody">
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
        <div class="mt-4">
          <div class="card">
            <div class="card-body">
              <h6>Quick Login untuk Testing:</h6>
              <div id="quickLoginButtons" class="mt-3">
                <!-- Buttons akan ditambahkan via JavaScript -->
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-4 text-center">
          <a href="monitoring.php" class="btn btn-outline-primary me-2">
            <i class="fas fa-chart-line me-1"></i>Monitoring
          </a>
          <a href="login.php" class="btn btn-primary">
            <i class="fas fa-sign-in-alt me-1"></i>Login
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      loadUsers();
    });

    function loadUsers() {
      $.ajax({
        url: 'api.php',
        method: 'POST',
        data: {
          action: 'fetchUsers' // Kita perlu buat endpoint ini
        },
        dataType: 'text',
        success: function(response) {
          try {
            const jsonData = JSON.parse(extractJson(response));
            if (jsonData.status && jsonData.data) {
              displayUsers(jsonData.data);
            } else {
              showError(jsonData.message || 'Gagal memuat data user');
            }
          } catch(e) {
            showError('Error parsing response: ' + e.message);
          }
        },
        error: function() {
          showError('Error connecting to server');
        }
      });
    }

    function extractJson(response) {
      const start = response.indexOf('{');
      const end = response.lastIndexOf('}');
      return (start !== -1 && end !== -1 && end > start)
        ? response.substring(start, end + 1)
        : response;
    }

    function showError(message) {
      $('#loading').hide();
      $('#errorMessage').text(message).show();
    }

    function displayUsers(users) {
      $('#loading').hide();
      
      if (users.length === 0) {
        showError('Tidak ada data user ditemukan');
        return;
      }

      let tableHtml = '';
      let quickLoginHtml = '';
      
      users.forEach(function(user) {
        // Tentukan role berdasarkan prov dan kab
        let role = 'Unknown';
        let roleClass = 'secondary';
        
        if (user.prov === "00" && user.kab === "00") {
          role = 'Pusat';
          roleClass = 'primary';
        } else if (user.prov !== "00" && user.kab === "00") {
          role = 'Provinsi';
          roleClass = 'warning';
        } else if (user.prov !== "00" && user.kab !== "00") {
          role = 'Kabupaten/Kota';
          roleClass = 'info';
        }

        tableHtml += `
          <tr>
            <td><strong>${user.username}</strong></td>
            <td>${user.name || '-'}</td>
            <td>${user.prov || '-'}</td>
            <td>${user.kab || '-'}</td>
            <td><span class="badge bg-${roleClass}">${role}</span></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" onclick="quickLogin('${user.username}')">
                Quick Login
              </button>
            </td>
          </tr>
        `;

        // Tambahkan quick login button (max 5 untuk demo)
        if (users.indexOf(user) < 5) {
          quickLoginHtml += `
            <button class="btn btn-sm btn-outline-${roleClass} me-2 mb-2" onclick="quickLogin('${user.username}')">
              ${user.username} (${role})
            </button>
          `;
        }
      });

      $('#usersTableBody').html(tableHtml);
      $('#quickLoginButtons').html(quickLoginHtml);
      $('#usersTable').show();
    }

    function quickLogin(username) {
      // Simpan username di localStorage dan redirect ke login
      localStorage.setItem('quick_login_username', username);
      window.location.href = 'login.php';
    }
  </script>
  
  <!-- Font Awesome -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html> 