<?php
/**
 * Secure Update Donator
 */

require_once __DIR__ . '/donators_db.php';
require_once __DIR__ . '/../classes/Security.php';
require_once __DIR__ . '/../classes/User.php';

// Check if user is logged in
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// Set content type for AJAX response
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        echo json_encode(['status' => 'error', 'message' => 'رمز الأمان غير صحيح']);
        exit();
    }
    
    // Sanitize and validate inputs
    $NO = Security::sanitizeInput($_POST['NO'] ?? '');
    $ADI = Security::sanitizeInput($_POST['ADI'] ?? '');
    $TEL = Security::sanitizeInput($_POST['TEL'] ?? '');
    
    // Validate inputs
    if (empty($NO) || empty($ADI) || empty($TEL)) {
        echo json_encode(['status' => 'error', 'message' => 'جميع الحقول مطلوبة']);
        exit();
    }
    
    if (!Security::validatePhone($TEL)) {
        echo json_encode(['status' => 'error', 'message' => 'رقم الهاتف غير صحيح']);
        exit();
    }
    
    try {
        // Update donator information
        $sql = "UPDATE donators SET ADI = ?, TEL = ? WHERE NO = ?";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt->execute([$ADI, $TEL, $NO])) {
            echo json_encode(['status' => 'success', 'message' => 'تم التحديث بنجاح']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'فشل في التحديث']);
        }
    } catch (Exception $e) {
        error_log("Update donator error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في النظام']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'طريقة الطلب غير صحيحة']);
}
?>
