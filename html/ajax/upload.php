<?php
require '../../templates/helper.php';
if (!isset($_SESSION['user'])) {
    echo "YOU ARE NOT LOGGED IN";
    die;
}

$uploadDir = '../users/' . $_SESSION['user'] . '/';
$questionName = $_POST['questionName'];
$questionDir = '../questions/' . $questionName;
$ajaxDir = '../../ajax/';

$name = explode(".",  basename($_FILES['fileInput']['name']));
$fileType = $name[1];

$fileName = $questionName . '.' . $fileType;
$uploadFile = $uploadDir . $fileName;

$javaName = $questionName;
$cppName = $questionName . ".execpp";
$cName = $questionName . ".exec";

$arr = array();
$arr['correct_cases'] = 0;

$sth = $db->prepare("SELECT * FROM questions WHERE `name`=?");
$sth->execute([$questionName]);
$question = $sth->fetchAll();

if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $uploadFile)) {
//    echo "File is valid, and was successfully uploaded.\n";

    $fileVal = `cat $uploadFile`;
    $sth = $db->prepare("INSERT INTO `submissions` (`user_id`, `question_id`, `submission`, `timestamp`) VALUES (?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], $fileVal, date('Y-m-d H:i:s', time())]);

//    $ioDirAmount = `ls $questionDir | wc -l`;
    //echo "IO Dir:" . $ioDirAmount;
//    $testAmount = ((int) ($ioDirAmount - 1)) / 2;
    //echo "Test:" . $testAmount . "<br>";

    $testAmount = $question[0]['testcases'];

    chdir($uploadDir);
    if ($fileType == "py") {
        try {
            for ($i = 1; $i <= $testAmount; $i++) {
                $runResults = run('../' . $questionDir, $questionName, $i, "python3 $fileName", 4);
                $symbol = $runResults['symbol'];

                if ($i == 1 && $symbol != '*') {
//                $arr['error_symbol'] = $symbol;
                    $arr['error'] = "Did not pass because outcome was " . $symbol . "<br>";
//                echo "Did not pass because outcome was " . $symbol . "<br>";
                    if ($symbol != '!' && $symbol != 'T') {
                        if (hasValue($runResults['stdout'])) {
                            $arr['error'] .= "The following was printed in stdout <br>" . $runResults['stdout'];
//                        echo "The following was printed in stdout <br>";
//                        echo $runResults['stdout'];
                        }

                        if (hasValue($runResults['fout'])) {
                            $arr['error'] .= "The following was printed in fout <br>" . $runResults['fout'];
//                        echo "The following was printed in fout <br>";
//                        echo $runResults['fout'];
                        }
                    } else if ($symbol == '!') {
                        $arr['error'] .= "The following was printed in error <br>" . $runResults['output'];
                    }
                    break;
                }

                if ($symbol != '*') {
                    $arr[$i] = array("symbol" => $symbol);
//                echo $symbol;
                } else {
                    $arr[$i] = array("symbol" => $symbol, "time" => $runResults['time']);
                    $arr['correct_cases'] += 1;
//                echo $symbol . "<br>" . $runResults['time'];
                }

//                if ($i == $testAmount) echo json_encode($arr);
            }
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "java") {
        try {
            full_run('../' . $questionDir, $questionName, "javac $fileName", "java $javaName", 30, 4, $testAmount, $arr);
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "cpp") {
        try {
            full_run('../' . $questionDir, $questionName, "g++ -o $cppName $fileName", "./$cppName", 30, 2, $testAmount, $arr);
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else if ($fileType == "c") {
        try {
            full_run('../' . $questionDir, $questionName, "gcc -o $cName $fileName", "./$cName", 30, 2, $testAmount, $arr);
        } catch (Exception $e) {
            $arr['error'] = $e;
        }
    } else {
        $arr['error'] = "Only Python3, Java, C++, and C supported!";
    }

    chdir($ajaxDir);

} else {
    $arr['error'] = "Could not upload file. Server error.";
}

echo json_encode($arr);

if (!hasValue($arr['error'])) {
    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=1 AND question_id=1");
    $sth->execute([$user_id, $question[0]['question_id']]);
    $max = $sth->fetchAll();

    $sth = $db->prepare("INSERT INTO grades (`user_id`, `question_id`, `output_json`, `correct_cases`) VALUES (?, ?, ?, ?)");
    $sth->execute([$user_id, $question[0]['question_id'], json_encode($arr), $arr['correct_cases']]);

    $points = 0;
    if (empty($pastGrades)) {
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

function full_run($questionDir, $questionName, $compCmd, $runCmd, $compileTimeout, $runTimeout, $testAmount, &$arr) {
    $result = exec_timeout($compCmd, $compileTimeout);
//    echo $result['output'];

    if (!empty($result['errors'])) {
        $arr['error'] = "Compilation failed!<br>" . $result['errors'];
//        die();
//        echo 'Compilation failed!' . '<br>';
//        echo $result['errors'];
//        echo '<br>';
    } else {
//        echo 'Compiled in ' . $result['time'] . '<br>';

        for ($i = 1; $i <= $testAmount; $i++) {
            $runResults = run($questionDir, $questionName, $i, $runCmd, $runTimeout);
            $symbol = $runResults['symbol'];

            if ($i == 1 && $symbol != '*') {
//                $arr['error_symbol'] = $symbol;
                $arr['error'] = "Did not pass because outcome was " . $symbol . "<br>";
//                echo "Did not pass because outcome was " . $symbol . "<br>";
                if ($symbol != '!' && $symbol != 'T') {
                    if (hasValue($runResults['stdout'])) {
                        $arr['error'] .= "The following was printed in stdout <br>" . $runResults['stdout'];
//                        echo "The following was printed in stdout <br>";
//                        echo $runResults['stdout'];
                    }

                    if (hasValue($runResults['fout'])) {
                        $arr['error'] .= "The following was printed in fout <br>" . $runResults['fout'];
//                        echo "The following was printed in fout <br>";
//                        echo $runResults['fout'];
                    }
                } else if ($symbol == '!') {
                    $arr['error'] .= "The following was printed in error <br>" . $runResults['output'];
                }
                break;
            }

            if ($symbol != '*') {
                $arr[$i] = array("symbol" => $symbol);
//                echo $symbol;
            } else {
                $arr[$i] = array("symbol" => $symbol, "time" => $runResults['time']);
                $arr['correct_cases'] += 1;
//                echo $symbol . "<br>" . $runResults['time'];
            }
//            echo "<br>";

//            if ($i == $testAmount) echo json_encode($arr);

//            print_r($arr);
        }
    }
}

function run($questionDir, $questionName, $i, $cmd, $timeout) {
    $output = `cp -f {$questionDir}/{$i}.in {$questionName}.in`;
    if (!empty($output)) {
        $arr['error'] = array("message" => "Server error.");
        die();
//        throw new \Exception("Could not move input cases!");
    }

    `rm {$questionName}.out`;

    $result = exec_timeout($cmd, $timeout);

    if (!empty($result['errors'])) {
        return array('symbol' => '!', 'output' => $result['errors']);
    } else {
        if ($result['isTimedOut']) {
            return array('symbol' => 'T');
        }
    }

    $contents = `cat {$questionName}.out`;

    $output = `test -f {$questionName}.out || echo "does not exist"`;
    if(str_replace(array("\n", "\r"), '', $output) == 'does not exist') {
        return array('symbol' => 'M', 'stdout' => $result['output']);
    }

    $output = `diff -w {$questionDir}/{$i}.out {$questionName}.out && echo "alike"`;
    if(str_replace(array("\n", "\r"), '', $output) == 'alike') {
        return array("symbol" => '*', 'time' => $result['time']);
    } else {
        $output = `[ -s {$questionName}.out ] || echo "empty"`;
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

function exec_timeout($cmd, $timeout) {
    // File descriptors passed to the process.
    $descriptors = array(
        0 => array('pipe', 'r'),  // stdin
        1 => array('pipe', 'w'),  // stdout
        2 => array('pipe', 'w')   // stderr
    );

    // Start the process.
    $time = -microtime(true);

    $process = proc_open($cmd, $descriptors, $pipes);

    if (!is_resource($process)) {
        throw new \Exception('Could not execute process');
    }

    // Set the stdout stream to non-blocking.
    stream_set_blocking($pipes[1], 0);

    // Set the stderr stream to non-blocking.
    stream_set_blocking($pipes[2], 0);

    // Turn the timeout into microseconds.
    $timeout = $timeout * 1000000;

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

    return array("output" => $buffer, "time" => $time, "errors" => $errors, "isTimedOut" => $timedOut);
}