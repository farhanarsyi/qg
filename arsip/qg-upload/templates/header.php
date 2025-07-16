<?php
// templates/header.php - Common HTML Header Template

$appConfig = require __DIR__ . '/../config/app.php';
$pageTitle = $pageTitle ?? $appConfig['name'];
$pageDescription = $pageDescription ?? 'Dashboard Quality Gates BPS';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Navbar CSS -->
    <link rel="stylesheet" href="assets/navbar.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --primary-light: #d1fae5;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --neutral-color: #6b7280;
            --light-color: #f9fafb;
            --dark-color: #111827;
            --border-color: #e5e7eb;
            --text-secondary: #374151;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: var(--dark-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .container-fluid {
            max-width: 95vw;
            margin: 0 auto;
            padding: 1rem;
        }
        
        h1 {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            font-size: 1.5rem;
        }
        
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.04);
            border: none;
            margin-bottom: 1.5rem;
            background-color: #ffffff;
            overflow: visible;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 0.5rem 0.6rem;
            font-weight: 500;
            font-size: 0.7rem;
            color: var(--dark-color);
        }
        
        .card-body {
            padding: 0.6rem;
            overflow: visible;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.25rem;
            color: var(--dark-color);
            font-size: 0.7rem;
        }
        
        /* Additional styles can be added here */
    </style>
    
    <?php if (isset($additionalCSS)): ?>
        <?= $additionalCSS ?>
    <?php endif; ?>
</head>
<body> 