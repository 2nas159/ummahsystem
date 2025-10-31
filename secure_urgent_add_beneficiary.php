<?php
/**
 * Secure Add Beneficiary for Urgent Subsidies
 * Replaces urgent_subsidies/add_beneficiary.php with secure implementation
 */

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Security.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/FileUpload.php';

// Check if user is logged in
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Get database configuration
$config = require __DIR__ . '/../config/database.php';
$urgentConfig = $config;
$urgentConfig['database'] = 'u850876726_urgent';

$db = Database::getInstance($urgentConfig);
$db->selectDatabase('u850876726_urgent');

// Set content type for JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        throw new Exception('رمز الأمان غير صحيح');
    }
    
    // Sanitize inputs
    $name = Security::sanitizeInput($_POST['name'] ?? '');
    $phone = Security::sanitizeInput($_POST['phone'] ?? '');
    $kimlik_number = Security::sanitizeInput($_POST['kimlik_number'] ?? '');
    $iban = Security::sanitizeInput($_POST['iban'] ?? '');
    $year = Security::sanitizeInput($_POST['year'] ?? '');
    $month = Security::sanitizeInput($_POST['month'] ?? '');
    
    // Validate inputs
    if (empty($name)) {
        throw new Exception('اسم المستفيد مطلوب');
    }
    
    if (!empty($phone) && !Security::validatePhone($phone)) {
        throw new Exception('رقم الهاتف غير صحيح');
    }
    
    if (empty($year) || empty($month)) {
        throw new Exception('السنة والشهر مطلوبان');
    }
    
    $created_at = date("Y-m-d", strtotime("$year-$month-01"));
    
    // Handle file upload
    $kimlik_image = '';
    if (isset($_FILES['kimlik_image']) && $_FILES['kimlik_image']['error'] == 0) {
        $fileUpload = new FileUpload('uploads', ['jpg', 'jpeg', 'png', 'gif'], 5242880);
        $uploadResult = $fileUpload->uploadFile($_FILES['kimlik_image'], 'urgent_');
        
        if ($uploadResult['success']) {
            $kimlik_image = $uploadResult['filename'];
        } else {
            throw new Exception('فشل في رفع الصورة: ' . implode(', ', $uploadResult['errors']));
        }
    }
    
    // Insert beneficiary
    $sql = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, kimlik_image, created_at) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    if ($stmt->execute([$name, $phone, $kimlik_number, $iban, $kimlik_image, $created_at])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'تم إضافة المستفيد بنجاح'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('فشل في إضافة المستفيد');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$db->close();
?>
