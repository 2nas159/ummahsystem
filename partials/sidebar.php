<?php
$BASE_PATH_PREFIX = isset($BASE_PATH_PREFIX) ? $BASE_PATH_PREFIX : '';
$currentMonthParam = date('Y-m');
$previousMonthParam = date('Y-m', strtotime('first day of -1 month'));
$currentYearParam = date('Y');

ob_start();
?>
<div class="sidebar-profile text-center position-relative">
    <div class="profile-glow"></div>
    <img src="<?php echo $BASE_PATH_PREFIX; ?>uploads/<?php echo isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default.png'; ?>"
        alt="صورة المستخدم" class="user-profile-img rounded-circle mb-2">
    <h6 class="text-white mb-0">
    <span class="text-white-50 small mb-0">مرحباً بك 👋</span><?php echo isset($_SESSION['isim']) ? $_SESSION['isim'] : 'مستخدم مجهول'; ?>
    </h6>
    
</div>

<div class="sidebar-nav flex-column p-0 pt-lg-3 overflow-y-auto">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if (strpos($current_page, 'home_admin.php') !== false) {
                                                                                echo 'active';
                                                                            } ?>" aria-current="page" href="<?php echo $BASE_PATH_PREFIX; ?>home_admin.php">
                <i class="fas fa-home fa-fw"></i>
                <span>الرئيسية</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if (strpos($current_page, 'general_beneficiaries/') !== false) {
                                                                                echo 'active';
                                                                            } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>general_beneficiaries/index.php">
                <i class="fas fa-hands-helping fa-fw"></i>
                <span>المساعدات</span>
            </a>
        </li>

        <li class="nav-item has-submenu">
            <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if (strpos($current_page, 'customized_subsidies/') !== false) {
                                                                                                        echo 'active';
                                                                                                    } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index_customized.php" role="button" aria-expanded="false" aria-controls="submenu-customized">
                <span class="d-flex align-items-center gap-2">
                    <i class="fas fa-user-tag fa-fw"></i>
                    <span>إعانات مخصصة</span>
                </span>
                <i class="fas fa-chevron-down submenu-icon"></i>
            </a>
            <ul id="submenu-customized" class="submenu collapse" aria-label="إعانات مخصصة">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5 <?php if (strpos($current_page, 'customized_subsidies/index_customized.php') !== false) {
                                                            echo 'active';
                                                        } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index_customized.php">
                        <i class="fas fa-home fa-fw" style="margin-left: 10px;"></i>الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/beneficiaries_list.php">
                        <i class="fas fa-users fa-fw" style="margin-left: 10px;"></i>المستفيدين
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/add_beneficiary.php">
                        <i class="fas fa-user-plus fa-fw" style="margin-left: 10px;"></i>إضافة مستفيد
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/sponsors.php">
                        <i class="fas fa-hand-holding-usd fa-fw" style="margin-left: 10px;"></i>الكفلاء
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/payments_list.php">
                        <i class="fas fa-money-check-alt fa-fw" style="margin-left: 10px;"></i>سجل المدفوعات
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-submenu">
            <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if (strpos($current_page, 'urgent_subsidies/') !== false) {
                                                                                                        echo 'active';
                                                                                                    } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index_urgent.php" role="button" aria-expanded="false" aria-controls="submenu-urgent">
                <span class="d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-triangle fa-fw"></i>
                    <span>إعانات عاجلة</span>
                </span>
                <i class="fas fa-chevron-down submenu-icon"></i>
            </a>
            <ul id="submenu-urgent" class="submenu collapse" aria-label="إعانات عاجلة">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5 <?php if (strpos($current_page, 'urgent_subsidies/index_urgent.php') !== false) {
                                                            echo 'active';
                                                        } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index_urgent.php">
                        <i class="fas fa-home fa-fw" style="margin-left: 10px;"></i>الرئيسية
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/payments_list.php">
                        <i class="fas fa-money-check-alt fa-fw" style="margin-left: 10px;"></i>سجل المدفوعات
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item has-submenu">
            <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if (strpos($current_page, 'fitr/') !== false || strpos($current_page, 'adha/') !== false) {
                                                                                                        echo 'active';
                                                                                                    } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php" role="button" aria-expanded="false" aria-controls="submenu-eids">
                <span class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-alt fa-fw"></i>
                    <span>الأعياد</span>
                </span>
                <i class="fas fa-chevron-down submenu-icon"></i>
            </a>
            <ul id="submenu-eids" class="submenu collapse" aria-label="الأعياد">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5 <?php if (strpos($current_page, 'fitr/') !== false) {
                                                            echo 'active';
                                                        } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php">
                        <i class="fas fa-moon fa-fw" style="margin-left: 10px;"></i>الفطر
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2 ps-5 <?php if (strpos($current_page, 'adha/') !== false) {
                                                            echo 'active';
                                                        } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>adha/index.php">
                        <i class="fas fa-sun fa-fw" style="margin-left: 10px;"></i>الأضحي
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if (strpos($current_page, 'donators/') !== false) {
                                                                                echo 'active';
                                                                            } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>donators/index.php">
                <i class="fas fa-hand-holding-heart fa-fw"></i>
                <span>المتبرعون</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if (strpos($current_page, 'reports/') !== false) {
                                                                                echo 'active';
                                                                            } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>reports/index.php">
                <i class="fas fa-chart-bar fa-fw"></i>
                <span>التقارير</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="#">
                <i class="fas fa-project-diagram fa-fw"></i>
                <span>المشاريع</span>
            </a>
        </li>
    </ul>

    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase small">
        <span>التقارير المحفوظة</span>
        <a class="link-secondary" href="<?php echo $BASE_PATH_PREFIX; ?>reports/create.php" aria-label="إضافة تقرير جديد">
            <i class="fas fa-plus-circle"></i>
        </a>
    </h6>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="<?php echo $BASE_PATH_PREFIX; ?>reports/quick.php?scope=current-month">
                <i class="fas fa-file-alt fa-fw"></i>
                <span>الشهر الحالي</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="<?php echo $BASE_PATH_PREFIX; ?>reports/quick.php?scope=previous-month">
                <i class="fas fa-file-alt fa-fw"></i>
                <span>الشهر السابق</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="<?php echo $BASE_PATH_PREFIX; ?>reports/quick.php?scope=current-year">
                <i class="fas fa-file-alt fa-fw"></i>
                <span>تقرير نهاية العام</span>
            </a>
        </li>
    </ul>

    <hr class="my-3">

    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 text-danger" href="<?php echo $BASE_PATH_PREFIX; ?>auth/logout.php">
                <i class="fas fa-sign-out-alt fa-fw"></i>
                <span>خروج</span>
            </a>
        </li>
    </ul>
