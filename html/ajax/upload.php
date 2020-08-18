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
    $arr['error'] = "You do not have enough points.";
    $err = true;
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

$fileName = $questionName . '.' . $fileType;
$uploadFile = $uploadDir . "/" . $fileName;
$tempFile = $_FILES['fileInput']['tmp_name'];

$file = $fileName;
$javaName = $questionName;
$cppName = $questionName . ".execpp";
$cName = $questionName . ".exec";

$arr['correct_cases'] = 0;
$msg = `sudo $scriptsDirectory/uploadProgram.sh $tempFile $uploadFile $username`;

if (!hasValue($msg)) {
    $fileVal = `cat $uploadFile`;

//    if (hasValue($msg)) {
//        $arr['error'] = "Could not upload file. Server error.";
//        die();
//    }

    $sth = $db->prepare("INSERT INTO `submissions` (`user_id`, `question_id`, `submission`, `timestamp`) VALUES (?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $fileVal, date('Y-m-d H:i:s', time())]);

    $testAmount = $question[0]['testcases'];

//    chdir($uploadDir);
    if ($fileType == "py") {
        try {
            for ($i = 1; $i <= $testAmount; $i++) {
                $runResults = run($questionDir,  $uploadDir, $questionName, $i, "python3 $file", 4, $scriptsDirectory, $username);
                if (!parse_results($runResults, $i, $arr)) {
                    break;
                }
            }
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "java") {
        try {
            full_run($questionDir, $questionName, $uploadDir, "javac $file", "java $javaName", 30, 4, $testAmount, $arr, $scriptsDirectory, $username);
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "cpp") {
        try {
            full_run($questionDir, $questionName, $uploadDir, "g++ -o $cppName $file", "$cppName", 30, 2, $testAmount, $arr, $scriptsDirectory, $username);
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
//    } else if ($fileType == "c") {
//        try {
//            full_run($questionDir, $questionName, $uploadDir, "gcc -o $cName $file", "$cName", 30, 2, $testAmount, $arr, $scriptsDirectory, $username);
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

if (!hasValue($arr['error'])) {
    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
    $sth->execute([$user_id, $question[0]['question_id']]);
    $max = $sth->fetchAll();

//    print_r($max);

    $sth = $db->prepare("INSERT INTO grades (`user_id`, `question_id`, `output_json`, `correct_cases`) VALUES (?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], json_encode($arr), $arr['correct_cases']]);

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

    $sth = $db->prepare("COMMIT;");
    $sth->execute();
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
            if (!parse_results($runResults, $i, $arr)) {
                break;
            }
        }
    }
}

function run($questionDir, $uploadDir, $questionName, $i, $cmd, $timeout, $scriptsDirectory, $username) {
    $question = $uploadDir . '/' . $questionName;

    $output = `sudo $scriptsDirectory/executeAsUser.sh admin "cp -f {$questionDir}/{$i}.in {$question}.in"`;
    if (!empty($output)) {
        $arr['error'] = array("message" => "Server error.");
        die();
    }

    `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$question}.out"`;

    $result = exec_timeout($cmd, $timeout, $uploadDir, $scriptsDirectory, $username);

    if (!empty($result['errors'])) {
        return array('symbol' => '!', 'output' => $result['errors']);
    } else {
        if ($result['isTimedOut']) {
            return array('symbol' => 'T');
        }
    }

    $contents = `cat {$question}.out`;

    $output = `test -f {$question}.out || echo "does not exist"`;
    if (str_replace(array("\n", "\r"), '', $output) == 'does not exist') {
        return array('symbol' => 'M', 'stdout' => $result['output']);
    }

    $output = `diff -w {$questionDir}/{$i}.out {$question}.out && echo "alike"`;
    if (str_replace(array("\n", "\r"), '', $output) == 'alike') {
        return array("symbol" => '*', 'time' => $result['time']);
    } else {
        $output = `[ -s {$question}.out ] || echo "empty"`;
        if (str_replace(array("\n", "\r"), '', $output) == 'empty') {
            if ($i == 1 && $result['output'] != '') {
                return array('symbol' => 'X', 'stdout' => $result['output']);
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