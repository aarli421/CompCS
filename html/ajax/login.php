<?php
session_start();
require '../../templates/helper.php';

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

if (hasValue($_POST['loginUsername']) && hasValue($_POST['loginPassword'])) {
    $sth = $db->prepare("SELECT `password`, `active` FROM `users` WHERE `username` = ?");
    $sth->execute([$_POST['loginUsername']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)){
        echo "You are not registered!";
    } else {
        if (hash('sha256', $_POST['loginPassword']) == $passArr[0]['password'] && $passArr[0]['active'] == 1) {
            $_SESSION['user'] = $_POST['loginUsername'];
            echo "Success";
            redirect("home");
        } else {
            echo "Login Failed";
        }
    }
}
?>
