<?php
include("reports_db.php");

header('Content-Type: text/html; charset=utf-8');

if (!isset($_GET['report_id'])) {
    echo '<div class="alert alert-danger">معرف التقرير غير موجود</div>';
    exit;
}

$report_id = (int)$_GET['report_id'];

// Fetch report metadata
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = :id");
$stmt->execute([':id' => $report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    echo '<div class="alert alert-danger">التقرير غير موجود</div>';
    exit;
}

// Fetch report items
$stmt = $pdo->prepare("SELECT * FROM report_items WHERE report_id = :id ORDER BY item_order ASC");
$stmt->execute([':id' => $report_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Status badges
$status_badges = [
    'draft' => '<span class="badge bg-warning">مسودة</span>',
    'submitted' => '<span class="badge bg-info">مقدم</span>',
    'approved' => '<span class="badge bg-success">موافق عليه</span>',
    'rejected' => '<span class="badge bg-danger">مرفوض</span>'
];

// Report types
$types = [
    'monthly' => 'شهري',
    'quarterly' => 'ربع سنوي',
    'annual' => 'سنوي',
    'custom' => 'مخصص'
];
?>

<div class="report-details">
    <!-- Report Header Info -->
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">معلومات التقرير</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>اسم التقرير:</strong><br>
                    <?= htmlspecialchars($report['report_name']) ?>
                </div>
                <div class="col-md-2">
                    <strong>النوع:</strong><br>
                    <?= $types[$report['report_type']] ?? $report['report_type'] ?>
                </div>
                <div class="col-md-2">
                    <strong>الشهر:</strong><br>
                    <?= date('Y-m', strtotime($report['report_month'])) ?>
                </div>
                <div class="col-md-2">
                    <strong>الحالة:</strong><br>
                    <?= $status_badges[$report['status']] ?? $report['status'] ?>
                </div>
                <div class="col-md-3">
                    <strong>تاريخ الإنشاء:</strong><br>
                    <?= date('Y-m-d H:i', strtotime($report['created_at'])) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Items Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الهدف</th>
                    <th>المهام</th>
                    <th>عدد المستفيدين</th>
                    <th>المبلغ (₺)</th>
                    <th>نسبة الإكمال (%)</th>
                    <th>الإيجابيات والسلبيات</th>
                    <th>التقييم</th>
                    <th>ملاحظات واقتراحات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">لا توجد أهداف في هذا التقرير</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $total_amount = 0;
                    $total_individuals = 0;
                    foreach ($items as $index => $item): 
                        $total_amount += (float)$item['amount'];
                        $total_individuals += (int)$item['number_of_individuals'];
                    ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= nl2br(htmlspecialchars($item['goal'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($item['tasks'])) ?></td>
                            <td class="text-center"><?= number_format($item['number_of_individuals']) ?></td>
                            <td class="text-center"><?= number_format($item['amount'], 2) ?> ₺</td>
                            <td class="text-center">
                                <span class="badge bg-<?= $item['completion_percentage'] >= 100 ? 'success' : ($item['completion_percentage'] >= 50 ? 'warning' : 'danger') ?>">
                                    <?= number_format($item['completion_percentage'], 1) ?>%
                                </span>
                            </td>
                            <td><?= nl2br(htmlspecialchars($item['negatives_and_obstacles'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($item['evaluation'])) ?></td>
                            <td><?= nl2br(htmlspecialchars($item['notes_and_recommendations'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-info fw-bold">
                        <td colspan="3" class="text-end">الإجمالي:</td>
                        <td class="text-center"><?= number_format($total_individuals) ?></td>
                        <td class="text-center"><?= number_format($total_amount, 2) ?> ₺</td>
                        <td colspan="4"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.report-details {
    padding: 1rem;
}

.report-details table {
    font-size: 0.9rem;
}

.report-details table td {
    vertical-align: top;
    word-wrap: break-word;
    max-width: 200px;
}

.report-details table th {
    white-space: nowrap;
    background-color: #f8f9fa;
}
</style>
