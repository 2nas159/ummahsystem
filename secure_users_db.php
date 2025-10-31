<?php
/**
 * Secure User Authentication
 * Replaces users_db.php with secure implementation
 */

require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Security.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$error = "";
$isim = "";
$profile_image = "";

// Initialize User class
$userAuth = new User();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "رمز الأمان غير صحيح. يرجى المحاولة مرة أخرى.";
    } else {
        $username = $_POST["username"] ?? '';
        $password = $_POST["password"] ?? '';
        
        // Sanitize inputs
        $username = Security::sanitizeInput($username);
        
        // Authenticate user
        $result = $userAuth->authenticate($username, $password);
        
        if ($result['success']) {
            // Redirect to appropriate page
            header("Location: " . $result['redirect']);
            exit();
        } else {
            $error = $result['message'];
        }
    }
}

// Generate CSRF token for the form
$csrf_token = Security::generateCSRFToken();
?>
