<?php
/**
 * Secure Donators Live Search
 * Replaces donators_live_search.php with secure implementation
 */

require_once __DIR__ . '/secure_donators_db.php';
require_once __DIR__ . '/classes/Security.php';
require_once __DIR__ . '/classes/User.php';

// Check if user is logged in
$userAuth = new User();
if (!$userAuth->isLoggedIn()) {
    echo "<p>غير مصرح بالوصول</p>";
    exit();
}

if (isset($_POST['query'])) {
    $query = Security::sanitizeInput($_POST['query']);
    
    // Validate query length
    if (strlen($query) < 2) {
        echo "<p>يرجى إدخال حرفين على الأقل للبحث</p>";
        exit();
    }
    
    try {
        // Prepare the SQL query with LIKE statement to search for similar entries
        $sql = "SELECT * FROM donators WHERE ADI LIKE ? OR TEL LIKE ? ORDER BY ADI ASC";
        $searchTerm = "%" . $query . "%";
        $result = $mysqli->fetchAll($sql, [$searchTerm, $searchTerm]);
        
        // Check if any rows were returned
        if (!empty($result)) {
            // Output results as a table
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>الرقم</th><th>الاسم</th><th>رقم الهاتف</th><th>العمليات</th></tr></thead>";
            echo "<tbody>";
            
            foreach ($result as $row) {
                echo "<tr id='row_" . htmlspecialchars($row['NO']) . "'>";
                echo "<td>" . htmlspecialchars($row['NO']) . "</td>";
                echo "<td class='editable' data-id='" . htmlspecialchars($row['NO']) . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
                echo "<td class='editable' data-id='" . htmlspecialchars($row['NO']) . "' data-field='TEL'>" . htmlspecialchars($row['TEL']) . "</td>";
                echo "<td>
                        <button class='btn btn-warning btn-sm edit-btn' data-id='" . htmlspecialchars($row['NO']) . "'>تعديل</button>
                        <a href='secure_delete_donators.php?NO=" . htmlspecialchars($row['NO']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"هل أنت متأكد من الحذف؟\")'>حذف</a>
                      </td>";
                echo "</tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>لم يتم العثور على نتائج.</p>";
        }
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        echo "<p>حدث خطأ في البحث</p>";
    }
} else {
    echo "<p>لم يتم إرسال استعلام البحث</p>";
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
                
                // Validate inputs
                if (!updatedName.trim() || !updatedTel.trim()) {
                    alert('يرجى ملء جميع الحقول');
                    return;
                }
                
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
                        var result = JSON.parse(response);
                        if (result.status === 'success') {
                            // Replace inputs with updated text
                            $('#row_' + rowId).find('td:eq(1)').text(updatedName);
                            $('#row_' + rowId).find('td:eq(2)').text(updatedTel);
                            
                            // Change button text back to "Edit"
                            button.text('تعديل');
                        } else {
                            alert('فشل في التحديث: ' + result.message);
                        }
                    },
                    error: function() {
                        alert('حدث خطأ في الاتصال');
                    }
                });
            }
        });
    });
</script>
