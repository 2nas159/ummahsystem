<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

if(isset($_POST['payment_id'])) {
    $payment_id = (int)$_POST['payment_id'];
    
    try {
        // حذف الدفعة فقط من جدول payments
        $query_delete = "DELETE FROM payments WHERE id = ?";
        $stmt = $conn->prepare($query_delete);
        $stmt->bind_param('i', $payment_id);
        
        if($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'تم حذف الدفعة بنجاح'
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("فشل في حذف الدفعة");
        }
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الدفعة غير موجود'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>

