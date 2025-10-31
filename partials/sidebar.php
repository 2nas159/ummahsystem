<?php
$BASE_PATH_PREFIX = isset($BASE_PATH_PREFIX) ? $BASE_PATH_PREFIX : '';
?>
<div class="sidebar border border-right col-md-3 col-lg-2 p-0 bg-body-tertiary">
    <div class="user-info text-center p-3">
        <img src="<?php echo $BASE_PATH_PREFIX; ?>uploads/<?php echo isset($_SESSION['profile_image']) ? $_SESSION['profile_image'] : 'default.png'; ?>"
            alt="صورة المستخدم" class="user-profile-img rounded-circle mb-2">
        <h6 class="text-center text-body-secondary">
            <?php echo isset($_SESSION['isim']) ? $_SESSION['isim'] : 'مستخدم مجهول'; ?>
        </h6>
    </div>

    <div class="offcanvas-md offcanvas-end bg-body-tertiary" tabindex="-1" id="sidebarMenu"
        aria-labelledby="sidebarMenuLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="sidebarMenuLabel">جمعية أمة الخيرية</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                data-bs-target="#sidebarMenu" aria-label="يغلق"></button>
        </div>
        <div class="offcanvas-body d-md-flex flex-column p-0 pt-lg-3 overflow-y-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'home_admin.php' || $current_page == '../home_admin.php') { echo 'active'; } ?>" aria-current="page" href="<?php echo $BASE_PATH_PREFIX; ?>home_admin.php">
                        <svg class="bi"><use xlink:href="#house-fill" /></svg>
                        الرئيسية
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'help.php' || $current_page == '../help.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>help.php">
                        <svg class="bi"><use xlink:href="#file-earmark" /></svg>
                        المساعدات
                    </a>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'customized_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index.php" role="button" aria-expanded="false" aria-controls="submenu-customized">
                        <svg class="bi"><use xlink:href="#file-earmark" /></svg>
                        اعانات مخصصة 🔽
                    </a>
                    <ul id="submenu-customized" class="submenu collapse" aria-label="اعانات مخصصة">
                        <li class="nav-item"><a class="nav-link <?php if ($current_page == 'customized_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/index.php">الرئيسية</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/beneficiaries_list.php">المستفيدين</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/add_beneficiary.php">إضافة مستفيد</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/sponsors.php">الكفلاء</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>customized_subsidies/payments_list.php">سجل المدفوعات</a></li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'urgent_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index.php" role="button" aria-expanded="false" aria-controls="submenu-urgent">
                        <svg class="bi"><use xlink:href="#file-earmark" /></svg>
                        اعانات عاجلة 🔽
                    </a>
                    <ul id="submenu-urgent" class="submenu collapse" aria-label="اعانات عاجلة">
                        <li class="nav-item"><a class="nav-link <?php if ($current_page == 'urgent_subsidies/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/index.php">الرئيسية</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>urgent_subsidies/payments_list.php">سجل المدفوعات</a></li>
                    </ul>
                </li>

                <li class="nav-item has-submenu">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'fitr/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php" role="button" aria-expanded="false" aria-controls="submenu-eids">
                        <svg class="bi"><use xlink:href="#file-earmark" /></svg>
                        الأعياد 🔽
                    </a>
                    <ul id="submenu-eids" class="submenu collapse" aria-label="الأعياد">
                        <li class="nav-item"><a class="nav-link <?php if ($current_page == 'fitr/index.php' || $current_page == 'index.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>fitr/index.php">الفطر</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH_PREFIX; ?>adha/index.php">الأضحي</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'donators.php' || $current_page == '../donators.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>donators.php">
                        <svg class="bi"><use xlink:href="#people" /></svg>
                        المتبرعون
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2 <?php if ($current_page == 'view_reports.php' || $current_page == '../view_reports.php') { echo 'active'; } ?>" href="<?php echo $BASE_PATH_PREFIX; ?>view_reports.php">
                        <svg class="bi"><use xlink:href="#graph-up" /></svg>
                        التقارير
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2  <?php if ($current_page == 'projects.php' || $current_page == '../projects.php') { echo 'active'; } ?>" href="#">
                        <svg class="bi"><use xlink:href="#puzzle" /></svg>
                        المشاريع
                    </a>
                </li>
            </ul>

            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-body-secondary text-uppercase">
                <span>التقارير المحفوظة</span>
                <a class="link-secondary" href="<?php echo $BASE_PATH_PREFIX; ?>reports.php" aria-label="إضافة تقرير جديد">
                    <svg class="bi"><use xlink:href="#plus-circle" /></svg>
                </a>
            </h6>
            <ul class="nav flex-column mb-auto">
                <li class="nav-item"><a class="nav-link d-flex align-items-center gap-2" href="#"><svg class="bi"><use xlink:href="#file-earmark-text" /></svg>الشهر الحالي</a></li>
                <li class="nav-item"><a class="nav-link d-flex align-items-center gap-2" href="#"><svg class="bi"><use xlink:href="#file-earmark-text" /></svg>الشهر السابق</a></li>
                <li class="nav-item"><a class="nav-link d-flex align-items-center gap-2" href="#"><svg class="bi"><use xlink:href="#file-earmark-text" /></svg>تقرير نهاية العام</a></li>
            </ul>

            <hr class="my-3">

            <ul class="nav flex-column mb-auto">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center gap-2" href="<?php echo $BASE_PATH_PREFIX; ?>secure_logout.php">
                        <svg class="bi"><use xlink:href="#door-closed" /></svg>
                        خروج
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>


