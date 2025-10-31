<?php
include('db_connection.php');

$year = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT) ?: date('Y');
$month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT) ?: date('n');

// جلب بيانات المستفيدين الخاصة بالشهر والسنة المحددة
$query_beneficiaries = "SELECT b.*, p.amount, p.payment_type 
                       FROM beneficiaries b 
                       LEFT JOIN payments p ON b.id = p.beneficiary_id 
                       AND YEAR(p.payment_date) = ? 
                       AND MONTH(p.payment_date) = ?
                       WHERE YEAR(b.created_at) = ? 
                       AND MONTH(b.created_at) = ?";
$stmt = $conn->prepare($query_beneficiaries);
$stmt->bind_param('iiii', $year, $month, $year, $month);
$stmt->execute();
$result_beneficiaries = $stmt->get_result();

// إضافة استعلام لحساب إجمالي المدفوعات
$query_total_payments = "SELECT SUM(amount) as total_amount 
                        FROM payments 
                        WHERE YEAR(payment_date) = ? 
                        AND MONTH(payment_date) = ?";
$stmt = $conn->prepare($query_total_payments);
$stmt->bind_param('ii', $year, $month);
$stmt->execute();
$result_payments = $stmt->get_result();
$total_payments = $result_payments->fetch_assoc()['total_amount'] ?: 0;

$title = "تفاصيل شهر " . date('F', mktime(0, 0, 0, $month, 10)) . " لسنة $year";
ob_start();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="css/styles.css">

