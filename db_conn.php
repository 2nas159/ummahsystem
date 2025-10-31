<?php

$host = "localhost";
$dbname = "u850876726_donators_help";
$username = "root";
$password = "";

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
