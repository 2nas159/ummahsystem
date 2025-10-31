<?php
include "donators_db.php"; // Include the correct database connection

// Check if the 'NO' parameter is passed via the URL
if (isset($_GET['NO'])) {
    $NO = $_GET['NO'];

    // Debug: Show received NO (You can remove this line once debugging is complete)
    // echo "NO received: " . $NO;

    // Prepare the DELETE query using prepared statements
    $sql = "DELETE FROM `donators` WHERE NO = ?";
    $stmt = $mysqli->prepare($sql);

    // Bind the parameter to the prepared statement
    $stmt->bind_param("i", $NO); // 'i' specifies that the parameter is an integer

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Redirect with a success message
        header("Location: donators.php?msg=Data deleted successfully");
        exit();
    } else {
        // Output an error message if something goes wrong
        echo "Failed: " . $mysqli->error;
    }

    // Close the statement
    // $stmt->close();
} else {
    // Output an error if 'NO' is not provided
    echo "No ID provided.";
}

