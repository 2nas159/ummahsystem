<?php
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
      <?php echo htmlspecialchars($_GET['msg']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  
  <!-- Search Bar and Add Button -->
  <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
    <h2>المساعدات</h2>
    <div class="d-flex gap-2">
      <a href="add.php" class="btn btn-success">إضافة اسم جديد</a>
      <form method="GET" action="" class="d-flex gap-2" id="searchForm">
        <input type="text" class="form-control" id="search" name="search" 
          placeholder="ابحث عن اسم، رقم الكملك، هاتف، عنوان، أو جنسية..." 
          value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
          style="width: 300px;">
        <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
          <a href="index.php" class="btn btn-secondary">إلغاء البحث</a>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> بحث
        </button>
      </form>
    </div>
  </div>

  <!-- MAIN TABLE -->
  <div id="mainTable" class="table-responsive mt-4">
    <?php
    include "db_conn.php";
    
    // Pagination parameters
    $perPage = 20;
    $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($currentPage < 1) $currentPage = 1;
    $offset = ($currentPage - 1) * $perPage;

    // Search parameter
    $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
    $searchCondition = '';
    $searchParams = [];
    
    if (!empty($searchTerm)) {
      $searchTermEscaped = $mysqli->real_escape_string($searchTerm);
      $searchCondition = "WHERE ADI LIKE '%$searchTermEscaped%' 
                          OR TC LIKE '%$searchTermEscaped%' 
                          OR TELEFON LIKE '%$searchTermEscaped%' 
                          OR Adres LIKE '%$searchTermEscaped%' 
                          OR UYRUK LIKE '%$searchTermEscaped%'";
    }

    // Get total count with search
    $countSql = "SELECT COUNT(*) AS total FROM `tablename` $searchCondition";
    $countResult = $mysqli->query($countSql);
    $totalRows = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;
    $totalPages = ($totalRows > 0) ? ceil($totalRows / $perPage) : 1;
    
    // Ensure current page doesn't exceed total pages
    if ($currentPage > $totalPages && $totalPages > 0) {
      $currentPage = $totalPages;
      $offset = ($currentPage - 1) * $perPage;
    }

    // Paginated query with search and ASC sorting (smaller to bigger)
    $sql = "SELECT * FROM `tablename` $searchCondition ORDER BY NO ASC LIMIT $perPage OFFSET $offset";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
      echo "<table class='table table-bordered table-hover'>";
      echo "<thead><tr><th class='text-center'>الرقم</th><th class='text-center'>الاسم</th><th class='text-center'>رقم الكملك</th><th class='text-center'>رقم الهاتف</th><th class='text-center'>العنوان</th><th class='text-center'>الجنسية</th><th class='text-center'>تعديل</th><th class='text-center'>حذف</th></tr></thead>";
      echo "<tbody>";

      while ($row = $result->fetch_assoc()) {
        echo "<tr id='row_" . $row['NO'] . "'>";
        echo "<td class='text-center'>" . $row['NO'] . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='TC'>" . htmlspecialchars($row['TC']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='TELEFON'>" . htmlspecialchars($row['TELEFON']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='Adres'>" . htmlspecialchars($row['Adres']) . "</td>";
        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='UYRUK'>" . htmlspecialchars($row['UYRUK']) . "</td>";
        echo "<td><button class='btn btn-warning btn-sm edit-btn' data-id='" . $row['NO'] . "'>تعديل</button></td>";
        echo "<td class='text-center'> <a href='delete.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>حذف</a></td>";
        echo "</tr>";
      }

      echo "</tbody></table>";
      
      // Pagination controls
      if ($totalPages > 1) {
        $urlBase = basename(__FILE__);
        $queryParams = [];
        if (!empty($searchTerm)) {
          $queryParams[] = 'search=' . urlencode($searchTerm);
        }
        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
        $separator = empty($queryParams) ? '?' : '&';
        
        echo "<div class='pagination-container'>";
        echo "<div class='pagination-info'>";
        $startRecord = $offset + 1;
        $endRecord = min($offset + $perPage, $totalRows);
        if (!empty($searchTerm)) {
          echo "<span>عرض " . $startRecord . " - " . $endRecord . " من " . $totalRows . " نتيجة للبحث: <strong>" . htmlspecialchars($searchTerm) . "</strong></span>";
        } else {
          echo "<span>عرض " . $startRecord . " - " . $endRecord . " من " . $totalRows . " نتيجة</span>";
        }
        echo "</div>";
        
        echo "<nav aria-label='Pagination' class='pagination-nav'>";
        echo "<ul class='pagination'>";
        
        // First page
        if ($currentPage > 1) {
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=1$queryString' title='الصفحة الأولى'><i class='bi bi-chevron-double-right'></i></a></li>";
        } else {
          echo "<li class='page-item disabled'><span class='page-link'><i class='bi bi-chevron-double-right'></i></span></li>";
        }
        
        // Previous page
        if ($currentPage > 1) {
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=" . ($currentPage - 1) . "$queryString' title='السابق'><i class='bi bi-chevron-right'></i></a></li>";
        } else {
          echo "<li class='page-item disabled'><span class='page-link'><i class='bi bi-chevron-right'></i></span></li>";
        }
        
        // Page numbers with smart ellipsis
        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $currentPage + 2);
        
        if ($startPage > 1) {
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=1$queryString'>1</a></li>";
          if ($startPage > 2) {
            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
          }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
          if ($i == $currentPage) {
            echo "<li class='page-item active'><span class='page-link'>$i</span></li>";
          } else {
            echo "<li class='page-item'><a class='page-link' href='$urlBase?page=$i$queryString'>$i</a></li>";
          }
        }
        
        if ($endPage < $totalPages) {
          if ($endPage < $totalPages - 1) {
            echo "<li class='page-item disabled'><span class='page-link'>...</span></li>";
          }
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=$totalPages$queryString'>$totalPages</a></li>";
        }
        
        // Next page
        if ($currentPage < $totalPages) {
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=" . ($currentPage + 1) . "$queryString' title='التالي'><i class='bi bi-chevron-left'></i></a></li>";
        } else {
          echo "<li class='page-item disabled'><span class='page-link'><i class='bi bi-chevron-left'></i></span></li>";
        }
        
        // Last page
        if ($currentPage < $totalPages) {
          echo "<li class='page-item'><a class='page-link' href='$urlBase?page=$totalPages$queryString' title='الصفحة الأخيرة'><i class='bi bi-chevron-double-left'></i></a></li>";
        } else {
          echo "<li class='page-item disabled'><span class='page-link'><i class='bi bi-chevron-double-left'></i></span></li>";
        }
        
        echo "</ul>";
        echo "</nav>";
        echo "</div>";
      }
    } else {
      echo "<div class='alert alert-info text-center'><p>لا توجد سجلات.</p></div>";
    }
    ?>
  </div>
