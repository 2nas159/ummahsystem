<?php
include "db_conn.php"; // Database connection

if (isset($_POST['NO']) && isset($_POST['ADI']) && isset($_POST['TC']) && isset($_POST['TELEFON']) && isset($_POST['Adres']) && isset($_POST['UYRUK'])) {
    $no = $_POST['NO'];
    $name = $_POST['ADI'];
    $tc = $_POST['TC'];
    $phone = $_POST['TELEFON'];
    $address = $_POST['Adres'];
    $nationality = $_POST['UYRUK'];

    // SQL query to update the donor's details
    $sql = "UPDATE `tablename` SET ADI=?, TC=?, TELEFON=?, Adres=?, UYRUK=? WHERE NO=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssi", $name, $tc, $phone, $address, $nationality, $no);

    if ($stmt->execute()) {
        echo "success"; // Response sent back to the frontend on success
    } else {
        echo "error"; // Response sent back to the frontend on failure
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo "error";
}
