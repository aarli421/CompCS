<?php
require '../templates/helper.php';
require '../templates/header.php';

$name = explode(".",  basename($_FILES['questionInput']['name']));

$root = $_SERVER['DOCUMENT_ROOT'];
$uploadFile = $root . '/questions/' . $_FILES['questionInput']['name'];
$targetFolder = $root . '/questions/' . $name[0];
$tempFile = $_FILES['questionInput']['tmp_name'];

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['testcase_value']) && isset($_POST['prompt'])) {
    $msg = `sudo $scriptsDirectory/uploadQuestion.sh $tempFile $uploadFile`;

    if (!hasValue($msg)) {
        `sudo $scriptsDirectory/executeAsUser.sh questionsadmin "unzip $uploadFile -d $targetFolder; rm $uploadFile"`;

        $ioDirAmount = `ls $targetFolder | wc -l`;
        $testAmount = ((int) ($ioDirAmount - 1)) / 2;

        $sth = $db->prepare("INSERT INTO `questions` (`name`, `unlock_value`, `testcase_value`, `testcases`) VALUES (?, ?, ?, ?);");
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

if (isset($_POST['editPrompt']) && isset($_POST['prompt']) && isset($_POST['questionName'])) {
    $sth = $db->prepare("UPDATE `questions` SET `prompt`=? WHERE `name`=?");
    $sth->execute([$_POST['prompt'], $_POST['questionName']]);

    if (!$sth) {
    ?>
        <script>
        $("#dialogDiv").html("Updating question failed.");
        </script>
    <?php
    }
}
?>
<section>
    <p id="dialogDiv"></p>
<form method="post" action="questionUpload" enctype="multipart/form-data">
    Unlock Value: <input name="unlock_value" type="number" /> <br>
    Test Case Value: <input name="testcase_value" type="number" /> <br>
    Send this file: <input name="questionInput" type="file" /> <br>
    Prompt: <textarea name="prompt"></textarea> <br>
    <input type="submit" value="Send File" />
</form>
<br>
<form method="post" action="questionUpload">
    Question Name: <input name="questionName"> <br>
    Edit Prompt: <textarea name="prompt"></textarea> <br>
    <input type="hidden" name="editPrompt" value="true">
    <input type="submit" value="Edit Prompt">
</form>
</section>
<?php
require  '../templates/footer.php';