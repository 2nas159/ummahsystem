<?php
include('db_connection.php');

// معالجة الفلترة إذا تم إرسال النموذج
$whereClauses = [];
$params = [];
$types = "";

// التحقق من الفلترة بناءً على الاسم
if (!empty($_GET['name'])) {
    $whereClauses[] = "b.name LIKE ?";
    $params[] = '%' . $_GET['name'] . '%';
    $types .= "s";
}

// التحقق من الفلترة بناءً على التاريخ
if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $whereClauses[] = "p.payment_date BETWEEN ? AND ?";
    $params[] = $_GET['start_date'];
    $params[] = $_GET['end_date'];
    $types .= "ss";
}

// بناء جملة WHERE للاستعلام
$whereSQL = "";
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// جلب المدفوعات بناءً على الفلترة
$query = "SELECT p.id, b.name, p.amount, p.payment_date, p.notes 
          FROM payments p
          JOIN beneficiaries b ON p.beneficiary_id = b.id
          $whereSQL";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$title = "سجل المدفوعات";
ob_start();
?>

<div class="container page-section">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="page-title m-0">سجل المدفوعات</h2>
    </div>

    <div class="card card-elevated">
        <div class="card-body">
            
            <!-- نموذج الفلترة -->
            <form method="GET" class="mb-4 row g-3">
                <div class="col-md-4">
                    <input type="text" name="name" class="form-control" placeholder="🔍 فلترة بالاسم" value="<?php echo isset($_GET['name']) ? $_GET['name'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                </div>
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-primary w-100">تطبيق الفلترة</button>
                </div>
            </form>

            <div class="table-responsive">
            <table class="table table-striped table-modern m-0">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>المبلغ</th>
                        <th>تاريخ الدفع</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_amount = 0; // حساب المجموع الإجمالي
                    if ($result->num_rows > 0) {
                        while ($payment = $result->fetch_assoc()) {
                            $total_amount += $payment['amount']; // إضافة المبلغ إلى المجموع الإجمالي
                    ?>
                        <tr>
                            <td><?php echo $payment['name']; ?></td>
                            <td><?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo $payment['payment_date']; ?></td>
                            <td><?php echo htmlspecialchars($payment['notes']); ?></td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center'>لا توجد نتائج مطابقة</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            </div>

            <!-- عرض المبلغ الإجمالي -->
            <div class="alert alert-info mt-3 mb-0 text-center">المبلغ الإجمالي: <?php echo number_format($total_amount, 2); ?>₺</div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>
