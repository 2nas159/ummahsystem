<?php
include "db_conn.php"; // Include the correct database connection

if (isset($_POST["submit"])) {
   // Get the maximum NO value and add 1 for the new record
   $maxNoQuery = "SELECT MAX(NO) as max_no FROM `tablename`";
   $maxResult = $mysqli->query($maxNoQuery);
   $maxRow = $maxResult->fetch_assoc();
   $NO = ($maxRow['max_no'] ?? 0) + 1;
   
   $ADI = $_POST['ADI'];
   $TC = $_POST['TC'];
   $TELEFON = $_POST['TELEFON'];
   $Adres = $_POST['Adres'];
   $UYRUK = $_POST['UYRUK'];

   $sql = "INSERT INTO `tablename`(`NO`, `ADI`, `TC`, `TELEFON`, `Adres`, UYRUK) VALUES ('$NO','$ADI','$TC','$TELEFON','$Adres', '$UYRUK')";

   $result = mysqli_query($mysqli, $sql);

   if ($result) {
      header("Location: index.php?msg=New Person created successfully");
   } else {
      echo "Failed: " . mysqli_error($mysqli);
   }
}

?>

<?php
$BASE_PATH_PREFIX = '../';
require_once __DIR__ . '/../layout.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
   <div class="container mt-4">
      <div class="text-center mb-4">
         <h3>أضف اسم جديد</h3>
         <p class="text-muted">اكمل الفورم ادناه لاضافة اسم جديد</p>
      </div>

      <div class="container d-flex justify-content-center">
         <form action="" method="post" style="width:50vw; min-width:300px;">
            <div class="row mb-3">
               <div class="col">
                  <label class="form-label">الإسم</label>
                  <input type="text" class="form-control" name="ADI" placeholder="" required>
               </div>
            </div>

            <div class="row mb-3">
               <div class="col">
                  <label class="form-label">رقم الهاتف</label>
                  <input type="text" class="form-control" name="TELEFON" placeholder="" required>
               </div>

               <div class="col">
                  <label class="form-label">رقم الكملك</label>
                  <input type="text" class="form-control" name="TC" placeholder="" required>
               </div>
            </div>

            <div class="row mb-3">
               <div class="col">
                  <label class="form-label">العنوان</label>
                  <input type="text" class="form-control" name="Adres" placeholder="" required>
               </div>

               <div class="col">
                  <label class="form-label">الجنسية</label>
                  <input type="text" class="form-control" name="UYRUK" placeholder="" required>
               </div>
            </div>

            <div>
               <button type="submit" class="btn btn-success" name="submit">إضافة</button>
               <a href="index.php" class="btn btn-danger">إلغاء</a>
            </div>
         </form>
      </div>
   </div>
</main>
