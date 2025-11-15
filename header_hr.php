<?php

if(!isset($_SESSION["username"]))
{
	header("location:login.php");
}

$current_page = basename($_SERVER['PHP_SELF']);

?>

<!doctype html>
<html lang="ar" dir="rtl" data-bs-theme="light">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <title>جمعية أمة الخيرية</title>

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
    <link rel="stylesheet" href="css/index.css">

</head>

<body>


    <header style="margin-bottom: 0;" class="navbar sticky-top bg-light flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6 text-dark" href="#">جمعية أمة الخيرية</a>
        <span style="margin-left: 10px;" class="greeting mt-2 mb-2"> مرحبا <?php echo "بمدير الاتش ار دكتور اسامة"; ?></span>
    </header>

    <div class="container-fluid">
        <div class="row">
            


            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="js/script.js"></script>
            <script src="js/color-modes.js"></script>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    document.querySelectorAll('.sidebar .nav-link').forEach(function (element) {

                        element.addEventListener('click', function (e) {

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