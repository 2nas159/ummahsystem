<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
include('db_connection.php');

try {
    if (!isset($_GET['term']) || empty($_GET['term'])) {
        echo json_encode([]);
        exit;
    }

    $searchTerm = $_GET['term'];

    // تحسين الاستعلام للبحث عن المستفيدين
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
    $searchPattern = "%{$searchTerm}%";
    $stmt->bind_param('ss', $searchPattern, $searchPattern);
    $stmt->execute();
    $result = $stmt->get_result();

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
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'حدث خطأ في البحث',
        'details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

$conn->close(); 