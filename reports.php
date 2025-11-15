<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("includes/csrf_helper.php");
$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';

// Display success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
    <!-- Success/Error Messages -->
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>إنشاء تقرير جديد</h4>
        </div>
        <div class="card-body">
            <form id="reportForm" method="post" action="create_report.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                
                <!-- Report Header Information -->
                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">اسم التقرير <span class="text-danger">*</span></label>
                        <input type="text" name="report_name" class="form-control form-control-lg" 
                               placeholder="أدخل اسم التقرير" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">نوع التقرير</label>
                        <select name="report_type" class="form-select form-select-lg">
                            <option value="monthly">شهري</option>
                            <option value="quarterly">ربع سنوي</option>
                            <option value="annual">سنوي</option>
                            <option value="custom">مخصص</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">الشهر <span class="text-danger">*</span></label>
                        <input type="month" name="report_month" class="form-control form-control-lg" 
                               value="<?= date('Y-m') ?>" required>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Report Items Container -->
                <div id="reportItemsContainer">
                    <!-- Items will be added here dynamically -->
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-4 flex-wrap">
                    <button type="button" class="btn btn-success" onclick="addReportItem()">
                        <i class="fas fa-plus me-2"></i>إضافة هدف جديد
                    </button>
                    <button type="button" class="btn btn-info" onclick="saveDraft()">
                        <i class="fas fa-save me-2"></i>حفظ كمسودة
                    </button>
                    <button type="submit" name="status" value="submitted" class="btn btn-primary">
                        <i class="fas fa-check me-2"></i>نشر التقرير
                    </button>
                    <input type="hidden" name="status" id="statusInput" value="submitted">
                </div>
            </form>
        </div>
    </div>
</main>

<style>
.report-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.report-item:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

.report-item .card-body {
    padding: 1.5rem;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

textarea.form-control {
    min-height: 80px;
    resize: vertical;
}

.btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

#reportForm {
    position: relative;
}

.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-overlay.show {
    display: flex;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0d6efd;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
let itemCounter = 0;

function addReportItem() {
    const container = document.getElementById('reportItemsContainer');
    const itemHtml = `
        <div class="card report-item" data-item-id="${itemCounter}">
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
                        <textarea name="goals[]" class="form-control" rows="2" required 
                                  placeholder="أدخل الهدف"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">المهام</label>
                        <textarea name="tasks[]" class="form-control" rows="2" 
                                  placeholder="أدخل المهام المطلوبة"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">عدد المستفيدين</label>
                        <input type="number" name="number_of_individuals[]" class="form-control" 
                               min="0" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">المبلغ (₺)</label>
                        <input type="number" name="amount[]" class="form-control" step="0.01" 
                               min="0" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">نسبة الإكمال (%)</label>
                        <input type="number" name="completion_percentage[]" class="form-control" 
                               step="0.01" min="0" max="100" placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">الإيجابيات والسلبيات</label>
                        <textarea name="negatives_and_obstacles[]" class="form-control" rows="2" 
                                  placeholder="أدخل الإيجابيات والسلبيات"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">التقييم</label>
                        <textarea name="evaluation[]" class="form-control" rows="2" 
                                  placeholder="أدخل التقييم"></textarea>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">ملاحظات واقتراحات</label>
                        <textarea name="notes_and_recommendations[]" class="form-control" rows="2" 
                                  placeholder="أدخل الملاحظات والاقتراحات"></textarea>
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
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                item.remove();
                updateItemNumbers();
            }, 300);
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

// Form validation
document.getElementById('reportForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.report-item');
    if (items.length === 0) {
        e.preventDefault();
        alert('يجب إضافة هدف واحد على الأقل');
        return false;
    }
    
    // Show loading overlay
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay show';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
});

// Initialize with one item
document.addEventListener('DOMContentLoaded', function() {
    addReportItem();
});
</script>
