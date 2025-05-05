<?php
require_once '../config.php';

// Check if user is authenticated and is superadmin
if (!isAuthenticated() || !isSuperAdmin()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = '';

// Process form submission for user management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
        $level = $_POST['user_level'];
        $prov = '00';
        $kab = '00';
        
        // For provinsi and kabkot, parse the area code
        if ($level === 'provinsi') {
            $prov = substr($username, 0, 2);
            $kab = '00';
        } else if ($level === 'kabkot') {
            $prov = substr($username, 0, 2);
            $kab = substr($username, 2, 2);
        }
        
        // Validate username format
        $validFormat = false;
        if ($level === 'pusat' && $username === '0000') {
            $validFormat = true;
        } else if ($level === 'provinsi' && strlen($username) === 4 && substr($username, 2, 2) === '00') {
            $validFormat = true;
        } else if ($level === 'kabkot' && strlen($username) === 4 && substr($username, 2, 2) !== '00') {
            $validFormat = true;
        }
        
        if (!$validFormat) {
            $message = "Format username tidak sesuai dengan level pengguna.";
            $messageType = "danger";
        } else {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = "Username $username sudah digunakan.";
                $messageType = "danger";
            } else {
                // Create password hash (default: same as username)
                $password = password_hash($username, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (username, password, name, user_level, prov_code, kab_code) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $password, $name, $level, $prov, $kab);
                
                if ($stmt->execute()) {
                    $message = "Pengguna $username berhasil ditambahkan.";
                    $messageType = "success";
                } else {
                    $message = "Gagal menambahkan pengguna: " . $stmt->error;
                    $messageType = "danger";
                }
                
                $stmt->close();
            }
        }
    }
    
    // Delete user
    else if (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_level != 'superadmin'");
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = "Pengguna berhasil dihapus.";
            $messageType = "success";
        } else {
            $message = "Gagal menghapus pengguna.";
            $messageType = "danger";
        }
        
        $stmt->close();
    }
    
    // Generate users
    else if (isset($_POST['generate_users'])) {
        try {
            $conn->begin_transaction();
            $countGenerated = 0;
            
            // Generate user for pusat (if not exists)
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = '0000'");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $username = '0000';
                $password = password_hash($username, PASSWORD_DEFAULT);
                $name = 'Pusat - Nasional';
                $level = 'pusat';
                $prov = '00';
                $kab = '00';
                
                $stmt = $conn->prepare("INSERT INTO users (username, password, name, user_level, prov_code, kab_code) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $username, $password, $name, $level, $prov, $kab);
                $stmt->execute();
                $countGenerated++;
            }
            
            // Get all distinct provinces and kabkot from coverages
            $provinces = [];
            $kabkots = [];
            
            $result = $conn->query("SELECT DISTINCT prov, kab, name FROM coverages WHERE prov != '00' ORDER BY prov, kab");
            
            while ($row = $result->fetch_assoc()) {
                $code = $row['prov'] . $row['kab'];
                
                if ($row['kab'] === '00') {
                    $provinces[$code] = [
                        'code' => $code,
                        'prov' => $row['prov'],
                        'kab' => $row['kab'],
                        'name' => $row['name']
                    ];
                } else {
                    $kabkots[$code] = [
                        'code' => $code,
                        'prov' => $row['prov'],
                        'kab' => $row['kab'],
                        'name' => $row['name']
                    ];
                }
            }
            
            // Generate province users
            foreach ($provinces as $province) {
                $username = $province['code'];
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $password = password_hash($username, PASSWORD_DEFAULT);
                    $name = $province['name'];
                    $level = 'provinsi';
                    $prov = $province['prov'];
                    $kab = $province['kab'];
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, password, name, user_level, prov_code, kab_code) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $username, $password, $name, $level, $prov, $kab);
                    $stmt->execute();
                    $countGenerated++;
                }
            }
            
            // Generate kabkot users
            foreach ($kabkots as $kabkot) {
                $username = $kabkot['code'];
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $password = password_hash($username, PASSWORD_DEFAULT);
                    $name = $kabkot['name'];
                    $level = 'kabkot';
                    $prov = $kabkot['prov'];
                    $kab = $kabkot['kab'];
                    
                    $stmt = $conn->prepare("INSERT INTO users (username, password, name, user_level, prov_code, kab_code) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $username, $password, $name, $level, $prov, $kab);
                    $stmt->execute();
                    $countGenerated++;
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            $message = "Berhasil membuat $countGenerated pengguna baru.";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Get all users
$users = [];
$result = $conn->query("SELECT * FROM users ORDER BY user_level, username");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get user statistics
$userStats = [
    'total' => count($users),
    'pusat' => 0,
    'provinsi' => 0,
    'kabkot' => 0
];

foreach ($users as $user) {
    if ($user['user_level'] === 'provinsi') {
        $userStats['provinsi']++;
    } else if ($user['user_level'] === 'kabkot') {
        $userStats['kabkot']++;
    } else if ($user['user_level'] === 'pusat') {
        $userStats['pusat']++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
        
        .card-stat {
            text-align: center;
            padding: 1.5rem;
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--neutral-color);
            margin-top: 0.5rem;
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
                        <a href="index.php" class="nav-link">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                        <a href="sync_coverage.php" class="nav-link">
                            <i class="fas fa-sync"></i> Sync Coverage
                        </a>
                        <a href="manage_users.php" class="nav-link active">
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
                    <h2 class="mb-4">Manajemen Pengguna</h2>
                    
                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card card-stat">
                                <div class="stat-value"><?php echo $userStats['total']; ?></div>
                                <div class="stat-label">Total Pengguna</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat">
                                <div class="stat-value"><?php echo $userStats['pusat']; ?></div>
                                <div class="stat-label">Pusat</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat">
                                <div class="stat-value"><?php echo $userStats['provinsi']; ?></div>
                                <div class="stat-label">Provinsi</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stat">
                                <div class="stat-value"><?php echo $userStats['kabkot']; ?></div>
                                <div class="stat-label">Kabupaten/Kota</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Auto Generate Button -->
                    <div class="mb-4">
                        <form method="post" action="">
                            <button type="submit" name="generate_users" class="btn btn-primary">
                                <i class="fas fa-magic me-2"></i> Generate Pengguna dari Data Coverage
                            </button>
                        </form>
                    </div>
                    
                    <!-- Add New User Form -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Tambah Pengguna Baru</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="" class="row g-3">
                                <div class="col-md-4">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" placeholder="Username (4 digit kode)" required>
                                    <div class="form-text">
                                        Format: 0000 (Pusat), XXOO (Provinsi), XXYY (Kabupaten)
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Nama</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Nama lengkap" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="user_level" class="form-label">Level Pengguna</label>
                                    <select class="form-select" id="user_level" name="user_level" required>
                                        <option value="pusat">Pusat</option>
                                        <option value="provinsi">Provinsi</option>
                                        <option value="kabkot">Kabupaten/Kota</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="add_user" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i> Tambah Pengguna
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- User List -->
                    <h5 class="mb-3">Daftar Pengguna</h5>
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Level</th>
                                    <th>Kode</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['username']; ?></td>
                                    <td><?php echo $user['name']; ?></td>
                                    <td>
                                        <span class="user-badge badge-<?php echo $user['user_level']; ?>">
                                            <?php echo ucfirst($user['user_level']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $user['prov_code'] . $user['kab_code']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['user_level'] !== 'superadmin'): ?>
                                        <form method="post" action="" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable({
                order: [[0, 'asc']],
                pageLength: 10,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ entri per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                    infoFiltered: "(difilter dari _MAX_ total entri)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });
    </script>
</body>
</html> 