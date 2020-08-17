<!DOCTYPE html>
<html lang="en">
<head>

    <title>CCC</title>
    <link rel="shortcut icon" href="images/ccc-logo.svg" type="image/svg+xml">

    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/login-register.css">
    <link rel="stylesheet" href="css/home.css">

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="css/templatemo-style.css">

    <!-- SCRIPTS -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/smoothscroll.js"></script>

</head>
<body>

<!-- PRE LOADER -->
<section class="preloader">
    <div class="spinner">

        <span class="spinner-rotate"></span>

    </div>
</section>


<!-- MENU -->
<section class="navbar custom-navbar navbar-fixed-top" role="navigation">
    <div class="container">

        <div class="navbar-header">
            <button class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="icon icon-bar"></span>
                <span class="icon icon-bar"></span>
                <span class="icon icon-bar"></span>
            </button>

            <!-- LOGO TEXT HERE -->


            <a href="index.php" class="navbar-brand" style="padding: 1px; padding-left: 0px;">

                <img src="images/ccc-logo.svg" alt="" width="67" height="67" style="padding-bottom: 10px">

            </a>
            <a href="index.php" class="navbar-brand" style="padding: 1px; padding-left: 25px; font-size: 16px;">
                Competitive<span><br>Computing</span><br>Club
            </a>

        </div>

        <!-- MENU LINKS IN NAV BAR-->
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-nav-first" >
                <?php
                if (isset($_SESSION['user'])) {
                ?>
                    <li><a href="home" class="smoothScroll" style="font-size: 18px">Home</a></li>
                <?php
                } else {
                ?>
                    <li><a href="login" class="smoothScroll" style="font-size: 18px">Login</a></li>
                    <li><a href="register" class="smoothScroll" style="font-size: 18px">Register</a></li>
                <?php
                }
                ?>
                <li><a href="contact" class="smoothScroll" style="font-size: 18px">Contact</a></li>
            </ul>


            <?php
            if (isset($_SESSION['user'])) {
            ?>
                <ul class="nav navbar-nav navbar-right">
                    <form action="home" method="post">
                        <input type="hidden" name="logout" value="true">
                        <input type="submit" class="section-btn" value="Logout">
                    </form>
                </ul>
            <?php
            } else {
            ?>
                <ul class="nav navbar-nav navbar-right">
                    <a href="#footer" class="section-btn">Learn to Code?</a>
                </ul>
            <?php
            }
            ?>
        </div>

    </div>
</section>