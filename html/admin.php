<?php
require '../templates/helper.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
}

$sth = $db->prepare("SELECT `admin` FROM users WHERE `user_id`=?");
$sth->execute([$user_id]);
$user = $sth->fetchAll();

if ($user[0]['admin'] < 2) {
    redirect("401");
}

require '../templates/header.php';

$message = "";

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['bonus']) && isset($_POST['admin']) && isset($_POST['testcase_value']) && isset($_POST['prompt']) && isset($_POST['contest'])) {
    $name = explode(".",  basename($_FILES['questionInput']['name']));

    $root = $_SERVER['DOCUMENT_ROOT'];
    $uploadFile = $root . '/questions/' . $_FILES['questionInput']['name'];
    $targetFolder = $root . '/questions/' . $name[0];
    $tempFile = $_FILES['questionInput']['tmp_name'];
    $uploadFolder = "/home/compcs/questions/" . $name[0];

    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `questions` WHERE `name`=?) LIMIT 1");
    $sth->execute([$name[0]]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] == 0) {
        $msg = `unzip -q $tempFile -d $uploadFolder`;

        if (!hasValue($msg)) {
            $ioDirAmount = `ls $targetFolder | wc -l`;

            if ($ioDirAmount % 2 == 0) {
                $testAmount = ((int)($ioDirAmount)) / 2;

                `sudo $scriptsDirectory/syncQuestion.sh`;
//        `sudo $scriptsDirectory/executeAsUser.sh questionsadmin "unzip $uploadFile -d $targetFolder; rm $uploadFile"`;

                $sth = $db->prepare("INSERT INTO `questions` (`name`, `prompt`, `unlock_value`, `testcase_value`, `testcases`, `admin`, `bonus`, `contest_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                $sth->execute([$name[0], $_POST['prompt'], $_POST['unlock_value'], $_POST['testcase_value'], $testAmount, $_POST['admin'], $_POST['bonus'], $_POST['contest']]);

                $msg = `rm -r $uploadFolder`;

                $message = "Successfully uploaded.";

//        echo 'Successfully uploaded';
            } else {
                $message = "Your input testcase files seem incorrect. Make sure you do not have a folder inside of the zip and instead just have all of the testcases";
            }
        } else {
            $message = "Unable to move file because " . $msg . ".";
//        echo 'Unable to move file';
        }
    } else {
        $message = "A question with this name already exists.";
    }
}

if (isset($_POST['editPrompt']) && isset($_POST['prompt']) && isset($_POST['questionName'])) {
    $sth = $db->prepare("UPDATE `questions` SET `prompt`=? WHERE `name`=?");
    $sth->execute([$_POST['prompt'], $_POST['questionName']]);

    if (!$sth) {
        $message = "Updating question failed.";
    }
}

if (isset($_POST['deleteQuestion']) && isset($_POST['questionName'])) {
    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `questions` WHERE `name`=?) LIMIT 1");
    $sth->execute([$_POST['questionName']]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] != 0) {
        $sth = $db->prepare("DELETE FROM `questions` WHERE `name`=?");
        $sth->execute([$_POST['questionName']]);

        $targetFolder = "/home/compcs/questions/" . $_POST['questionName'];

        $msg = `rm -r $targetFolder`;

        if (!hasValue($msg) || !$sth) {
            `sudo $scriptsDirectory/syncQuestion.sh --delete`;

            $message = "Successfully removed the question";
        } else {
            $message = "Could not delete question " . $msg;
        }
    } else {
        $message = "This question does not exist";
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
<br>
<form method="post" action="admin">
    Question Name: <input name="questionName"> <br>
    <input type="hidden" name="deleteQuestion" value="true">
    <input type="submit" value="Delete Question">
</form>
</section>
<script>
    $("#dialogDiv").html("<?php echo $message; ?>");
</script>
<?php
require  '../templates/footer.php';