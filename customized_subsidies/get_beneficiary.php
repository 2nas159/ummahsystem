<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $query = "SELECT * FROM beneficiaries WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $beneficiary = $result->fetch_assoc();
        
        if($beneficiary) {
            echo json_encode([
                'success' => true,
                'data' => $beneficiary
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'المستفيد غير موجود'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'خطأ في تنفيذ الاستعلام'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المستفيد غير موجود'
    ]);
}
