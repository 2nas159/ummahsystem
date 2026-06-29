<?php
session_start();
include("reports_db.php");
include("../includes/csrf_helper.php");

// Check authentication
if (!isset($_SESSION["username"])) {
    header("Location: ../index.php");
    exit;
}

// Get report ID
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($report_id <= 0) {
    $_SESSION['error_message'] = 'معرف التقرير غير صحيح';
    header('Location: index.php');
    exit;
}

// Fetch report data
$stmt = $pdo->prepare("SELECT * FROM reports WHERE id = :id");
$stmt->execute([':id' => $report_id]);
$report = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$report) {
    $_SESSION['error_message'] = 'التقرير غير موجود';
    header('Location: index.php');
    exit;
}

// Fetch report items
$stmt = $pdo->prepare("SELECT * FROM report_items WHERE report_id = :id ORDER BY item_order ASC");
$stmt->execute([':id' => $report_id]);
$report_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'خطأ أمني: رمز التحقق غير صحيح';
        header("Location: edit.php?id=$report_id");
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update report metadata
        $report_name = trim($_POST['report_name'] ?? '');
        $report_month = $_POST['report_month'] ?? '';
        $report_type = $_POST['report_type'] ?? 'monthly';
        $status = $_POST['status'] ?? 'draft';

        if (empty($report_name) || empty($report_month)) {
            throw new Exception('اسم التقرير والشهر مطلوبان');
        }

        $stmt = $pdo->prepare("
            UPDATE reports 
            SET report_name = :report_name, 
                report_month = :report_month, 
                report_type = :report_type,
                status = :status
            WHERE id = :id
        ");
        $stmt->execute([
            ':report_name' => $report_name,
            ':report_month' => $report_month . '-01',
            ':report_type' => $report_type,
            ':status' => $status,
            ':id' => $report_id
        ]);

        // Delete existing items
        $stmt = $pdo->prepare("DELETE FROM report_items WHERE report_id = :id");
        $stmt->execute([':id' => $report_id]);

        // Insert updated items
        $goals = $_POST['goals'] ?? [];
        $stmt = $pdo->prepare("
            INSERT INTO report_items 
            (report_id, goal, tasks, number_of_individuals, negatives_and_obstacles, 
             evaluation, amount, completion_percentage, notes_and_recommendations, item_order) 
            VALUES (:report_id, :goal, :tasks, :number_of_individuals, :negatives_and_obstacles, 
                    :evaluation, :amount, :completion_percentage, :notes_and_recommendations, :item_order)
        ");

        $item_count = 0;
        foreach ($goals as $index => $goal) {
            $goal = trim($goal);
            if (empty($goal)) continue;

            $stmt->execute([
                ':report_id' => $report_id,
                ':goal' => $goal,
                ':tasks' => trim($_POST['tasks'][$index] ?? ''),
                ':number_of_individuals' => (int)($_POST['number_of_individuals'][$index] ?? 0),
                ':negatives_and_obstacles' => trim($_POST['negatives_and_obstacles'][$index] ?? ''),
                ':evaluation' => trim($_POST['evaluation'][$index] ?? ''),
                ':amount' => (float)($_POST['amount'][$index] ?? 0),
                ':completion_percentage' => min(100, max(0, (float)($_POST['completion_percentage'][$index] ?? 0))),
                ':notes_and_recommendations' => trim($_POST['notes_and_recommendations'][$index] ?? ''),
                ':item_order' => $item_count++
            ]);
        }

        if ($item_count === 0) {
            throw new Exception('يجب إضافة هدف واحد على الأقل');
        }

        $pdo->commit();
        $_SESSION['success_message'] = 'تم تحديث التقرير بنجاح';
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'خطأ: ' . $e->getMessage();
        header("Location: edit.php?id=$report_id");
        exit;
    }
}

