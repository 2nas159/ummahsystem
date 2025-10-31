<?php
include "db_conn.php"; // Include database connection

if (isset($_POST['submit'])) {
   // Collect form data and sanitize inputs
   $ADI = $mysqli->real_escape_string(trim($_POST['ADI']));
   $TEL = $mysqli->real_escape_string(trim($_POST['TEL']));

   // Validate required fields
   if (!empty($ADI) && !empty($TEL)) {
      // Prepare SQL query to insert data into the database
      $sql = "INSERT INTO `donators` (`ADI`, `TEL`) VALUES (?, ?)";
      $stmt = $mysqli->prepare($sql);
      $stmt->bind_param("ss", $ADI, $TEL);

      if ($stmt->execute()) {
         header("Location: add_donators.php?msg=New donator added successfully");
         exit;
      } else {
         echo "Error: " . $mysqli->error;
      }
   } else {
      echo "Please fill in all required fields.";
   }
}
?>

<?php include "header.php" ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
   <div class="container mt-5">
      <div class="text-center mb-4">
         <h2 class="section-title">إضافة متبرع جديد</h2>
         <p class="section-subtitle">الحقول المعلمة مطلوبة</p>
      </div>

      <div class="d-flex justify-content-center">
         <div class="card card-elevated" style="width: 50vw; min-width: 320px;">
            <div class="card-body">
               <form action="" method="POST" novalidate>
                  <div class="mb-3">
                     <label class="form-label">الاسم <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" name="ADI" placeholder="اسم المتبرع" required>
                     <div class="form-text">اكتب الاسم الرباعي إن أمكن</div>
                  </div>

                  <div class="mb-3">
                     <label class="form-label">رقم الهاتف <span class="text-danger">*</span></label>
                     <input type="tel" class="form-control" name="TEL" placeholder="مثال: 05XXXXXXXX" pattern="[0-9]{9,15}" required>
                     <div class="form-text">أرقام فقط من 9 إلى 15 خانة</div>
                  </div>

                  <div class="d-flex gap-2">
                     <button type="submit" class="btn btn-primary" name="submit">إضافة متبرع</button>
                     <a href="donators.php" class="btn btn-outline-secondary">إلغاء</a>
                  </div>
               </form>
            </div>
         </div>
      </div>

      <?php if (isset($_GET['msg'])): ?>
      <div class="toastify" role="status" aria-live="polite"><?php echo htmlspecialchars($_GET['msg']); ?></div>
      <script>
         setTimeout(function(){
            var t = document.querySelector('.toastify');
            if(t){ t.remove(); }
         }, 3000);
      </script>
      <?php endif; ?>
   </div>
</main>