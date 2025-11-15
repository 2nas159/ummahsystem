<?php
include('db_connection.php');

if (isset($_POST['query']) && isset($_POST['year']) && isset($_POST['month'])) {
    $search_query = $_POST['query'];
    $year = intval($_POST['year']);
    $month = intval($_POST['month']);

    // البحث في قاعدة البيانات مع تحديد الشهر والسنة
    $query = "SELECT * FROM beneficiaries WHERE (name LIKE ? OR phone LIKE ? OR kimlik_number LIKE ? OR iban LIKE ?)
              AND YEAR(created_at) = ? AND MONTH(created_at) = ?";
    $search_term = '%' . $search_query . '%';
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssssii', $search_term, $search_term, $search_term, $search_term, $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $index = 1; // بدء الترقيم من 1
        while ($row = $result->fetch_assoc()) {
            // التحقق من الدفع
            $payment_query = "SELECT * FROM payments WHERE beneficiary_id = ? AND MONTH(payment_date) = ? AND YEAR(payment_date) = ?";
            $payment_stmt = $conn->prepare($payment_query);
            $payment_stmt->bind_param('iii', $row['id'], $month, $year);
            $payment_stmt->execute();
            $payment_result = $payment_stmt->get_result();

            $payment_info = "";
            if ($payment_result->num_rows > 0) {
                $payment_row = $payment_result->fetch_assoc();
                $paymentLabels = [
                    'cash' => 'نقدي',
                    'bank' => 'تحويل بنكي',
                    'in-kind' => 'تحويل عيني'
                ];
                $paymentTypeLabel = $paymentLabels[$payment_row['payment_type']] ?? htmlspecialchars($payment_row['payment_type']);
                $payment_info = "المبلغ المدفوع: " . number_format($payment_row['amount'], 2) . "<br>" .
                                "طريقة الدفع: " . $paymentTypeLabel;
            } else {
                // إذا لم يكن هناك مدفوعات، عرض نموذج الدفع
                $payment_info = "
                    <form id='payment_form_{$row['id']}' class='payment-form'>
                        <input type='hidden' name='beneficiary_id' value='{$row['id']}'>
                        <input type='hidden' name='year' value='{$year}'>
                        <input type='hidden' name='month' value='{$month}'>
                        <input type='number' name='amount' class='form-control mb-2' placeholder='المبلغ' required>
                        <select name='payment_type' class='form-control mb-2' required>
                            <option value=''>اختر نوع الدفعية</option>
                            <option value='cash'>نقدي</option>
                            <option value='bank'>تحويل بنكي</option>
                            <option value='in-kind'>تحويل عيني</option>
                        </select>
                        <button type='button' class='btn btn-primary' onclick='submitPayment({$row['id']})'>تسجيل الدفعية</button>
                    </form>
                ";
            }

            echo "
                <tr id='beneficiary_row_{$row['id']}'>
                    <td>{$index}</td> <!-- عمود الترقيم -->
                    <td class='view-mode'>{$row['name']}</td>
                    <td class='view-mode'>{$row['phone']}</td>
                    <td class='view-mode'>{$row['kimlik_number']}</td>
                    <td class='view-mode'>{$row['iban']}</td>
                    <td>
                        <button class='btn btn-sm btn-info' onclick='showImagePopup(\"uploads/{$row['kimlik_image']}\")'>عرض الكملك</button>
                    </td>
                    <td id='payment_info_{$row['id']}'>$payment_info</td>
                    <td>
                        <button class='btn btn-warning btn-sm' onclick='enableEdit({$row['id']})'>تعديل</button>
                        <button class='btn btn-primary btn-sm edit-mode' style='display:none;' onclick='saveEdit({$row['id']})'>حفظ</button>
                        <button class='btn btn-danger btn-sm' onclick='deleteBeneficiary({$row['id']})'>حذف</button>
                    </td>
                </tr>
            ";
            $index++; // زيادة الترقيم لكل صف
        }
    } else {
        echo "<tr><td colspan='8'>لا توجد نتائج مطابقة</td></tr>";
    }
}
