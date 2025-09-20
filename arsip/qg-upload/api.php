<?php
// api.php - Refactored API with clean structure

require_once 'bootstrap.php';

// Ensure user is logged in for API access
requireLogin();

// Function to execute query and return JSON response
function executeQuery($query, $params = []) {
    global $database;
    
    try {
        $result = $database->queryJson($query, $params);
        return json_encode($result);
    } catch (Exception $e) {
        return json_encode([
            "status" => false, 
            "message" => $e->getMessage()
        ]);
    }
}

// Function to format assessment data to match the old API format
function formatAssessmentResponse($data) {
    if (empty($data)) {
        return ["status" => true, "data" => []];
    }
    
    $assessment = $data[0];
    
    // Convert state to status field to match old API
    if (isset($assessment['state'])) {
        $assessment['status'] = $assessment['state'];
    }
    
    // Process JSON fields
    if (isset($assessment['assessment']) && !is_array($assessment['assessment'])) {
        $assessment['assessment'] = json_decode($assessment['assessment'], true);
    }
    
    if (isset($assessment['notes']) && !is_array($assessment['notes'])) {
        $assessment['notes'] = json_decode($assessment['notes'], true);
    }
    
    return ["status" => true, "data" => $assessment];
}

// Function to get nama daerah from daftar_daerah.csv
function getNamaDaerah($kode) {
    static $daerah_map = null;
    
    // Load CSV file only once
    if ($daerah_map === null) {
        $daerah_map = [];
        $csv_file = 'daftar_daerah.csv';
        
        if (file_exists($csv_file)) {
            $handle = fopen($csv_file, 'r');
            if ($handle) {
                // Skip header
                fgetcsv($handle);
                
                while (($data = fgetcsv($handle)) !== false) {
                    if (count($data) >= 2) {
                        $kode_daerah = $data[0];
                        $nama_daerah = $data[1];
                        $daerah_map[$kode_daerah] = $nama_daerah;
                    }
                }
                fclose($handle);
            }
        }
    }
    
    return isset($daerah_map[$kode]) ? $daerah_map[$kode] : "Wilayah " . $kode;
}

// Main API handler
header('Content-Type: application/json');

try {
    $endpoint = $_GET['endpoint'] ?? '';
    $wilayahFilter = getWilayahSQLFilter();
    
    switch ($endpoint) {
        case 'dashboard':
            $query = "SELECT TOP 10 * FROM qg_submissions WHERE $wilayahFilter ORDER BY created_at DESC";
            echo executeQuery($query);
            break;
            
        case 'monitoring':
            $status = $_GET['status'] ?? '';
            $query = "SELECT * FROM qg_submissions WHERE $wilayahFilter";
            
            if ($status) {
                $query .= " AND status = ?";
                echo executeQuery($query, [$status]);
            } else {
                echo executeQuery($query);
            }
            break;
            
        case 'assessment':
            $id = $_GET['id'] ?? '';
            if (!$id) {
                echo json_encode(["status" => false, "message" => "ID parameter required"]);
                break;
            }
            
            $query = "SELECT * FROM qg_assessments WHERE id = ? AND $wilayahFilter";
            $result = $database->queryJson($query, [$id]);
            
            if ($result['status']) {
                echo json_encode(formatAssessmentResponse($result['data']));
            } else {
                echo json_encode($result);
            }
            break;
            
        case 'stats':
            $query = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                FROM qg_submissions 
                WHERE $wilayahFilter
            ";
            echo executeQuery($query);
            break;
            
        case 'recent_activity':
            $limit = $_GET['limit'] ?? 20;
            $query = "SELECT TOP $limit * FROM qg_activity_log WHERE $wilayahFilter ORDER BY created_at DESC";
            echo executeQuery($query);
            break;
            
        case 'wilayah_summary':
            $query = "
                SELECT 
                    kode_provinsi,
                    kode_kabupaten,
                    COUNT(*) as total_submissions,
                    AVG(CAST(score as float)) as avg_score
                FROM qg_submissions 
                WHERE $wilayahFilter 
                GROUP BY kode_provinsi, kode_kabupaten
                ORDER BY total_submissions DESC
            ";
            echo executeQuery($query);
            break;
            
        case 'user_profile':
            $userData = getUserData();
            echo json_encode([
                "status" => true,
                "data" => [
                    "nama" => $userData['nama'] ?? '',
                    "jabatan" => $userData['jabatan'] ?? '',
                    "unit_kerja" => $userData['unit_kerja'] ?? '',
                    "wilayah" => getWilayahFilter()
                ]
            ]);
            break;
            
        // Add more API endpoints as needed...
            
        default:
            echo json_encode([
                "status" => false, 
                "message" => "Unknown endpoint: $endpoint"
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        "status" => false, 
        "message" => "API Error: " . $e->getMessage()
    ]);
}
?>
