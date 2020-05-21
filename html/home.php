<?php
require '../templates/header.php';
session_start();
echo $_SESSION['user'] . "<br>";
?>
<div id="question_select">
<?php
$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

$sth = $db->prepare("SELECT * FROM `questions`");
$sth->execute();
$passArr = $sth->fetchAll();

foreach ($passArr as $value) {
    echo '<form method="get">';
    echo "Name: " . $value['name'] . "<br>";
    echo "Difficulty: " . $value['difficulty'] . "<br>";
    echo '<input name="questionName" value="gymnastics" hidden>';
    echo '<button type="submit">Go to question';
    echo $value['name'];
    echo '</button>';
    echo '</form>';
}?>
</div>
<form method="get">
    <input name="questionName" value="gymnastics" hidden>
    <button type="submit">Go to question</button>
</form>
<?php
require '../templates/footer.php';
?>