<?php
header('Content-Type: application/json; charset=utf-8');
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Collect the form data from AJAX
        if (empty($_POST['id'])) {
            throw new Exception('معرف المستفيد مطلوب');
        }

        $id = intval($_POST['id']);
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $kimlik_number = isset($_POST['kimlik_number']) ? trim($_POST['kimlik_number']) : '';
        $iban = isset($_POST['iban']) ? trim($_POST['iban']) : '';

        // Validate required fields
        if (empty($name)) {
            throw new Exception('الاسم مطلوب');
        }

        // Update the beneficiary information in the database
        $query = "UPDATE beneficiaries SET name = ?, phone = ?, kimlik_number = ?, iban = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $name, $phone, $kimlik_number, $iban, $id);

        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'تم تحديث بيانات المستفيد بنجاح'
            ];
        } else {
            throw new Exception('فشل في تحديث بيانات المستفيد');
        }

        $stmt->close();
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }

    // Return the response as JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'طلب غير صالح'
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close();

