<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Delete the beneficiary from the database
    $query = "DELETE FROM beneficiaries WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'error' => 'Error deleting beneficiary.'];
    }

    // Return the response as JSON
    echo json_encode($response);
}

