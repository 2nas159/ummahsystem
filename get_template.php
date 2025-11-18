<?php
session_start();
include("reports_db.php");

header('Content-Type: application/json; charset=utf-8');

$template_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($template_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'معرف القالب غير صالح'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM report_templates WHERE id = :id");
$stmt->execute([':id' => $template_id]);
$template = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$template) {
    echo json_encode(['success' => false, 'message' => 'القالب غير موجود'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'template' => $template
], JSON_UNESCAPED_UNICODE);

