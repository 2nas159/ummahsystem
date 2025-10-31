<?php
include "donators_db.php"; // Connect to the database

if (isset($_POST['query'])) {
    $query = $_POST['query'];

    // Prepare the SQL query with a LIKE statement to search for similar entries
    $sql = "SELECT * FROM `donators` WHERE ADI LIKE ? OR TEL LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $search = "%" . $query . "%";
    $stmt->bind_param("ss", $search, $search); // Search by ADI and TEL

    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows were returned
    if ($result->num_rows > 0) {
        // Output results as a table
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>الرقم</th><th>الاسم</th><th>رقم الهاتف</th><th>العمليات</th></tr></thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr id='row_".$row['NO']."'>";
            echo "<td>" . $row['NO'] . "</td>";
            echo "<td class='editable' data-id='".$row['NO']."' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
            echo "<td class='editable' data-id='".$row['NO']."' data-field='TEL'>" . htmlspecialchars($row['TEL']) . "</td>";
            echo "<td>
                    <button class='btn btn-warning btn-sm edit-btn' data-id='" . $row['NO'] . "'>تعديل</button>
                    <a href='delete_donators.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>حذف</a>
                  </td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No results found.</p>";
    }
}
?>

<script>
    $(document).ready(function () {
        // Toggle row editing for search results
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
                    url: 'update_donator.php',
                    method: 'POST',
                    data: {
                        NO: rowId,
                        ADI: updatedName,
                        TEL: updatedTel
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
</script>
