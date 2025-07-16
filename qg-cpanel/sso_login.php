<?php
// sso_login.php - Halaman login SSO BPS

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

require_once 'vendor/autoload.php';
require_once 'sso_config.php';

use JKD\SSO\Client\Provider\Keycloak;

startSession();

// Jika sudah login, redirect sesuai parameter atau ke dashboard
if (isLoggedIn()) {
    $redirect_to = $_GET['redirect'] ?? 'index.php';
    
    // Validasi redirect target untuk keamanan
    $allowed_redirects = ['index.php', 'monitoring.php', 'profile.php', 'dashboard', 'monitoring', 'profile'];
    
    if (in_array($redirect_to, $allowed_redirects)) {
        if ($redirect_to === 'dashboard') $redirect_to = 'index.php';
        if ($redirect_to === 'monitoring') $redirect_to = 'monitoring.php';
        if ($redirect_to === 'profile') $redirect_to = 'profile.php';
        
        header('Location: ' . $redirect_to);
    } else {
        header('Location: index.php'); // Default ke dashboard
    }
    exit;
}

// Inisialisasi provider Keycloak dengan library JKD SSO
$provider = new Keycloak([
    'authServerUrl' => SSO_AUTH_SERVER_URL,
    'realm' => SSO_REALM,
    'clientId' => SSO_CLIENT_ID,
    'clientSecret' => SSO_CLIENT_SECRET,
    'redirectUri' => SSO_REDIRECT_URI
]);

// Generate authorization URL dengan state untuk CSRF protection
$auth_url = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SSO BPS - Quality Gates</title>
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
            margin: 0;
            padding: 0;
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
            border: none;
            overflow: hidden;
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
        
        .sso-info p {
            color: var(--neutral-color);
            margin-bottom: 0;
            font-size: 0.95rem;
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
        
        .btn-sso:active {
            transform: translateY(0);
        }
        
        .btn-sso i {
            margin-right: 0.75rem;
        }
        
        .footer-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
            color: var(--neutral-color);
            font-size: 0.85rem;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
            }
            
            .login-card {
                padding: 2rem 1.5rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .login-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="bps-logo">
                <i class="fas fa-building"></i>
            </div>
            
            <h1 class="login-title">Dashboard Quality Gates</h1>
            <p class="login-subtitle">Badan Pusat Statistik</p>
            
            <div class="sso-info">
                <h5><i class="fas fa-shield-alt"></i> Single Sign-On BPS</h5>
                <p>Gunakan akun pegawai BPS Anda untuk masuk ke sistem Dashboard Quality Gates.</p>
            </div>
            
            <a href="<?php echo htmlspecialchars($auth_url); ?>" class="btn-sso">
                <i class="fas fa-sign-in-alt"></i>
                Masuk dengan SSO BPS
            </a>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>