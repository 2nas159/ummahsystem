<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

try {
    if(empty($_POST['beneficiary_id']) || empty($_POST['amount']) || empty($_POST['payment_type'])) {
        throw new Exception('جميع الحقول مطلوبة');
    }

    $beneficiary_id = intval($_POST['beneficiary_id']);
    $amount = floatval($_POST['amount']);
    $payment_type = $_POST['payment_type'];
    $payment_date = date('Y-m-d');

    $query = "INSERT INTO payments (beneficiary_id, amount, payment_type, payment_date) 
              VALUES (?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('idss', $beneficiary_id, $amount, $payment_type, $payment_date);
    
    if($stmt->execute()) {
        $payment_type_text = $payment_type === 'cash' ? 'نقدي' : 'تحويل بنكي';
        
        $response = [
            'status' => 'success',
            'message' => 'تم تسجيل الدفع بنجاح',
            'amount' => $amount,
            'payment_type' => $payment_type_text
        ];
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('فشل في تسجيل الدفع');
    }

} catch(Exception $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>


