<?php
header('Content-Type: application/json; charset=utf-8');
include('db_connection.php');

try {
    if (!isset($_GET['term']) || empty($_GET['term'])) {
        echo json_encode([]);
        exit;
    }

    $searchTerm = $_GET['term'];
    
    // تحقق من اتصال قاعدة البيانات
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // تحديث الاستعلام مع اسم الجدول الصحيح
    $query = "SELECT 
        p.id as payment_id,
        p.beneficiary_id,
        b.name as beneficiary_name,
        p.amount,
        p.payment_date,
        YEAR(p.payment_date) as year,
        MONTH(p.payment_date) as month
    FROM payments p
    JOIN beneficiaries b ON p.beneficiary_id = b.id
    WHERE (b.name LIKE ? OR p.beneficiary_id LIKE ?)
    ORDER BY p.payment_date DESC
    LIMIT 50";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $searchPattern = "%{$searchTerm}%";
    $stmt->bind_param('ss', $searchPattern, $searchPattern);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Result fetch failed: " . $stmt->error);
    }

    $searchResults = [];
    while ($row = $result->fetch_assoc()) {
        $searchResults[] = [
            'payment_id' => $row['payment_id'],
            'beneficiary_id' => $row['beneficiary_id'],
            'beneficiary_name' => $row['beneficiary_name'],
            'amount' => $row['amount'],
            'payment_date' => $row['payment_date'],
            'year' => $row['year'],
            'month' => $row['month']
        ];
    }

    echo json_encode($searchResults, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'حدث خطأ في البحث',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}