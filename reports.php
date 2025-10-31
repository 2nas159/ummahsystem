<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        /* Optional styling for the file name input */
        
    </style>
    <title>إنشاء تقرير جديد</title>
</head>

<body>
    <?php include "header.php"; ?>

    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 mt-4">
        <h2 style="text-align: center;" class="mb-4">إنشاء تقرير جديد</h2>

        <form action="create_report.php" method="post">
            <div style="width: 600px; align-items: center;" class="input-group mb-3 report_name">
                <label style="padding-bottom: 26px" for="file_name">اسم التقرير</label>
                <input style="width: 200px;" type="text" id="file_name" name="file_name" class="form-control" required>
            </div>

            <div id="reportTable">
                <!-- الأهداف والمهمات -->

                <div class="input-group mb-3">
                    <label for="">الهدف</label>
                    <textarea class="form-control" name="goals[]"></textarea>
                </div>

                <div class="input-group mb-3">
                    <label for="">المهام</label>
                    <textarea class="form-control" name="tasks[]"></textarea>
                </div>


                <!-- عدد المستفيدين والإيجابيات والسلبيات -->

                <div class="input-group mb-3">
                    <label>عدد المستفيدين</label>
                    <input class="form-control" type="number" name="number_of_individuals[]">
                </div>
                <div class="input-group mb-3">
                    <label>الإيجابيات والسلبيات</label>
                    <textarea class="form-control" name="negatives_and_obstacles[]"></textarea>
                </div>

                <!-- التقييم والمبلغ -->

                <div class="input-group mb-3">
                    <label>التقييم</label>
                    <textarea class="form-control" name="evaluation[]"></textarea>
                </div>

                <div class="input-group mb-3">
                    <label>المبلغ</label>
                    <input class="form-control" type="number" step="0.01" name="amount[]">
                </div>

                <!-- نسبة التحقق والملاحظات -->

                <div class="input-group mb-3">
                    <label>نسبة التحقق</label>
                    <input class="form-control" type="text" step="0.01" name="completion_percentage[]">
                </div>

                <div class="input-group mb-3">
                    <label>ملاحظات واقتراحات</label>
                    <textarea class="form-control" name="notes_and_recommendations[]"></textarea>
                </div>

            </div>
            <div class="row">
                <div class="col">
                    <!-- زر إضافة المزيد من الأهداف -->
                    <button type="button" class="add-btn" onclick="addRow()">إضافة هدف </button>

                </div>
                <div class="col">
                    <!-- اختيار شهر التقرير -->
                    <label for="report_month">اختر شهر التقرير</label><br>
                    <input type="month" id="report_month" name="report_month" required><br><br>
                </div>
            </div>

            <button class="btn btn-success" type="submit">نشر التقرير</button>
        </form>

        <script>
            function addRow() {
                const tableBody = document.querySelector("#reportTable");
                let newGroup = `
                    <div id="reportTable">
                        <!-- الأهداف والمهمات -->
                        <div class="input-group mb-3">
                            <label for="">الهدف</label>
                            <textarea class="form-control" name="goals[]"></textarea>
                        </div>

                        <div class="input-group mb-3">
                            <label for="">المهام</label>
                            <textarea class="form-control" name="tasks[]"></textarea>
                        </div>


                        <!-- عدد المستفيدين والإيجابيات والسلبيات -->

                        <div class="input-group mb-3">
                            <label>عدد المستفيدين</label>
                            <input class="form-control" type="number" name="number_of_individuals[]">
                        </div>
                        <div class="input-group mb-3">
                            <label>الإيجابيات والسلبيات</label>
                            <textarea class="form-control" name="negatives_and_obstacles[]"></textarea>
                        </div>

                        <!-- التقييم والمبلغ -->

                        <div class="input-group mb-3">
                            <label>التقييم</label>
                            <textarea class="form-control" name="evaluation[]"></textarea>
                        </div>

                        <div class="input-group mb-3">
                            <label>المبلغ</label>
                            <input class="form-control" type="number" step="0.01" name="amount[]">
                        </div>

                        <!-- نسبة التحقق والملاحظات -->

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
                tableBody.innerHTML += newGroup;
            }
        </script>
</body>

</html>