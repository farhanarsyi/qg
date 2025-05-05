<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'qgate_monitoring';
$db_user = 'root';
$db_pass = '';

// Create database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// API Configuration
$api_base_url = "https://webapps.bps.go.id/nqaf/qgate/api/";
$api_cookie = "ci_session=5503368b0cb86766c2be9ed5d93c038762698865";

// Session configuration
session_start();

// Wrap the callApi function in a check to prevent redeclaration
if (!function_exists('callApi')) {
    function callApi($url, $postData) {
        global $api_cookie;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
            'Cookie: ' . $api_cookie
        ]);
        $response = curl_exec($ch);
        if(curl_errno($ch)){
            $error_msg = curl_error($ch);
            curl_close($ch);
            return json_encode(["status" => false, "message" => $error_msg]);
        }
        curl_close($ch);
        return $response;
    }
}

// Helper function to extract JSON from response
function extractJson($response) {
    $start = strpos($response, '{');
    $end = strrpos($response, '}');
    return ($start !== false && $end !== false && $end > $start)
        ? substr($response, $start, $end - $start + 1)
        : $response;
}

// Function to check user authentication
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Function to check if user is superadmin
function isSuperAdmin() {
    return isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'superadmin';
}

// Function to get user area codes
function getUserArea() {
    if (!isAuthenticated()) {
        return null;
    }
    
    return [
        'prov' => $_SESSION['prov_code'] ?? '00',
        'kab' => $_SESSION['kab_code'] ?? '00',
        'level' => $_SESSION['user_level'] ?? 'pusat'
    ];
}
?> 