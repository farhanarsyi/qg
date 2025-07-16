<?php
// index.php - Dashboard Quality Gates (Refactored)

try {
    require_once 'bootstrap.php';
    
    // Ensure user is logged in
    requireLogin('index.php');
    
    // Get user data and wilayah filter
    $userData = getUserData();
    $wilayahFilter = getWilayahFilter();
    
    // Set page variables
    $pageTitle = 'Dashboard - Quality Gates';
    $pageDescription = 'Dashboard Quality Gates BPS - Monitoring Kualitas Data';
    
} catch (Exception $e) {
    error_log('index.php Error: ' . $e->getMessage());
    echo "<!DOCTYPE html><html><head><title>Error - Dashboard</title></head><body>";
    echo "<h1>🚨 Dashboard Error</h1>";
    echo "<p>Terjadi kesalahan saat memuat dashboard: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><a href='sso_login.php'>🔑 Login Ulang</a> | <a href='main.php'>🏠 Kembali</a></p>";
    echo "</body></html>";
    exit;
}

// Load header template
require_once 'templates/header.php';
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg" id="mainNavbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">
      <i class="fas fa-chart-line me-2"></i>
      <span class="brand-text">Quality Gates</span>
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">
            <i class="fas fa-home me-1"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="monitoring.php">
            <i class="fas fa-monitor-heart-rate me-1"></i>Monitoring
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
            <i class="fas fa-user me-1"></i>Profil
          </a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle user-info" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle me-2"></i>
            <span class="user-name"><?= htmlspecialchars($userData['nama'] ?? 'User') ?></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><h6 class="dropdown-header">Wilayah Akses</h6></li>
            <li><span class="dropdown-item-text small"><?= htmlspecialchars($wilayahFilter['nama_user'] ?? '') ?></span></li>
            <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($wilayahFilter['jabatan'] ?? '') ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profil</a></li>
            <li><a class="dropdown-item" href="sso_logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <h1><i class="fas fa-chart-line me-2"></i>Dashboard Quality Gates</h1>
      <p class="dashboard-subtitle">
        Monitoring dan evaluasi kualitas data BPS - 
        <strong><?= htmlspecialchars($wilayahFilter['filterLabel'] ?? 'Akses Terbatas') ?></strong>
      </p>
    </div>
  </div>

  <!-- Dashboard Content -->
  <div class="row" id="dashboardContent">
    <!-- Statistics Cards -->
    <div class="col-md-3 mb-3">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
          <h5 class="card-title">Total Submissions</h5>
          <h3 class="text-primary" id="totalSubmissions">-</h3>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
          <h5 class="card-title">Completed</h5>
          <h3 class="text-success" id="completedSubmissions">-</h3>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-clock fa-2x text-warning mb-2"></i>
          <h5 class="card-title">Pending</h5>
          <h3 class="text-warning" id="pendingSubmissions">-</h3>
        </div>
      </div>
    </div>
    
    <div class="col-md-3 mb-3">
      <div class="card">
        <div class="card-body text-center">
          <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
          <h5 class="card-title">Rejected</h5>
          <h3 class="text-danger" id="rejectedSubmissions">-</h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Recent Activity -->
  <div class="row">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
        </div>
        <div class="card-body">
          <div id="recentActivity">
            <div class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-lg-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Regional Summary</h5>
        </div>
        <div class="card-body">
          <div id="regionalSummary">
            <div class="text-center">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Dashboard JavaScript
$(document).ready(function() {
    // Load dashboard data
    loadDashboardStats();
    loadRecentActivity();
    loadRegionalSummary();
});

function loadDashboardStats() {
    $.get('api.php?endpoint=stats')
        .done(function(response) {
            if (response.status && response.data.length > 0) {
                const stats = response.data[0];
                $('#totalSubmissions').text(stats.total || 0);
                $('#completedSubmissions').text(stats.completed || 0);
                $('#pendingSubmissions').text(stats.pending || 0);
                $('#rejectedSubmissions').text(stats.rejected || 0);
            }
        })
        .fail(function() {
            console.error('Failed to load dashboard stats');
        });
}

function loadRecentActivity() {
    $.get('api.php?endpoint=recent_activity&limit=10')
        .done(function(response) {
            if (response.status) {
                let html = '';
                if (response.data.length === 0) {
                    html = '<p class="text-muted">No recent activity</p>';
                } else {
                    response.data.forEach(function(item) {
                        html += `
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <strong>${item.activity || 'Activity'}</strong>
                                    <br><small class="text-muted">${item.description || ''}</small>
                                </div>
                                <small class="text-muted">${item.created_at || ''}</small>
                            </div>
                        `;
                    });
                }
                $('#recentActivity').html(html);
            }
        })
        .fail(function() {
            $('#recentActivity').html('<p class="text-danger">Failed to load recent activity</p>');
        });
}

function loadRegionalSummary() {
    $.get('api.php?endpoint=wilayah_summary')
        .done(function(response) {
            if (response.status) {
                let html = '';
                if (response.data.length === 0) {
                    html = '<p class="text-muted">No data available</p>';
                } else {
                    response.data.forEach(function(item) {
                        const wilayahName = '<?= $wilayahManager->getNamaDaerah("' + item.kode_provinsi + item.kode_kabupaten + '") ?>';
                        html += `
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <strong>${wilayahName}</strong>
                                    <br><small class="text-muted">${item.total_submissions} submissions</small>
                                </div>
                                <span class="badge bg-primary rounded-pill">${Math.round(item.avg_score || 0)}%</span>
                            </div>
                        `;
                    });
                }
                $('#regionalSummary').html(html);
            }
        })
        .fail(function() {
            $('#regionalSummary').html('<p class="text-danger">Failed to load regional summary</p>');
        });
}
</script>

<?php
// Load footer template
require_once 'templates/footer.php';
?> 