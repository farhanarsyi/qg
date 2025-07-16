<?php
// classes/Database.php - Database Connection and Query Manager

class Database {
    private $connection;
    private $config;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/database.php';
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }
    
    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $connectionOptions = [
                "Database" => $this->config['database'],
                "Uid" => $this->config['username'],
                "PWD" => $this->config['password']
            ];
            
            $this->connection = sqlsrv_connect($this->config['server'], $connectionOptions);
            
            if ($this->connection === false) {
                throw new Exception('Database connection failed');
            }
        } catch (Exception $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Execute query and return results
     */
    public function query($sql, $params = []) {
        $conn = $this->getConnection();
        
        if ($conn === null) {
            throw new Exception('No database connection available');
        }
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            $errorMsg = isset($errors[0]['message']) ? $errors[0]['message'] : "Query execution failed";
            throw new Exception($errorMsg);
        }
        
        $result = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Convert DateTime objects to string format
            foreach ($row as $key => $value) {
                if ($value instanceof DateTime) {
                    $row[$key] = $value->format('Y-m-d H:i:s');
                }
            }
            $result[] = $row;
        }
        
        sqlsrv_free_stmt($stmt);
        return $result;
    }
    
    /**
     * Execute query and return JSON response
     */
    public function queryJson($sql, $params = []) {
        try {
            $result = $this->query($sql, $params);
            return [
                'status' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->connection) {
            sqlsrv_close($this->connection);
            $this->connection = null;
        }
    }
    
    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct() {
        $this->close();
    }
} 