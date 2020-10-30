<?php
require '../../templates/helper.php';

$_SESSION['time_refreshed'] = time();
echo $_SESSION['time_refreshed'];

if (isset($_SESSION['user'])) {
    $_SESSION['user'] = $_SESSION['user'];
}

if (isset($_SESSION['contest'])) {
    $_SESSION['contest'] = $_SESSION['contest'];
}