<?php
/**
 * Secure Logging System
 * Handles application logging with different levels
 */

class Logger {
    private static $logDir = 'logs';
    private static $logFile = 'application.log';
    private static $maxFileSize = 10485760; // 10MB
    
    /**
     * Log message with specified level
     */
    public static function log($level, $message, $context = []) {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
        
        $logFile = self::$logDir . '/' . self::$logFile;
        
        // Rotate log file if it's too large
        if (file_exists($logFile) && filesize($logFile) > self::$maxFileSize) {
            self::rotateLog();
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logEntry = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log('INFO', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::log('DEBUG', $message, $context);
    }
    
    /**
     * Log security events
     */
    public static function security($message, $context = []) {
        self::log('SECURITY', $message, $context);
    }
    
    /**
     * Rotate log file
     */
    private static function rotateLog() {
        $logFile = self::$logDir . '/' . self::$logFile;
        $backupFile = $logFile . '.' . date('Y-m-d-H-i-s');
        
        if (file_exists($logFile)) {
            rename($logFile, $backupFile);
        }
    }
    
    /**
     * Get recent log entries
     */
    public static function getRecentLogs($lines = 100) {
        $logFile = self::$logDir . '/' . self::$logFile;
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $file = new SplFileObject($logFile);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        $logs = [];
        while (!$file->eof()) {
            $line = $file->fgets();
            if (!empty(trim($line))) {
                $logs[] = $line;
            }
        }
        
        return $logs;
    }
    
    /**
     * Clear old logs
     */
    public static function clearOldLogs($days = 30) {
        $logDir = self::$logDir;
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        
        if (is_dir($logDir)) {
            $files = glob($logDir . '/*.log*');
            foreach ($files as $file) {
                if (filemtime($file) < $cutoffTime) {
                    unlink($file);
                }
            }
        }
    }
}