<!-- تحسين هيكل الصفحة -->
<div class="dashboard-container">
    <!-- شريط التنقل العلوي -->
    <div class="top-bar">
        <div class="container-fluid">
            <div class="row align-items-center py-3">
                <div class="col">
                    <h1 class="page-title mb-0"><?php echo $title; ?></h1>
                </div>
                <div class="col-auto">
                    <div class="search-box">
                        <input type="text" id="search_input" placeholder="بحث عن مستفيد...">
                        <i class="fas fa-search"></i>
                    </div>
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

    <!-- المحتوى الرئيسي -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- بطاقة إحصائيات سريعة -->
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
                            <h3>إجمالي ��لمدفوعات</h3>
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

            <!-- جدول المستفيدين -->
            <div class="data-card">
                <div class="data-card-header">
                    <h2>قائمة لمستفيدين</h2>
                    <button class="btn btn-primary btn-add-beneficiary" type="button">
                        <i class="fas fa-plus"></i> إضافة مستفيد
                    </button>
                </div>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="col-number">#</th>
                                    <th class="col-name">اسم المستفيد</th>
                                    <th class="col-phone">رقم الهاتف</th>
                                    <th class="col-kimlik">رقم الكملك</th>
                                    <th class="col-iban">IBAN</th>
                                    <th class="col-image">صورة الكملك</th>
                                    <th class="col-payment">حالة الدفع</th>
                                    <th class="col-actions">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = $result_beneficiaries->fetch_assoc()) {
                                    echo "<tr id='beneficiary_row_" . $row['id'] . "'>";
                                    echo "<td class='number-cell'>" . $row['id'] . "</td>";
                                    echo "<td class='name-cell'>" . htmlspecialchars($row['name']) . "</td>";
                                    echo "<td class='number-cell'>" . htmlspecialchars($row['phone']) . "</td>";
                                    echo "<td class='number-cell'>" . htmlspecialchars($row['kimlik_number']) . "</td>";
                                    echo "<td class='number-cell iban-cell'>" . htmlspecialchars($row['iban']) . "</td>";

                                    echo "<td>";
                                    if (!empty($row['kimlik_image'])) {
                                        echo "<button class='btn btn-view-image' onclick='showImagePopup(\"uploads/" . htmlspecialchars($row['kimlik_image']) . "\")'>
                                                <i class='fas fa-image'></i> عرض الصورة
                                              </button>";
                                    } else {
                                        echo "<span class='no-image'><i class='fas fa-image-slash'></i> لا توجد صورة</span>";
                                    }
                                    echo "</td>";

                                    echo "<td id='payment_info_" . $row['id'] . "'>";
                                    if (!empty($row['amount'])) {
                                        echo "<span class='payment-status paid'>
                                                <i class='fas fa-check-circle'></i>
                                                تم الدفع: " . htmlspecialchars($row['amount']) . " 
                                                (" . htmlspecialchars($row['payment_type']) . ")
                                            </span>";
                                    } else {
                                        echo "<form id='payment_form_" . $row['id'] . "' class='payment-form'>
                                                <input type='hidden' name='beneficiary_id' value='" . $row['id'] . "'>
                                                <div class='payment-inputs'>
                                                    <input type='number' name='amount' class='form-control' placeholder='المبلغ' required>
                                                    <select name='payment_type' class='form-control' required>
                                                        <option value='cash'>نقدي</option>
                                                        <option value='bank'>تحويل بنكي</option>
                                                        <option value='not_physical'>تحويل عيني</option>
                                                    </select>
                                                    <button type='button' class='btn-action btn-pay' onclick='submitPayment(" . $row['id'] . ")'>
                                                        <i class='fas fa-check'></i> تأكيد الدفع
                                                    </button>
                                                </div>
                                            </form>";
                                    }
                                    echo "</td>";

                                    echo "<td>
                                        <div class='action-buttons'>
                                            <button class='btn-action btn-edit view-mode' onclick='enableEdit(" . $row['id'] . ")'>
                                                <i class='fas fa-edit'></i>
                                                <span>تعديل</span>
                                            </button>
                                            <button class='btn-action btn-save edit-mode' onclick='saveEdit(" . $row['id'] . ")' style='display:none;'>
                                                <i class='fas fa-save'></i>
                                                <span>حفظ</span>
                                            </button>
                                            <button class='btn-action btn-cancel edit-mode' onclick='cancelEdit(" . $row['id'] . ")' style='display:none;'>
                                                <i class='fas fa-times'></i>
                                                <span>إلغاء</span>
                                            </button>
                                            <button class='btn-action btn-delete view-mode' onclick='deleteBeneficiary(" . $row['id'] . ")'>
                                                <i class='fas fa-trash'></i>
                                                <span>حذف</span>
                                            </button>
                                        </div>
                                    </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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

<!-- Modal إضافة مستفيد -->
<div class="modal fade" id="addBeneficiaryModal" tabindex="-1" role="dialog" aria-labelledby="addBeneficiaryModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBeneficiaryModalLabel">إضافة مستفيد جديد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBeneficiaryForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>اسم المستفيد *</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>رقم الكملك</label>
                                <input type="text" name="kimlik_number" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>IBAN</label>
                                <input type="text" name="iban" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>صورة الكملك</label>
                                <input type="file" name="kimlik_image" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ المستفيد</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// معالجة إضافة مستفيد جديد
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];

    // استخدم المتغيرات من عنوان URL للحصول على السنة والشهر المحددين
    $year = intval($_GET['year']); // السنة الت يتم عرضها في الصفحة
    $month = intval($_GET['month']); // الشهر الذي يتم عرضه في الصفحة

    // افتراض يوم 1 للشهر والسنة المحددين
    $created_at = "$year-$month-01";

    $image = $_FILES['kimlik_image']['name']; // الحصول على اسم الصورة
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image);

    if (!empty($image)) { // إذا كان الصورة مرفوعة
        if (move_uploaded_file($_FILES['kimlik_image']['tmp_name'], $target_file)) {
            // إدخال بيانات المستفيد الجديد مع الصورة
            $query_insert_beneficiary = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, kimlik_image, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query_insert_beneficiary);
            $stmt->bind_param('ssssss', $name, $phone, $kimlik_number, $iban, $image, $created_at);
        }
    } else {
        // إدخال بيانات المستفيد الجديد بدون صورة
        $query_insert_beneficiary = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query_insert_beneficiary);
        $stmt->bind_param('sssss', $name, $phone, $kimlik_number, $iban, $created_at);
    }

    if ($stmt->execute()) {
        echo "<script>alert('تم إضافة المستفيد بنجاح!'); window.location.href='month_details.php?year=$year&month=$month';</script>";
    } else {
        echo "<script>alert('حدث خطأ أثناء إضافة المستفيد. حاول مرة أخرى.');</script>";
    }
}

