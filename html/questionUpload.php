<?php
require '../templates/helper.php';
require '../templates/header.php';

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

if (isset($_POST['addToSQL'])) {
    $dir = new DirectoryIterator("questions");
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
//            echo $fileinfo->getFilename() . "<br>";

            $sql = "
            INSERT INTO `questions` (`name`, `difficulty`)
            SELECT * FROM (SELECT ? AS `name`, ? AS `difficulty`) AS temp 
            WHERE NOT EXISTS (SELECT * FROM `questions` WHERE `name`=?) LIMIT 1;";
            $sth = $db->prepare($sql);
            $sth->execute([$fileinfo->getFilename(), 100, $fileinfo->getFilename()]);
        }
    }
}
?>
<form method="post" action="questionUpload.php" enctype="multipart/form-data">
    Difficulty: <input name="difficulty" type="number" /> <br>
    Send this file: <input name="questionInput" type="file" />
    <input type="submit" value="Send File" />
</form> <br>
<!--<form method="post" action="questionUpload.php">-->
<!--    <input name="addToSQL" value="true" hidden>-->
<!--    <input type="submit" value="Add to SQL" />-->
<!--</form>-->
<?php
require  '../templates/footer.php';