</main>

<!-- Inline Editing Script -->
<script>
  // Wait for jQuery to be loaded
  (function() {
    function initEditButton() {
      if (typeof jQuery === 'undefined') {
        setTimeout(initEditButton, 100);
        return;
      }
      
      var $ = jQuery;
      
      // Edit button click event - use event delegation for dynamically loaded content
      $(document).on('click', '.edit-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();
        
        var rowId = $(this).data('id');
        var button = $(this);
        
        // Check if we're in edit mode by looking for input fields
        var isEditMode = $('#row_' + rowId).find('input.form-control').length > 0;

        if (!isEditMode) {
          // Enter edit mode
          var name = $('#row_' + rowId).find('td:eq(1)').text().trim();
          var tc = $('#row_' + rowId).find('td:eq(2)').text().trim();
          var phone = $('#row_' + rowId).find('td:eq(3)').text().trim();
          var address = $('#row_' + rowId).find('td:eq(4)').text().trim();
          var nationality = $('#row_' + rowId).find('td:eq(5)').text().trim();

          // Escape HTML for input values
          function escapeHtml(text) {
            var map = {
              '&': '&amp;',
              '<': '&lt;',
              '>': '&gt;',
              '"': '&quot;',
              "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
          }

          $('#row_' + rowId).find('td:eq(1)').html('<input type="text" class="form-control" value="' + escapeHtml(name) + '" id="name_' + rowId + '">');
          $('#row_' + rowId).find('td:eq(2)').html('<input type="text" class="form-control" value="' + escapeHtml(tc) + '" id="tc_' + rowId + '">');
          $('#row_' + rowId).find('td:eq(3)').html('<input type="text" class="form-control" value="' + escapeHtml(phone) + '" id="phone_' + rowId + '">');
          $('#row_' + rowId).find('td:eq(4)').html('<input type="text" class="form-control" value="' + escapeHtml(address) + '" id="address_' + rowId + '">');
          $('#row_' + rowId).find('td:eq(5)').html('<input type="text" class="form-control" value="' + escapeHtml(nationality) + '" id="nationality_' + rowId + '">');

          button.html('تأكيد').removeClass('btn-warning').addClass('btn-success');
        } else {
          // Save changes
          var updatedName = $('#name_' + rowId).val().trim();
          var updatedTc = $('#tc_' + rowId).val().trim();
          var updatedPhone = $('#phone_' + rowId).val().trim();
          var updatedAddress = $('#address_' + rowId).val().trim();
          var updatedNationality = $('#nationality_' + rowId).val().trim();

          // Validate that all fields are filled
          if (!updatedName || !updatedTc || !updatedPhone || !updatedAddress || !updatedNationality) {
            alert('يرجى ملء جميع الحقول');
            return;
          }

          // Disable button during request
          button.prop('disabled', true).html('جاري الحفظ...');

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
              response = response.trim();
              if (response === 'success') {
                // Update the table cells with new values
                $('#row_' + rowId).find('td:eq(1)').text(updatedName);
                $('#row_' + rowId).find('td:eq(2)').text(updatedTc);
                $('#row_' + rowId).find('td:eq(3)').text(updatedPhone);
                $('#row_' + rowId).find('td:eq(4)').text(updatedAddress);
                $('#row_' + rowId).find('td:eq(5)').text(updatedNationality);

                button.html('تعديل').removeClass('btn-success').addClass('btn-warning').prop('disabled', false);
              } else {
                alert('فشل التحديث: ' + response);
                button.html('تأكيد').prop('disabled', false);
              }
            },
            error: function(xhr, status, error) {
              alert('حدث خطأ أثناء التحديث: ' + error);
              button.html('تأكيد').prop('disabled', false);
            }
          });
        }
      });
    }
    
    // Start initialization
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initEditButton);
    } else {
      initEditButton();
    }
  })();

  // Auto-submit search form on Enter key
  document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('search');
    if (searchInput) {
      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          document.getElementById('searchForm').submit();
        }
      });
    }
  });
