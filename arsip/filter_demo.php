<?php
require_once 'sso_config.php';
startSession();

if (!isLoggedIn()) {
    header('Location: sso_login.php');
    exit;
}

$user_data = getUserData();
$wilayah_filter = getUserWilayahFilter();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Demo Filter Wilayah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h2>🌏 Demo Filter Wilayah Quality Gates</h2>
        
        <div class="card mb-3">
            <div class="card-body">
                <h5>👤 User: <?php echo htmlspecialchars($user_data['nama'] ?? 'N/A'); ?></h5>
                <p><strong>Level:</strong> <?php echo $user_data['unit_kerja']['nama_level'] ?? 'N/A'; ?></p>
                <p><strong>Akses Data:</strong> 
                    <?php if ($wilayah_filter['can_view_all']): ?>
                        Seluruh Indonesia
                    <?php elseif ($wilayah_filter['level'] == 'provinsi'): ?>
                        Provinsi <?php echo htmlspecialchars($user_data['provinsi']); ?>
                    <?php else: ?>
                        <?php echo htmlspecialchars($user_data['kabupaten']); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h5>🧪 Test Level Berbeda</h5>
                <a href="demo_unit_levels.php?level=pusat" class="btn btn-success">Test Pusat</a>
                <a href="demo_unit_levels.php?level=provinsi" class="btn btn-info">Test Provinsi</a> 
                <a href="demo_unit_levels.php?level=kabupaten" class="btn btn-warning">Test Kabupaten</a>
                <hr>
                <a href="profile.php" class="btn btn-secondary">← Kembali ke Profil</a>
            </div>
        </div>
    </div>
</body>
</html> 