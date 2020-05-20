<?php
require("../templates/header.php");

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

if (hasValue($_GET['email']) && hasValue($_GET['hash'])) {
    $sth = $db->prepare("SELECT `email`, `hash`, `active` FROM `users` WHERE `email`=? AND `hash`=? AND `active`=0");
    $sth->execute([$_GET['email'], $_GET['hash']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {

    } else {
        $sth = $db->prepare("UPDATE users SET active=1 WHERE email=? AND hash=? AND active=0");
        $sth->execute([$_GET['email'], $_GET['hash']]);
        echo "Account Verified!";
    }
}

require("../templates/footer.php");