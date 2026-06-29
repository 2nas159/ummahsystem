<?php
/**
 * Database connection for General Beneficiaries module
 */

$config = require __DIR__ . '/../config/database.php';

$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DONATORS_DB_NAME'] ?? 'u850876726_donators_help';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

// Create a new MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

function getCount_help ($tableName) {
    global $mysqli;
    $table = $tableName;
    $query = "SELECT * FROM $table";
    $result = mysqli_query($mysqli, $query);
    $totalCount = mysqli_num_rows($result);
    return $totalCount;
};

// Check if there was a connection error
if ($mysqli->connect_errno) {
    die("Connection error: " . $mysqli->connect_error);
}

return $mysqli;
?>
