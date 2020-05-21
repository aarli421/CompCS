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
?>
<div id="question_select">
<?php
$dom = new DOMDocument('1.0');

foreach ($passArr as $value) {
    $form = $dom->createElement('form');
    $formType = $dom->createAttribute('method');
    $formType->value = 'get';
    $form->appendChild($formType);

    $input = $dom->createElement('input');
    $questionName = $dom->createAttribute('name');
    $questionName->value = 'questionName';
    $value = $dom->createAttribute('value');
    $value->value = $value['name'];
    $input->appendChild($questionName);
    $input->appendChild($value);

    $button = '<button type="submit">Submit</button>';

    $form->appendChild($input);
    $form->$button;

    echo $dom->saveHTML();


    echo "Name: " . $value['name'] . "<br>";
    echo "Difficulty: " . $value['difficulty'] . "<br>";
}?>
</div>
<?php
require '../templates/footer.php';
?>