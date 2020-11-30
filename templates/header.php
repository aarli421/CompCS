<?php
$fileName = basename($_SERVER["SCRIPT_FILENAME"], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <title>CCC
        <?php
        if ($fileName != "index") {
        ?>
            | <?php echo ucwords($fileName); ?>
        <?php
            if ($fileName == "question") {
        ?>
                | <?php echo $_GET['questionName']; ?>
        <?php
            } else if ($fileName == "home") {
        ?>
                | <?php echo $_SESSION['user']; ?>
        <?php
            } else if ($fileName == "section") { ?>
                | <?php echo $_GET['name']; ?>
        <?php
            }
        }
        ?>
    </title>
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
    <link rel="stylesheet" href="css/login-register-forgot.css">
    <link rel="stylesheet" href="css/tos.css">

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="css/index.css">

    <!-- SCRIPTS -->
    <script src="js/jquery.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/wow.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/smoothscroll.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
    <script id="MathJax-script" async
            src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js">
    </script>
    <script src="js/latex.js"></script>

    <?php
    if (isset($_SESSION['contest'])) {
    ?>
        <script src="js/refresh.js"></script>
    <?php
    } else {
        if ($fileName == "question") {
            ?>
        <script src="js/refresh.js"></script>
    <?php
        } else {
            if (isset($_SESSION['user'])) {
        ?>
<!--        <script src="js/loggedin.js"></script>-->
        <?php
            }
        }
    }
    ?>

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


            <a href="index" class="navbar-brand" style="padding: 1px; padding-left: 0px;">

                <img src="images/ccc-logo.svg" alt="" width="67" height="67" style="padding-bottom: 10px">

            </a>
            <a href="index" class="navbar-brand" style="padding: 1px; padding-left: 25px; font-size: 16px; text-align: left;">
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
                    <li><a href="contest" class="smoothScroll" style="font-size: 18px">Contest</a></li>
                <?php
                } else {
                ?>
                    <li><a href="login" class="smoothScroll" style="font-size: 18px">Login</a></li>
                    <li><a href="register" class="smoothScroll" style="font-size: 18px">Register</a></li>
                <?php
                }
                ?>
                <li><a href="result" class="smoothScroll" style="font-size: 18px">Result</a></li>
                <li><a href="curriculum" class="smoothScroll" style="font-size: 18px">Curriculum</a></li>
                <?php
                    if ($fileName == "home") {
                        ?>
                        <li>
                            <a class="dropbtn smoothScroll" onclick="myFunction()" style="font-size: 18px">Sections</a>
                            <div id ="myDropdown" class="dropdown-content">
                                <a href="#div0">Division 0</a>
                                <a href="#div1">Division 1</a>
                                <a href="#div2">Division 2</a>
                                <a href="#bonusquestions">Bonus</a>
                                <a href="#contestquestions">Contest Q's</a>
                            </div>
                        </li>
                        <?php
                    }
                ?>
                <li><a href="news" class="smoothScroll" style="font-size: 18px">News</a></li>
<!--                <li><a href="leaderboard" class="smoothScroll" style="font-size: 18px">Leaderboard</a></li>-->
<!--                <li><a href="contact" class="smoothScroll" style="font-size: 18px">Contact</a></li>-->
            </ul>


            <?php
            if (isset($_SESSION['user'])) {
                if (isset($_SESSION['contest']) && !isset($_SESSION['finish'])) {
                ?>
                    <ul class="nav navbar-nav navbar-right">
                        <form action="contest" method="post">
                            <input type="hidden" name="finish" value="true">
                            <input type="submit" class="section-btn" value="End Contest">
                        </form>
                    </ul>
            <?php
                } else {
            ?>
                    <ul class="nav navbar-nav navbar-right">
                        <form action="home" method="post">
                            <input type="hidden" name="logout" value="true">
                            <input type="submit" class="section-btn" value="Logout">
                        </form>
                    </ul>
            <?php
                }
            } else {
            ?>
                <ul class="nav navbar-nav navbar-right">
                    <a href="#footer" class="section-btn smoothScroll">Contact</a>
                </ul>
            <?php
            }
            ?>
        </div>

    </div>
</section>