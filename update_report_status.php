<?php
session_start();
include("reports_db.php");
include("includes/csrf_helper.php");

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صالحة'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'رمز التحقق غير صالح'], JSON_UNESCAPED_UNICODE);
    exit;
}

$report_id = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
$new_status = $_POST['status'] ?? '';

$allowed_statuses = ['draft', 'submitted', 'approved', 'rejected'];

if ($report_id <= 0 || !in_array($new_status, $allowed_statuses, true)) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة'], JSON_UNESCAPED_UNICODE);
    exit;
}

$approved_by = ($new_status === 'approved') ? getCurrentUserId() : null;
$approved_at = ($new_status === 'approved') ? date('Y-m-d H:i:s') : null;

try {
    $stmt = $pdo->prepare("
        UPDATE reports 
        SET status = :status,
            approved_by = :approved_by,
            approved_at = :approved_at
        WHERE id = :id
    ");
    $stmt->execute([
        ':status' => $new_status,
        ':approved_by' => $approved_by,
        ':approved_at' => $approved_at,
        ':id' => $report_id
    ]);

    echo json_encode(['success' => true, 'message' => 'تم تحديث حالة التقرير'], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

