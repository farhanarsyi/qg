<?php
/**
 * Environment Configuration
 * Detects environment and loads appropriate config
 */

// Detect environment based on server name or explicit setting
function detectEnvironment() {
    // Check if explicitly set
    if (defined('QG_ENVIRONMENT')) {
        return QG_ENVIRONMENT;
    }
    
    // Auto-detect based on domain
    $serverName = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
    
    if (strpos($serverName, 'dashboardqg.web.bps.go.id') !== false) {
        return 'production';
    } elseif (strpos($serverName, 'localhost') !== false) {
        return 'local';
    } else {
        return 'development';
    }
}

// Set environment constant
if (!defined('QG_ENVIRONMENT')) {
    define('QG_ENVIRONMENT', detectEnvironment());
}

// Load environment specific config
switch (QG_ENVIRONMENT) {
    case 'production':
        require_once __DIR__ . '/production.php';
        break;
    case 'local':
        require_once __DIR__ . '/local.php';
        break;
    default:
        require_once __DIR__ . '/development.php';
        break;
}

// Load shared configuration
require_once __DIR__ . '/shared.php'; 