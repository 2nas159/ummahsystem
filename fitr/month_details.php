<?php
include('db_connection.php');

$year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int) $_GET['year'] : date('Y');

// جلب بيانات المستفيدين في السنة المحددة
$query_beneficiaries = "SELECT b.*, p.id as payment_id, p.amount, p.payment_date, p.payment_type
                        FROM beneficiaries b 
                        LEFT JOIN payments p ON b.id = p.beneficiary_id 
                        AND YEAR(p.payment_date) = ?";
$stmt = $conn->prepare($query_beneficiaries);
$stmt->bind_param('i', $year);
$stmt->execute();
$result_beneficiaries = $stmt->get_result();

$title = "تفاصيل سنة " . $year;

ob_start();
?>

<div class="container page-section">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="page-title m-0">تفاصيل سنة <?php echo $year; ?></h2>
        <a href="index.php" class="btn btn-outline-primary btn-sm btn-icon"><i class="bi bi-arrow-right"></i><span>رجوع</span></a>
    </div>

    <!-- زر إضافة مستفيد جديد -->
    <div class="text-right mb-4">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBeneficiaryModal">
            <i class="fas fa-user-plus"></i> إضافة مستفيد جديد
        </button>
    </div>

    <div class="mb-3">
        <input type="text" id="searchBeneficiaries" class="form-control" placeholder="ابحث عن مستفيد">
    </div>

    <!-- نافذة منبثقة لإضافة مستفيد جديد -->
    <div class="modal fade" id="addBeneficiaryModal" tabindex="-1" aria-labelledby="addBeneficiaryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBeneficiaryModalLabel">إضافة مستفيد جديد</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addBeneficiaryForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">رقم الهاتف</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="mb-3">
                            <label for="kimlik_number" class="form-label">رقم الكملك</label>
                            <input type="text" class="form-control" id="kimlik_number" name="kimlik_number">
                        </div>
                        <div class="mb-3">
                            <label for="iban" class="form-label">رقم الـ IBAN</label>
                            <input type="text" class="form-control" id="iban" name="iban">
                        </div>
                        <div class="mb-3">
                            <label for="kimlik_photo" class="form-label">صورة الكملك</label>
                            <input type="file" class="form-control" id="kimlik_photo" name="kimlik_photo" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">إضافة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-elevated">
        <div class="card-body p-0">
        <div class="table-responsive">
        <form id="paymentsForm" method="POST" class="m-0">
            <table class="table table-striped table-modern m-0 align-middle">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">الاسم</th>
                        <th class="text-center">رقم الهاتف</th>
                        <th class="text-center">رقم الكملك</th>
                        <th class="text-center">رقم الـ IBAN</th>
                        <th class="text-center">المبلغ المدفوع</th>
                        <th class="text-center">نوع التحويل</th>
                        <th class="text-center">عرض الكملك</th>
                        <th class="text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody id="beneficiaries-table">
                    <?php while ($row = $result_beneficiaries->fetch_assoc()) { ?>
                        <tr id="beneficiary-row-<?php echo $row['id']; ?>">
                            <td class="text-center"></td>
                            <td class="text-center"><?php echo $row['name']; ?></td>
                            <td class="text-center"><?php echo $row['phone']; ?></td>
                            <td class="text-center"><?php echo $row['kimlik_number']; ?></td>
                            <td class="text-center"><?php echo $row['iban']; ?></td>
                            <td class="text-center">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="number" name="payments[<?php echo $row['id']; ?>][amount]" class="form-control" placeholder="المبلغ" 
                                    <?php echo $row['amount'] ? 'disabled value="' . $row['amount'] . '"' : ''; ?>>
                                    <?php if ($row['amount'] && $row['payment_id']): ?>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="deletePayment(<?php echo $row['payment_id']; ?>, <?php echo $row['id']; ?>)"
                                                title="حذف الدفعة">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <select name="payments[<?php echo $row['id']; ?>][payment_type]" class="form-select" 
                                        <?php echo $row['amount'] ? 'disabled' : ''; ?>>
                                    <option value="">اختر نوع الدفعية</option>
                                    <option value="تحويل بنكي" <?php echo $row['payment_type'] == 'تحويل بنكي' ? 'selected' : ''; ?>>تحويل بنكي</option>
                                    <option value="نقدي" <?php echo $row['payment_type'] == 'نقدي' ? 'selected' : ''; ?>>نقدي</option>
                                    <option value="حبوب" <?php echo $row['payment_type'] == 'حبوب' ? 'selected' : ''; ?>>حبوب</option>
                                </select>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-secondary btn-icon" onclick="showImage('<?php echo $row['kimlik_photo']; ?>')">
                                    <i class="bi bi-eye"></i><span>الكملك</span>
                                </button>
                            </td>
                            <td class="text-center text-nowrap">
                                <button type="button" class="btn btn-warning btn-sm btn-icon" onclick="editBeneficiary(<?php echo $row['id']; ?>)">
                                    <i class="bi bi-pencil-square"></i><span>تعديل</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-icon ms-1" onclick="deleteBeneficiary(<?php echo $row['id']; ?>)">
                                    <i class="bi bi-trash"></i><span>حذف</span>
                                </button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </form>
        </div>
        </div>
    </div>
    <div class="mt-3"><button type="submit" form="paymentsForm" class="btn btn-primary w-100 btn-icon"><i class="bi bi-check2-circle"></i><span>تسجيل جميع الدفعات</span></button></div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">عرض صورة الكملك</h5>
            </div>
            <div class="modal-body">
                <img src="" class="img-fluid" alt="صورة الكملك">
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- ADD BENEFICIARIES -->
<script>
$(document).ready(function() {
    $('#addBeneficiaryForm').on('submit', function(e) {
        e.preventDefault(); // منع الإرسال التقليدي للنموذج

        var formData = new FormData(this);

        $.ajax({
            url: 'add_beneficiary.php', // ملف PHP الذي يعالج إضافة المستفيد
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                console.log('Response:', response); // عرض الاستجابة في الـ console

                if (response.success) {
                    location.reload(); // إعادة تحميل الصفحة في حالة النجاح
                } else {
                    alert('Error: ' + (response.error || 'حدث خطأ غير معروف')); // التعامل مع الخطأ
                }
            },
            error: function(xhr, status, error) {
                alert('An error occurred: ' + error); // طباعة الخطأ إذا حدث
            }
        });
    });
});
</script>

