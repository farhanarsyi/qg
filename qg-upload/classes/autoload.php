<?php
// classes/autoload.php - Simple Autoloader for Application Classes

spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/' . $className . '.php';
    
    if (file_exists($classFile)) {
        require_once $classFile;
        return true;
    }
    
    return false;
});

// Load composer autoloader if exists
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} 