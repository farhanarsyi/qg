<?php
// login.php - Redirect to SSO (Legacy Login System Deprecated)
session_start();

// Sistem login lama sudah tidak digunakan lagi  
// Semua autentikasi sekarang menggunakan SSO BPS
header('Location: sso_login.php');
exit;
?> 