<?php
/**
 * Secure Application Initialization
 * Initialize all security features and error handling
 */

// Start output buffering
ob_start();

// Set timezone
date_default_timezone_set('Asia/Damascus');

// Initialize error handling
require_once __DIR__ . '/classes/ErrorHandler.php';
ErrorHandler::init();

// Initialize logging
require_once __DIR__ . '/classes/Logger.php';

// Start session with secure settings
if (session_status() == PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Set CSP header
$csp = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self';";
header("Content-Security-Policy: $csp");

// Log application start
Logger::info('Application started', [
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
]);

// Clean up old logs periodically
if (rand(1, 100) == 1) { // 1% chance
    Logger::clearOldLogs(30);
}
?>
