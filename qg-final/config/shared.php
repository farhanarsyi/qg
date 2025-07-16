<?php
/**
 * Shared Configuration
 * Configuration that applies to all environments
 */

// Application Information
define('QG_APP_NAME', 'Quality Gates Dashboard');
define('QG_APP_VERSION', '2.0.0');
define('QG_APP_DESCRIPTION', 'Dashboard Quality Gates BPS');

// Session Configuration
define('QG_SESSION_NAME', 'qg_session');
define('QG_SESSION_LIFETIME', 28800); // 8 hours

// File Upload Configuration
define('QG_MAX_UPLOAD_SIZE', '10M');
define('QG_ALLOWED_EXTENSIONS', ['csv', 'xlsx', 'pdf']);

// Pagination Configuration
define('QG_DEFAULT_PAGE_SIZE', 25);
define('QG_MAX_PAGE_SIZE', 100);

// Date/Time Configuration
define('QG_TIMEZONE', 'Asia/Jakarta');
date_default_timezone_set(QG_TIMEZONE);

// Cache Configuration
define('QG_CACHE_TTL', 3600); // 1 hour

// Security Configuration
define('QG_PASSWORD_MIN_LENGTH', 8);
define('QG_SESSION_REGENERATE_ID', true);

// API Rate Limiting
define('QG_API_RATE_LIMIT', 100); // requests per minute
define('QG_API_TIMEOUT', 30); // seconds

// Start output buffering if not already started
if (!ob_get_level()) {
    ob_start();
}

// Set default PHP settings
ini_set('default_charset', 'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8'); 