<?php
// database_config.php - Konfigurasi Database untuk Logging

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'qg');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Fungsi untuk membuat koneksi database
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}

// Fungsi untuk membuat tabel logging jika belum ada
function createLoggingTable() {
    try {
        $pdo = getDatabaseConnection();
        
        $sql = "CREATE TABLE IF NOT EXISTS logging (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL,
            nama VARCHAR(255) NOT NULL,
            provinsi VARCHAR(100) NOT NULL,
            kabupaten VARCHAR(100) NOT NULL,
            waktu_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_waktu_login (waktu_login),
            INDEX idx_provinsi (provinsi),
            INDEX idx_kabupaten (kabupaten)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        error_log('Logging table created or already exists');
        return true;
    } catch (Exception $e) {
        error_log('Failed to create logging table: ' . $e->getMessage());
        return false;
    }
}

// Fungsi untuk test koneksi database
function testDatabaseConnection() {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query("SELECT 1");
        return true;
    } catch (Exception $e) {
        error_log('Database connection test failed: ' . $e->getMessage());
        return false;
    }
}
?>
