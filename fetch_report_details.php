<style>
    /* تحسين تنسيق الجدول */
    table {
        border-collapse: collapse;
        width: 100%;
    }

    table th,
    table td {
        padding: 8px;
        text-align: center;
        font-size: 14px;
        white-space: nowrap;
        /* إجبار العناوين على البقاء في سطر واحد */
    }

    table td {
        white-space: normal;
        /* السماح بالتفاف النص الطويل داخل الخلايا */
        word-break: break-word;
        /* تقسيم الكلمات الطويلة */
    }

    table th {
        background-color: #f8f9fa;
        font-weight: bold;
        white-space: nowrap;
        /* منع العناوين من الانتقال إلى سطر جديد */
    }

    /* تحسين مظهر الأزرار */
    .btn-warning {
        background-color: #ffc107;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
    }

    /* تحسين مظهر الروابط */
    a {
        text-decoration: none;
        color: white;
    }
</style>
<?php
include("reports_db.php");

if (isset($_GET['file_name'])) {
    $file_name = urldecode($_GET['file_name']);

    // Fetch report details from the database based on file name
    $stmt = $pdo->prepare("SELECT * FROM operation_plan_report WHERE file_name = :file_name");
    $stmt->execute([':file_name' => $file_name]);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($report_data)) {
        die('Report not found.');
    }

    // عرض تفاصيل التقرير
    echo '<h3>التقرير: ' . htmlspecialchars($file_name) . '</h3>';
    echo '<table class="table table-responsive table-bordered">';
    echo '<thead><tr><th>الهدف</th><th>المهام</th><th>المستفيدين</th><th>السلبيات والعقبات</th><th>التقييم</th><th>المبلغ</th><th>الاكتمال</th><th>ملاحظات</th></tr></thead>';
    echo '<tbody>';

    foreach ($report_data as $row) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['goals']) . '</td>';
        echo '<td>' . htmlspecialchars($row['tasks']) . '</td>';
        echo '<td>' . htmlspecialchars($row['number_of_individuals']) . '</td>';
        echo '<td>' . htmlspecialchars($row['negatives_and_obstacles']) . '</td>';
        echo '<td>' . htmlspecialchars($row['evaluation']) . '</td>';
        echo '<td>' . htmlspecialchars($row['amount']) . '</td>';
        echo '<td>' . htmlspecialchars($row['completion_percentage']) . '</td>';
        echo '<td>' . htmlspecialchars($row['notes_and_recommendations']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo 'File name not received!';
}
?>