<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']); // الحصول على ID المستفيد

    // حذف المستفيد من قاعدة البيانات
    $query_delete = "DELETE FROM beneficiaries WHERE id = ?";
    $stmt = $conn->prepare($query_delete);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
