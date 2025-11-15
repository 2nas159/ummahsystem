<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];
    $monthly_amount = $_POST['monthly_amount'];

    // Check if a file was uploaded
    if (!empty($_FILES['kimlik_photo']['name'])) {
        $kimlik_photo = $_FILES['kimlik_photo']['name'];
        $target_dir = "uploads/"; // Directory where the file will be saved
        $target_file = $target_dir . basename($_FILES["kimlik_photo"]["name"]);
        
        // Move the uploaded file to the desired directory
        if (!move_uploaded_file($_FILES["kimlik_photo"]["tmp_name"], $target_file)) {
            // Handle upload error
            echo "Sorry, there was an error uploading your file.";
            exit;
        }
    } else {
        // If no file is uploaded, set the file name to NULL
        $kimlik_photo = NULL;
    }

    $query = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, monthly_amount, kimlik_photo, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssds', $name, $phone, $kimlik_number, $iban, $monthly_amount, $kimlik_photo);
    $stmt->execute();

    header("Location: beneficiaries_list.php");
}

$title = "إضافة مستفيد جديد";
ob_start();
?>

<div class="container page-section">
    <h2 class="page-title mb-3">إضافة مستفيد جديد</h2>
    <div class="card card-elevated">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">الاسم</label>
                    <input type="text" name="name" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">رقم الهاتف</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">رقم الكملك</label>
                    <input type="text" name="kimlik_number" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">رقم الـ IBAN</label>
                    <input type="text" name="iban" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">المبلغ الشهري</label>
                    <input type="number" name="monthly_amount" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">صورة الكملك (اختياري)</label>
                    <input type="file" name="kimlik_photo" id="kimlik_photo" class="form-control">
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-icon"><i class="bi bi-check2"></i><span>إضافة</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>
