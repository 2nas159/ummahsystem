<?php
include "reports_db.php";

if (isset($_GET['file_name'])) {
    $file_name = urldecode($_GET['file_name']);

    // Fetch report details from the database based on file name
    $stmt = $pdo->prepare("SELECT * FROM operation_plan_report WHERE file_name = :file_name");
    $stmt->execute([':file_name' => $file_name]);
    $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$report_data) {
        die('Report not found.');
    }
} else {
    die('No report specified.');
}

// If the form is submitted, update the report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $goals = $_POST['goals'];
    $tasks = $_POST['tasks'];
    $number_of_individuals = $_POST['number_of_individuals'];
    $negatives_and_obstacles = $_POST['negatives_and_obstacles'];
    $evaluation = $_POST['evaluation'];
    $amount = $_POST['amount'];
    $completion_percentage = $_POST['completion_percentage'];
    $notes_and_recommendations = $_POST['notes_and_recommendations'];

    // Delete existing entries for this report before re-inserting
    $stmt = $pdo->prepare("DELETE FROM operation_plan_report WHERE file_name = :file_name");
    $stmt->execute([':file_name' => $file_name]);

    // Re-insert updated entries
    $sql = "INSERT INTO operation_plan_report 
            (file_name, goals, tasks, number_of_individuals, negatives_and_obstacles, evaluation, amount, completion_percentage, notes_and_recommendations, report_month) 
            VALUES 
            (:file_name, :goals, :tasks, :number_of_individuals, :negatives_and_obstacles, :evaluation, :amount, :completion_percentage, :notes_and_recommendations, :report_month)";

    $stmt = $pdo->prepare($sql);
    for ($i = 0; $i < count($goals); $i++) {
        $stmt->execute([
            ':file_name' => $file_name,
            ':goals' => $goals[$i],
            ':tasks' => $tasks[$i],
            ':number_of_individuals' => $number_of_individuals[$i],
            ':negatives_and_obstacles' => $negatives_and_obstacles[$i],
            ':evaluation' => $evaluation[$i],
            ':amount' => $amount[$i],
            ':completion_percentage' => $completion_percentage[$i],
            ':notes_and_recommendations' => $notes_and_recommendations[$i],
            ':report_month' => $report_data[0]['report_month'] // Keep original report month
        ]);
    }

    header('Location: view_reports.php');
}
?>

<?php include "header.php" ?>

