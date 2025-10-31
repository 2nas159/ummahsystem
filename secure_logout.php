<?php
/**
 * Secure Logout
 * Replaces logout.php with secure implementation
 */

require_once __DIR__ . '/classes/User.php';

// Initialize User class
$userAuth = new User();

// Logout user
$userAuth->logout();

// Redirect to login page
header("Location: index.php");
exit();
?>
