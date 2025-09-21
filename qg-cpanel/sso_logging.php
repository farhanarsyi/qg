<?php
// sso_logging.php - Fungsi untuk logging SSO ke database

require_once 'database_config.php';

// Fungsi untuk menyimpan log login SSO ke database
function saveSSOLoginLog($sso_data) {
    try {
        // Pastikan tabel logging sudah ada
        createLoggingTable();
        
        $pdo = getDatabaseConnection();
        
        // Extract data dari SSO
        $username = $sso_data['username'] ?? '';
        $nama = $sso_data['nama'] ?? '';
        $provinsi = $sso_data['provinsi'] ?? '';
        $kabupaten = $sso_data['kabupaten'] ?? '';
        
        // Generate waktu login jika tidak ada
        $waktu_login = $sso_data['waktu_login'] ?? date('Y-m-d H:i:s');
        
        // Validasi data yang diperlukan
        if (empty($username) || empty($nama)) {
            error_log('SSO Logging: Missing required data - username or nama is empty');
            return false;
        }
        
        // Jika provinsi atau kabupaten kosong, gunakan default
        if (empty($provinsi)) {
            $provinsi = 'Tidak Diketahui';
        }
        if (empty($kabupaten)) {
            $kabupaten = 'Tidak Diketahui';
        }
        
        // Insert data ke tabel logging
        $sql = "INSERT INTO logging (username, nama, provinsi, kabupaten, waktu_login) 
                VALUES (:username, :nama, :provinsi, :kabupaten, :waktu_login)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':username' => $username,
            ':nama' => $nama,
            ':provinsi' => $provinsi,
            ':kabupaten' => $kabupaten,
            ':waktu_login' => $waktu_login
        ]);
        
        if ($result) {
            $log_id = $pdo->lastInsertId();
            error_log("SSO Login logged successfully - ID: $log_id, Username: $username, Nama: $nama");
            return $log_id;
        } else {
            error_log('SSO Logging: Failed to insert log record');
            return false;
        }
        
    } catch (Exception $e) {
        error_log('SSO Logging Error: ' . $e->getMessage());
        return false;
    }
}

// Fungsi untuk mendapatkan log login berdasarkan username
function getSSOLoginLogs($username = null, $limit = 50) {
    try {
        $pdo = getDatabaseConnection();
        
        if ($username) {
            $sql = "SELECT * FROM logging WHERE username = :username ORDER BY waktu_login DESC LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        } else {
            $sql = "SELECT * FROM logging ORDER BY waktu_login DESC LIMIT :limit";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log('Get SSO Logs Error: ' . $e->getMessage());
        return [];
    }
}

// Fungsi untuk mendapatkan statistik login
function getSSOLoginStats($days = 30) {
    try {
        $pdo = getDatabaseConnection();
        
        $sql = "SELECT 
                    COUNT(*) as total_logins,
                    COUNT(DISTINCT username) as unique_users,
                    provinsi,
                    COUNT(*) as login_count
                FROM logging 
                WHERE waktu_login >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY provinsi
                ORDER BY login_count DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log('Get SSO Stats Error: ' . $e->getMessage());
        return [];
    }
}

// Fungsi untuk membersihkan log lama (optional)
function cleanOldSSOLogs($days = 90) {
    try {
        $pdo = getDatabaseConnection();
        
        $sql = "DELETE FROM logging WHERE waktu_login < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        $deleted_count = $stmt->rowCount();
        error_log("Cleaned $deleted_count old SSO log records older than $days days");
        
        return $deleted_count;
        
    } catch (Exception $e) {
        error_log('Clean SSO Logs Error: ' . $e->getMessage());
        return false;
    }
}

// Fungsi untuk export log ke CSV
function exportSSOLogsToCSV($filename = null) {
    try {
        $pdo = getDatabaseConnection();
        
        $sql = "SELECT username, nama, provinsi, kabupaten, waktu_login FROM logging ORDER BY waktu_login DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $logs = $stmt->fetchAll();
        
        if (empty($logs)) {
            return false;
        }
        
        $filename = $filename ?: 'sso_logs_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = __DIR__ . '/exports/' . $filename;
        
        // Buat direktori exports jika belum ada
        if (!is_dir(__DIR__ . '/exports')) {
            mkdir(__DIR__ . '/exports', 0755, true);
        }
        
        $file = fopen($filepath, 'w');
        
        // Header CSV
        fputcsv($file, ['Username', 'Nama', 'Provinsi', 'Kabupaten', 'Waktu Login']);
        
        // Data CSV
        foreach ($logs as $log) {
            fputcsv($file, [
                $log['username'],
                $log['nama'],
                $log['provinsi'],
                $log['kabupaten'],
                $log['waktu_login']
            ]);
        }
        
        fclose($file);
        
        error_log("SSO logs exported to: $filepath");
        return $filepath;
        
    } catch (Exception $e) {
        error_log('Export SSO Logs Error: ' . $e->getMessage());
        return false;
    }
}
?>
