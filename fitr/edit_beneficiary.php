<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data from AJAX
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];

    // Update the beneficiary information in the database
    $query = "UPDATE beneficiaries SET name = ?, phone = ?, kimlik_number = ?, iban = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $name, $phone, $kimlik_number, $iban, $id);

    if ($stmt->execute()) {
        $response = ['success' => true];
    } else {
        $response = ['success' => false, 'error' => 'Error updating beneficiary.'];
    }

    // Return the response as JSON
    echo json_encode($response);
}

