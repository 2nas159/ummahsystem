<?php
session_start();
include("reports_db.php");

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$month_filter = $_GET['month'] ?? '';

// Build query
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "r.report_name LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($status_filter)) {
    $where_conditions[] = "r.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($type_filter)) {
    $where_conditions[] = "r.report_type = :type";
    $params[':type'] = $type_filter;
}

if (!empty($month_filter)) {
    $where_conditions[] = "DATE_FORMAT(r.report_month, '%Y-%m') = :month";
    $params[':month'] = $month_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total count
$count_sql = "SELECT COUNT(DISTINCT r.id) as total FROM reports r $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_reports = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_reports / $per_page);

// Fetch reports with pagination
$sql = "SELECT r.*, 
        (SELECT COUNT(*) FROM report_items WHERE report_id = r.id) as items_count,
        (SELECT SUM(amount) FROM report_items WHERE report_id = r.id) as total_amount
        FROM reports r 
        $where_clause 
        ORDER BY r.created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4 page-section">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="page-title m-0"><i class="fas fa-file-alt me-2"></i>التقارير المتاحة</h2>
        <a href="reports.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>تقرير جديد
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">البحث</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="ابحث عن تقرير..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>مسودة</option>
                        <option value="submitted" <?= $status_filter === 'submitted' ? 'selected' : '' ?>>مقدم</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>موافق عليه</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">النوع</label>
                    <select name="type" class="form-select">
                        <option value="">الكل</option>
                        <option value="monthly" <?= $type_filter === 'monthly' ? 'selected' : '' ?>>شهري</option>
                        <option value="quarterly" <?= $type_filter === 'quarterly' ? 'selected' : '' ?>>ربع سنوي</option>
                        <option value="annual" <?= $type_filter === 'annual' ? 'selected' : '' ?>>سنوي</option>
                        <option value="custom" <?= $type_filter === 'custom' ? 'selected' : '' ?>>مخصص</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الشهر</label>
                    <input type="month" name="month" class="form-control" value="<?= htmlspecialchars($month_filter) ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                    <a href="view_reports.php" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>إعادة تعيين
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">إجمالي التقارير</h5>
                    <h3 class="text-primary mb-0"><?= $total_reports ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">مسودات</h5>
                    <h3 class="text-warning mb-0">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'draft'");
                        echo $stmt->fetchColumn();
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">موافق عليها</h5>
                    <h3 class="text-success mb-0">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'approved'");
                        echo $stmt->fetchColumn();
                        ?>
                    </h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">مقدمة</h5>
                    <h3 class="text-info mb-0">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'submitted'");
                        echo $stmt->fetchColumn();
                        ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card card-elevated shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-modern m-0">
                    <thead class="table-light">
                        <tr>
                            <th>اسم التقرير</th>
                            <th>النوع</th>
                            <th>الشهر</th>
                            <th>الحالة</th>
                            <th>عدد الأهداف</th>
                            <th>المبلغ الإجمالي</th>
                            <th>تاريخ الإنشاء</th>
                            <th class="text-nowrap">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد تقارير متاحة</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($report['report_name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php
                                        $types = [
                                            'monthly' => 'شهري',
                                            'quarterly' => 'ربع سنوي',
                                            'annual' => 'سنوي',
                                            'custom' => 'مخصص'
                                        ];
                                        echo $types[$report['report_type']] ?? $report['report_type'];
                                        ?>
                                    </td>
                                    <td><?= date('Y-m', strtotime($report['report_month'])) ?></td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'draft' => '<span class="badge bg-warning">مسودة</span>',
                                            'submitted' => '<span class="badge bg-info">مقدم</span>',
                                            'approved' => '<span class="badge bg-success">موافق عليه</span>',
                                            'rejected' => '<span class="badge bg-danger">مرفوض</span>'
                                        ];
                                        echo $status_badges[$report['status']] ?? $report['status'];
                                        ?>
                                    </td>
                                    <td><?= $report['items_count'] ?? 0 ?></td>
                                    <td><?= number_format($report['total_amount'] ?? 0, 2) ?> ₺</td>
                                    <td><?= date('Y-m-d', strtotime($report['created_at'])) ?></td>
                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="toggleDetails(<?= $report['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="edit_report.php?id=<?= $report['id'] ?>" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteReport(<?= $report['id'] ?>, '<?= htmlspecialchars($report['report_name'], ENT_QUOTES) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Details Row -->
                                <tr id="details-<?= $report['id'] ?>" style="display: none;">
                                    <td colspan="8">
                                        <div class="p-3" id="report-content-<?= $report['id'] ?>">
                                            <div class="text-center">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">جاري التحميل...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>">السابق</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>">التالي</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function toggleDetails(reportId) {
    const detailsRow = $('#details-' + reportId);
    const contentDiv = $('#report-content-' + reportId);
    
    if (detailsRow.is(':visible')) {
        detailsRow.hide();
    } else {
        // Fetch report details via AJAX
        $.ajax({
            url: 'fetch_report_details.php',
            type: 'GET',
            data: { report_id: reportId },
            success: function(data) {
                contentDiv.html(data);
                detailsRow.show();
            },
            error: function() {
                contentDiv.html('<div class="alert alert-danger">فشل تحميل تفاصيل التقرير</div>');
                detailsRow.show();
            }
        });
    }
}

function deleteReport(reportId, reportName) {
    if (confirm('هل أنت متأكد من حذف التقرير "' + reportName + '"؟')) {
        $.ajax({
            url: 'delete_report.php',
            type: 'POST',
            data: { report_id: reportId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('خطأ: ' + (response.message || 'فشل حذف التقرير'));
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال بالخادم');
            }
        });
    }
}
</script>
