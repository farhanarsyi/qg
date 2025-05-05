<?php
require_once '../config.php';

// Check if user is authenticated and is superadmin
if (!isAuthenticated() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit;
}

// Get statistics
$userCount = 0;
$projectCount = 0;
$coverageCount = 0;
$syncCount = 0;

// Count users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
if ($result) {
    $userCount = $result->fetch_assoc()['count'];
}

// Count projects
$result = $conn->query("SELECT COUNT(*) as count FROM projects");
if ($result) {
    $projectCount = $result->fetch_assoc()['count'];
}

// Count coverages
$result = $conn->query("SELECT COUNT(*) as count FROM coverages");
if ($result) {
    $coverageCount = $result->fetch_assoc()['count'];
}

// Count sync logs
$result = $conn->query("SELECT COUNT(*) as count FROM sync_logs");
if ($result) {
    $syncCount = $result->fetch_assoc()['count'];
}

// Get recent activities
$recentLogs = [];
$result = $conn->query("SELECT * FROM sync_logs ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentLogs[] = $row;
    }
}

// Get recent users
$recentUsers = [];
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentUsers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Monitoring Quality Gates</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0071e3;
            --success-color: #34c759;
            --warning-color: #ff9f0a;
            --danger-color: #ff3b30;
            --neutral-color: #8e8e93;
            --light-color: #f5f5f7;
            --dark-color: #1d1d1f;
            --border-color: #d2d2d7;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f5f7;
            color: #1d1d1f;
            min-height: 100vh;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .sidebar {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        .content {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 1.5rem;
        }
        
        .nav-link {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background-color: rgba(0,113,227,0.1);
            color: var(--primary-color);
        }
        
        .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .nav-link i {
            width: 24px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: #005bbc;
            transform: translateY(-1px);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--neutral-color);
            margin-top: 0.5rem;
        }
        
        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0,113,227,0.1);
            color: var(--primary-color);
        }
        
        .activity-text {
            font-size: 0.9rem;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: var(--neutral-color);
        }
        
        .user-badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 100px;
            font-weight: 500;
            font-size: 0.8rem;
            text-align: center;
        }
        
        .badge-superadmin {
            background-color: rgba(142,142,147,0.15);
            color: var(--neutral-color);
        }
        
        .badge-pusat {
            background-color: rgba(0,113,227,0.15);
            color: var(--primary-color);
        }
        
        .badge-provinsi {
            background-color: rgba(52,199,89,0.15);
            color: var(--success-color);
        }
        
        .badge-kabkot {
            background-color: rgba(255,159,10,0.15);
            color: var(--warning-color);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="mb-0">Admin Dashboard</h1>
            <div>
                <span class="me-3"><?php echo $_SESSION['name']; ?></span>
                <a href="../logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="sidebar">
                    <h5 class="mb-3">Menu</h5>
                    <div class="nav flex-column">
                        <a href="index.php" class="nav-link active">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="sync_coverage.php" class="nav-link">
                            <i class="fas fa-sync"></i> Sync Coverage
                        </a>
                        <a href="manage_users.php" class="nav-link">
                            <i class="fas fa-users"></i> Kelola Pengguna
                        </a>
                        <a href="../index.php" class="nav-link">
                            <i class="fas fa-chart-bar"></i> Quality Gates
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mb-4">Dashboard</h2>
                    
                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-value"><?php echo $userCount; ?></div>
                                <div class="stat-label">Total Pengguna</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                                <div class="stat-value"><?php echo $projectCount; ?></div>
                                <div class="stat-label">Total Projects</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div class="stat-value"><?php echo $coverageCount; ?></div>
                                <div class="stat-label">Total Coverages</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card">
                                <div class="stat-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <div class="stat-value"><?php echo $syncCount; ?></div>
                                <div class="stat-label">Total Sinkronisasi</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Aksi Cepat</h5>
                                    <div class="d-flex gap-2">
                                        <a href="sync_coverage.php" class="btn btn-primary">
                                            <i class="fas fa-sync me-2"></i> Sinkronisasi Coverage
                                        </a>
                                        <a href="manage_users.php" class="btn btn-outline-primary">
                                            <i class="fas fa-user-plus me-2"></i> Tambah Pengguna
                                        </a>
                                        <a href="../index.php" class="btn btn-outline-primary">
                                            <i class="fas fa-chart-bar me-2"></i> Lihat Monitoring
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Recent Activities -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Aktivitas Terbaru</h5>
                                    <?php if (empty($recentLogs)): ?>
                                    <p class="text-muted">Belum ada aktivitas</p>
                                    <?php else: ?>
                                    <div>
                                        <?php foreach ($recentLogs as $log): ?>
                                        <div class="activity-item d-flex align-items-center">
                                            <div class="activity-icon me-3">
                                                <i class="fas fa-history"></i>
                                            </div>
                                            <div>
                                                <div class="activity-text">
                                                    <?php echo $log['message']; ?>
                                                    <span class="badge bg-<?php echo $log['status'] ? 'success' : 'danger'; ?> ms-2">
                                                        <?php echo $log['status'] ? 'Sukses' : 'Gagal'; ?>
                                                    </span>
                                                </div>
                                                <div class="activity-time">
                                                    <?php echo date('d M Y H:i', strtotime($log['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Users -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Pengguna Terbaru</h5>
                                    <?php if (empty($recentUsers)): ?>
                                    <p class="text-muted">Belum ada pengguna</p>
                                    <?php else: ?>
                                    <div>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <div class="activity-item d-flex align-items-center">
                                            <div class="activity-icon me-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="activity-text">
                                                    <?php echo $user['name']; ?>
                                                    <span class="user-badge badge-<?php echo $user['user_level']; ?> ms-2">
                                                        <?php echo ucfirst($user['user_level']); ?>
                                                    </span>
                                                </div>
                                                <div class="activity-time">
                                                    Username: <?php echo $user['username']; ?> | 
                                                    Dibuat: <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 