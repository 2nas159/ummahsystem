<?php

session_start();


if (!isset($_SESSION["username"])) {
    header("location:login.php");
}
include "header_hr.php";

include("reports_db.php");

// Fetch distinct file names from the database and group them
$stmt = $pdo->query("SELECT DISTINCT file_name, report_month, created_at FROM operation_plan_report ORDER BY created_at DESC");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    /* تحسين مظهر الروابط */
    a {
        text-decoration: none;
        color: white;
    }
</style>

<main class="mt-4">
    <div class="container">
        <h2 class="mb-4">التقارير المتاحة</h2>

        <table class="table table-responsive table-bordered">
            <thead>
                <tr>
                    <th>اسم التقرير</th>
                    <th>تاريخ الرفع</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reports as $report): ?>
                    <tr>
                        <td>
                            <button class="btn btn-primary">
                                <a href="javascript:void(0)"
                                    onclick="toggleDetails('<?= htmlspecialchars($report['file_name']) ?>')">
                                    <?= htmlspecialchars($report['file_name']) ?>
                                </a>
                            </button>
                        </td>
                        <td><?= htmlspecialchars($report['created_at']) ?></td>
                    </tr>
                    <!-- Hidden row for report details -->
                    <tr id="details-<?= htmlspecialchars(str_replace(' ', '_', $report['file_name'])) ?>"
                        style="display: none;">
                        <td colspan="3">
                            <!-- Content will be loaded by AJAX -->
                            <div id="report-content-<?= htmlspecialchars(str_replace(' ', '_', $report['file_name'])) ?>">
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- JavaScript to handle AJAX and toggle details -->
<script>
    function toggleDetails(fileName) {
        // Replace spaces with underscores in the fileName for the HTML ID
        const safeFileName = fileName.replace(/\s+/g, '_');
        const detailsRow = $('#details-' + safeFileName);

        // Check if the row is currently hidden
        if (detailsRow.is(':hidden')) {
            // Fetch report details via AJAX
            $.ajax({
                url: 'fetch_report_details.php',
                type: 'GET',
                data: { file_name: encodeURIComponent(fileName) },  // Encode the file name
                success: function (data) {
                    // Insert the details into the hidden row
                    $('#report-content-' + safeFileName).html(data);
                    // Show the row
                    detailsRow.show();
                },
                error: function () {
                    alert('Failed to load report details.');
                }
            });
        } else {
            // Hide the row if it's already visible
            detailsRow.hide();
        }
    }

    // AJAX function to delete a report
    function deleteReport(fileName) {
        if (confirm('Are you sure you want to delete this report?')) {
            $.ajax({
                url: 'delete_report.php',
                type: 'POST',
                data: { file_name: fileName },
                success: function (response) {
                    if (response === 'success') {
                        location.reload(); // Refresh the page after deletion
                    } else if (response === 'file_name_not_provided') {
                        alert('Error: No file name provided.');
                    } else {
                        alert('Error deleting report: ' + response);
                    }
                },
                error: function () {
                    alert('Failed to communicate with the server.');
                }
            });
        }
    }
</script>