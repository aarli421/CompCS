<?php
require '../../templates/helper.php';

$questionName = $_POST['questionName'];

$sth = $db->prepare("SELECT * FROM questions WHERE `name`=?");
$sth->execute([$questionName]);
$question = $sth->fetchAll();

$sth = $db->prepare("SELECT `username`, `points` FROM users WHERE `user_id`=?");
$sth->execute([$user_id]);
$user = $sth->fetchAll();

$err = false;
$arr = array();

if (!isset($_SESSION['user'])) {
    $arr['error'] = "You are not logged in! Refresh the page.";
    $err = true;
}

if (empty($question)) {
    $arr['error'] = "Question does not exist!";
    $err = true;
}

if ($user[0]['points'] < $question[0]['unlock_value']) {
    if (hasValue($_SESSION['contest'])) {
        if ($question[0]['contest_id'] != $_SESSION['contest']) {
            $arr['error'] = "You are not part of this contest.";
            $err = true;
        }
    } else {
        $arr['error'] = "You do not have enough points.";
        $err = true;
    }
}

if (hasValue($_SESSION['contest'])) {
    if ($question[0]['contest_id'] != 0 && $question[0]['contest_id'] != $_SESSION['contest']) {
        $arr['error'] = "You are not part of this question's contest.";
        $err = true;
    }

    if ($question[0]['contest_id'] == $_SESSION['contest']) {
        $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
        $sth->execute([$user_id, $_SESSION['contest']]);
        $try = $sth->fetchAll();

        $end = new DateTime($try[0]['end']);
        $curr = new DateTime(getCurrDate());

        if ($curr >= $end) {
            $arr['error'] = "Your contest has already ended. Please refresh your page or go back to contest page.";
            $err = true;
        }
    }
//    } else {
//        $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
//        $sth->execute([$user_id, $_SESSION['contest']]);
//        $try = $sth->fetchAll();
//
//        $diff = strtotime(getCurrDate()) - strtotime($try[0]['end']);
//        header("refresh:{$diff};url=contest.php");
//    }
} else {
    if ($passArr[0]['contest_id'] != 0) {
        $arr['error'] = "You are not part of any contest.";
        $err = true;
    }
}

if ($err) {
    echo json_encode($arr);
    exit();
}

$rootDir = $_SERVER['DOCUMENT_ROOT'];

$username = $user[0]['username'];

$uploadDir = $rootDir . '/users/' . $username;
$questionDir = $rootDir . '/questions/' . $questionName;

$name = explode(".",  basename($_FILES['fileInput']['name']));
$fileType = $name[1];

$fileName = $name[0] . '.' . $fileType;
$uploadFile = $uploadDir . "/" . $fileName;
$tempFile = $_FILES['fileInput']['tmp_name'];

$javaName = $name[0];
$cppName = $name[0] . ".execpp";
$cName = $name[0] . ".exec";

$arr['correct_cases'] = 0;
$msg = `sudo $scriptsDirectory/uploadProgram.sh $tempFile $uploadFile $username`;

$fileVal = `cat $uploadFile`;

$testAmount = $question[0]['testcases'];

if (!hasValue($msg)) {
    $client = new GearmanClient();
    $client->addServer();

    $params = array(
        "fileType" => $fileType,
        "testAmount" => $testAmount,
        "questionDir" => $questionDir,
        "uploadDir" => $uploadDir,
        "questionName" => $questionName,
        "fileName" => $fileName,
        "scriptsDirectory" => $scriptsDirectory,
        "username" => $username,
        "javaName" => $javaName,
        "cppName" => $cppName
    );

    $data = serialize($params);
    $result = $client->doNormal("test", $data);

    $arr = unserialize($result);
} else {
    $arr['error'] = "Could not upload file. Server error.";
}

echo json_encode($arr);

//postDiscord($_SESSION['user'] . " - Echoed output");

$date = getCurrDate();
if (!hasValue($arr['error']) && hasValue($date)) {
    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
    $sth->execute([$user_id, $question[0]['question_id']]);
    $max = $sth->fetchAll();

//    print_r($max);

//    postDiscord($_SESSION['user'] . " - Adding submissions");

    $sth = $db->prepare("START TRANSACTION;");
    $sth->execute();

    $sth = $db->prepare("INSERT INTO submissions (`user_id`, `question_id`, `submission`, `timestamp`) VALUES (?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $fileVal, $date]);

//    postDiscord($_SESSION['user'] . " - Insert Submission- " . json_encode($sth->errorInfo()) . " | " . json_encode($question));

//    print_r($sth);

    $sth = $db->prepare("SELECT LAST_INSERT_ID();");
    $sth->execute();
    $id = $sth->fetchAll();

    $sth = $db->prepare("INSERT INTO grades (`user_id`, `question_id`, `submission_id`, `output_json`, `correct_cases`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $id[0][0], json_encode($arr), $arr['correct_cases'], $date]);

//    postDiscord($_SESSION['user'] . " - Insert Grades- " . json_encode($sth->errorInfo()));

    $sth = $db->prepare("COMMIT;");
    $sth->execute();

//    postDiscord($_SESSION['user'] . " - Commit Submission + Grades - " . json_encode($sth->errorInfo()));

    $points = 0;
    if (empty($max)) {
        $points = $arr['correct_cases'] * $question[0]['testcase_value'];
    } else {
        if ($arr['correct_cases'] > $max[0][0]) {
            $points = ($arr['correct_cases'] - $max[0][0]) * $question[0]['testcase_value'];
        }
    }

    $sth = $db->prepare("START TRANSACTION;");
    $sth->execute();

    $sth = $db->prepare("UPDATE `users` SET `points`=`points`+? WHERE `user_id`=?;");
    $sth->execute([$points, $user_id]);

    if ($points != 0) postDiscord($csFirstDiscord, $_SESSION['user'] . " got " . $arr['correct_cases'] . "/" . $question[0]['testcases'] . " testcases on " . $questionName .  ".");

    $sth = $db->prepare("COMMIT;");
    $sth->execute();

//    postDiscord($_SESSION['user'] . " - Commit Points - " . json_encode($sth->errorInfo()));
}