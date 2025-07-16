<?php
/**
 * Bootstrap Configuration
 * Main configuration loader that sets up the application
 */

// Define application root path
define('QG_ROOT_PATH', dirname(__DIR__));
define('QG_CONFIG_PATH', __DIR__);
define('QG_INCLUDES_PATH', QG_ROOT_PATH . '/includes');
define('QG_CLASSES_PATH', QG_INCLUDES_PATH . '/classes');
define('QG_HELPERS_PATH', QG_INCLUDES_PATH . '/helpers');

// Load environment configuration
require_once __DIR__ . '/environment.php';

// Load Composer autoloader
if (file_exists(QG_ROOT_PATH . '/vendor/autoload.php')) {
    require_once QG_ROOT_PATH . '/vendor/autoload.php';
}

// Auto-load helper functions
$helperFiles = glob(QG_HELPERS_PATH . '/*.php');
foreach ($helperFiles as $file) {
    require_once $file;
}

// Auto-load classes (if not using composer autoload)
spl_autoload_register(function ($className) {
    // Convert namespace to file path
    $classPath = str_replace(['\\', 'QG\\'], ['/', ''], $className);
    $file = QG_CLASSES_PATH . '/' . $classPath . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize session with custom configuration
if (session_status() === PHP_SESSION_NONE) {
    session_name(QG_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => QG_SESSION_LIFETIME,
        'path' => '/',
        'secure' => !QG_DEBUG, // Use secure cookies in production
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
    
    // Regenerate session ID for security
    if (QG_SESSION_REGENERATE_ID && !isset($_SESSION['regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['regenerated'] = true;
    }
} 