<?php
include "db_conn.php"; // Database connection

if (isset($_POST['query'])) {
    $query = $_POST['query'];

    // SQL query with a LIKE clause to match relevant fields (e.g., name, phone)
    $sql = "SELECT * FROM `tablename` WHERE ADI LIKE ? OR TELEFON LIKE ?";
    $stmt = $mysqli->prepare($sql);
    $search = "%" . $query . "%";
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>الرقم</th><th>الاسم</th><th>رقم الهاتف</th><th>العنوان</th><th>الجنسية</th><th>العمليات</th></tr></thead>";
        echo "<tbody>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr id='row_" . $row['NO'] . "'>";
            echo "<td>" . $row['NO'] . "</td>";
            echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
            echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='TELEFON'>" . htmlspecialchars($row['TELEFON']) . "</td>";
            echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='Adres'>" . htmlspecialchars($row['Adres']) . "</td>";
            echo "<td class='editable' data-id='" . $row['NO'] . "' data-field='UYRUK'>" . htmlspecialchars($row['UYRUK']) . "</td>";
            echo "<td><button class='btn btn-warning btn-sm edit-btn' data-id='" . $row['NO'] . "'>تعديل</button></td>";
            echo "<td> <a href='delete.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"هل أنت متأكد من أنك تريد حذف هذا الاسم?\")'>حذف</a></td>";
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
            // Toggle row editing
            $('.edit-btn').click(function () {
                var rowId = $(this).data('id');
                var button = $(this);

                // If button is in "Edit" mode
                if (button.text() === 'تعديل') {
                    // Convert table cells to input fields
                    var name = $('#row_' + rowId).find('td:eq(1)').text();
                    var phone = $('#row_' + rowId).find('td:eq(2)').text();
                    var address = $('#row_' + rowId).find('td:eq(3)').text();
                    var nationality = $('#row_' + rowId).find('td:eq(4)').text();

                    $('#row_' + rowId).find('td:eq(1)').html('<input type="text" class="form-control" value="' + name + '" id="name_' + rowId + '">');
                    $('#row_' + rowId).find('td:eq(2)').html('<input type="text" class="form-control" value="' + phone + '" id="phone_' + rowId + '">');
                    $('#row_' + rowId).find('td:eq(3)').html('<input type="text" class="form-control" value="' + address + '" id="address_' + rowId + '">');
                    $('#row_' + rowId).find('td:eq(4)').html('<input type="text" class="form-control" value="' + nationality + '" id="nationality_' + rowId + '">');

                    // Change button text to "Confirm"
                    button.text('تأكيد');
                } else {
                    // Get updated values
                    var updatedName = $('#name_' + rowId).val();
                    var updatedPhone = $('#phone_' + rowId).val();
                    var updatedAddress = $('#address_' + rowId).val();
                    var updatedNationality = $('#nationality_' + rowId).val();

                    // Perform AJAX request to update the data
                    $.ajax({
                        url: 'edit.php',
                        method: 'POST',
                        data: {
                            NO: rowId,
                            ADI: updatedName,
                            TELEFON: updatedPhone,
                            Adres: updatedAddress,
                            UYRUK: updatedNationality
                        },
                        success: function (response) {
                            if (response === 'success') {
                                // Replace inputs with updated text
                                $('#row_' + rowId).find('td:eq(1)').text(updatedName);
                                $('#row_' + rowId).find('td:eq(2)').text(updatedPhone);
                                $('#row_' + rowId).find('td:eq(3)').text(updatedAddress);
                                $('#row_' + rowId).find('td:eq(4)').text(updatedNationality);

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