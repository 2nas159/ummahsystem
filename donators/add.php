<?php
/**
 * Secure Add Donators
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

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    // Verify CSRF token
    if (!Security::verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "رمز الأمان غير صحيح. يرجى المحاولة مرة أخرى.";
    } else {
        // Sanitize and validate inputs
        $ADI = Security::sanitizeInput($_POST['ADI'] ?? '');
        $TEL = Security::sanitizeInput($_POST['TEL'] ?? '');
        
        // Validate required fields
        if (empty($ADI) || empty($TEL)) {
            $error = "يرجى ملء جميع الحقول المطلوبة.";
        } elseif (!Security::validatePhone($TEL)) {
            $error = "رقم الهاتف غير صحيح.";
        } else {
            try {
                // Auto-generate the next donator number
                $maxNoSql = "SELECT MAX(NO) as max_no FROM donators";
                $maxResult = $mysqli->fetchOne($maxNoSql);
                $NO = ($maxResult && $maxResult['max_no']) ? (int)$maxResult['max_no'] + 1 : 1;
                
                // Insert new donator
                $sql = "INSERT INTO donators (NO, ADI, TEL) VALUES (?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                
                if ($stmt->execute([$NO, $ADI, $TEL])) {
                    $success = "تم إضافة المتبرع بنجاح برقم: " . $NO;
                    // Clear form data
                    $_POST = [];
                } else {
                    $error = "حدث خطأ أثناء إضافة المتبرع.";
                }
            } catch (Exception $e) {
                error_log("Add donator error: " . $e->getMessage());
                $error = "حدث خطأ في النظام. يرجى المحاولة لاحقاً.";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = Security::generateCSRFToken();
?>

<?php
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
   <div class="container mt-5">
      <div class="text-center mb-4">
         <h2>إضافة متبرع جديد</h2>
      </div>

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

      <div class="d-flex justify-content-center">
         <form action="" method="POST" style="width: 50vw; min-width: 300px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="mb-3">
               <label class="form-label">الاسم <span class="text-danger">*</span></label>
               <input type="text" class="form-control" name="ADI" placeholder="اسم المتبرع" 
                      value="<?php echo htmlspecialchars($_POST['ADI'] ?? ''); ?>" required>
            </div>

            <div class="mb-3">
               <label class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
               <input type="tel" class="form-control" name="TEL" placeholder="رقم هاتف المتبرع" 
                      value="<?php echo htmlspecialchars($_POST['TEL'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn btn-success" name="submit">إضافة متبرع</button>
            <a href="index.php" class="btn btn-danger">إلغاء</a>
         </form>
      </div>
   </div>
</main>
