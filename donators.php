<?php 
require_once __DIR__ . '/classes/Security.php';
include "header.php" 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 page-section">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title m-0">المتبرعين</h2>
            <div class="d-flex align-items-center gap-2">
                <a href="secure_add_donators.php" class="btn btn-primary btn-icon">
                    <i class="bi bi-person-plus"></i><span>إضافة متبرع جديد</span>
                </a>
                <input type="text" class="form-control" id="search"
                    placeholder="ابحث عن متبرعين" onkeyup="liveSearch()" style="width: 280px;">
            </div>
        </div>

        <!-- Search results and main table container -->
        <div id="searchResult" class="mt-4"></div>

        <!-- Main table that will be hidden when searching -->
        <div id="mainTable" class="mt-4">
            <?php
            include "secure_donators_db.php"; // Connect to the database
            
            // Pagination parameters
            $perPage = 20;
            $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
            if ($currentPage < 1) $currentPage = 1;
            $offset = ($currentPage - 1) * $perPage;

            // Get total count
            try {
                $countStmt = $mysqli->query("SELECT COUNT(*) AS total FROM donators");
                $row = $countStmt ? $countStmt->fetch(PDO::FETCH_ASSOC) : false;
                $totalRows = $row ? (int)$row['total'] : 0;
            } catch (Exception $e) {
                $totalRows = 0;
            }
            $totalPages = ($totalRows > 0) ? ceil($totalRows / $perPage) : 1;

            // Paginated fetch
            $sql = "SELECT * FROM `donators` ORDER BY NO ASC LIMIT $perPage OFFSET $offset";
            try {
                /** @var Database $mysqli */
                $stmt = $mysqli->query($sql);
                $donators = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                if ($donators && count($donators) > 0) {
                    echo "<div class='card card-elevated'><div class='card-body p-0'><div class='table-responsive'>";
                    echo "<table class='table table-striped table-modern m-0'>";
                    echo "<thead><tr><th>الرقم</th><th>الاسم</th><th>رقم الهاتف</th><th class='text-nowrap'>العمليات</th></tr></thead>";
                    echo "<tbody id='donatorsTable'>";
                    foreach ($donators as $row) {
                        echo "<tr id='row_" . $row['NO'] . "'>";
                        echo "<td>" . $row['NO'] . "</td>";
                        echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
                        echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='TEL'>" . htmlspecialchars($row['TEL']) . "</td>";
                        echo "<td class='text-nowrap'>
                        <button class='btn btn-warning btn-sm btn-icon edit-btn' data-id='" . $row['NO'] . "'><i class='bi bi-pencil-square'></i><span>تعديل</span></button>
                        <a href='secure_delete_donators.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm btn-icon ms-2' onclick='return confirm(\"هل أنت متأكد من الحذف؟\")'><i class='bi bi-trash'></i><span>حذف</span></a>
                        </td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table></div></div></div>";

                    // Pagination controls
                    if ($totalPages > 1) {
                        echo "<div class='pagination-wrapper' style='display:flex;justify-content:center;align-items:center;margin:20px 0;'>";
                        echo "<nav aria-label='Pagination' style='width:auto;'><ul class='pagination pagination-sm mb-0 flex-wrap' style='gap:2px;'>";
                        $urlBase = basename(__FILE__);
                        $separator = '?';
                        // Previous
                        if ($currentPage > 1) {
                            echo "<li class='page-item'><a class='page-link' href='$urlBase{$separator}page=" . ($currentPage - 1) . "'>&laquo;</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><span class='page-link'>&laquo;</span></li>";
                        }
                        // Pages 
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $currentPage) {
                                echo "<li class='page-item active'><span class='page-link'>$i</span></li>";
                            } else {
                                echo "<li class='page-item'><a class='page-link' href='$urlBase{$separator}page=$i'>$i</a></li>";
                            }
                        }
                        // Next
                        if ($currentPage < $totalPages) {
                            echo "<li class='page-item'><a class='page-link' href='$urlBase{$separator}page=" . ($currentPage + 1) . "'>&raquo;</a></li>";
                        } else {
                            echo "<li class='page-item disabled'><span class='page-link'>&raquo;</span></li>";
                        }
                        echo "</ul></nav>";
                        echo "</div>"; // end .pagination-wrapper
                    }
                } else {
                    echo "<div class='card card-elevated'><div class='card-body'><div class='empty-state'>لا يوجد متبرعون حاليا</div></div></div>";
                }
            } catch (Exception $e) {
                echo "<div class='card card-elevated'><div class='card-body'><div class='empty-state text-danger'>حدث خطأ في قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</div></div></div>";
            }
            ?>
        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="js/script.js" defer></script>

<script>
    $(document).ready(function () {
        // Toggle row editing
        $('.edit-btn').click(function () {
            var rowId = $(this).data('id');
            var button = $(this);

            // If button is in "Edit" mode
            if (button.text() === 'تعديل') {
                // Convert table cells to input fields
                var name = $('#row_' + rowId).find('td:eq(1)').text();
                var tel = $('#row_' + rowId).find('td:eq(2)').text();

                $('#row_' + rowId).find('td:eq(1)').html('<input type="text" class="form-control" value="' + name + '" id="name_' + rowId + '">');
                $('#row_' + rowId).find('td:eq(2)').html('<input type="text" class="form-control" value="' + tel + '" id="tel_' + rowId + '">');

                // Change button text to "Confirm"
                button.text('تأكيد');
            } else {
                // Get updated values
                var updatedName = $('#name_' + rowId).val();
                var updatedTel = $('#tel_' + rowId).val();

                // Perform AJAX request to update the data
                $.ajax({
                    url: 'secure_update_donator.php',
                    method: 'POST',
                    data: {
                        NO: rowId,
                        ADI: updatedName,
                        TEL: updatedTel,
                        csrf_token: '<?php echo Security::generateCSRFToken(); ?>'
                    },
                    success: function (response) {
                        if (response === 'success') {
                            // Replace inputs with updated text
                            $('#row_' + rowId).find('td:eq(1)').text(updatedName);
                            $('#row_' + rowId).find('td:eq(2)').text(updatedTel);

                            // Change button text back to "Edit"
                            button.text('تعديل');
                        } else {
                            alert('Failed to update. Please try again.');
                        }
                    }
                });
            }
        });
    });

    function liveSearch() {
        var query = $("#search").val();
        if (query != "") {
            // Hide the main table when searching
            $("#mainTable").hide();

            // Perform AJAX request to get search results
            $.ajax({
                url: "secure_donators_live_search.php",
                method: "POST",
                data: { query: query },
                success: function (data) {
                    $("#searchResult").html(data); // Show search results
                }
            });
        } else {
            // If the input is cleared, show the main table again
            $("#mainTable").show();
            $("#searchResult").html(""); // Clear search results
        }
    }
</script>