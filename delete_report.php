<?php
include "reports_db.php";

// Check if the file name is provided via POST
if (isset($_POST['file_name'])) {
    $fileName = trim($_POST['file_name']);
    
    // Prepare and execute the delete query
    $stmt = $pdo->prepare("DELETE FROM operation_plan_report WHERE file_name = :fileName");
    $stmt->execute([':fileName' => $fileName]);
    
    // Check if the query affected any rows (i.e., if the deletion was successful)
    if ($stmt->rowCount() > 0) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'file_name_not_provided';
}