</script>

<style>
  /* Pagination Styles */
  .pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding: 20px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    flex-wrap: wrap;
    gap: 15px;
  }

  .pagination-info {
    color: #64748b;
    font-size: 14px;
    font-weight: 500;
  }

  .pagination-nav {
    display: flex;
    align-items: center;
  }

  .pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    gap: 4px;
    flex-wrap: wrap;
  }

  .page-item {
    margin: 0;
  }

  .page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 8px 12px;
    color: #475569;
    background-color: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.2s ease;
    cursor: pointer;
  }

  .page-link:hover:not(.disabled):not(.active) {
    background-color: #f1f5f9;
    border-color: #cbd5e1;
    color: #0ea5e9;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .page-item.active .page-link {
    background-color: #0ea5e9;
    border-color: #0ea5e9;
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(14, 165, 233, 0.3);
  }

  .page-item.disabled .page-link {
    color: #cbd5e1;
    background-color: #f8fafc;
    border-color: #e2e8f0;
    cursor: not-allowed;
    opacity: 0.6;
  }

  .page-link i {
    font-size: 16px;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .pagination-container {
      flex-direction: column;
      align-items: stretch;
    }

    .pagination-info {
      text-align: center;
      width: 100%;
    }

    .pagination-nav {
      justify-content: center;
      width: 100%;
    }

    .pagination {
      justify-content: center;
    }

    .page-link {
      min-width: 36px;
      height: 36px;
      padding: 6px 10px;
      font-size: 13px;
    }
  }

  @media (max-width: 480px) {
    .page-link {
      min-width: 32px;
      height: 32px;
      padding: 4px 8px;
      font-size: 12px;
    }

    .page-link i {
      font-size: 14px;
    }
  }
</style>