$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-edit" style="margin-left: 10px;"></i>تعديل التقرير: <?= htmlspecialchars($report['report_name']) ?></h4>
        </div>
        <div class="card-body">
            <form id="reportForm" method="post">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <!-- Report Header -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">اسم التقرير <span class="text-danger">*</span></label>
                        <input type="text" name="report_name" class="form-control form-control-lg"
                            value="<?= htmlspecialchars($report['report_name']) ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">نوع التقرير</label>
                        <select name="report_type" class="form-select form-select-lg">
                            <option value="monthly" <?= $report['report_type'] === 'monthly' ? 'selected' : '' ?>>شهري</option>
                            <option value="quarterly" <?= $report['report_type'] === 'quarterly' ? 'selected' : '' ?>>ربع سنوي</option>
                            <option value="annual" <?= $report['report_type'] === 'annual' ? 'selected' : '' ?>>سنوي</option>
                            <option value="custom" <?= $report['report_type'] === 'custom' ? 'selected' : '' ?>>مخصص</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">الشهر <span class="text-danger">*</span></label>
                        <input type="month" name="report_month" class="form-control form-control-lg"
                            value="<?= date('Y-m', strtotime($report['report_month'])) ?>" required>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Report Items -->
                <div id="reportItemsContainer">
                    <?php foreach ($report_items as $index => $item): ?>
                        <div class="card report-item mb-3" data-item-id="<?= $index ?>">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-bullseye" style="margin-left: 10px;"></i>الهدف #<?= $index + 1 ?></h6>
                                <button type="button" class="btn btn-sm btn-light" onclick="removeReportItem(<?= $index ?>)">
                                    <i class="fas fa-trash" style="margin-left: 10px;"></i>حذف
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">الهدف <span class="text-danger">*</span></label>
                                        <textarea name="goals[]" class="form-control" rows="2" required><?= htmlspecialchars($item['goal']) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">المهام</label>
                                        <textarea name="tasks[]" class="form-control" rows="2"><?= htmlspecialchars($item['tasks']) ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">عدد المستفيدين</label>
                                        <input type="number" name="number_of_individuals[]" class="form-control"
                                            value="<?= $item['number_of_individuals'] ?>" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">المبلغ (₺)</label>
                                        <input type="number" name="amount[]" class="form-control" step="0.01"
                                            value="<?= $item['amount'] ?>" min="0">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">نسبة الإكمال (%)</label>
                                        <input type="number" name="completion_percentage[]" class="form-control"
                                            value="<?= $item['completion_percentage'] ?>" step="0.01" min="0" max="100">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">الإيجابيات والسلبيات</label>
                                        <textarea name="negatives_and_obstacles[]" class="form-control" rows="2"><?= htmlspecialchars($item['negatives_and_obstacles']) ?></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">التقييم</label>
                                        <textarea name="evaluation[]" class="form-control" rows="2"><?= htmlspecialchars($item['evaluation']) ?></textarea>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">ملاحظات واقتراحات</label>
                                        <textarea name="notes_and_recommendations[]" class="form-control" rows="2"><?= htmlspecialchars($item['notes_and_recommendations']) ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn btn-success" onclick="addReportItem()">
                        <i class="fas fa-plus" style="margin-left: 10px;"></i>إضافة هدف جديد
                    </button>
                    <button type="button" class="btn btn-info" onclick="saveDraft()">
                        <i class="fas fa-save" style="margin-left: 10px;"></i>حفظ كمسودة
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check" style="margin-left: 10px;"></i>تحديث التقرير
                    </button>
                    <input type="hidden" name="status" id="statusInput" value="<?= $report['status'] ?>">
                </div>
            </form>
        </div>
    </div>
</main>

<style>
    .report-item {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .report-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-color: #0d6efd;
    }

    .report-item .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0 !important;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<script>
    let itemCounter = <?= count($report_items) ?>;

    function addReportItem() {
        const container = document.getElementById('reportItemsContainer');
        const itemHtml = `
        <div class="card report-item mb-3" data-item-id="${itemCounter}">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bullseye me-2"></i>الهدف #${itemCounter + 1}</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="removeReportItem(${itemCounter})">
                    <i class="fas fa-trash me-1"></i>حذف
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">الهدف <span class="text-danger">*</span></label>
                        <textarea name="goals[]" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">المهام</label>
                        <textarea name="tasks[]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">عدد المستفيدين</label>
                        <input type="number" name="number_of_individuals[]" class="form-control" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">المبلغ (₺)</label>
                        <input type="number" name="amount[]" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نسبة الإكمال (%)</label>
                        <input type="number" name="completion_percentage[]" class="form-control" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الإيجابيات والسلبيات</label>
                        <textarea name="negatives_and_obstacles[]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">التقييم</label>
                        <textarea name="evaluation[]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">ملاحظات واقتراحات</label>
                        <textarea name="notes_and_recommendations[]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        </div>
    `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        itemCounter++;
        updateItemNumbers();
    }

    function removeReportItem(itemId) {
        if (confirm('هل أنت متأكد من حذف هذا الهدف؟')) {
            const item = document.querySelector(`[data-item-id="${itemId}"]`);
            if (item) {
                item.remove();
                updateItemNumbers();
            }
        }
    }

    function updateItemNumbers() {
        document.querySelectorAll('.report-item').forEach((item, index) => {
            item.querySelector('h6').innerHTML = `<i class="fas fa-bullseye me-2"></i>الهدف #${index + 1}`;
        });
    }

    function saveDraft() {
        document.getElementById('statusInput').value = 'draft';
        document.getElementById('reportForm').submit();
    }

    document.getElementById('reportForm').addEventListener('submit', function(e) {
        const items = document.querySelectorAll('.report-item');
        if (items.length === 0) {
            e.preventDefault();
            alert('يجب إضافة هدف واحد على الأقل');
            return false;
        }
    });
</script>
