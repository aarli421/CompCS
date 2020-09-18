<?php
$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction('test', 'test_program');

while ($worker->work()) {
    if ($worker->returnCode() != GEARMAN_SUCCESS) {
        echo "return_code: " . $worker->returnCode() . "\n";
        die();
    }
}

function test_program($job) {
    $workload = $job->workload();
    $arr = array();

    if ($data = unserialize($workload)) {
        $fileType = $data['fileType'];
        $testAmount = $data['testAmount'];
        $questionDir = $data['questionDir'];
        $uploadDir = $data['uploadDir'];
        $questionName = $data['questionName'];
        $fileName = $data['fileName'];
        $scriptsDirectory = $data['scriptsDirectory'];
        $username = $data['username'];
        $javaName = $data['javaName'];
        $cppName = $data['cppName'];

        if ($fileType == "py") {
            try {
                for ($i = 1; $i <= $testAmount; $i++) {
                    $runResults = run($questionDir,  $uploadDir, $questionName, $i, "python3 $fileName", 4, $scriptsDirectory, $username, $arr);

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
    } else {
        $arr['error'] = "Could not transfer data. Server error.";
    }

    return serialize($arr);
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
    try {
        $result = exec_timeout($compCmd, $compileTimeout, $uploadDir, $scriptsDirectory, $username);
    } catch (Exception $e) {
        $arr['error'] = "Could not run file.<br>" . $e;
        return;
    }

    if (!empty($result['errors'])) {
        $arr['error'] = "Compilation failed!<br>" . $result['errors'];
    } else {
        for ($i = 1; $i <= $testAmount; $i++) {
            $runResults = run($questionDir, $uploadDir, $questionName, $i, $runCmd, $runTimeout, $scriptsDirectory, $username, $arr);

            if ($runResults === false) return;

            $questionComb = $uploadDir . '/' . $questionName;
            `sudo $scriptsDirectory/executeAsUser.sh admin "rm -f {$questionComb}.in"`;
            `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$questionComb}.out"`;

            if (!parse_results($runResults, $i, $arr)) {
                break;
            }
        }
    }
}

function run($questionDir, $uploadDir, $questionName, $i, $cmd, $timeout, $scriptsDirectory, $username, &$arr) {
    $questionComb = $uploadDir . '/' . $questionName;

    $output = `sudo $scriptsDirectory/executeAsUser.sh admin "cp -f {$questionDir}/{$i}.in {$questionComb}.in"`;
    if (!empty($output)) {
        $arr['error'] = "Server error.";
        return false;
    }

    `sudo $scriptsDirectory/executeAsUser.sh $username "rm -f {$questionComb}.out"`;

    try {
        $result = exec_timeout($cmd, $timeout, $uploadDir, $scriptsDirectory, $username);
    } catch (Exception $e) {
        $arr['error'] = "Could not run file.<br>" . $e;
        return false;
    }

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