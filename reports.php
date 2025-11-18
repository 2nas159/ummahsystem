<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include("reports_db.php");
include("includes/csrf_helper.php");
$csrf_token = generateCSRFToken();
$BASE_PATH_PREFIX = '';
$templates_stmt = $pdo->query("SELECT id, template_name, template_type, created_at FROM report_templates ORDER BY created_at DESC");
$templates = $templates_stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- Template Controls -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label fw-bold">اختيار قالب جاهز</label>
                    <div class="input-group">
                        <select id="templateSelect" class="form-select">
                            <option value="">اختر قالباً</option>
                            <?php foreach ($templates as $template): ?>
                                <option value="<?= $template['id'] ?>">
                                    <?= htmlspecialchars($template['template_name']) ?> (<?= htmlspecialchars($template['template_type']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary" onclick="applySelectedTemplate()">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#saveTemplateModal">
                        <i class="fas fa-save me-2"></i>حفظ كقالب
                    </button>
                </div>
                <div class="col-md-3">
                    <a href="view_reports.php" class="btn btn-outline-info w-100">
                        <i class="fas fa-list me-2"></i>عرض التقارير
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-file-alt" style="margin-left: 10px;"></i>إنشاء تقرير جديد</h4>
        </div>
        <div class="card-body">
            <form id="reportForm" method="post" action="create_report.php">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
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
                        <label class="form-label fw-bold" id="periodLabel">الشهر <span class="text-danger">*</span></label>
                        <div id="periodInputs">
                            <input type="month" name="report_month" class="form-control form-control-lg mb-2" 
                                   value="<?= date('Y-m') ?>" required>
                            <input type="number" name="report_year" class="form-control form-control-lg d-none"
                                   min="2000" max="2100" placeholder="YYYY">
                        </div>
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
                        <i class="fas fa-plus" style="margin-left: 10px;"></i>إضافة هدف جديد
                    </button>
                    <button type="button" class="btn btn-info" onclick="saveDraft()">
                        <i class="fas fa-save" style="margin-left: 10px;"></i>حفظ كمسودة
                    </button>
                    <button type="submit" name="status" value="submitted" class="btn btn-primary">
                        <i class="fas fa-check" style="margin-left: 10px;"></i>نشر التقرير
                    </button>
                    <input type="hidden" name="status" id="statusInput" value="submitted">
                </div>
            </form>
        </div>
    </div>
</main>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-labelledby="saveTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveTemplateModalLabel"><i class="fas fa-save me-2"></i>حفظ كقالب</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="templateForm">
                    <div class="mb-3">
                        <label class="form-label">اسم القالب <span class="text-danger">*</span></label>
                        <input type="text" id="templateName" class="form-control" placeholder="أدخل اسم القالب" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع القالب</label>
                        <select id="templateType" class="form-select">
                            <option value="monthly">شهري</option>
                            <option value="quarterly">ربع سنوي</option>
                            <option value="annual">سنوي</option>
                            <option value="custom" selected>مخصص</option>
                        </select>
                    </div>
                </form>
                <div class="alert alert-info small mb-0">
                    سيتم حفظ جميع التفاصيل الحالية (اسم التقرير، النوع، الشهر، والأهداف) لاستخدامها لاحقاً.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">حفظ</button>
            </div>
        </div>
    </div>
</div>

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
#bullseye-icon {
    margin-left: 10px;
}
</style>

<script>
let itemCounter = 0;
const csrfToken = '<?= $csrf_token ?>';
const reportForm = document.getElementById('reportForm');
const reportTypeSelect = document.querySelector('select[name="report_type"]');
const monthInput = document.querySelector('input[name="report_month"]');
const yearInput = document.querySelector('input[name="report_year"]');
const periodLabel = document.getElementById('periodLabel');

function updatePeriodInputs() {
    if (!reportTypeSelect || !monthInput || !yearInput || !periodLabel) return;

    if (reportTypeSelect.value === 'annual') {
        // Yearly report: show year, hide month
        monthInput.classList.add('d-none');
        monthInput.removeAttribute('required');

        yearInput.classList.remove('d-none');
        yearInput.setAttribute('required', 'required');
        if (!yearInput.value) {
            yearInput.value = new Date().getFullYear();
        }

        periodLabel.innerHTML = 'السنة <span class="text-danger">*</span>';
    } else {
        // Other types: show month, hide year
        yearInput.classList.add('d-none');
        yearInput.removeAttribute('required');

        monthInput.classList.remove('d-none');
        monthInput.setAttribute('required', 'required');
        if (!monthInput.value) {
            monthInput.value = new Date().toISOString().slice(0, 7);
        }

        periodLabel.innerHTML = 'الشهر <span class="text-danger">*</span>';
    }
}

if (reportTypeSelect) {
    reportTypeSelect.addEventListener('change', updatePeriodInputs);
}

function addReportItem(itemData = null) {
    const container = document.getElementById('reportItemsContainer');
    const currentId = itemCounter;
    const itemHtml = `
        <div class="card report-item" data-item-id="${currentId}">
            <div class="card-header">
                <h6 class="mb-0"><i id="bullseye-icon" class="fas fa-bullseye" style="margin-left: 10px;"></i>الهدف #${currentId + 1}</h6>
                <button type="button" class="btn btn-sm btn-light" onclick="removeReportItem(${currentId})">
                    <i class="fas fa-trash" style="margin-left: 10px;"></i>حذف
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

    if (itemData) {
        const newItem = container.querySelector(`[data-item-id="${currentId}"]`);
        if (newItem) {
            newItem.querySelector('textarea[name="goals[]"]').value = itemData.goal || '';
            newItem.querySelector('textarea[name="tasks[]"]').value = itemData.tasks || '';
            newItem.querySelector('input[name="number_of_individuals[]"]').value = itemData.number_of_individuals || 0;
            newItem.querySelector('input[name="amount[]"]').value = itemData.amount || 0;
            newItem.querySelector('input[name="completion_percentage[]"]').value = itemData.completion_percentage || 0;
            newItem.querySelector('textarea[name="negatives_and_obstacles[]"]').value = itemData.negatives_and_obstacles || '';
            newItem.querySelector('textarea[name="evaluation[]"]').value = itemData.evaluation || '';
            newItem.querySelector('textarea[name="notes_and_recommendations[]"]').value = itemData.notes_and_recommendations || '';
        }
    }

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
    reportForm.dataset.customStatus = 'draft';
    if (typeof reportForm.requestSubmit === 'function') {
        reportForm.requestSubmit();
    } else {
        reportForm.submit();
    }
}

function getFormTemplateData() {
    const items = [];
    document.querySelectorAll('.report-item').forEach(item => {
        items.push({
            goal: item.querySelector('textarea[name="goals[]"]').value,
            tasks: item.querySelector('textarea[name="tasks[]"]').value,
            number_of_individuals: item.querySelector('input[name="number_of_individuals[]"]').value,
            amount: item.querySelector('input[name="amount[]"]').value,
            completion_percentage: item.querySelector('input[name="completion_percentage[]"]').value,
            negatives_and_obstacles: item.querySelector('textarea[name="negatives_and_obstacles[]"]').value,
            evaluation: item.querySelector('textarea[name="evaluation[]"]').value,
            notes_and_recommendations: item.querySelector('textarea[name="notes_and_recommendations[]"]').value
        });
    });

    return {
        report_name: document.querySelector('input[name="report_name"]').value,
        report_type: document.querySelector('select[name="report_type"]').value,
        report_month: document.querySelector('input[name="report_month"]').value,
        report_year: document.querySelector('input[name="report_year"]').value,
        status: document.getElementById('statusInput').value,
        items
    };
}

function applyTemplateData(templateData) {
    const nameField = document.querySelector('input[name="report_name"]');
    const typeField = document.querySelector('select[name="report_type"]');
    const monthField = document.querySelector('input[name="report_month"]');
    const yearField = document.querySelector('input[name="report_year"]');

    if (nameField) {
        nameField.value = templateData.report_name || '';
    }

    const type = templateData.report_type || 'monthly';
    if (typeField) {
        typeField.value = type;
    }

    // Adjust period inputs based on type
    updatePeriodInputs();

    if (type === 'annual') {
        if (yearField) {
            yearField.value = templateData.report_year 
                || (templateData.report_month ? templateData.report_month.substring(0, 4) : new Date().getFullYear());
        }
    } else {
        if (monthField) {
            monthField.value = templateData.report_month || '<?= date('Y-m') ?>';
        }
    }

    document.getElementById('statusInput').value = templateData.status || 'submitted';

    document.getElementById('reportItemsContainer').innerHTML = '';
    itemCounter = 0;

    if (Array.isArray(templateData.items) && templateData.items.length > 0) {
        templateData.items.forEach(item => addReportItem(item));
    } else {
        addReportItem();
    }
}

async function applySelectedTemplate() {
    const templateId = document.getElementById('templateSelect').value;
    if (!templateId) {
        alert('يرجى اختيار قالب');
        return;
    }
    try {
        const response = await fetch(`get_template.php?id=${templateId}`);
        const data = await response.json();
        if (data.success) {
            const templateData = JSON.parse(data.template.template_data);
            applyTemplateData(templateData);
        } else {
            alert(data.message || 'فشل تحميل القالب');
        }
    } catch (error) {
        console.error(error);
        alert('حدث خطأ أثناء تحميل القالب');
    }
}

async function saveTemplate() {
    const templateName = document.getElementById('templateName').value.trim();
    const templateType = document.getElementById('templateType').value;

    if (!templateName) {
        alert('يرجى إدخال اسم القالب');
        return;
    }

    const templateData = JSON.stringify(getFormTemplateData());
    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('template_name', templateName);
    formData.append('template_type', templateType);
    formData.append('template_data', templateData);

    try {
        const response = await fetch('save_template.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            const modalEl = document.getElementById('saveTemplateModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            addTemplateOption(templateName, templateType, result.template_id);
            document.getElementById('templateName').value = '';
            document.getElementById('templateType').value = 'custom';
            alert('تم حفظ القالب بنجاح');
        } else {
            alert(result.message || 'فشل حفظ القالب');
        }
    } catch (error) {
        console.error(error);
        alert('حدث خطأ أثناء حفظ القالب');
    }
}

function addTemplateOption(name, type, id) {
    if (!id) return;
    const select = document.getElementById('templateSelect');
    const option = document.createElement('option');
    option.value = id;
    option.textContent = `${name} (${type})`;
    select.appendChild(option);
    select.value = id;
}

// Form validation
reportForm.addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.report-item');
    if (items.length === 0) {
        e.preventDefault();
        alert('يجب إضافة هدف واحد على الأقل');
        return false;
    }
    
    const statusInput = document.getElementById('statusInput');
    if (reportForm.dataset.customStatus) {
        statusInput.value = reportForm.dataset.customStatus;
        delete reportForm.dataset.customStatus;
    } else {
        statusInput.value = 'submitted';
    }
    
    // Show loading overlay
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay show';
    overlay.innerHTML = '<div class="spinner"></div>';
    document.body.appendChild(overlay);
});

// Initialize with one item
document.addEventListener('DOMContentLoaded', function() {
    updatePeriodInputs();
    addReportItem();
});
</script>
