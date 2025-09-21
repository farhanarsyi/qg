<?php
// app_config.php - Konfigurasi Aplikasi untuk QG Cpanel

// ========================================
// KONFIGURASI CARD DISPLAY
// ========================================

// Konfigurasi untuk menampilkan/menyembunyikan card monitoring
define('SHOW_MONITORING_CARDS', true); // Set ke false untuk menyembunyikan card monitoring

// Konfigurasi untuk menampilkan/menyembunyikan card dashboard
define('SHOW_DASHBOARD_CARDS', true); // Set ke false untuk menyembunyikan card dashboard

// ========================================
// KONFIGURASI DEBUG MODE
// ========================================

// Konfigurasi debug mode - mengontrol logging dan console output
define('DEBUG_MODE', false); // Set ke true untuk mengaktifkan debug mode

// ========================================
// KONFIGURASI FILTER STATUS
// ========================================

// Konfigurasi untuk filter status lanjutan
define('ENABLE_ADVANCED_STATUS_FILTER', true); // Set ke false untuk menonaktifkan filter status lanjutan

// ========================================
// FUNGSI UTILITAS
// ========================================

/**
 * Cek apakah card monitoring harus ditampilkan
 * @return bool
 */
function shouldShowMonitoringCards() {
    return defined('SHOW_MONITORING_CARDS') ? SHOW_MONITORING_CARDS : true;
}

/**
 * Cek apakah card dashboard harus ditampilkan
 * @return bool
 */
function shouldShowDashboardCards() {
    return defined('SHOW_DASHBOARD_CARDS') ? SHOW_DASHBOARD_CARDS : true;
}

/**
 * Cek apakah debug mode aktif
 * @return bool
 */
function isDebugMode() {
    return defined('DEBUG_MODE') ? DEBUG_MODE : false;
}

/**
 * Cek apakah filter status lanjutan aktif
 * @return bool
 */
function isAdvancedStatusFilterEnabled() {
    return defined('ENABLE_ADVANCED_STATUS_FILTER') ? ENABLE_ADVANCED_STATUS_FILTER : true;
}

/**
 * Fungsi untuk log debug (hanya muncul jika debug mode aktif)
 * @param string $message
 * @param string $type (log, warn, error)
 */
function debugLog($message, $type = 'log') {
    if (isDebugMode()) {
        $prefix = '🔍 [DEBUG] ';
        switch ($type) {
            case 'warn':
                echo "<script>console.warn('" . $prefix . addslashes($message) . "');</script>";
                break;
            case 'error':
                echo "<script>console.error('" . $prefix . addslashes($message) . "');</script>";
                break;
            default:
                echo "<script>console.log('" . $prefix . addslashes($message) . "');</script>";
                break;
        }
    }
}

/**
 * Fungsi untuk mendapatkan konfigurasi card dalam format JavaScript
 * @return string
 */
function getCardConfigJS() {
    return "
    window.appConfig = {
        showMonitoringCards: " . (shouldShowMonitoringCards() ? 'true' : 'false') . ",
        showDashboardCards: " . (shouldShowDashboardCards() ? 'true' : 'false') . ",
        debugMode: " . (isDebugMode() ? 'true' : 'false') . ",
        advancedStatusFilter: " . (isAdvancedStatusFilterEnabled() ? 'true' : 'false') . "
    };
    ";
}

/**
 * Fungsi untuk mendapatkan konfigurasi card dalam format PHP array
 * @return array
 */
function getCardConfigArray() {
    return [
        'show_monitoring_cards' => shouldShowMonitoringCards(),
        'show_dashboard_cards' => shouldShowDashboardCards(),
        'debug_mode' => isDebugMode(),
        'advanced_status_filter' => isAdvancedStatusFilterEnabled()
    ];
}
?>