$content = ob_get_clean();
include('header.php');
?>

<!-- تضمين المكتبات المطلوبة -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- الدفع -->
<script>
    function submitPayment(id) {
        var form = $("#payment_form_" + id);
        var formData = form.serialize();

        // إظهار مؤشر التحميل
        var payBtn = form.find('.btn-pay');
        payBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري التنفيذ...');

        $.ajax({
            type: "POST",
            url: "process_payment.php",
            data: formData,
            dataType: 'json', // تحديد نوع البيانات المتوقعة
            success: function (response) {
                if (response.status === 'success') {
                    // تحديث خلية الدفع
                    $("#payment_info_" + id).html(`
                        <span class="payment-status paid">
                            <i class="fas fa-check-circle"></i>
                            تم الدفع: ${response.amount} (${response.payment_type})
                        </span>
                    `);
                } else {
                    alert('خطأ: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('خطأ:', error);
                alert('حدث خطأ في الاتصال بالخادم');
            },
            complete: function () {
                // إعادة تفعيل الزر
                payBtn.prop('disabled', false).html('<i class="fas fa-check"></i> تأكيد الدفع');
            }
        });
    }
</script>

<!-- التعديل والحذف -->
<script>
    function enableEdit(id) {
        var row = $("#beneficiary_row_" + id);

        // إخفاء أزرار العرض وإظهار أزرار التعديل
        row.find('.view-mode').hide();
        row.find('.edit-mode').show();

        // تحويل النص إلى حقول تعديل
        row.find('td').each(function () {
            var cell = $(this);
            if (cell.find('input').length === 0 && !cell.find('.action-buttons').length) {
                var text = cell.text().trim();
                cell.data('original-text', text); // حفظ النص الأصلي
                cell.html('<input type="text" class="form-control" value="' + text + '">');
            }
        });
    }

    function startEdit(id) {
        var row = $("#beneficiary_row_" + id);

        // تغير زر التعديل إلى زر حفظ
        var editBtn = row.find('.btn-edit');
        editBtn.html('<i class="fas fa-save"></i> حفظ')
            .removeClass('btn-edit')
            .addClass('btn-save')
            .attr('onclick', 'saveEdit(' + id + ')');

        // تحويل الخلايا المسموح بتعديلها فقط
        row.find('td').each(function (index) {
            var cell = $(this);

            // تحويل فقط الخلايا من 1 إلى 4 إلى حقول تحرير
            if (index >= 1 && index <= 4) {
                var currentValue = cell.text().trim();
                cell.html(`<input type="text" class="form-control" value="${currentValue}">`);
            }
            // باقي الخلايا تبقى كما هي
        });
    }

    function saveEdit(id) {
        var row = $("#beneficiary_row_" + id);
        var formData = new FormData();

        // جمع البيانات المسموح بتعديلها فقط
        formData.append('id', id);
        formData.append('name', row.find('td:nth-child(2) input').val());
        formData.append('phone', row.find('td:nth-child(3) input').val());
        formData.append('kimlik_number', row.find('td:nth-child(4) input').val());
        formData.append('iban', row.find('td:nth-child(5) input').val());

        // إظهار مؤشر التحميل
        var saveBtn = row.find('.btn-save');
        saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

        $.ajax({
            url: 'update_beneficiary.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert('خطأ: ' + response.message);
                }
            },
            error: function () {
                alert('حدث خطأ في الاتصال بالخادم');
            },
            complete: function () {
                saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> حفظ');
            }
        });
    }

    function cancelEdit(id) {
        var row = $("#beneficiary_row_" + id);

        // استعادة النص الأصلي
        row.find('td').each(function () {
            var cell = $(this);
            if (cell.find('input').length > 0) {
                cell.html(cell.data('original-text'));
            }
        });

        // إعادة أزرار العرض
        row.find('.edit-mode').hide();
        row.find('.view-mode').show();
    }

    function deleteBeneficiary(id) {
        if (confirm('هل أنت متأكد من الحذف؟')) {
            $.ajax({
                type: "POST",
                url: "delete_beneficiary.php", // صفحة معالجة الحذف
                data: { id: id },
                success: function (response) {
                    if (response == "success") {
                        // حذف الصف م الجدول
                        $("#beneficiary_row_" + id).remove();
                    } else {
                        alert("حدث خأ أثناء الحذف.");
                    }
                },
                error: function () {
                    alert("حدث خطأ أثناء الاتصال بالخادم.");
                }
            });
        }
    }

