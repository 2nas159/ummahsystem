<?php
include("reports_db.php");

// Fetch distinct file names from the database and group them
$stmt = $pdo->query("SELECT DISTINCT file_name, report_month, created_at FROM operation_plan_report ORDER BY created_at DESC");
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4 page-section">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="page-title m-0">التقارير المتاحة</h2>
    </div>

    <div class="card card-elevated">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-modern m-0">
                    <thead>
                        <tr>
                            <th>اسم التقرير</th>
                            <th>تاريخ الرفع</th>
                            <th class="text-nowrap">العمليات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="3"><div class="empty-state">لا توجد تقارير متاحة حاليا</div></td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-icon"
                                        aria-expanded="false"
                                        aria-controls="details-<?= htmlspecialchars(str_replace(' ', '_', $report['file_name'])) ?>"
                                        onclick="toggleDetails('<?= htmlspecialchars($report['file_name']) ?>')">
                                        <i class="bi bi-chevron-down"></i>
                                        <span><?= htmlspecialchars($report['file_name']) ?></span>
                                    </button>
                                </td>
                                <td><?= htmlspecialchars($report['created_at']) ?></td>
                                <td class="text-nowrap">
                                    <a class="btn btn-sm btn-warning btn-icon action-link"
                                       href="edit_report.php?file_name=<?= urlencode($report['file_name']) ?>">
                                        <i class="bi bi-pencil-square"></i><span>تعديل</span>
                                    </a>
                                    <button class="btn btn-sm btn-danger btn-icon ms-2"
                                        onclick="deleteReport('<?= htmlspecialchars($report['file_name']) ?>')">
                                        <i class="bi bi-trash"></i><span>حذف</span>
                                    </button>
                                </td>
                            </tr>
                            <!-- Hidden row for report details -->
                            <tr id="details-<?= htmlspecialchars(str_replace(' ', '_', $report['file_name'])) ?>" style="display: none;">
                                <td colspan="3">
                                    <div class="p-3" id="report-content-<?= htmlspecialchars(str_replace(' ', '_', $report['file_name'])) ?>"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
                    // تحويل السطور الجديدة إلى <br>
                    const formattedData = data.replace(/\n/g, '<br>');
                    $('#report-content-' + safeFileName).html(formattedData);
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