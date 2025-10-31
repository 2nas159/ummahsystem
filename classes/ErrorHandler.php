<?php
/**
 * Error Handler Class
 * Handles application errors and exceptions
 */

require_once __DIR__ . '/Logger.php';

class ErrorHandler {
    
    /**
     * Initialize error handling
     */
    public static function init() {
        // Set error reporting
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        
        // Set custom exception handler
        set_exception_handler([self::class, 'handleException']);
        
        // Set shutdown handler
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    /**
     * Handle PHP errors
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorType = self::getErrorType($severity);
        $errorMessage = "$errorType: $message in $file on line $line";
        
        Logger::error($errorMessage, [
            'severity' => $severity,
            'file' => $file,
            'line' => $line
        ]);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception) {
        $errorMessage = "Uncaught exception: " . $exception->getMessage();
        $errorMessage .= " in " . $exception->getFile() . " on line " . $exception->getLine();
        
        Logger::error($errorMessage, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Show user-friendly error page
        self::showErrorPage('حدث خطأ في النظام', 'يرجى المحاولة لاحقاً');
    }
    
    /**
     * Handle fatal errors
     */
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $errorMessage = "Fatal error: " . $error['message'];
            $errorMessage .= " in " . $error['file'] . " on line " . $error['line'];
            
            Logger::error($errorMessage, [
                'type' => $error['type'],
                'file' => $error['file'],
                'line' => $error['line']
            ]);
            
            self::showErrorPage('خطأ في النظام', 'حدث خطأ خطير في النظام');
        }
    }
    
    /**
     * Get error type from severity
     */
    private static function getErrorType($severity) {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $errorTypes[$severity] ?? 'Unknown Error';
    }
    
    /**
     * Show error page to user
     */
    private static function showErrorPage($title, $message) {
        http_response_code(500);
        
        echo '<!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>خطأ في النظام</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f8f9fa; }
                .error-container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-title { color: #dc3545; font-size: 24px; margin-bottom: 20px; }
                .error-message { color: #6c757d; font-size: 16px; margin-bottom: 30px; }
                .btn { background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .btn:hover { background-color: #0056b3; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1 class="error-title">' . htmlspecialchars($title) . '</h1>
                <p class="error-message">' . htmlspecialchars($message) . '</p>
                <a href="index.php" class="btn">العودة للصفحة الرئيسية</a>
            </div>
        </body>
        </html>';
        
        exit();
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = []) {
        Logger::security($event, $details);
    }
    
    /**
     * Log user actions
     */
    public static function logUserAction($action, $userId = null, $details = []) {
        $context = array_merge($details, [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        Logger::info("User action: $action", $context);
    }
}
