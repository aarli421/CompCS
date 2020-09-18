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
$ajaxDir = '../../ajax/';

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

if (!hasValue($msg)) {

//    if (hasValue($msg)) {
//        $arr['error'] = "Could not upload file. Server error.";
//        die();
//    }

    $testAmount = $question[0]['testcases'];

//    chdir($uploadDir);
    if ($fileType == "py") {
        try {
            for ($i = 1; $i <= $testAmount; $i++) {
                $runResults = run($questionDir,  $uploadDir, $questionName, $i, "python3 $fileName", 4, $scriptsDirectory, $username);

                $questionComb = $uploadDir . '/' . $questionName;
                `sudo $scriptsDirectory/executeAsUser.sh admin "rm -f {$questionComb}.in"`;
                `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$questionComb}.out"`;

                if (!parse_results($runResults, $i, $arr)) {
                    break;
                }
            }
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$uploadDir}/{$fileName}"`;
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "java") {
        try {
            full_run($questionDir, $questionName, $uploadDir, "javac $fileName", "java $javaName", 30, 4, $testAmount, $arr, $scriptsDirectory, $username);
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$uploadDir}/{$fileName}"`;
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$uploadDir}/{$javaName}.class"`;
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "cpp") {
        try {
            full_run($questionDir, $questionName, $uploadDir, "g++ -o $cppName $fileName", "./$cppName", 30, 2, $testAmount, $arr, $scriptsDirectory, $username);
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$uploadDir}/{$fileName}"`;
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$uploadDir}/{$cppName}"`;
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
//    } else if ($fileType == "c") {
//        try {
//            full_run($questionDir, $questionName, $uploadDir, "gcc -o $cName $fileName", "$cName", 30, 2, $testAmount, $arr, $scriptsDirectory, $username);
//        } catch (Exception $e) {
//            $arr['error'] = $e;
//        }
    } else {
        $arr['error'] = "Only Python3, Java, and C++ supported!";
    }

//    chdir($ajaxDir);

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

//    postDiscord($_SESSION['user'] . " - Added Points - " . json_encode($sth->errorInfo()));

    $sth = $db->prepare("COMMIT;");
    $sth->execute();

//    postDiscord($_SESSION['user'] . " - Commit Points - " . json_encode($sth->errorInfo()));
}

function parse_results($runResults, $i, &$arr) {
    $symbol = $runResults['symbol'];

    if ($i == 1 && $symbol != '*') {
        $arr['error'] = "Did not pass because outcome was " . $symbol . "<br>";
        if ($symbol != '!' && $symbol != 'T') {
            if (hasValue($runResults['stdout'])) {
                $arr['error'] .= "The following was printed in stdout <br>" . $runResults['stdout'];
            }

            if (hasValue($runResults['fout'])) {
                $arr['error'] .= "The following was printed in fout <br>" . $runResults['fout'];
            }
        } else if ($symbol == '!') {
            $arr['error'] .= "The following was printed in error <br>" . $runResults['output'];
        }
        return false;
    }

    if ($symbol != '*') {
        $arr[$i] = array("symbol" => $symbol);
    } else {
        $arr[$i] = array("symbol" => $symbol, "time" => $runResults['time']);
        $arr['correct_cases'] += 1;
    }

    return true;
}

function full_run($questionDir, $questionName, $uploadDir, $compCmd, $runCmd, $compileTimeout, $runTimeout, $testAmount, &$arr, $scriptsDirectory, $username) {
    $result = exec_timeout($compCmd, $compileTimeout, $uploadDir, $scriptsDirectory, $username);

    if (!empty($result['errors'])) {
        $arr['error'] = "Compilation failed!<br>" . $result['errors'];
    } else {
        for ($i = 1; $i <= $testAmount; $i++) {
            $runResults = run($questionDir, $uploadDir, $questionName, $i, $runCmd, $runTimeout, $scriptsDirectory, $username);

            $questionComb = $uploadDir . '/' . $questionName;
            `sudo $scriptsDirectory/executeAsUser.sh admin "rm -f {$questionComb}.in"`;
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$questionComb}.out"`;

            if (!parse_results($runResults, $i, $arr)) {
                break;
            }
        }
    }
}

