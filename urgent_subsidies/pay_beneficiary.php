<?php
include('db_connection.php');

$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $beneficiary_id = $_POST['beneficiary_id'];
    $payment_type = $_POST['payment_type'];
    $amount = $_POST['amount'];  // إضافة المبلغ
    
    // إدخال سجل الدفع
    $query_payment = "INSERT INTO payments (beneficiary_id, payment_type, amount, payment_date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($query_payment);
    $stmt->bind_param('isd', $beneficiary_id, $payment_type, $amount);  // 'i' للمبلغ الذي هو رقم عشري
    $stmt->execute();
    
    // إعادة توجيه إلى صفحة التفاصيل
    header("Location: month_details.php?year={$_GET['year']}&month={$_GET['month']}");
    exit();
}
?>
