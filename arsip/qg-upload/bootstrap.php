<?php
// bootstrap.php - Application Bootstrap File

// Start output buffering to prevent header issues
if (!ob_get_level()) {
    ob_start();
}

// Enable error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load autoloader
require_once __DIR__ . '/classes/autoload.php';

// Load configurations
$appConfig = require __DIR__ . '/config/app.php';
$dbConfig = require __DIR__ . '/config/database.php';
$ssoConfig = require __DIR__ . '/config/sso.php';

// Set timezone
date_default_timezone_set($appConfig['timezone']);

// Initialize core objects
$ssoManager = new SSOManager();
$database = new Database();
$wilayahManager = new WilayahManager($ssoManager);

// Helper functions
function requireLogin($currentPage = '') {
    global $ssoManager;
    $ssoManager->requireLogin($currentPage);
}

function getUserData() {
    global $ssoManager;
    return $ssoManager->getUserData();
}

function isLoggedIn() {
    global $ssoManager;
    return $ssoManager->isLoggedIn();
}

function getWilayahFilter() {
    global $wilayahManager;
    return $wilayahManager->getWilayahFilter();
}

function getWilayahSQLFilter($tableAlias = '') {
    global $wilayahManager;
    return $wilayahManager->getSQLFilter($tableAlias);
}

function getWilayahJSOptions() {
    global $wilayahManager;
    return $wilayahManager->getJSOptions();
}

function getNamaDaerah($kode) {
    global $wilayahManager;
    return $wilayahManager->getNamaDaerah($kode);
} 