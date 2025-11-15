<?php
header('Content-Type: application/json; charset=utf-8');

// ربط قاعدة البيانات
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // التحقق من وجود بيانات المدفوعات
        if (!isset($_POST['payments']) || !is_array($_POST['payments'])) {
            throw new Exception('لا توجد بيانات مدفوعات');
        }

        $payments = $_POST['payments'];
        $year = isset($_POST['year']) && is_numeric($_POST['year']) ? (int)$_POST['year'] : date('Y');
        $successCount = 0;
        $errors = [];
        $savedBeneficiaryIds = []; // قائمة بمعرفات المستفيدين الذين تم حفظ دفعاتهم بنجاح

        // تنفيذ الاستعلام أو حفظ البيانات
        foreach ($payments as $beneficiary_id => $payment_data) {
            $amount = isset($payment_data['amount']) ? trim($payment_data['amount']) : '';
            $payment_type = isset($payment_data['payment_type']) ? trim($payment_data['payment_type']) : '';

            // تخطي المدفوعات الفارغة
            if (empty($amount) || empty($payment_type)) {
                continue;
            }

            // التحقق من صحة المبلغ
            $amount = floatval($amount);
            if ($amount <= 0) {
                $errors[] = "المبلغ غير صالح للمستفيد ID: $beneficiary_id";
                continue;
            }

            // التحقق من وجود دفعة سابقة في نفس السنة
            $checkStmt = $conn->prepare("SELECT id FROM payments WHERE beneficiary_id = ? AND YEAR(payment_date) = ?");
            $checkStmt->bind_param("ii", $beneficiary_id, $year);
            $checkStmt->execute();
            $existingPayment = $checkStmt->get_result();

            if ($existingPayment->num_rows > 0) {
                // تحديث الدفعة الموجودة
                $updateStmt = $conn->prepare("UPDATE payments SET amount = ?, payment_type = ? WHERE beneficiary_id = ? AND YEAR(payment_date) = ?");
                $updateStmt->bind_param("dsii", $amount, $payment_type, $beneficiary_id, $year);
                if ($updateStmt->execute()) {
                    $successCount++;
                    $savedBeneficiaryIds[] = (int)$beneficiary_id;
                } else {
                    $errors[] = "فشل تحديث الدفعة للمستفيد ID: $beneficiary_id";
                }
                $updateStmt->close();
            } else {
                // إضافة دفعة جديدة
                $payment_date = $year . '-01-01'; // استخدام أول يوم من السنة
                $stmt = $conn->prepare("INSERT INTO payments (beneficiary_id, amount, payment_type, payment_date) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $beneficiary_id, $amount, $payment_type, $payment_date);
                if ($stmt->execute()) {
                    $successCount++;
                    $savedBeneficiaryIds[] = (int)$beneficiary_id;
                } else {
                    $errors[] = "فشل إضافة الدفعة للمستفيد ID: $beneficiary_id";
                }
                $stmt->close();
            }
            $checkStmt->close();
        }

        if ($successCount > 0) {
            $response = [
                'success' => true,
                'message' => "تم تسجيل $successCount دفعة بنجاح",
                'count' => $successCount,
                'saved_ids' => $savedBeneficiaryIds
            ];
            if (!empty($errors)) {
                $response['partial_errors'] = $errors;
                $response['message'] .= ' (مع بعض الأخطاء)';
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'لم يتم تسجيل أي دفعات. تأكد من إدخال المبلغ ونوع الدفعية.',
                'errors' => $errors
            ], JSON_UNESCAPED_UNICODE);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'طلب غير صالح'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();

