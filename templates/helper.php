<?php
session_start();
function getRootDir() {
    $dir = dirname($_SERVER["PHP_SELF"]);
    return $dir;
}

function getCurrDate() {
    return date('Y-m-d H:i:s', time());
}

function setupDb() {
    $dsn = 'mysql:host=localhost;dbname=compcs';
    $user = 'aaron';

    $handle = fopen($_SERVER['DOCUMENT_ROOT'] . '/../private/keys.csv', 'r');
    $data = fgetcsv($handle, 5, ',');
    $password = $data[0];

    try {
        $db = new PDO($dsn, $user, $password);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
//        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        echo $e;
        return false;
    }
}

function redirect($file) {
    $loc = $file; //. ".php";
    $host = $_SERVER["HTTP_HOST"];
    $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
    header("Location: https://$host/$loc");
}

function hasValue($value) {
    return isset($value) && !empty($value);
}

function postDiscord($msg) {
    $url = "https://discordapp.com/api/webhooks/756314285231046766/axZYJ85XJ6-goEPa3pgAsxDzqpV2FAEDc1yd8m_i4OctUCnjLRJRHIwMfYe9g0AH5-Ca";
    $data = array(
        'username' => 'Debugging',
        'content' => $msg
    );

    $options = array(
        'http' => array(
            'method'  => 'POST',
            'content' => json_encode($data),
            'header' =>  "Content-Type: application/json\r\n"
        )
    );

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
}

$db = setupDb();
if (!$db) {
    die("Database could not load");
}

$sth = $db->prepare("SELECT `user_id` FROM users WHERE `username`=?");
$sth->execute([$_SESSION['user']]);
$passArr = $sth->fetchAll();
$user_id = $passArr[0]['user_id'];

$scriptsDirectory = "/home/compcs/scripts";

$usernameReg = "^[A-Za-z0-9]*$";
$passwordReg = "^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?]{8,}$";
$emailReg = "^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$";
