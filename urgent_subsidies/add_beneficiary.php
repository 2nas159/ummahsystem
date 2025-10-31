<?php
include('db_connection.php');

header('Content-Type: application/json; charset=utf-8');

try {
    // التحقق من وجود الاسم فقط
    if(empty($_POST['name'])) {
        throw new Exception('اسم المستفيد مطلوب');
    }

    $name = $_POST['name'];
    $phone = $_POST['phone'] ?? '';
    $kimlik_number = $_POST['kimlik_number'] ?? '';
    $iban = $_POST['iban'] ?? '';
    $year = $_POST['year'];
    $month = $_POST['month'];
    
    $created_at = date("Y-m-d", strtotime("$year-$month-01"));

    // معالجة الصورة
    $kimlik_image = '';
    if(isset($_FILES['kimlik_image']) && $_FILES['kimlik_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['kimlik_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(!in_array($ext, $allowed)) {
            throw new Exception('نوع الملف غير مسموح به');
        }

        $newFileName = uniqid() . '.' . $ext;
        $uploadPath = 'uploads/' . $newFileName;
        
        if(move_uploaded_file($_FILES['kimlik_image']['tmp_name'], $uploadPath)) {
            $kimlik_image = $newFileName;
        }
    }

    $query = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, kimlik_image, created_at) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssss', $name, $phone, $kimlik_number, $iban, $kimlik_image, $created_at);
    
    if($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'تم إضافة المستفيد بنجاح'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('فشل في إضافة المستفيد');
    }

} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();
?>
