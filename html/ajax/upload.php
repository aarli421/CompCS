<?php
require '../../templates/helper.php';

$questionName = $_POST['questionName'];

$sth = $db->prepare("SELECT * FROM questions WHERE `name`=?");
$sth->execute([$questionName]);
$question = $sth->fetchAll();

$sth = $db->prepare("SELECT `username`, `points`, `start` FROM users WHERE `user_id`=?");
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

        if ($curr >= $end || hasValue($_SESSION['finish'])) {
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
    if ($question[0]['contest_id'] != 0) {
        $sth = $db->prepare("SELECT `end` FROM contests WHERE `contest_id`=?");
        $sth->execute([$question[0]['contest_id']]);
        $contest = $sth->fetchAll();

        $curr = new DateTime(getCurrDate());
        $end = new DateTime($contest[0]['end']);

        if ($curr <= $end) {
            $arr['error'] = "You are not part of any contest.";
            $err = true;
        }
    }
}

$off = false;
if ($off) {
    $err = true;
    $arr['error'] = "The testing server is intentionally down right now. Please check back later.";
}

if ($err) {
    echo json_encode($arr);
    exit();
}

$curr_date = getCurrDate();
$curr = new DateTime($curr_date);
$sth = $db->prepare("UPDATE `views` SET `timestamp`=?, `active`=1 WHERE `user_id`=? AND `question_id`=?");
$sth->execute([$curr->format('Y-m-d H:i:s'), $user_id, $question[0]['question_id']]);

$curr_copy = new DateTime($curr_date);
$curr_copy->sub(new DateInterval("PT00H30M00S"));

$sth = $db->prepare("UPDATE `views` SET `active`=0 WHERE `timestamp`<?");
$sth->execute([$curr_copy->format('Y-m-d H:i:s')]);

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
//$msg = `sudo $scriptsDirectory/uploadProgram.sh $tempFile $uploadFile $username`;

$fileVal = file_get_contents($tempFile);

$testAmount = $question[0]['testcases'];

//if (!hasValue($msg)) {

try {
    $handle = fopen('../private/keys.csv', 'r');
    $data = fgetcsv($handle, 5, ',');

    $client = new GearmanClient();
    $client->addServer($data[2], 4730);

    $params = array(
        "fileType" => $fileType,
        "testAmount" => $testAmount,
//    "questionDir" => $questionDir,
//    "uploadDir" => $uploadDir,
        "questionName" => $questionName,
        "fileName" => $fileName,
        "fileVal" => $fileVal,
//    "scriptsDirectory" => $scriptsDirectory,
        "username" => $username,
        "javaName" => $javaName,
        "cppName" => $cppName
    );

    $data = serialize($params);
    $result = $client->doNormal("test", $data);

    $arr = unserialize($result);
//} else {
//    $arr['error'] = "Could not upload file. Server error.";
//}

    echo json_encode($arr);
} catch (Exception $e) {
    $arr['error'] = "The testing server is down or has an error. Please check any announcements about whether this is intentional.";
}

//postDiscord($_SESSION['user'] . " - Echoed output");

if (!hasValue($arr['error']) && hasValue($curr)) {
//    print_r($max);

//    postDiscord($_SESSION['user'] . " - Adding submissions");

    $language = "error";

    if ($fileType == "py") {
        $language = "python";
    } else if ($fileType == "java") {
        $language = "java";
    } else if ($fileType == "cpp") {
        $language = "c++";
    }

    if ($language == "error") die();

    $sth = $db->prepare("START TRANSACTION;");
    $sth->execute();

    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
    $sth->execute([$user_id, $question[0]['question_id']]);
    $max = $sth->fetchAll();

    $contest = 0;
    if (hasValue($_SESSION['contest'])) $contest = $_SESSION['contest'];

    $sth = $db->prepare("INSERT INTO submissions (`user_id`, `question_id`, `submission`, `language`, `timestamp`) VALUES (?, ?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $fileVal, $language, $curr->format('Y-m-d H:i:s')]);

//    postDiscord($_SESSION['user'] . " - Insert Submission- " . json_encode($sth->errorInfo()) . " | " . json_encode($question));

//    print_r($sth);

    $sth = $db->prepare("SELECT LAST_INSERT_ID();");
    $sth->execute();
    $id = $sth->fetchAll();

    $sth = $db->prepare("INSERT INTO grades (`user_id`, `question_id`, `submission_id`, `output_json`, `correct_cases`, `timestamp`, `contest_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $id[0][0], json_encode($arr), $arr['correct_cases'], $curr->format('Y-m-d H:i:s'), $contest]);

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

    $sth = $db->prepare("SELECT `upper` FROM `divisions` WHERE `bonus`=0;");
    $sth->execute();
    $divisions = $sth->fetchAll();

    if ($question[0]['unlock_value'] >= $user[0]['start']) {
        $sth = $db->prepare("START TRANSACTION;");
        $sth->execute();

        $sth = $db->prepare("UPDATE `users` SET `points`=`points`+? WHERE `user_id`=?;");
        $sth->execute([$points, $user_id]);

        $sth = $db->prepare("COMMIT;");
        $sth->execute();
    }

    if ($points != 0) postDiscord($csFirstDiscord, $_SESSION['user'] . " got " . $arr['correct_cases'] . "/" . $question[0]['testcases'] . " testcases on " . $questionName .  ".");

    $sth = $db->prepare("SELECT `name`, `upper` FROM `divisions` WHERE `bonus`=0;");
    $sth->execute();
    $divisions = $sth->fetchAll();

    foreach ($divisions as $key => $value) {
        if ($user[0]['points'] <= $value['upper'] && $user[0]['points'] + $points > $value['upper']) {
            postDiscord($csFirstDiscord, ":partying_face: :confetti_ball: " . $_SESSION['user'] . " passed " . $value['name'] . "! :partying_face: :confetti_ball:");
        }
    }

//    postDiscord($_SESSION['user'] . " - Commit Points - " . json_encode($sth->errorInfo()));
}