function run($questionDir, $uploadDir, $questionName, $i, $cmd, $timeout, $scriptsDirectory, $username) {
    $questionComb = $uploadDir . '/' . $questionName;

    $output = `sudo $scriptsDirectory/executeAsUser.sh admin "cp -f {$questionDir}/{$i}.in {$questionComb}.in"`;
    if (!empty($output)) {
        $arr['error'] = array("message" => "Server error.");
        die();
    }

    `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$questionComb}.out"`;

    $result = exec_timeout($cmd, $timeout, $uploadDir, $scriptsDirectory, $username);

    if (!empty($result['errors'])) {
        return array('symbol' => '!', 'output' => $result['errors']);
    } else {
        if ($result['isTimedOut']) {
            return array('symbol' => 'T');
        }
    }

    $contents = `cat {$questionComb}.out`;

    $output = `test -f {$questionComb}.out || echo "does not exist"`;
    if (str_replace(array("\n", "\r"), '', $output) == 'does not exist') {
        return array('symbol' => 'M', 'stdout' => $result['output']);
    }

    $output = `diff -w {$questionDir}/{$i}.out {$questionComb}.out && echo "alike"`;
    if (str_replace(array("\n", "\r"), '', $output) == 'alike') {
        return array("symbol" => '*', 'time' => $result['time']);
    } else {
        $output = `[ -s {$questionComb}.out ] || echo "empty"`;
        if (str_replace(array("\n", "\r"), '', $output) == 'empty') {
            if ($i == 1 && $result['output'] != '') {
                return array('symbol' => 'E', 'stdout' => $result['output']);
            } else {
                return array('symbol' => 'E', 'stdout' => $result['output']);
            }
        } else {
            return array('symbol' => 'X', 'stdout' => $result['output'], 'fout' => $contents);
        }
    }
}

function exec_timeout($cmd, $timeout, $uploadDir, $scriptsDirectory, $username) {
    $newCmd = "sudo $scriptsDirectory" . "/executeAsUser.sh $username \"cd $uploadDir; $cmd\"";

    // File descriptors passed to the process.
    $child_id = uniqid();
    $descriptors = array(
        0 => array('pipe', 'r'),  // stdin
        1 => array('pipe', 'w'),  // stdout
        2 => array('pipe', 'w')   // stderr
    );

    // Start the process.
    $time = -microtime(true);

    $process = proc_open($newCmd, $descriptors, $pipes);

    if (!is_resource($process)) {
        throw new \Exception('Could not execute process');
    }

    // Set the stdout stream to non-blocking.
    stream_set_blocking($pipes[1], 0);

    // Set the stderr stream to non-blocking.
    stream_set_blocking($pipes[2], 0);

    $cd_offset = 0.035;

    // Turn the timeout into microseconds.
    $timeout = ($timeout + $cd_offset) * 1000000;

    // Output buffer.
    $buffer = '';

    $timedOut = true;

    // While we have time to wait.
    while ($timeout > 0) {
        $start = microtime(true);

        // Wait until we have output or the timer expired.
        $read  = array($pipes[1]);
        $other = array();
        stream_select($read, $other, $other, 0, $timeout);

        // Get the status of the process.
        // Do this before we read from the stream,
        // this way we can't lose the last bit of output if the process dies between these functions.
        $status = proc_get_status($process);

        // Read the contents from the buffer.
        // This function will always return immediately as the stream is non-blocking.
        $buffer .= stream_get_contents($pipes[1]);

        if (!$status['running']) {
            // Break from this loop if the process exited before the timeout.
            $timedOut = false;
            break;
        }

        // Subtract the number of microseconds that we waited.
        $timeout -= (microtime(true) - $start) * 1000000;
    }

    // Check if there were any errors.
    $errors = stream_get_contents($pipes[2]);

    // Instead of throwing the error, it is returned
//    if (!empty($errors)) {
//        throw new \Exception($errors);
//    }

    // Kill the process in case the timeout expired and it's still running.
    // If the process already exited this won't do anything.
    $time += microtime(true);
    proc_terminate($process, 9);

    // Close all streams.
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    proc_close($process);

    return array("output" => $buffer, "time" => $time - $cd_offset, "errors" => $errors, "isTimedOut" => $timedOut);
}