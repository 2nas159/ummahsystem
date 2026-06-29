<?php
session_start();
include("reports_db.php");

header('Content-Type: application/json; charset=utf-8');

$report_ids = $_GET['report_ids'] ?? [];

if (!is_array($report_ids) || count($report_ids) < 2) {
    echo json_encode(['success' => false, 'message' => 'يرجى اختيار تقريرين على الأقل'], JSON_UNESCAPED_UNICODE);
    exit;
}

$report_ids = array_map('intval', $report_ids);
$placeholders = implode(',', array_fill(0, count($report_ids), '?'));

try {
    // Fetch reports
    $stmt = $pdo->prepare("
        SELECT r.*, 
               (SELECT SUM(amount) FROM report_items WHERE report_id = r.id) as total_amount,
               (SELECT SUM(number_of_individuals) FROM report_items WHERE report_id = r.id) as total_individuals
        FROM reports r
        WHERE r.id IN ($placeholders)
    ");
    $stmt->execute($report_ids);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($reports) < 2) {
        echo json_encode(['success' => false, 'message' => 'لا يمكن العثور على التقارير المحددة'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Fetch items for each report
    $items_stmt = $pdo->prepare("
        SELECT * FROM report_items
        WHERE report_id IN ($placeholders)
        ORDER BY report_id, item_order
    ");
    $items_stmt->execute($report_ids);
    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group items by report
    $items_by_report = [];
    foreach ($items as $item) {
        $items_by_report[$item['report_id']][] = $item;
    }

    echo json_encode([
        'success' => true,
        'reports' => $reports,
        'items' => $items_by_report
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
