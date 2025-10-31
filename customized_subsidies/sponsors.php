<?php
// الاتصال بقاعدة البيانات
include('db_connection.php');

// معالجة الإضافة
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_sponsor'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    $query_add = "INSERT INTO sponsors (name, phone) VALUES (?, ?)";
    $stmt_add = $conn->prepare($query_add);
    $stmt_add->bind_param('ss', $name, $phone);
    $stmt_add->execute();
    
    header("Location: sponsors.php");
    exit();
}


// معالجة الحذف
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $query_delete = "DELETE FROM sponsors WHERE id = ?";
    $stmt_delete = $conn->prepare($query_delete);
    $stmt_delete->bind_param('i', $id);
    $stmt_delete->execute();
    
    header("Location: sponsors.php");
    exit();
}

// معالجة التعديل
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_sponsor'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    
    $query_edit = "UPDATE sponsors SET name = ?, phone = ? WHERE id = ?";
    $stmt_edit = $conn->prepare($query_edit);
    $stmt_edit->bind_param('ssi', $name, $phone, $id);
    $stmt_edit->execute();

    header("Location: sponsors.php");
    exit();
}

// جلب جميع الكفلاء من قاعدة البيانات
$query_select = "SELECT * FROM sponsors";
$result = $conn->query($query_select);

$title = "إدارة الكفلاء";
ob_start();
?>

<div class="container page-section">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="page-title m-0">إدارة الكفلاء</h2>
    </div>

    <div class="card card-elevated mb-3">
        <div class="card-body">
            <form method="POST" action="" class="row g-3">
                <div class="col-md-5">
                    <label for="name" class="form-label">اسم الكفيل</label>
                    <input type="text" class="form-control" id="name" name="name">
                </div>
                <div class="col-md-5">
                    <label for="phone" class="form-label">رقم الهاتف</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" name="add_sponsor" class="btn btn-primary w-100 btn-icon"><i class="bi bi-person-plus"></i><span>إضافة</span></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-elevated">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-modern m-0">
                    <thead>
                        <tr>
                            <th>الرقم</th>
                            <th>اسم الكفيل</th>
                            <th>رقم الهاتف</th>
                            <th class="text-nowrap">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['phone']; ?></td>
                                <td class="text-nowrap">
                                    <button class="btn btn-warning btn-sm btn-icon" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>"><i class="bi bi-pencil-square"></i><span>تعديل</span></button>
                                    <a href="sponsors.php?delete=<?php echo $row['id']; }?>" class="btn btn-danger btn-sm btn-icon ms-1" onclick="return confirm('هل أنت متأكد من الحذف؟')"><i class="bi bi-trash"></i><span>حذف</span></a>

                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editModalLabel<?php echo $row['id']; ?>">تعديل الكفيل</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">اسم الكفيل</label>
                                                            <input type="text" class="form-control" name="name" value="<?php echo $row['name']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">رقم الهاتف</label>
                                                            <input type="text" class="form-control" name="phone" value="<?php echo $row['phone']; ?>" required>
                                                        </div>
                                                        <button type="submit" name="edit_sponsor" class="btn btn-primary">حفظ التغييرات</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include('header.php');
?>