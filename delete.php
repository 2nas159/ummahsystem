<?php
include "db_conn.php"; // Include the correct database connection

// Check if the 'NO' parameter is passed via the URL
if (isset($_GET['NO'])) {
    $NO = intval($_GET['NO']);

    // Prepare the DELETE query using prepared statements
    $sql = "DELETE FROM `tablename` WHERE NO = ?";
    $stmt = $mysqli->prepare($sql);

    // Bind the parameter to the prepared statement
    $stmt->bind_param("i", $NO); // 'i' specifies that the parameter is an integer

    // Execute the prepared statement
    if ($stmt->execute()) {
        // After deletion, renumber all remaining records sequentially
        // Step 1: Get all records ordered by current NO and store their IDs
        $getAllSql = "SELECT NO FROM `tablename` ORDER BY NO ASC";
        $allResult = $mysqli->query($getAllSql);
        
        if ($allResult && $allResult->num_rows > 0) {
            // Store all current NO values in an array
            $oldNumbers = [];
            while ($row = $allResult->fetch_assoc()) {
                $oldNumbers[] = intval($row['NO']);
            }
            
            // Step 2: Set all NO values to negative (temporary) to avoid conflicts
            $tempSql = "UPDATE `tablename` SET NO = -NO";
            $mysqli->query($tempSql);
            
            // Step 3: Renumber sequentially starting from 1
            $newNo = 1;
            foreach ($oldNumbers as $oldNo) {
                $updateSql = "UPDATE `tablename` SET NO = ? WHERE NO = ?";
                $updateStmt = $mysqli->prepare($updateSql);
                $tempNo = -$oldNo; // Update using negative value
                $updateStmt->bind_param("ii", $newNo, $tempNo);
                $updateStmt->execute();
                $updateStmt->close();
                $newNo++;
            }
        }
        
        // Redirect with a success message
        header("Location: help.php?msg=تم حذف البيانات وإعادة ترقيم السجلات بنجاح");
        exit();
    } else {
        // Output an error message if something goes wrong
        echo "Failed: " . $mysqli->error;
    }

    // Close the statement
    $stmt->close();
} else {
    // Output an error if 'NO' is not provided
    echo "No ID provided.";
}
