<?php
include "db_conn.php"; // Database connection

if (isset($_POST['NO']) && isset($_POST['ADI']) && isset($_POST['TC']) && isset($_POST['TELEFON']) && isset($_POST['Adres']) && isset($_POST['UYRUK'])) {
    $no = intval($_POST['NO']);
    $name = trim($_POST['ADI']);
    $tc = trim($_POST['TC']);
    $phone = trim($_POST['TELEFON']);
    $address = trim($_POST['Adres']);
    $nationality = trim($_POST['UYRUK']);

    // SQL query to update the record
    $sql = "UPDATE `tablename` SET ADI=?, TC=?, TELEFON=?, Adres=?, UYRUK=? WHERE NO=?";
    $stmt = $mysqli->prepare($sql);
    
    if ($stmt) {
        // All fields as strings except NO which is integer
        $stmt->bind_param("sssssi", $name, $tc, $phone, $address, $nationality, $no);

        if ($stmt->execute()) {
            echo "success"; // Response sent back to the frontend on success
        } else {
            echo "error: " . $stmt->error; // Response with error message
        }

        $stmt->close();
    } else {
        echo "error: " . $mysqli->error;
    }
    $mysqli->close();
} else {
    echo "error: Missing required fields";
}
