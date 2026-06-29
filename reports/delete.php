<?php
session_start();
include("reports_db.php");

header('Content-Type: application/json; charset=utf-8');

// Check authentication
if (!isset($_SESSION["username"])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit;
}

// Check if report_id is provided
if (!isset($_POST['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف التقرير غير موجود']);
    exit;
}

$report_id = (int)$_POST['report_id'];

if ($report_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف التقرير غير صحيح']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Delete report items first (due to foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM report_items WHERE report_id = :id");
    $stmt->execute([':id' => $report_id]);
    
    // Delete report
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = :id");
    $stmt->execute([':id' => $report_id]);
    
    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف التقرير بنجاح'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'التقرير غير موجود'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'خطأ: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
