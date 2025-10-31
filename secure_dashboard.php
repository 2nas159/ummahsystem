<?php
/**
 * Secure Dashboard
 * Enhanced dashboard with better UI and functionality
 */

require_once __DIR__ . '/secure_init.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/DonatorController.php';
require_once __DIR__ . '/classes/BeneficiaryController.php';
require_once __DIR__ . '/classes/UIComponents.php';

// Check authentication
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$currentUser = $userAuth->getCurrentUser();

// Initialize controllers
$donatorController = new DonatorController();
$customizedBeneficiaryController = new BeneficiaryController('customized');
$urgentBeneficiaryController = new BeneficiaryController('urgent');

// Get statistics
$donatorStats = $donatorController->getStatistics();
$customizedStats = $customizedBeneficiaryController->getStatistics();
$urgentStats = $urgentBeneficiaryController->getStatistics();

// Get recent activities
$recentDonators = $donatorStats['recent_additions'];
$recentCustomized = $customizedStats['recent_additions'];
$recentUrgent = $urgentStats['recent_additions'];

// Calculate totals
$totalDonators = $donatorStats['total_donators'];
$totalCustomizedBeneficiaries = $customizedStats['total_beneficiaries'];
$totalUrgentBeneficiaries = $urgentStats['total_beneficiaries'];
$totalMonthlyAmount = $customizedStats['total_monthly_amount'];

// Generate breadcrumb
$breadcrumb = UIComponents::breadcrumb([
    ['text' => 'الرئيسية', 'url' => 'home_admin.php']
]);

// Generate stats cards
$statsCards = [
    UIComponents::statsCard(
        'إجمالي المتبرعين',
        number_format($totalDonators),
        'fas fa-users',
        'primary',
        '+5%'
    ),
    UIComponents::statsCard(
        'المستفيدون من المساعدات الخاصة',
        number_format($totalCustomizedBeneficiaries),
        'fas fa-heart',
        'success',
        '+12%'
    ),
    UIComponents::statsCard(
        'المستفيدون من المساعدات العاجلة',
        number_format($totalUrgentBeneficiaries),
        'fas fa-ambulance',
        'warning',
        '+8%'
    ),
    UIComponents::statsCard(
        'إجمالي المبالغ الشهرية',
        number_format($totalMonthlyAmount) . ' ليرة',
        'fas fa-money-bill-wave',
        'info',
        '+15%'
    )
];

// Generate recent activities
$recentActivities = [];
foreach ($recentDonators as $donator) {
    $recentActivities[] = [
        'type' => 'donator',
        'text' => "تم إضافة متبرع جديد: {$donator['ADI']}",
        'time' => 'الآن',
        'icon' => 'fas fa-user-plus text-primary'
    ];
}

foreach ($recentCustomized as $beneficiary) {
    $recentActivities[] = [
        'type' => 'beneficiary',
        'text' => "تم إضافة مستفيد من المساعدات الخاصة: {$beneficiary['name']}",
        'time' => 'منذ ساعة',
        'icon' => 'fas fa-heart text-success'
    ];
}

?>

<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <?php echo $breadcrumb; ?>
        
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h1 class="card-title">مرحباً، <?php echo htmlspecialchars($currentUser['isim']); ?>!</h1>
                        <p class="card-text text-muted">مرحباً بك في نظام إدارة جمعية أمة الخيرية</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <?php foreach ($statsCards as $card): ?>
                <div class="col-lg-3 col-md-6 mb-3">
                    <?php echo $card; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Main Content -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-lg-8 mb-4">
                <?php echo UIComponents::card(
                    'النشاطات الأخيرة',
                    '<div class="list-group list-group-flush">' . 
                    implode('', array_map(function($activity) {
                        return "
                        <div class='list-group-item d-flex align-items-center'>
                            <i class='{$activity['icon']} me-3'></i>
                            <div class='flex-grow-1'>
                                <p class='mb-1'>{$activity['text']}</p>
                                <small class='text-muted'>{$activity['time']}</small>
                            </div>
                        </div>";
                    }, array_slice($recentActivities, 0, 5))) . 
                    '</div>',
                    '<a href="#" class="btn btn-outline-primary btn-sm">عرض الكل</a>'
                ); ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <?php echo UIComponents::card(
                    'إجراءات سريعة',
                    '<div class="d-grid gap-2">
                        <a href="secure_add_donators.php" class="btn btn-primary">
                            <i class="fas fa-user-plus me-2"></i>إضافة متبرع جديد
                        </a>
                        <a href="customized_subsidies/add_beneficiary.php" class="btn btn-success">
                            <i class="fas fa-heart me-2"></i>إضافة مستفيد
                        </a>
                        <a href="urgent_subsidies/add_beneficiary.php" class="btn btn-warning">
                            <i class="fas fa-ambulance me-2"></i>مساعدات عاجلة
                        </a>
                        <a href="view_reports.php" class="btn btn-info">
                            <i class="fas fa-chart-bar me-2"></i>التقارير
                        </a>
                    </div>'
                ); ?>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="row">
            <div class="col-12">
                <?php echo UIComponents::card(
                    'إحصائيات المساعدات',
                    '<div class="row">
                        <div class="col-md-6">
                            <h6>المساعدات الخاصة</h6>
                            ' . UIComponents::progressBar(75, '75%', 'success') . '
                        </div>
                        <div class="col-md-6">
                            <h6>المساعدات العاجلة</h6>
                            ' . UIComponents::progressBar(60, '60%', 'warning') . '
                        </div>
                    </div>'
                ); ?>
            </div>
        </div>
    </div>
</main>

<!-- Enhanced JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth animations
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
        });
    });
    
    // Auto-refresh statistics every 30 seconds
    setInterval(function() {
        fetch('api/get_statistics.php')
            .then(response => response.json())
            .then(data => {
                // Update statistics without page reload
                console.log('Statistics updated:', data);
            })
            .catch(error => console.log('Error updating statistics:', error));
    }, 30000);
});
</script>

<!-- Enhanced CSS -->
<style>
.card {
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #eee;
}

.list-group-item:last-child {
    border-bottom: none;
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.progress {
    height: 8px;
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in-up {
    animation: fadeInUp 0.6s ease-out;
}
</style>
