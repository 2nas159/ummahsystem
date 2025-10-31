<?php
include('db_connection.php');

// عرض جميع الأخطاء لتسهيل عملية التصحيح
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect the form data
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $kimlik_number = $_POST['kimlik_number'];
    $iban = $_POST['iban'];
    
    // Handle the kimlik photo upload
    $kimlik_photo = '';
    if (isset($_FILES['kimlik_photo']) && $_FILES['kimlik_photo']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["kimlik_photo"]["name"]);
        move_uploaded_file($_FILES["kimlik_photo"]["tmp_name"], $target_file);
        $kimlik_photo = $target_file;
    }

    // Insert beneficiary into the database
    $query = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, kimlik_photo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $name, $phone, $kimlik_number, $iban, $kimlik_photo);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        
        // Prepare the HTML for the new row to be returned to the frontend
        $response = [
            'success' => true,
            'html' => "
                <tr id='beneficiary-row-$new_id'>
                    <td class='text-center'></td>
                    <td class='text-center'>$name</td>
                    <td class='text-center'>$phone</td>
                    <td class='text-center'>$kimlik_number</td>
                    <td class='text-center'>$iban</td>
                    <td class='text-center'><input type='number' name='payments[$new_id][amount]' class='form-control' placeholder='المبلغ'></td>
                    <td class='text-center'>
                        <select name='payments[$new_id][payment_type]' class='form-select'>
                            <option value=''>اختر نوع الدفعية</option>
                            <option value='تحويل بنكي'>تحويل بنكي</option>
                            <option value='نقدي'>نقدي</option>
                        </select>
                    </td>
                    <td class='text-center'>
                        <button type='button' class='btn btn-outline-secondary' onclick='showImage(\"$kimlik_photo\")'><i class='fas fa-eye'></i> الكملك</button>
                    </td>
                    <td class='text-center'>
                        <button type='button' class='btn btn-primary btn-sm' onclick='editBeneficiary($new_id)'><i class='fas fa-edit'></i> تعديل</button>
                        <button type='button' class='btn btn-danger btn-sm' onclick='deleteBeneficiary($new_id)'><i class='fas fa-trash'></i> حذف</button>
                    </td>
                </tr>"
        ];
    } else {
        $response = ['success' => false, 'error' => 'Error adding beneficiary.'];
    }

    // تأكد من إرجاع استجابة JSON صحيحة
$response = array("success" => true);
header('Content-Type: application/json');
echo json_encode($response);
}

