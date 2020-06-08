<?php
$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

$name = explode(".",  basename($_FILES['questionInput']['name']));

$uploadFile = 'questions/' . $_FILES['questionInput']['name'];
$targetFolder = 'questions/' . $name[0];

if (isset($_FILES['questionInput'])) {
    if (move_uploaded_file($_FILES['questionInput']['tmp_name'], $uploadFile)) {
        `unzip $uploadFile -d $targetFolder`;
        `rm $uploadFile`;

        $sth = $db->prepare("INSERT INTO `questions` (`name`, `difficulty`) VALUES (?, ?);");
        $sth->execute([$name[0], $_POST['difficulty']]);

        echo 'Successfully uploaded';
    } else {
        echo 'Unable to move file';
    }
}
?>
<form method="post" action="questionUpload.php" enctype="multipart/form-data">
    <input name="difficulty" type="number" />
    Send this file: <input name="questionInput" type="file" />
    <input type="submit" value="Send File" />
</form>