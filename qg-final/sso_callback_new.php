<?php
/**
 * SSO Callback Handler - Refactored Version
 * Handles SSO authentication callback from BPS
 */

// Load application configuration
require_once 'config/bootstrap.php';

try {
    // Initialize SSO Manager
    $ssoManager = new SSOManager();
    $logger = new Logger('SSO_CALLBACK');
    
    $logger->info('SSO Callback accessed', ['params' => $_GET]);
    
    // Validate required parameters
    $code = getArrayValue($_GET, 'code');
    $state = getArrayValue($_GET, 'state');
    
    if (!$code || !$state) {
        $logger->warning('Missing required parameters');
        redirect('sso_login.php?error=missing_params');
    }
    
    // Handle SSO callback
    $userData = $ssoManager->handleCallback($code, $state);
    
    // Determine redirect destination
    $redirectTo = getArrayValue($_GET, 'redirect', 'index.php');
    
    // Validate redirect target for security
    $allowedRedirects = ['index.php', 'monitoring.php', 'profile.php', 'dashboard', 'monitoring', 'profile'];
    
    if (in_array($redirectTo, $allowedRedirects)) {
        // Normalize redirect targets
        $redirectMap = [
            'dashboard' => 'index.php',
            'monitoring' => 'monitoring.php',
            'profile' => 'profile.php'
        ];
        
        $redirectTo = getArrayValue($redirectMap, $redirectTo, $redirectTo);
        redirect($redirectTo);
    } else {
        redirect('index.php'); // Default to dashboard
    }
    
} catch (Exception $e) {
    // Log the error
    $logger = $logger ?? new Logger('SSO_CALLBACK');
    $logger->error('SSO Callback Error: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Show user-friendly error page
    showErrorPage($e);
}

/**
 * Display error page
 * @param Exception $e The exception
 */
function showErrorPage($e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>SSO Error - <?= e(QG_APP_NAME) ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
            .error-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
            .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
            .error-content { background: #f8f9fa; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0; }
            .error-actions { margin-top: 20px; }
            .btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 4px; font-weight: 500; }
            .btn-primary { background: #007bff; color: white; }
            .btn-secondary { background: #6c757d; color: white; }
            .btn:hover { opacity: 0.8; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1 class="error-title">🚨 SSO Authentication Error</h1>
            
            <div class="error-content">
                <h3>What happened?</h3>
                <p>There was an error during the SSO authentication process.</p>
                <?php if (QG_DEBUG): ?>
                    <details>
                        <summary>Technical Details (Debug Mode)</summary>
                        <p><strong>Error:</strong> <?= e($e->getMessage()) ?></p>
                        <p><strong>File:</strong> <?= e($e->getFile()) ?></p>
                        <p><strong>Line:</strong> <?= $e->getLine() ?></p>
                    </details>
                <?php endif; ?>
            </div>
            
            <h3>Possible Solutions:</h3>
            <ul>
                <li>Check if SSO server is accessible</li>
                <li>Verify your network connection</li>
                <li>Try logging in again</li>
                <li>Contact system administrator if problem persists</li>
            </ul>
            
            <div class="error-actions">
                <a href="sso_login.php" class="btn btn-primary">🔑 Try Login Again</a>
                <a href="index.php" class="btn btn-secondary">🏠 Go to Home</a>
                <?php if (QG_DEBUG): ?>
                    <a href="debug.php?show=debug" class="btn btn-secondary">🐛 Debug Info</a>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?> 