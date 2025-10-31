<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
  <!-- Search Bar and Add Button -->
  <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <h2>المساعدات</h2>
    <div class="d-flex gap-2">
      <a href="add-new.php" class="btn btn-success">إضافة اسم جديد</a>
      <input type="text" class="form-control" id="search" placeholder="ابحث عن ..." onkeyup="liveSearch()"
        style="width: 250px;">
    </div>
  </div>

  <!-- MAIN TABLE -->
  <div id="mainTable" class="table-responsive mt-4">
    <?php
    include "db_conn.php";
    $sql = "SELECT * FROM `tablename`";
    $result = $mysqli->query($sql);

    if ($result->num_rows > 0) {
      echo "<table class='table table-bordered table-hover'>";
      echo "<thead><tr><th class='text-center'>الرقم</th><th class='text-center'>الاسم</th><th class='text-center'>رقم الكملك</th><th class='text-center'>رقم الهاتف</th><th class='text-center'>العنوان</th><th class='text-center'>الجنسية</th><th class='text-center'>تعديل</th><th class='text-center'>حذف</th></tr></thead>";
      echo "<tbody>";

      while ($row = $result->fetch_assoc()) {
        echo "<tr id='row_" . $row['NO'] . "'>";
        echo "<td class='text-center'>" . $row['NO'] . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['TC'] . "' data-field='ADI'>" . htmlspecialchars($row['TC']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='TELEFON'>" . htmlspecialchars($row['TELEFON']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='Adres'>" . htmlspecialchars($row['Adres']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='UYRUK'>" . htmlspecialchars($row['UYRUK']) . "</td>";
        echo "<td><button class='btn btn-warning btn-sm edit-btn' data-id='" . $row['NO'] . "'>تعديل</button></td>";
        echo "<td class='text-center'> <a href='delete.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>حذف</a></td>";
        echo "</tr>";
      }

      echo "</tbody></table>";
    } else {
      echo "<p>No records found.</p>";
    }
    ?>
  </div>
</main>

<!-- Inline Editing Script -->
<script>
  $(document).ready(function () {
    // Edit button click event
    $('.edit-btn').click(function () {
      var rowId = $(this).data('id');
      var button = $(this);

      // Toggle between edit and confirm
      if (button.text().trim() === 'تعديل') {
        var name = $('#row_' + rowId).find('td:eq(1)').text();
        var tc = $('#row_' + rowId).find('td:eq(2)').text();
        var phone = $('#row_' + rowId).find('td:eq(3)').text();
        var address = $('#row_' + rowId).find('td:eq(4)').text();
        var nationality = $('#row_' + rowId).find('td:eq(5)').text();

        $('#row_' + rowId).find('td:eq(1)').html('<input type="text" class="form-control" value="' + name + '" id="name_' + rowId + '">');
        $('#row_' + rowId).find('td:eq(2)').html('<input type="text" class="form-control" value="' + tc + '" id="tc_' + rowId + '">');
        $('#row_' + rowId).find('td:eq(3)').html('<input type="text" class="form-control" value="' + phone + '" id="phone_' + rowId + '">');
        $('#row_' + rowId).find('td:eq(4)').html('<input type="text" class="form-control" value="' + address + '" id="address_' + rowId + '">');
        $('#row_' + rowId).find('td:eq(5)').html('<input type="text" class="form-control" value="' + nationality + '" id="nationality_' + rowId + '">');

        button.html('<i class="fas fa-check"></i> تأكيد');
      } else {
        var updatedName = $('#name_' + rowId).val();
        var updatedTc = $('#tc_' + rowId).val();
        var updatedPhone = $('#phone_' + rowId).val();
        var updatedAddress = $('#address_' + rowId).val();
        var updatedNationality = $('#nationality_' + rowId).val();

        // AJAX request to update data
        $.ajax({
          url: 'edit.php',
          method: 'POST',
          data: {
            NO: rowId,
            ADI: updatedName,
            TC: updatedTc,
            TELEFON: updatedPhone,
            Adres: updatedAddress,
            UYRUK: updatedNationality
          },
          success: function (response) {
            if (response === 'success') {
              $('#row_' + rowId).find('td:eq(1)').text(updatedName);
              $('#row_' + rowId).find('td:eq(2)').text(updatedTc);
              $('#row_' + rowId).find('td:eq(3)').text(updatedPhone);
              $('#row_' + rowId).find('td:eq(4)').text(updatedAddress);
              $('#row_' + rowId).find('td:eq(5)').text(updatedNationality);

              button.html('<i class="fas fa-edit"></i> تعديل');
            } else {
              alert('Failed to update. Please try again.');
            }
          }
        });
      }
    });
  });

  function liveSearch() {
    var input = document.getElementById('search');
    var filter = input.value.toLowerCase();
    var table = document.querySelector('#mainTable table');
    var rows = table.querySelectorAll('tbody tr');

    rows.forEach(function (row) {
      var name = row.cells[1].textContent.toLowerCase();
      row.style.display = name.includes(filter) ? '' : 'none';
    });
  }
</script>