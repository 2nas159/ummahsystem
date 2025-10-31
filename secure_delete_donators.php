<?php
/**
 * Secure Delete Donators
 * Replaces delete_donators.php with secure implementation
 */

require_once __DIR__ . '/secure_donators_db.php';
require_once __DIR__ . '/classes/Security.php';
require_once __DIR__ . '/classes/User.php';

// Check if user is logged in
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    header("Location: index.php");
    exit();
}

// Check if the 'NO' parameter is provided
if (isset($_GET['NO'])) {
    $NO = Security::sanitizeInput($_GET['NO']);
    
    // Validate input
    if (!is_numeric($NO)) {
        header("Location: donators.php?error=رقم المتبرع غير صحيح");
        exit();
    }
    
    try {
        // Check if donator exists
        $checkSql = "SELECT COUNT(*) as count FROM donators WHERE NO = ?";
        $checkResult = $mysqli->fetchOne($checkSql, [$NO]);
        
        if ($checkResult['count'] == 0) {
            header("Location: donators.php?error=المتبرع غير موجود");
            exit();
        }
        
        // Delete donator
        $sql = "DELETE FROM donators WHERE NO = ?";
        $stmt = $mysqli->prepare($sql);
        
        if ($stmt->execute([$NO])) {
            header("Location: donators.php?success=تم حذف المتبرع بنجاح");
        } else {
            header("Location: donators.php?error=فشل في حذف المتبرع");
        }
    } catch (Exception $e) {
        error_log("Delete donator error: " . $e->getMessage());
        header("Location: donators.php?error=حدث خطأ في النظام");
    }
} else {
    header("Location: donators.php?error=لم يتم تحديد المتبرع");
}
?>
