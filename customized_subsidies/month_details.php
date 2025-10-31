<?php
include('db_connection.php');

$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int) $_GET['year'] : date('Y');
$month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int) $_GET['month'] : date('m');

// إضافة استعلام لحساب إجمالي المدفوعات
$query_total = "SELECT SUM(amount) as total_amount 
                FROM payments 
                WHERE YEAR(payment_date) = ? 
                AND MONTH(payment_date) = ?";
$stmt_total = $conn->prepare($query_total);
$stmt_total->bind_param('ii', $year, $month);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_payments = $result_total->fetch_assoc()['total_amount'] ?: 0;

// جلب بيانات المستفيدين في الشهر المحدد
$query_beneficiaries = "SELECT b.*, p.id as payment_id, p.amount, p.payment_date, p.sponsor_name, p.payment_type
                        FROM beneficiaries b 
                        LEFT JOIN payments p ON b.id = p.beneficiary_id 
                        AND YEAR(p.payment_date) = ? AND MONTH(p.payment_date) = ?";
$stmt = $conn->prepare($query_beneficiaries);
$stmt->bind_param('ii', $year, $month);
$stmt->execute();
$result_beneficiaries = $stmt->get_result();

$title = "تفاصيل شهر " . date('F', mktime(0, 0, 0, $month, 10)) . " لسنة $year";

// جلب أسماء الكفلاء من جدول sponsors
$sponsors = [];
$query_sponsors = "SELECT id, name FROM sponsors";
$result_sponsors = $conn->query($query_sponsors);
while ($row_sponsor = $result_sponsors->fetch_assoc()) {
    $sponsors[] = $row_sponsor;
}

ob_start();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/styles.css">

