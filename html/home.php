<?php
session_start();
require '../templates/header.php';
require '../templates/helper.php';
echo $_SESSION['user'] . "<br>";
?>
<div id="question_select">
<?php
$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

$sth = $db->prepare("SELECT `points` FROM `users` WHERE `username` = ?");
$sth->execute([$_SESSION['user']]);
$passArr = $sth->fetchAll();
$points = $passArr[0]['points'];

$sth = $db->prepare("SELECT * FROM `questions`");
$sth->execute();
$passArr = $sth->fetchAll();

foreach ($passArr as $value) {
    if ($value['unlock_value'] <= $points) {
        echo '<form method="get" action="question.php">';
        echo "Name: " . $value['name'] . "<br>";
        echo "Difficulty: " . $value['difficulty'] . "<br>";
        echo '<input name="questionName" value="';
        echo $value['name'];
        echo '" hidden>';
        echo '<button type="submit">Go to question</button>';
        echo '</form>';
    }
}?>
</div>
<?php
require '../templates/footer.php';
?>