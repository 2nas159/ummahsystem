<?php

$host = "localhost";
$user = "root";
$password = "";
$db = "u850876726_users";

session_start();

$data = mysqli_connect($host, $user, $password, $db);

if ($data === false) {
    die("connection error");
}

$error = "";
$isim = ""; // المتغير الجديد لعرض اسم المستخدم
$profile_image = ""; // المتغير الجديد لعرض صورة المستخدم

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $isim = $username; // تخزين اسم المستخدم الذي تم إدخاله

    // التحقق من وجود اسم المستخدم
    $sql = "SELECT * FROM login WHERE username='" . $username . "'";
    $result = mysqli_query($data, $sql);
    $row = mysqli_fetch_array($result);

    if ($row) {
        // التحقق من كلمة المرور
        if ($row["password"] == $password) {
            // تخزين اسم المستخدم وصورة المستخدم في الجلسة
            $_SESSION["username"] = $username;
            $_SESSION["isim"] = $isim;
            $_SESSION["profile_image"] = $row["profile_image"]; // تخزين صورة المستخدم في الجلسة

            if ($row["usertype"] == "user") {
                header("location:home_hr.php");
            } elseif ($row["usertype"] == "admin") {
                header("location:home_admin.php");
            }
        } else {
            // كلمة مرور غير صحيحة
            $error = "كلمة المرور غير صحيحة.";
        }
    } else {
        // اسم المستخدم غير موجود
        $error = "اسم المستخدم غير موجود.";
    }
}

?>