<style>
    /* General form control styles */
    .form-control {
        width: 100%;
        height: 40px;
        margin-bottom: 10px;
        font-size: 1rem;
        padding: 10px;
    }

    .input-group input {
        margin-right: 10px;
    }

    /* Style for text areas */
    textarea.form-control {
        height: 50PX;
    }

    /* Style for labels */
    label {
        font-weight: bold;
        display: block;
        padding: 12px;
        color: #333;
    }

    /* Row spacing */
    .row {
        display: flex;
        margin-bottom: 10px;
    }

    /* Ensures two columns with 50% width each */
    .col-md-6 {
        flex: 0 0 50%;
        padding: 0 10px;
    }


    /* Style for the submit button */
    .btn-success {
        width: 100%;
        padding: 15px;
        font-size: 1.2rem;
        background-color: #28a745;
        border: none;
        border-radius: 5px;
        color: white;
    }

    .btn-success:hover {
        background-color: #218838;
    }

    /* Separator line between groups */
    #reportTable {
        padding: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        border: 1px solid black;
    }

    /* Style for the add button */
    .add-btn {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        margin: 15px 0;
        cursor: pointer;
        display: block;
    }

    .add-btn:hover {
        background-color: #218838;
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

    <h2 style="text-align: center;" class="mt-4 mb-4">تعديل التقرير: <?= htmlspecialchars($file_name) ?></h2>

    <form method="POST">
        <!-- Loop through each report -->
        <?php foreach ($report_data as $row): ?>
            <!-- Group wrapper start -->
            <div id="reportTable">
                <!-- الأهداف والمهمات -->

                <div class="input-group mb-3">
                    <label for="">الهدف</label>
                    <textarea class="form-control" name="goals[]"><?= htmlspecialchars($row['goals']) ?></textarea>
                </div>

                <div class="input-group mb-3">
                    <label for="">المهام</label>
                    <textarea class="form-control" name="tasks[]"><?= htmlspecialchars($row['tasks']) ?></textarea>
                </div>


                <!-- عدد المستفيدين والإيجابيات والسلبيات -->

                <div class="input-group mb-3">
                    <label>عدد المستفيدين</label>
                    <input class="form-control" type="number" name="number_of_individuals[]"
                        value="<?= htmlspecialchars($row['number_of_individuals']) ?>">
                </div>
                <div class="input-group mb-3">
                    <label>الإيجابيات والسلبيات</label>
                    <textarea class="form-control"
                        name="negatives_and_obstacles[]"><?= htmlspecialchars($row['negatives_and_obstacles']) ?></textarea>
                </div>

                <!-- التقييم والمبلغ -->

                <div class="input-group mb-3">
                    <label>التقييم</label>
                    <textarea class="form-control"
                        name="evaluation[]"><?= htmlspecialchars($row['evaluation']) ?></textarea>
                </div>

                <div class="input-group mb-3">
                    <label>المبلغ</label>
                    <input class="form-control" type="number" step="0.01" name="amount[]"
                        value="<?= htmlspecialchars($row['amount']) ?>">
                </div>

                <!-- نسبة التحقق والملاحظات -->

                <div class="input-group mb-3">
                    <label>نسبة التحقق</label>
                    <input class="form-control" type="number" step="0.01" name="completion_percentage[]"
                        value="<?= htmlspecialchars($row['completion_percentage']) ?>">
                </div>

                <div class="input-group mb-3">
                    <label>ملاحظات واقتراحات</label>
                    <textarea class="form-control"
                        name="notes_and_recommendations[]"><?= htmlspecialchars($row['notes_and_recommendations']) ?></textarea>
                </div>
            </div>
            <!-- Group wrapper end -->
            <?php endforeach; ?>
            <!-- زر إضافة المزيد من الأهداف -->
            <button type="button" class="add-btn" onclick="addRow()">إضافة هدف </button>


        <button class="btn btn-success" type="submit">تحديث التقرير</button>
    </form>
</main>

<script>
    function addRow() {
        // Locate the last reportTable container
        const lastReportTable = document.querySelectorAll("#reportTable");
        const lastTable = lastReportTable[lastReportTable.length - 1];
        
        // New group to be added
        let newGroup = `
        <div id="reportTable">
            <div class="input-group mb-3">
                <label for="">الهدف</label>
                <textarea class="form-control" name="goals[]"></textarea>
            </div>

            <div class="input-group mb-3">
                <label for="">المهام</label>
                <textarea class="form-control" name="tasks[]"></textarea>
            </div>

            <div class="input-group mb-3">
                <label>عدد المستفيدين</label>
                <input class="form-control" type="number" name="number_of_individuals[]">
            </div>

            <div class="input-group mb-3">
                <label>الإيجابيات والسلبيات</label>
                <textarea class="form-control" name="negatives_and_obstacles[]"></textarea>
            </div>

            <div class="input-group mb-3">
                <label>التقييم</label>
                <textarea class="form-control" name="evaluation[]"></textarea>
            </div>

            <div class="input-group mb-3">
                <label>المبلغ</label>
                <input class="form-control" type="number" step="0.01" name="amount[]">
            </div>

            <div class="input-group mb-3">
                <label>نسبة التحقق</label>
                <input class="form-control" type="number" step="0.01" name="completion_percentage[]">
            </div>

            <div class="input-group mb-3">
                <label>ملاحظات واقتراحات</label>
                <textarea class="form-control" name="notes_and_recommendations[]"></textarea>
            </div>
        </div>
        `;

        // Insert the new row after the last table
        lastTable.insertAdjacentHTML('afterend', newGroup);
    }
</script>

