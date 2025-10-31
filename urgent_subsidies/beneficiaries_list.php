<?php
include('db_connection.php');

// جلب جميع المستفيدين
$filter = '';
if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
    if ($filter == 'paid') {
        $query = "SELECT beneficiaries.*, payments.amount FROM beneficiaries JOIN payments ON beneficiaries.id = payments.beneficiary_id";
    } elseif ($filter == 'unpaid') {
        $query = "SELECT beneficiaries.* FROM beneficiaries LEFT JOIN payments ON beneficiaries.id = payments.beneficiary_id WHERE payments.id IS NULL";
    } else {
        $query = "SELECT * FROM beneficiaries";
    }
} else {
    $query = "SELECT * FROM beneficiaries";
}

$result = $conn->query($query);

$title = "قائمة المستفيدين وتسجيل الدفعيات";
ob_start();
?>

<h2 class="page-title mb-3">قائمة المستفيدين وتسجيل الدفعيات</h2>

<!-- إضافة الفلتر -->
<div class="filter-container mb-3">
    <form method="GET" action="beneficiaries_list.php" class="form-inline">
        <label for="filter" class="mr-2">عرض:</label>
        <select name="filter" id="filter" class="form-control mr-2">
            <option value="">الكل</option>
            <option value="paid" <?php echo $filter == 'paid' ? 'selected' : ''; ?>>تم الدفع لهم</option>
            <option value="unpaid" <?php echo $filter == 'unpaid' ? 'selected' : ''; ?>>لم يتم الدفع لهم</option>
        </select>
        <button type="submit" class="btn btn-primary">تطبيق</button>
    </form>
</div>

<div class="card card-elevated">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-striped table-modern m-0">
    <thead>
        <tr>
            <th>الاسم</th>
            <th>رقم الهاتف</th>
            <th>رقم الكملك</th>
            <th>رقم الـ IBAN</th>
            <th>المبلغ الشهري</th>
            <th>تسجيل دفعية</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()) { 
            // فحص إذا كان المستفيد لديه دفعية
            $query_payment = "SELECT * FROM payments WHERE beneficiary_id = ?";
            $stmt_payment = $conn->prepare($query_payment);
            $stmt_payment->bind_param('i', $row['id']);
            $stmt_payment->execute();
            $payment_result = $stmt_payment->get_result();
            $payment = $payment_result->fetch_assoc();
            $is_paid = $payment ? true : false;
        ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['kimlik_number']; ?></td>
                <td><?php echo $row['iban']; ?></td>
                <td><?php echo $row['monthly_amount']; ?></td>
                <td>
                    <form method="POST" action="beneficiaries_list.php" class="form-inline">
                        <input type="hidden" name="beneficiary_id" value="<?php echo $row['id']; ?>">

                        <?php if ($is_paid) { ?>
                            <!-- إذا كانت الدفعة قد تم تسجيلها، اجعل الحقل غير قابل للتعديل -->
                            <input type="number" name="amount" class="form-control mb-2 mr-sm-2" value="<?php echo $payment['amount']; ?>" disabled>
                            <input type="date" name="payment_date" class="form-control mb-2 mr-sm-2" value="<?php echo $payment['payment_date']; ?>" disabled>
                            <select name="payment_type" class="form-control mb-2 mr-sm-2" disabled>
                                <option value="تحويل بنكي" <?php echo $payment['payment_type'] == 'تحويل بنكي' ? 'selected' : ''; ?>>تحويل بنكي</option>
                                <option value="تحويل دفعية" <?php echo $payment['payment_type'] == 'تحويل دفعية' ? 'selected' : ''; ?>>تحويل دفعية</option>
                            </select>
                        <?php } else { ?>
                            <!-- إذا لم يتم تسجيل الدفعة، اجعل الحقول قابلة للتعديل -->
                            <input type="number" name="amount" class="form-control mb-2 mr-sm-2" placeholder="المبلغ" required>
                            <input type="date" name="payment_date" class="form-control mb-2 mr-sm-2" value="<?php echo date('Y-m-d'); ?>" required>
                            <select name="payment_type" class="form-control mb-2 mr-sm-2" required>
                                <option value="">اختر نوع الدفعية</option>
                                <option value="تحويل بنكي">تحويل بنكي</option>
                                <option value="تحويل دفعية">تحويل دفعية</option>
                            </select>
                            <button type="submit" class="btn btn-primary mb-2">تسجيل الدفعية</button>
                        <?php } ?>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</div>
</div>
</div>

<?php
// معالجة البيانات بعد تقديم النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $beneficiary_id = $_POST['beneficiary_id'];
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_type = $_POST['payment_type'];

    // إدخال الدفعية إلى قاعدة البيانات
    $query = "INSERT INTO payments (beneficiary_id, amount, payment_date, payment_type) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('idss', $beneficiary_id, $amount, $payment_date, $payment_type);
    $stmt->execute();

    // إعادة التوجيه إلى نفس الصفحة بعد تسجيل الدفعية
    header("Location: beneficiaries_list.php");
    exit();
}

$content = ob_get_clean();
include('header.php');
?>
