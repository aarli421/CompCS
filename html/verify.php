<?php
require("../templates/header.php");
session_start();

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

if (hasValue($_GET['email']) && hasValue($_GET['hash'])) {
    $sth = $db->prepare("SELECT `username`, `email`, `hash`, `active` FROM `users` WHERE `email`=? AND `hash`=? AND `active`=0");
    $sth->execute([$_GET['email'], $_GET['hash']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {
        echo "Account not found!";
    } else {
        if ($passArr[0]['active'] == 1) {
            echo "Your account has already been made";
        } else {
            $sth = $db->prepare("UPDATE users SET active=1 WHERE email=? AND hash=? AND active=0");
            $sth->execute([$_GET['email'], $_GET['hash']]);

            $username = $passArr[0]['username'];
            `mkdir users/$username`;
            echo "Account Verified!";
        }
    }
}

require("../templates/footer.php");