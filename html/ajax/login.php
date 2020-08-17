<?php
require '../../templates/helper.php';

if (hasValue($_POST['loginUsername']) && hasValue($_POST['loginPassword'])) {
    $sth = $db->prepare("SELECT `password`, `active` FROM `users` WHERE `username`=?");
    $sth->execute([$_POST['loginUsername']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {
        echo "You are not registered!";
    } else {
        if (hash('sha256', $_POST['loginPassword']) == $passArr[0]['password'] && $passArr[0]['active'] == 1) {
            $_SESSION['user'] = $_POST['loginUsername'];
            echo "Success";
            exit();
//            redirect("home");
        } else {
            if ($passArr[0]['active'] == 1) {
                echo "Login failed.";
            } else {
                echo "Your account has not been activated.";
            }
        }
    }
}
