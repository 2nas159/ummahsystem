<?php
session_start();
include("reports_db.php");
include("../includes/csrf_helper.php");

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صالحة'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'رمز التحقق غير صالح'], JSON_UNESCAPED_UNICODE);
    exit;
}

$template_name = trim($_POST['template_name'] ?? '');
$template_type = $_POST['template_type'] ?? 'custom';
$template_data = $_POST['template_data'] ?? '';
$user_id = getCurrentUserId();

if (empty($template_name) || empty($template_data)) {
    echo json_encode(['success' => false, 'message' => 'اسم القالب والبيانات مطلوبة'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO report_templates (template_name, template_type, template_data, created_by)
        VALUES (:template_name, :template_type, :template_data, :created_by)
    ");
    $stmt->execute([
        ':template_name' => $template_name,
        ':template_type' => $template_type,
        ':template_data' => $template_data,
        ':created_by' => $user_id
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'تم حفظ القالب بنجاح',
        'template_id' => $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
