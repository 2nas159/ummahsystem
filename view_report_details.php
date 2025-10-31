<?php
include("reports_db.php");
if (isset($_GET['file_name'])) {
    $file_name = urldecode($_GET['file_name']);

    // Fetch report details from the database based on file name
    $stmt = $pdo->prepare("SELECT * FROM operation_plan_report WHERE file_name = :file_name");
    $stmt->execute([':file_name' => $file_name]);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "No report specified.";
    exit;
}
?>

<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <h2>Report: <?= htmlspecialchars($file_name) ?></h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Goals</th>
                <th>Tasks</th>
                <th>Number of Individuals</th>
                <th>Negatives and Obstacles</th>
                <th>Evaluation</th>
                <th>Amount</th>
                <th>Completion Percentage</th>
                <th>Notes and Recommendations</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['goals']) ?></td>
                    <td><?= htmlspecialchars($row['tasks']) ?></td>
                    <td><?= htmlspecialchars($row['number_of_individuals']) ?></td>
                    <td><?= htmlspecialchars($row['negatives_and_obstacles']) ?></td>
                    <td><?= htmlspecialchars($row['evaluation']) ?></td>
                    <td><?= htmlspecialchars($row['amount']) ?></td>
                    <td><?= htmlspecialchars($row['completion_percentage']) ?></td>
                    <td><?= htmlspecialchars($row['notes_and_recommendations']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>
