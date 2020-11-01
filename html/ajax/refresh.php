<?php
session_start();

//$_SESSION['time_refreshed'] = time();
echo session_id();

if (isset($_SESSION['user'])) {
    $_SESSION['user'] = $_SESSION['user'];
}

if (isset($_SESSION['contest'])) {
    $_SESSION['contest'] = $_SESSION['contest'];
}