<?php
session_start();
include("reports_db.php");
$BASE_PATH_PREFIX = '';
$scope = $_GET['scope'] ?? 'current-month';
$message = '';
$targetDescription = '';
$redirectUrl = '';
$params = [];
$query = '';

switch ($scope) {
    case 'previous-month':
        $targetMonth = date('Y-m', strtotime('first day of -1 month'));
        $targetDescription = "للشهر " . $targetMonth;
        $query = "SELECT id FROM reports WHERE DATE_FORMAT(report_month, '%Y-%m') = :month ORDER BY created_at DESC LIMIT 1";
        $params[':month'] = $targetMonth;
        break;
    case 'current-year':
        $targetYear = date('Y');
        $targetDescription = "لسنة $targetYear (تقرير سنوي)";
        $query = "SELECT id FROM reports WHERE report_type = 'annual' AND YEAR(report_month) = :year ORDER BY created_at DESC LIMIT 1";
        $params[':year'] = $targetYear;
        break;
    case 'current-month':
    default:
        $targetMonth = date('Y-m');
        $targetDescription = "للشهر " . $targetMonth;
        $query = "SELECT id FROM reports WHERE DATE_FORMAT(report_month, '%Y-%m') = :month ORDER BY created_at DESC LIMIT 1";
        $params[':month'] = $targetMonth;
        break;
}

if ($query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($report) {
        header("Location: view_report_details.php?id=" . $report['id']);
        exit;
    } else {
        $message = "لا توجد تقارير $targetDescription حالياً.";
    }
} else {
    $message = "نطاق البحث غير معروف.";
}

require_once __DIR__ . '/layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-5">
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
            <h4 class="mb-3 text-center"><?= htmlspecialchars($message) ?></h4>
            <p class="text-muted mb-4 text-center">يمكنك اختيار شهر أو سنة أخرى للبحث أو عرض جميع التقارير.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap mb-4">
                <a href="view_reports.php" class="btn btn-primary">
                    <i class="fas fa-list" style="margin-left: 10px;"></i>عرض جميع التقارير
                </a>
                <a href="reports.php" class="btn btn-outline-secondary">
                    <i class="fas fa-file-alt" style="margin-left: 10px;"></i>إنشاء تقرير جديد
                </a>
            </div>
            <div class="border-top pt-4 mt-4">
                <h5 class="mb-3">البحث عن تقرير معين</h5>
                <form class="row g-3 justify-content-center" action="view_reports.php" method="get">
                    <div class="col-md-4">
                        <label class="form-label">الشهر</label>
                        <input type="month" name="month" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">السنة</label>
                        <input type="number" name="year" class="form-control" min="2000" max="2100" placeholder="YYYY">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search" style="margin-left: 10px;"></i>بحث
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

