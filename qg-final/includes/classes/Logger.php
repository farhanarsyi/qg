<?php
/**
 * Simple Logger Class
 * Handles application logging
 */

class Logger {
    
    private $context;
    private $logFile;
    
    public function __construct($context = 'APP') {
        $this->context = $context;
        $this->logFile = QG_ROOT_PATH . '/logs/app.log';
        
        // Create logs directory if it doesn't exist
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Log info message
     */
    public function info($message, $data = []) {
        $this->log('INFO', $message, $data);
    }
    
    /**
     * Log warning message
     */
    public function warning($message, $data = []) {
        $this->log('WARNING', $message, $data);
    }
    
    /**
     * Log error message
     */
    public function error($message, $data = []) {
        $this->log('ERROR', $message, $data);
    }
    
    /**
     * Log debug message (only in debug mode)
     */
    public function debug($message, $data = []) {
        if (QG_DEBUG) {
            $this->log('DEBUG', $message, $data);
        }
    }
    
    /**
     * Write log entry
     */
    private function log($level, $message, $data = []) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] [{$level}] [{$this->context}] {$message}";
        
        if (!empty($data)) {
            $logEntry .= ' | Data: ' . json_encode($data);
        }
        
        $logEntry .= "\n";
        
        // Write to file
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Also write to error_log if enabled
        if (QG_DEBUG) {
            error_log($logEntry);
        }
    }
} 