<?php
/**
 * Secure Add Beneficiary for Customized Subsidies
 * Replaces customized_subsidies/add_beneficiary.php with secure implementation
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
$customizedConfig = $config;
$customizedConfig['database'] = 'u850876726_customized';

$db = Database::getInstance($customizedConfig);
$db->selectDatabase('u850876726_customized');

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "رمز الأمان غير صحيح. يرجى المحاولة مرة أخرى.";
    } else {
        // Sanitize inputs
        $name = Security::sanitizeInput($_POST['name'] ?? '');
        $phone = Security::sanitizeInput($_POST['phone'] ?? '');
        $kimlik_number = Security::sanitizeInput($_POST['kimlik_number'] ?? '');
        $iban = Security::sanitizeInput($_POST['iban'] ?? '');
        $monthly_amount = Security::sanitizeInput($_POST['monthly_amount'] ?? '');
        
        // Validate inputs
        if (empty($name)) {
            $error = "اسم المستفيد مطلوب";
        } elseif (!Security::validatePhone($phone)) {
            $error = "رقم الهاتف غير صحيح";
        } elseif (!is_numeric($monthly_amount) || $monthly_amount <= 0) {
            $error = "المبلغ الشهري يجب أن يكون رقماً موجباً";
        } else {
            try {
                // Handle file upload
                $kimlik_photo = null;
                if (isset($_FILES['kimlik_photo']) && $_FILES['kimlik_photo']['error'] == 0) {
                    $fileUpload = new FileUpload('uploads', ['jpg', 'jpeg', 'png', 'gif'], 5242880);
                    $uploadResult = $fileUpload->uploadFile($_FILES['kimlik_photo'], 'kimlik_');
                    
                    if ($uploadResult['success']) {
                        $kimlik_photo = $uploadResult['filename'];
                    } else {
                        $error = implode(', ', $uploadResult['errors']);
                    }
                }
                
                if (empty($error)) {
                    // Insert beneficiary
                    $sql = "INSERT INTO beneficiaries (name, phone, kimlik_number, iban, monthly_amount, kimlik_photo, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $stmt = $db->prepare($sql);
                    
                    if ($stmt->execute([$name, $phone, $kimlik_number, $iban, $monthly_amount, $kimlik_photo])) {
                        $success = "تم إضافة المستفيد بنجاح";
                        // Clear form data
                        $_POST = [];
                    } else {
                        $error = "فشل في إضافة المستفيد";
                    }
                }
            } catch (Exception $e) {
                error_log("Add beneficiary error: " . $e->getMessage());
                $error = "حدث خطأ في النظام. يرجى المحاولة لاحقاً.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<?php
$BASE_PATH_PREFIX = '';
require_once __DIR__ . '/layout.php';
?>

<h2>إضافة مستفيد جديد</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    
    <div class="form-group">
        <label>الاسم:</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label>رقم الهاتف:</label>
        <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label>رقم الكملك:</label>
        <input type="text" name="kimlik_number" class="form-control" value="<?php echo htmlspecialchars($_POST['kimlik_number'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label>رقم الـ IBAN:</label>
        <input type="text" name="iban" class="form-control" value="<?php echo htmlspecialchars($_POST['iban'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label>المبلغ الشهري:</label>
        <input type="number" name="monthly_amount" class="form-control" value="<?php echo htmlspecialchars($_POST['monthly_amount'] ?? ''); ?>" required>
    </div>
    
    <div class="form-group">
        <label>صورة الكملك (اختياري):</label>
        <input type="file" name="kimlik_photo" id="kimlik_photo" class="form-control" accept="image/*">
        <small class="form-text text-muted">الملفات المسموحة: JPG, JPEG, PNG, GIF (حجم أقصى: 5MB)</small>
    </div>
    
    <button type="submit" class="btn btn-primary">إضافة</button>
    <a href="beneficiaries_list.php" class="btn btn-secondary">إلغاء</a>
</form>
