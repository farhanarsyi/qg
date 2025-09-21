<?php
// setup_database.php - Script untuk setup database dan tabel logging

require_once 'database_config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Setup Database - SSO Logging</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; padding: 20px; }
        .setup-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>🗄️ Database Setup - SSO Logging System</h1>";

// Step 1: Check if database exists
echo "<div class='setup-section'>
        <h3>1. Database Check</h3>";

try {
    // Try to connect to MySQL without specifying database
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "<p class='success'>✅ MySQL connection successful</p>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $db_exists = $stmt->fetch();
    
    if ($db_exists) {
        echo "<p class='success'>✅ Database '" . DB_NAME . "' already exists</p>";
    } else {
        echo "<p class='warning'>⚠️ Database '" . DB_NAME . "' does not exist</p>";
        
        // Create database
        try {
            $pdo->exec("CREATE DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p class='success'>✅ Database '" . DB_NAME . "' created successfully</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Failed to create database: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ MySQL connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='info'>Please check your database configuration in database_config.php</p>";
}

echo "</div>";

// Step 2: Create logging table
echo "<div class='setup-section'>
        <h3>2. Create Logging Table</h3>";

try {
    $result = createLoggingTable();
    if ($result) {
        echo "<p class='success'>✅ Logging table created/verified successfully</p>";
        
        // Show table structure
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("DESCRIBE logging");
        $columns = $stmt->fetchAll();
        
        echo "<p class='info'>Table structure:</p>";
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Null</th>
                        <th>Key</th>
                        <th>Default</th>
                        <th>Extra</th>
                    </tr>
                </thead>
                <tbody>";
        
        foreach ($columns as $column) {
            echo "<tr>
                    <td>{$column['Field']}</td>
                    <td>{$column['Type']}</td>
                    <td>{$column['Null']}</td>
                    <td>{$column['Key']}</td>
                    <td>{$column['Default']}</td>
                    <td>{$column['Extra']}</td>
                  </tr>";
        }
        
        echo "</tbody></table>";
        
    } else {
        echo "<p class='error'>❌ Failed to create logging table</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error creating table: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Step 3: Test insert
echo "<div class='setup-section'>
        <h3>3. Test Data Insert</h3>";

try {
    require_once 'sso_logging.php';
    
    $test_data = array(
        'username' => 'setup_test_' . time(),
        'nama' => 'Setup Test User',
        'provinsi' => 'DKI Jakarta',
        'kabupaten' => 'Jakarta Pusat',
        'waktu_login' => date('Y-m-d H:i:s')
    );
    
    $log_id = saveSSOLoginLog($test_data);
    if ($log_id) {
        echo "<p class='success'>✅ Test data inserted successfully - Log ID: $log_id</p>";
        
        // Verify the data
        $logs = getSSOLoginLogs($test_data['username'], 1);
        if (!empty($logs)) {
            echo "<p class='success'>✅ Data verification successful</p>";
            echo "<p class='info'>Inserted data: " . htmlspecialchars(json_encode($logs[0])) . "</p>";
        } else {
            echo "<p class='warning'>⚠️ Data inserted but verification failed</p>";
        }
    } else {
        echo "<p class='error'>❌ Failed to insert test data</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error testing insert: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";

// Step 4: Configuration summary
echo "<div class='setup-section'>
        <h3>4. Configuration Summary</h3>
        <table class='table'>
            <tr><td><strong>Database Host:</strong></td><td>" . DB_HOST . "</td></tr>
            <tr><td><strong>Database Name:</strong></td><td>" . DB_NAME . "</td></tr>
            <tr><td><strong>Database User:</strong></td><td>" . DB_USER . "</td></tr>
            <tr><td><strong>Charset:</strong></td><td>" . DB_CHARSET . "</td></tr>
        </table>
      </div>";

// Final instructions
echo "<div class='setup-section'>
        <h3>🎉 Setup Complete!</h3>
        <p class='success'>Your SSO logging system is now ready to use.</p>
        <p><strong>What happens next:</strong></p>
        <ul>
            <li>Every SSO login will be automatically logged to the database</li>
            <li>Logs include: username, nama, provinsi, kabupaten, and waktu_login</li>
            <li>You can view logs using the test_logging.php script</li>
            <li>Logs are stored in the 'logging' table in your 'qg' database</li>
        </ul>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li><a href='test_logging.php' class='btn btn-info'>Run Logging Tests</a></li>
            <li><a href='sso_login.php' class='btn btn-primary'>Test SSO Login</a></li>
            <li>Monitor your database for new login entries</li>
        </ul>
      </div>";

echo "</div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
