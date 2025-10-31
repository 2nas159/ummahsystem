<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

if(isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    // بدء المعاملة
    $conn->begin_transaction();
    
    try {
        // حذف الدفعات المرتبطة أولاً
        $query_payments = "DELETE FROM payments WHERE beneficiary_id = ?";
        $stmt = $conn->prepare($query_payments);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // ثم حذف المستفيد
        $query_beneficiary = "DELETE FROM beneficiaries WHERE id = ?";
        $stmt = $conn->prepare($query_beneficiary);
        $stmt->bind_param('i', $id);
        
        if($stmt->execute()) {
            $conn->commit();
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("فشل في حذف المستفيد");
        }
    } catch(Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المستفيد غير موجود'
    ]);
}
?>
