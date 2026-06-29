<?php
/**
 * Secure Logout Page
 */

require_once __DIR__ . '/../classes/User.php';

// Initialize User class
$userAuth = new User();

// Logout user
$userAuth->logout();

// Redirect to root index.php (which will redirect to login)
header("Location: ../index.php");
exit();
?>
