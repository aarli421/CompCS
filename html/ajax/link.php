<?php
require '../../templates/helper.php';

if (hasValue($_POST['discord']) && hasValue($_POST['username']) && hasValue($_POST['password'])) {
    $sth = $db->prepare("SELECT * FROM `users` WHERE `username`=?");
    $sth->execute([$_POST['username']]);
    $user = $sth->fetchAll();

    if (!empty($user)) {
        $hashedPw = hash('sha256', $_POST['password']);

        if ($user[0]['password'] == $hashedPw) {
            $sth = $db->prepare("UPDATE `users` SET `discord`=? WHERE `username`=?");
            $sth->execute([$_POST['discord'], $_POST['username']]);

            if (!$sth) {
                die("There was a server side error in processing your info. Please try again later or contact the CCC team.");
            } else {
                echo "You have successfully linked your discord account with your CCC account. Delete your message to prevent other people from seeing your password. Do -update to update your roles in the discord server.";
            }
        } else {
            echo "Your password was incorrect.";
        }
    } else {
        echo "Your username does not exist.";
    }
}