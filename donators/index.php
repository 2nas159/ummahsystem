<?php 
require_once __DIR__ . '/../classes/Security.php';
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 page-section">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="page-title m-0">المتبرعين</h2>
            <div class="d-flex align-items-center gap-2">
                <a href="add.php" class="btn btn-primary btn-icon">
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
            include "donators_db.php"; // Connect to the database
            
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
            
            // Ensure current page doesn't exceed total pages
            if ($currentPage > $totalPages && $totalPages > 0) {
                $currentPage = $totalPages;
                $offset = ($currentPage - 1) * $perPage;
            }

            // Paginated fetch
            $sql = "SELECT * FROM `donators` ORDER BY NO ASC LIMIT $perPage OFFSET $offset";
            try {
                /** @var Database $mysqli */
                $stmt = $mysqli->query($sql);
                $donators = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
                if ($donators && count($donators) > 0) {
                    echo "<div class='card card-elevated'><div class='card-body p-0'><div class='table-responsive'>";
                    echo "<table class='table table-striped table-modern m-0'>";
                    echo "<thead><tr><th class='text-center'>الرقم</th><th class='text-center'>الاسم</th><th class='text-center'>رقم الهاتف</th><th class='text-center text-nowrap'>العمليات</th></tr></thead>";
                    echo "<tbody id='donatorsTable'>";
                    $rowNumber = $offset + 1; // Sequential numbering starting from current page
                    foreach ($donators as $row) {
                        echo "<tr id='row_" . $row['NO'] . "'>";
                        echo "<td class='text-center'>" . $rowNumber . "</td>";
                        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='ADI'>" . htmlspecialchars($row['ADI']) . "</td>";
                        echo "<td class='text-center editable' data-id='" . $row['NO'] . "' data-field='TEL'>" . htmlspecialchars($row['TEL']) . "</td>";
                        echo "<td class='text-center text-nowrap'>
                        <button class='btn btn-warning btn-sm btn-icon edit-btn' data-id='" . $row['NO'] . "'><i class='bi bi-pencil-square'></i><span>تعديل</span></button>
                        <a href='delete.php?NO=" . $row['NO'] . "' class='btn btn-danger btn-sm btn-icon ms-2' onclick='return confirm(\"هل أنت متأكد من الحذف؟\")'><i class='bi bi-trash'></i><span>حذف</span></a>
                        </td>";
                        echo "</tr>";
                        $rowNumber++;
                    }
                    echo "</tbody></table></div></div></div>";

                    // Pagination controls - matching help.php style
                    if ($totalPages > 1) {
                        $urlBase = basename(__FILE__);
                        $queryParams = [];
                        $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
                        $separator = empty($queryParams) ? '?' : '&';
                        
                        echo "<div class='pagination-container'>";
                        echo "<div class='pagination-info'>";
                        $startRecord = $offset + 1;
                        $endRecord = min($offset + $perPage, $totalRows);
                        echo "<span>عرض " . $startRecord . " - " . $endRecord . " من " . $totalRows . " نتيجة</span>";
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
                    echo "<div class='card card-elevated'><div class='card-body'><div class='empty-state'>لا يوجد متبرعون حاليا</div></div></div>";
                }
            } catch (Exception $e) {
                echo "<div class='card card-elevated'><div class='card-body'><div class='empty-state text-danger'>حدث خطأ في قاعدة البيانات: " . htmlspecialchars($e->getMessage()) . "</div></div></div>";
            }
            ?>
        </div>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script src="../js/script.js" defer></script>

<script>
    $(document).ready(function () {
        // Toggle row editing - use event delegation for dynamically loaded content
        $(document).on('click', '.edit-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            var rowId = $(this).data('id');
            var button = $(this);
            var row = $('#row_' + rowId);
            
            // Check if we're in edit mode by looking for input fields
            var isEditMode = row.find('input.form-control').length > 0;

            if (!isEditMode) {
                // Enter edit mode
                var name = row.find('td:eq(1)').text().trim();
                var tel = row.find('td:eq(2)').text().trim();

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

                row.find('td:eq(1)').html('<input type="text" class="form-control" value="' + escapeHtml(name) + '" id="name_' + rowId + '">');
                row.find('td:eq(2)').html('<input type="text" class="form-control" value="' + escapeHtml(tel) + '" id="tel_' + rowId + '">');

                // Change button to "Confirm"
                button.html('<i class="bi bi-check2-circle"></i><span>تأكيد</span>').removeClass('btn-warning').addClass('btn-success');
            } else {
                // Save changes
                var updatedName = $('#name_' + rowId).val().trim();
                var updatedTel = $('#tel_' + rowId).val().trim();

                // Validate that all fields are filled
                if (!updatedName || !updatedTel) {
                    alert('يرجى ملء جميع الحقول');
                    return;
                }

                // Disable button during request
                button.prop('disabled', true).html('<span>جاري الحفظ...</span>');

                // Perform AJAX request to update the data
                $.ajax({
                    url: 'update.php',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        NO: rowId,
                        ADI: updatedName,
                        TEL: updatedTel,
                        csrf_token: '<?php echo Security::generateCSRFToken(); ?>'
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            // Replace inputs with updated text
                            row.find('td:eq(1)').text(updatedName);
                            row.find('td:eq(2)').text(updatedTel);

                            // Change button back to "Edit"
                            button.html('<i class="bi bi-pencil-square"></i><span>تعديل</span>').removeClass('btn-success').addClass('btn-warning').prop('disabled', false);
                        } else {
                            alert('فشل التحديث: ' + (response.message || 'حدث خطأ'));
                            button.html('<i class="bi bi-check2-circle"></i><span>تأكيد</span>').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', xhr.responseText);
                        var errorMessage = 'حدث خطأ أثناء التحديث';
                        try {
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                        } catch (e) {
                            errorMessage = error;
                        }
                        alert(errorMessage);
                        button.html('<i class="bi bi-check2-circle"></i><span>تأكيد</span>').prop('disabled', false);
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
                url: "live_search.php",
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

<style>
  /* Pagination Styles - matching help.php */
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
    }
  }
</style>
