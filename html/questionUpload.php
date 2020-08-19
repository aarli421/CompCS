<?php
require '../templates/helper.php';
require '../templates/header.php';

$name = explode(".",  basename($_FILES['questionInput']['name']));

$root = $_SERVER['DOCUMENT_ROOT'];
$uploadFile = $root . '/questions/' . $_FILES['questionInput']['name'];
$targetFolder = $root . '/questions/' . $name[0];

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['testcase_value'])) {
    $msg = `sudo $scriptsDirectory/uploadProgram.sh $tempFile $uploadFile $username`;

    if (!hasValue($msg)) {
        `sudo $scriptsDirectory/executeAsUser.sh questionsadmin "unzip $uploadFile -d $targetFolder; rm $uploadFile"`;

        $ioDirAmount = `ls $targetFolder | wc -l`;
        $testAmount = ((int) ($ioDirAmount - 1)) / 2;

        $sth = $db->prepare("INSERT INTO `questions` (`name`, `unlock_value`, `testcase_value`, `testcases`) VALUES (?, ?);");
        $sth->execute([$name[0], $_POST['unlock_value'], $_POST['testcase_value'], $testAmount]);
        ?>
        <script>
            $("#dialogDiv").html("Successfully uploaded. Don't refresh and confirm submission or else the question will be duplicated!");
        </script>
        <?php
        echo 'Successfully uploaded';
    } else {
        ?>
        <script>
            $("#dialogDiv").html("Unable to move file. Don't refresh and confirm submission or else it will be duplicated!");
        </script>
        <?php
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
<section>
    <p id="dialogDiv"></p>
<form method="post" action="questionUpload.php" enctype="multipart/form-data">
    Unlock Value: <input name="unlock_value" type="number" /> <br>
    Test Case Value: <input name="testcase_value" type="number" /> <br>
    Send this file: <input name="questionInput" type="file" />
    <input type="submit" value="Send File" />
</form>
</section>
<!--<form method="post" action="questionUpload.php">-->
<!--    <input name="addToSQL" value="true" hidden>-->
<!--    <input type="submit" value="Add to SQL" />-->
<!--</form>-->
<?php
require  '../templates/footer.php';