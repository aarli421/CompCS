<?php
require '../templates/header.php';
session_start();
echo $_SESSION['user'] . "<br>";

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

$sth = $db->prepare("SELECT * FROM `questions`");
$sth->execute();
$passArr = $sth->fetchAll();

foreach ($passArr as $value) {
    echo "Name: " . $value['name'] . "<br>";
    echo "Difficulty: " . $value['difficulty'] . "<br>";
}

require '../templates/footer.php';