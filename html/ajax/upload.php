<?php
session_start();
require '../../templates/helper.php';

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

echo '<pre>';
if (move_uploaded_file($_FILES['fileInput']['tmp_name'], $uploadFile)) {
    echo "File is valid, and was successfully uploaded.\n";

    $ioDirAmount = `ls $questionDir | wc -l`;
    //echo "IO Dir:" . $ioDirAmount;
    $testAmount = ((int) ($ioDirAmount - 1)) / 2;
    //echo "Test:" . $testAmount . "<br>";

    chdir($uploadDir);
    if ($fileType == "py") {
        try {
            for ($i = 1; $i <= $testAmount; $i++) {
                $runResults = run('../' . $questionDir, $questionName, $i, "python3 $fileName", 4);
                $symbol = $runResults['symbol'];

                if ($i == 1 && $symbol != '*') {
                    break;
                }

                echo $symbol . "<br>";
            }
        } catch (Exception $e) {
            echo "<h1>" . $e . "</h1>";
        }
    } else if ($fileType == "java") {
        try {
            full_run('../' . $questionDir, $questionName, "javac $fileName", "java $javaName", 30, 4, $testAmount);
        } catch (Exception $e) {
            echo "<h1>" . $e . "</h1>";
        }
    } else if ($fileType == "cpp") {
        try {
            full_run('../' . $questionDir, $questionName, "g++ -o $cppName $fileName", "./$cppName", 30, 2, $testAmount);
        } catch (Exception $e) {
            echo "<h1>" . $e . "</h1>";
        }
    } else if ($fileType == "c") {
        try {
            full_run('../' . $questionDir, $questionName, "gcc -o $cName $fileName", "./$cName", 30, 2, $testAmount);
        } catch (Exception $e) {
            echo "<h1>" . $e . "</h1>";
        }
    } else {
        echo "Only Python3, Java, C++, and C supported!";
    }
    chdir($ajaxDir);

} else {
    echo "Could not upload!";
}

//echo 'Here is some more debugging info: <br>';
//print_r($_FILES);

echo "</pre>";

function full_run($questionDir, $questionName, $compCmd, $runCmd, $compileTimeout, $runTimeout, $testAmount) {
    $result = exec_timeout($compCmd, $compileTimeout);
    echo $result['output'];

    if (!empty($result['errors'])) {
        echo 'Compilation failed!' . '<br>';
        echo $result['errors'];
        echo '<br>';
    } else {
        echo 'Compiled in ' . $result['time'] . '<br>';

        for ($i = 1; $i <= $testAmount; $i++) {
            $runResults = run($questionDir, $questionName, $i, $runCmd, $runTimeout);
            $symbol = $runResults['symbol'];

            if ($i == 1 && $symbol != '*') {
                echo "Did not pass because outcome was " . $symbol . "<br>";
                if ($symbol == 'X') {
                    if (hasValue($runResults['stdout'])) {
                        echo "The following was printed in stdout <br>";
                        echo $runResults['stdout'];
                    }

                    if (hasValue($runResults['fout'])) {
                        echo "The following was printed in fout <br>";
                        echo $runResults['fout'];
                    }
                }
                break;
            }

            echo $symbol . "<br>";
        }
    }
}

function run($questionDir, $questionName, $i, $cmd, $timeout) {
    $output = `cp -f {$questionDir}/{$i}.in {$questionName}.in`;
    if (!empty($output)) {
        throw new \Exception("Could not move input cases!");
    }

    `rm {$questionName}.out`;

    $result = exec_timeout($cmd, $timeout);

    if (!empty($result['errors'])) {
        return array('symbol' => '!', 'output' => $result['errors']);
    } else {
        if ($result['isTimedOut']) {
            return array('symbol' => 't');
        } else {
            echo 'Test case #' . $i . ' ran in ' . $result['time'] . "<br>";
        }
    }

    $output = `test -f {$questionName}.out || echo "does not exist"`;
    if(str_replace(array("\n", "\r"), '', $output) == 'does not exist') {
        return array('symbol' => 'M');
    }

    $output = `cmp -s {$questionDir}/{$i}.out {$questionName}.out && echo "alike"`;
    if(str_replace(array("\n", "\r"), '', $output) == 'alike') {
        return array('symbol' => '*');
    } else {
        $output = `[ -s {$questionName}.out ] || echo "empty"`;
        if (str_replace(array("\n", "\r"), '', $output) == 'empty') {
            if ($i == 1) {
                return array('symbol' => 'X', 'stdout' => $result['output']);
            } else {
                return array('symbol' => 'E');
            }
        } else {
            $contents = `cat {$questionName}.out`;
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