<!-- EDIT BENEFICIARIES -->
<script>
    function editBeneficiary(beneficiaryId) {
    var row = $('#beneficiary-row-' + beneficiaryId);
    
    // Replace text with input fields
    row.find('td').each(function(index, td) {
        if (index === 1) {
            var name = $(td).text();
            $(td).html('<input type="text" class="form-control" value="' + name + '">');
        } else if (index === 2) {
            var phone = $(td).text();
            $(td).html('<input type="text" class="form-control" value="' + phone + '">');
        } else if (index === 3) {
            var kimlik = $(td).text();
            $(td).html('<input type="text" class="form-control" value="' + kimlik + '">');
        } else if (index === 4) {
            var iban = $(td).text();
            $(td).html('<input type="text" class="form-control" value="' + iban + '">');
        }
    });

    // Replace the "Edit" button with "Save"
    row.find('.btn-warning').replaceWith('<button type="button" class="btn btn-success btn-sm btn-icon" onclick="saveBeneficiary(' + beneficiaryId + ')"><i class="bi bi-check2-circle"></i><span>حفظ</span></button>');
}

function saveBeneficiary(beneficiaryId) {
    var row = $('#beneficiary-row-' + beneficiaryId);
    var updatedData = {
        id: beneficiaryId,
        name: row.find('td:eq(1) input').val(),
        phone: row.find('td:eq(2) input').val(),
        kimlik_number: row.find('td:eq(3) input').val(),
        iban: row.find('td:eq(4) input').val()
    };

    $.ajax({
        url: 'edit_beneficiary.php', // Your PHP script for updating the beneficiary
        type: 'POST',
        data: updatedData,
        dataType: 'json',  // Expecting JSON response from the server
        success: function(response) {
            if (response.success) {
                // Update the table with the new data
                row.find('td:eq(1)').text(updatedData.name);
                row.find('td:eq(2)').text(updatedData.phone);
                row.find('td:eq(3)').text(updatedData.kimlik_number);
                row.find('td:eq(4)').text(updatedData.iban);

                // Replace "Save" with "Edit"
                row.find('.btn-success').replaceWith('<button type="button" class="btn btn-warning btn-sm btn-icon" onclick="editBeneficiary(' + beneficiaryId + ')"><i class="bi bi-pencil-square"></i><span>تعديل</span></button>');
            } else {
                alert('Error: ' + response.error);
            }
        },
        error: function(xhr, status, error) {
            alert('An error occurred: ' + error);  // Handle any AJAX errors
        }
    });
}
</script>

