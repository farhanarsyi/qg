<?php
// File untuk mengecek status autentikasi user
session_start();

function isUserAuthenticated() {
    return isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true;
}

function getUserData() {
    if (isUserAuthenticated()) {
        return $_SESSION['user_data'];
    }
    return null;
}

function requireAuth() {
    if (!isUserAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function getLogoutUrl() {
    if (isset($_SESSION['logout_url'])) {
        return $_SESSION['logout_url'];
    }
    return 'logout.php';
}

// Fungsi untuk mendapatkan inisial nama user
function getUserInitials($nama) {
    $words = explode(' ', $nama);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        if (strlen($initials) >= 2) break;
    }
    return $initials ?: 'U';
}
?> 