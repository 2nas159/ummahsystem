<?php
include "db_conn.php"; // Include the correct database connection

if (isset($_POST["submit"])) {
   $NO = $_POST['NO'];
   $ADI = $_POST['ADI'];
   $TC = $_POST['TC'];
   $TELEFON = $_POST['TELEFON'];
   $Adres = $_POST['Adres'];
   $UYRUK = $_POST['UYRUK'];

   $sql = "INSERT INTO `tablename`(`NO`, `ADI`, `TC`, `TELEFON`, `Adres`, UYRUK) VALUES ('$NO','$ADI','$TC','$TELEFON','$Adres', '$UYRUK')";

   $result = mysqli_query($mysqli, $sql);

   if ($result) {
      header("Location: add-new.php?msg=New Person created successfully");
   } else {
      echo "Failed: " . mysqli_error($conn);
   }
}

?>

<?php include "header.php" ?>

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
                  <label class="form-label">الرقم</label>
                  <input type="text" class="form-control" name="NO" placeholder="">
               </div>

               <div class="col">
                  <label class="form-label">الإسم</label>
                  <input type="text" class="form-control" name="ADI" placeholder="">
               </div>
            </div>

            <div class="row mb-3">
               <div class="col">
                  <label class="form-label">رقم الهاتف</label>
                  <input type="number" class="form-control" name="TELEFON" placeholder="">
               </div>

               <div class="col">
                  <label class="form-label">رقم الكملك</label>
                  <input type="number" class="form-control" name="TC" placeholder="">
               </div>
            </div>

            <div class="row mb-3">
               <div class="col">
                  <label class="form-label">العنوان</label>
                  <input type="text" class="form-control" name="Adres" placeholder="">
               </div>

               <div class="col">
                  <label class="form-label">الجنسية</label>
                  <input type="text" class="form-control" name="UYRUK" placeholder="">
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