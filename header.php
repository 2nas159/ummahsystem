<?php
include('secure_users_db.php'); // تأكد من تضمين secure_users_db.php بعد session_start()

if (!isset($_SESSION["username"])) {
    header("location:index.php");
    exit(); // تأكد من إيقاف الشيفرة بعد التوجيه
}

// التحقق مما إذا كان المستخدم مسجل الدخول واسترجاع اسم المستخدم من الجلسة
$isim = isset($_SESSION["isim"]) ? $_SESSION["isim"] : ''; // استخدام isset لتجنب الأخطاء
$profile_image = isset($_SESSION["profile_image"]) ? $_SESSION["profile_image"] : 'default.png'; // إذا لم توجد صورة، استخدم صورة افتراضية

$current_page = basename($_SERVER['PHP_SELF']);

?>

<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="auto">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <title>جمعية أمة الخيرية</title>

    <link rel="apple-touch-icon" sizes="180x180" href="assets/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon-16x16.png">
    <link rel="manifest" href="assets/site.webmanifest">
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/dashboard-rtl/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <!-- Custom styles for this template -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="css/dashboard.rtl.css" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Global theme tokens and overrides -->
    <link rel="stylesheet" href="css/theme.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/index.css">

    <style>
        .user-profile-img {
            width: 80px;
            /* العرض */
            height: 80px;
            /* الارتفاع */
            object-fit: cover;
            /* لضمان ملاءمة الصورة داخل الحاوية دون تشويه */
            border-radius: 50%;
            /* تحويل الصورة إلى شكل دائري */
            border: 2px solid #ddd;
            /* إضافة حدود خفيفة حول الصورة */
        }

        /* Dark theme variables for sidebar */
        :root {
            --sidebar-bg: #1e293b;
            --sidebar-text: #f8fafc;
            --sidebar-hover: #334155;
            --sidebar-muted: #94a3b8;
            --sidebar-accent: #38bdf8;
            --sidebar-active: #38bdf8;
            --sidebar-border: #334155;
        }

        /* Apply dark theme to sidebar */
        .sidebar {
            background-color: var(--sidebar-bg) !important;
            border-color: var(--sidebar-border) !important;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text) !important;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--sidebar-hover);
        }

        .sidebar .nav-link.active {
            background-color: var(--sidebar-active);
            color: white !important;
        }

        .sidebar .text-body-secondary {
            color: var(--sidebar-muted) !important;
        }

        .sidebar .sidebar-heading {
            color: var(--sidebar-muted) !important;
        }

        .sidebar .link-secondary {
            color: var(--sidebar-accent) !important;
        }

        .sidebar .link-secondary:hover {
            color: var(--sidebar-accent) !important;
            opacity: 0.8;
        }

        .sidebar .offcanvas-header {
            background-color: var(--sidebar-bg);
            border-bottom-color: var(--sidebar-border);
        }

        .sidebar .offcanvas-title {
            color: var(--sidebar-text);
        }

        .sidebar .btn-close {
            filter: var(--sidebar-text) invert(1);
        }

        .sidebar hr {
            border-color: var(--sidebar-border);
        }
    </style>

</head>

<body>
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="check2" viewBox="0 0 16 16">
            <path
                d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
        </symbol>
        <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
        </symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path
                d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
            <path
                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
        </symbol>
        <symbol id="sun-fill" viewBox="0 0 16 16">
            <path
                d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
        </symbol>
    </svg>

    <div class="container-fluid">
        <div class="row">
            <?php $BASE_PATH_PREFIX = '';
            include __DIR__ . '/partials/sidebar.php'; ?>
            

            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" defer></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" defer></script>
            <script src="js/script.js" defer></script>


            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    document.querySelectorAll('.sidebar .nav-link').forEach(function(element) {
                        element.addEventListener('click', function(e) {
                            let nextEl = element.nextElementSibling;
                            let parentEl = element.parentElement;

                            if (nextEl) {
                                e.preventDefault();
                                let mycollapse = new bootstrap.Collapse(nextEl);

                                if (nextEl.classList.contains('show')) {
                                    mycollapse.hide();
                                } else {
                                    mycollapse.show();
                                    // find other submenus with class=show
                                    var opened_submenu = parentEl.parentElement.querySelector('.submenu.show');
                                    // if it exists, then close all of them
                                    if (opened_submenu) {
                                        new bootstrap.Collapse(opened_submenu);
                                    }
                                }
                            }
                        }); // addEventListener
                    }) // forEach
                });
                // DOMContentLoaded  end
            </script>
</body>

</html>