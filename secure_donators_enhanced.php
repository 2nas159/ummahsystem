<?php
/**
 * Enhanced Donators Page
 * Improved UI with better functionality
 */

require_once __DIR__ . '/secure_init.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/DonatorController.php';
require_once __DIR__ . '/classes/UIComponents.php';

// Check authentication
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$donatorController = new DonatorController();

// Handle search and pagination
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;

// Get data
if (!empty($search)) {
    $result = $donatorController->searchDonators($search, $page, $limit);
} else {
    $result = $donatorController->getAllDonators($page, $limit);
}

$donators = $result['data'];
$totalPages = $result['pages'];
$currentPage = $result['page'];

// Generate breadcrumb
$breadcrumb = UIComponents::breadcrumb([
    ['text' => 'الرئيسية', 'url' => 'home_admin.php'],
    ['text' => 'المتبرعين', 'url' => '']
]);

// Generate search form
$searchForm = UIComponents::searchForm('ابحث عن متبرعين...', '', 'GET');

// Generate pagination
$pagination = UIComponents::pagination($currentPage, $totalPages, 'secure_donators_enhanced.php');

// Generate table headers
$headers = ['الرقم', 'الاسم', 'رقم الهاتف', 'تاريخ الإضافة'];

// Generate table actions
$actions = [
    [
        'text' => 'تعديل',
        'url' => 'secure_edit_donator.php?id={id}',
        'class' => 'btn btn-warning btn-sm'
    ],
    [
        'text' => 'حذف',
        'url' => 'secure_delete_donators.php?NO={id}',
        'class' => 'btn btn-danger btn-sm'
    ]
];

// Generate data table
$tableData = array_map(function($donator) {
    return [
        'id' => $donator['NO'],
        'NO' => $donator['NO'],
        'الاسم' => $donator['ADI'],
        'رقم الهاتف' => $donator['TEL'],
        'تاريخ الإضافة' => date('Y-m-d', strtotime($donator['created_at'] ?? 'now'))
    ];
}, $donators);

$dataTable = UIComponents::dataTable($headers, $tableData, $actions);

// Generate statistics cards
$stats = $donatorController->getStatistics();
$statsCards = [
    UIComponents::statsCard(
        'إجمالي المتبرعين',
        number_format($stats['total_donators']),
        'fas fa-users',
        'primary'
    ),
    UIComponents::statsCard(
        'المضافون هذا الشهر',
        number_format(count($stats['recent_additions'])),
        'fas fa-user-plus',
        'success'
    )
];

?>

<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <?php echo $breadcrumb; ?>
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>إدارة المتبرعين</h2>
                    <a href="secure_add_donators.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة متبرع جديد
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php foreach ($statsCards as $card): ?>
                <div class="col-lg-6 col-md-6 mb-3">
                    <?php echo $card; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php echo $searchForm; ?>
                        
                        <!-- Advanced Filters -->
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">ترتيب حسب</label>
                                <select class="form-select" id="sortBy">
                                    <option value="name">الاسم</option>
                                    <option value="date">تاريخ الإضافة</option>
                                    <option value="phone">رقم الهاتف</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">عدد النتائج</label>
                                <select class="form-select" id="limit">
                                    <option value="10">10</option>
                                    <option value="20" selected>20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">تصدير البيانات</label>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-success" onclick="exportData('excel')">
                                        <i class="fas fa-file-excel me-1"></i>Excel
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="exportData('pdf')">
                                        <i class="fas fa-file-pdf me-1"></i>PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">قائمة المتبرعين</h5>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                                <i class="fas fa-sync-alt me-1"></i>تحديث
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleView()">
                                <i class="fas fa-th-large me-1"></i>عرض شبكي
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($donators)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">لا توجد متبرعين</h5>
                                <p class="text-muted">ابدأ بإضافة متبرع جديد</p>
                                <a href="secure_add_donators.php" class="btn btn-primary">إضافة متبرع</a>
                            </div>
                        <?php else: ?>
                            <?php echo $dataTable; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <?php echo $pagination; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add loading states
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.type === 'submit' || this.classList.contains('btn-primary')) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>جاري المعالجة...';
                this.disabled = true;
            }
        });
    });
    
    // Add confirmation for delete actions
    const deleteButtons = document.querySelectorAll('a[href*="delete"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من الحذف؟')) {
                e.preventDefault();
            }
        });
    });
    
    // Add search functionality
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
});

// Export data function
function exportData(format) {
    const search = document.querySelector('input[name="search"]').value;
    const url = `api/export_donators.php?format=${format}&search=${encodeURIComponent(search)}`;
    window.open(url, '_blank');
}

// Refresh table function
function refreshTable() {
    location.reload();
}

// Toggle view function
function toggleView() {
    const table = document.querySelector('.table');
    if (table.classList.contains('table-view')) {
        table.classList.remove('table-view');
        table.classList.add('grid-view');
    } else {
        table.classList.remove('grid-view');
        table.classList.add('table-view');
    }
}
</script>

<!-- Enhanced CSS -->
<style>
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.stats-card .card-body {
    padding: 1.5rem;
}

.stats-card h3 {
    font-size: 2rem;
    font-weight: 700;
}

.stats-card i {
    opacity: 0.8;
}

.empty-state {
    padding: 3rem 1rem;
    text-align: center;
}

.empty-state i {
    color: #6c757d;
    margin-bottom: 1rem;
}

.pagination .page-link {
    color: #007bff;
    border-color: #dee2e6;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>
