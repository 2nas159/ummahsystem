<?php
session_start();
include("reports_db.php");

$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($report_id <= 0) {
    header("Location: view_reports.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = :id");
$stmt->execute([':id' => $report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    $_SESSION['error_message'] = 'التقرير غير موجود';
    header("Location: view_reports.php");
    exit;
}

$items_stmt = $pdo->prepare("SELECT * FROM report_items WHERE report_id = :id ORDER BY item_order ASC");
$items_stmt->execute([':id' => $report_id]);
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

$status_labels = [
    'draft' => 'مسودة',
    'submitted' => 'مقدم',
    'approved' => 'موافق عليه',
    'rejected' => 'مرفوض'
];

$type_labels = [
    'monthly' => 'شهري',
    'quarterly' => 'ربع سنوي',
    'annual' => 'سنوي',
    'custom' => 'مخصص'
];

$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($report['report_name']) ?></h2>
            <p class="text-muted mb-0">
                <?= $type_labels[$report['report_type']] ?? $report['report_type'] ?> |
                <?= date('Y-m', strtotime($report['report_month'])) ?>
            </p>
        </div>
        <div class="btn-group">
            <a href="edit_report.php?id=<?= $report_id ?>" class="btn btn-primary" style="border-radius: 0 !important; margin-left: 10px;">
                <i class="fas fa-edit" style="margin-left: 10px;"></i>تعديل التقرير
            </a>
            <a href="view_reports.php" class="btn btn-outline-secondary" style="border-radius: 0 !important;">
                <i class="fas fa-arrow-left" style="margin-left: 10px;"></i>عودة للتقارير
            </a>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <h6 class="text-muted">الحالة</h6>
                    <p><?= $status_labels[$report['status']] ?? $report['status'] ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">تاريخ الإنشاء</h6>
                    <p><?= date('Y-m-d H:i', strtotime($report['created_at'])) ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">آخر تحديث</h6>
                    <p><?= date('Y-m-d H:i', strtotime($report['updated_at'])) ?></p>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted">إجمالي الأهداف</h6>
                    <p><?= count($items) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الهدف</th>
                            <th>المهام</th>
                            <th>المستفيدين</th>
                            <th>المبلغ</th>
                            <th>نسبة الإكمال</th>
                            <th>الإيجابيات والسلبيات</th>
                            <th>التقييم</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">لا توجد أهداف لهذا التقرير</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $index => $item): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['goal'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['tasks'])) ?></td>
                                    <td><?= number_format($item['number_of_individuals']) ?></td>
                                    <td><?= number_format($item['amount'], 2) ?> ₺</td>
                                    <td>
                                        <span class="badge bg-<?= $item['completion_percentage'] >= 100 ? 'success' : ($item['completion_percentage'] >= 50 ? 'warning' : 'danger') ?>">
                                            <?= number_format($item['completion_percentage'], 1) ?>%
                                        </span>
                                    </td>
                                    <td><?= nl2br(htmlspecialchars($item['negatives_and_obstacles'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['evaluation'])) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['notes_and_recommendations'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>