</div>
<?php
$sidebarInner = ob_get_clean();
?>

<div class="sidebar-wrapper">
    <!-- Desktop Sidebar -->
    <div class="sidebar border border-start col-md-3 col-lg-2 p-0 bg-body-tertiary position-fixed d-none d-md-flex flex-column" id="sidebar">
        <div class="sidebar-styles d-flex flex-column flex-grow-1">
            <?php echo $sidebarInner; ?>
        </div>
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-end bg-body-tertiary sidebar-offcanvas" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header border-bottom d-flex justify-content-end align-items-center gap-2">
            <h5 class="offcanvas-title d-flex align-items-center gap-2 mb-0" id="sidebarMenuLabel" style="font-size: 1.2rem;">
                <i class="fas fa-mosque text-primary" style="font-size: 1.5rem; margin-left: 10px;"></i>
                <span style="white-space: nowrap;">جمعية أمة الخيرية</span>
            </h5>
            <button type="button" class="btn-close ms-2" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarMenu" aria-label="إغلاق"></button>
        </div>
        <div class="offcanvas-body p-0 pt-lg-3 overflow-y-auto">
            <div class="sidebar-styles">
                <?php echo $sidebarInner; ?>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sidebar layout */
    #sidebar {
        height: 100vh;
        overflow-y: auto;
        top: 0;
        right: 0;
        z-index: 1000;
    }

    @media (max-width: 767.98px) {
        #sidebar {
            display: none !important;
        }

        main {
            margin-right: 0 !important;
            padding-top: 4rem !important;
        }
    }

    @media (min-width: 768px) {
        #sidebar {
            display: flex !important;
        }

        main {
            margin-right: 25% !important;
        }
    }

    @media (min-width: 992px) {
        main {
            margin-right: 16.66666667% !important;
        }
    }

    /* Shared sidebar styles */
    .sidebar-styles .nav-link {
        color: var(--sidebar-text) !important;
        transition: all 0.3s ease;
        border-radius: 12px;
        position: relative;
        margin: 4px 8px;
        padding-right: 1rem;
        border: 1px solid transparent;
    }

    .sidebar-styles .nav-link:hover,
    .sidebar-styles .nav-link.active {
        border-color: rgba(14, 165, 233, 0.3);
        background: rgba(14, 165, 233, 0.08);
        color: var(--sidebar-text) !important;
    }

    .sidebar-styles .nav-link.active {
        color: #0f172a !important;
        font-weight: 600;
        border-color: rgba(14, 165, 233, 0.6);
        background: rgba(14, 165, 233, 0.12);
    }

    .sidebar-styles .nav-link i {
        width: 20px;
        text-align: center;
        color: var(--sidebar-muted);
        transition: color 0.3s ease, transform 0.3s ease;
    }

    .sidebar-styles .nav-link:hover i,
    .sidebar-styles .nav-link.active i {
        color: #0ea5e9;
        transform: translateX(-3px);
    }

    .sidebar-styles .submenu {
        background-color: rgba(14, 165, 233, 0.05);
        border-right: 3px solid var(--sidebar-active);
        margin: 0 8px 8px;
        border-radius: 12px;
        padding: 8px 0;
    }

    .sidebar-styles .submenu .nav-link {
        font-size: 0.92rem;
        padding-right: 2rem !important;
        margin: 2px 8px;
        border-radius: 10px;
    }

    .sidebar-styles .submenu .nav-link:hover {
        background-color: rgba(14, 165, 233, 0.12);
        padding-right: 2.4rem;
    }

    .sidebar-styles .submenu .nav-link.active {
        background-color: rgba(14, 165, 233, 0.2);
        color: #0f172a !important;
        font-weight: 600;
    }

    .sidebar-styles .submenu .nav-link i {
        font-size: 0.85rem;
    }

    .sidebar-styles .submenu-icon {
        transition: transform 0.3s ease;
        font-size: 0.75rem;
    }

    .sidebar-styles .has-submenu>.nav-link[aria-expanded="true"] .submenu-icon {
        transform: rotate(180deg);
    }

    .sidebar-profile {
        padding: 2rem 1rem 1.5rem;
        background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
        color: white;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .profile-glow {
        position: absolute;
        top: -50px;
        right: -50px;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, transparent 70%);
        animation: pulse 6s infinite;
    }

    .sidebar-styles .user-profile-img {
        width: 80px;
        height: 80px;
        border: 3px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .sidebar-styles .user-info h6 {
        color: white !important;
        font-weight: 600;
        margin-top: 0.5rem;
    }

    .sidebar-styles::-webkit-scrollbar {
        width: 6px;
    }


    .sidebar-styles .sidebar-heading {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-top: 1rem;
    }

    .sidebar-styles::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-styles::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-styles::-webkit-scrollbar-thumb {
        background: var(--sidebar-border);
        border-radius: 3px;
    }

    .sidebar-styles::-webkit-scrollbar-thumb:hover {
        background: var(--sidebar-muted);
    }

    .sidebar-styles .nav-link.text-danger:hover {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545 !important;
    }

    .sidebar-styles .submenu .nav-link.active::before {
        content: '';
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 60%;
        background-color: white;
        border-radius: 0 3px 3px 0;
    }
</style>