<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

try {
    if(empty($_POST['id'])) {
        throw new Exception('معرف المستفيد مطلوب');
    }

    $id = intval($_POST['id']);
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $kimlik_number = $_POST['kimlik_number'] ?? '';
    $iban = $_POST['iban'] ?? '';

    // تحديث البيانات الأساسية فقط
    $query = "UPDATE beneficiaries SET 
              name = ?, 
              phone = ?, 
              kimlik_number = ?, 
              iban = ? 
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssi', $name, $phone, $kimlik_number, $iban, $id);
    
    if(!$stmt->execute()) {
        throw new Exception('فشل في تحديث البيانات');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'تم التحديث بنجاح'
    ], JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();

