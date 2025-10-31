<?php
header('Content-Type: application/json');

// ربط قاعدة البيانات
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // افتراض أن المدفوعات ترسل كـ POST
    $payments = $_POST['payments'];

    // تنفيذ الاستعلام أو حفظ البيانات
    foreach ($payments as $beneficiary_id => $payment_data) {
        $amount = $payment_data['amount'];
        $payment_type = $payment_data['payment_type'];

        // تحقق من البيانات أو قم بتنفيذ الاستعلام
        $stmt = $conn->prepare("INSERT INTO payments (beneficiary_id, amount, payment_type, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("ids", $beneficiary_id, $amount, $payment_type);
        $stmt->execute();
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

