<?php
require '../templates/header.php';
session_start();
echo $_SESSION['user'];

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

$sth = $db->prepare("SELECT * FROM `questions`");
$sth->execute();
$passArr = $sth->fetchAll();
print_r($passArr);

require '../templates/footer.php';