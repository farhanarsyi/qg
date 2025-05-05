<?php
require_once 'config.php';

// Check if user is authenticated
if (!isAuthenticated()) {
    header('Location: login.php');
    exit;
}

// Redirect to monitoring page
header('Location: monitoring.php');
exit;
?> 