</script>

<!-- عرض الصورة -->
<script>
    function showImagePopup(imageUrl) {
        $('#kimlikImage').attr('src', imageUrl);
        $('#imageModal').modal('show');
    }
</script>

<!-- البحث -->
<script>
    $(document).ready(function () {
        // وظيفة البحث المباشر
        $("#search_input").on('keyup', function () {
            var value = $(this).val().toLowerCase();

            $(".table tbody tr").filter(function () {
                // البحث في جميع خلايا الصف ما عدا خلية الصورة والإجراءات
                var rowText = '';
                $(this).find('td').each(function (index) {
                    // تجميع النص من الخلايا المهمة فقط (الاسم، رقم الهاتف، رقم الكملك، IBAN)
                    if (index >= 1 && index <= 4) {
                        rowText += $(this).text().toLowerCase() + ' ';
                    }
                });

                // إظهار/إخفاء الصف بناءً على نتيجة البحث
                $(this).toggle(rowText.indexOf(value) > -1);
            });

            // تحديث أرقام الصفوف المرئية
            updateVisibleRowNumbers();
        });

        // وظيفة تحديث أرقام الصفوف المرئية
        function updateVisibleRowNumbers() {
            var visibleIndex = 1;
            $(".table tbody tr:visible").each(function () {
                $(this).find('td:first').text(visibleIndex++);
            });
        }
    });
</script>

<!-- إضافة JavaScript للتعامل مع النموذج -->
<script>
    $(document).ready(function () {
        // معالجة النموذج
        $('#addBeneficiaryForm').on('submit', function (e) {
            e.preventDefault();

            // التحقق فقط من وجود الاسم
            var name = $(this).find('input[name="name"]').val();
            if (!name) {
                alert('الرجاء إدخال اسم المستفيد');
                return false;
            }

            var formData = new FormData(this);
            formData.append('year', <?php echo $year; ?>);
            formData.append('month', <?php echo $month; ?>);

            // إظهار مؤشر التحميل
            var submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> جاري الحفظ...');

            $.ajax({
                url: 'add_beneficiary.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    submitBtn.prop('disabled', false).html('حفظ المستفيد');

                    if (response.status === 'success') {
                        alert('تم إضافة المستفيد بنجاح');
                        $('#addBeneficiaryModal').modal('hide');
                        location.reload();
                    } else {
                        alert('خطأ: ' + (response.message || 'حدث خطأ غير معروف'));
                    }
                },
                error: function (xhr, status, error) {
                    submitBtn.prop('disabled', false).html('حفظ المستفيد');
                    console.error('خطأ Ajax:', error);
                    alert('حدث خطأ في الاتصال بالخادم');
                }
            });
        });

        // فتح Modal
        $('.btn-add-beneficiary').on('click', function () {
            $('#addBeneficiaryModal').modal('show');
        });
    });
</script>

