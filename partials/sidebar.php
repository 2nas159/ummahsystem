<?php
$BASE_PATH_PREFIX = isset($BASE_PATH_PREFIX) ? $BASE_PATH_PREFIX : '';
?>
<div class="sidebar border border-start col-md-3 col-lg-2 p-0 bg-body-tertiary position-fixed d-md-block" id="sidebar">
    <div class="user-info text-center p-3 border-bottom">
        <img src="<?php echo $BASE_PATH_PREFIX; ?>uploads/<?php echo isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default.png'; ?>"
            alt="صورة المستخدم" class="user-profile-img rounded-circle mb-2">
        <h6 class="text-center text-body-secondary mb-0">
            <?php echo isset($_SESSION['isim']) ? $_SESSION['isim'] : 'مستخدم مجهول'; ?>
        </h6>
    </div>

    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title d-flex align-items-center gap-2" id="sidebarMenuLabel">
                <i class="fas fa-mosque text-primary"></i>
                جمعية أمة الخيرية
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarMenu" aria-label="إغلاق"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if ($current_page == 'home_admin.php' || $current_page == '../home_admin.php') { echo 'active'; } ?>" aria-current="page" href="<?php echo $BASE_PATH_PREFIX; ?>home_admin.php">
                        <i class="fas fa-home fa-fw"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if ($current_page == 'help.php' || $current_page == '../help.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>help.php">
                        <i class="fas fa-hands-helping fa-fw"></i>
                        <span>المساعدات</span>
                    </a>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if ($current_page == 'customized_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index.php" role="button" aria-expanded="false" aria-controls="submenu-customized">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fas fa-user-tag fa-fw"></i>
                            <span>إعانات مخصصة</span>
                        </span>
                        <i class="fas fa-chevron-down submenu-icon"></i>
                    </a>
                    <ul id="submenu-customized" class="submenu collapse" aria-label="إعانات مخصصة">
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5 <?php if ($current_page == 'customized_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index.php">
                                <i class="fas fa-home fa-fw me-2"></i>الرئيسية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/beneficiaries_list.php">
                                <i class="fas fa-users fa-fw me-2"></i>المستفيدين
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/add_beneficiary.php">
                                <i class="fas fa-user-plus fa-fw me-2"></i>إضافة مستفيد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/sponsors.php">
                                <i class="fas fa-hand-holding-usd fa-fw me-2"></i>الكفلاء
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/payments_list.php">
                                <i class="fas fa-money-check-alt fa-fw me-2"></i>سجل المدفوعات
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if ($current_page == 'urgent_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index.php" role="button" aria-expanded="false" aria-controls="submenu-urgent">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fas fa-exclamation-triangle fa-fw"></i>
                            <span>إعانات عاجلة</span>
                        </span>
                        <i class="fas fa-chevron-down submenu-icon"></i>
                    </a>
                    <ul id="submenu-urgent" class="submenu collapse" aria-label="إعانات عاجلة">
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5 <?php if ($current_page == 'urgent_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index.php">
                                <i class="fas fa-home fa-fw me-2"></i>الرئيسية
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/payments_list.php">
                                <i class="fas fa-money-check-alt fa-fw me-2"></i>سجل المدفوعات
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center justify-content-between gap-2 px-3 py-2 <?php if ($current_page == 'fitr/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php" role="button" aria-expanded="false" aria-controls="submenu-eids">
                        <span class="d-flex align-items-center gap-2">
                            <i class="fas fa-calendar-alt fa-fw"></i>
                            <span>الأعياد</span>
                        </span>
                        <i class="fas fa-chevron-down submenu-icon"></i>
                    </a>
                    <ul id="submenu-eids" class="submenu collapse" aria-label="الأعياد">
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5 <?php if ($current_page == 'fitr/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php">
                                <i class="fas fa-moon fa-fw me-2"></i>الفطر
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link px-3 py-2 ps-5" href="<?php echo $BASE_PATH_PREFIX; ?>adha/index.php">
                                <i class="fas fa-sun fa-fw me-2"></i>الأضحي
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if ($current_page == 'donators.php' || $current_page == '../donators.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>donators.php">
                        <i class="fas fa-hand-holding-heart fa-fw"></i>
                        <span>المتبرعون</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if ($current_page == 'view_reports.php' || $current_page == '../view_reports.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>view_reports.php">
                        <i class="fas fa-chart-bar fa-fw"></i>
                        <span>التقارير</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 <?php if ($current_page == 'projects.php' || $current_page == '../projects.php') { echo 'active'; } ?>" href="#">
                        <i class="fas fa-project-diagram fa-fw"></i>
                        <span>المشاريع</span>
                    </a>
                </li>
            </ul>

            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase small">
                <span>التقارير المحفوظة</span>
                <a class="link-secondary" href="<?php echo $BASE_PATH_PREFIX; ?>reports.php" aria-label="إضافة تقرير جديد">
                    <i class="fas fa-plus-circle"></i>
                </a>
            </h6>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="#">
                        <i class="fas fa-file-alt fa-fw"></i>
                        <span>الشهر الحالي</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="#">
                        <i class="fas fa-file-alt fa-fw"></i>
                        <span>الشهر السابق</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2" href="#">
                        <i class="fas fa-file-alt fa-fw"></i>
                        <span>تقرير نهاية العام</span>
                    </a>
                </li>
            </ul>

            <hr class="my-3">

            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 px-3 py-2 text-danger" href="<?php echo $BASE_PATH_PREFIX; ?>secure_logout.php">
                        <i class="fas fa-sign-out-alt fa-fw"></i>
                        <span>خروج</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
/* Sidebar Responsive Styles */
.sidebar {
    height: 100vh;
    overflow-y: auto;
    top: 0;
    right: 0;
    z-index: 1000;
}

/* Hide sidebar on mobile by default, show on md and up */
@media (max-width: 767.98px) {
    .sidebar {
        display: none !important;
    }
    
    main {
        margin-right: 0 !important;
    }
}

@media (min-width: 768px) {
    .sidebar {
        display: block !important;
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

/* Sidebar Navigation Styles */
.sidebar .nav-link {
    color: var(--sidebar-text) !important;
    transition: all 0.3s ease;
    border-radius: 0;
    position: relative;
}

.sidebar .nav-link:hover {
    background-color: var(--sidebar-hover);
    color: var(--sidebar-text) !important;
    padding-right: 1.5rem;
}

.sidebar .nav-link.active {
    background-color: var(--sidebar-active);
    color: white !important;
    font-weight: 500;
}

.sidebar .nav-link.active i {
    color: white !important;
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
    color: var(--sidebar-muted);
    transition: color 0.3s ease;
}

.sidebar .nav-link:hover i,
.sidebar .nav-link.active i {
    color: inherit;
}

/* Submenu Styles */
.sidebar .submenu {
    background-color: rgba(0, 0, 0, 0.02);
    border-right: 3px solid var(--sidebar-active);
}

.sidebar .submenu .nav-link {
    font-size: 0.9rem;
    padding-right: 2rem !important;
}

.sidebar .submenu .nav-link:hover {
    background-color: var(--sidebar-hover);
    padding-right: 2.5rem;
}

.sidebar .submenu .nav-link.active {
    background-color: var(--sidebar-active);
    color: white !important;
}

.sidebar .submenu .nav-link i {
    font-size: 0.85rem;
}

/* Submenu Icon Animation */
.sidebar .submenu-icon {
    transition: transform 0.3s ease;
    font-size: 0.75rem;
}

.sidebar .has-submenu > .nav-link[aria-expanded="true"] .submenu-icon {
    transform: rotate(180deg);
}

/* User Info Styles */
.sidebar .user-info {
    background: linear-gradient(135deg, var(--sidebar-active) 0%, #0284c7 100%);
    color: white;
    padding: 1.5rem 1rem !important;
}

.sidebar .user-info h6 {
    color: white !important;
    font-weight: 500;
    margin-top: 0.5rem;
}

.sidebar .user-profile-img {
    border: 3px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Sidebar Heading */
.sidebar .sidebar-heading {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-top: 1rem;
}

/* Scrollbar Styling */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar::-webkit-scrollbar-thumb {
    background: var(--sidebar-border);
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: var(--sidebar-muted);
}

/* Offcanvas Header */
.sidebar .offcanvas-header {
    padding: 1rem;
    border-bottom: 1px solid var(--sidebar-border);
}

.sidebar .offcanvas-title {
    font-weight: 600;
    font-size: 1.1rem;
}

/* Mobile Menu Toggle Button */
@media (max-width: 767.98px) {
    .btn[data-bs-toggle="offcanvas"] {
        position: fixed;
        top: 1rem;
        left: 1rem; /* Left side in RTL layout */
        z-index: 1051;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border-radius: 50%;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Add padding to main content on mobile to avoid overlap with toggle button */
    main {
        padding-top: 4rem !important;
    }
}

/* Logout Link Special Styling */
.sidebar .nav-link.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545 !important;
}

/* Active State for Submenu Items */
.sidebar .submenu .nav-link.active::before {
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
