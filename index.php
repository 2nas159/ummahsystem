<?php
/**
 * Root Router index.php
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['username'])) {
    switch ($_SESSION['usertype']) {
        case 'admin':
            header("Location: home_admin.php");
            exit();
        case 'user':
            header("Location: home_hr.php");
            exit();
        case 'manager':
            header("Location: home_maneger.php");
            exit();
        default:
            header("Location: auth/login.php");
            exit();
    }
} else {
    header("Location: auth/login.php");
    exit();
}
?>