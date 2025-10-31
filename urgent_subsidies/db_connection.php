<?php
$servername = "localhost"; // اسم الخادم
$username = "root"; // اسم المستخدم لقاعدة البيانات
$password = ""; // كلمة المرور (اتركها فارغة إذا كنت تستخدم XAMPP أو MAMP بدون كلمة مرور)
$dbname = "u850876726_urgent"; // اسم قاعدة البيانات

// إنشاء اتصال بقاعدة البيانات
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

