<?php
require '../templates/helper.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

$sth = $db->prepare("SELECT `admin` FROM users WHERE `user_id`=?");
$sth->execute([$user_id]);
$user = $sth->fetchAll();

if ($user[0]['admin'] < 2) {
    redirect("401");
    exit();
}

require '../templates/header.php';

$message = "";

if (isset($_FILES['questionInput']) && isset($_POST['unlock_value']) && isset($_POST['bonus']) && isset($_POST['admin']) && isset($_POST['testcase_value']) && isset($_POST['prompt']) && isset($_POST['contest'])) {
    $name = explode(".",  basename($_FILES['questionInput']['name']));

    $root = $_SERVER['DOCUMENT_ROOT'];
    $uploadFile = $root . '/questions/' . $_FILES['questionInput']['name'];
    $targetFolder = '/home/compcs/questions/' . $name[0];
    $tempFile = $_FILES['questionInput']['tmp_name'];
    $uploadFolder = "/home/compcs/questions/" . $name[0];

    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `questions` WHERE `name`=?) LIMIT 1");
    $sth->execute([$name[0]]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] == 0) {
        $msg = `unzip -q $tempFile -d $uploadFolder`;

        if (!hasValue($msg)) {
            $ioDirAmount = `ls $uploadFolder | wc -l`;

            if ($ioDirAmount % 2 == 0) {
                $testAmount = ((int)($ioDirAmount)) / 2;
//        `sudo $scriptsDirectory/executeAsUser.sh questionsadmin "unzip $uploadFile -d $targetFolder; rm $uploadFile"`;

                $sth = $db->prepare("INSERT INTO `questions` (`name`, `prompt`, `unlock_value`, `testcase_value`, `testcases`, `admin`, `bonus`, `contest_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);");
                $sth->execute([$name[0], $_POST['prompt'], $_POST['unlock_value'], $_POST['testcase_value'], $testAmount, $_POST['admin'], $_POST['bonus'], $_POST['contest']]);


                `sudo $scriptsDirectory/syncQuestion.sh`;

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
    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `questions` WHERE `name`=?) LIMIT 1");
    $sth->execute([$_POST['questionName']]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] != 0) {
        $sth = $db->prepare("UPDATE `questions` SET `prompt`=? WHERE `name`=?");
        $sth->execute([$_POST['prompt'], $_POST['questionName']]);

        if (!$sth) {
            $message = "Updating question failed.";
        }
    } else {
        $message = "This question does not exist.";
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

            $message = "Successfully removed the question.";
        } else {
            $message = "Could not delete question " . $msg;
        }
    } else {
        $message = "This question does not exist.";
    }
}

if (isset($_POST['editContest']) && isset($_POST['questionName']) && isset($_POST['contestId'])) {
    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `questions` WHERE `name`=?) LIMIT 1");
    $sth->execute([$_POST['questionName']]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] != 0) {
        $sth = $db->prepare("UPDATE `questions` SET `bonus`=0, `admin`=0, `contest_id`=? WHERE `name`=?");
        $sth->execute([$_POST['contestId'], $_POST['questionName']]);

        if (!$sth) {
            $message = "Updating question failed.";
        }
    } else {
        $message = "This question does not exist.";
    }
}

if (hasValue($_POST['top20']) && hasValue($_POST['contestId'])) {
    $sth = $db->prepare("SELECT * FROM `results` WHERE `contest_id`=? ORDER BY `score` DESC LIMIT 20");
    $sth->execute([$_POST['contestId']]);
    $results = $sth->fetchAll();

    foreach ($results as $ind => $result) {
        $sth = $db->prepare("SELECT MAX(`score`) FROM `results` WHERE `user_id`=? AND `contest_id`>?");
        $sth->execute([$result['user_id'], $result['contest_id']]);
        $max = $sth->fetchAll();
        if (empty($max)) $max[0][0] = 0;

        if ($max[0][0] < 20) {
            $message .= " OR `results`.`user_id`=" . $result['user_id'] . "\n";
        }
    }
}

if (hasValue($_POST['time']) && hasValue($_POST['contestId']) && hasValue($_POST['userId'])) {
    $sth = $db->prepare("SELECT DISTINCT `question_id` FROM `grades` WHERE `user_id`=? AND `contest_id`=?");
    $sth->execute([$_POST['userId'], $_POST['contestId']]);
    $question_ids = $sth->fetchAll();

    foreach ($question_ids as $ind => $question_id) {
        $sth = $db->prepare("SELECT * FROM grades WHERE user_id=? AND question_id=? AND `contest_id`=? ORDER BY `correct_cases` DESC LIMIT 1");
        $sth->execute([$_POST['userId'], $question_id['question_id'], $_POST['contestId']]);
        $grade = $sth->fetchAll();

        $json = json_decode($grade[0]['output_json']);

        foreach ($json as $number => $result) {
            if ($number == 'correct_cases') continue;

            $message .= $result->time . "\n";
        }
    }
}
?>
<section>
<p id="dialogDiv"><?php echo $message; ?></p>
<p>Schematics for uploading questions:<br>
    Normal Question: Bonus = 0, Admin = 0, Contest = 0<br>
    Bonus Question: Bonus = 1, Admin = 0, Contest = 0<br>
    Testing Contest Question: Bonus = 2, Admin = 1, Contest = 0</p>
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
<br>
<form method="post" action="admin">
    Question Name: <input name="questionName"> <br>
    Contest Id: <input name="contestId" type="number"> <br>
    <input type="hidden" name="editContest" value="true">
    <input type="submit" value="Change Question to Contest Question">
</form>
<br>
<form method="post" action="admin">
    Contest Id: <input name="contestId" type="number"> <br>
    <input type="hidden" name="top20" value="true">
    <input type="submit" value="Get Top 20">
</form>
<br>
<form method="post" action="admin">
    Contest Id: <input name="contestId" type="number"> <br>
    User Id: <input name="userId" type="number"> <br>
    <input type="hidden" name="time" value="true">
    <input type="submit" value="Get Time">
</form>
</section>
<?php
require  '../templates/footer.php';