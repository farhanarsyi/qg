<?php
// config/app.php - Application Configuration

return [
    'name' => 'Quality Gates Dashboard',
    'version' => '2.0.0',
    'timezone' => 'Asia/Jakarta',
    'debug' => false,
    
    'session' => [
        'lifetime' => 7200, // 2 hours
        'cookie_name' => 'QG_SESSION'
    ],
    
    'paths' => [
        'assets' => '/assets/',
        'templates' => __DIR__ . '/../templates/',
        'classes' => __DIR__ . '/../classes/'
    ],
    
    'csv_files' => [
        'daftar_daerah' => 'daftar_daerah.csv'
    ]
]; 