<?php
/**
 * Common Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Redirect to a specific page
 * @param string $url URL to redirect to
 * @param int $statusCode HTTP status code
 */
function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/**
 * Safely get value from array with default
 * @param array $array Array to search
 * @param string $key Key to look for
 * @param mixed $default Default value if key not found
 * @return mixed
 */
function getArrayValue($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Escape HTML output
 * @param string $string String to escape
 * @return string Escaped string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date for display
 * @param string|int $date Date string or timestamp
 * @param string $format Date format
 * @return string Formatted date
 */
function formatDate($date, $format = 'Y-m-d H:i:s') {
    if (is_numeric($date)) {
        return date($format, $date);
    }
    return date($format, strtotime($date));
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if request is AJAX
 * @return bool
 */
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Return JSON response
 * @param mixed $data Data to return
 * @param int $statusCode HTTP status code
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log message to application log
 * @param string $level Log level
 * @param string $message Log message
 * @param array $context Additional context
 */
function logMessage($level, $message, $context = []) {
    $logger = new Logger('HELPER');
    switch (strtolower($level)) {
        case 'debug':
            $logger->debug($message, $context);
            break;
        case 'info':
            $logger->info($message, $context);
            break;
        case 'warning':
        case 'warn':
            $logger->warning($message, $context);
            break;
        case 'error':
            $logger->error($message, $context);
            break;
        default:
            $logger->info($message, $context);
    }
}

/**
 * Get current URL
 * @return string Current URL
 */
function getCurrentUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $uri;
}

/**
 * Validate required fields in array
 * @param array $data Data to validate
 * @param array $required Required field names
 * @return array Validation errors
 */
function validateRequired($data, $required) {
    $errors = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $errors[] = "Field '{$field}' is required";
        }
    }
    return $errors;
}

/**
 * Format file size
 * @param int $bytes Size in bytes
 * @return string Formatted size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.1f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
}

/**
 * Pagination helper
 * @param int $total Total items
 * @param int $page Current page
 * @param int $perPage Items per page
 * @return array Pagination data
 */
function paginate($total, $page = 1, $perPage = null) {
    $perPage = $perPage ?: QG_DEFAULT_PAGE_SIZE;
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    
    $offset = ($page - 1) * $perPage;
    
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
        'prev_page' => $page > 1 ? $page - 1 : null,
        'next_page' => $page < $totalPages ? $page + 1 : null
    ];
} 