<!-- تحويل البيانات إلى تنسيق Excel -->
<script>
    function exportToExcel() {
        // تحويل البيانات إلى تنسيق Excel
        let table = document.querySelector('.table');
        let wb = XLSX.utils.table_to_book(table, { sheet: "المستفيدون" });
        XLSX.writeFile(wb, `المستفيدون_${<?php echo $year; ?>}_${<?php echo $month; ?>}.xlsx`);
    }

    function exportToPDF() {
        // إنشاء نسخة من الجدول للتصدير
        const tableClone = $('.table').clone();
        
        // إزالة أعمدة الإجراءات والصور
        tableClone.find('.col-actions, .col-image').remove();
        tableClone.find('td:nth-child(6), td:nth-child(8)').remove();
        
        // تحسين تنسيق خلايا الدفع
        tableClone.find('td').each(function() {
            const cell = $(this);
            if (cell.find('.payment-status').length) {
                const paymentText = cell.find('.payment-status').text().trim();
                cell.html(paymentText);
            }
        });

        // إعداد خيارات PDF
        const opt = {
            margin: 1,
            filename: `المستفيدون_${<?php echo $year; ?>}_${<?php echo $month; ?>}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                useCORS: true,
                logging: false,
                direction: 'rtl'
            },
            jsPDF: { 
                unit: 'in', 
                format: 'a4', 
                orientation: 'landscape',
                language: 'ar'
            },
            pagebreak: { mode: 'avoid-all' }
        };

        // إنشاء عنصر مؤقت للتصدير
        const element = document.createElement('div');
        element.style.direction = 'rtl';
        
        // إضافة ترويسة للتقرير
        const header = document.createElement('div');
        header.style.textAlign = 'center';
        header.style.margin = '20px 0';
        header.style.fontFamily = 'Arial, sans-serif';
        header.innerHTML = `
            <h2 style="margin-bottom: 10px;">تقرير المستفيدين</h2>
            <p style="margin-bottom: 5px;">الشهر: ${getMonthName(<?php echo $month; ?>)}</p>
            <p>السنة: ${<?php echo $year; ?>}</p>
        `;
        element.appendChild(header);

        // إضافة الإحصائيات
        const stats = document.createElement('div');
        stats.style.marginBottom = '20px';
        stats.style.display = 'flex';
        stats.style.justifyContent = 'space-around';
        stats.style.fontFamily = 'Arial, sans-serif';
        stats.innerHTML = `
            <div>
                <strong>إجمالي المستفيدين:</strong> 
                ${$('.stat-card:eq(0) h2').text()}
            </div>
            <div>
                <strong>إجمالي المدفوعات:</strong> 
                ${$('.stat-card:eq(1) h2').text()}
            </div>
            <div>
                <strong>نسبة الدفع:</strong> 
                ${$('.stat-card:eq(3) h2').text()}
            </div>
        `;
        element.appendChild(stats);

        // إضافة الجدول المعدل
        element.appendChild(tableClone[0]);

        // تطبيق التنسيقات على الجدول
        const style = document.createElement('style');
        style.textContent = `
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-top: 20px;
                font-family: Arial, sans-serif;
            }
            th, td { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: right;
            }
            th { 
                background-color: #f8f9fa;
                font-weight: bold;
            }
            tr:nth-child(even) { 
                background-color: #f9f9f9; 
            }
        `;
        element.appendChild(style);

        // إظهار مؤشر التحميل
        const loadingDiv = document.createElement('div');
        loadingDiv.style.position = 'fixed';
        loadingDiv.style.top = '50%';
        loadingDiv.style.left = '50%';
        loadingDiv.style.transform = 'translate(-50%, -50%)';
        loadingDiv.style.padding = '20px';
        loadingDiv.style.background = 'rgba(0,0,0,0.7)';
        loadingDiv.style.color = 'white';
        loadingDiv.style.borderRadius = '10px';
        loadingDiv.style.zIndex = '9999';
        loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري إنشاء PDF...';
        document.body.appendChild(loadingDiv);

        // تنفيذ التصدير
        html2pdf().from(element).set(opt).save()
            .then(() => {
                document.body.removeChild(loadingDiv);
            })
            .catch(err => {
                console.error('PDF Export Error:', err);
                alert('حدث خطأ أثناء إنشاء PDF');
                document.body.removeChild(loadingDiv);
            });
    }

    // دالة مساعدة للحصول على اسم الشهر بالعربية
    function getMonthName(month) {
        const months = [
            'يناير', 'فبراير', 'مارس', 'إبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
        ];
        return months[month - 1];
    }

    // فلترة البيانات
    $('#payment_filter, #payment_type_filter').on('change', function () {
        let paymentStatus = $('#payment_filter').val();
        let paymentType = $('#payment_type_filter').val();

        $('.table tbody tr').each(function () {
            let row = $(this);
            let showRow = true;

            if (paymentStatus) {
                let isPaid = row.find('.payment-status.paid').length > 0;
                if (paymentStatus === 'paid' && !isPaid) showRow = false;
                if (paymentStatus === 'unpaid' && isPaid) showRow = false;
            }

            if (paymentType && showRow) {
                let rowPaymentType = row.find('.payment-status').text().includes(paymentType);
                if (!rowPaymentType) showRow = false;
            }

            row.toggle(showRow);
        });

        updateVisibleRowNumbers();
    });
</script>

<!-- إضافة رقم الصف -->
<script>
function updateRowNumbers() {
    let visibleIndex = 1;
    $('.table tbody tr:visible').each(function() {
        $(this).find('td:first').text(visibleIndex++);
    });
}

$(document).ready(function() {
    // تحديث الأرقام عند تحميل الصفحة
    updateRowNumbers();

    // تحديث الأرقام بعد أي عملية بحث
    $("#search_input").on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $(".table tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        updateRowNumbers();
    });

    // تحديث الأرقام بعد أي عملية فلترة
    $('#payment_filter, #payment_type_filter').on('change', function() {
        // ... existing filter code ...
        updateRowNumbers();
    });
});

// تحديث الأرقام بعد حذف أي صف
function deleteBeneficiary(id) {
    if (confirm('هل أنت متأكد من الحذف؟')) {
        $.ajax({
            type: "POST",
            url: "delete_beneficiary.php",
            data: { id: id },
            success: function(response) {
                if (response == "success") {
                    $("#beneficiary_row_" + id).remove();
                    updateRowNumbers(); // تحديث الأرقام بعد الحذف
                } else {
                    alert("حدث خطأ أثناء الحذف.");
                }
            },
            error: function() {
                alert("حدث خطأ أثناء الاتصال بالخادم.");
            }
        });
    }
}
</script>

<style>
/* تحسين عرض الجدول */
.data-card {
    margin: 0 20px;
}

.table {
    width: 100%;
    table-layout: auto;
    margin: 0;
    border-collapse: collapse;
}

/* تعديل عرض الأعمدة بنسب مئوية */
.col-number { width: 3%; }
.col-name { width: 17%; }
.col-phone { width: 10%; }
.col-kimlik { width: 10%; }
.col-iban { width: 22%; }
.col-image { width: 10%; }
.col-payment { width: 15%; }
.col-actions { width: 13%; }

/* تنسيق الخلايا */
.table th, .table td {
    padding: 8px;
    text-align: right;
    vertical-align: middle;
    border: 1px solid #dee2e6;
    font-size: 14px;
}

/* تنسيق خاص للـ IBAN */
.iban-cell {
    direction: ltr;
    text-align: left;
    font-family: monospace;
    font-size: 13px;
}

/* تنسيق الأزرار */
.action-buttons {
    display: flex;
    gap: 4px;
}

.btn-action {
    padding: 4px 8px;
    font-size: 12px;
}

/* تنسيق حالة الدفع */
.payment-status {
    font-size: 13px;
    white-space: nowrap;
}

/* تحسين مظهر الصفوف */
.table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: #f3f3f3;
}
</style>