<?php
include "donators_db.php"; // Include the correct database connection

if (isset($_POST['NO']) && isset($_POST['ADI']) && isset($_POST['TEL'])) {
    $NO = $_POST['NO'];
    $ADI = $_POST['ADI'];
    $TEL = $_POST['TEL'];

    // Prepare the SQL update query
    $sql = "UPDATE `donators` SET ADI = ?, TEL = ? WHERE NO = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ssi", $ADI, $TEL, $NO);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $mysqli->close();
} else {
    echo 'error';
}
