<?php
// test_logging.php - Test script untuk sistem logging SSO

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

require_once 'database_config.php';
require_once 'sso_logging.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test SSO Logging System</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>🔍 Test SSO Logging System</h1>";

// Test 1: Database Connection
echo "<div class='test-section'>
        <h3>1. Database Connection Test</h3>";

try {
    $pdo = getDatabaseConnection();
    echo "<p class='success'>✅ Database connection successful</p>";
    echo "<p class='info'>Connected to: " . DB_NAME . " on " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Test 2: Create Table
echo "<div class='test-section'>
        <h3>2. Create Logging Table Test</h3>";

$table_result = createLoggingTable();
if ($table_result) {
    echo "<p class='success'>✅ Logging table created/verified successfully</p>";
} else {
    echo "<p class='error'>❌ Failed to create logging table</p>";
}

echo "</div>";

// Test 3: Insert Test Data
echo "<div class='test-section'>
        <h3>3. Insert Test Log Data</h3>";

$test_data = array(
    'username' => 'test_user_' . time(),
    'nama' => 'Test User SSO',
    'provinsi' => 'DKI Jakarta',
    'kabupaten' => 'Jakarta Pusat',
    'waktu_login' => date('Y-m-d H:i:s')
);

$log_id = saveSSOLoginLog($test_data);
if ($log_id) {
    echo "<p class='success'>✅ Test log inserted successfully - Log ID: $log_id</p>";
    echo "<p class='info'>Test data: " . htmlspecialchars(json_encode($test_data)) . "</p>";
} else {
    echo "<p class='error'>❌ Failed to insert test log</p>";
}

echo "</div>";

// Test 4: Retrieve Logs
echo "<div class='test-section'>
        <h3>4. Retrieve Logs Test</h3>";

$logs = getSSOLoginLogs(null, 10);
if (!empty($logs)) {
    echo "<p class='success'>✅ Retrieved " . count($logs) . " log entries</p>";
    echo "<table class='table table-striped'>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Provinsi</th>
                    <th>Kabupaten</th>
                    <th>Waktu Login</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($logs as $log) {
        echo "<tr>
                <td>{$log['id']}</td>
                <td>{$log['username']}</td>
                <td>{$log['nama']}</td>
                <td>{$log['provinsi']}</td>
                <td>{$log['kabupaten']}</td>
                <td>{$log['waktu_login']}</td>
              </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p class='error'>❌ No logs found or failed to retrieve logs</p>";
}

echo "</div>";

// Test 5: Statistics
echo "<div class='test-section'>
        <h3>5. Login Statistics Test</h3>";

$stats = getSSOLoginStats(30);
if (!empty($stats)) {
    echo "<p class='success'>✅ Retrieved login statistics</p>";
    echo "<table class='table table-striped'>
            <thead>
                <tr>
                    <th>Provinsi</th>
                    <th>Total Logins</th>
                    <th>Unique Users</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($stats as $stat) {
        echo "<tr>
                <td>{$stat['provinsi']}</td>
                <td>{$stat['login_count']}</td>
                <td>{$stat['unique_users']}</td>
              </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p class='error'>❌ No statistics found or failed to retrieve statistics</p>";
}

echo "</div>";

// Test 6: Export Test
echo "<div class='test-section'>
        <h3>6. Export to CSV Test</h3>";

$export_file = exportSSOLogsToCSV('test_export.csv');
if ($export_file) {
    echo "<p class='success'>✅ Logs exported successfully</p>";
    echo "<p class='info'>Export file: " . htmlspecialchars($export_file) . "</p>";
} else {
    echo "<p class='error'>❌ Failed to export logs</p>";
}

echo "</div>";

// Summary
echo "<div class='test-section'>
        <h3>📊 Test Summary</h3>
        <p class='info'>All tests completed. Check the results above to verify the logging system is working properly.</p>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Ensure your MySQL database 'qg' exists</li>
            <li>Make sure the database user has proper permissions</li>
            <li>Test the actual SSO login flow</li>
            <li>Monitor the logs in the database</li>
        </ul>
        <p><a href='sso_login.php' class='btn btn-primary'>Test SSO Login</a></p>
      </div>";

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
