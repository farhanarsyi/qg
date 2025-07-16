<?php
/**
 * SSO Login Page - Refactored Version
 * Displays SSO login interface for BPS authentication
 */

// Load application configuration
require_once 'config/bootstrap.php';

try {
    // Initialize SSO Manager
    $ssoManager = new SSOManager();
    $logger = new Logger('SSO_LOGIN');
    
    // If already logged in, redirect appropriately
    if ($ssoManager->isLoggedIn()) {
        $redirectTo = getArrayValue($_GET, 'redirect', 'index.php');
        
        // Validate redirect target
        $allowedRedirects = ['index.php', 'monitoring.php', 'profile.php', 'dashboard', 'monitoring', 'profile'];
        
        if (in_array($redirectTo, $allowedRedirects)) {
            $redirectMap = [
                'dashboard' => 'index.php',
                'monitoring' => 'monitoring.php',
                'profile' => 'profile.php'
            ];
            
            $redirectTo = getArrayValue($redirectMap, $redirectTo, $redirectTo);
            redirect($redirectTo);
        } else {
            redirect('index.php');
        }
    }
    
    // Generate authorization URL
    $authUrl = $ssoManager->getAuthorizationUrl();
    $error = getArrayValue($_GET, 'error');
    
} catch (Exception $e) {
    $logger = $logger ?? new Logger('SSO_LOGIN');
    $logger->error('SSO Login Error: ' . $e->getMessage());
    $error = 'system_error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SSO BPS - <?= e(QG_APP_NAME) ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #059669;
            --primary-hover: #047857;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --neutral-color: #6b7280;
            --dark-color: #111827;
            --border-color: #e5e7eb;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            color: var(--dark-color);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            margin: 2rem;
        }
        
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(5, 150, 105, 0.08);
            padding: 3rem 2.5rem;
            text-align: center;
        }
        
        .bps-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }
        
        .bps-logo i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-title {
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--neutral-color);
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }
        
        .error-alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--danger-color);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .sso-info {
            background: rgba(5, 150, 105, 0.05);
            border: 1px solid rgba(5, 150, 105, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .sso-info h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.75rem;
        }
        
        .btn-sso {
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-weight: 500;
            font-size: 1.1rem;
            color: white;
            width: 100%;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-sso:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(5, 150, 105, 0.3);
            color: white;
            text-decoration: none;
        }
        
        .footer-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--neutral-color);
            font-size: 0.85rem;
        }
        
        @media (max-width: 480px) {
            .login-container { margin: 1rem; }
            .login-card { padding: 2rem 1.5rem; }
            .login-title { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="bps-logo">
                <i class="fas fa-chart-line"></i>
            </div>
            
            <h1 class="login-title">Login SSO</h1>
            <p class="login-subtitle">Masuk dengan akun BPS Anda</p>
            
            <?php if ($error): ?>
                <div class="error-alert">
                    <strong>⚠️ Error:</strong>
                    <?php
                    $errorMessages = [
                        'missing_params' => 'Parameter login tidak lengkap',
                        'invalid_state' => 'Session tidak valid, silakan coba lagi',
                        'system_error' => 'Terjadi kesalahan sistem, silakan coba lagi'
                    ];
                    echo e(getArrayValue($errorMessages, $error, 'Terjadi kesalahan, silakan coba lagi'));
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="sso-info">
                <h5><i class="fas fa-shield-alt"></i> Single Sign-On BPS</h5>
                <p>Gunakan username dan password BPS Anda untuk mengakses sistem Quality Gates.</p>
            </div>
            
            <a href="<?= e($authUrl) ?>" class="btn-sso">
                <i class="fas fa-sign-in-alt"></i>
                Masuk dengan SSO BPS
            </a>
            
            <div class="footer-info">
                <strong><?= e(QG_APP_NAME) ?></strong> v<?= e(QG_APP_VERSION) ?><br>
                Badan Pusat Statistik
            </div>
        </div>
    </div>
</body>
</html> 