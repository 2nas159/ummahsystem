<?php
session_start();
include("reports_db.php");
include("includes/csrf_helper.php");
$csrf_token = generateCSRFToken();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Search and filters
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$type_filter = $_GET['type'] ?? '';
$month_filter = $_GET['month'] ?? '';
$year_filter = $_GET['year'] ?? '';

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
    $where_conditions[] = "r.report_type <> 'annual'";
    $where_conditions[] = "DATE_FORMAT(r.report_month, '%Y-%m') = :month";
    $params[':month'] = $month_filter;
}

if (!empty($year_filter) && preg_match('/^\d{4}$/', $year_filter)) {
    $where_conditions[] = "((r.report_type = 'annual' AND YEAR(r.report_month) = :year_annual) OR (r.report_type <> 'annual' AND YEAR(r.report_month) = :year_monthly))";
    $params[':year_annual'] = $year_filter;
    $params[':year_monthly'] = $year_filter;
} else {
    $year_filter = '';
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
        ORDER BY r.report_month DESC, r.created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Analytics data
$monthly_stmt = $pdo->query("
    SELECT DATE_FORMAT(r.report_month, '%Y-%m') AS month, COALESCE(SUM(ri.amount),0) AS total_amount
    FROM reports r
    LEFT JOIN report_items ri ON ri.report_id = r.id
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
");
$monthly_stats = array_reverse($monthly_stmt->fetchAll(PDO::FETCH_ASSOC));

$status_stmt = $pdo->query("SELECT status, COUNT(*) as total FROM reports GROUP BY status");
$status_counts = [];
while ($row = $status_stmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = (int)$row['total'];
}

$type_stmt = $pdo->query("SELECT report_type, COUNT(*) as total FROM reports GROUP BY report_type");
$type_counts = [];
while ($row = $type_stmt->fetch(PDO::FETCH_ASSOC)) {
    $type_counts[$row['report_type']] = (int)$row['total'];
}

$all_reports_stmt = $pdo->query("SELECT id, report_name FROM reports ORDER BY created_at DESC");
$all_reports = $all_reports_stmt->fetchAll(PDO::FETCH_ASSOC);

$monthly_labels = array_column($monthly_stats, 'month');
$monthly_values = array_map(fn($row) => (float)$row['total_amount'], $monthly_stats);

$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4 page-section">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="page-title m-0"><i class="fas fa-file-alt" style="margin-left: 10px;"></i>التقارير المتاحة</h2>
        <a href="reports.php" class="btn btn-primary">
            <i class="fas fa-plus" style="margin-left: 10px;"></i>تقرير جديد
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
                <div class="col-md-2">
                    <label class="form-label">السنة</label>
                    <input type="number" name="year" class="form-control" min="2000" max="2100"
                           value="<?= htmlspecialchars($year_filter) ?>" placeholder="YYYY">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search" style="margin-left: 10px;"></i>بحث
                    </button>
                    <a href="view_reports.php" class="btn btn-secondary">
                        <i class="fas fa-redo" style="margin-left: 10px;"></i>إعادة تعيين
                    </a>
                </div>
                <div class="col-12 d-flex flex-wrap justify-content-end gap-2 mt-2">
                    <button type="button" class="btn btn-outline-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> تصدير Excel
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf"></i> تصدير PDF
                    </button>
                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#compareModal">
                        <i class="fas fa-balance-scale"></i> مقارنة التقارير
                    </button>
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
                    <h3 class="text-warning mb-0"><?= $status_counts['draft'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">موافق عليها</h5>
                    <h3 class="text-success mb-0"><?= $status_counts['approved'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="text-muted mb-2">مقدمة</h5>
                    <h3 class="text-info mb-0"><?= $status_counts['submitted'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-8">
                    <h5 class="mb-3">إجمالي المبالغ (آخر 6 أشهر)</h5>
                    <canvas id="monthlyChart" height="200"></canvas>
                </div>
                <div class="col-lg-4">
                    <h5 class="mb-3">توزيع الحالات</h5>
                    <canvas id="statusChart" height="180"></canvas>
                    <h5 class="mt-4 mb-3">أنواع التقارير</h5>
                    <canvas id="typeChart" height="180"></canvas>
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
                                        <?php if ($month_filter): ?>
                                            <p class="text-muted mb-3">لا توجد تقارير للشهر <?= htmlspecialchars($month_filter) ?>.</p>
                                        <?php elseif ($year_filter): ?>
                                            <p class="text-muted mb-3">لا توجد تقارير للسنة <?= htmlspecialchars($year_filter) ?>.</p>
                                        <?php else: ?>
                                            <p class="text-muted mb-3">لا توجد تقارير متاحة حالياً.</p>
                                        <?php endif; ?>
                                        <a href="view_reports.php" class="btn btn-outline-primary">
                                            عرض جميع التقارير
                                        </a>
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
                                        <div class="btn-group btn-group-sm mb-1" role="group">
                                            <button type="button" class="btn btn-outline-primary" 
                                                    onclick="toggleDetails(<?= $report['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="edit_report.php?id=<?= $report['id'] ?>" 
                                               class="btn btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger" 
                                                    onclick="deleteReport(<?= $report['id'] ?>, '<?= htmlspecialchars($report['report_name'], ENT_QUOTES) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-success" 
                                                    onclick="updateStatus(<?= $report['id'] ?>, 'approved')"
                                                    <?= $report['status'] === 'approved' ? 'disabled' : '' ?>>
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-info" 
                                                    onclick="updateStatus(<?= $report['id'] ?>, 'submitted')"
                                                    <?= $report['status'] === 'submitted' ? 'disabled' : '' ?>>
                                                <i class="fas fa-paper-plane"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary" 
                                                    onclick="updateStatus(<?= $report['id'] ?>, 'rejected')"
                                                    <?= $report['status'] === 'rejected' ? 'disabled' : '' ?>>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
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
                    <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>&year=<?= urlencode($year_filter) ?>">السابق</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>&year=<?= urlencode($year_filter) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>&type=<?= urlencode($type_filter) ?>&month=<?= urlencode($month_filter) ?>&year=<?= urlencode($year_filter) ?>">التالي</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
    <!-- Compare Modal -->
    <div class="modal fade" id="compareModal" tabindex="-1" aria-labelledby="compareModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="compareModalLabel"><i class="fas fa-balance-scale me-2"></i>مقارنة التقارير</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اختر تقريرين على الأقل للمقارنة</label>
                        <select id="compareSelect" class="form-select" multiple size="8">
                            <?php foreach ($all_reports as $rep): ?>
                                <option value="<?= $rep['id'] ?>"><?= htmlspecialchars($rep['report_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">استخدم Ctrl أو Shift لتحديد أكثر من تقرير</small>
                    </div>
                    <div id="compareResult" class="border rounded p-3" style="min-height: 150px;">
                        <p class="text-muted mb-0">سيتم عرض نتائج المقارنة هنا.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-primary" onclick="compareReports()">مقارنة الآن</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
const csrfToken = '<?= $csrf_token ?>';
const monthlyChartLabels = <?= json_encode($monthly_labels, JSON_UNESCAPED_UNICODE) ?>;
const monthlyChartValues = <?= json_encode(array_map('floatval', $monthly_values)) ?>;
const statusChartData = <?= json_encode($status_counts, JSON_UNESCAPED_UNICODE) ?>;
const typeChartData = <?= json_encode($type_counts, JSON_UNESCAPED_UNICODE) ?>;

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
});

function initCharts() {
    if (monthlyChartLabels.length && document.getElementById('monthlyChart')) {
        new Chart(document.getElementById('monthlyChart'), {
            type: 'line',
            data: {
                labels: monthlyChartLabels,
                datasets: [{
                    label: 'المبلغ الإجمالي (₺)',
                    data: monthlyChartValues,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }

    const statusLabels = Object.keys(statusChartData);
    const statusValues = Object.values(statusChartData);
    if (statusLabels.length && document.getElementById('statusChart')) {
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b']
                }]
            },
            options: { responsive: true }
        });
    }

    const typeLabels = Object.keys(typeChartData);
    const typeValues = Object.values(typeChartData);
    if (typeLabels.length && document.getElementById('typeChart')) {
        new Chart(document.getElementById('typeChart'), {
            type: 'bar',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeValues,
                    backgroundColor: '#764ba2'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }
}

function exportToExcel() {
    const table = document.querySelector('.table.table-hover');
    if (!table) return;
    const wb = XLSX.utils.table_to_book(table, { sheet: 'التقارير' });
    XLSX.writeFile(wb, `reports_${new Date().toISOString().slice(0,10)}.xlsx`);
}

function exportToPDF() {
    const tableWrapper = document.querySelector('.table-responsive');
    if (!tableWrapper) return;
    const clone = tableWrapper.cloneNode(true);
    html2pdf().set({
        margin: 0.5,
        filename: `reports_${new Date().toISOString().slice(0,10)}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    }).from(clone).save();
}

function toggleDetails(reportId) {
    const detailsRow = $('#details-' + reportId);
    const contentDiv = $('#report-content-' + reportId);
    
    if (detailsRow.is(':visible')) {
        detailsRow.hide();
    } else {
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

function updateStatus(reportId, status) {
    $.ajax({
        url: 'update_report_status.php',
        type: 'POST',
        data: { report_id: reportId, status: status, csrf_token: csrfToken },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || 'فشل تحديث حالة التقرير');
            }
        },
        error: function() {
            alert('حدث خطأ في الاتصال بالخادم');
        }
    });
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

function compareReports() {
    const select = document.getElementById('compareSelect');
    const selectedIds = Array.from(select.selectedOptions).map(option => option.value);
    if (selectedIds.length < 2) {
        alert('يرجى اختيار تقريرين على الأقل');
        return;
    }

    const params = new URLSearchParams();
    selectedIds.forEach(id => params.append('report_ids[]', id));

    fetch('compare_reports.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderComparisonResult(data);
            } else {
                alert(data.message || 'فشل مقارنة التقارير');
            }
        })
        .catch(error => {
            console.error(error);
            alert('حدث خطأ أثناء المقارنة');
        });
}

function renderComparisonResult(data) {
    const container = document.getElementById('compareResult');
    const reports = data.reports;
    const items = data.items;

    let html = '<div class="table-responsive mb-3"><table class="table table-bordered">';
    html += '<thead><tr><th>المؤشر</th>';
    reports.forEach(rep => {
        html += `<th>${rep.report_name}</th>`;
    });
    html += '</tr></thead><tbody>';

    const statusLabels = {
        draft: 'مسودة',
        submitted: 'مقدم',
        approved: 'موافق عليه',
        rejected: 'مرفوض'
    };
    const typeLabels = {
        monthly: 'شهري',
        quarterly: 'ربع سنوي',
        annual: 'سنوي',
        custom: 'مخصص'
    };

    const rows = [
        { label: 'الحالة', value: rep => statusLabels[rep.status] || rep.status },
        { label: 'النوع', value: rep => typeLabels[rep.report_type] || rep.report_type },
        { label: 'الشهر', value: rep => (rep.report_month || '').substring(0,7) },
        { label: 'عدد الأهداف', value: rep => items[rep.id] ? items[rep.id].length : 0 },
        { label: 'المبلغ الإجمالي (₺)', value: rep => Number(rep.total_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) },
        { label: 'عدد المستفيدين', value: rep => Number(rep.total_individuals || 0).toLocaleString('tr-TR') },
        { label: 'تاريخ الإنشاء', value: rep => rep.created_at }
    ];

    rows.forEach(row => {
        html += `<tr><th>${row.label}</th>`;
        reports.forEach(rep => {
            html += `<td>${row.value(rep)}</td>`;
        });
        html += '</tr>';
    });

    html += '</tbody></table></div>';

    html += '<div class="row g-3">';
    reports.forEach(rep => {
        html += '<div class="col-md-6"><div class="border rounded p-3">';
        html += `<h6>${rep.report_name}</h6><ul class="mb-0">`;
        (items[rep.id] || []).forEach(item => {
            html += `<li><strong>${item.goal}</strong> - ${item.tasks || ''}</li>`;
        });
        html += '</ul></div></div>';
    });
    html += '</div>';

    container.innerHTML = html;
}
</script>