<div class="dashboard-container">
    <!-- شريط التنقل العلوي -->
    <div class="top-bar">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col">
                    <h1 class="page-title mb-0"><?php echo $title; ?></h1>
                </div>
                <div class="col-auto">
                    <div class="export-buttons">
                        <button class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel"></i> تصدير Excel
                        </button>
                        <button class="btn btn-danger" onclick="exportToPDF()">
                            <i class="fas fa-file-pdf"></i> تصدير PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقات الإحصائيات -->
    <div class="row stats-cards mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>إجمالي المستفيدين</h3>
                    <h2><?php echo $result_beneficiaries->num_rows; ?></h2>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>إجمالي المدفوعات</h3>
                    <h2><?php echo number_format($total_payments, 2); ?> ₺</h2>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>متوسط المدفوعات</h3>
                    <h2><?php echo number_format($total_payments / ($result_beneficiaries->num_rows ?: 1), 2); ?> ₺</h2>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-content">
                    <h3>نسبة الدفع</h3>
                    <h2><?php 
                        $paid_count = mysqli_query($conn, "SELECT COUNT(*) as count FROM payments WHERE YEAR(payment_date) = $year AND MONTH(payment_date) = $month")->fetch_assoc()['count'];
                        echo round(($paid_count / ($result_beneficiaries->num_rows ?: 1)) * 100) . '%'; 
                    ?></h2>
                </div>
                <div class="stat-card-icon">
                    <i class="fas fa-percentage"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- إضافة الفلاتر أعلى الجدول -->
    <div class="filters-container mb-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">بحث</label>
                    <div class="search-box">
                        <input type="text" id="searchInput" class="form-control" placeholder="بحث...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">حالة الدفع</label>
                    <select id="paymentFilter" class="form-select">
                        <option value="">الكل</option>
                        <option value="مدفوع">تم الدفع</option>
                        <option value="لم يتم الدفع">لم يتم الدفع</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">الكافل</label>
                    <select id="sponsorFilter" class="form-select">
                        <option value="">الكل</option>
                        <?php foreach ($sponsors as $sponsor) { ?>
                            <option value="<?php echo $sponsor['name']; ?>"><?php echo $sponsor['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label class="form-label">نوع الدفع</label>
                    <select id="paymentTypeFilter" class="form-select">
                        <option value="">الكل</option>
                        <option value="تحويل بنكي">تحويل بنكي</option>
                        <option value="نقدي">نقدي</option>
                        <option value="تحويل عيني">تحويل عيني</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- تحديث هيكل الجدول -->
    <div class="data-card">
        <div class="data-card-header">
            <h2>قائمة المستفيدين</h2>
        </div>
        <form method="POST" id="paymentsForm">
            <div class="table-container">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">الاسم</th>
                            <th class="text-center">رقم الهاتف</th>
                            <th class="text-center">رقم الكملك</th>
                            <th class="text-center">رقم الـ IBAN</th>
                            <th class="text-center">المبلغ المدفوع</th>
                            <th class="text-center">اسم الكافل</th>
                            <th class="text-center">نوع التحويل</th>
                            <th class="text-center">عرض الكملك</th>
                            <th class="text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_beneficiaries->fetch_assoc()) { ?>
                            <tr id="beneficiary_row_<?php echo $row['id']; ?>">
                                <td class="text-center"></td>
                                <td class="text-center"><?php echo $row['name']; ?></td>
                                <td class="text-center"><?php echo $row['phone']; ?></td>
                                <td class="text-center"><?php echo $row['kimlik_number']; ?></td>
                                <td class="text-center iban-cell"><?php echo $row['iban']; ?></td>
                                <td class="text-center">
                                    <input type="number" name="payments[<?php echo $row['id']; ?>][amount]" 
                                           class="form-control text-center" placeholder="المبلغ" 
                                           <?php echo $row['amount'] ? 'disabled value="' . $row['amount'] . '"' : ''; ?>>
                                </td>
                                <td class="text-center">
                                    <select name="payments[<?php echo $row['id']; ?>][sponsor_name]" 
                                            class="form-select" <?php echo $row['amount'] ? 'disabled' : ''; ?>>
                                        <option value="">اختر الكافل</option>
                                        <?php foreach ($sponsors as $sponsor) { ?>
                                            <option value="<?php echo $sponsor['name']; ?>" 
                                                    <?php echo $row['sponsor_name'] == $sponsor['name'] ? 'selected' : ''; ?>>
                                                <?php echo $sponsor['name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <select name="payments[<?php echo $row['id']; ?>][payment_type]" 
                                            class="form-select payment-type-select" 
                                            <?php echo $row['amount'] ? 'disabled' : ''; ?>>
                                        <option value="">نوع الدفع</option>
                                        <option value="تحويل بنكي" <?php echo $row['payment_type'] == 'تحويل بنكي' ? 'selected' : ''; ?>>تحويل بنكي</option>
                                        <option value="نقدي" <?php echo $row['payment_type'] == 'نقدي' ? 'selected' : ''; ?>>نقدي</option>
                                        <option value="تحويل عيني" <?php echo $row['payment_type'] == 'تحويل عيني' ? 'selected' : ''; ?>>تحويل عيني</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    <?php if ($row['kimlik_photo']) { ?>
                                        <button type="button" 
                                                class="btn btn-outline-secondary btn-sm btn-view-kimlik" 
                                                onclick="showImagePopup('uploads/<?php echo htmlspecialchars($row['kimlik_photo']); ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    <?php } ?>
                                </td>
                                <td class="text-center">
                                    <div class="action-buttons">
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editBeneficiary(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteBeneficiary(<?php echo $row['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- زر تسجيل الدفعات أسفل الجدول -->
            <div class="submit-section">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> تسجيل الدفعات
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">صورة الكملك</h5>
            </div>
            <div class="modal-body text-center">
                <img id="kimlikImage" src="" alt="صورة الكملك" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<!-- إضافة Modal للتعديل -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل الدفعة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPaymentForm">
                    <input type="hidden" id="edit_payment_id">
                    <div class="mb-3">
                        <label class="form-label">المبلغ</label>
                        <input type="number" class="form-control" id="edit_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">اسم الكافل</label>
                        <select class="form-select" id="edit_sponsor_name" required>
                            <option value="">اختر الكافل</option>
                            <?php foreach ($sponsors as $sponsor) { ?>
                                <option value="<?php echo $sponsor['name']; ?>"><?php echo $sponsor['name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نوع الدفع</label>
                        <select class="form-select" id="edit_payment_type" required>
                            <option value="">اختر نوع الدفع</option>
                            <option value="تحويل بنكي">تحويل بنكي</option>
                            <option value="نقدي">نقدي</option>
                            <option value="تحويل عيني">تحويل عيني</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تاريخ الدفع</label>
                        <input type="date" class="form-control" id="edit_payment_date" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="updatePayment()">حفظ التغييرات</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal تعديل المستفيد -->
<div class="modal fade" id="editBeneficiaryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل بيانات المستفيد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBeneficiaryForm">
                    <input type="hidden" id="edit_beneficiary_id">
                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" class="form-control" id="edit_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" class="form-control" id="edit_phone" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم الكملك</label>
                        <input type="text" class="form-control" id="edit_kimlik" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">رقم IBAN</label>
                        <input type="text" class="form-control" id="edit_iban" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="updateBeneficiary()">حفظ التغييرات</button>
            </div>
        </div>
    </div>
</div>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payments = $_POST['payments'];
    
    foreach ($payments as $beneficiary_id => $payment) {
        $amount = $payment['amount'];
        $payment_date = "$year-$month-01";
        $payment_type = $payment['payment_type'];
        $sponsor_name = $payment['sponsor_name'];
        
        if ($amount && $payment_type && $sponsor_name) {
            $query_insert_payment = "INSERT INTO payments (beneficiary_id, amount, payment_date, payment_type, sponsor_name) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query_insert_payment);
            $stmt->bind_param('idsss', $beneficiary_id, $amount, $payment_date, $payment_type, $sponsor_name);
            $stmt->execute();
        }
    }
    
    header("Location: month_details.php?year=$year&month=$month");
    exit();
}

$content = ob_get_clean();
include('header.php');
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // تحديث الأرقام عند تحميل الصفحة
    updateRowNumbers();

    // دالة الفلترة
    function filterTable() {
        var searchText = $('#searchInput').val().toLowerCase();
        var paymentStatus = $('#paymentFilter').val();
        var sponsor = $('#sponsorFilter').val();
        var paymentType = $('#paymentTypeFilter').val();

        $('.table tbody tr').each(function() {
            var row = $(this);
            var text = row.text().toLowerCase();
            var hasAmount = row.find('input[name*="[amount]"]').val() !== '';
            var rowSponsor = row.find('select[name*="[sponsor_name]"]').val();
            var rowPaymentType = row.find('select[name*="[payment_type]"]').val();

            var matchSearch = text.includes(searchText);
            var matchPayment = paymentStatus === '' || 
                             (paymentStatus === 'مدفوع' && hasAmount) || 
                             (paymentStatus === 'لم يتم الدفع' && !hasAmount);
            var matchSponsor = sponsor === '' || rowSponsor === sponsor;
            var matchType = paymentType === '' || rowPaymentType === paymentType;

            row.toggle(matchSearch && matchPayment && matchSponsor && matchType);
        });

        updateRowNumbers();
    }

    // تحديث أرقام الصفوف
    function updateRowNumbers() {
        var visibleIndex = 1;
        $('.table tbody tr:visible').each(function() {
            $(this).find('td:first').text(visibleIndex++);
        });
    }

    // ربط أحداث الفلترة
    $('#searchInput').on('keyup', filterTable);
    $('#paymentFilter').on('change', filterTable);
    $('#sponsorFilter').on('change', filterTable);
    $('#paymentTypeFilter').on('change', filterTable);
});
</script>

<script>
// تصدير إلى Excel
function exportToExcel() {
    // إنشاء نسخة من الجدول للتصدير
    var table = document.querySelector('.table').cloneNode(true);
    
    // إزالة الأعمدة غير المطلوبة والأزرار
    var rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        // إزالة عمود الإجراءات وعمود عرض الكملك
        if(row.lastElementChild) {
            row.removeChild(row.lastElementChild); // حذف عمود الإجراءات
            row.removeChild(row.lastElementChild); // حذف عمود عرض الكملك
        }
    });

    // تحويل الحقول إلى نص
    var inputs = table.querySelectorAll('input');
    inputs.forEach(input => {
        var td = input.parentElement;
        td.textContent = input.value;
    });

    var selects = table.querySelectorAll('select');
    selects.forEach(select => {
        var td = select.parentElement;
        td.textContent = select.options[select.selectedIndex]?.text || '';
    });

    // تحويل الجدول إلى ورقة عمل
    var wb = XLSX.utils.table_to_book(table, {sheet: "المستفيدين"});
    
    // تحديد اسم الملف
    var fileName = `المستفيدين_${new Date().toISOString().slice(0,10)}.xlsx`;
    
    // تنزيل الملف
    XLSX.writeFile(wb, fileName);
}

// تصدير إلى PDF
function exportToPDF() {
    // إنشاء نسخة من الجدول للتصدير
    var tableClone = document.querySelector('.table').cloneNode(true);
    
    // إزالة الأعمدة غير المطلوبة والأزرار
    var rows = tableClone.querySelectorAll('tr');
    rows.forEach(row => {
        // إزالة عمود الإجراءات وعمود عرض الكملك
        if(row.lastElementChild) {
            row.removeChild(row.lastElementChild);
            row.removeChild(row.lastElementChild);
        }
    });

    // تحويل الحقول إلى نص
    var inputs = tableClone.querySelectorAll('input');
    inputs.forEach(input => {
        var td = input.parentElement;
        td.textContent = input.value;
    });

    var selects = tableClone.querySelectorAll('select');
    selects.forEach(select => {
        var td = select.parentElement;
        td.textContent = select.options[select.selectedIndex]?.text || '';
    });

    // إنشاء div مؤقت للتصدير
    var container = document.createElement('div');
    container.style.direction = 'rtl';
    
    // إضافة العنوان
    var title = document.createElement('h2');
    title.textContent = 'قائمة المستفيدين';
    title.style.textAlign = 'center';
    title.style.marginBottom = '20px';
    container.appendChild(title);
    
    // إضافة الجدول
    container.appendChild(tableClone);

    // خيارات PDF
    var opt = {
        margin: 1,
        filename: `المستفيدين_${new Date().toISOString().slice(0,10)}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    // تصدير PDF
    html2pdf().set(opt).from(container).save();
}

// تحديث أزرار التصدير
document.querySelector('.export-buttons').innerHTML = `
    <button class="btn btn-success" onclick="exportToExcel()">
        <i class="fas fa-file-excel"></i> تصدير Excel
    </button>
    <button class="btn btn-danger" onclick="exportToPDF()">
        <i class="fas fa-file-pdf"></i> تصدير PDF
    </button>
`;
</script>

<script>
$(document).ready(function() {
    // تحديث الأرقام عند تحميل الصفحة
    updateRowNumbers();

    // البحث المباشر
    $("#search_input").on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(".table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        updateRowNumbers();
    });
});

// تحديث أرقام الصفوف
function updateRowNumbers() {
    let visibleIndex = 1;
    $('.table tbody tr:visible').each(function() {
        $(this).find('td:first').text(visibleIndex++);
    });
}
</script>

<script>
// دالة فتح modal التعديل
function editBeneficiary(id) {
    $.ajax({
        url: 'get_beneficiary.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const beneficiary = response.data;
                $('#edit_beneficiary_id').val(beneficiary.id);
                $('#edit_name').val(beneficiary.name);
                $('#edit_phone').val(beneficiary.phone);
                $('#edit_kimlik').val(beneficiary.kimlik_number);
                $('#edit_iban').val(beneficiary.iban);
                
                $('#editBeneficiaryModal').modal('show');
            } else {
                alert(response.message || 'حدث خطأ في جلب البيانات');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            alert('حدث خطأ في الاتصال بالخادم');
        }
    });
}

function updateBeneficiary() {
    const data = {
        id: $('#edit_beneficiary_id').val(),
        name: $('#edit_name').val(),
        phone: $('#edit_phone').val(),
        kimlik_number: $('#edit_kimlik').val(),
        iban: $('#edit_iban').val()
    };

    $.ajax({
        url: 'update_beneficiary.php',
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                location.reload(); // تحديث الصفحة بعد التعديل
            } else {
                alert(response.message || 'حدث خطأ أثناء التحديث');
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
            alert('حدث خطأ في الاتصال بالخادم');
        }
    });
}

function deleteBeneficiary(id) {
    if(confirm('هل أنت متأكد من حذف هذا المستفيد؟')) {
        $.ajax({
            url: 'delete_beneficiary.php',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    location.reload(); // تحديث الصفحة بعد الحذف
                } else {
                    alert(response.message || 'حدث خطأ أثناء الحذف');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('حدث خطأ في الاتصال بالخادم');
            }
        });
    }
}
</script>

<!-- دالة عرض صورة الكملك -->
<script>
function showImagePopup(imagePath) {
    // تحديث مصدر الصورة
    document.getElementById('kimlikImage').src = imagePath;
    
    // عرض Modal
    var imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
    imageModal.show();
}
</script>

<!-- التحقق من صحة البيانات قبل الإرسال -->
<script>
$(document).ready(function() {
    $('#paymentsForm').on('submit', function(e) {
        e.preventDefault();
        
        // التحقق من وجود تغييرات
        let hasChanges = false;
        $('input[name*="[amount]"]').each(function() {
            if ($(this).val() && !$(this).prop('disabled')) {
                hasChanges = true;
                return false;
            }
        });

        if (!hasChanges) {
            alert('لم يتم إدخال أي دفعات جديدة');
            return;
        }

        // التحقق من اكتمال البيانات
        let isValid = true;
        let incompleteRows = [];
        
        $('input[name*="[amount]"]').each(function() {
            if ($(this).val() && !$(this).prop('disabled')) {
                const row = $(this).closest('tr');
                const beneficiaryName = row.find('td:eq(1)').text(); // اسم المستفيد
                const sponsor = row.find('select[name*="[sponsor_name]"]').val();
                const type = row.find('select[name*="[payment_type]"]').val();
                
                if (!sponsor || !type) {
                    isValid = false;
                    incompleteRows.push(beneficiaryName);
                }
            }
        });

        if (!isValid) {
            alert('يرجى إكمال جميع البيانات المطلوبة للمستفيدين التاليين:\n' + incompleteRows.join('\n'));
            return;
        }

        // تأكيد التسجيل
        if (confirm('هل أنت متأكد من تسجيل الدفعات؟')) {
            this.submit();
        }
    });
});
</script>
