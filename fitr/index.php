<?php
include('db_connection.php');

// جلب السنوات من جدول المدفوعات
$query_years = "SELECT DISTINCT YEAR(payment_date) as year FROM payments ORDER BY year DESC";
$result_years = $conn->query($query_years);

// جلب إحصائيات لكل سنة (إجمالي المدفوعات وعدد المستفيدين)
$query_stats = "SELECT YEAR(payment_date) as year, COUNT(DISTINCT beneficiary_id) as total_beneficiaries, SUM(amount) as total_amount 
                FROM payments GROUP BY YEAR(payment_date) ORDER BY year DESC";
$result_stats = $conn->query($query_stats);

$stats = [];
while ($row = $result_stats->fetch_assoc()) {
    $stats[$row['year']] = [
        'total_beneficiaries' => $row['total_beneficiaries'],
        'total_amount' => $row['total_amount']
    ];
}

ob_start();
?>

<div class="container page-section">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="page-title m-0">السنوات المتاحة لزكاة الفطر</h2>
    </div>

    <div class="row g-3">
        <?php foreach ($stats as $year => $stat) { ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card card-elevated h-100">
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <h5 class="card-title mb-2"><?php echo $year; ?></h5>
                        <div class="text-muted mb-1">عدد المستفيدين: <?php echo $stat['total_beneficiaries']; ?></div>
                        <div class="text-muted mb-3">إجمالي المدفوعات: <?php echo number_format($stat['total_amount'], 2) . " ليرة"; ?></div>
                        <a href="month_details.php?year=<?php echo $year; ?>" class="btn btn-primary btn-icon align-self-center">
                            <i class="bi bi-calendar-check"></i><span>عرض التفاصيل</span>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include('header.php');
?>