<?php
require '../templates/helper.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
}

$sth = $db->prepare("SELECT `admin` FROM users WHERE `user_id`=?");
$sth->execute([$user_id]);
$user = $sth->fetchAll();

if ($user[0]['admin'] < 1) {
    redirect("401");
}

require '../templates/header.php';

$name = explode(".",  basename($_FILES['questionInput']['name']));

$root = $_SERVER['DOCUMENT_ROOT'];
$uploadFile = $root . '/questions/' . $_FILES['questionInput']['name'];
$targetFolder = $root . '/questions/' . $name[0];
$tempFile = $_FILES['questionInput']['tmp_name'];
$uploadFolder = "/home/compcs/uploads/" . $name[0];

$message = "";

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['bonus']) && isset($_POST['admin']) && isset($_POST['testcase_value']) && isset($_POST['prompt']) && isset($_POST['contest'])) {
    $msg = `unzip -q $tempFile -d $uploadFolder`;

    if (!hasValue($msg)) {
        $ioDirAmount = `ls $targetFolder | wc -l`;

        if ($ioDirAmount % 2 == 0) {
            $testAmount = ((int) ($ioDirAmount)) / 2;

            $message = "sudo " . $scriptsDirectory . "/uploadQuestion.sh " . $uploadFolder;

            `sudo $scriptsDirectory/uploadQuestion.sh $uploadFolder`;
//        `sudo $scriptsDirectory/executeAsUser.sh questionsadmin "unzip $uploadFile -d $targetFolder; rm $uploadFile"`;

            $sth = $db->prepare("INSERT INTO `questions` (`name`, `prompt`, `unlock_value`, `testcase_value`, `testcases`, `admin`, `bonus`, `contest_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
            $sth->execute([$name[0], $_POST['prompt'], $_POST['unlock_value'], $_POST['testcase_value'], $testAmount, $_POST['admin'], $_POST['bonus'], $_POST['contest']]);

            $message = "Successfully uploaded. Don't refresh and confirm submission or else the question will be duplicated!";

//        echo 'Successfully uploaded';
        } else {
            $message = "Your input testcase files seem incorrect. Make sure you do not have a folder inside of the zip and instead just have all of the testcases";
        }
    } else {
        $message = "Unable to move file because " . $msg . ".";
//        echo 'Unable to move file';
    }
}

if (isset($_POST['editPrompt']) && isset($_POST['prompt']) && isset($_POST['questionName'])) {
    $sth = $db->prepare("UPDATE `questions` SET `prompt`=? WHERE `name`=?");
    $sth->execute([$_POST['prompt'], $_POST['questionName']]);

    if (!$sth) {
        $message = "Updating question failed.";
    }
}
?>
<section>
<p id="dialogDiv"></p>
<form method="post" action="admin" enctype="multipart/form-data">
    Unlock Value: <input name="unlock_value" type="number" /> <br>
    Test Case Value: <input name="testcase_value" type="number" /> <br>
    Bonus: <input name="bonus" type="number" /> <br>
    Admin: <input name="admin" type="number" /> <br>
    Contest: <input name="contest" type="number" /> <br>
    Testcases: <input name="questionInput" type="file" /> <br>
    Prompt: <textarea name="prompt"></textarea> <br>
    <input type="submit" value="Send Question" />
</form>
<br>
<form method="post" action="admin">
    Question Name: <input name="questionName"> <br>
    Edit Prompt: <textarea name="prompt"></textarea> <br>
    <input type="hidden" name="editPrompt" value="true">
    <input type="submit" value="Edit Prompt">
</form>
</section>
<script>
    $("#dialogDiv").html("<?php echo $message; ?>");
</script>
<?php
require  '../templates/footer.php';