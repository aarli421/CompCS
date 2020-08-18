<?php
require '../templates/helper.php';
require '../templates/header.php';

$name = explode(".",  basename($_FILES['questionInput']['name']));

$uploadFile = 'questions/' . $_FILES['questionInput']['name'];
$targetFolder = 'questions/' . $name[0];

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['testcase_value'])) {
    if (move_uploaded_file($_FILES['questionInput']['tmp_name'], $uploadFile)) {
        `unzip $uploadFile -d $targetFolder`;
        `rm $uploadFile`;

        $ioDirAmount = `ls $targetFolder | wc -l`;
        echo "IO Dir:" . $ioDirAmount;
        $testAmount = ((int) ($ioDirAmount - 1)) / 2;

        $sth = $db->prepare("INSERT INTO `questions` (`name`, `unlock_value`, `testcase_value`, `testcases`) VALUES (?, ?);");
        $sth->execute([$name[0], $_POST['unlock_value'], $_POST['testcase_value'], $testAmount]);

        echo 'Successfully uploaded';
    } else {
        echo 'Unable to move file';
    }
}

//if (isset($_POST['addToSQL'])) {
//    $dir = new DirectoryIterator("questions");
//    foreach ($dir as $fileinfo) {
//        if (!$fileinfo->isDot()) {
////            echo $fileinfo->getFilename() . "<br>";
//
//            $sql = "
//            INSERT INTO `questions` (`name`, `difficulty`)
//            SELECT * FROM (SELECT ? AS `name`, ? AS `difficulty`) AS temp
//            WHERE NOT EXISTS (SELECT * FROM `questions` WHERE `name`=?) LIMIT 1;";
//            $sth = $db->prepare($sql);
//            $sth->execute([$fileinfo->getFilename(), 100, $fileinfo->getFilename()]);
//        }
//    }
//}
?>
<form method="post" action="questionUpload.php" enctype="multipart/form-data">
    Unlock Value: <input name="unlock_value" type="number" /> <br>
    Test Case Value: <input name="testcase_value" type="number" /> <br>
    Send this file: <input name="questionInput" type="file" />
    <input type="submit" value="Send File" />
</form> <br>
<!--<form method="post" action="questionUpload.php">-->
<!--    <input name="addToSQL" value="true" hidden>-->
<!--    <input type="submit" value="Add to SQL" />-->
<!--</form>-->
<?php
require  '../templates/footer.php';