<?php
/**
 * Secure Donators Database Connection
 */

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Security.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get database configuration
$config = require __DIR__ . '/../config/database.php';

// Create database instance for donators database
$donatorsConfig = $config;
$donatorsConfig['database'] = 'u850876726_donators_help';

$mysqli = Database::getInstance($donatorsConfig);
$mysqli->selectDatabase('u850876726_donators_help');

// Legacy compatibility function
if (!function_exists('getCount_help')) {
    function getCount_help($tableName) {
        global $mysqli;
        try {
            $sql = "SELECT COUNT(*) as count FROM `$tableName`";
            $result = $mysqli->fetchOne($sql);
            return $result['count'];
        } catch (Exception $e) {
            error_log("Count query error: " . $e->getMessage());
            return 0;
        }
    }
}

// Check connection
if (!$mysqli) {
    die("Connection error: Unable to connect to database");
}

return $mysqli;
?>
