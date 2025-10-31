<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];

    $query = "UPDATE beneficiaries SET 
              name = ?, 
              phone = ?, 
              kimlik_number = ?, 
              iban = ? 
              WHERE id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $name, $phone, $kimlik_number, $iban, $id);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'حدث خطأ أثناء التحديث'
        ]);
    }
}
?>
