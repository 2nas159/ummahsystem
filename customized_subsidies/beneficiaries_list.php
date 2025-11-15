<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['beneficiary_id'])) {
    $id = $_POST['beneficiary_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];
    $monthly_amount = $_POST['monthly_amount'];

    // Update query
    $query = "UPDATE beneficiaries SET name = ?, phone = ?, kimlik_number = ?, iban = ?, monthly_amount = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssdi', $name, $phone, $kimlik_number, $iban, $monthly_amount, $id);
    $stmt->execute();

    // Redirect after updating
    header("Location: beneficiaries_list.php");
    exit();
}

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

<div class="card card-elevated">
<div class="card-body p-0">
<div class="table-responsive">
<table style="text-align: center;" class="table table-striped table-modern m-0">
    <thead>
        <tr>
            <th class="text-center">الاسم</th>
            <th class="text-center">رقم الهاتف</th>
            <th class="text-center">رقم الكملك</th>
            <th class="text-center">رقم الـ IBAN</th>
            <th class="text-center">المبلغ</th>
            <th class="text-center">صورة</th>
            <th class="text-center">تعديل</th>
            <th class="text-center">حذف</th>
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
                <td class="text-center"><?php echo $row['name']; ?></td>
                <td class="text-center"><?php echo $row['phone']; ?></td>
                <td class="text-center"><?php echo $row['kimlik_number']; ?></td>
                <td class="text-center"><?php echo $row['iban']; ?></td>
                <td class="text-center"><?php echo $row['monthly_amount']; ?></td>
                <td class="text-center"><button class="btn btn-secondary"
                        onclick="showImage('<?php echo $row['kimlik_photo']; ?>')">الكملك</button></td>
                <td class="text-center">
                    <button class="btn btn-primary" onclick="openEditModal(<?php echo $row['id']; ?>)">تعديل</button>
                    </td>
                    <td class="text-center">
                    <button class="btn btn-danger" onclick="deleteBeneficiary(<?php echo $row['id']; ?>)">حذف</button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- Modal for Edit -->
<div id="editModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل بيانات المستفيد</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST">
                    <input type="hidden" name="beneficiary_id" id="beneficiary_id">
                    <div class="form-group">
                        <label>الاسم:</label>
                        <input type="text" name="name" id="edit_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>رقم الهاتف:</label>
                        <input type="text" name="phone" id="edit_phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>رقم الكملك:</label>
                        <input type="text" name="kimlik_number" id="edit_kimlik_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>رقم الـ IBAN:</label>
                        <input type="text" name="iban" id="edit_iban" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>المبلغ الشهري:</label>
                        <input type="number" name="monthly_amount" id="edit_monthly_amount" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">تأكيد</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Modal for Image -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>
    function openEditModal(id) {
        // Clear any previous data from the modal
        $('#beneficiary_id').val('');
        $('#edit_name').val('');
        $('#edit_phone').val('');
        $('#edit_kimlik_number').val('');
        $('#edit_iban').val('');
        $('#edit_monthly_amount').val('');

        // Use AJAX to fetch beneficiary data
        $.ajax({
            url: 'get_beneficiary.php', // URL to the script that fetches data
            type: 'GET',
            data: { id: id },
            success: function (response) {
                let data = JSON.parse(response);
                // Populate the form fields with the fetched data
                $('#beneficiary_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_phone').val(data.phone);
                $('#edit_kimlik_number').val(data.kimlik_number);
                $('#edit_iban').val(data.iban);
                $('#edit_monthly_amount').val(data.monthly_amount);
                // Show the modal
                $('#editModal').modal('show');
            },
            error: function (xhr, status, error) {
                console.log('Error: ' + error); // Log any error for debugging
            }
        });
    }


    function deleteBeneficiary(id) {
        if (confirm("هل أنت متأكد أنك تريد حذف هذا المستفيد؟")) {
            window.location.href = 'delete_beneficiary.php?id=' + id;
        }
    }

    function showImage(photo) {
        let imgUrl = 'uploads/' + photo;
        $('#imageModal img').attr('src', imgUrl);
        $('#imageModal').modal('show');
    }

</script>