<!-- DELETE BENEFICIARIES -->
<script>
    function deleteBeneficiary(beneficiaryId) {
    if (confirm('هل أنت متأكد أنك تريد حذف هذا المستفيد؟')) {
        $.ajax({
            url: 'delete_beneficiary.php',  // Your PHP script for deletion
            type: 'POST',
            data: { id: beneficiaryId },
            dataType: 'json',  // Expecting JSON response from the server
            success: function(response) {
                if (response.success) {
                    // Remove the row from the table
                    $('#beneficiary-row-' + beneficiaryId).remove();

                    // Update row numbers after deletion
                    updateRowNumbers();
                } else {
                    alert('Error: ' + response.error);
                }
            }
        });
    }
}

function deletePayment(paymentId, beneficiaryId) {
    if(confirm('هل أنت متأكد من حذف هذه الدفعة؟ سيتم حذف المبلغ ونوع الدفع فقط، ولن يتم حذف بيانات المستفيد.')) {
        $.ajax({
            url: 'delete_payment.php',
            type: 'POST',
            data: { 
                payment_id: paymentId,
                beneficiary_id: beneficiaryId,
                year: <?php echo $year; ?>
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // إعادة تفعيل الحقول
                    const row = $('#beneficiary-row-' + beneficiaryId);
                    row.find('input[name*="[amount]"]').prop('disabled', false).val('');
                    row.find('select[name*="[payment_type]"]').prop('disabled', false).val('');
                    // إزالة زر الحذف
                    row.find('button[onclick*="deletePayment"]').remove();
                    alert('تم حذف الدفعة بنجاح');
                } else {
                    alert(response.message || 'حدث خطأ أثناء حذف الدفعة');
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

<!-- NUMBERING BENEFICIARIES -->
<script>
    $(document).ready(function() {
    updateRowNumbers(); // Call this function on page load to update row numbers
    });

    function updateRowNumbers() {
        $('#beneficiaries-table tr').each(function(index, row) {
            $(row).find('td:eq(0)').text(index + 1);  // Update the first <td> in each row with the index
        });
    }
</script>

<!-- SUBMIT PAYMENTS -->
<script>
$('#paymentsForm').on('submit', function(e) {
    e.preventDefault(); // منع الإرسال التقليدي للنموذج

    var paymentsData = $(this).serialize(); // جمع بيانات النموذج
    var year = <?php echo $year; ?>; // إضافة السنة إلى البيانات
    paymentsData += '&year=' + year;

    $.ajax({
        url: 'submit_payments.php', // ملف PHP الذي يعالج المدفوعات
        type: 'POST',
        data: paymentsData,
        dataType: 'json', // تحديد نوع الاستجابة كـ JSON
        success: function(response) {
            if (response.success) {
                // تعطيل الحقول فقط للمستفيدين الذين تم حفظ دفعاتهم بنجاح
                if (response.saved_ids && response.saved_ids.length > 0) {
                    response.saved_ids.forEach(function(beneficiaryId) {
                        // تعطيل حقول المبلغ ونوع الدفعية للمستفيد المحدد
                        $('input[name="payments[' + beneficiaryId + '][amount]"]').prop('disabled', true);
                        $('select[name="payments[' + beneficiaryId + '][payment_type]"]').prop('disabled', true);
                    });
                }
                
                var message = response.message || 'تم تسجيل الدفعات بنجاح';
                if (response.partial_errors && response.partial_errors.length > 0) {
                    message += '\n\nأخطاء:\n' + response.partial_errors.join('\n');
                    // إذا كانت هناك أخطاء جزئية، لا نعيد تحميل الصفحة فوراً
                    alert(message);
                } else {
                    // إذا نجحت جميع الدفعات، نعرض الرسالة ونعيد التحميل بعد ثانية
                    alert(message);
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
            } else {
                var errorMsg = response.error || 'خطأ غير معروف';
                if (response.errors && response.errors.length > 0) {
                    errorMsg += '\n\n' + response.errors.join('\n');
                }
                alert('حدث خطأ: ' + errorMsg);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', xhr.responseText);
            var errorMessage = 'حدث خطأ في الاتصال';
            try {
                var errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.error) {
                    errorMessage = errorResponse.error;
                }
            } catch (e) {
                errorMessage = error;
            }
            alert('حدث خطأ: ' + errorMessage);
        }
    });
});
</script>

<!-- SHOW IMAGE POPUP -->
<script>
    function showImage(imagePath) {
    // تحديد مصدر الصورة وتحديث الرابط الخاص بها
    $('#imageModal img').attr('src', imagePath);

    // عرض النافذة المنبثقة
    $('#imageModal').modal('show');
}
</script>

<!-- SEARCH BENEFICIARIES -->
<script>
    $(document).ready(function() {
    $('#searchBeneficiaries').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#beneficiaries-table tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>