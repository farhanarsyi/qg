<?php
// sso_integration.php - Legacy SSO Integration (Compatibility Layer)
// This file provides backward compatibility for existing SSO code
// New code should use the bootstrap.php and class-based structure

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

require_once 'bootstrap.php';

// Legacy function wrappers for backward compatibility
function getSSOWilayahFilter() {
    global $wilayahManager;
    return $wilayahManager->getWilayahFilter();
}

function getWilayahSQLFilter($table_alias = '') {
    global $wilayahManager;
    return $wilayahManager->getSQLFilter($table_alias);
}

function getWilayahJSOptions() {
    global $wilayahManager;
    return $wilayahManager->getJSOptions();
}

function getWilayahFilterLabel($filter) {
    global $wilayahManager;
    return $wilayahManager->getFilterLabel($filter);
}

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    global $ssoManager;
    return $ssoManager->isLoggedIn();
}

function getUserData() {
    global $ssoManager;
    return $ssoManager->getUserData();
}

function requireSSOLogin($currentPage = '') {
    global $ssoManager;
    $ssoManager->requireLogin($currentPage);
}

// Legacy navbar rendering function (if still used somewhere)
function renderSSONavbar($activePage = '') {
    global $ssoManager, $wilayahManager;
    
    if (!$ssoManager->isLoggedIn()) {
        return;
    }
    
    $userData = $ssoManager->getUserData();
    $wilayahFilter = $wilayahManager->getWilayahFilter();
    
    // Simple navbar for compatibility - in real implementation, this should be in templates
    echo '<nav class="navbar navbar-expand-lg" id="mainNavbar">';
    echo '<div class="container-fluid">';
    echo '<a class="navbar-brand" href="index.php">';
    echo '<i class="fas fa-chart-line me-2"></i>';
    echo '<span class="brand-text">Quality Gates</span>';
    echo '</a>';
    echo '</div>';
    echo '</nav>';
}

// Additional compatibility functions
function injectWilayahJS() {
    global $wilayahManager;
    
    $options = $wilayahManager->getJSOptions();
    echo "<script>window.wilayahOptions = $options;</script>";
}

function renderDebugWilayahInfo() {
    if (!isset($_GET['debug'])) {
        return;
    }
    
    global $ssoManager, $wilayahManager;
    
    echo '<div style="position: fixed; bottom: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #ccc; z-index: 9999;">';
    echo '<h6>Debug Info:</h6>';
    echo '<small>';
    echo 'Logged In: ' . ($ssoManager->isLoggedIn() ? 'Yes' : 'No') . '<br>';
    
    if ($ssoManager->isLoggedIn()) {
        $userData = $ssoManager->getUserData();
        $wilayahFilter = $wilayahManager->getWilayahFilter();
        
        echo 'User: ' . htmlspecialchars($userData['nama'] ?? 'Unknown') . '<br>';
        echo 'Unit: ' . htmlspecialchars($wilayahFilter['unit_kerja'] ?? 'Unknown') . '<br>';
        echo 'Filter: ' . htmlspecialchars($wilayahFilter['filterLabel'] ?? 'None') . '<br>';
    }
    
    echo '</small>';
    echo '</div>';
}