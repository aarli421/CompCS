<?php
require '../../templates/helper.php';

if (hasValue($_POST['discord'])) {
    $sth = $db->prepare("SELECT * FROM `users` WHERE `discord`=?");
    $sth->execute([$_POST['discord']]);
    $user = $sth->fetchAll();

    if (!empty($user)) {
        $s = $user[0]['school'];

        $sth = $db->prepare("SELECT * FROM `divisions` WHERE `bonus`=0");
        $sth->execute();
        $divisions = $sth->fetchAll();

        foreach ($divisions as $id => $division) {
            if ($user[0]['points'] >= $division[0]['lower']) {
                $s .= "|" . $division[0]['name'];
            }
        }

        echo $s;

    } else {
        echo "You have not linked your discord account yet.";
    }
}