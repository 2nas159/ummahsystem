<?php
include('db_connection.php');
header('Content-Type: application/json; charset=utf-8');

try {
if(empty($_POST['beneficiary_id']) || empty($_POST['amount']) || empty($_POST['payment_type'])) {
        throw new Exception('جميع الحقول مطلوبة');
    }

    $beneficiary_id = intval($_POST['beneficiary_id']);
$amount = floatval($_POST['amount']);
if ($amount <= 0) {
    throw new Exception('المبلغ يجب أن يكون أكبر من صفر');
}

$payment_type = $_POST['payment_type'];
$valid_types = [
    'cash' => 'نقدي',
    'bank' => 'تحويل بنكي',
    'in-kind' => 'تحويل عيني'
];

$legacy_types = [
    'نقدي' => 'cash',
    'تحويل بنكي' => 'bank',
    'تحويل عيني' => 'in-kind'
];

if (!array_key_exists($payment_type, $valid_types)) {
    if (isset($legacy_types[$payment_type])) {
        $payment_type = $legacy_types[$payment_type];
    } else {
        throw new Exception('نوع الدفع غير صالح');
    }
}

// تحديد التاريخ بناءً على السنة والشهر المحددين (إن وجد)
$year = isset($_POST['year']) ? intval($_POST['year']) : null;
$month = isset($_POST['month']) ? intval($_POST['month']) : null;

if ($year && $month && $month >= 1 && $month <= 12) {
    $payment_date = date('Y-m-d', strtotime(sprintf('%04d-%02d-01', $year, $month)));
} else {
    $payment_date = date('Y-m-d');
}

    $query = "INSERT INTO payments (beneficiary_id, amount, payment_type, payment_date) 
              VALUES (?, ?, ?, ?)";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('idss', $beneficiary_id, $amount, $payment_type, $payment_date);
    
    if($stmt->execute()) {
        $payment_type_text = $valid_types[$payment_type];
        
        $response = [
            'status' => 'success',
            'message' => 'تم تسجيل الدفع بنجاح',
            'amount' => number_format($amount, 2),
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


