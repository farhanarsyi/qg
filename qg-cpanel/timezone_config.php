<?php
// timezone_config.php - Set timezone before any other operations

// Set proper timezone to avoid UTC+7 warnings
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Jakarta');
}

// Also set via ini_set as backup
if (function_exists('ini_set')) {
    ini_set('date.timezone', 'Asia/Jakarta');
}
?>
