<?php
require '../../templates/helper.php';

if (isset($_SESSION['user'])) {
    $_SESSION['user'] = $_SESSION['user'];
}

if (isset($_SESSION['contest'])) {
    $_SESSION['contest'] = $_SESSION['contest'];
}