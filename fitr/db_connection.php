<?php
$servername = "localhost";  // قد يكون 127.0.0.1
$username = "root";         // أو اسم مستخدم آخر إذا قمت بتغييره
$password = "";             // إذا لم تضع كلمة مرور، اتركه فارغًا
$dbname = "u850876726_fitr";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التأكد من نجاح الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

$conn->set_charset("utf8"); // ضبط الترميز لدعم اللغة العربية
?>
